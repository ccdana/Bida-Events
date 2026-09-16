<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestContribution extends Model
{
    // Solo permitimos created_at, deshabilitando updated_at al ser un historial
    const UPDATED_AT = null;

    // El cliente puede ocultar un aporte sin borrarlo
    public const VISIBLE = 'visible';

    public const HIDDEN = 'hidden';

    protected $fillable = ['invitation_id', 'guest_id', 'type', 'content_text', 'file_path', 'moderation_status'];

    /** Lo que ven los invitados: todo lo que el cliente no ocultó. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('moderation_status', self::VISIBLE);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}