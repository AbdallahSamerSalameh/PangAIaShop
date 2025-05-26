<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class FixCartCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix zero quantity items in carts and remove direct product references';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cart cleanup...');

        // Step 1: Delete cart items with zero quantity
        $this->info('Step 1: Cleaning up zero quantity items in cart_items table...');
        $deletedCartItems = CartItem::where('quantity', 0)->forceDelete();
        $this->info("Deleted {$deletedCartItems} cart items with zero quantity");

        // Step 2: Soft delete carts with zero quantity
        $this->info('Step 2: Cleaning up zero quantity items in carts table...');
        $cartsUpdated = Cart::where('quantity', 0)->update(['deleted_at' => now()]);
        $this->info("Soft deleted {$cartsUpdated} carts with zero quantity");

        // Step 3: Fix H&M product issue specifically (product_id = 1)
        $this->info('Step 3: Fixing H&M hoodies (product_id = 1) issue...');
        $h_mCartItemsDeleted = CartItem::where('product_id', 1)->where('quantity', 0)->forceDelete();
        $this->info("Deleted {$h_mCartItemsDeleted} H&M product cart items");

        $h_mCartsUpdated = Cart::where('product_id', 1)->where('quantity', 0)->update(['deleted_at' => now()]);
        $this->info("Soft deleted {$h_mCartsUpdated} H&M product carts");

        // Step 4: Remove direct product references from carts
        $this->info('Step 4: Removing direct product references from carts...');
        $directRefUpdated = Cart::whereNotNull('product_id')->update([
            'product_id' => null,
            'quantity' => null
        ]);
        $this->info("Updated {$directRefUpdated} carts to remove direct product references");

        $this->info('Cart cleanup complete!');
    }
}
