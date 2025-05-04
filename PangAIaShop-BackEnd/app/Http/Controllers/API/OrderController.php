<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $orders = $user->orders()->with(['items.product.images', 'payment', 'shipment'])->latest()->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
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
            'shipping_address' => 'required|string',
            'billing_address' => 'required|string',
            'payment_method' => 'required|string',
            'promo_code' => 'nullable|string|exists:promo_codes,code'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->with(['items.product', 'items.variant'])->firstOrFail();
        
        // Check if the cart is empty
        if ($cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Create the order
            $order = new Order([
                'user_id' => $user->id,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'payment_method' => $request->payment_method,
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'shipping_fee' => $cart->shipping_fee,
                'discount' => $cart->discount,
                'total' => $cart->total,
                'status' => 'pending',
                'promo_code' => $request->promo_code
            ]);
            
            $order->save();
            
            // Create order items from cart items
            foreach ($cart->items as $cartItem) {
                $orderItem = new OrderItem([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'variant_id' => $cartItem->variant_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'subtotal' => $cartItem->quantity * $cartItem->price
                ]);
                
                $order->items()->save($orderItem);
                
                // Update inventory (decrement stock)
                $inventory = $cartItem->product->inventory;
                if ($inventory) {
                    $inventory->stock -= $cartItem->quantity;
                    $inventory->save();
                }
            }
            
            // Clear the cart after order is placed
            $cart->items()->delete();
            $cart->updateTotals();
            
            // Load order details
            $order->load(['items.product.images', 'items.variant']);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => $order
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified order.
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Order $order)
    {
        $user = $request->user();
        
        // Check if the order belongs to the authenticated user
        if ($order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $order->load(['items.product.images', 'items.variant', 'payment', 'shipment']);
        
        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
    
    /**
     * Cancel an order.
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, Order $order)
    {
        $user = $request->user();
        
        // Check if the order belongs to the authenticated user
        if ($order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        // Check if the order can be cancelled
        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be cancelled',
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Update order status to cancelled
            $order->status = 'cancelled';
            $order->save();
            
            // Restore inventory (increment stock)
            foreach ($order->items as $orderItem) {
                $inventory = $orderItem->product->inventory;
                if ($inventory) {
                    $inventory->stock += $orderItem->quantity;
                    $inventory->save();
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}