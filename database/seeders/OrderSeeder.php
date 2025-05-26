<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('account_status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        $admins = Admin::all();
        $promoCodes = PromoCode::all();

        // Create orders in various states
        $this->createOrdersInState('pending', 5, $users, $products, $admins, $promoCodes);
        $this->createOrdersInState('processing', 8, $users, $products, $admins, $promoCodes);
        $this->createOrdersInState('shipped', 12, $users, $products, $admins, $promoCodes);
        $this->createOrdersInState('delivered', 15, $users, $products, $admins, $promoCodes);
        $this->createOrdersInState('cancelled', 3, $users, $products, $admins, $promoCodes);
        $this->createOrdersInState('returned', 2, $users, $products, $admins, $promoCodes);
    }

    private function createOrdersInState($status, $count, $users, $products, $admins, $promoCodes)
    {
        // Filter promo codes to only include active, non-expired ones
        $validPromoCodes = $promoCodes->filter(function($promoCode) {
            return $promoCode->is_active && 
                   $promoCode->valid_until > now() &&
                   $promoCode->valid_from <= now();
        });
        
        for ($i = 0; $i < $count; $i++) {
            try {
                $user = $users->random();
                $admin = $admins->random();
                $usePromoCode = fake()->boolean(30);
                $promoCode = $usePromoCode && $validPromoCodes->isNotEmpty() ? $validPromoCodes->random() : null;
                $discountAmount = $promoCode ? fake()->randomFloat(2, 5, 25) : 0;
                
                // Generate address information
                $shippingStreet = fake()->streetAddress;
                $shippingCity = fake()->city;
                $shippingState = fake()->state;
                $shippingPostalCode = fake()->postcode;
                $shippingCountry = 'US';
                
                // 80% chance billing address is same as shipping
                $useSameAddress = fake()->boolean(80);
                $billingStreet = $useSameAddress ? $shippingStreet : fake()->streetAddress;
                $billingCity = $useSameAddress ? $shippingCity : fake()->city;
                $billingState = $useSameAddress ? $shippingState : fake()->state;
                $billingPostalCode = $useSameAddress ? $shippingPostalCode : fake()->postcode;
                $billingCountry = $useSameAddress ? $shippingCountry : 'US';
                
                $orderDate = fake()->dateTimeBetween('-3 months', 'now');
                $expectedDeliveryDate = fake()->dateTimeBetween('+1 day', '+14 days');
                
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => 'ORD-' . strtoupper(fake()->bothify('??###??####')),
                    'shipping_street' => $shippingStreet,
                    'shipping_city' => $shippingCity,
                    'shipping_state' => $shippingState,
                    'shipping_postal_code' => $shippingPostalCode,
                    'shipping_country' => $shippingCountry,
                    'billing_street' => $billingStreet,
                    'billing_city' => $billingCity,
                    'billing_state' => $billingState,
                    'billing_postal_code' => $billingPostalCode,
                    'billing_country' => $billingCountry,
                    'total_amount' => 0, // Will be calculated from items
                    'order_date' => $orderDate,
                    'status' => $status,
                    'discount' => $discountAmount, // Using 'discount' for Order table
                    'promo_code_id' => $promoCode?->id,
                    'expected_delivery_date' => in_array($status, ['pending', 'processing', 'shipped']) ? $expectedDeliveryDate : null,
                    'admin_notes' => fake()->boolean(30) ? fake()->sentence : null,
                    'handled_by' => $admin->id
                ]);

                // Add 1-5 items to each order
                $orderItems = $products->random(fake()->numberBetween(1, 5));
                $totalAmount = 0;

                foreach ($orderItems as $product) {
                    $quantity = fake()->numberBetween(1, 3);
                    $price = $product->price;
                    $subtotal = $price * $quantity;
                    $totalAmount += $subtotal;

                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'tax_rate' => 0.0000,
                        'tax_amount' => 0.00,
                        'tax_name' => fake()->boolean(30) ? 'Sales Tax' : null,
                        'tax_region' => fake()->boolean(30) ? $shippingState : null,
                        'discount_amount' => 0.00 // Using 'discount_amount' for OrderItem table
                    ]);
                }

                // Update order total (add shipping cost)
                $shippingCost = fake()->randomElement([5, 15, 25]);
                $order->update([
                    'subtotal' => $totalAmount,
                    'shipping' => $shippingCost,
                    'total_amount' => $totalAmount + $shippingCost - $discountAmount
                ]);
            } catch (\Exception $e) {
                echo "Error creating order: " . $e->getMessage() . "\n";
            }
        }
    }
}
