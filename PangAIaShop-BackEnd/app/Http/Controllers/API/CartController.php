<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Display the user's cart.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        
        $cart->load(['items.product.images', 'items.variant']);
        
        // Calculate cart totals
        $cart->updateTotals();
        
        return response()->json([
            'success' => true,
            'data' => $cart
        ]);
    }

    /**
     * Add a product to the cart.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        
        $product = Product::findOrFail($request->product_id);
        $variant = null;
        
        if ($request->variant_id) {
            $variant = ProductVariant::findOrFail($request->variant_id);
            
            // Ensure variant belongs to the product
            if ($variant->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Variant does not belong to this product',
                ], 400);
            }
        }
        
        // Check if the product (and variant) is already in the cart
        $cartItem = $cart->items()
            ->where('product_id', $product->id)
            ->where('variant_id', $request->variant_id)
            ->first();
        
        if ($cartItem) {
            // Update existing cart item quantity
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            // Add new cart item
            $price = $variant ? $variant->price : $product->price;
            
            $cartItem = new CartItem([
                'product_id' => $product->id,
                'variant_id' => $request->variant_id,
                'quantity' => $request->quantity,
                'price' => $price,
            ]);
            
            $cart->items()->save($cartItem);
        }
        
        // Check inventory and adjust quantity if needed
        $this->checkAndAdjustInventory($cartItem);
        
        // Update cart totals
        $cart->updateTotals();
        
        $cart->load(['items.product.images', 'items.variant']);
        
        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'data' => $cart
        ]);
    }

    /**
     * Update a cart item's quantity.
     *
     * @param Request $request
     * @param CartItem $cartItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCartItem(Request $request, CartItem $cartItem)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        
        // Ensure the cart item belongs to the user's cart
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        // Update cart item quantity
        $cartItem->quantity = $request->quantity;
        $cartItem->save();
        
        // Check inventory and adjust quantity if needed
        $this->checkAndAdjustInventory($cartItem);
        
        // Update cart totals
        $cart->updateTotals();
        
        $cart->load(['items.product.images', 'items.variant']);
        
        return response()->json([
            'success' => true,
            'message' => 'Cart item updated',
            'data' => $cart
        ]);
    }

    /**
     * Remove an item from the cart.
     *
     * @param Request $request
     * @param CartItem $cartItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromCart(Request $request, CartItem $cartItem)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        
        // Ensure the cart item belongs to the user's cart
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $cartItem->delete();
        
        // Update cart totals
        $cart->updateTotals();
        
        $cart->load(['items.product.images', 'items.variant']);
        
        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'data' => $cart
        ]);
    }

    /**
     * Clear the cart.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCart(Request $request)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        
        $cart->items()->delete();
        
        // Update cart totals
        $cart->updateTotals();
        
        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
            'data' => $cart
        ]);
    }

    /**
     * Apply a promo code to the cart.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyPromoCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|exists:promo_codes,code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        
        $promoCode = \App\Models\PromoCode::where('code', $request->code)
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->first();
        
        if (!$promoCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired promo code',
            ], 400);
        }
        
        // Apply promo code to cart
        $cart->promo_code = $promoCode->code;
        $cart->save();
        
        // Update cart totals with discount
        $cart->updateTotals();
        
        $cart->load(['items.product.images', 'items.variant']);
        
        return response()->json([
            'success' => true,
            'message' => 'Promo code applied',
            'data' => $cart
        ]);
    }

    /**
     * Remove the promo code from the cart.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removePromoCode(Request $request)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        
        // Remove promo code from cart
        $cart->promo_code = null;
        $cart->save();
        
        // Update cart totals
        $cart->updateTotals();
        
        $cart->load(['items.product.images', 'items.variant']);
        
        return response()->json([
            'success' => true,
            'message' => 'Promo code removed',
            'data' => $cart
        ]);
    }

    /**
     * Check inventory and adjust quantity if needed.
     *
     * @param CartItem $cartItem
     * @return void
     */
    private function checkAndAdjustInventory(CartItem $cartItem)
    {
        $product = $cartItem->product;
        $inventory = $product->inventory;
        
        if ($inventory && $cartItem->quantity > $inventory->stock) {
            $cartItem->quantity = $inventory->stock;
            $cartItem->save();
        }
    }
}