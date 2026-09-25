<?php

namespace App\EventProfiles;

/**
 * «Lienzo»: la invitación en blanco para cualquier evento (una inauguración, un aniversario, una
 * cena de empresa…). Fondo blanco, letra negra y todos los módulos disponibles: quien la arma
 * decide los colores, las tipografías y cada texto desde el editor.
 */
class CanvasProfile extends EventProfile
{
    public function code(): string
    {
        return 'lienzo';
    }

    public function label(): string
    {
        return 'Evento libre';
    }

    public function modules(): array
    {
        return [
            'bienvenida', 'ubicacion', 'itinerario', 'rsvp', 'rsvp_whatsapp', 'galeria', 'dress_code', 'destacados',
            'video', 'musica', 'playlist', 'hashtag', 'encuestas', 'regalos', 'cuenta_regresiva',
            'agendar', 'fotomural', 'post_evento',
        ];
    }

    public function heroFields(): array
    {
        return [
            'nombre' => ['label' => 'Nombre o título del evento', 'placeholder' => 'Ej. Aniversario 25 de Casa Molina', 'help' => 'Lo que va en grande en la portada: un nombre, una marca o el título de la celebración.'],
        ];
    }

    public function featuredGroups(): array
    {
        return [
            'padrinos' => ['Anfitriones', 'Anfitrión'],
            'chambelanes' => ['Invitados especiales', 'Invitado especial'],
            'damitas' => ['Equipo', 'Integrante'],
        ];
    }

    /** Sin foto obligatoria: la portada en blanco también se ve bien solo con tipografía. */
    public function required(): array
    {
        return [
            'bienvenida' => ['nombre' => 'Falta el nombre o título del evento'],
            'ubicacion' => ['nombre_lugar' => 'Falta el lugar'],
        ];
    }

    public function sample(): array
    {
        return ['name' => 'Casa Molina', 'subtitle' => 'Celebramos 25 años'];
    }
}
