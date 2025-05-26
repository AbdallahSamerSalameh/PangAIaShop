<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoCode extends Model
{
    use HasFactory, SoftDeletes;
    
    // Turn off Laravel's automatic timestamps since our table only has created_at
    public $timestamps = false;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_uses',
        'target_audience',
        'valid_from',
        'valid_until',
        'is_active',
        'created_by',
        'created_at'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_uses' => 'integer',
        'target_audience' => 'array',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
    
    /**
     * Get all usages of this promo code
     */
    public function usages()
    {
        return $this->hasMany(PromoCodeUsage::class);
    }
    
    /**
     * Get the number of times this user has used this promo code
     */
    public function getUserUsageCount($userId)
    {
        return $this->usages()->where('user_id', $userId)->count();
    }
    
    /**
     * Check if a user has reached their usage limit for this promo code
     */
    public function hasReachedUserLimit($userId)
    {
        // Hardcoded limit of 3 uses per user as per requirements
        $maxUsesPerUser = 3;
        
        return $this->getUserUsageCount($userId) >= $maxUsesPerUser;
    }
    
    /**
     * Calculate discount amount based on subtotal with maximum cap of $100
     */
    public function calculateDiscount($subtotal)
    {
        $discount = 0;
        $maxDiscountCap = 100.00; // Hard cap at $100
        
        if ($this->discount_type === 'percentage') {
            $discount = $subtotal * ($this->discount_value / 100);
            
            // Apply $100 cap
            if ($discount > $maxDiscountCap) {
                $discount = $maxDiscountCap;
            }
        } else { // Fixed amount
            $discount = $this->discount_value;
            
            // Apply $100 cap
            if ($discount > $maxDiscountCap) {
                $discount = $maxDiscountCap;
            }
        }
        
        // Apply the $100 cap
        if ($discount > $maxDiscountCap) {
            $discount = $maxDiscountCap;
        }
        
        // Discount should not exceed the subtotal
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }
        
        return $discount;
    }
}
