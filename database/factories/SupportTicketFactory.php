<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupportTicketFactory extends Factory
{
    protected $ticketTypes = [
        'technical' => [
            'weight' => 30,
            'priority_weights' => ['low' => 20, 'medium' => 50, 'high' => 25, 'urgent' => 5],
            'templates' => [
                'Technical issue with website',
                'Mobile app not working',
                'Login problems',
                'Payment processing issue',
                'Cannot add items to cart',
            ],
        ],
        'billing' => [
            'weight' => 25,
            'priority_weights' => ['low' => 20, 'medium' => 50, 'high' => 25, 'urgent' => 5],
            'templates' => [
                'Billing address update needed',
                'Payment method issue',
                'Double charged for order',
                'Promotional code not working',
                'Price discrepancy issue',
            ],
        ],
        'shipping' => [
            'weight' => 30,
            'priority_weights' => ['low' => 20, 'medium' => 50, 'high' => 25, 'urgent' => 5],
            'templates' => [
                'Order not received',
                'Wrong item received',
                'Missing items in order',
                'Order delivery delayed',
                'Damaged items received',
            ],
        ],
        'general' => [
            'weight' => 15,
            'priority_weights' => ['low' => 30, 'medium' => 50, 'high' => 15, 'urgent' => 5],
            'templates' => [
                'Product inquiry',
                'Return request',
                'Refund status',
                'Account question',
                'General feedback',
            ],
        ],
    ];

    protected $statusFlow = [
        'open' => ['in_progress', 'closed'],
        'in_progress' => ['waiting', 'resolved', 'closed'],
        'waiting' => ['in_progress', 'resolved', 'closed'],
        'resolved' => ['closed', 'open'],
        'closed' => ['open'],
    ];

    public function definition(): array
    {
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $admin = Admin::inRandomOrder()->first();
        $department = $this->getWeightedRandom($this->ticketTypes);
        
        // Get related order or product if applicable
        $order = $this->getRelatedOrder($user, $department);
        $product = $this->getRelatedProduct($order, $department);
        
        // Generate ticket details
        $subject = $this->generateSubject($department, $order, $product);
        $priority = $this->getWeightedPriority($department);
        $status = $this->determineStatus();
        
        // Calculate resolution time
        $created = fake()->dateTimeBetween('-3 months', 'now');
        $resolutionTime = in_array($status, ['resolved', 'closed']) 
            ? fake()->numberBetween(3600, 259200) // 1 hour to 3 days in seconds
            : null;
        
        return [
            'user_id' => $user->id,
            'order_id' => $order?->id,
            'product_id' => $product?->id,
            'subject' => $subject,
            'priority' => $priority,
            'status' => $status,
            'department' => $department,
            'assigned_to' => $status !== 'open' ? $admin?->id : null,
            'resolution_time' => $resolutionTime,
            'created_at' => $created,
            'updated_at' => fake()->dateTimeBetween($created, 'now'),
        ];
    }

    protected function getWeightedRandom(array $items): string
    {
        if (empty($items)) {
            return 'general'; // Default fallback value
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

    protected function getWeightedPriority(string $department): string
    {
        if (!isset($this->ticketTypes[$department]) || !isset($this->ticketTypes[$department]['priority_weights'])) {
            return 'medium'; // Default fallback value
        }
        
        $weights = $this->ticketTypes[$department]['priority_weights'];
        $priorityItems = [];
        
        foreach ($weights as $priority => $weight) {
            $priorityItems[$priority] = ['weight' => $weight];
        }
        
        return $this->getWeightedRandom($priorityItems);
    }

    protected function getRelatedOrder(User $user, string $department): ?Order
    {
        if ($department === 'shipping') {
            return Order::where('user_id', $user->id)
                ->inRandomOrder()
                ->first();
        }
        
        return fake()->boolean(30) ? 
            Order::where('user_id', $user->id)->inRandomOrder()->first() : 
            null;
    }

    protected function getRelatedProduct(?Order $order, string $department): ?Product
    {
        if ($department === 'general') {
            return fake()->boolean(50) ? Product::inRandomOrder()->first() : null;
        }
        
        if ($order) {
            $product = Product::whereHas('orderItems', function($query) use ($order) {
                $query->where('order_id', $order->id);
            })->inRandomOrder()->first();
            
            if ($product) {
                return $product;
            }
        }
        
        return null;
    }

    protected function generateSubject(string $department, ?Order $order, ?Product $product): string
    {
        $template = fake()->randomElement($this->ticketTypes[$department]['templates']);
        
        if ($order && strpos($template, 'order') !== false) {
            $template .= " #{$order->id}";
        }
        
        if ($product && strpos($template, 'item') !== false) {
            $template .= " - {$product->name}";
        }
        
        return $template;
    }

    protected function determineStatus(): string
    {
        return fake()->randomElement([
            'open',
            'in_progress',
            'waiting',
            'resolved',
            'closed',
            'closed', // Weighted to have more closed tickets
        ]);
    }

    public function urgent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'urgent',
            ];
        });
    }

    public function resolved(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'resolved',
                'resolution_time' => fake()->numberBetween(3600, 86400), // 1 hour to 1 day
            ];
        });
    }
}
