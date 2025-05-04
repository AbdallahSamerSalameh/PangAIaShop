<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new admin (Super Admin only)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:admins',
            'password_hash' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,Super Admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if the authenticated user is a Super Admin
        if ($request->user()->role !== 'Super Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admins can create new admins.'
            ], 403);
        }

        $admin = Admin::create([
            'username' => $request->username,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password_hash),
            'role' => $request->role,
            'is_active' => true,
            'created_by' => $request->user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Admin created successfully',
            'data' => [
                'admin' => $admin
            ]
        ], 201);
    }

    /**
     * Login admin and create token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password_hash' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check in admins table only
        $admin = Admin::where('email', $request->email)->first();
        
        // If admin not found
        if (!$admin) {
            logger("No admin found with email: {$request->email}");
            return response()->json([
                'success' => false,
                'message' => 'Admin not found with provided email'
            ], 401);
        }
        
        // Check if admin is active
        if (!$admin->is_active) {
            logger("Admin account is inactive: {$admin->id}");
            return response()->json([
                'success' => false,
                'message' => 'Admin account is inactive'
            ], 401);
        }
        
        logger("Admin found with ID: {$admin->id}, checking password...");
        
        // Try multiple authentication approaches
        $authenticated = false;
        
        // 1. Try standard Hash::check (works if password is properly hashed)
        if (Hash::check($request->password_hash, $admin->password_hash)) {
            logger("Password validated with Hash::check");
            $authenticated = true;
        } 
        // 2. Try direct comparison (in case password is stored as plaintext)
        else if ($request->password_hash === $admin->password_hash) {
            logger("Password validated with direct comparison (plaintext)");
            $authenticated = true;
        }
        // Password validation failed
        else {
            logger("Password validation failed");
            logger("Received password_hash: [length: ".strlen($request->password_hash)."]");
            logger("Stored hash: [length: ".strlen($admin->password_hash)."]");
        }
        
        if ($authenticated) {
            // Update last login timestamp
            $admin->last_login = now();
            $admin->failed_login_count = 0;
            $admin->save();
            
            // Generate token
            $token = $admin->createToken('auth_token')->plainTextToken;
            
            // Prepare admin data for response
            $adminData = $admin->toArray();
            $adminData['is_super_admin'] = ($admin->role === 'Super Admin');
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $adminData,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ]
            ]);
        }
        
        // Invalid credentials - increment failed login count
        $admin->failed_login_count += 1;
        $admin->save();
        
        return response()->json([
            'success' => false,
            'message' => 'Invalid login credentials'
        ], 401);
    }

    /**
     * Logout admin (Revoke the token)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated admin
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function user(Request $request)
    {
        $admin = $request->user();
        $adminData = $admin->toArray();
        $adminData['is_super_admin'] = ($admin->role === 'Super Admin');
        
        return response()->json([
            'success' => true,
            'data' => $adminData
        ]);
    }
}