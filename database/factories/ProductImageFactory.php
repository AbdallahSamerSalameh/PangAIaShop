<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $imageType = fake()->randomElement(['thumbnail', 'gallery', '360-view']);
        
        return [
            'product_id' => $product->id,
            'image_url' => fake()->imageUrl(800, 800, 'products'),
            'alt_text' => "{$product->name} - {$imageType} view",
            'image_type' => $imageType,
            'is_primary' => $imageType === 'gallery', // gallery images can be primary
            'uploaded_by' => 1, // Default to first admin
            'uploaded_at' => now()
        ];
    }

    public function primary(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'image_type' => 'gallery',
                'is_primary' => true,
            ];
        });
    }

    public function thumbnail(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'image_type' => 'thumbnail',
                'is_primary' => false,
            ];
        });
    }
}
