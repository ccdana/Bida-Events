<?php

namespace App\Modules\Card;

use App\Models\Invitation;
use App\Modules\Concerns\HasSectionTexts;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Module;

/**
 * Respuesta del destinatario: un mensaje corto de vuelta. Aquí solo se guardan los textos de la
 * sección; cada respuesta es un aporte en guest_contributions (type = card_reply), con la misma
 * moderación y límite por minuto que las canciones.
 */
class ReplyModule extends Module
{
    use HasSectionTexts, ReadsValues;

    public const CODE = 'respuesta';

    public const CONTRIBUTION_TYPE = 'card_reply';

    public function code(): string
    {
        return self::CODE;
    }

    public function label(): string
    {
        return 'Respuesta';
    }

    public function kinds(): array
    {
        return [self::KIND_CARD];
    }

    public function defaults(): object
    {
        return (object) [];
    }

    public function relations(): array
    {
        return ['sections.feature'];
    }

    public function load(Invitation $invitation): array
    {
        $section = $this->section($invitation);

        return $this->compact([
            'titulo' => $section?->title,
            'descripcion' => $section?->intro,
            'placeholder' => $section?->placeholder,
        ]);
    }

    public function save(Invitation $invitation, array $data): void
    {
        $this->saveSection($invitation, [
            'title' => $this->text($data['titulo'] ?? null, 255),
            'intro' => $this->text($data['descripcion'] ?? null),
            'placeholder' => $this->text($data['placeholder'] ?? null, 255),
        ]);
    }

    public function rules(string $prefix): array
    {
        return [
            "{$prefix}.respuesta.titulo" => ['nullable', 'string', 'max:255'],
            "{$prefix}.respuesta.descripcion" => ['nullable', 'string', 'max:1000'],
            "{$prefix}.respuesta.placeholder" => ['nullable', 'string', 'max:255'],
        ];
    }

    public function partial(): string
    {
        return 'invitations.partials.modules.respuesta';
    }

    public function panel(): string
    {
        return 'admin.invitations.panels.modules.respuesta';
    }
}
