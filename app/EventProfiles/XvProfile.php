<?php

namespace App\EventProfiles;

class XvProfile extends EventProfile
{
    public function code(): string
    {
        return 'xv';
    }

    public function label(): string
    {
        return __('XV años');
    }

    public function modules(): array
    {
        return [
            'bienvenida', 'ubicacion', 'itinerario', 'dress_code', 'destacados', 'galeria', 'video',
            'musica', 'playlist', 'hashtag', 'encuestas', 'regalos', 'rsvp', 'rsvp_whatsapp', 'cuenta_regresiva',
            'agendar', 'fotomural', 'post_evento',
        ];
    }

    public function heroFields(): array
    {
        return [
            'nombre' => ['label' => __('Nombre de la quinceañera'), 'placeholder' => __('Ej. Sofía Valentina')],
        ];
    }

    public function featuredGroups(): array
    {
        return [
            'chambelanes' => [__('Chambelanes'), __('Chambelán')],
            'damitas' => [__('Damitas'), __('Damita')],
            'padrinos' => [__('Padrinos'), __('Padrino')],
        ];
    }

    public function required(): array
    {
        return [
            'bienvenida' => ['nombre' => __('Falta el nombre de la quinceañera'), 'imagen_hero' => __('Falta la foto de portada')],
            'ubicacion' => ['nombre_lugar' => __('Falta el lugar de la fiesta')],
            'itinerario' => ['eventos' => __('No hay ningún momento cargado')],
        ];
    }

    public function sample(): array
    {
        return ['name' => __('Sofía Valentina'), 'subtitle' => __('Celebrando mis XV años')];
    }
}
