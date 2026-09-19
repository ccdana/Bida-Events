<?php

namespace App\EventProfiles;

use App\Modules\Module;

/**
 * «Nuestra historia» (plantilla we-story-together): la historia de una pareja contada en cuatro
 * actos, del reflejo de la luna en el agua a un cielo estrellado. Producto aparte de la carta del
 * Día del Amor: reutiliza portada, juntos desde, galería, música, dedicatoria y respuesta, y suma
 * el módulo «relato». Nada es obligatorio para publicar: cada acto tiene textos de respaldo.
 */
class StoryCardProfile extends EventProfile
{
    public function code(): string
    {
        return 'historia';
    }

    public function label(): string
    {
        return 'Nuestra historia';
    }

    public function kind(): string
    {
        return Module::KIND_CARD;
    }

    public function season(): string
    {
        return 'amor';
    }

    public function modules(): array
    {
        return ['bienvenida', 'relato', 'juntos_desde', 'galeria', 'musica', 'dedicatoria', 'respuesta'];
    }

    public function enabledByDefault(): array
    {
        return ['bienvenida', 'relato', 'juntos_desde', 'galeria', 'musica', 'dedicatoria', 'respuesta'];
    }

    public function heroFields(): array
    {
        return [
            'nombre' => ['label' => 'Nombre', 'placeholder' => 'Ej. Ana'],
            'nombre_pareja' => ['label' => 'Nombre de su pareja', 'placeholder' => 'Ej. Luis'],
        ];
    }

    /** Solo avisos: la tarjeta se lee completa aunque falten, con los textos de respaldo de cada acto. */
    public function required(): array
    {
        return [
            'bienvenida' => [
                'nombre' => 'Faltan los nombres de la pareja',
                'imagen_hero' => 'Falta la primera foto juntos (la que se ve en el agua)',
            ],
            'relato' => ['anecdota' => 'Falta la anécdota: es el corazón del tercer acto'],
            'juntos_desde' => ['fecha' => 'Falta el día en que se conocieron'],
        ];
    }

    public function sample(): array
    {
        return ['name' => 'Ana & Luis', 'subtitle' => 'La historia que escribimos juntos'];
    }
}
