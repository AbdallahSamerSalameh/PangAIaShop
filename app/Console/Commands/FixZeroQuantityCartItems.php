<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class FixZeroQuantityCartItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:fix-zero-quantity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix zero quantity cart items that cannot be deleted';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting to fix zero quantity cart items...');

        // Step 1: Find and remove any cart items with zero quantity
        $cartItemsCount = CartItem::where('quantity', 0)->forceDelete();
        $this->info("Removed {$cartItemsCount} cart items with zero quantity");

        // Step 2: Find and mark as deleted any cart entries with zero quantity
        $cartsCount = Cart::where('quantity', 0)->update(['deleted_at' => now()]);
        $this->info("Marked {$cartsCount} cart entries with zero quantity as deleted");

        // Step 3: Specifically fix the H&M product (product_id = 1)
        $h_mCartItemsCount = CartItem::where('product_id', 1)->where('quantity', 0)->forceDelete();
        $this->info("Removed {$h_mCartItemsCount} H&M product cart items with zero quantity");

        $h_mCartsCount = Cart::where('product_id', 1)->where('quantity', 0)->update(['deleted_at' => now()]);
        $this->info("Marked {$h_mCartsCount} H&M product cart entries with zero quantity as deleted");

        // Step 4: Fix any specific problematic entries that might still be lingering
        DB::statement("UPDATE cart_items SET deleted_at = NOW() WHERE quantity = 0 AND deleted_at IS NULL");
        DB::statement("UPDATE carts SET deleted_at = NOW() WHERE quantity = 0 AND deleted_at IS NULL");
        $this->info("Fixed any remaining problematic entries with direct SQL statements");

        $this->info('Zero quantity cart items fix completed successfully!');

        return Command::SUCCESS;
    }
}
