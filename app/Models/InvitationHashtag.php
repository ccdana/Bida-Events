<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Etiqueta para compartir fotos en redes. */
class InvitationHashtag extends Model
{
    protected $table = 'invitation_hashtags';

    protected $fillable = ['invitation_id', 'tag', 'platform', 'button_text'];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
