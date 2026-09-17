<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationLocation;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Concerns\ReplacesOrderedRows;
use App\Modules\Module;

/** Ubicación del evento: una fila, lista para sumar otra (ceremonia y fiesta) sin cambiar el esquema. */
class LocationModule extends Module
{
    use ReadsValues, ReplacesOrderedRows;

    public function code(): string
    {
        return 'ubicacion';
    }

    public function label(): string
    {
        return 'Ubicación';
    }

    public function defaults(): array
    {
        return ['lat' => -16.5, 'lng' => -68.15];
    }

    public function relations(): array
    {
        return ['locations'];
    }

    public function load(Invitation $invitation): array
    {
        $location = $invitation->locations->first();

        if (! $location instanceof InvitationLocation) {
            return [];
        }

        return $this->compact([
            'lat' => $location->latitude,
            'lng' => $location->longitude,
            'nombre_lugar' => $location->name,
            'direccion' => $location->address,
            'maps_url' => $location->map_url,
            'nota' => $location->note,
            'imagen_lugar' => $location->image_url,
        ]);
    }

    public function save(Invitation $invitation, array $data): void
    {
        $row = [
            'name' => $this->text($data['nombre_lugar'] ?? null, 255),
            'address' => $this->text($data['direccion'] ?? null, 500),
            'latitude' => $this->number($data['lat'] ?? null),
            'longitude' => $this->number($data['lng'] ?? null),
            'map_url' => $this->text($data['maps_url'] ?? null),
            'image_url' => $this->text($data['imagen_lugar'] ?? null),
            'note' => $this->text($data['nota'] ?? null),
        ];

        $hasData = array_filter($row, fn ($value) => $value !== null) !== [];

        $this->replaceOrdered($invitation->locations(), $hasData ? [$row] : []);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['nombre_lugar'] ?? null)
            || $this->filled($data['direccion'] ?? null)
            || $this->filled($data['maps_url'] ?? null)
            || $this->filled($data['imagen_lugar'] ?? null);
    }
}
