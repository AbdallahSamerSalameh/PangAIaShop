<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPreference extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'language',
        'currency',
        'theme_preference',
        'notification_preferences',
        'ai_interaction_enabled',
        'chat_history_enabled',
        'last_interaction_date'
    ];

    protected $casts = [
        'notification_preferences' => 'array',
        'ai_interaction_enabled' => 'boolean',
        'chat_history_enabled' => 'boolean',
        'last_interaction_date' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
