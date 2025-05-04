<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use App\Models\Category;
use App\Models\Admin;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first admin for created_by field
        $admin = Admin::first()?->id ?? 1;
        
        // Create a welcome promo code
        PromoCode::factory()->create([
            'code' => 'WELCOME10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_order_amount' => 50,
            'max_uses' => null,
            'target_audience' => [
                'segment' => 'new_customers',
                'min_orders' => null,
                'countries' => null
            ],
            'valid_from' => now(),
            'valid_until' => now()->addMonths(12),
            'is_active' => true,
            'created_by' => $admin
        ]);

        // Create some active percentage-based promos
        PromoCode::factory()->count(3)->create([
            'discount_type' => 'percentage',
            'discount_value' => fn() => fake()->numberBetween(5, 25),
            'min_order_amount' => fn() => fake()->randomElement([100, 150, 200, 250]),
            'valid_from' => fn() => now(),
            'valid_until' => fn() => now()->addDays(fake()->numberBetween(7, 30)),
            'is_active' => true,
            'created_by' => $admin
        ]);

        // Create some active fixed-amount promos
        PromoCode::factory()->count(2)->create([
            'discount_type' => 'fixed',
            'discount_value' => fn() => fake()->randomElement([10, 15, 20, 25, 30]),
            'min_order_amount' => fn() => fake()->randomElement([75, 100, 150]),
            'valid_from' => fn() => now(),
            'valid_until' => fn() => now()->addDays(fake()->numberBetween(7, 30)),
            'is_active' => true,
            'created_by' => $admin
        ]);

        // Create category-specific promos
        $categories = Category::where('parent_category_id', '!=', null)->get();
        if ($categories->isNotEmpty()) {
            foreach ($categories->random(min(3, $categories->count())) as $category) {
                PromoCode::factory()->create([
                    'code' => strtoupper(substr($category->name, 0, 3) . fake()->numberBetween(10, 30)),
                    'discount_type' => 'percentage',
                    'discount_value' => fake()->numberBetween(10, 20),
                    'min_order_amount' => fake()->randomElement([50, 75, 100]),
                    'valid_from' => now(),
                    'valid_until' => now()->addDays(14),
                    'is_active' => true,
                    'created_by' => $admin,
                    'target_audience' => [
                        'segment' => 'all',
                        'category_id' => $category->id
                    ]
                ]);
            }
        }

        // Create some expired promos
        PromoCode::factory()->count(3)->create([
            'valid_from' => fn() => fake()->dateTimeBetween('-3 months', '-2 months'),
            'valid_until' => fn() => fake()->dateTimeBetween('-1 month', '-1 day'),
            'is_active' => false,
            'created_by' => $admin
        ]);

        // Create some upcoming promos
        PromoCode::factory()->count(2)->create([
            'valid_from' => fn() => fake()->dateTimeBetween('+1 week', '+2 weeks'),
            'valid_until' => fn() => fake()->dateTimeBetween('+3 weeks', '+1 month'),
            'is_active' => true,
            'created_by' => $admin
        ]);

        // Create some fully used promos (at max_uses)
        PromoCode::factory()->count(2)->create([
            'max_uses' => function() { 
                return fake()->numberBetween(50, 100); 
            },
            'is_active' => false,
            'valid_from' => fn() => fake()->dateTimeBetween('-1 month', '-1 week'),
            'valid_until' => fn() => now()->addDays(7),
            'created_by' => $admin
        ]);
    }
}
