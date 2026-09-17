<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Textos del encabezado de una sección (galería, itinerario, playlist…): una fila por
 * invitación y módulo.
 */
class InvitationSection extends Model
{
    protected $fillable = ['invitation_id', 'feature_id', 'title', 'subtitle', 'intro', 'placeholder', 'cta_text', 'cta_url'];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
}
