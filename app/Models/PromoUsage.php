<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoUsage extends Model
{
    protected $fillable = [
        'promo_id', 'user_id', 'session_id', 'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];
}