<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationData extends Model
{
    // Especificamos la tabla manualmente si no sigue el plural estándar
    protected $table = 'invitation_data';

    // Desactivamos timestamps ya que se actualiza bajo demanda mediante agregaciones
    public $timestamps = false;

    protected $fillable = ['invitation_id', 'feature_code', 'json_data'];

    // CARACTERÍSTICA CLAVE: Mapea la columna JSON a un array manipulable en PHP
    protected $casts = [
        'json_data' => 'array', 
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}