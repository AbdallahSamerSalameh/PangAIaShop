<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItem extends Model
{
    use HasFactory, SoftDeletes;
      protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'unit_price'
    ];
    
    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer'
    ];
    
    // Track if inventory has been released to prevent double-release
    protected $inventoryReleased = false;/**
     * The "booted" method of the model.
     *
     * @return void
     */    protected static function booted()
    {
        // Reserve inventory when cart item is created
        static::created(function ($cartItem) {
            $cartItem->reserveInventory();
        });
        
        // Handle inventory reservation when cart item quantity is updated
        static::updating(function ($cartItem) {
            if ($cartItem->exists && $cartItem->isDirty('quantity')) {
                $oldQuantity = $cartItem->getOriginal('quantity');
                $newQuantity = $cartItem->quantity;
                
                if ($newQuantity <= 0) {
                    // Release all reserved inventory before deletion
                    $cartItem->releaseInventory($oldQuantity);
                    $cartItem->forceDelete();
                    return true;
                } else if ($oldQuantity !== $newQuantity) {
                    // Update inventory reservation
                    $cartItem->updateInventoryReservation($oldQuantity, $newQuantity);
                }
            }
            return true;
        });        // Release inventory when cart item is deleted
        static::deleting(function ($cartItem) {
            // Only release if not already released
            if (!$cartItem->inventoryReleased) {
                // Load the product and variant relationships if not already loaded
                if (!$cartItem->relationLoaded('product')) {
                    $cartItem->load('product.inventory');
                }
                if ($cartItem->variant_id && !$cartItem->relationLoaded('variant')) {
                    $cartItem->load('variant.inventory');
                }
                
                $cartItem->releaseInventory();
            }
        });
    }
    
    public function cart()
    {
        return $this->belongsTo(Cart::class)->withTrashed();
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
    
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
      /**
     * Get the actual price to use for this cart item, considering sale prices
     * 
     * @return float
     */
    public function getPrice()
    {
        // If this is a variant, check variant price first
        if ($this->variant_id) {
            $variant = $this->variant;
            if ($variant && $variant->price) {
                // Even for variants, prefer the unit_price if it was specifically set at cart creation
                // This allows for sale prices to be honored even for variants
                if ($this->unit_price && $this->unit_price < $variant->price) {
                    return $this->unit_price;
                }
                
                return $variant->price;
            }
        }
        
        $product = $this->product;
        
        // If unit_price is already set and it's the sale price, use it
        if ($this->unit_price) {
            // If there's a sale price and the unit_price matches it, return unit_price
            if ($product->sale_price && $product->sale_price > 0 && $this->unit_price <= $product->sale_price) {
                return $this->unit_price;
            }
            // If there's no sale price, use the unit_price
            if (!$product->sale_price || $product->sale_price == 0) {
                return $this->unit_price;
            }
        }
        
        // Use product sale price if available, otherwise use regular price
        if ($product->sale_price && $product->sale_price > 0) {
            return $product->sale_price;
        }
        
        return $product->price;
    }
    
    /**
     * Calculate the subtotal for this cart item
     * 
     * @return float
     */
    public function getSubtotal()
    {
        return $this->quantity * $this->getPrice();
    }
    
    /**
     * Reserve inventory for this cart item
     */
    public function reserveInventory($quantity = null)
    {
        $quantity = $quantity ?? $this->quantity;
        $inventory = $this->getInventory();
        
        if ($inventory) {
            return $inventory->reserveQuantity($quantity);
        }
        return false;
    }
      /**
     * Release reserved inventory for this cart item
     */
    public function releaseInventory($quantity = null)
    {
        $quantity = $quantity ?? $this->quantity;
        $inventory = $this->getInventory();
        
        if ($inventory && !$this->inventoryReleased) {
            $result = $inventory->releaseReservedQuantity($quantity);
            if ($result) {
                $this->inventoryReleased = true;
            }
            return $result;
        }
        return false;
    }
    
    /**
     * Update inventory reservation when quantity changes
     */
    public function updateInventoryReservation($oldQuantity, $newQuantity)
    {
        $inventory = $this->getInventory();
        
        if ($inventory) {
            $difference = $newQuantity - $oldQuantity;
            
            if ($difference > 0) {
                // Need to reserve more
                return $inventory->reserveQuantity($difference);
            } else if ($difference < 0) {
                // Need to release some
                return $inventory->releaseReservedQuantity(abs($difference));
            }
        }
        return true;
    }
    
    /**
     * Get the inventory for this cart item
     */
    public function getInventory()
    {
        if ($this->variant_id) {
            return $this->variant ? $this->variant->inventory : null;
        } else {
            return $this->product ? $this->product->inventory : null;
        }
    }
}