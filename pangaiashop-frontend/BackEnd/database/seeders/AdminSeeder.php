<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        Admin::factory()->create([
            'username' => 'superadmin',
            'email' => 'superadmin@pangaia.com',
            'password_hash' => Hash::make('SuperAdmin@123'),
            'role' => 'Super Admin',
            'phone_number' => '+1234567890',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create 5 regular admins
        Admin::factory()
            ->count(5)
            ->regularAdmin()
            ->create();
    }
}
