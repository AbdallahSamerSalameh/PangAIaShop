@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Total Orders Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2 card-hover">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Orders</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800 metric-number">{{ $totalOrders ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Revenue Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2 card-hover">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Sales</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($totalRevenue ?? 0, 2) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- Total Products Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2 card-hover">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800 metric-number">{{ $totalProducts ?? 0 }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- Total Customers Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2 card-hover">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Total Customers</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800 metric-number">{{ $totalCustomers ?? 0 }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Content Row -->
<div class="row">

    <!-- Area Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Sales Overview</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Dropdown Header:</div>
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Something else here</a>
                    </div>
                </div>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="myAreaChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Sales by Category (%)</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Dropdown Header:</div>
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Something else here</a>
                    </div>
                </div>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="myPieChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    @if(isset($categoriesChartData) && count($categoriesChartData['labels']) > 0)
                    @php
                    $colors = ['#F28123', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#6f42c1', '#fd7e14', '#20c997',
                    '#6610f2', '#dc3545'];
                    @endphp
                    @foreach($categoriesChartData['labels'] as $index => $category)
                    <span class="mr-2 mb-1 d-inline-block">
                        <i class="fas fa-circle" style="color: {{ $colors[$index % count($colors)] }}"></i>
                        {{ ucfirst($category) }} ({{ $categoriesChartData['data'][$index] }}%)
                    </span>
                    @endforeach
                    @else
                    <span class="mr-2">
                        <i class="fas fa-circle text-primary"></i> No sales data
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Recent Orders -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
            </div>
            <div class="card-body">
                @if(isset($recentOrders) && $recentOrders->count() > 0)
                @foreach($recentOrders as $order)
                <div class="d-flex align-items-center border-bottom py-2">
                    <div class="mr-3">
                        <div class="icon-circle bg-primary">
                            <i class="fas fa-shopping-cart text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="small text-gray-500">Order #{{ $order->id }}</div>
                        <div class="font-weight-bold">${{ number_format($order->total_amount, 2) }}</div>
                    </div>
                    <div class="text-right">
                        @php
                        $badgeClass = 'badge-secondary'; // default
                        switch(strtolower($order->status)) {
                        case 'processing':
                        $badgeClass = 'badge-info';
                        break;
                        case 'cancelled':
                        $badgeClass = 'badge-danger';
                        break;
                        case 'delivered':
                        $badgeClass = 'badge-success';
                        break;
                        case 'shipped':
                        $badgeClass = 'badge-purple';
                        break;
                        case 'pending':
                        $badgeClass = 'badge-warning';
                        break;
                        }
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ ucfirst($order->status) }} </span>
                    </div>
                </div>
                @endforeach @else
                <p class="text-muted">No recent orders found.</p>
                @endif
                <a class="btn btn-primary btn-sm mt-3" style="color: #fff !important; display: inline-block !important;"
                    href="{{ route('admin.orders.index') }}">View all Orders</a>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Top Products</h6>
            </div>
            <div class="card-body">
                @if(isset($topProducts) && $topProducts->count() > 0)
                @foreach($topProducts as $product)
                <div class="d-flex align-items-center border-bottom py-2">
                    <div class="mr-3">
                        <div class="icon-circle bg-success">
                            <i class="fas fa-box text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="font-weight-bold">{{ $product->name }}</div>
                        <div class="small text-gray-500">{{ $product->sales_count ?? 0 }} sales</div>
                    </div>
                    <div class="text-right">
                        <div class="font-weight-bold">${{ number_format($product->price, 2) }}</div>
                    </div>
                </div>
                @endforeach @else
                <p class="text-muted">No product data available.</p>
                @endif
                <a class="btn btn-primary btn-sm mt-3" style="color: #fff !important; display: inline-block !important;"
                    href="{{ route('admin.products.index') }}">View all Products</a>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<!-- Page level plugins -->
<script src="{{ asset('admin-assets/vendor/chart.js/Chart.min.js') }}"></script>

<!-- Pass sales data to JavaScript -->
<script>
    window.salesData = @json($salesChartData);
    window.categoryData = @json($categoriesChartData);
</script>

<!-- Page level custom scripts with custom branding colors -->
<script src="{{ asset('admin-assets/js/demo/chart-area-custom.js') }}"></script>
<script src="{{ asset('admin-assets/js/demo/chart-pie-custom.js') }}"></script>
@endpush