<?php

namespace App\Modules;

use App\Models\Invitation;

/**
 * Un módulo de invitación o tarjeta (portada, galería, dedicatoria…) como unidad completa:
 * sabe leer y guardar sus tablas, con qué forma lo reciben el editor y las vistas, cuándo tiene
 * contenido y qué reglas valida.
 *
 * La «forma» es el arreglo con claves en español que ya usan el editor (Alpine) y las plantillas
 * (Blade), por ejemplo ['titulo' => …, 'fotos' => […]]. Los módulos traducen esa forma a sus
 * tablas y de vuelta, así el almacenamiento cambia sin tocar las vistas.
 *
 * Agregar un módulo: una clase que extienda esta, su migración y modelo, y registrarla en
 * config/modules.php. Ver docs/temporadas.md.
 */
abstract class Module
{
    public const KIND_INVITATION = 'invitation';

    public const KIND_CARD = 'card';

    /** Código estable del módulo; también es la clave en el arreglo de módulos y en features. */
    abstract public function code(): string;

    /** Nombre para el panel y el menú. */
    abstract public function label(): string;

    /**
     * Forma vacía del módulo. Un objeto vacío se escribe como (object) [] para que el editor
     * lo reciba como {} y no como [].
     */
    abstract public function defaults(): array|object;

    /** Lee sus tablas (con las relaciones de relations() ya cargadas) y devuelve la forma. */
    abstract public function load(Invitation $invitation): array;

    /** Guarda la forma en sus tablas. Se llama dentro de la transacción del guardado. */
    abstract public function save(Invitation $invitation, array $data): void;

    /** Productos en los que tiene sentido este módulo. */
    public function kinds(): array
    {
        return [self::KIND_INVITATION];
    }

    /** Relaciones de Invitation que load() necesita, para cargarlas todas de una vez. */
    public function relations(): array
    {
        return [];
    }

    /** Si trae datos cargados: un módulo con contenido se muestra aunque venga apagado. */
    public function hasContent(array $data): bool
    {
        return false;
    }

    /** Visibilidad con la que nace en una invitación nueva. */
    public function visibleByDefault(): bool
    {
        return false;
    }

    /** Reglas de validación de su forma, bajo el prefijo del formulario (p. ej. modulos_data). */
    public function rules(string $prefix): array
    {
        return [];
    }

    /** Nombres legibles de sus campos para los mensajes de validación. */
    public function attributes(string $prefix): array
    {
        return [];
    }

    /** Vista pública propia (módulos nuevos). Los módulos anteriores siguen en shell/modules. */
    public function partial(): ?string
    {
        return null;
    }

    /** Panel del editor propio (módulos nuevos). */
    public function panel(): ?string
    {
        return null;
    }
}
