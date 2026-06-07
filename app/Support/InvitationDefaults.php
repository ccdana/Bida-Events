<?php

namespace App\Support;

use Database\Seeders\XvSofiaModuleData;

class InvitationDefaults
{
    public static function modules(): array
    {
        $modules = XvSofiaModuleData::all();

        $modules['bienvenida']['nombre_quinceanera'] = 'Nombre de la Quinceañera';
        $modules['bienvenida']['subtitulo'] = 'Celebrando mis XV Años';
        $modules['bienvenida']['mensaje'] = 'Te invito a ser parte de esta noche especial.';
        $modules['bienvenida']['fecha_texto'] = now()->addMonths(3)->translatedFormat('l j \d\e F, Y');

        return $modules;
    }

    public static function templates(): array
    {
        return [
            'invitations.templates.xv-premium' => 'XV Años Premium',
        ];
    }

    public static function itineraryIcons(): array
    {
        return ['glass', 'candle', 'dance', 'dinner', 'music', 'star'];
    }
}
