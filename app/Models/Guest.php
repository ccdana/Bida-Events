<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Guest extends Model
{
    // Desactivamos timestamps para acelerar las escrituras masivas en confirmaciones rápidas
    public $timestamps = false;

    protected $fillable = [
        'invitation_id',
        'name',
        'phone',
        'passes_allocated',
        'passes_confirmed',
        'status',
        'table_number',
        'dietary_restrictions',
        'qr_code_token',
        'confirmed_at',
    ];

    // checked_in_passes y checked_in_at no van en fillable: solo los escribe la puerta (DoorController)

    protected $casts = [
        'passes_allocated' => 'integer',
        'passes_confirmed' => 'integer',
        'confirmed_at' => 'datetime',
        'checked_in_passes' => 'integer',
        'checked_in_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Guest $guest) {
            $guest->qr_code_token = $guest->qr_code_token ?: self::newToken();
        });
    }

    /** Clave del enlace personal del invitado (/p/{slug}/i/{token}). */
    public static function newToken(): string
    {
        return Str::random(32);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    /**
     * Obtiene lo que este invitado ha aportado a la fiesta (canciones, dedicatorias, etc.).
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(GuestContribution::class);
    }
}
