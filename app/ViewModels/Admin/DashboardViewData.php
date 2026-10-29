<?php

namespace App\ViewModels\Admin;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\PaymentOrder;
use App\Models\SubscriptionPayment;
use App\Modules\Module;
use App\Support\EventCountdown;
use App\Support\EventIcons;
use App\Support\InvitationTemplates;
use App\Support\Localization;
use App\Support\Money;
use App\Support\Packages;
use App\Support\ResellerSubscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * El inicio del administrador: tres números del momento (eventos de la semana, activas y cobrado en el mes), lo que pide
 * atención (revendedores por vencer y pagos que siguen esperando) y los eventos en una sola lista, cada uno con la ficha
 * que evita tener que abrir el editor para saber de qué se trata (cliente, enlaces, invitados).
 */
class DashboardViewData
{
    /** Filtros rápidos por producto entero, además de los tipos de evento. */
    public const KINDS = [Module::KIND_INVITATION, Module::KIND_CARD];

    /** Una orden que lleva más de esto sin confirmarse ya no es «alguien pagando»: merece una mirada. */
    private const STALE_ORDER_MINUTES = 30;

    /** $scope: las invitaciones de esta lista (las de clientes o las de muestra); sin él, todas. $withPulse: solo la lista de clientes lleva números y avisos. */
    public function make(LengthAwarePaginator $invitations, string $type = '', string $search = '', ?Builder $scope = null, bool $withPulse = true): array
    {
        $items = collect($invitations->items())->map(fn (Invitation $invitation) => $this->row($invitation));

        return [
            'items' => $items,
            'invitations' => $invitations,
            'type' => $type,
            'search' => $search,
            'pulse' => $withPulse ? $this->pulse($scope ?? Invitation::query()) : null,
            'attention' => $withPulse ? $this->attention() : [],
        ];
    }

