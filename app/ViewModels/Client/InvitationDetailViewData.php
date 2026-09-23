<?php

namespace App\ViewModels\Client;

use App\EventProfiles\EventProfiles;
use App\Models\GuestContribution;
use App\Models\Invitation;
use App\Modules\Card\ReplyModule;
use App\Modules\Module;
use App\Support\CloudinaryImage;
use App\Support\InvitationTemplates;
use Illuminate\Support\Collection;

/**
 * Página de un evento en el panel del cliente. Separa lo que el cliente mira por separado:
 * sus invitados (con su enlace personal), las respuestas a su tarjeta y las fotos o canciones
 * que suben los invitados.
 */
class InvitationDetailViewData
{
    public function __construct(private EventProfiles $profiles) {}

    public function make(Invitation $invitation, Collection $guests, ?Collection $contributionRows = null): array
    {
        $profile = $this->profiles->forTemplate($invitation->template);
        $isCard = $profile->kind() === Module::KIND_CARD;

        $confirmed = $guests->where('status', 'confirmed');
        $declined = $guests->where('status', 'declined');
        $pending = $guests->where('status', 'pending');
        $totalPasses = (int) $guests->sum('passes_confirmed');
        $totalAllocatedPasses = (int) $guests->sum('passes_allocated');
        $confirmationRate = $totalAllocatedPasses > 0
            ? round(($totalPasses / $totalAllocatedPasses) * 100, 1)
            : 0;

        $rows = $guests->map(fn ($guest) => [
            'guest' => $guest,
            'statusLabel' => match ($guest->status) {
                'confirmed' => 'Confirmado',
                'declined' => 'No asiste',
                'pending' => 'Pendiente',
                default => ucfirst((string) $guest->status),
            },
            'statusClass' => match ($guest->status) {
                'confirmed' => 'is-confirmed',
                'declined' => 'is-declined',
                default => 'is-pending',
            },
            'passesLabel' => $guest->passes_confirmed.'/'.$guest->passes_allocated,
            'dietaryRestrictions' => $guest->dietary_restrictions ?: 'Sin indicar',
            'link' => route('invitation.guest', [$invitation->slug, $guest->qr_code_token]),
            'whatsapp' => $this->whatsapp($guest->phone),
            // Solo se puede quitar a quien todavía no respondió: nadie pierde una confirmación por error
            'canRemove' => $guest->status === 'pending',
        ])->values();

        // Flores (u otras reacciones) de la plantilla, para decir con qué respondieron la tarjeta
        $reactions = InvitationTemplates::get($invitation->template)['reactions'] ?? [];

        // Fotos y canciones que el cliente puede ocultar de su invitación
        $contributions = ($contributionRows ?? new Collection)->map(fn (GuestContribution $contribution) => [
            'id' => $contribution->id,
            'isPhoto' => $contribution->type === 'live_photo',
            // Respuesta del destinatario de una tarjeta: se lee completa
            'isReply' => $contribution->type === ReplyModule::CONTRIBUTION_TYPE,
            'url' => $contribution->type === 'live_photo' ? CloudinaryImage::url($contribution->file_path, 200) : null,
            'text' => $contribution->type === 'live_photo' ? 'Foto del fotomural' : (string) $contribution->content_text,
            'author' => $contribution->guest?->name,
            'date' => $contribution->created_at?->locale('es')->translatedFormat('j \d\e F, H:i'),
            'meta' => collect([$contribution->guest?->name, $contribution->created_at?->diffForHumans()])->filter()->implode(' · '),
            'isHidden' => $contribution->moderation_status === GuestContribution::HIDDEN,
            'reaction' => $contribution->reaction ? ($reactions[$contribution->reaction] ?? null) : null,
        ])->values();

        $replies = $contributions->where('isReply', true)->values();
        $media = $contributions->where('isReply', false)->values();

        $isPublished = $invitation->status === 'active'
            && (! $invitation->expires_at || ! $invitation->expires_at->isBefore(now()->startOfDay()));

        return compact(
            'invitation',
            'guests',
            'contributions',
            'replies',
            'media',
            'confirmed',
            'declined',
            'pending',
            'totalPasses',
            'totalAllocatedPasses',
            'confirmationRate',
            'rows',
            'isCard',
        ) + [
            'kindLabel' => $isCard ? 'Tarjeta' : 'Invitación',
            'typeLabel' => $profile->label(),
            'templateLabel' => InvitationTemplates::get($invitation->template)['label'],
            'publicUrl' => $isPublished ? route('invitation.show', $invitation->slug) : null,
            'unavailableReason' => $isPublished
                ? null
                : ($invitation->status === 'active' ? 'El enlace venció: escríbenos para renovarlo.' : 'Todavía sin publicar: la estamos preparando.'),
            // Las tarjetas se mandan a una sola persona: no llevan lista de invitados
            'canAddGuests' => ! $isCard,
        ];
    }

    /** Los celulares bolivianos de 8 dígitos se completan con el código de país para WhatsApp. */
    private function whatsapp(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (strlen($digits) === 8) {
            $digits = "591{$digits}";
        }

        return strlen($digits) >= 8 ? "https://wa.me/{$digits}" : null;
    }
}
