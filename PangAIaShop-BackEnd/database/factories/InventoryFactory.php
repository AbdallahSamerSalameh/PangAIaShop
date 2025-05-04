<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $variant = fake()->boolean(40) ? 
            ProductVariant::where('product_id', $product->id)->inRandomOrder()->first() : 
            null;
        $admin = Admin::inRandomOrder()->first()?->id;

        // Calculate inventory amounts
        $quantity = fake()->numberBetween(10, 100);
        $reservedQuantity = fake()->numberBetween(0, min(10, $quantity));
        $lowStockThreshold = fake()->numberBetween(5, 20);
        
        // Generate warehouse information
        $warehouseLocations = ['East Warehouse', 'West Warehouse', 'North Warehouse', 'Central Distribution'];
        $location = fake()->randomElement($warehouseLocations);
        
        // Last restocked date
        $lastRestocked = fake()->dateTimeBetween('-3 months', 'now');
        
        return [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $quantity,
            'reserved_quantity' => $reservedQuantity,
            'location' => $location,
            'last_restocked' => $lastRestocked,
            'low_stock_threshold' => $lowStockThreshold,
            'updated_by' => $admin,
            'updated_at' => fake()->dateTimeBetween($lastRestocked, 'now')
        ];
    }

    public function lowStock(): static
    {
        return $this->state(function (array $attributes) {
            $lowQuantity = fake()->numberBetween(1, 5);
            return [
                'quantity' => $lowQuantity,
                'reserved_quantity' => min($lowQuantity - 1, 1)
            ];
        });
    }

    public function outOfStock(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'quantity' => 0,
                'reserved_quantity' => 0
            ];
        });
    }
}
