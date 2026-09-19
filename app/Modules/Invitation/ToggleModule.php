<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Modules\Module;

/**
 * Módulos que no guardan datos propios, solo se encienden o apagan (cuenta regresiva, agendar,
 * fotomural): su contenido sale de la fecha del evento o de los aportes de los invitados.
 */
class ToggleModule extends Module
{
    public function __construct(
        protected string $code,
        protected string $label,
        protected array $kinds = [self::KIND_INVITATION],
    ) {}

    public function code(): string
    {
        return $this->code;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function kinds(): array
    {
        return $this->kinds;
    }

    public function defaults(): array
    {
        return [];
    }

    public function load(Invitation $invitation): array
    {
        return [];
    }

    public function save(Invitation $invitation, array $data): void
    {
        // Nada que guardar: la visibilidad la guarda ConfigModule
    }
}
