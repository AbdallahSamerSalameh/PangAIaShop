<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CascadeSoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'sku',
        'vendor_id',
        'created_by',
        'updated_by',
        'status',
        'weight',
        'dimensions',
        'warranty_info',
        'return_policy'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relations
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function priceHistory()
    {
        return $this->hasMany(PriceHistory::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Define which relationships should cascade on delete.
     *
     * @return array
     */
    public function getCascadeDeletes(): array
    {
        return [
            'variants',
            'images',
            'inventory',
            'priceHistory',
            'cartItems',
            'wishlistItems'
        ];
    }

    /*
    protected static function boot()
    {
        parent::boot();

        // When a product is soft deleted, cascade to related entities
        static::deleting(function($product) {
            // Only cascade if this is a soft delete operation
            if (!$product->isForceDeleting()) {
                // Cascade soft delete to related entities
                $product->variants()->each(function($variant) {
                    $variant->delete();
                });
                
                $product->images()->each(function($image) {
                    $image->delete();
                });
                
                $product->inventory()->each(function($inventory) {
                    $inventory->delete();
                });
                
                $product->priceHistory()->each(function($priceHistory) {
                    $priceHistory->delete();
                });
                
                $product->cartItems()->each(function($cartItem) {
                    $cartItem->delete();
                });
                
                $product->wishlistItems()->each(function($wishlistItem) {
                    $wishlistItem->delete();
                });
                
                // We don't delete reviews when products are deleted
                // to preserve customer feedback history
                
                // We don't delete order items as they represent completed orders
                // with financial implications
            }
        });

        // When a product is restored, cascade restore to related entities
        static::restored(function($product) {
            // Restore related entities that were soft deleted with this product
            $product->variants()->onlyTrashed()->each(function($variant) {
                $variant->restore();
            });
            
            $product->images()->onlyTrashed()->each(function($image) {
                $image->restore();
            });
            
            $product->inventory()->onlyTrashed()->each(function($inventory) {
                $inventory->restore();
            });
            
            $product->priceHistory()->onlyTrashed()->each(function($priceHistory) {
                $priceHistory->restore();
            });
            
            $product->cartItems()->onlyTrashed()->each(function($cartItem) {
                $cartItem->restore();
            });
            
            $product->wishlistItems()->onlyTrashed()->each(function($wishlistItem) {
                $wishlistItem->restore();
            });
        });
    }
    */
}
