<?php

namespace App\Http\Controllers;

use App\Services\GuestCartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Cart;

class GuestCartController extends Controller
{
    protected $guestCartService;
    
    public function __construct(GuestCartService $guestCartService)
    {
        $this->guestCartService = $guestCartService;
    }
    
    /**
     * Display the guest cart
     */
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('cart.index');
        }
        
        $cart = $this->guestCartService->getCart();
        $products = [];
        
        // Get product details for each cart item
        foreach ($cart as $item) {
            $product = Product::with('images')->find($item['product_id']);
            if ($product) {
                $productData = $product->toArray();
                $productData['cart_quantity'] = $item['quantity'];
                $productData['cart_item_id'] = $item['id'];
                $products[] = $productData;
            }
        }
        
        return view('guest.cart', [
            'products' => $products,
            'cartTotal' => $this->calculateCartTotal($products),
        ]);
    }
    
    /**
     * Add a product to the guest cart
     */
    public function addToCart(Request $request)
    {
        if (Auth::check()) {
            // Redirect to regular cart controller if user is logged in
            return redirect()->route('cart.add');
        }
        
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'variant_id' => 'nullable|exists:product_variants,id',
        ]);
        
        $this->guestCartService->addToCart(
            $validated['product_id'],
            $validated['quantity'],
            $validated['variant_id'] ?? null
        );
          if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully',
                'cart_count' => count($this->guestCartService->getCart()),
                'cart' => $this->guestCartService->getCart()
            ]);
        }
        
        return redirect()->back()->with('success', 'Product added to cart successfully');
    }
    
    /**
     * Remove an item from the guest cart
     */
    public function removeFromCart(Request $request, $itemId)
    {
        if (Auth::check()) {
            return redirect()->route('cart.remove', $itemId);
        }
        
        $this->guestCartService->removeFromCart($itemId);
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Item removed from cart',
                'cart' => $this->guestCartService->getCart()
            ]);
        }
        
        return redirect()->back()->with('success', 'Item removed from cart successfully');
    }
    
    /**
     * Update the quantity of an item in the guest cart
     */
    public function updateQuantity(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('cart.update');
        }
        
        $validated = $request->validate([
            'item_id' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);
        
        $this->guestCartService->updateCartItemQuantity(
            $validated['item_id'],
            $validated['quantity']
        );
        
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cart updated',
                'cart' => $this->guestCartService->getCart()
            ]);
        }
        
        return redirect()->back()->with('success', 'Cart updated successfully');
    }
    
    /**
     * Calculate the cart total
     */    protected function calculateCartTotal($products)
    {
        $total = 0;
        
        foreach ($products as $product) {
            // Use sale_price if available
            $price = ($product['sale_price'] && $product['sale_price'] > 0) ? 
                $product['sale_price'] : 
                $product['price'];
            
            $total += $price * $product['cart_quantity'];
        }
        
        return $total;
    }
    
    /**
     * Transfer guest cart to user cart when logging in
     */
    public function transferCart($userId)
    {
        $guestCart = $this->guestCartService->getCart();
        
        foreach ($guestCart as $item) {
            // Check if product exists and is in stock
            $product = Product::find($item['product_id']);
            
            if ($product && $product->stock >= $item['quantity']) {
                // Add to user's cart
                Cart::create([
                    'user_id' => $userId,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                ]);
            }
        }
        
        // Clear the guest cart
        $this->guestCartService->clearCart();
    }
}
