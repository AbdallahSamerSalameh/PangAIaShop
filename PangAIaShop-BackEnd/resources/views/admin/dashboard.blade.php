@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Orders Stat -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700">Orders</h3>
                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Today</span>
            </div>
            <div class="flex items-center">
                <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
                <div>
                    <span class="block text-2xl font-bold text-gray-800">{{ $todayOrders }}</span>
                    <span class="block text-sm text-gray-500">
                        @if($ordersTrendPercentage > 0)
                            <span class="text-green-500">
                                <i class="fas fa-arrow-up mr-1"></i> {{ number_format($ordersTrendPercentage, 1) }}%
                            </span>
                        @elseif($ordersTrendPercentage < 0)
                            <span class="text-red-500">
                                <i class="fas fa-arrow-down mr-1"></i> {{ number_format(abs($ordersTrendPercentage), 1) }}%
                            </span>
                        @else
                            <span class="text-gray-500">
                                <i class="fas fa-minus mr-1"></i> 0%
                            </span>
                        @endif
                        vs yesterday
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Revenue Stat -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700">Revenue</h3>
                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Today</span>
            </div>
            <div class="flex items-center">
                <div class="rounded-full bg-green-100 p-3 mr-4">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
                <div>
                    <span class="block text-2xl font-bold text-gray-800">${{ number_format($todayRevenue, 2) }}</span>
                    <span class="block text-sm text-gray-500">
                        @if($revenueTrend > 0)
                            <span class="text-green-500">
                                <i class="fas fa-arrow-up mr-1"></i> {{ $revenueTrend }}%
                            </span>
                        @elseif($revenueTrend < 0)
                            <span class="text-red-500">
                                <i class="fas fa-arrow-down mr-1"></i> {{ abs($revenueTrend) }}%
                            </span>
                        @else
                            <span class="text-gray-500">
                                <i class="fas fa-minus mr-1"></i> 0%
                            </span>
                        @endif
                        vs yesterday
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Visitors Stat -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700">Visitors</h3>
                <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">Today</span>
            </div>
            <div class="flex items-center">
                <div class="rounded-full bg-purple-100 p-3 mr-4">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
                <div>
                    <span class="block text-2xl font-bold text-gray-800">{{ $todayVisitors }}</span>
                    <span class="block text-sm text-gray-500">
                        @if($visitorsTrend > 0)
                            <span class="text-green-500">
                                <i class="fas fa-arrow-up mr-1"></i> {{ $visitorsTrend }}%
                            </span>
                        @elseif($visitorsTrend < 0)
                            <span class="text-red-500">
                                <i class="fas fa-arrow-down mr-1"></i> {{ abs($visitorsTrend) }}%
                            </span>
                        @else
                            <span class="text-gray-500">
                                <i class="fas fa-minus mr-1"></i> 0%
                            </span>
                        @endif
                        vs yesterday
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Conversion Rate Stat -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700">Conversion</h3>
                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Today</span>
            </div>
            <div class="flex items-center">
                <div class="rounded-full bg-yellow-100 p-3 mr-4">
                    <i class="fas fa-chart-pie text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <span class="block text-2xl font-bold text-gray-800">{{ $conversionRate }}%</span>
                    <span class="block text-sm text-gray-500">
                        @if($conversionTrend > 0)
                            <span class="text-green-500">
                                <i class="fas fa-arrow-up mr-1"></i> {{ $conversionTrend }}%
                            </span>
                        @elseif($conversionTrend < 0)
                            <span class="text-red-500">
                                <i class="fas fa-arrow-down mr-1"></i> {{ abs($conversionTrend) }}%
                            </span>
                        @else
                            <span class="text-gray-500">
                                <i class="fas fa-minus mr-1"></i> 0%
                            </span>
                        @endif
                        vs yesterday
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts and Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Sales Chart -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-700">Sales Overview</h3>
            </div>
            <div class="p-6">
                <canvas id="salesChart" height="300"></canvas>
            </div>
        </div>
        
        <!-- Top Products -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-700">Top Products</h3>
                <a href="{{ route('admin.reports.products') }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
            </div>
            <div class="p-6">
                <ul class="divide-y divide-gray-200">
                    @forelse($topProducts as $product)
                        <li class="py-3 flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-md overflow-hidden bg-gray-100">
                                @if($product->main_image_url)
                                    <img src="{{ Storage::url($product->main_image_url) }}" alt="{{ $product->name }}" class="h-10 w-10 object-cover">
                                @else
                                    <div class="h-10 w-10 flex items-center justify-center text-gray-400">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $product->sales_count }} sold</p>
                            </div>
                            <div class="text-sm font-medium text-gray-900">${{ number_format($product->revenue, 2) }}</div>
                        </li>
                    @empty
                        <li class="py-4 text-center text-gray-500">No products data available</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-700">Recent Orders</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentOrders as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600 hover:text-blue-900">
                                    <a href="{{ route('admin.orders.show', $order->id) }}">#{{ $order->id }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full overflow-hidden bg-gray-100">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($order->user->name) }}&color=7F9CF5&background=EBF4FF" alt="{{ $order->user->name }}">
                                        </div>
                                        <div class="ml-2">
                                            <div class="text-sm font-medium text-gray-900">{{ $order->user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $order->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($order->status === 'completed') bg-green-100 text-green-800
                                        @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                        @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->order_date->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No recent orders</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Activity Log -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-700">Recent Activities</h3>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
            </div>
            <div class="p-6">
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @forelse($recentActivities as $activity)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex items-start space-x-3">
                                        <div>
                                            <div class="relative px-1">
                                                <div class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                    @if(in_array($activity->action, ['login', 'admin_create', 'product_create', 'order_create'])) 
                                                        bg-green-500
                                                    @elseif(in_array($activity->action, ['logout', 'admin_update', 'product_update', 'order_update'])) 
                                                        bg-blue-500
                                                    @elseif(in_array($activity->action, ['login_failed', 'admin_delete', 'product_delete', 'order_delete'])) 
                                                        bg-red-500
                                                    @else 
                                                        bg-gray-500
                                                    @endif">
                                                    @switch($activity->action)
                                                        @case('login')
                                                            <i class="fas fa-sign-in-alt text-white"></i>
                                                            @break
                                                        @case('logout')
                                                            <i class="fas fa-sign-out-alt text-white"></i>
                                                            @break
                                                        @case('admin_create')
                                                        @case('product_create')
                                                        @case('order_create')
                                                            <i class="fas fa-plus text-white"></i>
                                                            @break
                                                        @case('admin_update')
                                                        @case('product_update')
                                                        @case('order_update')
                                                            <i class="fas fa-edit text-white"></i>
                                                            @break
                                                        @case('admin_delete')
                                                        @case('product_delete')
                                                        @case('order_delete')
                                                            <i class="fas fa-trash text-white"></i>
                                                            @break
                                                        @default
                                                            <i class="fas fa-cog text-white"></i>
                                                    @endswitch
                                                </div>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div>
                                                <div class="text-sm">
                                                    <a href="{{ route('admin.admins.show', $activity->admin_id) }}" class="font-medium text-gray-900">
                                                        {{ $activity->admin->username }}
                                                    </a>
                                                </div>
                                                <p class="mt-0.5 text-sm text-gray-500">
                                                    {{ ucfirst(str_replace('_', ' ', $activity->action)) }}
                                                    @if($activity->resource && $activity->resource_id)
                                                        {{ $activity->resource }} #{{ $activity->resource_id }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="mt-1 text-sm text-gray-700">
                                                <p>{{ $activity->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="py-4 text-center text-gray-500">No recent activities</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sales Chart
        const salesData = @json($formattedSalesChartData);
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.labels,
                datasets: [{
                    label: 'Sales',
                    data: salesData.sales,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }, {
                    label: 'Revenue',
                    data: salesData.revenue,
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    });
</script>
@endsection