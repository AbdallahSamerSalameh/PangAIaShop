<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceHistoryFactory extends Factory
{
    protected $priceChangeReasons = [
        'regular_price_change' => 25,
        'sale' => 20,
        'promotional_offer' => 15,
        'clearance' => 10,
        'seasonal_pricing' => 10,
        'competitive_adjustment' => 8,
        'cost_increase' => 7,
        'bulk_discount_added' => 5
    ];

    protected $seasonalPatterns = [
        '01' => ['increase' => 0, 'sale' => 70],    // January (New Year sales)
        '02' => ['increase' => 5, 'sale' => 20],    // February
        '03' => ['increase' => 10, 'sale' => 15],   // March
        '04' => ['increase' => 5, 'sale' => 25],    // April (Spring sales)
        '05' => ['increase' => 0, 'sale' => 20],    // May
        '06' => ['increase' => 0, 'sale' => 30],    // June (Summer sales)
        '07' => ['increase' => 0, 'sale' => 60],    // July (Summer clearance)
        '08' => ['increase' => 15, 'sale' => 20],   // August (Back to school)
        '09' => ['increase' => 10, 'sale' => 15],   // September
        '10' => ['increase' => 5, 'sale' => 20],    // October
        '11' => ['increase' => 0, 'sale' => 80],    // November (Black Friday)
        '12' => ['increase' => 0, 'sale' => 70],    // December (Holiday sales)
    ];

    public function definition(): array
    {
        // Get a random product
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        
        // Maybe get a variant
        $variant = fake()->boolean(70) ? 
            ProductVariant::where('product_id', $product->id)->inRandomOrder()->first() : 
            null;
        
        // Get admin for changed_by
        $admin = Admin::inRandomOrder()->first();
        
        // Base and new prices
        $previousPrice = max($variant ? $variant->price : $product->price, 1.0); // Ensure price is never zero
        $timestamp = fake()->dateTimeBetween('-1 year', 'now');
        $month = $timestamp->format('m');
        
        // Get seasonal patterns for the month
        $pattern = $this->seasonalPatterns[$month];
        
        // Determine if this is a price increase or decrease
        $isIncrease = fake()->boolean($pattern['increase']);
        $isSale = !$isIncrease && fake()->boolean($pattern['sale']);
        
        // Calculate new price
        if ($isIncrease) {
            // Price increases are usually smaller
            $newPrice = $previousPrice * (1 + fake()->randomFloat(2, 0.02, 0.15));
        } elseif ($isSale) {
            // Sales have bigger discounts
            $newPrice = $previousPrice * (1 - fake()->randomFloat(2, 0.1, 0.5));
        } else {
            // Regular price decreases
            $newPrice = $previousPrice * (1 - fake()->randomFloat(2, 0.05, 0.2));
        }
        
        // Ensure new price is at least $1
        $newPrice = max(round($newPrice, 2), 1.0);
        
        // Determine reason for price change
        $reason = $this->getPriceChangeReason($isIncrease, $isSale, $month);
        
        return [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'previous_price' => $previousPrice,
            'new_price' => $newPrice,
            'updated_at' => $timestamp,
            'changed_by' => $admin->id,
            'reason' => $reason,
        ];
    }

    protected function getPriceChangeReason($isIncrease, $isSale, $month): string
    {
        if ($isSale) {
            if ($month == '11') return 'black_friday_sale';
            if ($month == '12') return 'holiday_sale';
            if ($month == '01') return 'new_year_sale';
            if ($month == '07') return 'summer_clearance';
            return 'promotional_offer';
        }

        if ($isIncrease) {
            return fake()->randomElement([
                'cost_increase',
                'competitive_adjustment',
                'seasonal_pricing'
            ]);
        }

        return $this->getWeightedRandom($this->priceChangeReasons);
    }

    protected function getWeightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $random = fake()->numberBetween(1, $total);
        $sum = 0;

        foreach ($weights as $item => $weight) {
            $sum += $weight;
            if ($random <= $sum) {
                return $item;
            }
        }

        return array_key_first($weights);
    }
}
