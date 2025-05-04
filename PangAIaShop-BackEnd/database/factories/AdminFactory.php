<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $twoFactorVerified = fake()->boolean();
        
        return [
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password_hash' => Hash::make('password'), // Default password for testing
            'role' => fake()->randomElement(['Admin', 'Super Admin']),
            'avatar_url' => fake()->imageUrl(200, 200, 'people'),
            'phone_number' => fake()->phoneNumber(),
            'last_password_change' => fake()->dateTimeBetween('-1 year', 'now'),
            'failed_login_count' => fake()->numberBetween(0, 10),
            'last_login' => fake()->dateTimeThisYear(),
            'is_active' => fake()->boolean(80), // 80% chance to be active
            'two_factor_verified' => $twoFactorVerified,
            'two_factor_method' => fake()->randomElement(['app', 'sms', 'email']),
            'backup_codes' => $twoFactorVerified ? array_map(fn () => fake()->numerify('##########'), range(1, 5)) : null,
            'two_factor_enabled_at' => $twoFactorVerified ? fake()->dateTimeBetween('-6 months', 'now') : null,
        ];
    }

    /**
     * Indicate that the admin is a super admin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Super Admin',
            'two_factor_verified' => true,
            'two_factor_method' => fake()->randomElement(['app', 'sms', 'email']),
            'backup_codes' => array_map(fn () => fake()->numerify('##########'), range(1, 5)),
            'two_factor_enabled_at' => fake()->dateTimeBetween('-6 months', '-1 day'),
            'last_password_change' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the admin is a regular admin.
     */
    public function regularAdmin(): static
    {
        $twoFactorVerified = fake()->boolean(60); // 60% chance to have 2FA verified
        
        return $this->state(fn (array $attributes) => [
            'role' => 'Admin',
            'two_factor_verified' => $twoFactorVerified,
            'two_factor_method' => fake()->randomElement(['app', 'sms', 'email']),
            'backup_codes' => $twoFactorVerified ? array_map(fn () => fake()->numerify('##########'), range(1, 5)) : null,
            'two_factor_enabled_at' => $twoFactorVerified ? fake()->dateTimeBetween('-6 months', '-1 day') : null,
            'last_password_change' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }
}
