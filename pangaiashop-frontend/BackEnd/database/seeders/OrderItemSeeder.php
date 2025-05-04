<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::all();
        $products = Product::where('status', 'active')->get();

        foreach ($orders as $order) {
            // Add 1-5 items to each order
            $itemCount = fake()->numberBetween(1, 5);
            $orderProducts = $products->random(min($itemCount, $products->count()));
            $totalAmount = 0;

            foreach ($orderProducts as $product) {
                $quantity = fake()->numberBetween(1, 3);
                $variant = null;

                // 40% chance of having a variant if product has variants
                if (fake()->boolean(40)) {
                    $variant = ProductVariant::where('product_id', $product->id)
                        ->inRandomOrder()
                        ->first();
                }

                $price = $product->price + ($variant ? $variant->price_adjustment : 0);
                $taxRate = 0.0825; // 8.25% sales tax
                $taxAmount = $price * $quantity * $taxRate;
                $discountAmount = fake()->boolean(30) ? $price * $quantity * fake()->randomFloat(2, 0.05, 0.20) : 0;

                OrderItem::create([
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
                ]);

                $totalAmount += ($price * $quantity) + $taxAmount - $discountAmount;
            }

            // Update order total
            $order->update([
                'total_amount' => $totalAmount
            ]);
        }
    }
}
