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
        // Drop the existing trigger that's causing problems
        DB::unprepared('DROP TRIGGER IF EXISTS before_order_amount_check');
        
        // Recreate the trigger with the correct column name
        DB::unprepared('
            CREATE TRIGGER before_order_amount_check BEFORE INSERT ON orders
            FOR EACH ROW
            BEGIN
                IF NEW.total_amount < 0 OR NEW.discount < 0 THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Order amounts cannot be negative";
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop our modified trigger
        DB::unprepared('DROP TRIGGER IF EXISTS before_order_amount_check');
        
        // Restore the original trigger
        DB::unprepared('
            CREATE TRIGGER before_order_amount_check BEFORE INSERT ON orders
            FOR EACH ROW
            BEGIN
                IF NEW.total_amount < 0 OR NEW.discount_amount < 0 THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Order amounts cannot be negative";
                END IF;
            END
        ');
    }
};
