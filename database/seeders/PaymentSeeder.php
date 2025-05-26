<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Get orders that need payments
        $orders = Order::all();

        foreach ($orders as $order) {
            // Create successful payments for 80% of orders
            if (fake()->boolean(80)) {
                Payment::factory()->create([
                    'order_id' => $order->id,
                    'amount' => $order->total_amount,
                    'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'bank_transfer', 'crypto']),
                    'payment_processor' => fake()->randomElement(['Stripe', 'PayPal', 'Square', 'Braintree', 'Authorize.net']),
                    'status' => 'completed',
                    'transaction_id' => fake()->unique()->uuid,
                    'created_at' => $order->order_date->addMinutes(fake()->numberBetween(5, 60))
                ]);
            } else {
                // Create failed or pending payments for remaining orders
                $status = fake()->randomElement(['failed', 'pending']);
                $paymentMethod = fake()->randomElement(['credit_card', 'paypal', 'bank_transfer', 'crypto']);
                $processor = match($paymentMethod) {
                    'credit_card' => fake()->randomElement(['Stripe', 'Authorize.net', 'Braintree']),
                    'paypal' => 'PayPal',
                    'bank_transfer' => fake()->randomElement(['Plaid', 'Stripe ACH']),
                    'crypto' => fake()->randomElement(['Coinbase', 'BitPay']),
                    default => 'Unknown'
                };
                
                Payment::factory()->create([
                    'order_id' => $order->id,
                    'amount' => $order->total_amount,
                    'payment_method' => $paymentMethod,
                    'payment_processor' => $processor,
                    'status' => $status,
                    'transaction_id' => $status === 'failed' ? fake()->unique()->uuid : fake()->unique()->uuid,
                    'created_at' => $order->order_date->addMinutes(fake()->numberBetween(5, 60)),
                    'refund_reason' => $status === 'failed' ? fake()->randomElement([
                        'insufficient_funds',
                        'card_declined',
                        'payment_timeout',
                        'invalid_card_details'
                    ]) : null
                ]);
            }
        }

        // Create some refunded payments
        $completedPayments = Payment::where('status', 'completed')
            ->take(3)
            ->get();

        foreach ($completedPayments as $payment) {
            Payment::factory()->create([
                'order_id' => $payment->order_id,
                'amount' => $payment->amount * -1, // Negative amount for refund
                'payment_method' => $payment->payment_method,
                'payment_processor' => $payment->payment_processor,
                'status' => 'refunded',
                'transaction_id' => fake()->unique()->uuid,
                'created_at' => $payment->created_at->addDays(fake()->numberBetween(1, 14)),
                'refund_id' => fake()->uuid,
                'refund_reason' => fake()->randomElement([
                    'customer_request',
                    'product_damaged',
                    'wrong_item_received',
                    'item_not_received'
                ])
            ]);
        }
    }
}
