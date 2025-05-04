<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Database\Seeder;

class ShipmentSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin for created_by and updated_by fields
        $admin = Admin::first();
        
        // Get orders that need shipments (shipped or delivered status)
        $shippableOrders = Order::whereIn('status', ['shipped', 'delivered'])->get();

        foreach ($shippableOrders as $order) {
            $isDelivered = $order->status === 'delivered';
            $shippedAt = $order->order_date->addDays(fake()->numberBetween(1, 3));
            $deliveredAt = $isDelivered ? $shippedAt->copy()->addDays(fake()->numberBetween(1, 5)) : null;
            
            Shipment::create([
                'order_id' => $order->id,
                'tracking_number' => fake()->uuid,
                'origin_country' => 'US',
                'destination_country' => 'US',
                'destination_region' => $order->shipping_state,
                'destination_zip' => $order->shipping_postal_code,
                'weight' => fake()->randomFloat(2, 0.1, 20),
                'shipping_zone' => fake()->randomElement(['domestic', 'international', 'regional']),
                'status' => $isDelivered ? 'delivered' : 'shipped',
                'actual_cost' => fake()->randomFloat(2, 5, 50),
                'shipping_method' => fake()->randomElement(['standard', 'express', 'priority', 'economy', 'overnight']),
                'service_level' => fake()->randomElement(['standard', 'express']),
                'base_cost' => fake()->randomFloat(2, 3, 10),
                'per_item_cost' => fake()->randomFloat(2, 0.5, 3),
                'per_weight_unit_cost' => fake()->randomFloat(2, 0.2, 2),
                'delivery_time_days' => fake()->numberBetween(1, 10),
                'shipped_at' => $shippedAt,
                'delivered_at' => $deliveredAt,
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
                'updated_at' => now()
            ]);
        }
    }
}
