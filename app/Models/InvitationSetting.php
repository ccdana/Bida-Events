<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationSetting extends Model
{
    protected $fillable = [
        'invitation_id',
        'template',
        'colors',
        'typography',
        'module_visibility',
        'extra',
    ];

    protected $casts = [
        'colors' => 'array',
        'typography' => 'array',
        'module_visibility' => 'array',
        'extra' => 'array',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
