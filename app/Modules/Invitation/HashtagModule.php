<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationHashtag;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Module;

class HashtagModule extends Module
{
    use ReadsValues;

    public function code(): string
    {
        return 'hashtag';
    }

    public function label(): string
    {
        return 'Hashtag';
    }

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
        return ['hashtag'];
    }

    public function load(Invitation $invitation): array
    {
        $hashtag = $invitation->hashtag;

        return $hashtag ? $this->compact([
            'hashtag' => $hashtag->tag,
            'plataforma' => $hashtag->platform,
            'texto_boton' => $hashtag->button_text,
        ]) : [];
    }

    public function save(Invitation $invitation, array $data): void
    {
        $attributes = [
            'tag' => $this->text($data['hashtag'] ?? null, 100),
            'platform' => $this->text($data['plataforma'] ?? null, 30),
            'button_text' => $this->text($data['texto_boton'] ?? null, 255),
        ];

        if (array_filter($attributes) === []) {
            InvitationHashtag::where('invitation_id', $invitation->id)->delete();

            return;
        }

        InvitationHashtag::updateOrCreate(['invitation_id' => $invitation->id], $attributes);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['hashtag'] ?? null);
    }
}
