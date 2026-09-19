<?php

namespace App\EventProfiles;

class BirthdayProfile extends EventProfile
{
    public function code(): string
    {
        return 'cumple';
    }

    public function label(): string
    {
        return 'Cumpleaños';
    }

    public function modules(): array
    {
        return [
            'bienvenida', 'ubicacion', 'itinerario', 'rsvp', 'galeria', 'dress_code', 'destacados',
            'video', 'musica', 'playlist', 'hashtag', 'encuestas', 'regalos', 'cuenta_regresiva',
            'agendar', 'fotomural', 'post_evento',
        ];
    }

    /** La edad es un dato: la plantilla la muestra en grande sobre el pastel. */
    public function heroFields(): array
    {
        return [
            'nombre' => ['label' => 'Nombre de quien cumple años', 'placeholder' => 'Ej. Valeria'],
            'edad' => ['label' => 'Edad que cumple', 'placeholder' => 'Ej. 30', 'type' => 'number', 'help' => 'Se muestra en grande en la portada y en las velas del pastel.'],
        ];
    }

    public function featuredGroups(): array
    {
        return [
            'chambelanes' => ['Amigos', 'Amigo'],
            'damitas' => ['Familia', 'Familiar'],
            'padrinos' => ['Anfitriones', 'Anfitrión'],
        ];
    }

    public function required(): array
    {
        return [
            'bienvenida' => ['nombre' => 'Falta el nombre de quien cumple años', 'edad' => 'Falta la edad que cumple'],
            'ubicacion' => ['nombre_lugar' => 'Falta el lugar de la fiesta'],
        ];
    }

    public function sample(): array
    {
        return ['name' => 'Valeria', 'subtitle' => 'Mis 30 años'];
    }
}
