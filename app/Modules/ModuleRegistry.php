<?php

namespace App\Modules;

use App\Models\Invitation;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Los módulos registrados en config/modules.php, en su orden. Es la única lista de módulos del
 * sistema: de aquí salen los códigos que envía el editor, las formas vacías, la visibilidad por
 * defecto, las relaciones que hay que cargar y las reglas de los módulos nuevos.
 */
class ModuleRegistry
{
    /** @var array<string, Module>|null */
    protected ?array $modules = null;

    /** @return array<string, Module> código => módulo */
    public function all(): array
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        $this->modules = [];

        foreach (config('modules.modules', []) as $entry) {
            $module = is_array($entry) ? new $entry[0](...array_slice($entry, 1)) : app($entry);

            if (! $module instanceof Module) {
                throw new InvalidArgumentException('Los módulos registrados deben extender '.Module::class.'.');
            }

            if (isset($this->modules[$module->code()])) {
                throw new InvalidArgumentException("El módulo «{$module->code()}» está registrado dos veces.");
            }

            $this->modules[$module->code()] = $module;
        }

        return $this->modules;
    }

    /** @return list<string> */
    public function codes(): array
    {
        return array_keys($this->all());
    }

    public function has(string $code): bool
    {
        return isset($this->all()[$code]);
    }

    public function get(string $code): Module
    {
        return $this->all()[$code] ?? throw new InvalidArgumentException("No hay un módulo registrado con el código «{$code}».");
    }

    /** @return array<string, Module> Módulos disponibles para un producto (invitación o tarjeta). */
    public function forKind(string $kind): array
    {
        return array_filter($this->all(), fn (Module $module) => in_array($kind, $module->kinds(), true));
    }

    /** Forma vacía de todos los módulos, como la espera el editor. */
    public function emptyModules(): array
    {
        return array_map(fn (Module $module) => $module->defaults(), $this->all());
    }

    /** @return array<string, bool> Visibilidad con la que nace una invitación nueva (sin config). */
    public function visibilityDefaults(): array
    {
        $flags = [];

        foreach ($this->all() as $code => $module) {
            if ($code !== 'config') {
                $flags[$code] = $module->visibleByDefault();
            }
        }

        return $flags;
    }

    /** @return list<string> Relaciones de Invitation que necesitan todos los módulos, sin repetir. */
    public function relations(): array
    {
        return array_values(array_unique(array_merge(...array_map(fn (Module $module) => $module->relations(), array_values($this->all())))));
    }

    /** Lee todos los módulos desde sus tablas, con una carga de relaciones. */
    public function load(Invitation $invitation): array
    {
        $invitation->loadMissing($this->relations());

        $modules = [];

        foreach ($this->all() as $code => $module) {
            $modules[$code] = $module->load($invitation);
        }

        return $modules;
    }

    /** Guarda cada módulo en sus tablas (el llamador abre la transacción). */
    public function save(Invitation $invitation, array $modules): void
    {
        foreach ($this->all() as $code => $module) {
            $data = $modules[$code] ?? [];
            $module->save($invitation, is_array($data) ? $data : (array) $data);
        }

        // Lo leído antes del guardado ya no vale
        foreach ($this->relations() as $relation) {
            $invitation->unsetRelation(Str::before($relation, '.'));
        }
    }

    /** Reglas que declaran los módulos (los anteriores siguen en InvitationModuleRules). */
    public function rules(string $prefix): array
    {
        return array_merge(...array_map(fn (Module $module) => $module->rules($prefix), array_values($this->all())));
    }

    public function attributes(string $prefix): array
    {
        return array_merge(...array_map(fn (Module $module) => $module->attributes($prefix), array_values($this->all())));
    }
}
