<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'location',
        'date',
        'poster',
        'quota',
        'category',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }
}