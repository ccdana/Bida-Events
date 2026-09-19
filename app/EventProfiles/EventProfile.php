<?php

namespace App\EventProfiles;

use App\Modules\Module;

/**
 * Perfil de un tipo de evento: qué producto es (invitación o tarjeta), qué módulos ofrece, cómo
 * se llaman las cosas en ese evento y qué datos son obligatorios. El editor, las validaciones y
 * los avisos de «qué falta» leen el perfil en vez de preguntar «¿es una boda?».
 *
 * Nuevo tipo de evento o temporada: una clase que extienda esta, registrada en
 * config/event_profiles.php, y las plantillas del catálogo que apunten a su código.
 */
abstract class EventProfile
{
    /** Código estable; coincide con event_types.code y con la clave "event" de las plantillas. */
    abstract public function code(): string;

    /** Nombre del tipo de evento para el panel: «XV años», «Boda», «Día del Amor». */
    abstract public function label(): string;

    /** Módulos que ofrece este evento, en el orden de las pestañas del editor (sin config). */
    abstract public function modules(): array;

    public function kind(): string
    {
        return Module::KIND_INVITATION;
    }

    /** Temporada de las tarjetas (amor, halloween…); null en invitaciones. */
    public function season(): ?string
    {
        return null;
    }

    /** Módulos encendidos al crear una invitación de este tipo. */
    public function enabledByDefault(): array
    {
        return ['bienvenida'];
    }

    /**
     * Campos de la portada que pide este evento, con su rótulo y ejemplo.
     *
     * @return array<string, array{label: string, placeholder: string, help?: string, type?: string}>
     */
    public function heroFields(): array
    {
        return [
            'nombre' => ['label' => 'Nombre', 'placeholder' => 'Ej. Sofía Valentina'],
        ];
    }

    /**
     * Grupos de personas destacadas: clave del grupo => [plural, singular].
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public function featuredGroups(): array
    {
        return [];
    }

    /**
     * Datos sin los que la invitación queda incompleta: módulo => [ruta del campo => aviso].
     * La ruta es relativa al módulo; una lista cuenta como completa si tiene elementos.
     *
     * @return array<string, array<string, string>>
     */
    public function required(): array
    {
        return [
            'bienvenida' => [
                'nombre' => 'Falta el nombre',
                'imagen_hero' => 'Falta la foto de portada',
            ],
        ];
    }

    /** Textos de ejemplo del editor (muestra de tipografías y marcadores). */
    public function sample(): array
    {
        return ['name' => 'Sofía Valentina', 'subtitle' => 'Celebrando mis XV años'];
    }

    /** Lo que viaja al editor. */
    public function toArray(): array
    {
        return [
            'code' => $this->code(),
            'label' => $this->label(),
            'kind' => $this->kind(),
            'season' => $this->season(),
            'modules' => $this->modules(),
            'enabledByDefault' => $this->enabledByDefault(),
            'heroFields' => $this->heroFields(),
            'featuredGroups' => $this->featuredGroups(),
            'required' => $this->required(),
            'sample' => $this->sample(),
        ];
    }
}
