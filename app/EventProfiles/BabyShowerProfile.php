<?php

namespace App\EventProfiles;

/**
 * Baby shower: la celebración de la llegada de un bebé. El nombre de la portada es el del bebé (o
 * «Bebé en camino» si todavía no lo tiene); quienes organizan van en destacados y la lista de
 * regalos pesa más que en otros eventos.
 */
class BabyShowerProfile extends EventProfile
{
    public function code(): string
    {
        return 'babyshower';
    }

    public function label(): string
    {
        return 'Baby shower';
    }

    public function modules(): array
    {
        return [
            'bienvenida', 'ubicacion', 'itinerario', 'regalos', 'destacados', 'galeria', 'dress_code', 'video',
            'musica', 'playlist', 'hashtag', 'encuestas', 'rsvp', 'rsvp_whatsapp', 'cuenta_regresiva',
            'agendar', 'fotomural', 'post_evento',
        ];
    }

    public function enabledByDefault(): array
    {
        return ['bienvenida', 'ubicacion', 'itinerario', 'regalos', 'rsvp', 'cuenta_regresiva'];
    }

    public function heroFields(): array
    {
        return [
            'nombre' => [
                'label' => 'Nombre del bebé',
                'placeholder' => 'Ej. Valentina',
                'help' => 'Si todavía no tiene nombre, escribe «Bebé en camino» o el nombre de la mamá.',
            ],
        ];
    }

    /** Quienes organizan van primero; el cortejo es la familia. */
    public function featuredGroups(): array
    {
        return [
            'padrinos' => ['Anfitrionas', 'Anfitriona'],
            'chambelanes' => ['Abuelos', 'Abuelo'],
            'damitas' => ['Tías y amigas', 'Tía'],
        ];
    }

    /** La foto no es obligatoria: muchas familias todavía no tienen una del bebé. */
    public function required(): array
    {
        return [
            'bienvenida' => ['nombre' => 'Falta el nombre del bebé'],
            'ubicacion' => ['nombre_lugar' => 'Falta el lugar de la celebración'],
        ];
    }

    public function sample(): array
    {
        return ['name' => 'Valentina', 'subtitle' => 'Baby shower'];
    }
}
