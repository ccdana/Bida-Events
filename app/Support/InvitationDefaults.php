<?php

namespace App\Support;

use Database\Seeders\XvSofiaModuleData;

class InvitationDefaults
{
    public static function moduleCodes(): array
    {
        return [
            'config',
            'bienvenida',
            'ubicacion',
            'itinerario',
            'dress_code',
            'destacados',
            'galeria',
            'musica',
            'video',
            'playlist',
            'hashtag',
            'encuestas',
            'regalos',
            'post_evento',
            'rsvp',
            'cuenta_regresiva',
            'agendar',
            'fotomural',
        ];
    }

    public static function moduleTabMap(): array
    {
        return [
            'bienvenida' => 'hero',
            'ubicacion' => 'ubicacion',
            'itinerario' => 'itinerario',
            'dress_code' => 'dress',
            'destacados' => 'destacados',
            'galeria' => 'galeria',
            'video' => 'video',
            'musica' => 'musica',
            'playlist' => 'playlist',
            'hashtag' => 'hashtag',
            'encuestas' => 'encuestas',
            'regalos' => 'regalos',
            'rsvp' => 'rsvp',
            'cuenta_regresiva' => 'countdown',
            'agendar' => 'agendar',
            'fotomural' => 'fotomural',
            'post_evento' => 'post_evento',
        ];
    }

    public static function moduleVisibilityDefaults(): array
    {
        return [
            'bienvenida' => true,
            'video' => false,
            'musica' => false,
            'galeria' => false,
            'itinerario' => false,
            'dress_code' => false,
            'destacados' => false,
            'ubicacion' => false,
            'hashtag' => false,
            'encuestas' => false,
            'playlist' => false,
            'regalos' => false,
            'rsvp' => false,
            'fotomural' => false,
            'cuenta_regresiva' => false,
            'agendar' => false,
            'post_evento' => false,
        ];
    }

    public static function modules(): array
    {
        $modules = XvSofiaModuleData::all();

        $modules['bienvenida']['nombre_quinceanera'] = 'Nombre de la Quinceañera';
        $modules['bienvenida']['subtitulo'] = 'Celebrando mis XV Años';
        $modules['bienvenida']['mensaje'] = 'Te invito a ser parte de esta noche especial.';
        $modules['bienvenida']['fecha_texto'] = now()->addMonths(3)->translatedFormat('l j \d\e F, Y');

        return $modules;
    }

    /**
     * Retorna módulos vacíos para nuevas invitaciones
     */
    public static function emptyModules(): array
    {
        return [
            'config' => [
                'colores' => [
                    'primary' => '#C9A96E',
                    'secondary' => '#2C1810',
                    'accent' => '#F5E6D3',
                    'text' => '#1A1A1A',
                    'background' => '#FFFAF5',
                ],
                'tipografias' => [
                    'titulos' => 'Playfair Display',
                    'cuerpo' => 'Montserrat',
                    'script' => 'Great Vibes',
                ],
                'modulos' => self::moduleVisibilityDefaults(),
                'template' => 'pages.invitations.templates.xv-premium',
            ],
            'bienvenida' => (object) [],
            'ubicacion' => ['lat' => -16.5, 'lng' => -68.15],
            'itinerario' => ['titulo' => 'Itinerario', 'eventos' => []],
            'dress_code' => ['sugerencias' => [], 'colores_permitidos' => [], 'colores_prohibidos' => []],
            'destacados' => ['chambelanes' => [], 'damitas' => [], 'padrinos' => []],
            'galeria' => ['fotos' => []],
            'musica' => (object) [],
            'video' => (object) [],
            'playlist' => (object) [],
            'hashtag' => (object) [],
            'encuestas' => ['preguntas' => []],
            'regalos' => [
                'sobres' => ['titulo' => '', 'direccion' => ''],
                'banco' => [
                    'banco' => '',
                    'titular' => '',
                    'ci' => '',
                    'cuenta' => '',
                    'qr_url' => '',
                ],
                'titulo' => '',
                'tienda_url' => '',
                'tienda_texto' => '',
                'opciones' => [],
            ],
            'post_evento' => (object) [],
            'rsvp' => (object) [],
        ];
    }

    public static function templates(): array
    {
        return [
            'pages.invitations.templates.xv-premium' => 'XV Años Premium',
        ];
    }

    public static function itineraryIcons(): array
    {
        return ['glass', 'candle', 'dance', 'dinner', 'music', 'star'];
    }
}
