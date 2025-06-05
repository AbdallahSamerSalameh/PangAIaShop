<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditLoggable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use AuditLoggable;
    /**
     * Display a listing of orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */    public function index(Request $request)    {
        $statusFilter = $request->input('status');
        $searchQuery = $request->input('search');
        $quickSearch = $request->input('quick_search');
        $perPage = (int)$request->input('per_page', 10); // Cast to integer and default to 10
          // Validate per_page to ensure it's a reasonable number
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;        $orders = Order::with(['user', 'items.product.images', 'items.product.categories'])
            ->when($statusFilter, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($searchQuery, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($subQ) use ($search) {
                          $subQ->where('username', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            })
            ->when($quickSearch, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($subQ) use ($search) {
                          $subQ->where('username', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('order_date', 'desc')
            ->paginate($perPage);

        $statuses = Order::distinct('status')->pluck('status');
          // Calculate order statistics
        $totalOrders = Order::count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $processingOrders = Order::where('status', 'processing')->count();
        $shippedOrders = Order::where('status', 'shipped')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        
        return view('admin.orders.index', compact(
            'orders', 
            'statuses', 
            'statusFilter', 
            'searchQuery', 
            'totalOrders', 
            'deliveredOrders', 
            'processingOrders', 
            'shippedOrders',
            'cancelledOrders'
        ));
    }

    /**
     * Display the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\View\View
     */    public function show(Order $order)
    {        $order->load([
            'user', 
            'items.product.images',
            'items.product.categories', 
            'shipments', 
            'payment'
        ]);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\View\View
     */    public function edit(Order $order)
    {
        $order->load(['items.product.images', 'items.product.categories', 'user']);
        
        $statuses = [
            'Pending',
            'Processing',
            'Shipped',
            'Delivered',
            // 'Completed',
            'Cancelled',
            // 'Refunded'
        ];
        
        return view('admin.orders.edit', compact('order', 'statuses'));
    }

    /**
     * Update the specified order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */    public function update(Request $request, Order $order)
    {
        $validatedData = $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,completed,cancelled,refunded',
            'admin_notes' => 'nullable|string',
            'expected_delivery_date' => 'nullable|date',
            'tracking_number' => 'nullable|string|max:255',
        ]);
          $oldStatus = $order->status;
        $newStatus = $validatedData['status'];
        
        // Store original data for audit log
        $originalData = $order->toArray();
        
        // Handle inventory adjustments based on status changes
        if ($oldStatus !== $newStatus) {
            if ($newStatus === 'cancelled' && in_array($oldStatus, ['pending', 'processing'])) {
                // Return items to inventory if order was cancelled
                $this->returnItemsToInventory($order);
            } else if ($newStatus === 'processing' && $oldStatus === 'cancelled') {
                // Deduct from inventory if a cancelled order is reactivated
                $this->deductItemsFromInventory($order);
            }
        }

        $order->update([
            'status' => $newStatus,
            'admin_notes' => $validatedData['admin_notes'],
            'expected_delivery_date' => $validatedData['expected_delivery_date'],
            'handled_by' => Auth::id(), // Track which admin handled the order
        ]);
        
        // Log the order status change
        if ($oldStatus !== $newStatus) {
            $this->logCustomAction('status_change', $order, "Changed order #{$order->id} status from {$oldStatus} to {$newStatus}", [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'admin_notes' => $validatedData['admin_notes']
            ]);
        } else {
            // Log general update
            $this->logUpdate($order, $originalData, "Updated order #{$order->id}");
        }
        
        // Update or create shipment record if tracking number is provided
        if (!empty($validatedData['tracking_number'])) {
            Shipment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'tracking_number' => $validatedData['tracking_number'],
                    'status' => $newStatus === 'shipped' ? 'In Transit' : ($newStatus === 'delivered' ? 'Delivered' : 'Pending'),
                    'shipped_at' => $newStatus === 'shipped' ? now() : null,
                    'delivered_at' => $newStatus === 'delivered' ? now() : null,
                ]
            );
        }

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order status updated successfully!');
    }

    /**
     * Remove the specified order from storage.
     * (Often this is an archival process rather than deletion)
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Order $order)
    {
        // Check if the order can be deleted
        if (!in_array($order->status, ['Cancelled', 'Refunded'])) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Only cancelled or refunded orders can be deleted.');
        }
        
        // For archive rather than delete
        $order->update(['is_archived' => true]);
        
        // Or to actually delete:
        // $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order archived successfully!');
    }
    
    /**
     * Return order items to inventory
     *
     * @param  \App\Models\Order  $order
     * @return void
     */    private function returnItemsToInventory(Order $order)
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = $item->product;
                
                if ($product) {
                    $inventory = Inventory::where('product_id', $product->id)->first();
                    
                    if ($inventory) {
                        $inventory->increment('quantity', $item->quantity);
                    }
                }
            }
        });
    }
    
    /**
     * Deduct order items from inventory
     *
     * @param  \App\Models\Order  $order
     * @return void
     */    private function deductItemsFromInventory(Order $order)
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = $item->product;
                
                if ($product) {
                    $inventory = Inventory::where('product_id', $product->id)->first();
                    
                    if ($inventory && $inventory->quantity >= $item->quantity) {
                        $inventory->decrement('quantity', $item->quantity);
                    }
                }
            }
        });
    }

    /**
     * Display a listing of pending orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function pending(Request $request)
    {        $searchQuery = $request->input('search');
          $orders = Order::with(['user', 'items'])
            ->where('status', 'Pending')
            ->when($searchQuery, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($subQ) use ($search) {
                          $subQ->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('order_date', 'desc')
            ->paginate(15);

        $statuses = Order::distinct('status')->pluck('status');
        
        return view('admin.orders.pending', [
            'orders' => $orders, 
            'statuses' => $statuses, 
            'searchQuery' => $searchQuery,
            'statusFilter' => 'Pending'
        ]);
    }

    /**
     * Generate and display/print invoice for an order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\View\View
     */
    public function invoice(Order $order)
    {
        $order->load([
            'user', 
            'items.product', 
            'payment'
        ]);
        
        return view('admin.orders.invoice', compact('order'));
    }
}
