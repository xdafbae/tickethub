<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatMap extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'layout',
    ];

    protected $casts = [
        'layout' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}