<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CartController extends Controller
{    /**
     * Display the user's cart.
     *
     * @return \Illuminate\View\View
     */    public function index(Request $request)
    {
        // Redirect guest users to guest cart
        if (!Auth::check()) {
            return redirect()->route('guest.cart');
        }
          $cart = $this->getOrCreateCart();
        
        // Clean up any zero quantity items first
        $cart->cleanZeroQuantityItems();
        
        // Get cart items with product details and inventory information
        // Use activeItems instead of items() to only get non-zero quantity items
        $cartItems = $cart->activeItems()
            ->with(['product.images' => function($query) {
                $query->where('is_primary', true);
            }, 'product.inventory', 'variant'])
            ->get();
        
        // Calculate cart totals
        $totals = $cart->updateTotals();
            
        // Transform the collection to add featured image and additional calculations
        $cartItems = $cartItems->map(function($item) {
            $product = $item->product;
            
            $product->featured_image = $product->images->first() 
                ? $product->images->first()->image_url 
                : 'assets/img/products/product-img-1.jpg';
                
            // Calculate item subtotal
            $item->subtotal = $item->quantity * ($item->unit_price ?? $product->price);
            
            return $item;
        });
        
        // Calculate totals
        $subtotal = $cartItems->sum('subtotal');
        $discount = $cart->discount ?: 0;
        $shipping = 5.00; // Default shipping cost
        $total = $subtotal + $shipping - $discount;
        
        return view('frontend.pages.cart', [
            'cart' => $cart,
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $total,
            'appliedCoupon' => $cart->promo_code
        ]);
    }
    
    /**
     * Add a product to the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */    public function add(Request $request)
    {
        // Redirect guest users to guest cart add
        if (!Auth::check()) {
            return redirect()->route('guest.cart.add', $request->only('product_id', 'quantity', 'variant_id'));
        }
        
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $productId = $request->product_id;
        $quantity = $request->quantity;
        
        // Get the product
        $product = Product::findOrFail($productId);
          // Check if product is in stock by checking inventory
        $productInventory = $product->inventory->first();
        $stockQuantity = $productInventory ? $productInventory->quantity : 0;
        
        if ($stockQuantity < $quantity) {
            $message = 'Sorry, only ' . $stockQuantity . ' items available in stock.';
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
            }
            
            return redirect()->back()->with('error', $message);
        }
        
        // Get or create cart
        $cart = $this->getOrCreateCart();
        
        // Check if product already exists in cart
        $cartItem = $cart->items()->where('product_id', $productId)->first();
        
        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem->quantity + $quantity;
              // Check if new quantity exceeds stock
            $productInventory = $product->inventory->first();
            $stockQuantity = $productInventory ? $productInventory->quantity : 0;
            
            if ($newQuantity > $stockQuantity) {
                $message = 'Sorry, only ' . $stockQuantity . ' items available in stock.';
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ]);
                }
                
                return redirect()->back()->with('error', $message);
            }
            
            $cartItem->quantity = $newQuantity;
            $cartItem->save();
            
            $message = 'Cart updated successfully!';
        } else {            // Add new cart item
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => ($product->sale_price && $product->sale_price > 0) ? $product->sale_price : $product->price
            ]);
            
            $message = 'Product added to cart!';
        }
        
        // Update cart timestamp
        $cart->touch();
        
        // Return appropriate response based on request type
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_count' => $cart->items()->sum('quantity')
            ]);
        }
        
        return redirect()->back()->with('success', $message);
    }
    
    /**
     * Update cart item quantity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */    public function update(Request $request)
    {
        // Redirect guest users to guest cart update
        if (!Auth::check()) {
            return redirect()->route('guest.cart.update', $request->all());
        }
        
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $cartItemId = $request->cart_item_id;
        $quantity = $request->quantity;
        
        // Get cart
        $cart = $this->getOrCreateCart();
        
        // Get cart item
        $cartItem = $cart->items()->where('id', $cartItemId)->firstOrFail();
        
        // Get product
        $product = $cartItem->product;
          // Check if quantity exceeds stock
        $productInventory = $product->inventory->first();
        $stockQuantity = $productInventory ? $productInventory->quantity : 0;
        
        if ($quantity > $stockQuantity) {
            $message = 'Sorry, only ' . $stockQuantity . ' items available in stock.';
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
            }
            
            return redirect()->back()->with('error', $message);
        }
        
        // Update quantity
        $cartItem->quantity = $quantity;
        $cartItem->save();
        
        // Update cart timestamp
        $cart->touch();
          // Calculate new item subtotal
        $subtotal = $cartItem->quantity * ($cartItem->unit_price ?? $cartItem->product->price);
          // Calculate cart totals
        $cartItems = $cart->items()->with('product')->get();
        $cartSubtotal = $cartItems->sum(function($item) {
            return $item->quantity * ($item->unit_price ?? $item->product->price);
        });
        
        $discount = $cart->discount ?: 0;
        $total = $cartSubtotal - $discount;
        
        // Return appropriate response based on request type
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully!',
                'item_subtotal' => $subtotal,
                'cart_subtotal' => $cartSubtotal,
                'cart_discount' => $discount,
                'cart_total' => $total,
                'formatted_item_subtotal' => '$' . number_format($subtotal, 2),
                'formatted_cart_subtotal' => '$' . number_format($cartSubtotal, 2),
                'formatted_cart_discount' => '$' . number_format($discount, 2),
                'formatted_cart_total' => '$' . number_format($total, 2),
                'cart_count' => $cart->items()->sum('quantity')
            ]);
        }
        
        return redirect()->route('cart')->with('success', 'Cart updated successfully!');
    }
    
    /**
     * Remove an item from the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function remove(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id'
        ]);
        
        $cartItemId = $request->cart_item_id;
        
        // Get cart
        $cart = $this->getOrCreateCart();
        
        // Delete cart item
        $cart->items()->where('id', $cartItemId)->delete();
          // Update cart timestamp
        $cart->touch();
        
        // Get cart items as a collection then calculate totals
        $cartItems = $cart->items()->with('product')->get();
        $cartSubtotal = $cartItems->sum(function($item) {
            return $item->quantity * ($item->unit_price ?? $item->product->price);
        });
        
        $discount = $cart->discount ?: 0;
        $total = $cartSubtotal - $discount;
        
        // Return appropriate response based on request type
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart!',
                'cart_subtotal' => $cartSubtotal,
                'cart_discount' => $discount,
                'cart_total' => $total,
                'formatted_cart_subtotal' => '$' . number_format($cartSubtotal, 2),
                'formatted_cart_discount' => '$' . number_format($discount, 2),
                'formatted_cart_total' => '$' . number_format($total, 2),
                'cart_count' => $cart->items()->sum('quantity'),
                'cart_empty' => $cart->items()->count() === 0
            ]);
        }
        
        return redirect()->route('cart')->with('success', 'Item removed from cart!');
    }
    
    /**
     * Apply a coupon code to the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50'
        ]);
        
        $couponCode = $request->coupon_code;
        
        // Get cart
        $cart = $this->getOrCreateCart();
          // Check if coupon exists and is valid
        $promoCode = PromoCode::where('code', $couponCode)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('valid_from')
                      ->orWhere('valid_from', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('valid_until')
                      ->orWhere('valid_until', '>=', now());
            })
            ->first();
            
        if (!$promoCode) {
            $message = 'Invalid or expired coupon code.';
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
            }
            
            return redirect()->back()->with('error', $message)->withInput();
        }
          // Check global usage limits
        if ($promoCode->usage_limit && $promoCode->usage_count >= $promoCode->usage_limit) {
            $message = 'This coupon has reached its usage limit.';
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
            }
            
            return redirect()->back()->with('error', $message)->withInput();
        }
        
        // Check per-user usage limits (maximum 3 times per user)
        if (Auth::check()) {
            $userUsageCount = $promoCode->getUserUsageCount(Auth::id());
            if ($userUsageCount >= 3) {
                $message = 'You have already used this promo code the maximum number of times.';
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ]);
                }
                
                return redirect()->back()->with('error', $message)->withInput();
            }
        }// Calculate cart subtotal
        $cartItems = $cart->items()->with('product')->get();
        $subtotal = $cartItems->sum(function($item) {
            return $item->quantity * ($item->unit_price ?? $item->product->price);
        });
        
        // Check minimum order amount
        if ($promoCode->minimum_order_amount && $subtotal < $promoCode->minimum_order_amount) {
            $message = 'Your order must be at least $' . number_format($promoCode->minimum_order_amount, 2) . ' to use this coupon.';
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
            }
            
            return redirect()->back()->with('error', $message)->withInput();
        }
          // Calculate discount amount with $100 maximum cap
        $discount = $promoCode->calculateDiscount($subtotal);
        
        // Apply discount to cart
        $cart->discount = $discount;
        $cart->promo_code = $promoCode->code;
        $cart->promo_code_id = $promoCode->id;
        $cart->save();
        
        // Increment usage count for the promo code
        $promoCode->increment('usage_count');
        
        // Calculate total
        $total = $subtotal - $discount;
        
        // Success message
        $message = 'Coupon applied successfully!';
        
        // Return appropriate response based on request type
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_subtotal' => $subtotal,
                'cart_discount' => $discount,
                'cart_total' => $total,
                'formatted_cart_subtotal' => '$' . number_format($subtotal, 2),
                'formatted_cart_discount' => '$' . number_format($discount, 2),
                'formatted_cart_total' => '$' . number_format($total, 2),
                'coupon_code' => $promoCode->code
            ]);
        }
        
        return redirect()->route('cart')->with('success', $message);
    }
    
    /**
     * Remove applied coupon from the cart.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function removeCoupon(Request $request)
    {
        // Get cart
        $cart = $this->getOrCreateCart();
        
        // Remove coupon
        $cart->discount = 0;
        $cart->promo_code = null;
        $cart->promo_code_id = null;
        $cart->save();        // Calculate cart totals
        $cartItems = $cart->items()->with('product')->get();
        $subtotal = $cartItems->sum(function($item) {
            return $item->quantity * ($item->unit_price ?? $item->product->price);
        });
        
        $total = $subtotal;
        
        // Return appropriate response based on request type
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon removed successfully!',
                'cart_subtotal' => $subtotal,
                'cart_discount' => 0,
                'cart_total' => $total,
                'formatted_cart_subtotal' => '$' . number_format($subtotal, 2),
                'formatted_cart_discount' => '$0.00',
                'formatted_cart_total' => '$' . number_format($total, 2)
            ]);
        }
        
        return redirect()->route('cart')->with('success', 'Coupon removed successfully!');
    }
    
    /**
     * Clear all items from the cart.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clear()
    {
        // Get cart
        $cart = $this->getOrCreateCart();
        
        // Delete all items
        $cart->items()->delete();
        
        // Reset discount
        $cart->discount = 0;
        $cart->promo_code = null;
        $cart->promo_code_id = null;
        $cart->save();
        
        return redirect()->route('cart')->with('success', 'Cart cleared successfully!');
    }
    
    /**
     * Display checkout page
     *
     * @return \Illuminate\View\View
     */
    public function checkout()
    {
        // Redirect guest users to login
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to checkout');
        }
        
        $cart = $this->getOrCreateCart();
        
        // Redirect if cart is empty
        if ($cart->items()->count() === 0) {
            return redirect()->route('cart')->with('error', 'Your cart is empty');
        }
        
        // Get cart items with product details
        $cartItems = $cart->items()
            ->with(['product.images' => function($query) {
                $query->where('is_primary', true);
            }])
            ->get();
            
        // Transform the collection to add featured image and additional calculations
        $cartItems = $cartItems->map(function($item) {
            $product = $item->product;
            
            $product->featured_image = $product->images->first() 
                ? $product->images->first()->image_url 
                : 'assets/img/products/product-img-1.jpg';
                
            // Calculate item subtotal
            $item->subtotal = $item->quantity * ($item->unit_price ?? $product->price);
            
            return $item;
        });
        
        // Calculate totals
        $subtotal = $cartItems->sum('subtotal');
        $discount = $cart->discount ?: 0;
        $shipping = 5.00; // Default shipping cost
        $total = $subtotal + $shipping - $discount;
        
        return view('frontend.pages.checkout', [
            'cart' => $cart,
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $total
        ]);
    }
    
    /**
     * Process checkout
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    // (This duplicate processCheckout method is removed to fix the syntax error)

    /**     * Display order confirmation
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\View\View
     */
    public function orderConfirmation(Order $order)
    {
        // Make sure user can only view their own orders
        if (Auth::id() !== $order->user_id) {
            abort(403);
        }
          
        return view('frontend.pages.order-confirmation', [
            'order' => $order->load(['items.product', 'shipments', 'payment'])
        ]);
    }

    /**     * Helper method to get or create a cart.
     *
     * @return \App\Models\Cart
     */
    public function getOrCreateCart()
    {
        // For authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            
            // First try to find an existing cart
            $cart = Cart::where('user_id', $user->id)->first();
            
            if (!$cart) {
                // Create a new cart without attaching any products directly to it
                $cart = new Cart();
                $cart->user_id = $user->id;
                // Don't set a product_id - we'll use cart_items instead
                $cart->discount = 0;
                $cart->save();
            }
            
            // Clean up any zero quantity items that might be lingering
            $cart->cleanZeroQuantityItems();
            
            return $cart;
        }
        
        // For guest users (should not reach here but just in case)
        $sessionCartId = Session::get('cart_id');
        
        if ($sessionCartId) {
            $cart = Cart::find($sessionCartId);
            
            if ($cart) {
                return $cart;
            }
        }
        
        // Create new cart for guest without any placeholder product
        $cart = new Cart();
        $cart->user_id = null;
        $cart->discount = 0;
        $cart->save();
        
        Session::put('cart_id', $cart->id);
        
        return $cart;
    }
    
    /**
     * Add a product to the cart
     */    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        try {
            DB::beginTransaction();
            
            $cart = $this->getOrCreateCart();
            
            // Check product availability
            $product = Product::with('inventory')->findOrFail($validated['product_id']);
              // Set variant_id to null if not provided
            $variantId = $request->has('variant_id') ? $validated['variant_id'] : null;
            
            if ($variantId) {
                $variant = ProductVariant::find($variantId);
                $inventoryItem = $variant ? $variant->inventory : null;
            } else {
                $inventoryItem = $product->inventory->first();
            }
            
            if (!$inventoryItem || $inventoryItem->quantity < $validated['quantity']) {
                throw new \Exception('Product is out of stock or insufficient quantity available.');
            }
              // Check if the item already exists in cart
            $cartItem = $cart->items()
                ->where('product_id', $validated['product_id'])
                ->where('variant_id', $variantId)
                ->first();
                  if ($cartItem) {
                // Update quantity if item exists
                $newQuantity = $cartItem->quantity + $validated['quantity'];
                if ($inventoryItem->quantity < $newQuantity) {
                    throw new \Exception('Cannot add more of this item. Insufficient stock.');
                }
                
                $cartItem->quantity = $newQuantity;
                $cartItem->save();
            } else {                // Create new cart item                // Determine the correct price to use: sale_price (if available) or regular price
                $productPrice = ($product->sale_price && $product->sale_price > 0) 
                    ? $product->sale_price 
                    : $product->price;
                
                $cart->items()->create([
                    'product_id' => $validated['product_id'],
                    'variant_id' => $variantId,
                    'quantity' => $validated['quantity'],
                    'unit_price' => $variantId && isset($variant->price) ? $variant->price : $productPrice
                ]);
            }
            
            // Update cart expiry
            $cart->updateExpiry();
            
            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Product added to cart successfully',
                    'cart' => $cart->load('items.product')
                ]);
            }
            
            return redirect()->back()->with('success', 'Product added to cart successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Update cart item quantity
     */
    public function updateQuantity(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:0'
        ]);
        
        try {
            DB::beginTransaction();
            
            $cart = $this->getOrCreateCart();
            $cartItem = $cart->items()->findOrFail($validated['item_id']);
            
            // Check if we should remove the item
            if ($validated['quantity'] === 0) {
                $cartItem->delete();
                
                DB::commit();
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Item removed from cart',
                        'cart' => $cart->fresh()->load('items.product')
                    ]);
                }
                
                return redirect()->back()->with('success', 'Item removed from cart');
            }
              // Check stock availability
            $product = $cartItem->product;
            $inventoryItem = $cartItem->variant_id ? 
                $cartItem->variant->inventory : 
                $product->inventory->first();
                
            if (!$inventoryItem || $inventoryItem->quantity < $validated['quantity']) {
                throw new \Exception('Requested quantity is not available in stock.');
            }
            
            $cartItem->quantity = $validated['quantity'];
            $cartItem->save();
            
            // Update cart expiry
            $cart->updateExpiry();
            
            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cart updated successfully',
                    'cart' => $cart->fresh()->load('items.product')
                ]);
            }
            
            return redirect()->back()->with('success', 'Cart updated successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
      /**
     * Apply a promo code to the cart
     */
    public function applyPromoCode(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string'
        ]);
        
        $cart = $this->getOrCreateCart();
        $code = $validated['code'];
        
        // Find the promo code directly in the controller
        $promoCode = PromoCode::where('code', $code)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->first();
            
        if (!$promoCode) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Invalid or expired promo code'
                ], 422);
            }
            return redirect()->back()->with('error', 'Invalid or expired promo code');
        }
            
        // Check per-user limit
        if (Auth::check()) {
            $userId = Auth::id();
            $userUsageCount = $promoCode->getUserUsageCount($userId);
            if ($userUsageCount >= 3) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'You have already used this promo code the maximum number of times'
                    ], 422);
                }
                return redirect()->back()->with('error', 'You have already used this promo code the maximum number of times');
            }
        }
        
        // Calculate cart subtotal
        $cartItems = $cart->items()->with(['product', 'variant'])->get();
        $subtotal = $cartItems->sum(function($item) {
            return $item->quantity * $item->getPrice();
        });
        
        // Calculate discount with $100 cap
        $discount = 0;
        $maxDiscountCap = 100.00; // Hard cap at $100
        
        if ($promoCode->discount_type === 'percentage') {
            $discount = $subtotal * ($promoCode->discount_value / 100);
            // Cap at $100
            if ($discount > $maxDiscountCap) {
                $discount = $maxDiscountCap;
            }
        } else {
            $discount = $promoCode->discount_value;
            // Cap at $100
            if ($discount > $maxDiscountCap) {
                $discount = $maxDiscountCap;
            }
            // Discount should not exceed subtotal
            if ($discount > $subtotal) {
                $discount = $subtotal;
            }
        }
        
        // Apply discount to cart
        $cart->promo_code = $promoCode->code;
        $cart->promo_code_id = $promoCode->id;
        $cart->discount = $discount;
        $cart->save();
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Promo code applied successfully',
                'cart' => $cart->fresh()->load('items.product')
            ]);
        }
        
        return redirect()->back()->with('success', 'Promo code applied successfully');
    }
    
    /**
     * Remove a promo code from the cart
     */
    public function removePromoCode(Request $request)
    {
        $cart = $this->getOrCreateCart();
        $cart->removePromoCode();
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Promo code removed successfully',
                'cart' => $cart->fresh()->load('items.product')
            ]);
        }
        
        return redirect()->back()->with('success', 'Promo code removed successfully');
    }
    
    /**
     * Clear all items from the cart
     */
    public function clearCart(Request $request)
    {
        $cart = $this->getOrCreateCart();
        $cart->items()->delete();
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cart cleared successfully',
                'cart' => $cart->fresh()
            ]);
        }
        
        return redirect()->back()->with('success', 'Cart cleared successfully');
    }
    
    /**
     * Process full checkout process
     */    public function processCheckout(Request $request)
    {
        $validated = $request->validate([
            'billing_name' => 'required|string|max:255',
            'billing_email' => 'required|email|max:255',
            'billing_street' => 'required|string|max:255',
            'billing_city' => 'required|string|max:100',
            'billing_state' => 'required|string|max:100',
            'billing_postal_code' => 'required|string|max:20',
            'billing_country' => 'required|string|size:2',
            'billing_phone' => 'required|string|max:20',
            'shipping_street' => 'required_without:same_address|string|max:255|nullable',
            'shipping_city' => 'required_without:same_address|string|max:100|nullable',
            'shipping_state' => 'required_without:same_address|string|max:100|nullable',
            'shipping_postal_code' => 'required_without:same_address|string|max:20|nullable',
            'shipping_country' => 'required_without:same_address|string|size:2|nullable',
            'shipping_phone' => 'required_without:same_address|string|max:20|nullable',
            'payment_method' => 'required|in:credit_card,paypal,bank_transfer',
            'payment_details' => 'required|array',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            
            $cart = $this->getOrCreateCart();
            
            // Update cart status to checkout
            $cart->status = 'checkout';
            $cart->save();
            
            // Get cart items with products and inventory
            $cartItems = $cart->items()
                ->with(['product.inventory', 'variant.inventory'])
                ->get();
            
            // Check if cart is empty
            if ($cartItems->isEmpty()) {
                throw new \Exception('Your cart is empty');
            }
              // Verify stock availability for all items
            foreach ($cartItems as $item) {
                $inventoryItem = $item->variant_id ? 
                    $item->variant->inventory : 
                    $item->product->inventory->first();
                    
                if (!$inventoryItem || $inventoryItem->quantity < $item->quantity) {
                    throw new \Exception("Insufficient stock for {$item->product->name}");
                }
            }
            
            // Calculate order totals
            $totals = $cart->updateTotals();
            $shipping = 5.00; // Default shipping cost
            $total = $totals['total'] + $shipping;
            
            // Process payment
            $paymentResult = $this->processPayment(
                $total, 
                $validated['payment_method'],
                $validated['payment_details']
            );
            
            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['message']);
            }              // Create order
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'status' => 'processing',
                'shipping_street' => $request->has('same_address') ? $validated['billing_street'] : $validated['shipping_street'],
                'shipping_city' => $request->has('same_address') ? $validated['billing_city'] : $validated['shipping_city'],
                'shipping_state' => $request->has('same_address') ? $validated['billing_state'] : $validated['shipping_state'],
                'shipping_postal_code' => $request->has('same_address') ? $validated['billing_postal_code'] : $validated['shipping_postal_code'],
                'shipping_country' => $request->has('same_address') ? $validated['billing_country'] : $validated['shipping_country'],
                'billing_street' => $validated['billing_street'],
                'billing_city' => $validated['billing_city'],
                'billing_state' => $validated['billing_state'],
                'billing_postal_code' => $validated['billing_postal_code'],
                'billing_country' => $validated['billing_country'],
                'subtotal' => $totals['subtotal'],
                'shipping' => $shipping,
                'discount' => $totals['discount'],
                'total_amount' => $total,
                'order_date' => now(),
                'expected_delivery_date' => now()->addDays(7),
                'notes' => $validated['notes']
            ]);
              // Create order items and update inventory
            foreach ($cartItems as $item) {
                // Create order item
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price ?? $item->product->price,
                    'tax_rate' => 0.0000,
                    'tax_amount' => 0.00,
                    'discount_amount' => 0.00
                ]);
                
                // Update inventory
                if ($item->variant_id) {
                    // For variant products, get the variant inventory
                    $inventoryItem = $item->variant->inventory;
                    if ($inventoryItem) {
                        $inventoryItem->quantity -= $item->quantity;
                        $inventoryItem->save();
                    }
                } else {
                    // For regular products, get the first inventory record
                    $inventoryItem = $item->product->inventory()->first();
                    if ($inventoryItem) {
                        $inventoryItem->quantity -= $item->quantity;
                        $inventoryItem->save();
                    }
                }
            }
            
            // Create payment record
            $order->payment()->create([
                'payment_method' => $validated['payment_method'],
                'payment_processor' => $paymentResult['processor'],
                'transaction_id' => $paymentResult['transaction_id'],
                'amount' => $total,
                'status' => 'completed'
            ]);
              // Create shipment
            $order->shipments()->create([
                'status' => 'processing',
                'tracking_number' => 'TRK-' . strtoupper(Str::random(8)),
                'shipping_method' => 'standard',
                'actual_cost' => $shipping,
                'service_level' => 'standard',
                'delivery_time_days' => 7,
                'origin_country' => 'US',
                'destination_country' => $request->has('same_address') ? $validated['billing_country'] : $validated['shipping_country'],
                'destination_zip' => $request->has('same_address') ? $validated['billing_postal_code'] : $validated['shipping_postal_code'],
                'recipient_name' => $request->has('same_address') ? $validated['billing_name'] : ($validated['shipping_name'] ?? $validated['billing_name']),
                'recipient_phone' => $request->has('same_address') ? $validated['billing_phone'] : ($validated['shipping_phone'] ?? $validated['billing_phone']),
                'recipient_email' => $validated['billing_email'],
                'shipped_at' => now(),
                'carrier' => 'Standard Shipping'
            ]);
              // Record promo code usage if a promo code was applied
            if ($cart->promo_code_id) {
                $cart->recordPromoCodeUsage($order->id);
            }
            
            // Clear cart
            $cart->items()->delete();
            $cart->status = 'completed';
            $cart->promo_code = null;
            $cart->promo_code_id = null;
            $cart->discount = 0;
            $cart->save();
            
            DB::commit();
            
            // Return success response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order placed successfully',
                    'order' => $order->load(['items.product', 'shipments', 'payment'])
                ]);
            }
              return redirect()
                ->route('order.confirmation', ['order' => $order->id])
                ->with('success', 'Your order has been placed successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return redirect()
                ->back()
                ->with('error', $e->getMessage())                ->withInput();
        }
    }
    
    /**
     * Process payment for an order
     */
    private function processPayment($amount, $method, $details)
    {
        // This is a simulation for demonstration purposes
        // In a production environment, you would integrate with a real payment gateway
        
        switch ($method) {
            case 'credit_card':
                // Validate credit card details
                if (empty($details['card_number']) || empty($details['expiry_date']) || empty($details['cvv'])) {
                    return [
                        'success' => false,
                        'message' => 'Missing credit card details'
                    ];
                }
                
                // Here you would make an API call to your payment processor
                return [
                    'success' => true,
                    'processor' => 'stripe',
                    'transaction_id' => 'sim_' . Str::random(24),
                    'message' => 'Payment processed successfully'
                ];
                
            case 'paypal':
                // Integration with PayPal would go here
                return [
                    'success' => true,
                    'processor' => 'paypal',
                    'transaction_id' => 'sim_' . Str::random(24),
                    'message' => 'Payment processed successfully'
                ];
                
            case 'bank_transfer':
                // Bank transfer processing would go here
                return [
                    'success' => true,
                    'processor' => 'bank',
                    'transaction_id' => 'sim_' . Str::random(24),
                    'message' => 'Bank transfer initiated successfully'
                ];
                
            default:
                return [
                    'success' => false,
                    'message' => 'Unsupported payment method'
                ];
        }
    }
}