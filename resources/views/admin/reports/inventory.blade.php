@extends('admin.layouts.app')

@section('title', 'Inventory Reports')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Inventory Reports</h1>
    <div>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Export Report
        </a>
    </div>
</div>

<!-- Inventory Overview -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">1,247</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Low Stock Items</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">23</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Out of Stock</div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">8</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Value</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$125,847</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Alert -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-warning">Low Stock Alert</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="lowStockTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Min Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Wireless Headphones Pro</td>
                        <td>WHP-001</td>
                        <td>Electronics</td>
                        <td>5</td>
                        <td>10</td>
                        <td><span class="badge badge-warning">Low Stock</span></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-primary">Restock</a>
                        </td>
                    </tr>
                    <tr>
                        <td>Running Shoes Elite</td>
                        <td>RSE-003</td>
                        <td>Clothing</td>
                        <td>3</td>
                        <td>15</td>
                        <td><span class="badge badge-warning">Low Stock</span></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-primary">Restock</a>
                        </td>
                    </tr>
                    <tr>
                        <td>Smartphone Case Premium</td>
                        <td>SCP-012</td>
                        <td>Electronics</td>
                        <td>0</td>
                        <td>20</td>
                        <td><span class="badge badge-danger">Out of Stock</span></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-danger">Urgent Restock</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Inventory by Category -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Inventory by Category</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Total Products</th>
                                <th>In Stock</th>
                                <th>Low Stock</th>
                                <th>Out of Stock</th>
                                <th>Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Electronics</td>
                                <td>547</td>
                                <td>489</td>
                                <td>45</td>
                                <td>13</td>
                                <td>$67,540.25</td>
                            </tr>
                            <tr>
                                <td>Clothing</td>
                                <td>324</td>
                                <td>298</td>
                                <td>19</td>
                                <td>7</td>
                                <td>$28,965.80</td>
                            </tr>
                            <tr>
                                <td>Books</td>
                                <td>189</td>
                                <td>175</td>
                                <td>12</td>
                                <td>2</td>
                                <td>$8,450.50</td>
                            </tr>
                            <tr>
                                <td>Home & Garden</td>
                                <td>187</td>
                                <td>165</td>
                                <td>18</td>
                                <td>4</td>
                                <td>$20,890.95</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Stock Status Distribution</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="stockChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> In Stock
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-warning"></i> Low Stock
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-danger"></i> Out of Stock
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Page level plugins -->
<script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendor/chart.js/Chart.min.js') }}"></script>

<!-- Page level custom scripts -->
<script>
$(document).ready(function() {
    $('#lowStockTable').DataTable({
        "order": [[ 3, "asc" ]],
        "pageLength": 25,
        "responsive": true
    });
});

// Stock Status Chart
var ctx = document.getElementById("stockChart");
var stockChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: ["In Stock", "Low Stock", "Out of Stock"],
    datasets: [{
      data: [1127, 94, 26],
      backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
      hoverBackgroundColor: ['#17a673', '#f4b619', '#c0392b'],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    }],
  },
  options: {
    maintainAspectRatio: false,
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,
    },
    legend: {
      display: false
    },
    cutoutPercentage: 80,
  },
});
</script>
@endsection
