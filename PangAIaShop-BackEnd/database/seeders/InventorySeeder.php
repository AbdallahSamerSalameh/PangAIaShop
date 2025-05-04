<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // Get all active products
        $activeProducts = Product::where('status', 'active')->get();
        
        // Create inventory for active products
        foreach ($activeProducts as $product) {
            Inventory::factory()->create([
                'product_id' => $product->id,
                'quantity' => fake()->numberBetween(10, 100),
                'reserved_quantity' => fake()->numberBetween(0, 5),
                'low_stock_threshold' => fake()->numberBetween(5, 15),
                'location' => fake()->randomElement(['East Warehouse', 'West Warehouse', 'North Warehouse', 'Central Warehouse'])
            ]);
            
            // Add inventory for variants if they exist
            $variants = ProductVariant::where('product_id', $product->id)->get();
            foreach ($variants as $variant) {
                Inventory::factory()->create([
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'quantity' => fake()->numberBetween(5, 50),
                    'reserved_quantity' => fake()->numberBetween(0, 3),
                    'low_stock_threshold' => fake()->numberBetween(5, 10),
                    'location' => fake()->randomElement(['East Warehouse', 'West Warehouse', 'North Warehouse', 'Central Warehouse'])
                ]);
            }
        }

        // Get discontinued products
        $discontinuedProducts = Product::where('status', 'discontinued')->get();
        
        // Create empty inventory for discontinued products
        foreach ($discontinuedProducts as $product) {
            Inventory::factory()->outOfStock()->create([
                'product_id' => $product->id,
                'low_stock_threshold' => fake()->numberBetween(5, 15)
            ]);
        }

        // Create some low stock inventory
        $lowStockProducts = Product::where('status', 'active')
            ->take(3)
            ->get();

        foreach ($lowStockProducts as $product) {
            Inventory::factory()->lowStock()->create([
                'product_id' => $product->id,
                'low_stock_threshold' => 10
            ]);
        }
    }
}
