<?php

namespace App\EventProfiles;

class GraduationProfile extends EventProfile
{
    public function code(): string
    {
        return 'graduacion';
    }

    public function label(): string
    {
        return 'Graduación';
    }

    public function modules(): array
    {
        return [
            'bienvenida', 'ubicacion', 'itinerario', 'rsvp', 'rsvp_whatsapp', 'destacados', 'galeria', 'dress_code',
            'video', 'musica', 'playlist', 'hashtag', 'encuestas', 'regalos', 'cuenta_regresiva',
            'agendar', 'fotomural', 'post_evento',
        ];
    }

    /** La carrera o el colegio va en el subtítulo de la portada, bajo el nombre. */
    public function heroFields(): array
    {
        return [
            'nombre' => ['label' => 'Nombre de quien se gradúa', 'placeholder' => 'Ej. Mariana Rojas'],
        ];
    }

    public function featuredGroups(): array
    {
        return [
            'padrinos' => ['Padrinos de promoción', 'Padrino'],
            'chambelanes' => ['Familia', 'Familiar'],
            'damitas' => ['Compañeros', 'Compañero'],
        ];
    }

    public function required(): array
    {
        return [
            'bienvenida' => ['nombre' => 'Falta el nombre de quien se gradúa', 'imagen_hero' => 'Falta la foto de portada'],
            'ubicacion' => ['nombre_lugar' => 'Falta el lugar de la fiesta'],
        ];
    }

    public function sample(): array
    {
        return ['name' => 'Mariana Rojas', 'subtitle' => 'Licenciatura en Arquitectura'];
    }
}
