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
        return 'XV años';
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
            'nombre' => ['label' => 'Nombre de la quinceañera', 'placeholder' => 'Ej. Sofía Valentina'],
        ];
    }

    public function featuredGroups(): array
    {
        return [
            'chambelanes' => ['Chambelanes', 'Chambelán'],
            'damitas' => ['Damitas', 'Damita'],
            'padrinos' => ['Padrinos', 'Padrino'],
        ];
    }

    public function required(): array
    {
        return [
            'bienvenida' => ['nombre' => 'Falta el nombre de la quinceañera', 'imagen_hero' => 'Falta la foto de portada'],
            'ubicacion' => ['nombre_lugar' => 'Falta el lugar de la fiesta'],
            'itinerario' => ['eventos' => 'No hay ningún momento cargado'],
        ];
    }

    public function sample(): array
    {
        return ['name' => 'Sofía Valentina', 'subtitle' => 'Celebrando mis XV años'];
    }
}
