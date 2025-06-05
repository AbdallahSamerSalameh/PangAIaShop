<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Models\Review;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Load the setting helper function
        require_once app_path('Helpers/SettingHelper.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Schema::defaultStringLength(191);

        // Share notification data with admin views
        View::composer(['admin.layouts.topbar', 'admin.layouts.sidebar'], function ($view) {
            $pendingReviewsCount = Review::where('moderation_status', 'pending')->count();
            
            // Calculate actual unread notifications considering dismissals
            $unreadNotifications = 0;
            
            // Add to count if reviews notification is not dismissed
            if ($pendingReviewsCount > 0) {
                $unreadNotifications += 1; // Count as 1 notification regardless of number of reviews
            }
            
            $view->with([
                'unreadNotifications' => $unreadNotifications,
                'pendingReviewsCount' => $pendingReviewsCount
            ]);
        });
    }
}
