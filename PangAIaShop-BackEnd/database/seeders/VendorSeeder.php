<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        // Create the main store vendor
        Vendor::factory()->create([
            'name' => 'PangAIa Official Store',
            'status' => 'active',
            'rating' => 5.0,
        ]);

        // Create some active vendors
        Vendor::factory()->count(8)->create([
            'status' => 'active',
            'rating' => fn() => fake()->randomFloat(1, 3.5, 5.0),
        ]);

        // Create some pending vendors
        Vendor::factory()->count(3)->create([
            'status' => 'pending',
            'rating' => null,
        ]);

        // Create some inactive vendors
        Vendor::factory()->count(2)->create([
            'status' => 'inactive',
            'rating' => fn() => fake()->randomFloat(1, 1.0, 3.0),
        ]);
    }
}
