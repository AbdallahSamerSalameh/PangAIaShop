<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number ?? 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</title>
    <link href="{{ asset('admin-assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin-assets/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
            body { background: white !important; }
            .card { border: 1px solid #ddd !important; box-shadow: none !important; }
        }
        
        .invoice-header {
            border-bottom: 3px solid #4e73df;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }
        
        .invoice-title {
            color: #4e73df;
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .invoice-details {
            background-color: #f8f9fc;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .invoice-table th {
            background-color: #4e73df;
            color: white;
            border: none;
        }
        
        .invoice-table td {
            border-color: #e3e6f0;
        }
        
        .total-section {
            background-color: #f8f9fc;
            border-radius: 10px;
            padding: 20px;
        }
        
        .company-info {
            color: #5a5c69;
        }
          .invoice-status {
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="bg-white">
    <div class="container-fluid py-4">
        <!-- Header Actions -->
        <div class="row no-print mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                    </a>
                    <div>
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="fas fa-print mr-2"></i>Print Invoice
                        </button>
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-info">
                            <i class="fas fa-eye mr-2"></i>View Order Details
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Content -->
        <div class="card shadow">
            <div class="card-body position-relative">
                <!-- Status Badge -->
                <div class="invoice-status">
                    @php
                        $statusClass = match(strtolower($order->status)) {
                            'pending' => 'badge-warning',
                            'processing' => 'badge-info',
                            'shipped' => 'badge-primary',
                            'delivered', 'completed' => 'badge-success',
                            'cancelled' => 'badge-danger',
                            'refunded' => 'badge-secondary',
                            default => 'badge-light'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }} p-2">{{ ucfirst($order->status) }}</span>
                </div>

                <!-- Invoice Header -->
                <div class="invoice-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h1 class="invoice-title">INVOICE</h1>
                            <p class="company-info">
                                <strong>PangAI Shop</strong><br>
                                123 Business Street<br>
                                City, State 12345<br>
                                Email: info@pangaishop.com<br>
                                Phone: (555) 123-4567
                            </p>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <h3 class="text-primary mb-3">Invoice #{{ $order->order_number ?? 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</h3>
                            <p class="mb-1"><strong>Invoice Date:</strong> {{ now()->format('M d, Y') }}</p>
                            <p class="mb-1"><strong>Order Date:</strong> {{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('M d, Y') : 'N/A' }}</p>
                            @if($order->payment)
                                <p class="mb-1"><strong>Payment Status:</strong> <span class="badge badge-success">Paid</span></p>
                            @else
                                <p class="mb-1"><strong>Payment Status:</strong> <span class="badge badge-warning">Pending</span></p>
                            @endif
                        </div>
                    </div>
                </div>                <!-- Customer Information -->
                <div class="invoice-details">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">Bill To:</h5>                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    @php
                                        $userImage = $order->user && $order->user->profile_image ? asset('storage/' . $order->user->profile_image) : ($order->user->avatar_url ?? null);
                                    @endphp
                                    @include('admin.components.image-with-fallback', [
                                        'src' => $userImage,
                                        'alt' => $order->user->username ?? $order->user->name ?? 'Guest User',
                                        'type' => 'profile',
                                        'class' => 'img-profile rounded-circle',
                                        'style' => 'width: 45px; height: 45px; object-fit: cover;'
                                    ])
                                </div>
                                <div>
                                    <div class="font-weight-bold">{{ $order->user->username ?? $order->user->name ?? 'Guest User' }}</div>
                                    <div class="text-muted small">{{ $order->user->email ?? 'No email' }}</div>
                                </div>
                            </div>
                            <address>
                                @if($order->billing_street)
                                    {{ $order->billing_street }}<br>
                                    {{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_postal_code }}<br>
                                    {{ $order->billing_country }}
                                @else
                                    <em>No billing address provided</em>
                                @endif
                            </address>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">Ship To:</h5>
                            <address>
                                @if($order->shipping_street)
                                    {{ $order->shipping_street }}<br>
                                    {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }}<br>
                                    {{ $order->shipping_country }}
                                @else
                                    <em>Same as billing address</em>
                                @endif
                            </address>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered invoice-table">
                        <thead>
                            <tr>
                                <th width="50%">Product</th>
                                <th width="15%" class="text-center">Quantity</th>
                                <th width="15%" class="text-right">Unit Price</th>
                                <th width="20%" class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product->name ?? 'Product Deleted' }}</strong>
                                    @if($item->product && $item->product->sku)
                                        <br><small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-right">${{ number_format($item->price, 2) }}</td>
                                <td class="text-right">${{ number_format($item->quantity * $item->price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="row">
                    <div class="col-md-6">
                        @if($order->notes)
                        <div class="total-section">
                            <h6 class="text-primary">Order Notes:</h6>
                            <p class="mb-0">{{ $order->notes }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="total-section">
                            <table class="table table-sm">
                                <tr>
                                    <td class="border-0"><strong>Subtotal:</strong></td>
                                    <td class="border-0 text-right">${{ number_format($order->subtotal ?? $order->total_amount, 2) }}</td>
                                </tr>
                                @if($order->shipping && $order->shipping > 0)
                                <tr>
                                    <td class="border-0"><strong>Shipping:</strong></td>
                                    <td class="border-0 text-right">${{ number_format($order->shipping, 2) }}</td>
                                </tr>
                                @endif
                                @if($order->discount && $order->discount > 0)
                                <tr>
                                    <td class="border-0 text-danger"><strong>Discount:</strong></td>
                                    <td class="border-0 text-right text-danger">-${{ number_format($order->discount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="border-top">
                                    <td class="border-0"><h5 class="text-primary mb-0">Total Amount:</h5></td>
                                    <td class="border-0 text-right"><h5 class="text-primary mb-0">${{ number_format($order->total_amount, 2) }}</h5></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="row mt-5">
                    <div class="col-12">
                        <hr>
                        <div class="text-center text-muted">
                            <p class="mb-1">Thank you for your business!</p>
                            <p class="mb-0">This invoice was generated on {{ now()->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('admin-assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('admin-assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
