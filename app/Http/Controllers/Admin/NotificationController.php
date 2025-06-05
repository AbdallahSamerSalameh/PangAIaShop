<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{    /**
     * Dismiss a notification for the current admin session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */    public function dismiss(Request $request)
    {
        $request->validate([
            'type' => 'required|string'
        ]);

        $notificationType = $request->input('type');
        
        // Handle reset all functionality
        if ($notificationType === 'reset_all') {
            // Clear all notification dismissals
            session()->forget([
                'notification_reviews_dismissed',
                'notification_orders_dismissed'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'All notification dismissals reset successfully'
            ]);
        }
        
        // Store dismissal in session
        session(["notification_{$notificationType}_dismissed" => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Notification dismissed successfully'
        ]);
    }

    /**
     * Dismiss all notifications for the current admin session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dismissAll(Request $request)
    {
        $request->validate([
            'types' => 'required|array',
            'types.*' => 'string'
        ]);

        $notificationTypes = $request->input('types');
        
        // Store dismissals in session for all provided types
        foreach ($notificationTypes as $type) {
            session(["notification_{$type}_dismissed" => true]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications dismissed successfully'
        ]);
    }
}
