<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'event_id','user_id','buyer_name','buyer_email','buyer_phone',
        'subtotal','discount','total','status','promo_code','seats','items','external_ref',
        'checkin_status','checked_in_at'
    ];

    protected $casts = [
        'seats' => 'array',
        'items' => 'array',
        'checked_in_at' => 'datetime',
    ];

    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
}