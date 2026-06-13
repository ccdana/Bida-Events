<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int $event_type_id
 * @property string $slug
 * @property string $template
 * @property string $title
 * @property Carbon $event_date
 * @property string $status
 * @property Carbon $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array $modules
 * @property bool $is_post_event
 */
class Invitation extends Model
{
    protected $fillable = [
        'user_id',
        'event_type_id',
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

    protected ?array $modulesCache = null;

    // Attributes cache
    protected $appends = ['modules', 'is_post_event'];
    protected $hidden = ['modulesData'];

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
     * Scopes para queries optimizadas
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'active')
                    ->where('expires_at', '>=', now()->toDateString());
    }

    public function scopeWithAllData(Builder $query): Builder
    {
        return $query->with(['eventType', 'user', 'modulesData']);
    }

    /**
     * Módulos indexados por feature_code - computed property con cache
     */
    public function getModulesAttribute(): array
    {
        if ($this->modulesCache !== null) {
            return $this->modulesCache;
        }

        $this->modulesCache = $this->modulesData
            ->keyBy('feature_code')
            ->map(fn ($item) => $item->json_data)
            ->toArray();

        return $this->modulesCache;
    }

    public function clearModulesCache(): void
    {
        $this->modulesCache = null;
    }

    public function getIsPostEventAttribute(): bool
    {
        return now()->isAfter($this->event_date);
    }
}
