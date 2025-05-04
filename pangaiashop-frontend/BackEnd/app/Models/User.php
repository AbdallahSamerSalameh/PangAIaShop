<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CascadeSoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, CascadeSoftDeletes;

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'phone_number',
        'street',
        'city',
        'state',
        'postal_code',
        'country',
        'avatar_url',
        'two_factor_secret',
        'is_verified',
        'account_status',
    ];

    protected $hidden = [
        'password_hash',
        'two_factor_secret',
        'encrypted_recovery_email',
        'backup_codes'
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_password_change' => 'datetime',
        'is_verified' => 'boolean',
        'two_factor_verified' => 'boolean',
        'backup_codes' => 'array',
        'two_factor_enabled_at' => 'datetime',
        'two_factor_expires_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
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
            'cart',
            'orders',
            'reviews',
            'wishlists',
            'preferences',
            'supportTickets'
        ];
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     // When a user is soft deleted, cascade to related entities
    //     static::deleting(function($user) {
    //         // Only cascade if this is a soft delete operation
    //         if (!$user->isForceDeleting()) {
    //             $user->cart()->each(function($cart) {
    //                 $cart->delete();
    //             });

    //             $user->orders()->each(function($order) {
    //                 $order->delete();
    //             });

    //             $user->reviews()->each(function($review) {
    //                 $review->delete();
    //             });

    //             $user->wishlists()->each(function($wishlist) {
    //                 $wishlist->delete();
    //             });

    //             $user->preferences()->delete();

    //             $user->supportTickets()->each(function($ticket) {
    //                 $ticket->delete();
    //             });
    //         }
    //     });

    //     // When a user is restored, cascade restore to related entities
    //     static::restored(function($user) {
    //         $user->cart()->onlyTrashed()->each(function($cart) {
    //             $cart->restore();
    //         });

    //         $user->orders()->onlyTrashed()->each(function($order) {
    //             $order->restore();
    //         });

    //         $user->reviews()->onlyTrashed()->each(function($review) {
    //             $review->restore();
    //         });

    //         $user->wishlists()->onlyTrashed()->each(function($wishlist) {
    //             $wishlist->restore();
    //         });

    //         if ($user->preferences()->withTrashed()->exists()) {
    //             $user->preferences()->withTrashed()->restore();
    //         }

    //         $user->supportTickets()->onlyTrashed()->each(function($ticket) {
    //             $ticket->restore();
    //         });
    //     });
    // }
}
