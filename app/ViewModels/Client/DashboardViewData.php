<?php

namespace App\ViewModels\Client;

use App\EventProfiles\EventProfiles;
use App\Models\Invitation;
use App\Models\User;
use App\Modules\Module;
use App\Payments\PaymentGateways;
use App\Support\DiyInvitations;
use App\Services\InvitationModuleService;
use App\Support\InvitationTemplates;
use App\Support\LeadSource;
use App\Support\Localization;
use App\Support\Packages;
use App\Support\ResellerSubscription;
use App\Support\SampleContent;
use Illuminate\Support\Collection;

/**
 * Panel del cliente: sus eventos separados en secciones (invitaciones y tarjetas, lo que viene
 * y lo que ya pasó) con lo mínimo para decidir qué hacer con cada uno.
 */
class DashboardViewData
{
    /** @var array<string, int>|null */
    private ?array $placeholders = null;

    public function __construct(private EventProfiles $profiles) {}

    public function make(Collection $invitations, string $search = '', ?User $user = null): array
    {
        $forReseller = (bool) $user?->isReseller();
        $rows = $invitations->map(fn (Invitation $invitation) => $this->row($invitation, $forReseller, $user));

        // Primero lo que todavía no pasó y, dentro de eso, lo más cercano
        $upcoming = $rows->where('isPast', false)->sortBy('timestamp')->values();
        $past = $rows->where('isPast', true)->sortByDesc('timestamp')->values();

        // El próximo evento con fecha se destaca arriba; el resto va en filas
        $featured = $upcoming->first(fn (array $row) => $row['timestamp'] > 0);
        $list = $upcoming->reject(fn (array $row) => $featured !== null && $row['invitation']->is($featured['invitation']))->values();

        return [
            'featured' => $featured,
            'list' => $list,
            'past' => $past,
            'search' => $search,
            'total' => $rows->count(),
            'reseller' => $forReseller ? $this->reseller($user) : null,
            // Lo que pide atención del revendedor: sin cliente, sin publicar con la fecha cerca
            'attention' => $forReseller && $user->hasActiveSubscription() ? $this->attention($rows) : [],
            // «Hazlo tú»: crear su propia invitación (sin plan), con cuántos borradores le quedan
            'diy' => $user && DiyInvitations::canBuild($user) ? $this->diy($user) : null,
            // Dónde elegir el plan: pagarlo aquí si hay pago en línea; si no, ver los planes y escribirnos
            'startUrl' => app(PaymentGateways::class)->availableMethods() ? route('client.subscription') : lroute('pricing').'#planes',
        ];
    }

    /**
     * Lo que pide atención en el inicio del revendedor: cada evento suyo sin cliente asignado o sin publicar con la
     * fecha a menos de dos semanas. Con el atajo al editor, donde se resuelve.
     *
     * @return array<int, array{icon: string, text: string, url: string}>
     */
    private function attention(Collection $rows): array
    {
        $items = [];

        foreach ($rows->where('isPast', false)->sortBy('timestamp') as $row) {
            if (! $row['ownedByReseller']) {
                continue;
            }

            $invitation = $row['invitation'];
            $url = route('client.invitations.edit', $invitation);

            if ($invitation->user_id === null) {
                $items[] = ['icon' => 'user-plus', 'text' => $invitation->title.' · sin cliente', 'url' => $url];
            }

            if ($row['publicUrl'] === null && $row['daysLeft'] !== null && $row['daysLeft'] <= 14) {
                $items[] = ['icon' => 'eye-slash', 'text' => $invitation->title.' · sin publicar, '.$row['whenLabel'], 'url' => $url];
            }
        }

        return $items;
    }

