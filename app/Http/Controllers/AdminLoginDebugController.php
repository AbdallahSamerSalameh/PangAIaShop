<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class AdminLoginDebugController extends Controller
{
    /**
     * Show a direct admin login page for debugging
     */
    public function showLogin()
    {
        // Check if the view exists
        if (View::exists('admin.auth.login')) {
            return view('admin.auth.login');
        }
        
        return "Admin login view exists: " . (View::exists('admin.auth.login') ? 'YES' : 'NO');
    }
}
