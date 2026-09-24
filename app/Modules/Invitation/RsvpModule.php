<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationRsvpSetting;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Module;

/** Textos de la confirmación de asistencia y el WhatsApp que la recibe (paquete Estándar); las respuestas con pase viven en guests. */
class RsvpModule extends Module
{
    use ReadsValues;

    public function code(): string
    {
        return 'rsvp';
    }

    public function label(): string
    {
        return 'Confirmación de asistencia';
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
            'whatsapp' => $settings->whatsapp,
        ]) : [];
    }

    public function save(Invitation $invitation, array $data): void
    {
        $attributes = [
            'title' => $this->text($data['titulo_confirmacion'] ?? null, 255),
            'message' => $this->text($data['mensaje_personalizado'] ?? null),
            'confirmed_text' => $this->text($data['texto_confirmado'] ?? null),
            'declined_text' => $this->text($data['texto_declinado'] ?? null),
            // WhatsApp que recibe las confirmaciones del paquete Estándar: solo dígitos, con código de país
            'whatsapp' => substr(preg_replace('/\D+/', '', (string) ($data['whatsapp'] ?? '')) ?? '', 0, 20) ?: null,
        ];

        if (array_filter($attributes) === []) {
            InvitationRsvpSetting::where('invitation_id', $invitation->id)->delete();

            return;
        }

        InvitationRsvpSetting::updateOrCreate(['invitation_id' => $invitation->id], $attributes);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['titulo_confirmacion'] ?? null)
            || $this->filled($data['mensaje_personalizado'] ?? null)
            || $this->filled($data['texto_confirmado'] ?? null)
            || $this->filled($data['texto_declinado'] ?? null)
            || $this->filled($data['whatsapp'] ?? null);
    }
}
