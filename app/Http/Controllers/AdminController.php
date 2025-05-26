<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Display a listing of admins
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Ensure only super admins can list all admins
        if (!Auth::guard('admin')->user()->is_super_admin) {
            return response()->json([
                'message' => 'Unauthorized. Only super admins can view all admins.'
            ], 403);
        }
        
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');
        
        $query = Admin::query();
        
        // Apply search if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }
        
        $admins = $query->orderBy('id', 'desc')->paginate($perPage);
        
        // Log activity
        $this->logActivity('View', 'Viewed admin list');
        
        return response()->json($admins);
    }
    
    /**
     * Store a newly created admin
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Ensure only super admins can create new admins
        if (!Auth::guard('admin')->user()->is_super_admin) {
            return response()->json([
                'message' => 'Unauthorized. Only super admins can create new admins.'
            ], 403);
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:admins,username',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,Super Admin',
            'phone_number' => 'nullable|string',
            'is_active' => 'nullable|boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Create admin
        $admin = Admin::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone_number' => $request->phone_number,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
            'is_super_admin' => $request->role === 'Super Admin',
            'created_by' => Auth::guard('admin')->id()
        ]);
        
        // Log activity
        $this->logActivity('Create', 'Created new admin: ' . $admin->username);
        
        return response()->json([
            'message' => 'Admin created successfully',
            'data' => $admin
        ], 201);
    }
    
    /**
     * Display the specified admin
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Ensure only super admins can view any admin, or admins can only view themselves
        if (!Auth::guard('admin')->user()->is_super_admin && Auth::guard('admin')->id() != $id) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }
        
        $admin = Admin::findOrFail($id);
        
        // Log activity
        $this->logActivity('View', 'Viewed admin details: ' . $admin->username);
        
        return response()->json([
            'data' => $admin
        ]);
    }
    
    /**
     * Update the specified admin
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Ensure only super admins can update any admin, or admins can only update themselves
        if (!Auth::guard('admin')->user()->is_super_admin && Auth::guard('admin')->id() != $id) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }
        
        $admin = Admin::findOrFail($id);
        
        // Validate request
        $rules = [
            'username' => 'sometimes|required|string|unique:admins,username,' . $id,
            'email' => 'sometimes|required|email|unique:admins,email,' . $id,
            'phone_number' => 'nullable|string',
        ];
        
        // Only super admins can change roles and active status
        if (Auth::guard('admin')->user()->is_super_admin) {
            $rules['role'] = 'sometimes|required|string|in:admin,Super Admin';
            $rules['is_active'] = 'sometimes|boolean';
        }
        
        // Password is optional for updates
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Update fields
        if ($request->filled('username')) {
            $admin->username = $request->username;
        }
        
        if ($request->filled('email')) {
            $admin->email = $request->email;
        }
        
        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }
        
        if (Auth::guard('admin')->user()->is_super_admin) {
            if ($request->filled('role')) {
                $admin->role = $request->role;
                $admin->is_super_admin = $request->role === 'Super Admin';
            }
            
            if ($request->has('is_active')) {
                $admin->is_active = $request->is_active;
            }
        }
        
        if ($request->filled('phone_number')) {
            $admin->phone_number = $request->phone_number;
        }
        
        $admin->updated_by = Auth::guard('admin')->id();
        $admin->save();
        
        // Log activity
        $this->logActivity('Update', 'Updated admin: ' . $admin->username);
        
        return response()->json([
            'message' => 'Admin updated successfully',
            'data' => $admin
        ]);
    }
    
    /**
     * Remove the specified admin
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Ensure only super admins can delete admins
        if (!Auth::guard('admin')->user()->is_super_admin) {
            return response()->json([
                'message' => 'Unauthorized. Only super admins can delete admins.'
            ], 403);
        }
        
        // Prevent deletion of own account
        if (Auth::guard('admin')->id() == $id) {
            return response()->json([
                'message' => 'You cannot delete your own account.'
            ], 400);
        }
        
        $admin = Admin::findOrFail($id);
        $username = $admin->username;
        
        // Delete admin
        $admin->delete();
        
        // Log activity
        $this->logActivity('Delete', 'Deleted admin: ' . $username);
        
        return response()->json([
            'message' => 'Admin deleted successfully'
        ]);
    }
    
    /**
     * Get admin audit logs
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function auditLogs(Request $request)
    {
        // Ensure only super admins can view audit logs
        if (!Auth::guard('admin')->user()->is_super_admin) {
            return response()->json([
                'message' => 'Unauthorized. Only super admins can view audit logs.'
            ], 403);
        }
        
        $perPage = $request->input('per_page', 20);
        
        $logs = AdminAuditLog::with('admin')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        return response()->json($logs);
    }
    
    /**
     * Log admin activity
     *
     * @param string $action
     * @param string $description
     * @return void
     */
    private function logActivity($action, $description)
    {
        AdminAuditLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => $action,
            'resource' => 'admin',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}
