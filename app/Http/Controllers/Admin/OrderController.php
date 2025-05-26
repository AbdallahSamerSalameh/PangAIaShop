<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $statusFilter = $request->input('status');
        $searchQuery = $request->input('search');
        
        $orders = Order::with(['user', 'orderItems'])
            ->when($statusFilter, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($searchQuery, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($subQ) use ($search) {
                          $subQ->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            })
            ->latest()
            ->paginate(15);
        
        $statuses = Order::distinct('status')->pluck('status');
        
        return view('admin.orders.index', compact('orders', 'statuses', 'statusFilter', 'searchQuery'));
    }

    /**
     * Display the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\View\View
     */
    public function show(Order $order)
    {
        $order->load([
            'user', 
            'orderItems.product', 
            'shipment', 
            'payment'
        ]);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\View\View
     */
    public function edit(Order $order)
    {
        $order->load(['orderItems.product', 'user']);
        
        $statuses = [
            'Pending',
            'Processing',
            'Shipped',
            'Delivered',
            'Cancelled',
            'Refunded'
        ];
        
        return view('admin.orders.edit', compact('order', 'statuses'));
    }

    /**
     * Update the specified order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Order $order)
    {
        $validatedData = $request->validate([
            'status' => 'required|string|in:Pending,Processing,Shipped,Delivered,Cancelled,Refunded',
            'tracking_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        
        $oldStatus = $order->status;
        $newStatus = $validatedData['status'];
        
        // Handle inventory adjustments based on status changes
        if ($oldStatus !== $newStatus) {
            if ($newStatus === 'Cancelled' && in_array($oldStatus, ['Pending', 'Processing'])) {
                // Return items to inventory if order was cancelled
                $this->returnItemsToInventory($order);
            } else if ($newStatus === 'Processing' && $oldStatus === 'Cancelled') {
                // Deduct from inventory if a cancelled order is reactivated
                $this->deductItemsFromInventory($order);
            }
        }
        
        $order->update([
            'status' => $newStatus,
            'notes' => $validatedData['notes'],
        ]);
        
        // Update or create shipment record if tracking number is provided
        if (!empty($validatedData['tracking_number'])) {
            Shipment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'tracking_number' => $validatedData['tracking_number'],
                    'status' => $newStatus === 'Shipped' ? 'In Transit' : ($newStatus === 'Delivered' ? 'Delivered' : 'Pending'),
                    'shipped_at' => $newStatus === 'Shipped' ? now() : null,
                    'delivered_at' => $newStatus === 'Delivered' ? now() : null,
                ]
            );
        }

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', 'Order updated successfully!');
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
     */
    private function returnItemsToInventory(Order $order)
    {
        DB::transaction(function () use ($order) {
            foreach ($order->orderItems as $item) {
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
     */
    private function deductItemsFromInventory(Order $order)
    {
        DB::transaction(function () use ($order) {
            foreach ($order->orderItems as $item) {
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
    {
        $searchQuery = $request->input('search');
        
        $orders = Order::with(['user', 'orderItems'])
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
            ->latest()
            ->paginate(15);
        
        $statuses = Order::distinct('status')->pluck('status');
        
        return view('admin.orders.pending', [
            'orders' => $orders, 
            'statuses' => $statuses, 
            'searchQuery' => $searchQuery,
            'statusFilter' => 'Pending'
        ]);
    }
}
