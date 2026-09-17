<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationHero;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Module;

/**
 * Portada (bienvenida). Los nombres se guardan separados: `nombre` y `nombre_pareja`. Para las
 * plantillas que todavía leen un solo texto, `nombre_quinceanera` se arma con los dos.
 */
class HeroModule extends Module
{
    use ReadsValues;

    public function code(): string
    {
        return 'bienvenida';
    }

    public function label(): string
    {
        return 'Portada';
    }

    public function kinds(): array
    {
        return [self::KIND_INVITATION, self::KIND_CARD];
    }

    public function defaults(): object
    {
        return (object) [];
    }

    public function visibleByDefault(): bool
    {
        return true;
    }

    public function relations(): array
    {
        return ['hero'];
    }

    public function load(Invitation $invitation): array
    {
        $hero = $invitation->hero;

        if (! $hero) {
            return [];
        }

        return $this->compact([
            'nombre_quinceanera' => self::fullName($hero->primary_name, $hero->secondary_name),
            'nombre' => $hero->primary_name,
            'nombre_pareja' => $hero->secondary_name,
            'edad' => $hero->age,
            'subtitulo' => $hero->subtitle,
            'mensaje' => $hero->message,
            'fecha_texto' => $hero->date_text,
            'imagen_hero' => $hero->image_url,
            'imagen_hero_alt' => $hero->image_alt,
            'mensaje_post_evento' => $hero->post_event_message,
        ]);
    }

    public function save(Invitation $invitation, array $data): void
    {
        [$primary, $secondary] = $this->names($data);

        $attributes = [
            'primary_name' => $primary,
            'secondary_name' => $secondary,
            'age' => is_numeric($data['edad'] ?? null) ? max(0, min(150, (int) $data['edad'])) : null,
            'subtitle' => $this->text($data['subtitulo'] ?? null, 255),
            'message' => $this->text($data['mensaje'] ?? null),
            'date_text' => $this->text($data['fecha_texto'] ?? null, 255),
            'image_url' => $this->text($data['imagen_hero'] ?? null),
            'image_alt' => $this->text($data['imagen_hero_alt'] ?? null, 255),
            'post_event_message' => $this->text($data['mensaje_post_evento'] ?? null),
        ];

        if (array_filter($attributes, fn ($value) => $value !== null) === []) {
            InvitationHero::where('invitation_id', $invitation->id)->delete();

            return;
        }

        InvitationHero::updateOrCreate(['invitation_id' => $invitation->id], $attributes);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['nombre_quinceanera'] ?? null)
            || $this->filled($data['nombre'] ?? null)
            || $this->filled($data['subtitulo'] ?? null)
            || $this->filled($data['mensaje'] ?? null)
            || $this->filled($data['imagen_hero'] ?? null);
    }

    /**
     * Los nombres separados mandan. El texto completo (`nombre_quinceanera`) solo se usa si llega
     * solo o si alguien lo cambió y ya no coincide con los separados; en ese caso se guarda entero
     * como nombre principal, porque partirlo a ciegas rompería nombres como «María y José Luis».
     *
     * @return array{0: ?string, 1: ?string}
     */
    protected function names(array $data): array
    {
        $primary = $this->text($data['nombre'] ?? null, 255);
        $secondary = $this->text($data['nombre_pareja'] ?? null, 255);
        $full = $this->text($data['nombre_quinceanera'] ?? null, 255);

        $composed = self::fullName($primary, $secondary);

        if ($full !== null && $full !== $composed) {
            return [$full, null];
        }

        return [$primary ?? $full, $secondary];
    }

    /** Arma el texto completo con los dos nombres, como lo leen las plantillas. */
    public static function fullName(?string $primary, ?string $secondary): ?string
    {
        return $secondary ? trim(($primary ?? '').' & '.$secondary, ' &') : $primary;
    }
}
