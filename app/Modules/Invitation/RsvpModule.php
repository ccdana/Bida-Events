<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationRsvpSetting;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Module;

/**
 * Confirmación con pase QR: la respuesta queda guardada en guests y el invitado recibe su pase.
 * Guarda los textos del formulario (también los usa la confirmación por WhatsApp, que es otro
 * módulo: RsvpWhatsappModule). En una invitación solo puede estar encendida una de las dos.
 */
class RsvpModule extends Module
{
    use ReadsValues;

    public function code(): string
    {
        return 'rsvp';
    }

    public function label(): string
    {
        return 'Confirmación con pase QR';
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
        $settings = $invitation->rsvpSetting;

        return $settings ? $this->compact([
            'titulo_confirmacion' => $settings->title,
            'mensaje_personalizado' => $settings->message,
            'texto_confirmado' => $settings->confirmed_text,
            'texto_declinado' => $settings->declined_text,
        ]) : [];
    }

    public function save(Invitation $invitation, array $data): void
    {
        $attributes = [
            'title' => $this->text($data['titulo_confirmacion'] ?? null, 255),
            'message' => $this->text($data['mensaje_personalizado'] ?? null),
            'confirmed_text' => $this->text($data['texto_confirmado'] ?? null),
            'declined_text' => $this->text($data['texto_declinado'] ?? null),
        ];

        $settings = InvitationRsvpSetting::firstWhere('invitation_id', $invitation->id);

        // Sin textos ni número de WhatsApp (el otro módulo usa la misma fila), la fila sobra
        if (array_filter($attributes) === [] && ! $settings?->whatsapp) {
            $settings?->delete();

            return;
        }

        InvitationRsvpSetting::updateOrCreate(['invitation_id' => $invitation->id], $attributes);
        $invitation->unsetRelation('rsvpSetting');
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['titulo_confirmacion'] ?? null)
            || $this->filled($data['mensaje_personalizado'] ?? null)
            || $this->filled($data['texto_confirmado'] ?? null)
            || $this->filled($data['texto_declinado'] ?? null);
    }
}
