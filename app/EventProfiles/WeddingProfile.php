<?php

namespace App\EventProfiles;

class WeddingProfile extends EventProfile
{
    public function code(): string
    {
        return 'boda';
    }

    public function label(): string
    {
        return __('Boda');
    }

    public function modules(): array
    {
        return [
            'bienvenida', 'galeria', 'ubicacion', 'itinerario', 'dress_code', 'destacados', 'video',
            'musica', 'playlist', 'hashtag', 'encuestas', 'regalos', 'rsvp', 'rsvp_whatsapp', 'cuenta_regresiva',
            'agendar', 'fotomural', 'post_evento',
        ];
    }

    /** La pareja son dos personas: cada nombre en su campo. */
    public function heroFields(): array
    {
        return [
            'nombre' => ['label' => __('Nombre de uno de los novios'), 'placeholder' => __('Ej. Camila')],
            'nombre_pareja' => ['label' => __('Nombre de su pareja'), 'placeholder' => __('Ej. Andrés'), 'help' => __('La portada los une con un ampersand caligráfico.')],
        ];
    }

    public function featuredGroups(): array
    {
        return [
            'chambelanes' => [__('Caballeros de honor'), __('Caballero')],
            'damitas' => [__('Damas de honor'), __('Dama')],
            'padrinos' => [__('Padrinos'), __('Padrino')],
        ];
    }

    public function required(): array
    {
        return [
            'bienvenida' => [
                'nombre' => __('Falta el nombre de uno de los novios'),
                'nombre_pareja' => __('Falta el nombre de su pareja'),
                'imagen_hero' => __('Falta la foto de portada'),
            ],
            'ubicacion' => ['nombre_lugar' => __('Falta el lugar de la boda')],
            'itinerario' => ['eventos' => __('No hay ningún momento cargado')],
        ];
    }

    public function sample(): array
    {
        return ['name' => __('Ana & Luis'), 'subtitle' => __('Nos casamos')];
    }
}
