<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Concerns\ReplacesOrderedRows;
use App\Modules\Module;

/**
 * Personas destacadas por grupo (chambelanes, damitas, padrinos…). El nombre del grupo lo define
 * cada perfil de evento; la tabla admite cualquiera.
 */
class FeaturedPeopleModule extends Module
{
    use ReadsValues, ReplacesOrderedRows;

    public function code(): string
    {
        return 'destacados';
    }

    public function label(): string
    {
        return 'Invitados de honor';
    }

    public function defaults(): array
    {
        return ['chambelanes' => [], 'damitas' => [], 'padrinos' => []];
    }

    public function relations(): array
    {
        return ['featuredPeople'];
    }

    public function load(Invitation $invitation): array
    {
        $groups = [];

        foreach ($invitation->featuredPeople as $person) {
            $groups[$person->group][] = $this->compact([
                // Los padrinos se escriben con «nombres» (una pareja) y el resto con «nombre»
                $person->name_key => $person->name,
                'iniciales' => $person->initials,
                'rol' => $person->role,
                'detalle' => $person->detail,
                'mensaje' => $person->message,
            ]);
        }

        return $groups;
    }

    public function save(Invitation $invitation, array $data): void
    {
        $rows = [];

        foreach ($this->items($data) as $group => $people) {
            if (! is_string($group) || ! is_array($people) || ! array_is_list($people)) {
                continue;
            }

            foreach ($people as $person) {
                if (! is_array($person)) {
                    continue;
                }

                $nameKey = array_key_exists('nombres', $person) ? 'nombres' : 'nombre';

                $rows[] = [
                    'group' => mb_substr($group, 0, 50),
                    'name_key' => $nameKey,
                    'name' => $this->text($person[$nameKey] ?? null, 255),
                    'initials' => $this->text($person['iniciales'] ?? null, 10),
                    'role' => $this->text($person['rol'] ?? null, 255),
                    'detail' => $this->text($person['detalle'] ?? null),
                    'message' => $this->text($person['mensaje'] ?? null),
                ];
            }
        }

        $this->replaceOrdered($invitation->featuredPeople(), $rows);
    }

    public function hasContent(array $data): bool
    {
        foreach ($this->items($data) as $people) {
            if (is_array($people) && $people !== []) {
                return true;
            }
        }

        return false;
    }
}
