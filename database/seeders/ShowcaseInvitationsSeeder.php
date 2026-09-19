<?php

namespace Database\Seeders;

use App\Models\EventType;
use App\Models\Guest;
use App\Models\GuestContribution;
use App\Models\Invitation;
use App\Models\PollVote;
use App\Services\InvitationCacheService;
use App\Services\InvitationModuleService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

/**
 * Invitaciones de muestra (una por plantilla) que se enseñan a los clientes en la portada.
 * Los datos de cada una están en database/seeders/showcase/{slug}.php con sus fotos y videos en Cloudinary.
 *
 * Es idempotente: php artisan db:seed --class=ShowcaseInvitationsSeeder
 */
class ShowcaseInvitationsSeeder extends Seeder
{
    public const SLUGS = ['xv-isabella', 'boda-camila-andres', 'bautizo-emilia', 'cumple-daniela-30', 'tarjeta-ana-luis', 'tarjeta-sobre-mia'];

    public function run(): void
    {
        foreach (self::SLUGS as $slug) {
            $this->seedInvitation(self::data($slug));
        }
    }

    public static function data(string $slug): array
    {
        return require database_path("seeders/showcase/{$slug}.php");
    }

    protected function seedInvitation(array $data): void
    {
        $attributes = $data['invitation'];
        $eventType = EventType::updateOrCreate(
            ['slug' => $attributes['event_type']['slug']],
            Arr::only($attributes['event_type'], ['name', 'code', 'kind', 'season'])
        );

        $invitation = Invitation::updateOrCreate(['slug' => $attributes['slug']], [
            'user_id' => null,
            'event_type_id' => $eventType->id,
            'template' => $attributes['template'],
            'title' => $attributes['title'],
            'event_date' => $attributes['event_date'],
            'status' => $attributes['status'],
            'expires_at' => $attributes['expires_at'],
        ]);

        app(InvitationModuleService::class)->syncAllModules($invitation, $data['modules']);

        // La actividad de los invitados se rehace completa para dejarla igual que en la exportación
        PollVote::where('invitation_id', $invitation->id)->delete();
        GuestContribution::where('invitation_id', $invitation->id)->delete();
        Guest::where('invitation_id', $invitation->id)->delete();

        $guestIds = collect($data['guests'])
            ->mapWithKeys(fn (array $guest) => [
                $guest['qr_code_token'] => Guest::create(['invitation_id' => $invitation->id] + $guest)->id,
            ]);

        foreach ($data['contributions'] as $contribution) {
            (new GuestContribution)->forceFill([
                'invitation_id' => $invitation->id,
                'guest_id' => $guestIds[$contribution['guest']] ?? null,
                'type' => $contribution['type'],
                'content_text' => $contribution['content_text'],
                'file_path' => $contribution['file_path'],
                'created_at' => $contribution['created_at'],
            ])->save();
        }

        $pollIds = $invitation->polls()->pluck('id', 'poll_key');

        foreach ($data['poll_votes'] as $vote) {
            PollVote::create([
                'invitation_id' => $invitation->id,
                'invitation_poll_id' => $pollIds[$vote['poll']],
                'option_index' => $vote['option'],
                'guest_id' => $guestIds[$vote['guest']] ?? null,
                'voter_key' => $vote['voter_key'],
                'created_at' => $vote['created_at'],
            ]);
        }

        InvitationCacheService::invalidate($invitation);
    }
}
