<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationGiftOption extends Model
{
    /** Opción de la lista (grupal, tarjeta, etc.) */
    public const TYPE_OPTION = 'option';

    /** Lluvia de sobres: título y dónde se dejan */
    public const TYPE_ENVELOPE = 'envelope';

    /** Tienda o lista de regalos externa: texto del botón y enlace */
    public const TYPE_STORE = 'store';

    protected $fillable = [
        'invitation_id',
        'type',
        'title',
        'description',
        'url',
        'address',
        'image_url',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
