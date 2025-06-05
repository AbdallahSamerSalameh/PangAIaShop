<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditLoggable;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    use AuditLoggable;/**
     * Display a listing of admins.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */    public function index(Request $request)
    {        // Log admin list access
        $this->logCustomAction(
            'Viewed admin list',
            null,
            'Super Admin accessed the admin management list'
        );
        
        $searchQuery = $request->input('search');
        $perPage = $request->input('per_page', 15); // Default to 15, allow user selection
        
        // Validate per_page parameter
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }
        
        $admins = Admin::where('role', '!=', 'Super Admin') // Exclude Super Admins
            ->when($searchQuery, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->where('role', '!=', 'Super Admin'); // Double ensure Super Admins are excluded from search
            })
            ->orderBy('username')
            ->paginate($perPage);
        
        return view('admin.admins.index', compact('admins', 'searchQuery', 'perPage'));
    }/**
     * Show the form for creating a new admin.
     *
     * @return \Illuminate\View\View
     */    public function create()
    {        // Log admin creation form access
        $this->logCustomAction(
            'Accessed admin creation form',
            null,
            'Super Admin accessed the create new admin form'
        );
        
        $roles = ['Admin', 'Super Admin'];
        
        return view('admin.admins.create', compact('roles'));
    }

    /**
     * Store a newly created admin in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:Admin,Super Admin',
            'is_active' => 'boolean',
            'phone_number' => 'nullable|string|max:20',
        ]);
        
        // Hash password
        $validatedData['password_hash'] = Hash::make($validatedData['password']);
        unset($validatedData['password']); // Remove plain password
        
        // Set default active status if not provided
        if (!isset($validatedData['is_active'])) {
            $validatedData['is_active'] = true;
        }
          $admin = Admin::create($validatedData);
            // Log the action using AuditLoggable trait
        $this->logCreate(
            $admin,
            'Created new admin: ' . $admin->username . ' (' . $admin->email . ') with role: ' . $admin->role
        );
        
        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin created successfully!');
    }

    /**
     * Display the specified admin.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\View\View
     */    public function show(Admin $admin)
    {        // Log admin profile view
        $this->logCustomAction(
            'Viewed admin profile',
            $admin,
            'Super Admin viewed profile of: ' . $admin->username . ' (' . $admin->email . ')'
        );
        
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
     */    public function edit(Admin $admin)
    {        // Log admin edit form access
        $this->logCustomAction(
            'Accessed admin edit form',
            $admin,
            'Super Admin accessed edit form for: ' . $admin->username . ' (' . $admin->email . ')'
        );
        
        // Only Super Admin can change roles
        $roles = ['Admin', 'Super Admin'];
        
        return view('admin.admins.edit', compact('admin', 'roles'));
    }

    /**
     * Update the specified admin in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\RedirectResponse
     */    public function update(Request $request, Admin $admin)
    {
        $validationRules = [
            'username' => 'required|string|max:100',
            'email' => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('admins')->ignore($admin->id),
            ],
            'phone_number' => 'nullable|string|max:20',
            'role' => 'required|in:Admin,Super Admin',
            'is_active' => 'boolean',
        ];
        
        // Password is optional during update
        if ($request->filled('password')) {
            $validationRules['password'] = 'string|min:8|confirmed';
        }
        
        $validatedData = $request->validate($validationRules);        // Hash password if provided
        if (isset($validatedData['password'])) {
            $validatedData['password_hash'] = Hash::make($validatedData['password']);
            unset($validatedData['password']); // Remove plain password
        }
        
        // Set default active status if not provided
        if (!isset($validatedData['is_active']) && $request->has('is_active')) {
            $validatedData['is_active'] = false;
        }
        
        // Prevent changing the last super admin's role
        if (isset($validatedData['role']) && 
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
        
        // Store original data for audit logging
        $originalData = $admin->toArray();
        
        $admin->update($validatedData);
        
        // Log the action using AuditLoggable trait
        $changes = [];
        if (isset($validatedData['username'])) $changes[] = 'username';
        if (isset($validatedData['email'])) $changes[] = 'email';
        if (isset($validatedData['role'])) $changes[] = 'role';
        if (isset($validatedData['is_active'])) $changes[] = 'status';
        if (isset($validatedData['password_hash'])) $changes[] = 'password';
        if (isset($validatedData['phone_number'])) $changes[] = 'phone';
          $this->logUpdate(
            $admin,
            $originalData,
            'Updated admin: ' . $admin->username . ' (' . $admin->email . ') - Changed: ' . implode(', ', $changes)
        );
        
        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin updated successfully!');
    }

    /**
     * Remove the specified admin from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\RedirectResponse
     */    public function destroy(Request $request, Admin $admin)
    {
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
        }        // Log the action before deletion using AuditLoggable trait
        $this->logDelete(
            $admin,
            'Deleted admin: ' . $admin->username . ' (' . $admin->email . ') with role: ' . $admin->role
        );
        
        $admin->delete();
        
        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin deleted successfully!');
    }
}
