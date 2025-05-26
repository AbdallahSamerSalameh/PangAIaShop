<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Execute raw SQL to make product_id nullable
        DB::statement('ALTER TABLE `carts` MODIFY `product_id` BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to non-nullable
        DB::statement('ALTER TABLE `carts` MODIFY `product_id` BIGINT UNSIGNED NOT NULL');
    }
};
