<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Textos de la confirmación de asistencia. */
class InvitationRsvpSetting extends Model
{
    protected $table = 'invitation_rsvp_settings';

    protected $fillable = ['invitation_id', 'title', 'message', 'confirmed_text', 'declined_text', 'whatsapp'];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
