<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display a listing of the user's wishlist items.
     *
     * @return \Illuminate\View\View
     */    public function index()
    {
        $user = Auth::user();
        
        // Get or create user's wishlist
        $wishlist = Wishlist::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => 'My Wishlist']
        );
        
        // Get wishlist items with product details
        $wishlistItems = $wishlist->items()
            ->with(['product.images' => function($query) {
                $query->where('is_primary', true);
            }, 'product.categories', 'product.inventory'])
            ->get();
            
        // Transform the collection to add featured image
        $wishlistItems = $wishlistItems->map(function($item) {
            $product = $item->product;
            
            $product->featured_image = $product->images->first() 
                ? $product->images->first()->image_url 
                : 'assets/img/products/product-img-1.jpg';
                
            // Set category names for display
            if ($product->categories->count() > 0) {
                $product->category_names = $product->categories->pluck('name');
            } else {
                $product->category_names = collect(['Uncategorized']);
            }
                
            return $item;
        });
        
        return view('frontend.user.wishlist', [
            'wishlistItems' => $wishlistItems
        ]);
    }
    
    /**
     * Add a product to the user's wishlist.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);
        
        $user = Auth::user();
        $productId = $request->product_id;
        
        // Get or create user's wishlist
        $wishlist = Wishlist::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => 'My Wishlist']
        );
        
        // Check if product already exists in wishlist
        $existingItem = WishlistItem::where('wishlist_id', $wishlist->id)
            ->where('product_id', $productId)
            ->first();
            
        if (!$existingItem) {
            // Add product to wishlist
            WishlistItem::create([
                'wishlist_id' => $wishlist->id,
                'product_id' => $productId,
                'added_at' => now()
            ]);
            
            $message = 'Product added to your wishlist!';
        } else {
            $message = 'Product is already in your wishlist!';
        }
        
        // Return appropriate response based on request type
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }
        
        return redirect()->back()->with('success', $message);
    }
    
    /**
     * Remove an item from the user's wishlist.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);
        
        $user = Auth::user();
        $productId = $request->product_id;
        
        // Find user's wishlist
        $wishlist = Wishlist::where('user_id', $user->id)->first();
        
        if ($wishlist) {
            // Delete wishlist item
            WishlistItem::where('wishlist_id', $wishlist->id)
                ->where('product_id', $productId)
                ->delete();
        }
        
        // Return appropriate response based on request type
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product removed from your wishlist!'
            ]);
        }
        
        return redirect()->back()->with('success', 'Product removed from your wishlist!');
    }
    
    /**
     * Clear all items from the user's wishlist.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clear()
    {
        $user = Auth::user();
        
        // Find user's wishlist
        $wishlist = Wishlist::where('user_id', $user->id)->first();
        
        if ($wishlist) {
            // Delete all wishlist items
            WishlistItem::where('wishlist_id', $wishlist->id)->delete();
        }
        
        return redirect()->route('wishlist')->with('success', 'Your wishlist has been cleared!');
    }
      /**
     * Move all items from wishlist to cart.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function moveToCart()
    {
        $user = Auth::user();
        
        // Find user's wishlist
        $wishlist = Wishlist::where('user_id', $user->id)->first();
        
        if (!$wishlist) {
            return redirect()->route('wishlist')->with('error', 'Your wishlist is empty!');
        }
        
        // Get all wishlist items
        $wishlistItems = WishlistItem::where('wishlist_id', $wishlist->id)
            ->with('product')
            ->get();
            
        if ($wishlistItems->isEmpty()) {
            return redirect()->route('wishlist')->with('error', 'Your wishlist is empty!');
        }
        
        // Get or create user's cart
        $cart = app(CartController::class)->getOrCreateCart();
        
        // Add wishlist items to cart
        foreach ($wishlistItems as $item) {            // Check if product is in stock
            $productInventory = $item->product->inventory->first();
            $stockQuantity = $productInventory ? $productInventory->quantity : 0;
            
            if ($stockQuantity > 0) {
                // Check if product already exists in cart
                $cartItem = $cart->items()->where('product_id', $item->product_id)->first();
                
                if ($cartItem) {
                    // Update quantity
                    $cartItem->quantity += 1;
                    $cartItem->save();
                } else {
                    // Add new cart item
                    $cart->items()->create([
                        'product_id' => $item->product_id,
                        'quantity' => 1,
                        'price' => $item->product->price
                    ]);
                }
            }
        }
        
        return redirect()->route('cart')->with('success', 'All available products from your wishlist have been added to cart!');
    }

    /**
     * Check if a product is in the user's wishlist.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);
        
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'inWishlist' => false
            ]);
        }
        
        $productId = $request->product_id;
        
        // Find user's wishlist
        $wishlist = Wishlist::where('user_id', $user->id)->first();
        
        $inWishlist = false;
        
        if ($wishlist) {
            // Check if product exists in wishlist
            $existingItem = WishlistItem::where('wishlist_id', $wishlist->id)
                ->where('product_id', $productId)
                ->first();
                
            $inWishlist = $existingItem ? true : false;
        }
        
        return response()->json([
            'inWishlist' => $inWishlist
        ]);
    }
}