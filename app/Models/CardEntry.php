<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Capítulo de la historia o recuerdo especial de una tarjeta tipo cuaderno. */
class CardEntry extends Model
{
    public const SECTION_STORY = 'historia';

    public const SECTION_MEMORIES = 'recuerdos';

    /** «Aventuras por vivir»: solo usa el título. */
    public const SECTION_ADVENTURES = 'aventuras';

    protected $table = 'card_entries';

    protected $fillable = ['invitation_id', 'section', 'title', 'happened_on', 'body', 'image_url', 'image_alt', 'sort_order'];

    protected $casts = ['happened_on' => 'date', 'sort_order' => 'integer'];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