    /**
     * Lo que el revendedor necesita ver al entrar: su plan, cuántas invitaciones publicó este mes (el
     * cupo cuenta las publicadas; crear borradores no lo gasta) y si su suscripción está por vencer o
     * ya venció (entonces no puede crear ni editar hasta renovar).
     */
    private function reseller(User $user): array
    {
        $plan = $user->planConfig();
        $limit = ResellerSubscription::quotaLimit($user);
        $used = ResellerSubscription::quotaUsed($user);
        $daysLeft = ResellerSubscription::daysLeft($user);
        $active = $user->hasActiveSubscription();
        $hasQuota = ResellerSubscription::hasQuotaLeft($user);
        // Con tarjeta se renueva sola: el aviso de vencimiento solo hace falta si canceló o falló el cobro
        $autoRenews = $user->renewsAutomatically() && $user->subscription_status === 'active';
        $payOnline = (bool) app(PaymentGateways::class)->availableMethods();
        $graceEnds = ResellerSubscription::graceEndsAt($user)?->locale(app()->getLocale())->translatedFormat(Localization::dateFormat('j \d\e F'));

        $warning = match (true) {
            $daysLeft === null => $payOnline
                ? 'Tu suscripción todavía no está activa. Elige tu plan y paga en línea para empezar.'
                : 'Tu suscripción todavía no está activa. Escríbenos para registrar tu primer pago.',
            ! $active => 'Tu suscripción venció: tus invitaciones siguen en línea, pero no puedes crear ni editar hasta renovarla.',
            $user->subscription_status === 'past_due' => "No pudimos cobrar la renovación con tu tarjeta. Revisa tu medio de pago antes del {$graceEnds} para no perder el acceso al editor.",
            ResellerSubscription::inGrace($user) => "Tu suscripción venció. Tienes hasta el {$graceEnds} para renovarla sin perder el acceso al editor.",
            $autoRenews => null,
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
            'renewsLabel' => $user->subscription_renews_at?->locale(app()->getLocale())->translatedFormat(Localization::dateFormat('j \d\e F \d\e Y')),
            'renewsShort' => $this->shortDate($user->subscription_renews_at),
            'quotaPercent' => $limit ? min(100, (int) round($used / $limit * 100)) : null,
            'isActive' => $active,
            // Ya pasó la fecha (aunque siga en los días de gracia): «venció el», no «renueva el»
            'isPastDue' => $daysLeft !== null && $daysLeft < 0,
            'autoRenews' => $autoRenews,
            'warning' => $warning,
            // Junto al aviso: pagar o renovar en línea (o el portal de la tarjeta, si el cobro falló)
            'payUrl' => $warning && $payOnline ? route('client.subscription') : null,
            'isExpired' => $daysLeft !== null && ! $active,
            // Sin cupo igual crea y arma borradores: lo que no puede es publicar hasta el mes que viene
            'canCreate' => $active,
            'blockedReason' => $active ? null : 'Renueva tu suscripción para crear invitaciones.',
            'quotaNote' => $active && ! $hasQuota ? 'Ya publicaste todas las de este mes: puedes seguir armando borradores y publicarlos el mes que viene.' : null,
        ];
    }

    /** «Hazlo tú»: si puede crear otra (tope de borradores) y si todavía le queda la gratuita. */
    private function diy(User $user): array
    {
        $drafts = DiyInvitations::draftsCount($user);
        $limit = DiyInvitations::draftsLimit();

        return [
            'canCreate' => $drafts < $limit,
            'drafts' => $drafts,
            'limit' => $limit,
            'freeAvailable' => DiyInvitations::freeAvailable($user),
            'blockedReason' => $drafts < $limit ? null : "Ya tienes {$limit} borradores: publica uno o borra el que no uses.",
        ];
    }

