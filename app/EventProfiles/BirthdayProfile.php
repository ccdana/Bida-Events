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
        return __('Cumpleaños');
    }

    public function modules(): array
    {
        return [
            'bienvenida', 'ubicacion', 'itinerario', 'rsvp', 'rsvp_whatsapp', 'galeria', 'dress_code', 'destacados',
            'video', 'musica', 'playlist', 'hashtag', 'encuestas', 'regalos', 'cuenta_regresiva',
            'agendar', 'fotomural', 'post_evento',
        ];
    }

    /** La edad es un dato: la plantilla la muestra en grande sobre el pastel. */
    public function heroFields(): array
    {
        return [
            'nombre' => ['label' => __('Nombre de quien cumple años'), 'placeholder' => __('Ej. Valeria')],
            'edad' => ['label' => __('Edad que cumple'), 'placeholder' => __('Ej. 30'), 'type' => 'number', 'help' => __('Se muestra en grande en la portada y en las velas del pastel.')],
        ];
    }

    public function featuredGroups(): array
    {
        return [
            'chambelanes' => [__('Amigos'), __('Amigo')],
            'damitas' => [__('Familia'), __('Familiar')],
            'padrinos' => [__('Anfitriones'), __('Anfitrión')],
        ];
    }

    public function required(): array
    {
        return [
            'bienvenida' => ['nombre' => __('Falta el nombre de quien cumple años'), 'edad' => __('Falta la edad que cumple')],
            'ubicacion' => ['nombre_lugar' => __('Falta el lugar de la fiesta')],
        ];
    }

    public function sample(): array
    {
        return ['name' => __('Valeria'), 'subtitle' => __('Mis 30 años')];
    }
}
