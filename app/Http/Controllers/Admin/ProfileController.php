<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditLoggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Admin;
use App\Models\AdminAuditLog;

class ProfileController extends Controller
{
    use AuditLoggable;    /**
     * Display the profile for the authenticated admin.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $admin = Auth::guard('admin')->user();
        
        // Get recent activity logs for this admin with more comprehensive data
        $recentActivities = AdminAuditLog::where('admin_id', $admin->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
        
        // Get activity statistics
        $activityStats = [
            'total_activities' => AdminAuditLog::where('admin_id', $admin->id)->count(),
            'today_activities' => AdminAuditLog::where('admin_id', $admin->id)
                ->whereDate('created_at', today())->count(),
            'this_week_activities' => AdminAuditLog::where('admin_id', $admin->id)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'last_30_days_activities' => AdminAuditLog::where('admin_id', $admin->id)
                ->where('created_at', '>=', now()->subDays(30))->count(),
        ];
        
        // Get activity breakdown by action type
        $activityBreakdown = AdminAuditLog::where('admin_id', $admin->id)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();
          // Log profile view
        $this->logCustomAction(
            'viewed_own_profile',
            $admin,
            "Admin {$admin->username} viewed their profile page"
        );
        
        return view('admin.profile.show', compact(
            'admin', 
            'recentActivities', 
            'activityStats', 
            'activityBreakdown'
        ));
    }/**
     * Update the admin's profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $validatedData = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('admins')->ignore($admin->id),
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle profile image upload if provided
        if ($request->hasFile('profile_image')) {
            // Delete old profile image if it exists
            if ($admin->profile_image && Storage::disk('public')->exists($admin->profile_image)) {
                Storage::disk('public')->delete($admin->profile_image);
            }
            
            $imagePath = $request->file('profile_image')->store('admin/profile', 'public');
            $validatedData['profile_image'] = $imagePath;
        }        // Store original data for logging
        $originalData = [
            'username' => $admin->username,
            'email' => $admin->email,
            'phone_number' => $admin->phone_number,
            'profile_image' => $admin->profile_image
        ];

        // Update the admin's profile
        Admin::where('id', $admin->id)->update($validatedData);        // Refresh the admin model to get updated data
        $admin = Admin::find($admin->id);

        // Log the profile update
        $this->logCustomAction(
            'update_profile',
            $admin,
            "Updated profile information for admin: {$admin->username}",
            [
                'previous_data' => $originalData,
                'new_data' => [
                    'username' => $admin->username,
                    'email' => $admin->email,
                    'phone_number' => $admin->phone_number,
                    'profile_image' => $admin->profile_image
                ]
            ]
        );

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully!');
    }    /**
     * Update the admin's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validatedData = $request->validate([
            'current_password' => ['required', 'string', function ($attribute, $value, $fail) use ($admin) {
                if (!Hash::check($value, $admin->password_hash)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);        Admin::where('id', $admin->id)->update([
            'password_hash' => Hash::make($validatedData['password']),
            'last_password_change' => now()
        ]);

        // Log the password change
        $this->logCustomAction(
            'change_password',
            $admin,
            "Changed password for admin: {$admin->username}"
        );

        return redirect()->route('admin.profile')->with('success', 'Password updated successfully!');
    }
}
