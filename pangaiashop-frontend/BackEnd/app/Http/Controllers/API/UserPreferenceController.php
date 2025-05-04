<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserPreferenceController extends Controller
{
    /**
     * Get the authenticated user's preferences.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $preferences = UserPreference::firstOrCreate(['user_id' => $user->id]);
        
        return response()->json([
            'success' => true,
            'data' => $preferences
        ]);
    }

    /**
     * Update the authenticated user's preferences.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme' => 'nullable|string|in:light,dark,system',
            'notifications_enabled' => 'nullable|boolean',
            'email_notifications' => 'nullable|boolean',
            'push_notifications' => 'nullable|boolean',
            'language' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $preferences = UserPreference::firstOrCreate(['user_id' => $user->id]);
        
        // Update preferences
        if ($request->has('theme')) {
            $preferences->theme = $request->theme;
        }
        
        if ($request->has('notifications_enabled')) {
            $preferences->notifications_enabled = $request->notifications_enabled;
        }
        
        if ($request->has('email_notifications')) {
            $preferences->email_notifications = $request->email_notifications;
        }
        
        if ($request->has('push_notifications')) {
            $preferences->push_notifications = $request->push_notifications;
        }
        
        if ($request->has('language')) {
            $preferences->language = $request->language;
        }
        
        if ($request->has('currency')) {
            $preferences->currency = $request->currency;
        }
        
        $preferences->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
            'data' => $preferences
        ]);
    }
}