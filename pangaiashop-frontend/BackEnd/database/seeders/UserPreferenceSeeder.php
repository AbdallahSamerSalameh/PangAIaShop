<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;

class UserPreferenceSeeder extends Seeder
{
    public function run(): void
    {
        // Get all active users using the correct field name
        $users = User::where('account_status', 'active')->get();

        foreach ($users as $user) {
            // Use the factory directly without overriding fields that don't exist
            UserPreference::factory()->create([
                'user_id' => $user->id
            ]);
        }
    }
}
