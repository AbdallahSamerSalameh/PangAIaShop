@extends('admin.layouts.app')

@section('title', 'Orders Management')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Orders Management</h1>
    <div>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm mr-2">
            <i class="fas fa-download fa-sm text-white-50"></i> Export Orders
        </a>
    </div>
</div>

<!-- Order Statistics -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Orders</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">1,247</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                            Completed Orders</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">956</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            Pending Orders</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">187</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Cancelled Orders</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">104</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Orders List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Sample data - replace with actual data from controller -->
                    <tr>
                        <td>#ORD-001247</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <img class="img-profile rounded-circle" style="width: 30px; height: 30px;"
                                        src="{{ asset('admin-assets/img/undraw_profile.svg') }}">
                                </div>
                                <div>
                                    <div class="font-weight-bold">John Doe</div>
                                    <div class="text-muted small">john@example.com</div>
                                </div>
                            </div>
                        </td>
                        <td>2024-01-20</td>
                        <td><span class="badge badge-warning">Processing</span></td>
                        <td>3</td>
                        <td>$249.99</td>
                        <td><span class="badge badge-success">Paid</span></td>
                        <td>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                    aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Actions:</div>
                                    <a class="dropdown-item" href="#"><i class="fas fa-eye fa-sm fa-fw mr-2 text-gray-400"></i> View Details</a>
                                    <a class="dropdown-item" href="#"><i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i> Update Status</a>
                                    <a class="dropdown-item" href="#"><i class="fas fa-print fa-sm fa-fw mr-2 text-gray-400"></i> Print Invoice</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#"><i class="fas fa-times fa-sm fa-fw mr-2 text-gray-400"></i> Cancel Order</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>#ORD-001246</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <img class="img-profile rounded-circle" style="width: 30px; height: 30px;"
                                        src="{{ asset('admin-assets/img/undraw_profile_1.svg') }}">
                                </div>
                                <div>
                                    <div class="font-weight-bold">Jane Smith</div>
                                    <div class="text-muted small">jane@example.com</div>
                                </div>
                            </div>
                        </td>
                        <td>2024-01-19</td>
                        <td><span class="badge badge-success">Completed</span></td>
                        <td>2</td>
                        <td>$89.99</td>
                        <td><span class="badge badge-success">Paid</span></td>
                        <td>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                    aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Actions:</div>
                                    <a class="dropdown-item" href="#"><i class="fas fa-eye fa-sm fa-fw mr-2 text-gray-400"></i> View Details</a>
                                    <a class="dropdown-item" href="#"><i class="fas fa-redo fa-sm fa-fw mr-2 text-gray-400"></i> Refund</a>
                                    <a class="dropdown-item" href="#"><i class="fas fa-print fa-sm fa-fw mr-2 text-gray-400"></i> Print Invoice</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Page level plugins -->
<script src="{{ asset('admin-assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<!-- Page level custom scripts -->
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "order": [[ 0, "desc" ]],
        "pageLength": 25,
        "responsive": true
    });
});
</script>
@endsection
