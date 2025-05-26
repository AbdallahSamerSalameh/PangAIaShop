@extends('frontend.layouts.master')

@section('title', 'Inventory Diagnostic')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3>Inventory Diagnostic Results</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p><strong>How to interpret:</strong> This page shows the raw inventory data and how it's being processed.
                        If products are showing as "Out of Stock" despite having inventory, check the "in_stock_via_attribute" and "manual_check" columns.</p>
                    </div>
                    
                    <h4>Results</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Raw Quantity</th>
                                    <th>Quantity Type</th>
                                    <th>Quantity as Int</th>
                                    <th>in_stock via attribute</th>
                                    <th>stock_qty via attribute</th>
                                    <th>Manual Check (qty > 0)</th>
                                    <th>Inventory ID</th>
                                    <th>Inventory Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                <tr>
                                    <td>{{ $result['product_id'] }}</td>
                                    <td>{{ $result['product_name'] }}</td>
                                    <td>{{ $result['raw_quantity'] }}</td>
                                    <td>{{ $result['quantity_type'] }}</td>
                                    <td>{{ $result['quantity_as_int'] }}</td>
                                    <td>{{ $result['in_stock_via_attribute'] ? 'true' : 'false' }}</td>
                                    <td>{{ $result['stock_qty_via_attribute'] }}</td>
                                    <td>{{ $result['manual_check'] }}</td>
                                    <td>{{ $result['inventory_id'] }}</td>
                                    <td>{{ $result['inventory_count'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <h4 class="mt-4">Raw Data</h4>
                    <pre>{{ print_r($raw_data->toArray(), true) }}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
