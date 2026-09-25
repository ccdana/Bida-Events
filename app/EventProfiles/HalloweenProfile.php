<?php

namespace App\EventProfiles;

/**
 * Fiesta de Halloween: una invitación completa (con confirmación, lugar y playlist) que se vende
 * por temporada, hasta el 31 de octubre. El dress code aquí es el disfraz.
 */
class HalloweenProfile extends EventProfile
{
    public function code(): string
    {
        return 'halloween';
    }

    public function label(): string
    {
        return 'Halloween';
    }

    public function season(): string
    {
        return 'halloween';
    }

    public function modules(): array
    {
        return [
            'bienvenida', 'ubicacion', 'itinerario', 'rsvp', 'rsvp_whatsapp', 'dress_code', 'playlist', 'encuestas',
            'galeria', 'video', 'musica', 'hashtag', 'destacados', 'regalos', 'cuenta_regresiva',
            'agendar', 'fotomural', 'post_evento',
        ];
    }

    public function enabledByDefault(): array
    {
        return ['bienvenida', 'ubicacion', 'rsvp', 'dress_code', 'cuenta_regresiva'];
    }

    /** El «nombre» es el de la fiesta: puede ser de una persona, de un grupo o de un local. */
    public function heroFields(): array
    {
        return [
            'nombre' => ['label' => 'Nombre de la fiesta o de quien invita', 'placeholder' => 'Ej. La noche de Diego', 'help' => 'Va en grande en la portada, bajo la luna.'],
        ];
    }

    public function featuredGroups(): array
    {
        return [
            'padrinos' => ['Anfitriones', 'Anfitrión'],
            'chambelanes' => ['DJ y show', 'Artista'],
            'damitas' => ['Jurado de disfraces', 'Jurado'],
        ];
    }

    /** La foto no es obligatoria: la portada ya trae la luna y las calabazas. */
    public function required(): array
    {
        return [
            'bienvenida' => ['nombre' => 'Falta el nombre de la fiesta'],
            'ubicacion' => ['nombre_lugar' => 'Falta el lugar de la fiesta'],
            'dress_code' => ['sugerencias' => 'Cuenta qué disfraces esperan'],
        ];
    }

    public function sample(): array
    {
        return ['name' => 'La noche de Diego', 'subtitle' => 'Fiesta de disfraces'];
    }
}
