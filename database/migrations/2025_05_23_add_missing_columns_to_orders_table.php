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
        // First check if the orders table exists
        if (Schema::hasTable('orders')) {
            // Rename discount_amount to discount if it exists
            if (Schema::hasColumn('orders', 'discount_amount') && !Schema::hasColumn('orders', 'discount')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->renameColumn('discount_amount', 'discount');
                });
            }
            
            // Add admin_notes column if it doesn't exist
            if (!Schema::hasColumn('orders', 'admin_notes') && !Schema::hasColumn('orders', 'notes')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->text('admin_notes')->nullable();
                });
            }
            
            // Add notes column if it doesn't exist and admin_notes exists
            if (!Schema::hasColumn('orders', 'notes') && Schema::hasColumn('orders', 'admin_notes')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->string('notes', 500)->nullable();
                });
            }
            
            // Ensure order_number exists
            if (!Schema::hasColumn('orders', 'order_number')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->string('order_number', 50)->after('user_id')->nullable();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We won't try to remove these columns since they're essential to the app
    }
};
