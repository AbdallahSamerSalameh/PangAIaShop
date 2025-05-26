<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CartFixController extends Controller
{
    /**
     * Fix zero quantity items in the cart
     */
    public function fixZeroQuantityItems(Request $request)
    {
        // Find all users with carts
        $userIds = Cart::distinct()->pluck('user_id');
        $result = [];
        
        foreach ($userIds as $userId) {
            if (!$userId) continue;
            
            // Find all carts for this user
            $carts = Cart::where('user_id', $userId)->get();
            
            foreach ($carts as $cart) {
                // Clean up the direct cart entry (from carts table)
                if ($cart->quantity === 0) {
                    $cart->deleted_at = now();
                    $cart->save();
                    $result[] = "Updated cart {$cart->id} for user {$userId}";
                }
                
                // Clean up cart items with zero quantity
                $zeroItems = CartItem::where('cart_id', $cart->id)
                    ->where('quantity', 0)
                    ->get();
                    
                foreach ($zeroItems as $item) {
                    $item->forceDelete();
                    $result[] = "Deleted cart item {$item->id} for cart {$cart->id}";
                }
            }
        }
        
        // Special fix for the problematic H&M product (ID 1)
        $deleted = CartItem::where('product_id', 1)
            ->where('quantity', 0)
            ->forceDelete();
            
        $result[] = "Deleted {$deleted} H&M product items";
        
        $cartsFixed = Cart::where('product_id', 1)
            ->where('quantity', 0)
            ->update(['deleted_at' => now()]);
            
        $result[] = "Fixed {$cartsFixed} H&M product carts";
        
        return response()->json([
            'success' => true,
            'message' => 'Cart cleanup completed',
            'results' => $result
        ]);
    }
    
    /**
     * Create a proper cart structure without the placeholder product
     */
    public function fixCartStructure(Request $request)
    {
        // Find all carts that have direct product_id reference
        $carts = Cart::whereNotNull('product_id')->get();
        $result = [];
        
        foreach ($carts as $cart) {
            // Remove the direct product reference
            $productId = $cart->product_id;
            $quantity = $cart->quantity;
            
            // Only create a cart item if quantity > 0
            if ($quantity > 0) {
                // Check if there's already a cart item for this product
                $existingItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_id', $productId)
                    ->first();
                    
                if (!$existingItem) {
                    // Create a proper cart item
                    $product = Product::find($productId);
                    if ($product) {
                        CartItem::create([
                            'cart_id' => $cart->id,
                            'product_id' => $productId,
                            'quantity' => $quantity,
                            'unit_price' => $product->price
                        ]);
                        $result[] = "Created cart item for product {$productId} in cart {$cart->id}";
                    }
                }
            }
            
            // Remove the direct product reference from the cart
            $cart->product_id = null;
            $cart->quantity = null;
            $cart->save();
            $result[] = "Removed direct product reference from cart {$cart->id}";
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Cart structure fixed',
            'results' => $result
        ]);
    }
}
