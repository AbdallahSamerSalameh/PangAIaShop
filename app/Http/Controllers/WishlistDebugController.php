<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Auth;
use Illuminate\Support\Facades\Log;

class WishlistDebugController extends Controller
{
    /**
     * Display wishlist debug information
     * 
     * @return \Illuminate\Http\Response
     */
    public function debug()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'User not logged in'], 401);
        }
        
        try {
            $user = Auth::user();
            $wishlist = Wishlist::where('user_id', $user->id)->first();
            
            if (!$wishlist) {
                return response()->json([
                    'wishlist_exists' => false,
                    'items' => [],
                    'counts' => [
                        'database_count' => 0,
                    ],
                    'message' => 'No wishlist found for this user'
                ]);
            }
            
            // Get wishlist items from database
            $wishlistItems = WishlistItem::where('wishlist_id', $wishlist->id)
                ->with('product')
                ->get();
            
            $itemsData = $wishlistItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? 'Product not found',
                    'added_at' => $item->created_at->format('Y-m-d H:i:s'),
                ];
            });
            
            return response()->json([
                'wishlist_exists' => true,
                'wishlist_id' => $wishlist->id,
                'items' => $itemsData,
                'counts' => [
                    'database_count' => $wishlistItems->count(),
                ],
                'message' => 'Wishlist data retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Wishlist debug error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
