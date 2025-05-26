<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CascadeSoftDeletes;

class Wishlist extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    // Disable the standard timestamps (created_at and updated_at)
    public $timestamps = false;
    
    // Define which column to use for created_at
    const CREATED_AT = 'created_at';
    
    protected $fillable = [
        'user_id',
        'name',
        'wishlist_privacy'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(WishlistItem::class);
    }

    /**
     * Define which relationships should cascade on delete.
     *
     * @return array
     */
    public function getCascadeDeletes(): array
    {
        return [
            'items'
        ];
    }

    /*
    protected static function boot()
    {
        parent::boot();
        
        static::deleted(function($wishlist) {
            $wishlist->items()->delete();
        });

        static::restored(function($wishlist) {
            $wishlist->items()->restore();
        });
    }
    */
}
