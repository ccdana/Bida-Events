<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationMedia;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Module;

/**
 * Canción de fondo (musica) o video (video): una fila de invitation_media por tipo.
 * Dos módulos, una sola clase: el tipo se elige al registrarla (AudioModule, VideoModule).
 */
abstract class MediaModule extends Module
{
    use ReadsValues;

    abstract protected function type(): string;

    public function kinds(): array
    {
        return [self::KIND_INVITATION, self::KIND_CARD];
    }

    public function defaults(): object
    {
        return (object) [];
    }

    public function relations(): array
    {
        return ['media'];
    }

    protected function row(Invitation $invitation): ?InvitationMedia
    {
        return $invitation->media->firstWhere('type', $this->type());
    }

    /** Una fila por tipo: se actualiza la existente o se crea; sin datos, se borra. */
    protected function store(Invitation $invitation, array $attributes): void
    {
        $query = $invitation->media()->where('type', $this->type());

        if (array_filter($attributes, fn ($value) => $value !== null && $value !== false) === []) {
            $query->delete();

            return;
        }

        $current = (clone $query)->first();

        if ($current) {
            $current->fill($attributes)->save();

            return;
        }

        $invitation->media()->create($attributes + ['type' => $this->type(), 'status' => 'active', 'sort_order' => 0]);
    }
}
