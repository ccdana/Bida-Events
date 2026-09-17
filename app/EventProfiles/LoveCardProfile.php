<?php

namespace App\EventProfiles;

use App\Modules\Module;

/** Tarjeta del Día del Amor (21 de septiembre): de una persona a otra. */
class LoveCardProfile extends EventProfile
{
    public function code(): string
    {
        return 'amor';
    }

    public function label(): string
    {
        return 'Día del Amor';
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
        return ['bienvenida', 'dedicatoria', 'juntos_desde', 'galeria', 'musica', 'video', 'respuesta'];
    }

    public function enabledByDefault(): array
    {
        return ['bienvenida', 'dedicatoria', 'respuesta'];
    }

    /** La portada de la tarjeta lleva la foto y una frase; los nombres van en la dedicatoria. */
    public function heroFields(): array
    {
        return [];
    }

    public function required(): array
    {
        return [
            // La portada de la tarjeta no lleva nombres: solo la foto de los dos
            'bienvenida' => ['imagen_hero' => 'Falta la foto de portada'],
            'dedicatoria' => [
                'para' => 'Falta para quién es la tarjeta',
                'de' => 'Falta quién la manda',
                'mensaje' => 'Falta el mensaje',
            ],
        ];
    }

    public function sample(): array
    {
        return ['name' => 'Para Ana', 'subtitle' => 'Feliz Día del Amor'];
    }
}
