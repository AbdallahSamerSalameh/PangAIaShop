<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixOrdersTable extends Command
{
    protected $signature = 'orders:fix';
    protected $description = 'Fix the orders table structure';

    public function handle()
    {
        $this->info('Checking orders table structure...');
        
        try {
            // Check if the table exists
            if (!Schema::hasTable('orders')) {
                $this->error('Orders table does not exist!');
                return 1;
            }
            
            // Add order_number column if it doesn't exist
            if (!Schema::hasColumn('orders', 'order_number')) {
                $this->info('Adding order_number column...');
                DB::statement('ALTER TABLE orders ADD COLUMN order_number VARCHAR(50) AFTER user_id');
                $this->info('Column added!');
            } else {
                $this->info('order_number column already exists.');
            }
            
            // Check discount column - either ensure it exists or rename from discount_amount
            if (!Schema::hasColumn('orders', 'discount') && Schema::hasColumn('orders', 'discount_amount')) {
                $this->info('Renaming discount_amount to discount...');
                DB::statement('ALTER TABLE orders CHANGE COLUMN discount_amount discount DECIMAL(10,2) DEFAULT 0.00');
                $this->info('Column renamed!');
            } else if (!Schema::hasColumn('orders', 'discount') && !Schema::hasColumn('orders', 'discount_amount')) {
                $this->info('Adding discount column...');
                DB::statement('ALTER TABLE orders ADD COLUMN discount DECIMAL(10,2) DEFAULT 0.00 AFTER total_amount');
                $this->info('Column added!');
            } else {
                $this->info('discount column already exists.');
            }
            
            // Add subtotal if it doesn't exist
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $this->info('Adding subtotal column...');
                DB::statement('ALTER TABLE orders ADD COLUMN subtotal DECIMAL(10,2) DEFAULT 0.00 AFTER total_amount');
                $this->info('Column added!');
            } else {
                $this->info('subtotal column already exists.');
            }
            
            // Add shipping if it doesn't exist
            if (!Schema::hasColumn('orders', 'shipping')) {
                $this->info('Adding shipping column...');
                DB::statement('ALTER TABLE orders ADD COLUMN shipping DECIMAL(10,2) DEFAULT 0.00 AFTER subtotal');
                $this->info('Column added!');
            } else {
                $this->info('shipping column already exists.');
            }
            
            // Add notes if it doesn't exist
            if (!Schema::hasColumn('orders', 'notes')) {
                $this->info('Adding notes column...');
                DB::statement('ALTER TABLE orders ADD COLUMN notes VARCHAR(500) NULL AFTER expected_delivery_date');
                $this->info('Column added!');
            } else {
                $this->info('notes column already exists.');
            }
            
            $this->info('Orders table structure fixed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error fixing orders table: ' . $e->getMessage());
            return 1;
        }
    }
}
