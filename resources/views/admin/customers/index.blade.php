@extends('admin.layouts.app')

@section('title', 'Customers Management')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Customers Management</h1>
    <div>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm mr-2">
            <i class="fas fa-download fa-sm text-white-50"></i> Export Customers
        </a>
        <a href="{{ route('admin.customers.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Customer
        </a>
    </div>
</div>

<!-- Customer Statistics -->
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
                            Active Customers</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">2,156</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">New This Month</div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">187</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-plus fa-2x text-gray-300"></i>
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
                            Pending Verification</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">18</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customers Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Customers List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Sample data - replace with actual data from controller -->
                    <tr>
                        <td>1</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <img class="img-profile rounded-circle" style="width: 40px; height: 40px;"
                                        src="{{ asset('admin-assets/img/undraw_profile.svg') }}">
                                </div>
                                <div>
                                    <div class="font-weight-bold">John Doe</div>
                                    <div class="text-muted small">Premium Customer</div>
                                </div>
                            </div>
                        </td>
                        <td>john.doe@example.com</td>
                        <td>+1 (555) 123-4567</td>
                        <td><span class="badge badge-success">Active</span></td>
                        <td>27</td>
                        <td>$2,549.99</td>
                        <td>2023-06-15</td>
                        <td>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                    aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Actions:</div>
                                    <a class="dropdown-item" href="#"><i class="fas fa-eye fa-sm fa-fw mr-2 text-gray-400"></i> View Profile</a>
                                    <a class="dropdown-item" href="#"><i class="fas fa-shopping-cart fa-sm fa-fw mr-2 text-gray-400"></i> View Orders</a>
                                    <a class="dropdown-item" href="#"><i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i> Edit</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-warning" href="#"><i class="fas fa-ban fa-sm fa-fw mr-2 text-gray-400"></i> Suspend</a>
                                    <a class="dropdown-item text-danger" href="#"><i class="fas fa-trash fa-sm fa-fw mr-2 text-gray-400"></i> Delete</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <img class="img-profile rounded-circle" style="width: 40px; height: 40px;"
                                        src="{{ asset('admin-assets/img/undraw_profile_1.svg') }}">
                                </div>
                                <div>
                                    <div class="font-weight-bold">Jane Smith</div>
                                    <div class="text-muted small">Regular Customer</div>
                                </div>
                            </div>
                        </td>
                        <td>jane.smith@example.com</td>
                        <td>+1 (555) 987-6543</td>
                        <td><span class="badge badge-success">Active</span></td>
                        <td>12</td>
                        <td>$899.45</td>
                        <td>2023-09-22</td>
                        <td>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                    aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Actions:</div>
                                    <a class="dropdown-item" href="#"><i class="fas fa-eye fa-sm fa-fw mr-2 text-gray-400"></i> View Profile</a>
                                    <a class="dropdown-item" href="#"><i class="fas fa-shopping-cart fa-sm fa-fw mr-2 text-gray-400"></i> View Orders</a>
                                    <a class="dropdown-item" href="#"><i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i> Edit</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-warning" href="#"><i class="fas fa-ban fa-sm fa-fw mr-2 text-gray-400"></i> Suspend</a>
                                    <a class="dropdown-item text-danger" href="#"><i class="fas fa-trash fa-sm fa-fw mr-2 text-gray-400"></i> Delete</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <img class="img-profile rounded-circle" style="width: 40px; height: 40px;"
                                        src="{{ asset('admin-assets/img/undraw_profile_2.svg') }}">
                                </div>
                                <div>
                                    <div class="font-weight-bold">Mike Johnson</div>
                                    <div class="text-muted small">New Customer</div>
                                </div>
                            </div>
                        </td>
                        <td>mike.johnson@example.com</td>
                        <td>+1 (555) 456-7890</td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td>1</td>
                        <td>$149.99</td>
                        <td>2024-01-18</td>
                        <td>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                    aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Actions:</div>
                                    <a class="dropdown-item" href="#"><i class="fas fa-eye fa-sm fa-fw mr-2 text-gray-400"></i> View Profile</a>
                                    <a class="dropdown-item" href="#"><i class="fas fa-check fa-sm fa-fw mr-2 text-gray-400"></i> Verify Account</a>
                                    <a class="dropdown-item" href="#"><i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i> Edit</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#"><i class="fas fa-trash fa-sm fa-fw mr-2 text-gray-400"></i> Delete</a>
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
