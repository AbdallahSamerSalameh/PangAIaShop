<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
{
    public function run(): void
    {
        // Get active users - fixed to use account_status instead of status
        $users = User::where('account_status', 'active')->get();

        // If no active users are found, get all users instead
        if ($users->isEmpty()) {
            $users = User::all();
        }

        foreach ($users as $user) {
            // Create default wishlist for each user
            Wishlist::factory()->create([
                'user_id' => $user->id,
                'name' => 'My Wishlist',
                'wishlist_privacy' => 'private'
            ]);

            // 30% chance of having additional wishlists
            if (fake()->boolean(30)) {
                // Create 1-3 additional wishlists
                $additionalCount = fake()->numberBetween(1, 3);
                
                for ($i = 0; $i < $additionalCount; $i++) {
                    Wishlist::factory()->create([
                        'user_id' => $user->id,
                        'name' => fake()->randomElement([
                            'Birthday Wishlist',
                            'Gift Ideas',
                            'Shopping List',
                            'For Later',
                            'Favorites',
                            'Holiday Wishlist'
                        ]),
                        'wishlist_privacy' => fake()->randomElement(['private', 'public', 'shared'])
                    ]);
                }
            }
        }

        // Create some empty wishlists (10% of total)
        $totalWishlists = (int) ($users->count() * 1.3); // Approximate total wishlists created
        $emptyWishlistCount = (int) ($totalWishlists * 0.1);
        
        Wishlist::factory()->count($emptyWishlistCount)->create([
            'user_id' => fn() => $users->random()->id,
            'wishlist_privacy' => fn() => fake()->randomElement(['private', 'public', 'shared'])
        ]);
    }
}
