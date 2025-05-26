<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Admin;

class ProfileController extends Controller
{
    /**
     * Display the profile for the authenticated admin.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $admin = Auth::guard('admin')->user();
        
        return view('admin.profile.show', compact('admin'));
    }

    /**
     * Update the admin's profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('admins')->ignore($admin->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
        ]);        // Handle profile image upload if provided
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('admin/profile', 'public');
            $validatedData['profile_image'] = $imagePath;
        }

        // Update the admin's profile using where clause and update method
        \App\Models\Admin::where('id', $admin->id)->update($validatedData);

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully!');
    }

    /**
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
        ]);

        \App\Models\Admin::where('id', $admin->id)->update([
            'password_hash' => Hash::make($validatedData['password']),
            'last_password_change' => now()
        ]);

        return redirect()->route('admin.profile')->with('success', 'Password updated successfully!');
    }
}
