<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CascadeSoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes, HasApiTokens;

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'role',
        'avatar_url',
        'phone_number',
        'two_factor_secret',
        'last_password_change',
        'failed_login_count',
        'last_login',
        'created_by',
        'is_active',
        'two_factor_verified',
        'two_factor_method',
        'backup_codes',
        'two_factor_enabled_at',
        'two_factor_expires_at'
    ];

    protected $hidden = [
        'password_hash',
        'two_factor_secret',
        'backup_codes'
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_password_change' => 'datetime',
        'is_active' => 'boolean',
        'two_factor_verified' => 'boolean',
        'backup_codes' => 'array',
        'two_factor_enabled_at' => 'datetime',
        'two_factor_expires_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Audit logs for tracking admin actions
    public function auditLogs()
    {
        return $this->hasMany(AdminAuditLog::class);
    }

    // Categories created by this admin
    public function categories()
    {
        return $this->hasMany(Category::class, 'created_by');
    }

    // Products created by this admin
    public function createdProducts()
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    // Products updated by this admin
    public function updatedProducts()
    {
        return $this->hasMany(Product::class, 'updated_by');
    }

    // Promo codes created by this admin
    public function promoCodes()
    {
        return $this->hasMany(PromoCode::class, 'created_by');
    }

    // Support tickets assigned to this admin
    public function assignedSupportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to');
    }

    // Vendors managed by this admin
    public function managedVendors()
    {
        return $this->hasMany(Vendor::class, 'managed_by');
    }

    // Orders handled by this admin
    public function handledOrders()
    {
        return $this->hasMany(Order::class, 'handled_by');
    }

    // Payments processed by this admin
    public function processedPayments()
    {
        return $this->hasMany(Payment::class, 'processed_by');
    }

    // Created admins (for Super Admins)
    public function createdAdmins()
    {
        return $this->hasMany(Admin::class, 'created_by');
    }

    // Creator admin
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // Shipments created by this admin
    public function createdShipments()
    {
        return $this->hasMany(Shipment::class, 'created_by');
    }

    // Shipments updated by this admin
    public function updatedShipments()
    {
        return $this->hasMany(Shipment::class, 'updated_by');
    }

    // Reviews moderated by this admin
    public function moderatedReviews()
    {
        return $this->hasMany(Review::class, 'moderated_by');
    }

    // Price histories changed by this admin
    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class, 'changed_by');
    }

    // Inventory updates by this admin
    public function inventoryUpdates()
    {
        return $this->hasMany(Inventory::class, 'updated_by');
    }

    /**
     * Define which relationships should cascade on delete.
     *
     * @return array
     */
    public function getCascadeDeletes(): array
    {
        return [
            'createdAdmins',
            'managedVendors',
            'categories',
            'createdProducts',
            'promoCodes'
        ];
    }

    /*
    protected static function boot()
    {
        parent::boot();

        // When an admin is soft deleted, cascade to related entities
        static::deleting(function($admin) {
            // Only cascade if this is a soft delete operation
            if (!$admin->isForceDeleting()) {
                // Cascade soft delete to related entities
                $admin->createdAdmins()->each(function($relatedAdmin) {
                    $relatedAdmin->delete();
                });
                
                $admin->managedVendors()->each(function($vendor) {
                    $vendor->delete();
                });
                
                $admin->categories()->each(function($category) {
                    $category->delete();
                });
                
                $admin->createdProducts()->each(function($product) {
                    $product->delete();
                });
                
                $admin->promoCodes()->each(function($promoCode) {
                    $promoCode->delete();
                });
            }
        });

        // When an admin is restored, cascade restore to related entities
        static::restored(function($admin) {
            // Restore related entities that were soft deleted
            $admin->createdAdmins()->onlyTrashed()->each(function($relatedAdmin) {
                $relatedAdmin->restore();
            });
            
            $admin->managedVendors()->onlyTrashed()->each(function($vendor) {
                $vendor->restore();
            });
            
            $admin->categories()->onlyTrashed()->each(function($category) {
                $category->restore();
            });
            
            $admin->createdProducts()->onlyTrashed()->each(function($product) {
                $product->restore();
            });
            
            $admin->promoCodes()->onlyTrashed()->each(function($promoCode) {
                $promoCode->restore();
            });
        });
    }
    */
}
