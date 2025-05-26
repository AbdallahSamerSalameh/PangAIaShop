<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'product_id',
        'variant_id',
        'quantity',
        'carts_expiry',
        'promo_code',
        'discount',
        'promo_code_id',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'carts_expiry' => 'datetime',
        'deleted_at' => 'datetime',
        'status' => 'string'
    ];
    
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Only mark carts with zero quantity as deleted during updates, not creation
        static::updating(function ($cart) {
            if ($cart->isDirty('quantity') && $cart->quantity === 0 && $cart->deleted_at === null) {
                $cart->deleted_at = now();
            }
            // Always return true to allow the operation to continue
            return true;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
    
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    // Helper methods for cart operations
    
    /**
     * Calculate and update cart totals
     */
    public function updateTotals()
    {
        $items = $this->items()->with(['product', 'variant'])->get();
        
        $subtotal = $items->sum(function($item) {
            return $item->quantity * $item->getPrice();
        });
        
        $discount = $this->discount ?: 0;
        
        // Check if we need to recalculate the discount for percentage based promo codes
        if ($this->promo_code_id) {
            $promoCode = $this->promo_code_id ? PromoCode::find($this->promo_code_id) : null;
            if ($promoCode) {
                $discount = $promoCode->calculateDiscount($subtotal);
                $this->discount = $discount;
                $this->save();
            }
        }
        
        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => max(0, $subtotal - $discount)
        ];
    }
    
    /**
     * Update cart expiry date
     */
    public function updateExpiry()
    {
        $this->carts_expiry = now()->addDays(7);
        $this->save();
    }
    
    /**
     * Check if cart is empty
     */
    public function isEmpty()
    {
        return $this->items()->count() === 0;
    }
    
    /**
     * Get active cart items (non-deleted)
     */
    public function activeItems()
    {
        return $this->items()->whereNull('deleted_at');
    }
    
    /**
     * Clean up any items with zero quantity
     */
    public function cleanZeroQuantityItems()
    {
        $this->items()->where('quantity', 0)->delete();
    }
    
    /**
     * Apply a promo code to the cart
     * 
     * @param string $code The promo code to apply
     * @return bool Whether the promo code was successfully applied
     */
    public function applyPromoCode($code)
    {
        // Find the promo code
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
            return false;
        }
        
        // Check global usage limits
        if ($promoCode->max_uses && $promoCode->usages()->count() >= $promoCode->max_uses) {
            return false;
        }
        
        // Check per-user limit (max 3 times per user)
        if ($this->user_id) {
            $userUsageCount = $promoCode->getUserUsageCount($this->user_id);
            if ($userUsageCount >= 3) {
                return false;
            }
        }
        
        // Calculate cart subtotal
        $cartItems = $this->items()->with('product')->get();
        $subtotal = $cartItems->sum(function($item) {
            return $item->quantity * $item->getPrice();
        });
        
        // Check minimum order amount
        if ($promoCode->min_order_amount && $subtotal < $promoCode->min_order_amount) {
            return false;
        }
        
        // Calculate discount with $100 cap
        $discount = $promoCode->calculateDiscount($subtotal);
        
        // Apply discount to cart
        $this->promo_code = $promoCode->code;
        $this->promo_code_id = $promoCode->id;
        $this->discount = $discount;
        $this->save();
        
        return true;
    }
    
    /**
     * Remove the promo code from the cart
     */
    public function removePromoCode()
    {
        $this->promo_code = null;
        $this->promo_code_id = null;
        $this->discount = 0;
        $this->save();
        
        return true;
    }
    
    /**
     * Record the usage of a promo code by this user
     */
    public function recordPromoCodeUsage($orderId = null)
    {
        if (!$this->promo_code_id || !$this->user_id) {
            return;
        }
        
        PromoCodeUsage::create([
            'user_id' => $this->user_id,
            'promo_code_id' => $this->promo_code_id,
            'order_id' => $orderId,
            'discount_amount' => $this->discount,
            'used_at' => now()
        ]);
    }
}
