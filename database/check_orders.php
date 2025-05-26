<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../bootstrap/app.php';

// Query all order statuses
$orders = \App\Models\Order::all(['id', 'order_number', 'status']);
use Illuminate\Support\Facades\DB;

echo "=== ALL ORDERS AND THEIR STATUSES ===\n";
foreach ($orders as $order) {
    echo "Order ID: {$order->id}, Number: {$order->order_number}, Status: '{$order->status}'\n";
}

// Count by status
$ordersByStatus = \App\Models\Order::select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();

echo "\n=== ORDER COUNTS BY STATUS ===\n";
foreach ($ordersByStatus as $statusItem) {
    echo "Status: '{$statusItem->status}', Count: {$statusItem->count}\n";
}

// Check normalized counts (case insensitive)
$allStatuses = \App\Models\Order::select('status')->distinct()->get()->pluck('status')->toArray();
echo "\n=== UNIQUE STATUS VALUES ===\n";
echo implode(', ', $allStatuses) . "\n";

// Test our normalization logic
$normalizedStatuses = [
    'pending' => 0,
    'processing' => 0,
    'shipped' => 0,
    'delivered' => 0,
    'cancelled' => 0,
    'returned' => 0,
];

foreach ($orders as $order) {
    $status = strtolower(trim($order->status));
    if (array_key_exists($status, $normalizedStatuses)) {
        $normalizedStatuses[$status]++;
    }
}

echo "\n=== NORMALIZED COUNTS ===\n";
foreach ($normalizedStatuses as $status => $count) {
    echo "Status: '{$status}', Count: {$count}\n";
}