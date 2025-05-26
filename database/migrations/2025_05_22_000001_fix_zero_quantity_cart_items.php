<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class FixZeroQuantityCartItems extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        // First, mark all cart items with zero quantity as deleted
        DB::statement("UPDATE cart_items SET deleted_at = NOW() WHERE quantity = 0 AND deleted_at IS NULL");
        
        // Mark all entries in the carts table with zero quantity as deleted
        DB::statement("UPDATE carts SET deleted_at = NOW() WHERE quantity = 0 AND deleted_at IS NULL");
        
        // Specifically handle the H&M product (product_id = 1)
        DB::statement("UPDATE cart_items SET deleted_at = NOW() WHERE product_id = 1 AND quantity = 0 AND deleted_at IS NULL");
        DB::statement("UPDATE carts SET deleted_at = NOW() WHERE product_id = 1 AND quantity = 0 AND deleted_at IS NULL");
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        // No rollback needed for this fix
    }
}
