<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing columns to orders table
        if (Schema::hasTable('orders')) {
            // Check for order_number column
            if (!Schema::hasColumn('orders', 'order_number')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->string('order_number', 50)->nullable()->after('user_id');
                });
            }
            
            // Check for subtotal column
            if (!Schema::hasColumn('orders', 'subtotal')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->decimal('subtotal', 10, 2)->default(0.00)->after('total_amount');
                });
            }
            
            // Check for shipping column
            if (!Schema::hasColumn('orders', 'shipping')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->decimal('shipping', 10, 2)->default(0.00)->after('subtotal');
                });
            }
            
            // Ensure the correct discount column exists
            if (Schema::hasColumn('orders', 'discount_amount') && !Schema::hasColumn('orders', 'discount')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->renameColumn('discount_amount', 'discount');
                });
            } elseif (!Schema::hasColumn('orders', 'discount') && !Schema::hasColumn('orders', 'discount_amount')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->decimal('discount', 10, 2)->default(0.00)->after('shipping');
                });
            }
            
            // Add notes column if it doesn't exist
            if (!Schema::hasColumn('orders', 'notes')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->string('notes', 500)->nullable()->after('admin_notes');
                });
            }
        }
        
        // Fix order_items table if needed
        if (Schema::hasTable('order_items')) {
            // Make sure discount_amount exists in order_items
            if (!Schema::hasColumn('order_items', 'discount_amount')) {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->decimal('discount_amount', 10, 2)->default(0.00)->after('tax_region');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We won't reverse these migrations since they're critical to the application
    }
};
