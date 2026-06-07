<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventType extends Model
{
    // Desactivamos timestamps ya que esta tabla maestra no los requiere
    public $timestamps = false;

    protected $fillable = ['name', 'slug'];

    /**
     * Obtener todas las invitaciones asociadas a este tipo de evento.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}