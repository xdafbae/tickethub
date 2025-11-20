<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id','provider','provider_transaction_id','status','amount','redirect_url','raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}