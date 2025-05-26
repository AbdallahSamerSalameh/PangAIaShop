<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class PriceHistorySeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have admin users for the changed_by field
        $admins = Admin::all();
        
        if ($admins->isEmpty()) {
            $this->command->error('No admin users found. Please run AdminSeeder first.');
            return;
        }

        // Get all products
        $products = Product::all();
        
        if ($products->isEmpty()) {
            $this->command->error('No products found. Please run ProductSeeder first.');
            return;
        }

        $this->command->info('Creating price history records...');
        
        foreach ($products as $product) {
            // For each product, create 1-3 price history records
            $numberOfRecords = fake()->numberBetween(1, 3);
            
            // Starting price (will be our reference point)
            $currentPrice = max($product->price, 1.0); // Ensure we never have a zero price
            
            for ($i = 0; $i < $numberOfRecords; $i++) {
                // Generate a new price based on the current price
                // Either increase or decrease by 5-15%
                $isIncrease = fake()->boolean(50);
                $changePercentage = fake()->randomFloat(2, 5, 15) / 100;
                
                $previousPrice = $currentPrice;
                
                if ($isIncrease) {
                    $newPrice = $currentPrice * (1 + $changePercentage);
                } else {
                    $newPrice = $currentPrice * (1 - $changePercentage);
                }
                
                // Ensure price is at least $1
                $newPrice = max(round($newPrice, 2), 1.0);
                
                // Create the price history record
                PriceHistory::create([
                    'product_id' => $product->id,
                    'previous_price' => $previousPrice, // Correct field name as per migration
                    'new_price' => $newPrice,
                    'changed_by' => $admins->random()->id,
                    'reason' => $this->getRandomReason($isIncrease),
                ]);
                
                // Update our current price for the next iteration
                $currentPrice = $newPrice;
            }
            
            // Create price history for product variants if any
            $variants = ProductVariant::where('product_id', $product->id)->get();
            
            foreach ($variants as $variant) {
                // Only create 1 record per variant for simplicity
                $previousPrice = max($variant->price, 1.0);
                
                // Similar logic as above
                $isIncrease = fake()->boolean(50);
                $changePercentage = fake()->randomFloat(2, 5, 15) / 100;
                
                if ($isIncrease) {
                    $newPrice = $previousPrice * (1 + $changePercentage);
                } else {
                    $newPrice = $previousPrice * (1 - $changePercentage);
                }
                
                $newPrice = max(round($newPrice, 2), 1.0);
                
                PriceHistory::create([
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'previous_price' => $previousPrice,
                    'new_price' => $newPrice,
                    'changed_by' => $admins->random()->id,
                    'reason' => $this->getRandomReason($isIncrease),
                ]);
            }
        }
        
        $this->command->info('Price history records created successfully!');
    }
    
    private function getRandomReason(bool $isIncrease): string
    {
        if ($isIncrease) {
            return fake()->randomElement([
                'cost_increase',
                'competitive_adjustment',
                'seasonal_pricing',
                'supplier_price_change'
            ]);
        } else {
            return fake()->randomElement([
                'promotional_offer',
                'clearance',
                'seasonal_sale',
                'black_friday_sale',
                'holiday_sale',
                'competitive_pricing'
            ]);
        }
    }
}
