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
        return __('Graduación');
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
            'nombre' => ['label' => __('Nombre de quien se gradúa'), 'placeholder' => __('Ej. Mariana Rojas')],
        ];
    }

    public function featuredGroups(): array
    {
        return [
            'padrinos' => [__('Padrinos de promoción'), __('Padrino')],
            'chambelanes' => [__('Familia'), __('Familiar')],
            'damitas' => [__('Compañeros'), __('Compañero')],
        ];
    }

    public function required(): array
    {
        return [
            'bienvenida' => ['nombre' => __('Falta el nombre de quien se gradúa'), 'imagen_hero' => __('Falta la foto de portada')],
            'ubicacion' => ['nombre_lugar' => __('Falta el lugar de la fiesta')],
        ];
    }

    public function sample(): array
    {
        return ['name' => __('Mariana Rojas'), 'subtitle' => __('Licenciatura en Arquitectura')];
    }
}
