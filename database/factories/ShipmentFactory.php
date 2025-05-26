<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShipmentFactory extends Factory
{
    public function definition(): array
    {
        $order = Order::inRandomOrder()->first() ?? Order::factory()->create();
        $admin = Admin::inRandomOrder()->first()?->id;
        
        // Shipping methods
        $shippingMethods = ['standard', 'express', 'priority', 'economy', 'overnight'];
        $serviceLevels = ['standard', 'express'];
        
        // Get shipping addresses from order
        $destinationCountry = $order->shipping_country;
        $destinationRegion = $order->shipping_state;
        $destinationZip = $order->shipping_postal_code;
        
        // Calculate costs
        $weight = fake()->randomFloat(2, 0.5, 15.0);
        $baseCost = fake()->randomFloat(2, 5.0, 15.0);
        $perItemCost = fake()->randomFloat(2, 1.0, 3.0);
        $perWeightUnitCost = fake()->randomFloat(2, 0.5, 2.0);
        
        // Calculate shipping dates
        $shippedAt = fake()->dateTimeBetween($order->order_date, '+3 days');
        $deliveredAt = null;
        
        // Set status based on shipped date
        $status = 'processing';
        if ($shippedAt <= now()) {
            $status = fake()->randomElement(['shipped', 'delivered']);
            if ($status === 'delivered') {
                $deliveredAt = fake()->dateTimeBetween($shippedAt, '+10 days');
            }
        }
        
        // Calculate tracking number based on carrier
        $trackingNumber = strtoupper(fake()->bothify('???#########??'));
        
        return [
            'order_id' => $order->id,
            'tracking_number' => $trackingNumber,
            'origin_country' => 'US',
            'destination_country' => $destinationCountry,
            'destination_region' => $destinationRegion,
            'destination_zip' => $destinationZip,
            'weight' => $weight,
            'shipping_zone' => fake()->randomElement(['domestic', 'international', 'local']),
            'status' => $status,
            'actual_cost' => $baseCost + ($perItemCost * 2) + ($perWeightUnitCost * $weight),
            'shipping_method' => fake()->randomElement($shippingMethods),
            'service_level' => fake()->randomElement($serviceLevels),
            'base_cost' => $baseCost,
            'per_item_cost' => $perItemCost,
            'per_weight_unit_cost' => $perWeightUnitCost,
            'delivery_time_days' => fake()->numberBetween(2, 10),
            'shipped_at' => $shippedAt,
            'delivered_at' => $deliveredAt,
            'created_by' => $admin,
            'updated_by' => $admin,
            'updated_at' => now()
        ];
    }
}
