<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display a listing of admins.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Ensure the current user is a Super Admin
        if (Auth::guard('admin')->user()->role !== 'Super Admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access this page.');
        }
        
        $searchQuery = $request->input('search');
        
        $admins = Admin::when($searchQuery, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(15);
        
        return view('admin.admins.index', compact('admins', 'searchQuery'));
    }

    /**
     * Show the form for creating a new admin.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Ensure the current user is a Super Admin
        if (Auth::guard('admin')->user()->role !== 'Super Admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access this page.');
        }
        
        $roles = ['Admin', 'Super Admin'];
        
        return view('admin.admins.create', compact('roles'));
    }

    /**
     * Store a newly created admin in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Ensure the current user is a Super Admin
        if (Auth::guard('admin')->user()->role !== 'Super Admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to perform this action.');
        }
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:Admin,Super Admin',
            'is_active' => 'boolean',
        ]);
        
        // Hash password
        $validatedData['password'] = Hash::make($validatedData['password']);
        
        // Set default active status if not provided
        if (!isset($validatedData['is_active'])) {
            $validatedData['is_active'] = true;
        }
        
        $admin = Admin::create($validatedData);
          // Log the action
        AdminAuditLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => 'create',
            'resource' => 'admin',
            'resource_id' => $admin->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin created successfully!');
    }

    /**
     * Display the specified admin.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\View\View
     */
    public function show(Admin $admin)
    {
        // Ensure the current user is a Super Admin or viewing their own profile
        if (Auth::guard('admin')->user()->role !== 'Super Admin' &&
            Auth::guard('admin')->id() !== $admin->id) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access this page.');
        }
        
        $auditLogs = AdminAuditLog::where('admin_id', $admin->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();
        
        return view('admin.admins.show', compact('admin', 'auditLogs'));
    }

    /**
     * Show the form for editing the specified admin.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\View\View
     */
    public function edit(Admin $admin)
    {
        // Ensure the current user is a Super Admin or editing their own profile
        if (Auth::guard('admin')->user()->role !== 'Super Admin' &&
            Auth::guard('admin')->id() !== $admin->id) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access this page.');
        }
        
        // Only Super Admin can change roles
        $roles = [];
        if (Auth::guard('admin')->user()->role === 'Super Admin') {
            $roles = ['Admin', 'Super Admin'];
        }
        
        return view('admin.admins.edit', compact('admin', 'roles'));
    }

    /**
     * Update the specified admin in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Admin $admin)
    {
        // Ensure the current user is a Super Admin or updating their own profile
        $isSelfEdit = Auth::guard('admin')->id() === $admin->id;
        $isSuperAdmin = Auth::guard('admin')->user()->role === 'Super Admin';
        
        if (!$isSuperAdmin && !$isSelfEdit) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to perform this action.');
        }
        
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('admins')->ignore($admin->id),
            ],
        ];
        
        // Only allow role to be changed by super admins
        if ($isSuperAdmin) {
            $validationRules['role'] = 'required|in:Admin,Super Admin';
            $validationRules['is_active'] = 'boolean';
        }
        
        // Password is optional during update
        if ($request->filled('password')) {
            $validationRules['password'] = 'string|min:8|confirmed';
        }
        
        $validatedData = $request->validate($validationRules);
        
        // Hash password if provided
        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }
        
        // Set default active status if provided and user is super admin
        if ($isSuperAdmin && !isset($validatedData['is_active']) && $request->has('is_active')) {
            $validatedData['is_active'] = false;
        }
        
        // Super admins can't deactivate themselves
        if ($isSuperAdmin && $isSelfEdit && isset($validatedData['is_active']) && !$validatedData['is_active']) {
            return redirect()->route('admin.admins.edit', $admin->id)
                ->with('error', 'Super admins cannot deactivate themselves.');
        }
        
        // Prevent changing the last super admin's role
        if ($isSuperAdmin && 
            isset($validatedData['role']) && 
            $validatedData['role'] !== 'Super Admin' && 
            $admin->role === 'Super Admin') {
            
            $superAdminCount = Admin::where('role', 'Super Admin')
                ->where('is_active', true)
                ->count();
            
            if ($superAdminCount <= 1) {
                return redirect()->route('admin.admins.edit', $admin->id)
                    ->with('error', 'Cannot change the role of the last Super Admin.');
            }
        }
        
        $admin->update($validatedData);
          // Log the action
        AdminAuditLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => 'update',
            'resource' => 'admin',
            'resource_id' => $admin->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        if ($isSelfEdit) {
            return redirect()->route('admin.profile')
                ->with('success', 'Your profile was updated successfully!');
        }
        
        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin updated successfully!');
    }

    /**
     * Remove the specified admin from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, Admin $admin)
    {
        // Ensure the current user is a Super Admin
        if (Auth::guard('admin')->user()->role !== 'Super Admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to perform this action.');
        }
        
        // Prevent deleting yourself
        if (Auth::guard('admin')->id() === $admin->id) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot delete your own account.');
        }
        
        // Prevent deleting the last super admin
        if ($admin->role === 'Super Admin') {
            $superAdminCount = Admin::where('role', 'Super Admin')
                ->where('is_active', true)
                ->count();
            
            if ($superAdminCount <= 1) {
                return redirect()->route('admin.admins.index')
                    ->with('error', 'Cannot delete the last Super Admin account.');
            }
        }
          // Log the action before deletion
        AdminAuditLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => 'delete',
            'resource' => 'admin',
            'resource_id' => $admin->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        $admin->delete();
        
        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin deleted successfully!');
    }
}
