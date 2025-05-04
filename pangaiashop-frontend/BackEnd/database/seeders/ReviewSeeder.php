<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Get active users using the correct account_status column
        $users = User::where('account_status', 'active')->get();
        
        // Fallback to all users if none are active
        if ($users->isEmpty()) {
            $users = User::all();
        }
        
        // Get active products
        $products = Product::where('status', 'active')->get();
        
        // Fallback to all products if none are active
        if ($products->isEmpty()) {
            $products = Product::all();
        }
        
        // Get admin for moderation
        $admin = Admin::first();

        // Since there's no is_featured column, let's pick some random products to be "featured"
        $featuredProducts = $products->random(min(5, $products->count()));
        
        foreach ($featuredProducts as $product) {
            // Create 5-10 reviews for each "featured" product
            $reviewCount = fake()->numberBetween(5, 10);
            for ($i = 0; $i < $reviewCount; $i++) {
                $title = "Great product, highly recommended!";
                $content = fake()->paragraph(3);
                $sentiment = fake()->randomFloat(2, 0.7, 1.0); // Positive sentiment
                
                Review::factory()->create([
                    'product_id' => $product->id,
                    'user_id' => $users->random()->id,
                    'rating' => fake()->numberBetween(3, 5), // Featured products tend to have better ratings
                    'comment' => $title . "\n\n" . $content,
                    'sentiment_score' => $sentiment,
                    'helpful_count' => fake()->numberBetween(5, 50),
                    'moderation_status' => 'approved',
                    'moderated_by' => $admin?->id,
                    'moderated_at' => fake()->dateTimeBetween('-2 months', 'now')
                ]);
            }
        }

        // Create 1-3 reviews for regular products
        foreach ($products->except($featuredProducts->pluck('id')->toArray()) as $product) {
            $reviewCount = fake()->numberBetween(1, 3);
            for ($i = 0; $i < $reviewCount; $i++) {
                $moderation_status = fake()->randomElement(['pending', 'approved', 'approved', 'approved']); // 75% chance of being approved
                $title = fake()->sentence();
                $content = fake()->paragraph(2);
                $rating = fake()->numberBetween(1, 5);
                $sentiment = $rating >= 4 ? 
                    fake()->randomFloat(2, 0.7, 1.0) : 
                    ($rating <= 2 ? 
                        fake()->randomFloat(2, 0.0, 0.3) : 
                        fake()->randomFloat(2, 0.4, 0.6));
                
                $review = Review::factory()->create([
                    'product_id' => $product->id,
                    'user_id' => $users->random()->id,
                    'rating' => $rating,
                    'comment' => $title . "\n\n" . $content,
                    'sentiment_score' => $sentiment,
                    'helpful_count' => fake()->numberBetween(0, 30),
                    'moderation_status' => $moderation_status
                ]);
                
                // Add moderation details if approved or rejected
                if ($moderation_status !== 'pending' && $admin) {
                    $review->moderated_by = $admin->id;
                    $review->moderated_at = fake()->dateTimeBetween('-1 month', 'now');
                    $review->save();
                }
            }
        }

        // Create some rejected reviews
        if (!$products->isEmpty() && !$users->isEmpty() && $admin) {
            for ($i = 0; $i < 3; $i++) {
                $title = "Not happy with this purchase";
                $content = fake()->paragraph(2);
                
                Review::factory()->create([
                    'product_id' => $products->random()->id,
                    'user_id' => $users->random()->id,
                    'rating' => fake()->numberBetween(1, 2),
                    'comment' => $title . "\n\n" . $content,
                    'sentiment_score' => fake()->randomFloat(2, 0.0, 0.3),
                    'helpful_count' => fake()->numberBetween(0, 10),
                    'moderation_status' => 'rejected',
                    'moderated_by' => $admin->id,
                    'moderated_at' => fake()->dateTimeBetween('-1 month', 'now')
                ]);
            }
        }
    }
}
