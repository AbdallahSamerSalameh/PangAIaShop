<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class NormalizeReturnedOrderStatus extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        // Update orders with status 'RETURNED', 'Returned', or any case variations to lowercase 'returned'
        DB::statement("UPDATE orders SET status = 'returned' WHERE LOWER(status) = 'returned'");
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        // No need for reversing this normalization
    }
}