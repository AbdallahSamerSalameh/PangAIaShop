<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        
        // Get appropriate category based on product type
        $category = $this->getMatchingCategory($product->category_type, $product->product_type);
        
        return [
            'product_id' => $product->id,
            'category_id' => $category->id,
            'is_primary' => fake()->boolean(70), // 70% chance of being primary category
            'position' => fake()->numberBetween(1, 10),
            'created_at' => $product->created_at,
            'updated_at' => fake()->dateTimeBetween($product->created_at, 'now'),
        ];
    }

    protected function getMatchingCategory($categoryType, $productType): Category
    {
        // First try to find a leaf category that matches the product type
        $category = Category::where('name', $productType)->first();
        
        if (!$category) {
            // If no exact match, try to find a subcategory
            $category = Category::whereHas('parent', function($query) use ($categoryType) {
                $query->where('name', $categoryType);
            })->inRandomOrder()->first();
        }
        
        if (!$category) {
            // If still no match, use the main category
            $category = Category::where('name', $categoryType)
                ->whereNull('parent_category_id')
                ->first();
        }
        
        if (!$category) {
            // As a last resort, get any active category
            $category = Category::where('status', 'active')
                ->inRandomOrder()
                ->first();
        }
        
        return $category ?? Category::factory()->create();
    }

    public function primary(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_primary' => true,
                'position' => 1,
            ];
        });
    }

    public function secondary(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_primary' => false,
                'position' => fake()->numberBetween(2, 10),
            ];
        });
    }
}
