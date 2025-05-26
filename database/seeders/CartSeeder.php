<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        // Get active users using account_status instead of status
        $users = User::where('account_status', 'active')->get();
        
        // If no active users found, get all users as fallback
        if ($users->isEmpty()) {
            $users = User::all();
        }
        
        // Get products that have inventory
        $activeProducts = Product::whereHas('inventory', function($query) {
            $query->where('quantity', '>', 0);
        })->get();
        
        // If no products with inventory found, get some products as fallback
        if ($activeProducts->isEmpty()) {
            $activeProducts = Product::limit(10)->get();
        }

        // Create carts for some users (70% of active users)
        $usersWithCart = $users->random(min((int)($users->count() * 0.7), $users->count()));

        foreach ($usersWithCart as $user) {
            // Add 1-5 products to each user's cart
            $cartItemCount = fake()->numberBetween(1, 5);
            $cartProducts = $activeProducts->random(min($cartItemCount, $activeProducts->count()));

            foreach ($cartProducts as $product) {
                Cart::factory()->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'variant_id' => null, // Can be updated if variants are needed
                    'quantity' => fake()->numberBetween(1, 3),
                    'carts_expiry' => fake()->dateTimeBetween('now', '+7 days')
                ]);
            }
        }

        // Only create expired cart items if we have enough users
        if ($users->count() >= 3) {
            // Create some expired cart items
            foreach ($users->random(min(3, $users->count())) as $user) {
                Cart::factory()->create([
                    'user_id' => $user->id,
                    'product_id' => $activeProducts->random()->id,
                    'quantity' => fake()->numberBetween(1, 3),
                    'carts_expiry' => fake()->dateTimeBetween('-30 days', '-1 day')
                ]);
            }
        }
    }
}
