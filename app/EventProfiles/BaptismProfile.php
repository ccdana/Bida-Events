<?php

namespace App\EventProfiles;

class BaptismProfile extends EventProfile
{
    public function code(): string
    {
        return 'bautizo';
    }

    public function label(): string
    {
        return __('Bautizo');
    }

    public function modules(): array
    {
        return [
            'bienvenida', 'ubicacion', 'itinerario', 'destacados', 'galeria', 'dress_code', 'video',
            'musica', 'playlist', 'hashtag', 'encuestas', 'regalos', 'rsvp', 'rsvp_whatsapp', 'cuenta_regresiva',
            'agendar', 'fotomural', 'post_evento',
        ];
    }

    public function heroFields(): array
    {
        return [
            'nombre' => ['label' => __('Nombre del bebé'), 'placeholder' => __('Ej. Mateo Andrés')],
        ];
    }

    /** En un bautizo los padrinos son lo principal; el cortejo es la familia. */
    public function featuredGroups(): array
    {
        return [
            'padrinos' => [__('Padrinos'), __('Padrino')],
            'chambelanes' => [__('Abuelos'), __('Abuelo')],
            'damitas' => [__('Tíos'), __('Tío')],
        ];
    }

    public function required(): array
    {
        return [
            'bienvenida' => ['nombre' => __('Falta el nombre del bebé'), 'imagen_hero' => __('Falta la foto de portada')],
            'ubicacion' => ['nombre_lugar' => __('Falta la iglesia o el lugar')],
            'destacados' => ['padrinos' => __('Faltan los padrinos')],
        ];
    }

    public function sample(): array
    {
        return ['name' => __('Mateo Andrés'), 'subtitle' => __('Mi bautizo')];
    }
}
