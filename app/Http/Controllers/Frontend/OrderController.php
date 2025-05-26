<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
                       ->orderBy('order_date', 'desc')
                       ->paginate(10);
        
        return view('frontend.user.orders', [
            'orders' => $orders
        ]);
    }
    
    /**
     * Display the specified order details.
     *
     * @param  int  $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */    public function show($id)
    {
        $user = Auth::user();
        $order = Order::with(['items.product', 'payment', 'shipments'])
                      ->where('user_id', $user->id)
                      ->where('id', $id)
                      ->first();
        
        if (!$order) {
            return redirect()->route('orders')
                             ->with('error', 'Order not found or you do not have permission to view it.');
        }
        
        return view('frontend.user.order-details', [
            'order' => $order
        ]);
    }
    
    /**
     * Cancel the specified order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Request $request, $id)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)
                      ->where('id', $id)
                      ->first();
        
        if (!$order) {
            return redirect()->route('orders')
                             ->with('error', 'Order not found or you do not have permission to cancel it.');
        }
        
        // Check if the order can be cancelled (based on your business rules)
        $allowedStatuses = ['pending', 'processing'];
        
        if (!in_array($order->status, $allowedStatuses)) {
            return redirect()->route('orders.show', $order->id)
                             ->with('error', 'This order cannot be cancelled.');
        }
        
        // Update order status and save cancellation reason
        $order->status = 'cancelled';
        $order->cancelled_at = now();
        $order->cancellation_reason = $request->reason;
        $order->save();
          // You might want to handle inventory adjustments, refunds, etc. here
        
        // Check if the request is coming from the orders list page
        $referer = $request->headers->get('referer');
        
        if ($referer && str_contains($referer, 'orders') && !str_contains($referer, 'orders/' . $id)) {
            return redirect()->route('orders')
                            ->with('success', 'Order has been cancelled successfully.');
        } else {
            return redirect()->route('orders.show', $order->id)
                            ->with('success', 'Order has been cancelled successfully.');
        }
    }
    
    /**
     * Track the shipment of the specified order.
     *
     * @param  int  $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */    public function track($id)
    {
        $user = Auth::user();
        $order = Order::with('shipments')
                      ->where('user_id', $user->id)
                      ->where('id', $id)
                      ->first();
        
        if (!$order || $order->shipments->isEmpty()) {
            return redirect()->route('orders')
                             ->with('error', 'Order or shipment information not found.');
        }
        
        return view('frontend.user.order-tracking', [
            'order' => $order,
            'shipment' => $order->shipments->first()
        ]);
    }
}