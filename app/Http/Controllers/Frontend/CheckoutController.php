<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        // Get cart items for the current session or user
        $cart = $this->getOrCreateCart();
        $cartItems = $cart->items()->with(['product', 'product.images' => function($query) {
            $query->where('is_primary', true);
        }])->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('shop')->with('error', 'Your cart is empty. Please add items before checkout.');
        }
        
        // Transform cart items to include featured image
        $cartItems->transform(function($item) {
            $item->product->featured_image = $item->product->images->first() 
                ? $item->product->images->first()->image_url 
                : 'assets/img/products/product-img-1.jpg';
            
            return $item;
        });
        
        // Calculate cart totals
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += ($item->product->price * $item->quantity);
        }
        
        $shipping = $subtotal > 0 ? 10.00 : 0.00; // Example shipping calculation
        $discount = Session::get('discount', 0);
        $total = $subtotal + $shipping - $discount;
        
        return view('frontend.pages.checkout', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'discount' => $discount,
            'total' => $total,
        ]);
    }
    
    /**
     * Process the checkout and create an order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'payment_method' => 'required|in:credit_card,paypal,cash_on_delivery',
        ]);
        
        // Get cart
        $cart = $this->getOrCreateCart();
        $cartItems = $cart->items()->with('product')->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('shop')->with('error', 'Your cart is empty.');
        }
        
        // Calculate totals
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += ($item->product->price * $item->quantity);
        }
        
        $shipping = $subtotal > 0 ? 10.00 : 0.00;
        $discount = Session::get('discount', 0);
        $total = $subtotal + $shipping - $discount;
        
        // Create order
        $order = Order::create([
            'user_id' => Auth::id(),
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'status' => 'pending',
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'discount' => $discount,
            'total' => $total,
            'billing_name' => $request->name,
            'billing_email' => $request->email,
            'billing_address' => $request->address,
            'billing_phone' => $request->phone,
            'notes' => $request->notes,
            'promo_code' => Session::get('coupon_code'),
        ]);
        
        // Create order items
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->product->price,
                'total_price' => $item->quantity * $item->product->price,
            ]);
        }
        
        // Create payment record
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => $request->payment_method,
            'amount' => $total,
            'status' => $request->payment_method === 'cash_on_delivery' ? 'pending' : 'processing',
            'transaction_id' => $request->payment_method === 'cash_on_delivery' ? null : 'TXN-' . strtoupper(Str::random(10)),
        ]);
        
        // Create shipment record
        Shipment::create([
            'order_id' => $order->id,
            'tracking_number' => 'TRK-' . strtoupper(Str::random(10)),
            'status' => 'processing',
            'shipping_address' => $request->has('same_address') ? $request->address : $request->shipping_address,
            'shipping_method' => 'standard',
            'estimated_delivery_date' => now()->addDays(5),
        ]);
          // Mark cart as completed and reset promo code
        $cart->status = 'completed';
        $cart->promo_code = null;
        $cart->promo_code_id = null;
        $cart->discount = 0;
        $cart->save();
        
        // Clear session data
        Session::forget(['discount', 'coupon_code']);
        
        return redirect()->route('home')->with('success', 'Your order has been placed successfully! Order number: ' . $order->order_number);
    }
    
    /**
     * Get the current cart or create a new one if it doesn't exist.
     *
     * @return \App\Models\Cart
     */
    private function getOrCreateCart()
    {
        $userId = Auth::id();
        $sessionId = Session::getId();
        
        $cart = null;
        
        if ($userId) {
            // Try to find a cart associated with the user
            $cart = Cart::where('user_id', $userId)
                       ->where('status', 'active')
                       ->first();
                       
            if (!$cart) {
                // Also check for session-based cart to merge or create a new one
                $sessionCart = Cart::where('session_id', $sessionId)
                                  ->where('status', 'active')
                                  ->first();
                                  
                if ($sessionCart) {
                    // Update session cart to be associated with the user
                    $sessionCart->user_id = $userId;
                    $sessionCart->save();
                    $cart = $sessionCart;
                } else {
                    // Create a new cart for the user
                    $cart = Cart::create([
                        'user_id' => $userId,
                        'status' => 'active',
                        'created_at' => now(),
                    ]);
                }
            }
        } else {
            // Try to find a cart associated with the session
            $cart = Cart::where('session_id', $sessionId)
                       ->where('status', 'active')
                       ->first();
                       
            if (!$cart) {
                // Create a new cart for the session
                $cart = Cart::create([
                    'session_id' => $sessionId,
                    'status' => 'active',
                    'created_at' => now(),
                ]);
            }
        }
        
        return $cart;
    }
}