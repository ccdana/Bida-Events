<?php

namespace App\Models;

use App\Services\MediaUploadService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestContribution extends Model
{
    // Solo permitimos created_at, deshabilitando updated_at al ser un historial
    const UPDATED_AT = null;

    // El cliente puede ocultar un aporte sin borrarlo
    public const VISIBLE = 'visible';

    public const HIDDEN = 'hidden';

    protected $fillable = ['invitation_id', 'guest_id', 'type', 'content_text', 'reaction', 'file_path', 'moderation_status'];

    /** Lo que ven los invitados: todo lo que el cliente no ocultó. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('moderation_status', self::VISIBLE);
    }

    /**
     * Al borrar una foto se borra también su archivo en Cloudinary, para no pagar por archivos
     * que ya nadie ve. Solo aplica al borrado por modelo (no a un DELETE masivo por consulta).
     */
    protected static function booted(): void
    {
        static::deleted(function (self $contribution) {
            if ($contribution->type === 'live_photo' && $contribution->file_path) {
                app(MediaUploadService::class)->delete($contribution->file_path);
            }
        });
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
