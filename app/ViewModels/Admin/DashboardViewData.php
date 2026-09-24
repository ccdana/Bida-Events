<?php

namespace App\ViewModels\Admin;

use App\Models\EventType;
use App\Models\Guest;
use App\Models\Invitation;
use App\Modules\Module;
use App\Support\InvitationTemplates;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * El panel del administrador: los eventos separados en secciones por tipo, cada uno con la ficha
 * que evita tener que abrir el editor para saber de qué se trata (cliente, enlaces, invitados).
 */
class DashboardViewData
{
    /** Filtros rápidos por producto entero, además de los tipos de evento. */
    public const KINDS = [Module::KIND_INVITATION, Module::KIND_CARD];

    public function make(LengthAwarePaginator $invitations, string $type = '', string $search = ''): array
    {
        $items = collect($invitations->items())->map(fn (Invitation $invitation) => $this->row($invitation));

        // Secciones por tipo de evento, en el orden del catálogo; las invitaciones antes que las tarjetas
        $sections = $this->eventTypes()
            ->map(fn (array $eventType) => $eventType + ['rows' => $items->where('typeSlug', $eventType['slug'])->values()])
            ->filter(fn (array $section) => $section['rows']->isNotEmpty())
            ->values();

        // Lo que no tiene tipo conocido no se pierde: va al final, en su propia sección
        $orphans = $items->whereNotIn('typeSlug', $this->eventTypes()->pluck('slug')->all())->values();

        if ($orphans->isNotEmpty()) {
            $sections->push(['slug' => '', 'name' => 'Sin tipo de evento', 'isCard' => false, 'total' => $orphans->count(), 'rows' => $orphans]);
        }

        // Las cifras son de todas las invitaciones, no solo de la página que se muestra
        $total = Invitation::count();
        $active = Invitation::where('status', 'active')->count();

        $metrics = [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'guests' => Guest::count(),
        ];

        return [
            'items' => $items,
            'sections' => $sections,
            'metrics' => $metrics,
            'invitations' => $invitations,
            'type' => $type,
            'search' => $search,
        ];
    }

    /** Una invitación vista desde el panel: lo que hace falta para decidir sin abrir el editor. */
    private function row(Invitation $invitation): array
    {
        $guests = (int) ($invitation->guests_count ?? 0);
        $confirmed = (int) ($invitation->confirmed_guests_count ?? 0);
        $template = InvitationTemplates::get($invitation->template);

        return [
            'invitation' => $invitation,
            'statusClass' => $invitation->status === 'active' ? 'is-active' : 'is-draft',
            'statusLabel' => $invitation->status === 'active' ? 'Activa' : 'Inactiva',
            'isCard' => $invitation->eventType?->kind === Module::KIND_CARD,
            'typeSlug' => $invitation->eventType?->slug ?? '',
            'typeName' => $invitation->eventType?->name ?? 'Sin tipo',
            'templateLabel' => $template['label'],
            'guestCount' => $guests,
            'confirmedCount' => $confirmed,
            'pendingCount' => (int) ($invitation->pending_guests_count ?? 0),
            'confirmedPasses' => (int) ($invitation->confirmed_passes_sum ?? 0),
            'contributionCount' => (int) ($invitation->contributions_count ?? 0),
            'client' => $invitation->user ? ['name' => $invitation->user->name, 'username' => $invitation->user->username] : null,
            'eventDateLabel' => $invitation->event_date?->locale('es')->translatedFormat('j \d\e F \d\e Y, H:i') ?? 'Sin fecha',
            'createdLabel' => $invitation->created_at?->locale('es')->translatedFormat('j \d\e F \d\e Y') ?? '',
            'expiresLabel' => $invitation->expires_at?->locale('es')->translatedFormat('j \d\e F \d\e Y') ?? 'Sin vencimiento',
            'isExpired' => (bool) $invitation->expires_at?->isBefore(now()->startOfDay()),
            'isPast' => (bool) $invitation->is_post_event,
            'publicUrl' => route('invitation.show', $invitation->slug),
            // Cuántos ya contestaron (confirmaron o avisaron que no van), para leerlo de un vistazo
            'responseRate' => $guests > 0 ? (int) round(($guests - (int) ($invitation->pending_guests_count ?? 0)) / $guests * 100) : null,
        ];
    }

    /**
     * Tipos de evento del catálogo con cuántas invitaciones tiene cada uno.
     *
     * @return Collection<int, array{slug: string, name: string, isCard: bool, total: int}>
     */
    private function eventTypes()
    {
        return once(function () {
            $counts = Invitation::query()
                ->select('event_type_id', DB::raw('count(*) as total'))
                ->groupBy('event_type_id')
                ->pluck('total', 'event_type_id');

            return EventType::query()
                ->orderByRaw("case when kind = '".Module::KIND_CARD."' then 1 else 0 end")
                ->orderBy('name')
                ->get(['id', 'slug', 'name', 'kind'])
                ->map(fn (EventType $eventType) => [
                    'slug' => $eventType->slug,
                    'name' => $eventType->name,
                    'isCard' => $eventType->kind === Module::KIND_CARD,
                    'total' => (int) ($counts[$eventType->id] ?? 0),
                ]);
        });
    }
}
