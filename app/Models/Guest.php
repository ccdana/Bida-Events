<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'confirmed_at'
    ];

    protected $casts = [
        'passes_allocated' => 'integer',
        'passes_confirmed' => 'integer',
        'confirmed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Guest $guest) {
            if (empty($guest->qr_code_token)) {
                $guest->qr_code_token = \App\Services\InvitationModuleService::generateGuestToken();
            }
        });
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