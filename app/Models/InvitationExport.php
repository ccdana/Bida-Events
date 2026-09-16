<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class InvitationExport extends Model
{
    public const PENDING = 'pending';

    public const READY = 'ready';

    public const FAILED = 'failed';

    /** Tipo de archivo => [etiqueta, prefijo del nombre, extensión] */
    public const TYPES = [
        'guests-excel' => ['Excel de invitados', 'invitados', 'xlsx'],
        'guests-pdf' => ['PDF de invitados', 'reporte-invitados', 'pdf'],
        'invitation-pdf' => ['PDF de la invitación', 'invitacion', 'pdf'],
    ];

    protected $fillable = ['invitation_id', 'user_id', 'type', 'status', 'path', 'error'];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return self::TYPES[$this->type][0] ?? 'Archivo';
    }

    /** Nombre con el que se descarga, por ejemplo "invitados-xv-isabella.xlsx". */
    public function filename(): string
    {
        [, $prefix, $extension] = self::TYPES[$this->type] ?? ['Archivo', 'archivo', 'bin'];

        return "{$prefix}-{$this->invitation->slug}.{$extension}";
    }

    public function isReady(): bool
    {
        return $this->status === self::READY
            && $this->path
            && Storage::disk('local')->exists($this->path);
    }

    /** Borra el archivo generado; se usa al limpiar exportaciones viejas. */
    public function deleteFile(): void
    {
        if ($this->path && Storage::disk('local')->exists($this->path)) {
            Storage::disk('local')->delete($this->path);
        }
    }
}
