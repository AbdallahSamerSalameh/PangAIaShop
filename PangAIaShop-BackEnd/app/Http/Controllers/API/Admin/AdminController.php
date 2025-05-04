<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Display a listing of all admins.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Pagination and filtering parameters
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $role = $request->input('role');
        
        // Query builder
        $query = Admin::query();
        
        // Apply search filters if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }
        
        // Apply role filter if provided
        if ($role) {
            $query->where('role', $role);
        }
        
        // Get paginated results
        $admins = $query->paginate($perPage);
        
        // Log this action
        $this->logActivity('Viewed admin list');
        
        return response()->json([
            'success' => true,
            'data' => $admins
        ]);
    }

    /**
     * Store a newly created admin in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:admins',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,Super Admin',
            'phone_number' => 'nullable|string|max:20',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Create new admin
        $admin = Admin::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone_number' => $request->phone_number,
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);
        
        // Log this action
        $this->logActivity("Created new admin: {$admin->username}");
        
        return response()->json([
            'success' => true,
            'message' => 'Admin created successfully',
            'data' => $admin
        ], 201);
    }

    /**
     * Display the specified admin.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $admin = Admin::findOrFail($id);
        
        // Log this action
        $this->logActivity("Viewed admin details: {$admin->username}");
        
        return response()->json([
            'success' => true,
            'data' => $admin
        ]);
    }

    /**
     * Update the specified admin in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        
        // Validate input
        $rules = [
            'username' => 'required|string|max:255|unique:admins,username,' . $id,
            'email' => 'required|string|email|max:255|unique:admins,email,' . $id,
            'role' => 'required|string|in:admin,Super Admin',
            'phone_number' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ];
        
        // Only validate password if it's provided
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Prevent Super Admin downgrade if this is the only Super Admin
        if ($admin->role === 'Super Admin' && $request->role !== 'Super Admin') {
            $superAdminCount = Admin::where('role', 'Super Admin')->count();
            if ($superAdminCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot downgrade the only Super Admin'
                ], 422);
            }
        }
        
        // Update admin
        $admin->username = $request->username;
        $admin->email = $request->email;
        $admin->role = $request->role;
        $admin->phone_number = $request->phone_number;
        
        if (isset($request->is_active)) {
            $admin->is_active = $request->is_active;
        }
        
        // Update password if provided
        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }
        
        $admin->updated_by = Auth::id();
        $admin->save();
        
        // Log this action
        $this->logActivity("Updated admin: {$admin->username}");
        
        return response()->json([
            'success' => true,
            'message' => 'Admin updated successfully',
            'data' => $admin
        ]);
    }

    /**
     * Remove the specified admin from the database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        
        // Prevent deletion of the only Super Admin
        if ($admin->role === 'Super Admin') {
            $superAdminCount = Admin::where('role', 'Super Admin')->count();
            if ($superAdminCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the only Super Admin'
                ], 422);
            }
        }
        
        // Store admin username for logging
        $username = $admin->username;
        
        // Delete admin
        $admin->delete();
        
        // Log this action
        $this->logActivity("Deleted admin: {$username}");
        
        return response()->json([
            'success' => true,
            'message' => 'Admin deleted successfully'
        ]);
    }

    /**
     * Retrieve the current admin profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        $admin = Auth::guard('admin')->user();
        
        return response()->json([
            'success' => true,
            'data' => $admin
        ]);
    }

    /**
     * Update the current admin profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request)
    {
        $adminId = Auth::guard('admin')->id();
        $admin = Admin::findOrFail($adminId);
        
        // Validate input
        $rules = [
            'username' => 'required|string|max:255|unique:admins,username,' . $admin->id,
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'phone_number' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ];
        
        // Only validate password if it's provided
        if ($request->filled('current_password')) {
            $rules['current_password'] = 'required|string';
            $rules['password'] = 'required|string|min:8|confirmed';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check current password if changing password
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $admin->password_hash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }
            
            $admin->password_hash = Hash::make($request->password);
        }
        
        // Update profile data
        $admin->username = $request->username;
        $admin->email = $request->email;
        $admin->phone_number = $request->phone_number;
        
        // Handle avatar upload if provided
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($admin->avatar_url) {
                // Logic to delete old avatar file
            }
            
            // Store new avatar
            $avatar = $request->file('avatar');
            $path = $avatar->store('avatars', 'public');
            $admin->avatar_url = config('app.url') . '/storage/' . $path;
        }
        
        $admin->save();
        
        // Log this action
        $this->logActivity("Updated own profile");
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $admin
        ]);
    }

    /**
     * Log admin activity
     *
     * @param string $action
     * @return void
     */
    private function logActivity($action)
    {
        $admin = Auth::guard('admin')->user();
        
        AdminAuditLog::create([
            'admin_id' => $admin->id,
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}