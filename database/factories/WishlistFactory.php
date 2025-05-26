<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wishlist>
 */
class WishlistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'wishlist_privacy' => fake()->randomElement(['private', 'public', 'shared']),
        ];
    }

    /**
     * Indicate that the wishlist is private
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'wishlist_privacy' => 'private'
        ]);
    }

    /**
     * Indicate that the wishlist is public
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'wishlist_privacy' => 'public'
        ]);
    }
}
