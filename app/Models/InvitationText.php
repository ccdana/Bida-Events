<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Un texto de la plantilla que esta invitación reemplaza por uno propio (ver App\Support\EditableTexts). */
class InvitationText extends Model
{
    protected $fillable = ['invitation_id', 'key', 'value'];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
