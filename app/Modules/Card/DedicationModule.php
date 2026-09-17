<?php

namespace App\Modules\Card;

use App\Models\CardDedication;
use App\Models\Invitation;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Module;

/** Dedicatoria de la tarjeta: de quién, para quién, el mensaje y la firma. */
class DedicationModule extends Module
{
    use ReadsValues;

    public function code(): string
    {
        return 'dedicatoria';
    }

    public function label(): string
    {
        return 'Dedicatoria';
    }

    public function kinds(): array
    {
        return [self::KIND_CARD];
    }

    public function defaults(): object
    {
        return (object) [];
    }

    public function visibleByDefault(): bool
    {
        return true;
    }

    public function relations(): array
    {
        return ['dedication'];
    }

    public function load(Invitation $invitation): array
    {
        $dedication = $invitation->dedication;

        return $dedication ? $this->compact([
            'de' => $dedication->from_name,
            'para' => $dedication->to_name,
            'mensaje' => $dedication->message,
            'firma' => $dedication->signature,
        ]) : [];
    }

    public function save(Invitation $invitation, array $data): void
    {
        $attributes = [
            'from_name' => $this->text($data['de'] ?? null, 255),
            'to_name' => $this->text($data['para'] ?? null, 255),
            'message' => $this->text($data['mensaje'] ?? null, 3000),
            'signature' => $this->text($data['firma'] ?? null, 255),
        ];

        if (array_filter($attributes) === []) {
            CardDedication::where('invitation_id', $invitation->id)->delete();

            return;
        }

        CardDedication::updateOrCreate(['invitation_id' => $invitation->id], $attributes);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['para'] ?? null) || $this->filled($data['mensaje'] ?? null);
    }

    public function rules(string $prefix): array
    {
        return [
            "{$prefix}.dedicatoria.de" => ['nullable', 'string', 'max:255'],
            "{$prefix}.dedicatoria.para" => ['nullable', 'string', 'max:255'],
            "{$prefix}.dedicatoria.mensaje" => ['nullable', 'string', 'max:3000'],
            "{$prefix}.dedicatoria.firma" => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(string $prefix): array
    {
        return [
            "{$prefix}.dedicatoria.de" => 'quién la manda',
            "{$prefix}.dedicatoria.para" => 'para quién es',
            "{$prefix}.dedicatoria.mensaje" => 'mensaje de la dedicatoria',
            "{$prefix}.dedicatoria.firma" => 'firma',
        ];
    }

    public function partial(): string
    {
        return 'invitations.partials.modules.dedicatoria';
    }

    public function panel(): string
    {
        return 'admin.invitations.panels.modules.dedicatoria';
    }
}
