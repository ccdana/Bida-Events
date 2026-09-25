<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationRsvpSetting;
use App\Modules\Module;

/**
 * Confirmación por WhatsApp: el invitado arma su respuesta y se la envía al organizador; nada se
 * guarda en el sistema. Es un módulo aparte de la confirmación con pase QR (rsvp) y en una
 * invitación solo puede estar encendido uno de los dos. Guarda el número que recibe las
 * respuestas; el título y el mensaje del formulario son los de rsvp (se comparten).
 */
class RsvpWhatsappModule extends Module
{
    public const CODE = 'rsvp_whatsapp';

    public function code(): string
    {
        return self::CODE;
    }

    public function label(): string
    {
        return 'Confirmación por WhatsApp';
    }

    public function defaults(): object
    {
        return (object) [];
    }

    public function relations(): array
    {
        return ['rsvpSetting'];
    }

    public function load(Invitation $invitation): array
    {
        $whatsapp = $invitation->rsvpSetting?->whatsapp;

        return $whatsapp ? ['whatsapp' => $whatsapp] : [];
    }

    public function save(Invitation $invitation, array $data): void
    {
        // Solo dígitos, con código de país
        $whatsapp = substr(preg_replace('/\D+/', '', (string) ($data['whatsapp'] ?? '')) ?? '', 0, 20) ?: null;
        $settings = InvitationRsvpSetting::firstWhere('invitation_id', $invitation->id);

        if ($settings === null) {
            if ($whatsapp !== null) {
                InvitationRsvpSetting::create(['invitation_id' => $invitation->id, 'whatsapp' => $whatsapp]);
            }

            return;
        }

        $settings->update(['whatsapp' => $whatsapp]);

        // Sin número ni textos de la confirmación, la fila sobra
        if (collect($settings->only(['title', 'message', 'confirmed_text', 'declined_text', 'whatsapp']))->filter()->isEmpty()) {
            $settings->delete();
        }

        $invitation->unsetRelation('rsvpSetting');
    }

    public function hasContent(array $data): bool
    {
        return trim((string) ($data['whatsapp'] ?? '')) !== '';
    }
}
