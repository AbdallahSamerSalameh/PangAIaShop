<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserPreferenceFactory extends Factory
{
    public function definition(): array
    {
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        
        return [
            'user_id' => $user->id,
            'language' => fake()->randomElement(['en', 'ar', 'es', 'fr', 'de']),
            'currency' => fake()->randomElement(['USD', 'EUR', 'GBP', 'AED', 'SAR']),
            'theme_preference' => fake()->randomElement(['system', 'light', 'dark']),
            'notification_preferences' => json_encode([
                'email' => [
                    'order_updates' => fake()->boolean(90),
                    'promotions' => fake()->boolean(60),
                    'price_alerts' => fake()->boolean(70),
                    'newsletter' => fake()->boolean(50),
                    'security_alerts' => fake()->boolean(95),
                ],
                'push' => [
                    'order_updates' => fake()->boolean(80),
                    'chat_messages' => fake()->boolean(70),
                    'promotions' => fake()->boolean(50),
                ],
                'sms' => [
                    'order_updates' => fake()->boolean(60),
                    'security_alerts' => fake()->boolean(80),
                ]
            ]),
            'ai_interaction_enabled' => fake()->boolean(85),
            'chat_history_enabled' => fake()->boolean(75),
            'last_interaction_date' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function minimal(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'notification_preferences' => json_encode([
                    'email' => [
                        'order_updates' => true,
                        'security_alerts' => true,
                    ],
                ]),
                'ai_interaction_enabled' => false,
                'chat_history_enabled' => false,
            ];
        });
    }

    public function fullNotifications(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'notification_preferences' => json_encode([
                    'email' => [
                        'order_updates' => true,
                        'promotions' => true,
                        'price_alerts' => true,
                        'newsletter' => true,
                        'security_alerts' => true,
                    ],
                    'push' => [
                        'order_updates' => true,
                        'chat_messages' => true,
                        'promotions' => true,
                    ],
                    'sms' => [
                        'order_updates' => true, 
                        'security_alerts' => true,
                    ],
                ]),
            ];
        });
    }
}
