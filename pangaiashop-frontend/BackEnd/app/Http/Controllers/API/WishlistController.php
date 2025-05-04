<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    /**
     * Display the user's wishlist.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $wishlist = Wishlist::firstOrCreate(['user_id' => $user->id]);
        
        $wishlist->load(['items.product.images']);
        
        return response()->json([
            'success' => true,
            'data' => $wishlist
        ]);
    }

    /**
     * Add a product to the wishlist.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToWishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $wishlist = Wishlist::firstOrCreate(['user_id' => $user->id]);
        
        $product = Product::findOrFail($request->product_id);
        
        // Check if the product is already in the wishlist
        $wishlistItem = $wishlist->items()->where('product_id', $product->id)->first();
        
        if ($wishlistItem) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in wishlist',
            ], 400);
        } else {
            // Add new wishlist item
            $wishlistItem = new WishlistItem([
                'product_id' => $product->id,
            ]);
            
            $wishlist->items()->save($wishlistItem);
        }
        
        $wishlist->load(['items.product.images']);
        
        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist',
            'data' => $wishlist
        ]);
    }

    /**
     * Remove an item from the wishlist.
     *
     * @param Request $request
     * @param WishlistItem $wishlistItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromWishlist(Request $request, WishlistItem $wishlistItem)
    {
        $user = $request->user();
        $wishlist = Wishlist::where('user_id', $user->id)->firstOrFail();
        
        // Ensure the wishlist item belongs to the user's wishlist
        if ($wishlistItem->wishlist_id !== $wishlist->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $wishlistItem->delete();
        
        $wishlist->load(['items.product.images']);
        
        return response()->json([
            'success' => true,
            'message' => 'Item removed from wishlist',
            'data' => $wishlist
        ]);
    }

    /**
     * Clear the wishlist.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearWishlist(Request $request)
    {
        $user = $request->user();
        $wishlist = Wishlist::where('user_id', $user->id)->firstOrFail();
        
        $wishlist->items()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Wishlist cleared',
            'data' => $wishlist
        ]);
    }

    /**
     * Move a wishlist item to cart.
     *
     * @param Request $request
     * @param WishlistItem $wishlistItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function moveToCart(Request $request, WishlistItem $wishlistItem)
    {
        $user = $request->user();
        $wishlist = Wishlist::where('user_id', $user->id)->firstOrFail();
        
        // Ensure the wishlist item belongs to the user's wishlist
        if ($wishlistItem->wishlist_id !== $wishlist->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $cart = app(CartController::class);
        
        // Create a new request with the product id and quantity
        $cartRequest = new Request([
            'product_id' => $wishlistItem->product_id,
            'quantity' => 1
        ]);
        
        // Set the user for the new request
        $cartRequest->setUserResolver(function() use ($user) {
            return $user;
        });
        
        // Add the product to cart
        $response = $cart->addToCart($cartRequest);
        
        // If the product was added to cart successfully, remove it from wishlist
        if ($response->getStatusCode() === 200) {
            $wishlistItem->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Item moved to cart',
                'data' => json_decode($response->getContent())
            ]);
        }
        
        return $response;
    }
}