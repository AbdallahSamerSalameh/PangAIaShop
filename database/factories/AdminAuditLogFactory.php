<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminAuditLogFactory extends Factory
{
    protected $actionTypes = [
        'product' => [
            'weight' => 25,
            'actions' => [
                'create' => ['weight' => 20],
                'update' => ['weight' => 50],
                'delete' => ['weight' => 10],
                'restore' => ['weight' => 5],
                'price_change' => ['weight' => 15],
            ],
        ],
        'order' => [
            'weight' => 20,
            'actions' => [
                'status_update' => ['weight' => 40],
                'refund' => ['weight' => 20],
                'cancel' => ['weight' => 15],
                'edit' => ['weight' => 25],
            ],
        ],
        'user' => [
            'weight' => 15,
            'actions' => [
                'verify' => ['weight' => 30],
                'suspend' => ['weight' => 20],
                'unsuspend' => ['weight' => 15],
                'edit' => ['weight' => 35],
            ],
        ],
        'category' => [
            'weight' => 10,
            'actions' => [
                'create' => ['weight' => 25],
                'update' => ['weight' => 45],
                'delete' => ['weight' => 15],
                'reorder' => ['weight' => 15],
            ],
        ],
        'promo' => [
            'weight' => 15,
            'actions' => [
                'create' => ['weight' => 30],
                'update' => ['weight' => 35],
                'deactivate' => ['weight' => 20],
                'extend' => ['weight' => 15],
            ],
        ],
        'inventory' => [
            'weight' => 15,
            'actions' => [
                'stock_adjustment' => ['weight' => 40],
                'reorder' => ['weight' => 30],
                'location_change' => ['weight' => 30],
            ],
        ],
    ];

    public function definition(): array
    {
        // Get admin user
        $admin = Admin::inRandomOrder()->first() ?? Admin::factory()->create();
        
        // Select resource type and specific action
        $resourceType = $this->getWeightedRandom($this->actionTypes);
        $action = $this->getWeightedAction($resourceType);
        
        // Get related model and generate changes
        $modelInfo = $this->getRelatedModel($resourceType);
        $changes = $this->generateChanges($resourceType, $action, $modelInfo['model']);
        
        // Generate IP address and user agent
        $ipAddress = fake()->ipv4();
        $userAgent = fake()->userAgent();
        
        return [
            'admin_id' => $admin->id,
            'action' => $action,
            'resource' => $resourceType,
            'resource_id' => $modelInfo['model']->id,
            'previous_data' => json_encode($changes['before']),
            'new_data' => json_encode($changes['after']),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    protected function getWeightedRandom(array $items): string
    {
        if (empty($items)) {
            return 'product'; // Default fallback value
        }
        
        $total = 0;
        foreach ($items as $item => $config) {
            if (is_array($config) && isset($config['weight'])) {
                $total += $config['weight'];
            }
        }
        
        if ($total <= 0) {
            return array_key_first($items);
        }
        
        $random = fake()->numberBetween(1, $total);
        $sum = 0;

        foreach ($items as $item => $config) {
            if (is_array($config) && isset($config['weight'])) {
                $sum += $config['weight'];
                if ($random <= $sum) {
                    return $item;
                }
            }
        }

        return array_key_first($items);
    }

    protected function getWeightedAction(string $resourceType): string
    {
        if (!isset($this->actionTypes[$resourceType]) || !isset($this->actionTypes[$resourceType]['actions'])) {
            return 'create'; // Default fallback value
        }
        
        $actions = $this->actionTypes[$resourceType]['actions'];
        $actionItems = [];
        
        foreach ($actions as $action => $config) {
            if (is_array($config) && isset($config['weight'])) {
                $actionItems[$action] = ['weight' => $config['weight']];
            }
        }
        
        return $this->getWeightedRandom($actionItems);
    }

    protected function getRelatedModel(string $resourceType): array
    {
        $model = match($resourceType) {
            'product' => [
                'type' => Product::class,
                'model' => Product::inRandomOrder()->first() ?? Product::factory()->create(),
            ],
            'order' => [
                'type' => Order::class,
                'model' => Order::inRandomOrder()->first() ?? Order::factory()->create(),
            ],
            'user' => [
                'type' => User::class,
                'model' => User::inRandomOrder()->first() ?? User::factory()->create(),
            ],
            'category' => [
                'type' => Category::class,
                'model' => Category::inRandomOrder()->first() ?? Category::factory()->create(),
            ],
            'promo' => [
                'type' => PromoCode::class,
                'model' => PromoCode::inRandomOrder()->first() ?? PromoCode::factory()->create(),
            ],
            default => [
                'type' => Product::class,
                'model' => Product::factory()->create(),
            ],
        };

        return $model;
    }

    protected function generateChanges(string $resourceType, string $action, $model): array
    {
        $changes = match($resourceType) {
            'product' => $this->generateProductChanges($action, $model),
            'order' => $this->generateOrderChanges($action, $model),
            'user' => $this->generateUserChanges($action, $model),
            'category' => $this->generateCategoryChanges($action, $model),
            'promo' => $this->generatePromoChanges($action, $model),
            'inventory' => $this->generateInventoryChanges($action, $model),
            default => ['before' => [], 'after' => []],
        };

        return $changes;
    }

    protected function generateProductChanges(string $action, $model): array
    {
        return match($action) {
            'create' => [
                'before' => null,
                'after' => $model->toArray(),
            ],
            'update' => [
                'before' => [
                    'name' => $model->name,
                    'price' => $model->price,
                    'status' => $model->status,
                ],
                'after' => [
                    'name' => fake()->boolean() ? $model->name : fake()->words(3, true),
                    'price' => fake()->boolean() ? $model->price : fake()->randomFloat(2, 10, 1000),
                    'status' => fake()->boolean() ? $model->status : fake()->randomElement(['active', 'inactive']),
                ],
            ],
            'delete' => [
                'before' => ['status' => 'active'],
                'after' => ['status' => 'deleted'],
            ],
            'price_change' => [
                'before' => ['price' => $model->price],
                'after' => ['price' => fake()->randomFloat(2, 10, 1000)],
            ],
            default => ['before' => [], 'after' => []],
        };
    }

    protected function generateOrderChanges(string $action, $model): array
    {
        return match($action) {
            'status_update' => [
                'before' => ['status' => $model->status],
                'after' => ['status' => fake()->randomElement(['processing', 'shipped', 'delivered'])],
            ],
            'refund' => [
                'before' => ['refund_status' => 'none'],
                'after' => [
                    'refund_status' => 'processed',
                    'refund_amount' => $model->total_amount,
                ],
            ],
            'cancel' => [
                'before' => ['status' => $model->status],
                'after' => ['status' => 'cancelled'],
            ],
            default => ['before' => [], 'after' => []],
        };
    }

    protected function generateUserChanges(string $action, $model): array
    {
        return match($action) {
            'verify' => [
                'before' => ['verified' => false],
                'after' => ['verified' => true],
            ],
            'suspend' => [
                'before' => ['status' => 'active'],
                'after' => ['status' => 'suspended'],
            ],
            'unsuspend' => [
                'before' => ['status' => 'suspended'],
                'after' => ['status' => 'active'],
            ],
            default => ['before' => [], 'after' => []],
        };
    }

    protected function generateCategoryChanges(string $action, $model): array
    {
        return match($action) {
            'create' => [
                'before' => null,
                'after' => $model->toArray(),
            ],
            'update' => [
                'before' => [
                    'name' => $model->name,
                    'description' => $model->description,
                ],
                'after' => [
                    'name' => fake()->words(2, true),
                    'description' => fake()->sentence(),
                ],
            ],
            'delete' => [
                'before' => ['status' => 'active'],
                'after' => ['status' => 'deleted'],
            ],
            default => ['before' => [], 'after' => []],
        };
    }

    protected function generatePromoChanges(string $action, $model): array
    {
        return match($action) {
            'create' => [
                'before' => null,
                'after' => $model->toArray(),
            ],
            'update' => [
                'before' => [
                    'code' => $model->code,
                    'value' => $model->value,
                ],
                'after' => [
                    'code' => strtoupper(fake()->bothify('PROMO##??')),
                    'value' => fake()->numberBetween(5, 50),
                ],
            ],
            'deactivate' => [
                'before' => ['status' => 'active'],
                'after' => ['status' => 'inactive'],
            ],
            'extend' => [
                'before' => ['end_date' => $model->end_date],
                'after' => ['end_date' => fake()->dateTimeBetween('+1 week', '+1 month')],
            ],
            default => ['before' => [], 'after' => []],
        };
    }

    protected function generateInventoryChanges(string $action, $model): array
    {
        return match($action) {
            'stock_adjustment' => [
                'before' => ['quantity' => $model->quantity],
                'after' => ['quantity' => fake()->numberBetween(0, 100)],
            ],
            'reorder' => [
                'before' => ['reorder_point' => $model->reorder_point],
                'after' => ['reorder_point' => fake()->numberBetween(5, 50)],
            ],
            'location_change' => [
                'before' => ['location' => $model->location],
                'after' => ['location' => 'ZONE-' . fake()->randomLetter() . fake()->numberBetween(1, 99)],
            ],
            default => ['before' => [], 'after' => []],
        };
    }

    public function failed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failure',
                'notes' => fake()->randomElement([
                    'Action failed due to validation errors',
                    'Operation timed out',
                    'Insufficient permissions',
                    'Resource locked by another process',
                    'Database constraint violation',
                ]),
            ];
        });
    }

    public function highImpact(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'impact_level' => 'high',
                'notes' => fake()->sentence(),
            ];
        });
    }
}
