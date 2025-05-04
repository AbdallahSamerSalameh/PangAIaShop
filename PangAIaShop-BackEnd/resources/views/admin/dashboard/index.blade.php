@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
    <div class="mb-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Sales Card -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-gray-500 text-sm uppercase font-semibold">Total Sales</h3>
                        <p class="text-2xl font-bold">${{ number_format($totalSales, 2) }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-dollar-sign text-blue-500 text-xl"></i>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    @if($salesGrowth > 0)
                        <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ number_format($salesGrowth, 1) }}%</span>
                    @elseif($salesGrowth < 0)
                        <span class="text-red-500"><i class="fas fa-arrow-down"></i> {{ number_format(abs($salesGrowth), 1) }}%</span>
                    @else
                        <span class="text-gray-500"><i class="fas fa-minus"></i> 0%</span>
                    @endif
                    from last month
                </p>
            </div>
            
            <!-- Total Orders Card -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-gray-500 text-sm uppercase font-semibold">Total Orders</h3>
                        <p class="text-2xl font-bold">{{ number_format($totalOrders) }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-shopping-cart text-green-500 text-xl"></i>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    @if($ordersGrowth > 0)
                        <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ number_format($ordersGrowth, 1) }}%</span>
                    @elseif($ordersGrowth < 0)
                        <span class="text-red-500"><i class="fas fa-arrow-down"></i> {{ number_format(abs($ordersGrowth), 1) }}%</span>
                    @else
                        <span class="text-gray-500"><i class="fas fa-minus"></i> 0%</span>
                    @endif
                    from last month
                </p>
            </div>
            
            <!-- Total Products Card -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-gray-500 text-sm uppercase font-semibold">Products</h3>
                        <p class="text-2xl font-bold">{{ number_format($totalProducts) }}</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-box text-purple-500 text-xl"></i>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    {{ number_format($lowStockCount) }} products with low stock
                </p>
            </div>
            
            <!-- Total Customers Card -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-gray-500 text-sm uppercase font-semibold">Customers</h3>
                        <p class="text-2xl font-bold">{{ number_format($totalCustomers) }}</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-users text-yellow-500 text-xl"></i>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    @if($customersGrowth > 0)
                        <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ number_format($customersGrowth, 1) }}%</span>
                    @elseif($customersGrowth < 0)
                        <span class="text-red-500"><i class="fas fa-arrow-down"></i> {{ number_format(abs($customersGrowth), 1) }}%</span>
                    @else
                        <span class="text-gray-500"><i class="fas fa-minus"></i> 0%</span>
                    @endif
                    from last month
                </p>
            </div>
        </div>
        
        <!-- Order Status Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @foreach($ordersByStatus as $status)
                <div class="bg-white rounded-lg shadow-sm p-4 text-center">
                    <h4 class="font-semibold text-gray-500 uppercase text-xs mb-2">{{ ucfirst($status->status) }}</h4>
                    <p class="text-2xl font-bold">{{ $status->count }}</p>
                </div>
            @endforeach
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Sales Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Sales Overview</h3>
            <canvas id="salesChart" height="200"></canvas>
        </div>
        
        <!-- Top Categories Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Sales by Category</h3>
            <canvas id="categoriesChart" height="200"></canvas>
        </div>
    </div>
    
    <!-- Recent Orders and Low Stock Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold">Recent Orders</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-blue-500 hover:text-blue-700 text-sm">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td class="py-2 px-4 text-sm border-b">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="text-blue-500 hover:text-blue-700">
                                        #{{ $order->id }}
                                    </a>
                                </td>
                                <td class="py-2 px-4 text-sm border-b">{{ $order->user->name ?? 'Guest' }}</td>
                                <td class="py-2 px-4 text-sm border-b">${{ number_format($order->total_amount, 2) }}</td>
                                <td class="py-2 px-4 text-sm border-b">
                                    @if($order->status == 'completed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    @elseif($order->status == 'processing')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    @elseif($order->status == 'cancelled')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2 px-4 text-sm border-b">{{ $order->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 px-4 text-sm text-center text-gray-500">No recent orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Low Stock Products -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold">Low Stock Products</h3>
                <a href="{{ route('admin.inventory.index') }}" class="text-blue-500 hover:text-blue-700 text-sm">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Threshold</th>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockProducts as $product)
                            <tr>
                                <td class="py-2 px-4 text-sm border-b">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="text-blue-500 hover:text-blue-700">
                                        {{ $product->name }}
                                    </a>
                                </td>
                                <td class="py-2 px-4 text-sm border-b">{{ $product->stock_quantity }}</td>
                                <td class="py-2 px-4 text-sm border-b">{{ $product->stock_threshold }}</td>
                                <td class="py-2 px-4 text-sm border-b">
                                    @if($product->stock_quantity <= 0)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Out of Stock
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Low Stock
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2 px-4 text-sm border-b">
                                    <a href="{{ route('admin.inventory.edit', $product->id) }}" class="text-blue-500 hover:text-blue-700">
                                        Update Stock
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 px-4 text-sm text-center text-gray-500">No low stock products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($salesChartData['labels']) !!},
            datasets: [{
                label: 'Sales',
                data: {!! json_encode($salesChartData['data']) !!},
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });
    
    // Categories Chart
    const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
    new Chart(categoriesCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($categoriesChartData['labels']) !!},
            datasets: [{
                data: {!! json_encode($categoriesChartData['data']) !!},
                backgroundColor: [
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(239, 68, 68, 0.7)',
                    'rgba(139, 92, 246, 0.7)',
                    'rgba(249, 115, 22, 0.7)',
                    'rgba(6, 182, 212, 0.7)',
                    'rgba(236, 72, 153, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
</script>
@endsection