    private function row(Invitation $invitation, bool $forReseller = false, ?User $user = null): array
    {
        $profile = $this->profiles->forTemplate($invitation->template);
        $isCard = $profile->kind() === Module::KIND_CARD;
        $guests = $invitation->guests;
        $isPublished = $invitation->status === 'active'
            && (! $invitation->expires_at || ! $invitation->expires_at->isBefore(now()->startOfDay()));
        $publicUrl = $isPublished ? route('invitation.show', $invitation->slug) : null;
        $isPast = (bool) $invitation->is_post_event;
        $daysLeft = $invitation->event_date ? (int) now()->startOfDay()->diffInDays($invitation->event_date->copy()->startOfDay(), false) : null;
        $ownedByReseller = $forReseller && $user && (int) $invitation->reseller_id === (int) $user->id;
        // La armó el equipo (ni él mismo en «Hazlo tú» ni su revendedor): ahí pide los cambios por WhatsApp
        $teamMade = ! $invitation->is_diy && ! $ownedByReseller && $invitation->reseller_id === null;
        $confirmed = $guests->where('status', 'confirmed');
        $weekAgo = now()->subDays(7);
        $isDraft = $invitation->is_diy && $invitation->package === null;

        return [
            'invitation' => $invitation,
            'isCard' => $isCard,
            'isPast' => $isPast,
            'timestamp' => $invitation->event_date?->getTimestamp() ?? 0,
            'daysLeft' => $daysLeft,
            'whenLabel' => $isPast ? 'ya pasó' : $this->whenLabel($daysLeft),
            'dateLabel' => $this->shortDate($invitation->event_date),
            'longDateLabel' => $invitation->event_date
                ? $invitation->event_date->locale(app()->getLocale())->translatedFormat(Localization::dateFormat('j \d\e F')).', '.$invitation->event_date->format('H:i')
                : null,
            'kindLabel' => $isCard ? 'Tarjeta' : 'Invitación',
            'typeLabel' => $profile->label(),
            'templateLabel' => InvitationTemplates::get($invitation->template)['label'],
            'publicUrl' => $publicUrl,
            // Por qué no se puede abrir la página: es lo que el cliente nos preguntaría
            'unavailableReason' => $isPublished
                ? null
                : match (true) {
                    $invitation->status === 'active' => 'El enlace venció: escríbenos para renovarlo.',
                    // «Hazlo tú»: la arma y la publica el propio dueño
                    $invitation->is_diy => $invitation->package === null
                        ? 'Borrador: publícala desde el editor cuando esté lista.'
                        : 'Oculta: publícala otra vez desde el editor.',
                    // El revendedor la arma él mismo: la publica desde su editor
                    $forReseller => 'Todavía sin publicar: publícala desde el editor.',
                    default => 'Todavía sin publicar: la estamos preparando.',
                },
            'isDraft' => $isDraft,
            // Un borrador de «Hazlo tú»: cuánto de ejemplo le queda por cambiar
            'draftLeft' => $isDraft ? $this->examplesLeft($invitation) : null,
            // En números sueltos, no en tarjetas: confirmaron, sin responder, no asisten (o las respuestas de una tarjeta)
            'confirmed' => $confirmed->count(),
            'passes' => (int) $confirmed->sum('passes_confirmed'),
            'pending' => $guests->where('status', 'pending')->count(),
            'declined' => $guests->where('status', 'declined')->count(),
            'total' => $guests->count(),
            'progress' => $guests->count() > 0 ? (int) round($confirmed->count() / $guests->count() * 100) : 0,
            'replies' => (int) ($invitation->replies_count ?? 0),
            'others' => max(0, (int) ($invitation->contributions_count ?? 0) - (int) ($invitation->replies_count ?? 0)),
            // Lo que cambió en la última semana, calculado con las fechas (no se guarda nada)
            'newConfirmed' => $confirmed->filter(fn ($guest) => $guest->confirmed_at !== null && $guest->confirmed_at->greaterThanOrEqualTo($weekAgo))->count(),
            'newContributions' => (int) ($invitation->recent_contributions_count ?? 0),
            'ownerLabel' => $forReseller && $user ? $this->ownerLabel($invitation, $user) : null,
            'ownedByReseller' => $ownedByReseller,
            'clientName' => $invitation->user?->name,
            'teamMade' => $teamMade,
            // «Hazlo tú»: el nivel con el que se publicó (Gratis, Básico…); null mientras es borrador
            'tierLabel' => $invitation->is_diy && Packages::isTier($invitation->package) ? (Packages::tierNames()[$invitation->package] ?? null) : null,
            // WhatsApp: mandar el enlace (a los invitados; el revendedor, a su cliente) y, si la armó el equipo, pedir un cambio
            'shareUrl' => $publicUrl ? $this->shareUrl($invitation, $publicUrl, $ownedByReseller) : null,
            'helpUrl' => $teamMade
                ? LeadSource::whatsappUrl(request(), "Hola {brand}, quiero un cambio en mi invitación «{$invitation->title}».", 'PANEL')
                : null,
        ];
    }

    /** «20 nov» (sin el punto con que el calendario abrevia el mes). */
    private function shortDate(?\Carbon\CarbonInterface $date): ?string
    {
        return $date ? str_replace('.', '', $date->locale(app()->getLocale())->translatedFormat(Localization::dateFormat('j M'))) : null;
    }

    private function whenLabel(?int $days): string
    {
        return match (true) {
            $days === null => '',
            $days < 0 => 'ya pasó',
            $days === 0 => 'hoy',
            $days === 1 => 'mañana',
            $days <= 60 => "faltan {$days} días",
            default => 'faltan '.(int) round($days / 30).' meses',
        };
    }

    /** El mensaje listo para WhatsApp: a los invitados, o del revendedor a su cliente. */
    private function shareUrl(Invitation $invitation, string $publicUrl, bool $toClient): string
    {
        $text = $toClient && $invitation->user && $invitation->user_id !== $invitation->reseller_id
            ? "Hola {$invitation->user->name}, aquí está tu invitación «{$invitation->title}»: {$publicUrl}"
            : "Te invito a «{$invitation->title}»: {$publicUrl}";

        return 'https://wa.me/?text='.rawurlencode($text);
    }

    /** Cuántas cosas de ejemplo siguen sin cambiar en las partes encendidas de un borrador («Tu foto aquí», «Escribe aquí…»). */
    private function examplesLeft(Invitation $invitation): int
    {
        $this->placeholders ??= array_flip(array_map('trim', SampleContent::placeholders()));
        $modules = app(InvitationModuleService::class)->storedModules($invitation);
        $enabled = $modules['config']['modulos'] ?? [];
        $count = 0;

        foreach ($modules as $code => $data) {
            if ($code === 'config' || empty($enabled[$code]) || ! is_array($data)) {
                continue;
            }

            array_walk_recursive($data, function (mixed $value) use (&$count) {
                if (is_string($value) && isset($this->placeholders[trim($value)])) {
                    $count++;
                }
            });
        }

        return $count;
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
}
