<?php

namespace App\EventProfiles;

use App\Support\InvitationTemplates;
use InvalidArgumentException;

/** Los perfiles registrados en config/event_profiles.php. */
class EventProfiles
{
    /** @var array<string, EventProfile>|null */
    protected ?array $profiles = null;

    /** @return array<string, EventProfile> código => perfil */
    public function all(): array
    {
        return $this->profiles ??= collect(config('event_profiles', []))
            ->map(fn (string $class) => app($class))
            ->keyBy(fn (EventProfile $profile) => $profile->code())
            ->all();
    }

    public function get(string $code): EventProfile
    {
        return $this->all()[$code] ?? throw new InvalidArgumentException("No hay un perfil de evento «{$code}».");
    }

    /** Perfil de una plantilla; las plantillas sin perfil conocido usan el de la plantilla por defecto. */
    public function forTemplate(?string $template): EventProfile
    {
        $event = InvitationTemplates::get($template)['event'];

        return $this->all()[$event] ?? $this->get(InvitationTemplates::get(InvitationTemplates::DEFAULT)['event']);
    }
}
