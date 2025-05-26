@extends('admin.layouts.app')

@section('title', 'Customer Reports')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Customer Reports</h1>
    <div>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Export Report
        </a>
    </div>
</div>

<!-- Customer Overview -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Customers</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">2,547</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
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
                            New This Month</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">187</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Customer Lifetime Value</div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">$234.50</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                            Repeat Customers</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">68%</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-redo fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customer Segmentation -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Customer Growth Over Time</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="customerGrowthChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Customer Segments</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="customerSegmentChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-primary"></i> Premium
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> Regular
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-info"></i> New
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Customers -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Top Customers by Lifetime Value</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="topCustomersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Join Date</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Avg Order Value</th>
                        <th>Last Purchase</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <img class="img-profile rounded-circle" style="width: 35px; height: 35px;"
                                        src="{{ asset('admin-assets/img/undraw_profile.svg') }}">
                                </div>
                                <div>
                                    <div class="font-weight-bold">John Doe</div>
                                    <div class="text-muted small">Premium Customer</div>
                                </div>
                            </div>
                        </td>
                        <td>john.doe@example.com</td>
                        <td>2023-03-15</td>
                        <td>47</td>
                        <td>$5,847.25</td>
                        <td>$124.41</td>
                        <td>2024-01-18</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <img class="img-profile rounded-circle" style="width: 35px; height: 35px;"
                                        src="{{ asset('admin-assets/img/undraw_profile_1.svg') }}">
                                </div>
                                <div>
                                    <div class="font-weight-bold">Jane Smith</div>
                                    <div class="text-muted small">Premium Customer</div>
                                </div>
                            </div>
                        </td>
                        <td>jane.smith@example.com</td>
                        <td>2023-01-22</td>
                        <td>32</td>
                        <td>$4,156.80</td>
                        <td>$129.90</td>
                        <td>2024-01-15</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <img class="img-profile rounded-circle" style="width: 35px; height: 35px;"
                                        src="{{ asset('admin-assets/img/undraw_profile_2.svg') }}">
                                </div>
                                <div>
                                    <div class="font-weight-bold">Mike Johnson</div>
                                    <div class="text-muted small">Regular Customer</div>
                                </div>
                            </div>
                        </td>
                        <td>mike.johnson@example.com</td>
                        <td>2023-06-10</td>
                        <td>28</td>
                        <td>$3,247.65</td>
                        <td>$115.99</td>
                        <td>2024-01-12</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Customer Demographics -->
<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Customer Registration Trends</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td><strong>This Week</strong></td>
                                <td>47 new customers</td>
                                <td><span class="text-success">+12%</span></td>
                            </tr>
                            <tr>
                                <td><strong>This Month</strong></td>
                                <td>187 new customers</td>
                                <td><span class="text-success">+8%</span></td>
                            </tr>
                            <tr>
                                <td><strong>This Quarter</strong></td>
                                <td>542 new customers</td>
                                <td><span class="text-success">+15%</span></td>
                            </tr>
                            <tr>
                                <td><strong>This Year</strong></td>
                                <td>1,847 new customers</td>
                                <td><span class="text-success">+22%</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Customer Activity</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td><strong>Active Customers</strong></td>
                                <td>2,156 customers</td>
                                <td><span class="badge badge-success">84.6%</span></td>
                            </tr>
                            <tr>
                                <td><strong>Inactive (30+ days)</strong></td>
                                <td>298 customers</td>
                                <td><span class="badge badge-warning">11.7%</span></td>
                            </tr>
                            <tr>
                                <td><strong>Dormant (90+ days)</strong></td>
                                <td>93 customers</td>
                                <td><span class="badge badge-danger">3.7%</span></td>
                            </tr>
                            <tr>
                                <td><strong>Average Orders/Customer</strong></td>
                                <td>4.2 orders</td>
                                <td><span class="text-info">+0.3</span></td>
                            </tr>
                        </tbody>
                    </table>
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
    $('#topCustomersTable').DataTable({
        "order": [[ 5, "desc" ]],
        "pageLength": 25,
        "responsive": true
    });
});

// Customer Growth Chart
var ctx = document.getElementById("customerGrowthChart");
var customerGrowthChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    datasets: [{
      label: "New Customers",
      lineTension: 0.3,
      backgroundColor: "rgba(78, 115, 223, 0.05)",
      borderColor: "rgba(78, 115, 223, 1)",
      pointRadius: 3,
      pointBackgroundColor: "rgba(78, 115, 223, 1)",
      pointBorderColor: "rgba(78, 115, 223, 1)",
      pointHoverRadius: 3,
      pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
      pointHoverBorderColor: "rgba(78, 115, 223, 1)",
      pointHitRadius: 10,
      pointBorderWidth: 2,
      data: [65, 78, 92, 89, 156, 145, 178, 189, 201, 198, 187, 203],
    }],
  },
  options: {
    maintainAspectRatio: false,
    layout: {
      padding: {
        left: 10,
        right: 25,
        top: 25,
        bottom: 0
      }
    },
    scales: {
      xAxes: [{
        time: {
          unit: 'date'
        },
        gridLines: {
          display: false,
          drawBorder: false
        },
        ticks: {
          maxTicksLimit: 7
        }
      }],
      yAxes: [{
        ticks: {
          maxTicksLimit: 5,
          padding: 10,
        },
        gridLines: {
          color: "rgb(234, 236, 244)",
          zeroLineColor: "rgb(234, 236, 244)",
          drawBorder: false,
          borderDash: [2],
          zeroLineBorderDash: [2]
        }
      }],
    },
    legend: {
      display: false
    },
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      titleMarginBottom: 10,
      titleFontColor: '#6e707e',
      titleFontSize: 14,
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      intersect: false,
      mode: 'index',
      caretPadding: 10,
    }
  }
});

// Customer Segment Chart
var ctx = document.getElementById("customerSegmentChart");
var customerSegmentChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: ["Premium", "Regular", "New"],
    datasets: [{
      data: [25, 60, 15],
      backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
      hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
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
