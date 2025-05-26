<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        $order = Order::inRandomOrder()->first() ?? Order::factory()->create();
        $admin = Admin::inRandomOrder()->first()?->id;
        
        $paymentMethods = ['credit_card', 'paypal', 'bank_transfer', 'crypto'];
        $paymentProcessors = [
            'credit_card' => ['Stripe', 'PayPal Pro', 'Authorize.net', 'Braintree'],
            'paypal' => ['PayPal', 'PayPal Express'],
            'bank_transfer' => ['Bank Wire', 'ACH Transfer', 'SWIFT'],
            'crypto' => ['BitPay', 'Coinbase', 'CryptoPay']
        ];
        
        $method = fake()->randomElement($paymentMethods);
        $processor = fake()->randomElement($paymentProcessors[$method]);
        
        // Generate a realistic transaction ID based on processor
        $transactionId = match($processor) {
            'Stripe' => 'ch_' . strtolower(fake()->regexify('[a-z0-9]{24}')),
            'PayPal', 'PayPal Express', 'PayPal Pro' => fake()->regexify('[A-Z0-9]{12,17}'),
            'BitPay', 'Coinbase' => fake()->regexify('[a-f0-9]{32}'),
            default => strtoupper(fake()->regexify('[A-Z0-9]{8,16}')),
        };
        
        // Determine payment status
        $status = fake()->randomElement([
            'completed', 'completed', 'completed', 'completed',
            'pending', 'pending',
            'failed',
            'refunded'
        ]);
        
        // Generate refund details if status is refunded
        $refundId = null;
        $refundReason = null;
        if ($status === 'refunded') {
            $refundId = 're_' . strtolower(fake()->regexify('[a-z0-9]{24}'));
            $refundReason = fake()->randomElement([
                'Customer request',
                'Item not as described',
                'Item damaged',
                'Order cancelled',
                'Duplicate payment',
                'Fraudulent transaction'
            ]);
        }
        
        return [
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'payment_method' => $method,
            'payment_processor' => $processor,
            'transaction_id' => $transactionId,
            'status' => $status,
            'created_at' => $order->order_date,
            'updated_at' => fake()->dateTimeBetween($order->order_date, 'now'),
            'refund_id' => $refundId,
            'refund_reason' => $refundReason,
            'processed_by' => fake()->boolean(70) ? $admin : null,
        ];
    }
}
