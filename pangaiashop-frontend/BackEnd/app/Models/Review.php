<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    // Turn off Laravel's automatic timestamps since our table only has created_at
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'comment',
        'sentiment_score',
        'helpful_count',
        'moderation_status',
        'moderated_by',
        'moderated_at',
        'created_at'
    ];

    protected $casts = [
        'rating' => 'integer',
        'sentiment_score' => 'float',
        'helpful_count' => 'integer',
        'created_at' => 'datetime',
        'moderated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function moderatedBy()
    {
        return $this->belongsTo(Admin::class, 'moderated_by')->withTrashed();
    }
}
