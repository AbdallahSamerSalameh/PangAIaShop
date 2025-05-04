<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Array of tables that need soft delete functionality
        // We're excluding tables that already have soft delete functionality
        $tables = [
            'admins',
            'admin_audit_logs',
            'carts',
            'categories',
            'inventories',
            'orders',
            'order_items',
            'payments',
            'password_reset_tokens',
            'price_histories',
            'products',
            'product_categories',
            'product_images',
            'product_variants',
            'promo_codes',
            'reviews',
            'shipments',
            'support_tickets',
            'users',
            'user_preferences',
            'vendors',
            // Wishlists table already has softDeletes column
            // Wishlist_items table already has softDeletes column
        ];

        // Add softDeletes column to each table
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Array of tables with soft delete functionality
        $tables = [
            'admins',
            'admin_audit_logs',
            'carts',
            'categories',
            'inventories',
            'orders',
            'order_items',
            'payments',
            'password_reset_tokens',
            'price_histories',
            'products',
            'product_categories',
            'product_images',
            'product_variants',
            'promo_codes',
            'reviews',
            'shipments',
            'support_tickets',
            'users',
            'user_preferences',
            'vendors',
            // We're excluding wishlists and wishlist_items tables as they might have pre-existing softDeletes
        ];

        // Remove softDeletes column from each table
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
