<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test user with known credentials
        User::factory()->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password_hash' => Hash::make('password123'),
            'phone_number' => '+1234567890',
            'is_verified' => true,
            'account_status' => 'active'
        ]);

        // Create some verified users
        User::factory()->count(10)->create([
            'is_verified' => true,
            'account_status' => 'active'
        ]);

        // Create some unverified users
        User::factory()->count(5)->create([
            'is_verified' => false,
            'account_status' => 'active'
        ]);

        // Create some suspended users
        User::factory()->count(3)->create([
            'is_verified' => true,
            'account_status' => 'suspended'
        ]);

        // Create some deactivated users
        User::factory()->count(2)->create([
            'is_verified' => true,
            'account_status' => 'deactivated'
        ]);
    }
}