    /**
     * Los números del momento. Las cifras son de toda la lista (no solo de la página que se muestra) y las cuenta la base.
     *
     * @return array{weekEvents: int, active: int, total: int, guests: int, revenue: list<string>}
     */
    private function pulse(Builder $scope): array
    {
        $today = now()->startOfDay();
        $counts = (clone $scope)->reorder()->toBase()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END), 0) AS active")
            ->selectRaw('COALESCE(SUM(CASE WHEN event_date >= ? AND event_date < ? THEN 1 ELSE 0 END), 0) AS week', [$today->toDateTimeString(), $today->copy()->addDays(7)->toDateTimeString()])
            ->first();

        return [
            'weekEvents' => (int) $counts->week,
            'active' => (int) $counts->active,
            'total' => (int) $counts->total,
            'guests' => Guest::whereIn('invitation_id', (clone $scope)->reorder()->select('id'))->count(),
            'revenue' => $this->monthRevenue(),
        ];
    }

    /**
     * Lo cobrado en el mes, por moneda: las compras en línea que ya se pagaron y los pagos que el administrador registró a
     * mano (los de suscripción que vinieron de una compra en línea ya cuentan en su orden).
     *
     * @return list<string>
     */
    private function monthRevenue(): array
    {
        $from = now()->startOfMonth();
        $until = now()->endOfMonth();

        $paid = PaymentOrder::query()
            ->where('status', PaymentOrder::PAID)
            ->whereBetween('paid_at', [$from, $until])
            ->selectRaw('currency, SUM(amount) AS total')
            ->groupBy('currency')
            ->pluck('total', 'currency');

        $manual = SubscriptionPayment::query()
            ->whereNull('payment_order_id')
            ->whereBetween('paid_at', [$from->toDateString(), $until->toDateString()])
            ->selectRaw('currency, SUM(amount) AS total')
            ->groupBy('currency')
            ->pluck('total', 'currency');

        return collect($paid->keys()->merge($manual->keys())->unique())
            ->map(fn (string $currency) => Money::format((float) ($paid[$currency] ?? 0) + (float) ($manual[$currency] ?? 0), $currency))
            ->values()
            ->all();
    }

    /**
     * Lo que pide atención, con su atajo: los revendedores que vencen o ya vencieron y las compras que siguen esperando la
     * confirmación de la pasarela (las más recientes no cuentan: puede que el comprador esté pagando ahora mismo).
     *
     * @return list<array{icon: string, text: string, note: string, url: string, danger: bool}>
     */
    private function attention(): array
    {
        $items = [];

        foreach (ResellerSubscription::dueSoon() as $reseller) {
            $days = (int) now()->startOfDay()->diffInDays($reseller->subscription_renews_at->copy()->startOfDay(), false);

            $items[] = [
                'icon' => 'storefront',
                'text' => ($reseller->business_name ?: $reseller->name).' · plan '.(config("bida.reseller_plans.{$reseller->reseller_plan}.name") ?? 'sin plan'),
                'note' => match (true) {
                    $days < 0 => 'venció hace '.abs($days).(abs($days) === 1 ? ' día' : ' días'),
                    $days === 0 => 'vence hoy',
                    default => 'vence en '.$days.($days === 1 ? ' día' : ' días'),
                },
                'url' => route('admin.resellers.index'),
                'danger' => $days < 0,
            ];
        }

        $orders = PaymentOrder::query()
            ->where('status', PaymentOrder::PENDING)
            ->where('created_at', '<=', now()->subMinutes(self::STALE_ORDER_MINUTES))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->with(['user:id,name,business_name', 'invitation:id,title'])
            ->latest()
            ->limit(5)
            ->get();

        foreach ($orders as $order) {
            $items[] = [
                'icon' => 'hourglass-medium',
                'text' => ($order->user?->business_name ?: $order->user?->name ?: 'Sin cuenta').' · '.$order->concept(),
                'note' => Money::format((float) $order->amount, $order->currency).' · hace '.$order->created_at->diffForHumans(null, true),
                'url' => $order->invitation_id ? route('admin.invitations.edit', $order->invitation_id) : route('admin.resellers.index'),
                'danger' => false,
            ];
        }

        return $items;
    }

    /** Una invitación vista desde el panel: lo que hace falta para decidir sin abrir el editor. */
    private function row(Invitation $invitation): array
    {
        $guests = (int) ($invitation->guests_count ?? 0);
        $confirmed = (int) ($invitation->confirmed_guests_count ?? 0);
        $template = InvitationTemplates::get($invitation->template);
        $isExpired = (bool) $invitation->expires_at?->isBefore(now()->startOfDay());
        $isPast = (bool) $invitation->is_post_event;
        $isActive = $invitation->status === 'active';
        $daysLeft = EventCountdown::days($invitation->event_date);
        $short = fn (?\Carbon\CarbonInterface $date) => $date ? str_replace('.', '', $date->locale(app()->getLocale())->translatedFormat(Localization::dateFormat('j M'))) : null;

        return [
            'invitation' => $invitation,
            // El punto de estado: en línea, sin publicar, con el enlace vencido o ya celebrada
            'state' => match (true) {
                $isPast => ['past', 'Ya celebrada'],
                $isActive && $isExpired => ['expired', 'Enlace vencido'],
                $isActive => ['live', 'En línea'],
                default => ['draft', 'Sin publicar'],
            },
            'isCard' => $invitation->eventType?->kind === Module::KIND_CARD,
            'typeSlug' => $invitation->eventType?->slug ?? '',
            'typeName' => $invitation->eventType?->name ?? 'Sin tipo',
            'typeIcon' => EventIcons::for($invitation->eventType?->code),
            'templateLabel' => $template['label'],
            // Paquete vendido; sin paquete, todo incluido (tarjetas, revendedores y anteriores). Las de
            // «Hazlo tú» dicen que las armó el cliente y con qué nivel se publicaron (o que son borrador)
            'packageLabel' => $invitation->is_diy
                ? 'Hazlo tú · '.(Packages::tierNames()[$invitation->package ?? ''] ?? 'borrador')
                : (Packages::names()[$invitation->package ?? ''] ?? null),
            'guestCount' => $guests,
            'confirmedCount' => $confirmed,
            'pendingCount' => (int) ($invitation->pending_guests_count ?? 0),
            'confirmedPasses' => (int) ($invitation->confirmed_passes_sum ?? 0),
            'contributionCount' => (int) ($invitation->contributions_count ?? 0),
            'client' => $invitation->user ? ['name' => $invitation->user->name, 'username' => $invitation->user->username] : null,
            'dateLabel' => $short($invitation->event_date) ?? 'Sin fecha',
            'whenLabel' => $isPast ? '' : EventCountdown::label($daysLeft),
            'eventDateLabel' => $invitation->event_date?->locale(app()->getLocale())->translatedFormat(Localization::dateFormat('j \d\e F \d\e Y, H:i')) ?? 'Sin fecha',
            'createdLabel' => $invitation->created_at?->locale(app()->getLocale())->translatedFormat(Localization::dateFormat('j \d\e F \d\e Y')) ?? '',
            'expiresLabel' => $invitation->expires_at?->locale(app()->getLocale())->translatedFormat(Localization::dateFormat('j \d\e F \d\e Y')) ?? 'Sin vencimiento',
            'isExpired' => $isExpired,
            'isPast' => $isPast,
            'publicUrl' => route('invitation.show', $invitation->slug),
            // Cuántos ya contestaron (confirmaron o avisaron que no van), para leerlo de un vistazo
            'responseRate' => $guests > 0 ? (int) round(($guests - (int) ($invitation->pending_guests_count ?? 0)) / $guests * 100) : null,
        ];
    }
}
