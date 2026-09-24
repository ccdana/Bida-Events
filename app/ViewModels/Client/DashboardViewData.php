<?php

namespace App\ViewModels\Client;

use App\EventProfiles\EventProfiles;
use App\Models\Invitation;
use App\Models\User;
use App\Modules\Module;
use App\Support\InvitationTemplates;
use App\Support\ResellerSubscription;
use Illuminate\Support\Collection;

/**
 * Panel del cliente: sus eventos separados en secciones (invitaciones y tarjetas, lo que viene
 * y lo que ya pasó) con lo mínimo para decidir qué hacer con cada uno.
 */
class DashboardViewData
{
    public function __construct(private EventProfiles $profiles) {}

    public function make(Collection $invitations, string $search = '', ?User $user = null): array
    {
        $rows = $invitations->map(fn (Invitation $invitation) => $this->row($invitation, (bool) $user?->isReseller(), $user));

        // Primero lo que todavía no pasó y, dentro de eso, lo más cercano
        $sort = fn (Collection $group) => $group
            ->sortBy(fn (array $row) => [$row['isPast'] ? 1 : 0, $row['isPast'] ? -$row['timestamp'] : $row['timestamp']])
            ->values();

        $sections = collect([
            [
                'key' => 'invitaciones',
                'title' => 'Mis invitaciones',
                'description' => 'Eventos con lista de invitados y confirmaciones.',
                'rows' => $sort($rows->where('isCard', false)),
            ],
            [
                'key' => 'tarjetas',
                'title' => 'Mis tarjetas',
                'description' => 'Tarjetas que enviaste y las respuestas que te dejaron.',
                'rows' => $sort($rows->where('isCard', true)),
            ],
        ])->filter(fn (array $section) => $section['rows']->isNotEmpty())->values();

        return [
            'sections' => $sections,
            'search' => $search,
            'total' => $rows->count(),
            'reseller' => $user?->isReseller() ? $this->reseller($user) : null,
        ];
    }

    /**
     * Lo que el revendedor necesita ver al entrar: su plan, cuánto cupo le queda este mes y si su
     * suscripción está por vencer o ya venció (entonces no puede crear ni editar hasta renovar).
     */
    private function reseller(User $user): array
    {
        $plan = $user->planConfig();
        $limit = ResellerSubscription::quotaLimit($user);
        $used = ResellerSubscription::quotaUsed($user);
        $daysLeft = ResellerSubscription::daysLeft($user);
        $active = $user->hasActiveSubscription();
        $hasQuota = ResellerSubscription::hasQuotaLeft($user);

        $warning = match (true) {
            $daysLeft === null => 'Tu suscripción todavía no está activa. Escríbenos para registrar tu primer pago.',
            ! $active => 'Tu suscripción venció: tus invitaciones siguen en línea, pero no puedes crear ni editar hasta renovarla.',
            $daysLeft <= ResellerSubscription::WARNING_DAYS => $daysLeft === 1
                ? 'Tu suscripción vence mañana. Renuévala para no perder el acceso al editor.'
                : "Tu suscripción vence en {$daysLeft} días. Renuévala para no perder el acceso al editor.",
            default => null,
        };

        return [
            'planName' => $plan['name'] ?? 'Sin plan',
            'quotaUsed' => $used,
            'quotaLimit' => $limit,
            'quotaLabel' => $limit === null ? "{$used} este mes (sin tope)" : "{$used} de {$limit}",
            'renewsLabel' => $user->subscription_renews_at?->locale('es')->translatedFormat('j \d\e F \d\e Y'),
            'isActive' => $active,
            'warning' => $warning,
            'isExpired' => $daysLeft !== null && ! $active,
            'canCreate' => $active && $hasQuota,
            // Por qué no puede crear, para decirlo junto al botón
            'blockedReason' => match (true) {
                ! $active => 'Renueva tu suscripción para crear invitaciones.',
                ! $hasQuota => 'Ya usaste el cupo de este mes.',
                default => null,
            },
        ];
    }

    private function row(Invitation $invitation, bool $forReseller = false, ?User $user = null): array
    {
        $profile = $this->profiles->forTemplate($invitation->template);
        $isCard = $profile->kind() === Module::KIND_CARD;
        $guests = $invitation->guests;
        $isPublished = $invitation->status === 'active'
            && (! $invitation->expires_at || ! $invitation->expires_at->isBefore(now()->startOfDay()));

        return [
            'invitation' => $invitation,
            'isCard' => $isCard,
            'isPast' => (bool) $invitation->is_post_event,
            'timestamp' => $invitation->event_date?->getTimestamp() ?? 0,
            'kindLabel' => $isCard ? 'Tarjeta' : 'Invitación',
            'typeLabel' => $profile->label(),
            'templateLabel' => InvitationTemplates::get($invitation->template)['label'],
            'publicUrl' => $isPublished ? route('invitation.show', $invitation->slug) : null,
            // Por qué no se puede abrir la página: es lo que el cliente nos preguntaría
            'unavailableReason' => $isPublished
                ? null
                : match (true) {
                    $invitation->status === 'active' => 'El enlace venció: escríbenos para renovarlo.',
                    // El revendedor la arma él mismo: la publica desde su editor
                    $forReseller => 'Todavía sin publicar: publícala desde el editor.',
                    default => 'Todavía sin publicar: la estamos preparando.',
                },
            'metrics' => $isCard ? $this->cardMetrics($invitation) : $this->guestMetrics($guests),
            'guestsCount' => $guests->count(),
            'ownerLabel' => $forReseller && $user ? $this->ownerLabel($invitation, $user) : null,
        ];
    }

    /**
     * Para el revendedor, de quién es cada evento: de un cliente suyo, suyo propio, todavía sin
     * cliente, o una invitación que le armó el equipo (ahí él es el cliente y no la edita).
     */
    private function ownerLabel(Invitation $invitation, User $user): string
    {
        $ownsIt = (int) $invitation->reseller_id === (int) $user->id;
        $isClient = (int) $invitation->user_id === (int) $user->id;

        return match (true) {
            ! $ownsIt => 'Te la armó el equipo',
            $isClient => 'Evento tuyo',
            $invitation->user_id === null => 'Sin cliente todavía',
            default => 'Cliente: '.($invitation->user?->name ?? 'sin nombre'),
        };
    }

    /** @return array<int, array{label: string, value: int|string, note?: string}> */
    private function guestMetrics(Collection $guests): array
    {
        $confirmed = $guests->where('status', 'confirmed');

        return [
            ['label' => 'Personas confirmadas', 'value' => (int) $confirmed->sum('passes_confirmed'), 'note' => $confirmed->count().' de '.$guests->count().' invitados'],
            ['label' => 'Sin responder', 'value' => $guests->where('status', 'pending')->count()],
            ['label' => 'No asisten', 'value' => $guests->where('status', 'declined')->count()],
        ];
    }

    /** @return array<int, array{label: string, value: int|string, note?: string}> */
    private function cardMetrics(Invitation $invitation): array
    {
        $replies = (int) ($invitation->replies_count ?? 0);
        $others = max(0, (int) ($invitation->contributions_count ?? 0) - $replies);

        return [
            ['label' => 'Respuestas', 'value' => $replies, 'note' => $replies === 0 ? 'Todavía nadie respondió' : null],
            ['label' => 'Fotos y canciones', 'value' => $others],
        ];
    }
}
