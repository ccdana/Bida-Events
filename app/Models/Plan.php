<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'slug', 'max_photos', 'months_online', 'price'];

    protected $casts = [
        'max_photos' => 'integer',
        'months_online' => 'integer',
        'price' => 'decimal:2', // Devuelve el precio como flotante con 2 decimales
    ];

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}