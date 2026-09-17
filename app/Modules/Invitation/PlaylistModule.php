<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Modules\Concerns\HasSectionTexts;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Module;

/** Playlist colaborativa: solo sus textos; las canciones son aportes de los invitados (guest_contributions). */
class PlaylistModule extends Module
{
    use HasSectionTexts, ReadsValues;

    public function code(): string
    {
        return 'playlist';
    }

    public function label(): string
    {
        return 'Playlist';
    }

    public function defaults(): object
    {
        return (object) [];
    }

    public function relations(): array
    {
        return ['sections.feature'];
    }

    public function load(Invitation $invitation): array
    {
        $section = $this->section($invitation);

        return $this->compact([
            'titulo' => $section?->title,
            'descripcion' => $section?->intro,
            'placeholder' => $section?->placeholder,
        ]);
    }

    public function save(Invitation $invitation, array $data): void
    {
        $this->saveSection($invitation, [
            'title' => $this->text($data['titulo'] ?? null, 255),
            'intro' => $this->text($data['descripcion'] ?? null),
            'placeholder' => $this->text($data['placeholder'] ?? null, 255),
        ]);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['titulo'] ?? null)
            || $this->filled($data['descripcion'] ?? null)
            || $this->filled($data['placeholder'] ?? null);
    }
}
