<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CascadeSoftDeletes;

class Vendor extends Authenticatable
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $fillable = [
        'name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'address',
        'website',
        'tax_id',
        'managed_by',
        'payment_terms',
        'status',
        'rating'
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function managedBy()
    {
        return $this->belongsTo(Admin::class, 'managed_by');
    }

    /**
     * Define which relationships should cascade on delete.
     *
     * @return array
     */
    public function getCascadeDeletes(): array
    {
        return [
            'products'
        ];
    }

    /*
    protected static function boot()
    {
        parent::boot();

        // When a vendor is soft deleted, cascade to related entities
        static::deleting(function($vendor) {
            // Only cascade if this is a soft delete operation
            if (!$vendor->isForceDeleting()) {
                // Cascade soft delete to related products
                $vendor->products()->each(function($product) {
                    $product->delete();
                });
            }
        });

        // When a vendor is restored, cascade restore to related entities
        static::restored(function($vendor) {
            // Restore related products that were soft deleted with this vendor
            $vendor->products()->onlyTrashed()->each(function($product) {
                $product->restore();
            });
        });
    }
    */
}
