<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WishlistItem>
 */
class WishlistItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wishlist_id' => Wishlist::factory(),
            'product_id' => Product::factory(),
            'variant_id' => fake()->boolean(70) ? ProductVariant::factory() : null,
            'added_at' => fake()->dateTimeThisYear(),
        ];
    }

    /**
     * Configure the model factory to create a wishlist item without a variant
     */
    public function withoutVariant(): static
    {
        return $this->state(fn (array $attributes) => [
            'variant_id' => null
        ]);
    }
}
