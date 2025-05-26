<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;

class AdminTestController extends Controller
{
    /**
     * A test method to check if the controller can be accessed
     */
    public function test()
    {
        return "Admin Test Controller is working!";
    }
    
    /**
     * A direct login method that bypasses regular routing
     */
    public function directLogin()
    {
        // Check if the view exists
        if (View::exists('admin.auth.login')) {
            return view('admin.auth.login');
        }
        
        return "Admin login view exists but could not be rendered properly.";
    }
    
    /**
     * Direct login processing 
     */
    public function processLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        
        // Try direct authentication
        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('/admin/dashboard')->with('success', 'Logged in successfully');
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->except('password'));
    }
}
