<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WishlistItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wishlist_id',
        'product_id',
        'notes'
    ];

    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class);
        // Wishlist uses SoftDeletes, but we'll remove withTrashed() to ensure compatibility
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
        // Product doesn't use SoftDeletes, so we remove withTrashed()
    }
}
