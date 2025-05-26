<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromoCodeFactory extends Factory
{
    public function definition(): array
    {
        $admin = Admin::inRandomOrder()->first()?->id;
        
        // Determine discount type
        $discountType = fake()->randomElement(['percentage', 'fixed', 'free_shipping']);
        
        // Generate appropriate discount value based on type
        $discountValue = match($discountType) {
            'percentage' => fake()->numberBetween(5, 50),
            'fixed' => fake()->randomFloat(2, 5, 100),
            'free_shipping' => 0.00
        };
        
        // Generate a unique promo code
        $prefix = match($discountType) {
            'percentage' => fake()->randomElement(['SAVE', 'DISC', 'OFF']),
            'fixed' => fake()->randomElement(['FLAT', 'DEAL', 'SAVE']),
            'free_shipping' => fake()->randomElement(['SHIP', 'FREESHIP', 'SHIPFREE']),
        };
        $code = $prefix . strtoupper(fake()->regexify('[A-Z0-9]{4,6}'));
        
        // Set valid dates
        $validFrom = fake()->dateTimeBetween('-1 month', '+1 week');
        $validUntil = fake()->dateTimeBetween('+1 week', '+3 months');
        
        // Target audience - JSON data for specific customer segments
        $targetAudience = fake()->boolean(70) ? [
            'segment' => fake()->randomElement(['new_customers', 'returning_customers', 'vip', 'all']),
            'min_orders' => fake()->optional(0.3)->numberBetween(1, 10),
            'countries' => fake()->optional(0.3)->randomElements(['US', 'CA', 'UK', 'AU', 'DE', 'FR'], fake()->numberBetween(1, 3)),
        ] : null;
        
        return [
            'code' => $code,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'min_order_amount' => fake()->optional(0.7)->randomFloat(2, 20, 200),
            'max_uses' => fake()->optional(0.8)->numberBetween(10, 1000),
            'target_audience' => $targetAudience,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'is_active' => true,
            'created_by' => $admin,
            'created_at' => fake()->dateTimeBetween('-6 months', '-1 day')
        ];
    }
}
