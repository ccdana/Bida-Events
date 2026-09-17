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
        return 'Bautizo';
    }

    public function modules(): array
    {
        return [
            'bienvenida', 'ubicacion', 'itinerario', 'destacados', 'galeria', 'dress_code', 'video',
            'musica', 'playlist', 'hashtag', 'encuestas', 'regalos', 'rsvp', 'cuenta_regresiva',
            'agendar', 'fotomural', 'post_evento',
        ];
    }

    public function heroFields(): array
    {
        return [
            'nombre' => ['label' => 'Nombre del bebé', 'placeholder' => 'Ej. Mateo Andrés'],
        ];
    }

    /** En un bautizo los padrinos son lo principal; el cortejo es la familia. */
    public function featuredGroups(): array
    {
        return [
            'padrinos' => ['Padrinos', 'Padrino'],
            'chambelanes' => ['Abuelos', 'Abuelo'],
            'damitas' => ['Tíos', 'Tío'],
        ];
    }

    public function required(): array
    {
        return [
            'bienvenida' => ['nombre' => 'Falta el nombre del bebé', 'imagen_hero' => 'Falta la foto de portada'],
            'ubicacion' => ['nombre_lugar' => 'Falta la iglesia o el lugar'],
            'destacados' => ['padrinos' => 'Faltan los padrinos'],
        ];
    }

    public function sample(): array
    {
        return ['name' => 'Mateo Andrés', 'subtitle' => 'Mi bautizo'];
    }
}
