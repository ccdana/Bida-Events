<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Ejemplo de una sugerencia de vestimenta («Vestido largo», «Traje oscuro»…). */
class InvitationDressCodeExample extends Model
{
    public $timestamps = false;

    protected $fillable = ['dress_code_item_id', 'text', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InvitationDressCodeItem::class, 'dress_code_item_id');
    }
}
