<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Order::query();
            
            // Apply search filter
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }
            
            // Apply status filter
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            // Apply date range filter
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Apply sorting
            $sortField = $request->sort_field ?? 'created_at';
            $sortDirection = $request->sort_direction ?? 'desc';
            $query->orderBy($sortField, $sortDirection);
            
            // Include relationships
            $query->with(['user:id,name,email', 'items.product:id,name,price']);
            
            // Paginate results
            $perPage = $request->per_page ?? 10;
            $orders = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $order = Order::with([
                'user:id,name,email',
                'items.product',
                'payment',
                'shipment'
            ])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
    }

    /**
     * Update the order status.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,returned',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            $order = Order::findOrFail($id);
            $oldStatus = $order->status;
            $newStatus = $request->status;
            
            // Handle inventory changes if order is cancelled
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                // Restore inventory for each order item
                foreach ($order->items as $item) {
                    $inventory = Inventory::where('product_id', $item->product_id)->first();
                    if ($inventory) {
                        $inventory->stock += $item->quantity;
                        $inventory->updated_by = $request->user()->id;
                        $inventory->save();
                    }
                }
            }
            
            // Handle inventory changes if order is uncancelled
            if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                // Deduct inventory for each order item
                foreach ($order->items as $item) {
                    $inventory = Inventory::where('product_id', $item->product_id)->first();
                    if ($inventory) {
                        $inventory->stock -= $item->quantity;
                        $inventory->updated_by = $request->user()->id;
                        $inventory->save();
                    }
                }
            }
            
            // Update order status
            $order->status = $newStatus;
            
            // Add notes if provided
            if ($request->has('notes')) {
                $order->admin_notes = $request->notes;
            }
            
            // Record who handled the order
            $order->handled_by = $request->user()->id;
            
            $order->save();
            
            // Create order status history
            $order->statusHistory()->create([
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by' => $request->user()->id,
                'notes' => $request->notes
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order->load(['user:id,name,email', 'items.product:id,name,price'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created order.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|string',
            'billing_address' => 'required|string',
            'payment_method' => 'required|string',
            'shipping_method' => 'required|string',
            'notes' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            // Generate order number
            $orderNumber = 'ORD-' . time() . '-' . rand(1000, 9999);
            
            // Calculate subtotal, tax, and total
            $subtotal = 0;
            $itemsData = [];
            
            foreach ($request->items as $item) {
                $product = \App\Models\Product::findOrFail($item['product_id']);
                
                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;
                
                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $itemSubtotal
                ];
                
                // Deduct from inventory
                $inventory = Inventory::where('product_id', $product->id)->first();
                if ($inventory) {
                    if ($inventory->stock < $item['quantity']) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Insufficient stock for product: {$product->name}"
                        ], 422);
                    }
                    
                    $inventory->stock -= $item['quantity'];
                    $inventory->updated_by = $request->user()->id;
                    $inventory->save();
                }
            }
            
            // Calculate tax (e.g., 10%)
            $taxRate = 0.10;
            $tax = $subtotal * $taxRate;
            
            // Calculate shipping cost
            $shippingCost = 10.00; // Example fixed shipping cost
            
            // Calculate total
            $total = $subtotal + $tax + $shippingCost;
            
            // Create the order
            $order = new Order();
            $order->user_id = $request->user_id;
            $order->order_number = $orderNumber;
            $order->status = 'pending';
            $order->subtotal = $subtotal;
            $order->tax = $tax;
            $order->shipping = $shippingCost;
            $order->total = $total;
            $order->shipping_address = $request->shipping_address;
            $order->billing_address = $request->billing_address;
            $order->payment_method = $request->payment_method;
            $order->shipping_method = $request->shipping_method;
            $order->notes = $request->notes;
            $order->admin_notes = $request->admin_notes;
            $order->created_by = $request->user()->id;
            $order->handled_by = $request->user()->id;
            $order->save();
            
            // Create the order items
            foreach ($itemsData as $itemData) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $itemData['product_id'];
                $orderItem->quantity = $itemData['quantity'];
                $orderItem->unit_price = $itemData['unit_price'];
                $orderItem->subtotal = $itemData['subtotal'];
                $orderItem->save();
            }
            
            // Create order status history
            $order->statusHistory()->create([
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by' => $request->user()->id,
                'notes' => 'Order created by admin'
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->load(['user:id,name,email', 'items.product:id,name,price'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified order.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'shipping_address' => 'sometimes|required|string',
            'billing_address' => 'sometimes|required|string',
            'payment_method' => 'sometimes|required|string',
            'shipping_method' => 'sometimes|required|string',
            'notes' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::findOrFail($id);
            
            // Only allow updating non-critical fields
            if ($request->has('shipping_address')) {
                $order->shipping_address = $request->shipping_address;
            }
            
            if ($request->has('billing_address')) {
                $order->billing_address = $request->billing_address;
            }
            
            if ($request->has('payment_method')) {
                $order->payment_method = $request->payment_method;
            }
            
            if ($request->has('shipping_method')) {
                $order->shipping_method = $request->shipping_method;
            }
            
            if ($request->has('notes')) {
                $order->notes = $request->notes;
            }
            
            if ($request->has('admin_notes')) {
                $order->admin_notes = $request->admin_notes;
            }
            
            $order->updated_by = $request->user()->id;
            $order->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order->load(['user:id,name,email', 'items.product:id,name,price'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified order.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);
            
            // Only allow deletion of pending orders
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending orders can be deleted'
                ], 422);
            }
            
            DB::beginTransaction();
            
            // Restore inventory
            foreach ($order->items as $item) {
                $inventory = Inventory::where('product_id', $item->product_id)->first();
                if ($inventory) {
                    $inventory->stock += $item->quantity;
                    $inventory->updated_by = Auth::id();
                    $inventory->save();
                }
            }
            
            // Soft delete the order
            $order->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order: ' . $e->getMessage()
            ], 500);
        }
    }
}