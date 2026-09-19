<?php

namespace App\ViewModels\Admin;

use App\Models\Guest;
use App\Models\Invitation;
use App\Modules\Module;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DashboardViewData
{
    public const KINDS = [Module::KIND_INVITATION, Module::KIND_CARD];

    public function make(LengthAwarePaginator $invitations, ?string $kind = null): array
    {
        $items = collect($invitations->items())->map(fn (Invitation $invitation) => [
            'invitation' => $invitation,
            'statusClass' => $invitation->status === 'active' ? 'is-active' : 'is-draft',
            'statusLabel' => $invitation->status === 'active' ? 'Activa' : 'Inactiva',
            'guestCount' => (int) ($invitation->guests_count ?? 0),
            'eventTypeName' => $invitation->eventType?->name ?? 'Sin tipo',
            'isCard' => $invitation->eventType?->kind === Module::KIND_CARD,
            'eventDateLabel' => $invitation->event_date?->format('d-m-Y H:i') ?? '',
        ])->values();

        // Las cifras son de todas las invitaciones, no solo de la página que se muestra
        $total = Invitation::count();
        $active = Invitation::where('status', 'active')->count();

        $metrics = [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'guests' => Guest::count(),
        ];

        $filters = [
            ['kind' => null, 'label' => 'Todo', 'url' => route('admin.dashboard')],
            ['kind' => Module::KIND_INVITATION, 'label' => 'Invitaciones', 'url' => route('admin.dashboard', ['tipo' => Module::KIND_INVITATION])],
            ['kind' => Module::KIND_CARD, 'label' => 'Tarjetas', 'url' => route('admin.dashboard', ['tipo' => Module::KIND_CARD])],
        ];

        return compact('items', 'metrics', 'invitations', 'kind', 'filters');
    }
}
