<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Colores y tipografías de la invitación. */
class InvitationTheme extends Model
{
    protected $table = 'invitation_themes';

    protected $fillable = ['invitation_id', 'color_primary', 'color_secondary', 'color_accent', 'color_text', 'color_background', 'font_titles', 'font_body', 'font_script'];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
