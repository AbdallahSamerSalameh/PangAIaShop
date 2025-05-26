<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateProductStockStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update-stock-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the in_stock status for all products based on inventory quantities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating product stock status...');
        
        // Get all products with their inventory
        $products = \App\Models\Product::with('inventory')->get();
        $count = 0;
        
        foreach ($products as $product) {
            $inventory = $product->inventory;
            if ($inventory) {
                $wasInStock = $product->in_stock;
                $product->in_stock = ($inventory->quantity > 0);
                
                // Only save if there's a change to minimize database operations
                if ($wasInStock !== $product->in_stock) {
                    $product->save();
                    $count++;
                }
            } else {
                // No inventory record means product is not in stock
                if ($product->in_stock) {
                    $product->in_stock = false;
                    $product->save();
                    $count++;
                }
            }
        }
        
        $this->info("Updated stock status for {$count} products.");
    }
}
