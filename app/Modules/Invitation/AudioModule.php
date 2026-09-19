<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationMedia;

class AudioModule extends MediaModule
{
    public function code(): string
    {
        return 'musica';
    }

    public function label(): string
    {
        return 'Música';
    }

    protected function type(): string
    {
        return InvitationMedia::TYPE_AUDIO;
    }

    public function load(Invitation $invitation): array
    {
        $media = $this->row($invitation);

        return $media ? $this->compact([
            'titulo' => $media->title,
            'artista' => $media->artist,
            'audio_url' => $media->url,
            'autoplay' => $media->autoplay,
        ]) : [];
    }

    public function save(Invitation $invitation, array $data): void
    {
        $title = $this->text($data['titulo'] ?? null, 255);
        $url = $this->text($data['audio_url'] ?? null);

        // Solo con canción o título hay algo que guardar (el interruptor solo no alcanza)
        if ($title === null && $url === null) {
            $this->store($invitation, []);

            return;
        }

        $this->store($invitation, [
            'title' => $title,
            'artist' => $this->text($data['artista'] ?? null, 255),
            'url' => $url,
            'autoplay' => (bool) ($data['autoplay'] ?? false),
        ]);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['audio_url'] ?? null) || $this->filled($data['titulo'] ?? null);
    }
}
