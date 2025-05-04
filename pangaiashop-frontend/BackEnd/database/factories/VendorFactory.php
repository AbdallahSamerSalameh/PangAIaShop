<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    public function definition(): array
    {
        $admin = Admin::inRandomOrder()->first()?->id;
        
        // Generate company and contact information
        $companyName = fake()->company();
        $contactName = fake()->name();
        
        // Payment terms options
        $paymentTerms = fake()->randomElement(['net_30', 'net_60', 'net_15', 'immediate']);
        
        return [
            'name' => $companyName,
            'contact_name' => $contactName,
            'contact_email' => fake()->companyEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'address' => json_encode([
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'country' => fake()->country(),
            ]),
            'website' => fake()->url(),
            'tax_id' => fake()->bothify('??########'),
            'managed_by' => $admin,
            'payment_terms' => $paymentTerms,
            'status' => fake()->randomElement(['active', 'inactive', 'pending']),
            'rating' => fake()->randomFloat(1, 3.0, 5.0)
        ];
    }

    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active'
            ];
        });
    }

    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'inactive'
            ];
        });
    }
}
