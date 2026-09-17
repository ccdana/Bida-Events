<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationMedia;

class VideoModule extends MediaModule
{
    public function code(): string
    {
        return 'video';
    }

    public function label(): string
    {
        return 'Video';
    }

    protected function type(): string
    {
        return InvitationMedia::TYPE_VIDEO;
    }

    public function load(Invitation $invitation): array
    {
        $media = $this->row($invitation);

        return $media ? $this->compact([
            'titulo' => $media->title,
            'video_url' => $media->url,
            'poster' => $media->poster_url,
        ]) : [];
    }

    public function save(Invitation $invitation, array $data): void
    {
        $title = $this->text($data['titulo'] ?? null, 255);
        $url = $this->text($data['video_url'] ?? null);
        $poster = $this->text($data['poster'] ?? null);

        $this->store($invitation, $title === null && $url === null && $poster === null ? [] : [
            'title' => $title,
            'url' => $url,
            'poster_url' => $poster,
            'autoplay' => false,
        ]);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['video_url'] ?? null) || $this->filled($data['poster'] ?? null);
    }
}
