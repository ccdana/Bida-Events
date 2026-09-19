<?php

namespace App\EventProfiles;

use App\Modules\Module;

/**
 * Tarjeta «Libro de aventuras» del Día del Amor (21 de septiembre): un cuaderno de recortes que se
 * hojea, con el mes del aniversario, la carta, su historia por capítulos, recuerdos, collages de
 * flores amarillas, fotos con marco y un juego de memoria.
 */
class AdventureBookProfile extends EventProfile
{
    public function code(): string
    {
        return 'aventura';
    }

    public function label(): string
    {
        return 'Libro de aventuras';
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
        return ['bienvenida', 'juntos_desde', 'dedicatoria', 'historia', 'recuerdos', 'collage', 'marcos', 'memoria', 'musica', 'respuesta', 'aventuras'];
    }

    public function enabledByDefault(): array
    {
        return ['bienvenida', 'juntos_desde', 'dedicatoria', 'historia', 'recuerdos', 'collage', 'memoria', 'respuesta', 'aventuras'];
    }

    /** La tapa lleva la foto y una frase; los nombres van en la dedicatoria. */
    public function heroFields(): array
    {
        return [];
    }

    public function required(): array
    {
        return [
            'bienvenida' => ['imagen_hero' => 'Falta la foto de la tapa'],
            'juntos_desde' => ['fecha' => 'Falta la fecha del aniversario'],
            'dedicatoria' => [
                'para' => 'Falta para quién es el libro',
                'de' => 'Falta quién lo manda',
                'mensaje' => 'Falta la carta',
            ],
        ];
    }

    public function sample(): array
    {
        return ['name' => 'Para Ana', 'subtitle' => 'Nuestro libro de aventuras'];
    }
}
