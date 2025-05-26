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
        // Make product_id nullable in carts table for the new cart structure
        if (Schema::hasTable('carts') && Schema::hasColumn('carts', 'product_id')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->foreignId('product_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('carts') && Schema::hasColumn('carts', 'product_id')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->foreignId('product_id')->nullable(false)->change();
            });
        }
    }
};
