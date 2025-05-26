<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Database\Seeder;

class WishlistItemSeeder extends Seeder
{
    public function run(): void
    {
        // Get all non-empty wishlists
        $wishlists = Wishlist::has('user')->get();
        // Using 'active' which is a valid enum value in the products table
        $products = Product::where('status', 'active')->get();

        foreach ($wishlists as $wishlist) {
            // Add 2-8 items to each wishlist
            $itemCount = fake()->numberBetween(2, 8);
            $wishlistProducts = $products->random(min($itemCount, $products->count()));

            foreach ($wishlistProducts as $product) {
                // 30% chance of adding a specific variant
                $variant = null;
                if (fake()->boolean(30)) {
                    // Simply get a random variant without filtering by is_active
                    $variant = ProductVariant::where('product_id', $product->id)
                        ->inRandomOrder()
                        ->first();
                }

                WishlistItem::factory()->create([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'added_at' => fake()->dateTimeBetween('-3 months', 'now'),
                    'priority' => fake()->randomElement(['high', 'medium', 'low']),
                    'notes' => fake()->boolean(20) ? fake()->sentence : null,
                ]);
            }
        }

        // Create some items in public wishlists for discontinued products
        $publicWishlists = Wishlist::where('wishlist_privacy', 'public')->get();
        $discontinuedProducts = Product::where('status', 'discontinued')->get();

        // If no discontinued products are found, use draft products instead
        if ($discontinuedProducts->isEmpty()) {
            $discontinuedProducts = Product::where('status', 'draft')->get();
        }

        // If still no products found, just use some active products
        if ($discontinuedProducts->isEmpty()) {
            $discontinuedProducts = Product::where('status', 'active')->limit(3)->get();
        }

        foreach ($publicWishlists->random(min((int)($publicWishlists->count() * 0.3), $publicWishlists->count())) as $wishlist) {
            if ($discontinuedProducts->isNotEmpty()) {
                WishlistItem::factory()->create([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $discontinuedProducts->random()->id,
                    'variant_id' => null,
                    'added_at' => fake()->dateTimeBetween('-1 month', 'now'),
                    'priority' => 'high',
                    'notes' => 'Waiting for restock'
                ]);
            }
        }
    }
}
