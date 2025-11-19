<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'usage_limit_total', 'usage_limit_per_user',
        'used_count', 'starts_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Normalisasi setiap kali atribut code di-set
    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper(trim((string) $value));
    }

    // Scope: cari promo berdasarkan kode yang sudah dinormalisasi
    public function scopeByNormalizedCode($query, string $code)
    {
        $norm = strtoupper(trim($code));
        return $query->whereRaw('UPPER(TRIM(code)) = ?', [$norm]);
    }
}