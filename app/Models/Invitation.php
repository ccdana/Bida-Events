<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'expires_at',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'expires_at' => 'date',
    ];

    protected $appends = ['is_post_event'];

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
     * Módulos con su interruptor de visibilidad (una fila por módulo). Los datos de cada módulo
     * viven en sus tablas; ver app/Modules.
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'invitation_features')
            ->withPivot('is_enabled');
    }

    public function theme(): HasOne
    {
        return $this->hasOne(InvitationTheme::class);
    }

    public function hero(): HasOne
    {
        return $this->hasOne(InvitationHero::class);
    }

    /** Textos del encabezado de cada sección (título, introducción…). */
    public function sections(): HasMany
    {
        return $this->hasMany(InvitationSection::class);
    }

    public function hashtag(): HasOne
    {
        return $this->hasOne(InvitationHashtag::class);
    }

    public function rsvpSetting(): HasOne
    {
        return $this->hasOne(InvitationRsvpSetting::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(InvitationBankAccount::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Dedicatoria de una tarjeta estacional. */
    public function dedication(): HasOne
    {
        return $this->hasOne(CardDedication::class);
    }

    /** Fechas importantes de una tarjeta («juntos desde»). */
    public function milestones(): HasMany
    {
        return $this->hasMany(CardMilestone::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Capítulos y recuerdos de una tarjeta tipo cuaderno, separados por sección. */
    public function cardEntries(): HasMany
    {
        return $this->hasMany(CardEntry::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Relato de la tarjeta «Nuestra historia»: primeras impresiones, anécdota, reflexión y promesa. */
    public function story(): HasOne
    {
        return $this->hasOne(CardStory::class);
    }

    /** Momentos clave de «Nuestra historia», en orden. */
    public function storyMoments(): HasMany
    {
        return $this->hasMany(CardStoryMoment::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Lista de invitados de este evento.
     */
    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    /** Archivos (Excel y PDF) que pidió el cliente desde su panel. */
    public function exports(): HasMany
    {
        return $this->hasMany(InvitationExport::class);
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

    public function itineraryItems(): HasMany
    {
        return $this->hasMany(InvitationItineraryItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(InvitationGalleryImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function polls(): HasMany
    {
        return $this->hasMany(InvitationPoll::class)->orderBy('sort_order')->orderBy('id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(InvitationLocation::class)->orderBy('sort_order')->orderBy('id');
    }

    public function featuredPeople(): HasMany
    {
        return $this->hasMany(InvitationFeaturedPerson::class)->orderBy('sort_order')->orderBy('id');
    }

    public function dressCodeItems(): HasMany
    {
        return $this->hasMany(InvitationDressCodeItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function giftOptions(): HasMany
    {
        return $this->hasMany(InvitationGiftOption::class)->orderBy('sort_order')->orderBy('id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(InvitationMedia::class)->orderBy('sort_order')->orderBy('id');
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
        return $query->with(['eventType', 'user']);
    }

    public function getIsPostEventAttribute(): bool
    {
        $cutoff = $this->event_date->copy()->addDay()->startOfDay()->addHours(5);

        return now()->greaterThanOrEqualTo($cutoff);
    }
}
