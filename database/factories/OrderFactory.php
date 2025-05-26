<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $admin = Admin::inRandomOrder()->first()?->id;
        
        // Generate realistic total
        $totalAmount = fake()->randomFloat(2, 20, 500);
        $discountAmount = fake()->randomFloat(2, 0, $totalAmount * 0.3);
        
        // Apply promo code if applicable
        $promoCode = fake()->boolean(30) ? PromoCode::inRandomOrder()->first()?->id : null;
        
        $createdAt = fake()->dateTimeBetween('-1 year', 'now');
        $expectedDelivery = fake()->dateTimeBetween('+1 day', '+14 days');
        
        return [
            'user_id' => $user->id,
            'shipping_street' => fake()->streetAddress(),
            'shipping_city' => fake()->city(),
            'shipping_state' => fake()->state(),
            'shipping_postal_code' => fake()->postcode(),
            'shipping_country' => fake()->countryCode(),
            'billing_street' => fake()->boolean(80) ? fake()->streetAddress() : $user->street,
            'billing_city' => fake()->boolean(80) ? fake()->city() : $user->city,
            'billing_state' => fake()->boolean(80) ? fake()->state() : $user->state,
            'billing_postal_code' => fake()->boolean(80) ? fake()->postcode() : $user->postal_code,
            'billing_country' => fake()->boolean(80) ? fake()->countryCode() : $user->country,
            'total_amount' => $totalAmount,
            'order_date' => $createdAt,
            'status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned']),
            'discount_amount' => $discountAmount,
            'promo_code_id' => $promoCode,
            'expected_delivery_date' => $expectedDelivery,
            'admin_notes' => fake()->boolean(30) ? fake()->paragraph() : null,
            'handled_by' => fake()->boolean(70) ? $admin : null,
        ];
    }
}
