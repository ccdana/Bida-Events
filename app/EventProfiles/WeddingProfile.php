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
        return 'Boda';
    }

    public function modules(): array
    {
        return [
            'bienvenida', 'galeria', 'ubicacion', 'itinerario', 'dress_code', 'destacados', 'video',
            'musica', 'playlist', 'hashtag', 'encuestas', 'regalos', 'rsvp', 'cuenta_regresiva',
            'agendar', 'fotomural', 'post_evento',
        ];
    }

    /** La pareja son dos personas: cada nombre en su campo. */
    public function heroFields(): array
    {
        return [
            'nombre' => ['label' => 'Nombre de uno de los novios', 'placeholder' => 'Ej. Camila'],
            'nombre_pareja' => ['label' => 'Nombre de su pareja', 'placeholder' => 'Ej. Andrés', 'help' => 'La portada los une con un ampersand caligráfico.'],
        ];
    }

    public function featuredGroups(): array
    {
        return [
            'chambelanes' => ['Caballeros de honor', 'Caballero'],
            'damitas' => ['Damas de honor', 'Dama'],
            'padrinos' => ['Padrinos', 'Padrino'],
        ];
    }

    public function required(): array
    {
        return [
            'bienvenida' => [
                'nombre' => 'Falta el nombre de uno de los novios',
                'nombre_pareja' => 'Falta el nombre de su pareja',
                'imagen_hero' => 'Falta la foto de portada',
            ],
            'ubicacion' => ['nombre_lugar' => 'Falta el lugar de la boda'],
            'itinerario' => ['eventos' => 'No hay ningún momento cargado'],
        ];
    }

    public function sample(): array
    {
        return ['name' => 'Ana & Luis', 'subtitle' => 'Nos casamos'];
    }
}
