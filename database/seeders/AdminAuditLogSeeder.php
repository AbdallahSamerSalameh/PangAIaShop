<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\AdminAuditLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class AdminAuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $admins = Admin::all();
        
        // Generate audit logs for product management
        $products = Product::all();
        foreach ($products->random(10) as $product) {
            AdminAuditLog::factory()->create([
                'admin_id' => $admins->random()->id,
                'action' => 'product_update',
                'resource' => 'product',
                'resource_id' => $product->id,
                'previous_data' => json_encode([
                    'price' => fake()->randomFloat(2, 10, 1000),
                    'stock' => fake()->numberBetween(0, 100)
                ]),
                'new_data' => json_encode([
                    'price' => fake()->randomFloat(2, 10, 1000),
                    'stock' => fake()->numberBetween(0, 100)
                ]),
                'ip_address' => fake()->ipv4,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => fake()->dateTimeBetween('-1 month', 'now')
            ]);
        }

        // Generate audit logs for order management
        $orders = Order::all();
        foreach ($orders->random(15) as $order) {
            AdminAuditLog::factory()->create([
                'admin_id' => $admins->random()->id,
                'action' => 'order_status_update',
                'resource' => 'order',
                'resource_id' => $order->id,
                'previous_data' => json_encode(['status' => 'pending']),
                'new_data' => json_encode(['status' => 'processing']),
                'ip_address' => fake()->ipv4,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => fake()->dateTimeBetween('-1 month', 'now')
            ]);
        }

        // Generate audit logs for user management
        $users = User::all();
        foreach ($users->random(5) as $user) {
            AdminAuditLog::factory()->create([
                'admin_id' => $admins->random()->id,
                'action' => 'user_status_update',
                'resource' => 'user',
                'resource_id' => $user->id,
                'previous_data' => json_encode(['status' => 'pending']),
                'new_data' => json_encode(['status' => 'active']),
                'ip_address' => fake()->ipv4,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => fake()->dateTimeBetween('-1 month', 'now')
            ]);
        }

        // Generate audit logs for vendor management
        $vendors = Vendor::all();
        foreach ($vendors->random(3) as $vendor) {
            AdminAuditLog::factory()->create([
                'admin_id' => $admins->random()->id,
                'action' => 'vendor_verification',
                'resource' => 'vendor',
                'resource_id' => $vendor->id,
                'previous_data' => json_encode(['status' => 'pending']),
                'new_data' => json_encode(['status' => 'verified']),
                'ip_address' => fake()->ipv4,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => fake()->dateTimeBetween('-1 month', 'now')
            ]);
        }

        // Generate security-related audit logs
        foreach ($admins as $admin) {
            // Login attempts
            AdminAuditLog::factory()->create([
                'admin_id' => $admin->id,
                'action' => 'login',
                'resource' => 'admin',
                'resource_id' => $admin->id,
                'previous_data' => null,
                'new_data' => null,
                'ip_address' => fake()->ipv4,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => fake()->dateTimeBetween('-1 week', 'now')
            ]);

            // Settings changes
            if (fake()->boolean(30)) {
                AdminAuditLog::factory()->create([
                    'admin_id' => $admin->id,
                    'action' => 'settings_update',
                    'resource' => 'admin',
                    'resource_id' => $admin->id,
                    'previous_data' => json_encode(['two_factor_enabled' => false]),
                    'new_data' => json_encode(['two_factor_enabled' => true]),
                    'ip_address' => fake()->ipv4,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'created_at' => fake()->dateTimeBetween('-1 month', 'now')
                ]);
            }
        }
    }
}
