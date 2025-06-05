<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PasswordResetToken extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'admin_id',
        'token_hash',
        'request_ip',
        'expires_at',
        'is_used',
        'used_at',
        'reset_type'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean'
    ];

    // No timestamps needed for this model
    public $timestamps = false;

    /**
     * Get the user that owns the password reset token.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin that owns the password reset token.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function isUsed()
    {
        return $this->is_used || !is_null($this->used_at);
    }

    public function markAsUsed()
    {
        $this->is_used = true;
        $this->used_at = now();
        $this->save();
    }
}
