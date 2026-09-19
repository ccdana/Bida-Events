<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Portada: nombres, edad, subtítulo, mensaje, fecha visible y foto. */
class InvitationHero extends Model
{
    protected $table = 'invitation_heroes';

    protected $fillable = ['invitation_id', 'primary_name', 'secondary_name', 'age', 'subtitle', 'message', 'date_text', 'image_url', 'image_alt', 'post_event_message'];

    protected $casts = ['age' => 'integer'];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
