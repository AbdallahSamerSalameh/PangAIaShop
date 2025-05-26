<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class GuestCartService
{
    /**
     * Add an item to the guest cart
     * 
     * @param int $productId
     * @param int $quantity
     * @param int|null $variantId
     * @return array The updated cart
     */
    public function addToCart($productId, $quantity = 1, $variantId = null)
    {
        $cart = Session::get('guest_cart', []);
        
        // Generate a unique cart item ID
        $itemId = 'item_' . uniqid();
        
        // Check if product already exists in cart
        $existingItemIndex = $this->findCartItemIndex($cart, $productId, $variantId);
        
        if ($existingItemIndex !== false) {
            // Update quantity if product already exists
            $cart[$existingItemIndex]['quantity'] += $quantity;
        } else {
            // Add new product to cart
            $cart[] = [
                'id' => $itemId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'added_at' => now()->toDateTimeString(),
            ];
        }
        
        Session::put('guest_cart', $cart);
        return $cart;
    }
    
    /**
     * Remove an item from the guest cart
     * 
     * @param string $itemId
     * @return array The updated cart
     */
    public function removeFromCart($itemId)
    {
        $cart = Session::get('guest_cart', []);
        
        // Find item in cart and remove it
        foreach ($cart as $index => $item) {
            if ($item['id'] === $itemId) {
                unset($cart[$index]);
                break;
            }
        }
        
        // Re-index array
        $cart = array_values($cart);
        
        Session::put('guest_cart', $cart);
        return $cart;
    }
    
    /**
     * Update the quantity of an item in the guest cart
     * 
     * @param string $itemId
     * @param int $quantity
     * @return array The updated cart
     */
    public function updateCartItemQuantity($itemId, $quantity)
    {
        $cart = Session::get('guest_cart', []);
        
        foreach ($cart as $index => $item) {
            if ($item['id'] === $itemId) {
                $cart[$index]['quantity'] = $quantity;
                break;
            }
        }
        
        Session::put('guest_cart', $cart);
        return $cart;
    }
    
    /**
     * Get the guest cart
     * 
     * @return array
     */
    public function getCart()
    {
        return Session::get('guest_cart', []);
    }
    
    /**
     * Clear the guest cart
     * 
     * @return void
     */
    public function clearCart()
    {
        Session::forget('guest_cart');
    }
    
    /**
     * Find a cart item by product ID and variant ID
     * 
     * @param array $cart
     * @param int $productId
     * @param int|null $variantId
     * @return int|bool The index of the item or false if not found
     */
    protected function findCartItemIndex($cart, $productId, $variantId)
    {
        foreach ($cart as $index => $item) {
            if ($item['product_id'] == $productId && $item['variant_id'] == $variantId) {
                return $index;
            }
        }
        
        return false;
    }
}
