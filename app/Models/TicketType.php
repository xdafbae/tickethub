<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'quota',
        'is_active',
        'available_from',
        'available_to',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'available_from' => 'date',
        'available_to' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}