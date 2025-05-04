<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $order = Order::inRandomOrder()->first() ?? Order::factory()->create();
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $variant = fake()->boolean(40) ? 
            ProductVariant::where('product_id', $product->id)->inRandomOrder()->first() : 
            null;
        
        // Determine quantity based on product price
        $quantity = $product->price > 100 ? 
            fake()->numberBetween(1, 2) : 
            fake()->numberBetween(1, 5);
        
        $price = $product->price;
        if ($variant && $variant->price_adjustment) {
            $price += $variant->price_adjustment;
        }
        
        // Tax calculation
        $taxRate = 0.15; // 15% tax rate
        $taxAmount = $price * $quantity * $taxRate;
        
        // Small discount for some items
        $discountAmount = fake()->boolean(25) ? round($price * $quantity * 0.1, 2) : 0; // 10% discount sometimes
        
        return [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $quantity,
            'price' => $price,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'tax_name' => 'Sales Tax',
            'tax_region' => fake()->state(),
            'discount_amount' => $discountAmount
        ];
    }
}
