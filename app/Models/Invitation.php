<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invitation extends Model
{
    protected $fillable = [
        'user_id',
        'event_type_id',
        'plan_id',
        'slug',
        'template',
        'title',
        'event_date',
        'status',
        'expires_at'
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'expires_at' => 'date',
    ];

    /**
     * Cliente dueño de la invitación.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Obtiene los módulos/funcionalidades habilitados de forma personalizada para esta invitación.
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'invitation_features')
                    ->withPivot('is_enabled');
    }

    /**
     * Obtiene los bloques de textos y payloads de datos JSON editados desde el panel.
     */
    public function modulesData(): HasMany
    {
        return $this->hasMany(InvitationData::class);
    }

    /**
     * Lista de invitados de este evento.
     */
    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    /**
     * Interacciones en tiempo real de los invitados (Playlist, fotos en vivo).
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(GuestContribution::class);
    }

    public function pollVotes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    /**
     * Módulos indexados por feature_code para la vista pública.
     */
    public function getModulesAttribute(): array
    {
        return $this->modulesData
            ->pluck('json_data', 'feature_code')
            ->toArray();
    }

    public function isPostEvent(): bool
    {
        return now()->isAfter($this->event_date);
    }
}
