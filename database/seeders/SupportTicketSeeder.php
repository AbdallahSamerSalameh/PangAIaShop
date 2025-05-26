<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupportTicketSeeder extends Seeder
{
    public function run(): void
    {
        // Fix: Use account_status instead of status
        $users = User::where('account_status', 'active')->get();
        
        // Fallback to all users if none are active
        if ($users->isEmpty()) {
            $users = User::all();
        }
        
        $admins = Admin::where('role', 'Admin')->get();
        
        // Fallback to all admins if none with 'Admin' role are found
        if ($admins->isEmpty()) {
            $admins = Admin::all();
        }

        // Create some open tickets
        SupportTicket::factory()->count(5)->create([
            'user_id' => fn() => $users->random()->id,
            'status' => 'open',
            'priority' => fn() => fake()->randomElement(['low', 'medium', 'high']),
            'assigned_to' => null
        ]);

        // Create in-progress tickets
        SupportTicket::factory()->count(8)->create([
            'user_id' => fn() => $users->random()->id,
            'status' => 'in_progress',
            'priority' => fn() => fake()->randomElement(['medium', 'high']),
            'assigned_to' => fn() => $admins->random()->id
        ]);

        // Create resolved tickets with resolution time
        SupportTicket::factory()->count(15)->create([
            'user_id' => fn() => $users->random()->id,
            'status' => 'resolved',
            'priority' => fn() => fake()->randomElement(['low', 'medium', 'high']),
            'assigned_to' => fn() => $admins->random()->id,
            'resolution_time' => fn() => fake()->numberBetween(3600, 172800) // 1 hour to 2 days in seconds
        ]);

        // Create some urgent tickets
        SupportTicket::factory()->count(3)->create([
            'user_id' => fn() => $users->random()->id,
            'status' => 'open',
            'priority' => 'urgent',
            'assigned_to' => null
        ]);

        // Create some closed tickets with resolution time
        SupportTicket::factory()->count(10)->create([
            'user_id' => fn() => $users->random()->id,
            'status' => 'closed',
            'priority' => fn() => fake()->randomElement(['low', 'medium']),
            'assigned_to' => fn() => $admins->random()->id,
            'resolution_time' => fn() => fake()->numberBetween(3600, 259200) // 1 hour to 3 days in seconds
        ]);
    }
}
