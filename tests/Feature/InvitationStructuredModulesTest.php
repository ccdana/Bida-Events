<?php

namespace Tests\Feature;

use App\Models\EventType;
use App\Models\Invitation;
use App\Models\InvitationData;
use App\Models\InvitationPoll;
use App\Models\PollVote;
use App\Services\InvitationModuleService;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationStructuredModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_writes_tables_and_resolves_the_same_modules(): void
    {
        $invitation = $this->makeInvitation();
        $modules = XvSofiaModuleData::all();

        app(InvitationModuleService::class)->syncAllModules($invitation, $modules);

        $this->assertDatabaseCount('invitation_settings', 1);
        $this->assertDatabaseCount('invitation_itinerary_items', 6);
        $this->assertDatabaseCount('invitation_gallery_images', 5);
        $this->assertDatabaseCount('invitation_polls', 4);
        $this->assertDatabaseCount('invitation_poll_options', 15);

        $resolved = app(InvitationModuleService::class)->resolveModules(Invitation::find($invitation->id));

        $this->assertSame($modules['itinerario']['eventos'], $resolved['itinerario']['eventos']);
        $this->assertSame($modules['itinerario']['titulo'], $resolved['itinerario']['titulo']);
        $this->assertSame($modules['galeria']['fotos'], $resolved['galeria']['fotos']);
        $this->assertSame($modules['encuestas']['preguntas'], $resolved['encuestas']['preguntas']);
        $this->assertSame($modules['config']['colores'], $resolved['config']['colores']);
        $this->assertSame($modules['config']['template'], $resolved['config']['template']);
    }

    public function test_resaving_keeps_poll_ids_and_removes_deleted_polls(): void
    {
        $invitation = $this->makeInvitation();
        $service = app(InvitationModuleService::class);
        $modules = XvSofiaModuleData::all();

        $service->syncAllModules($invitation, $modules);
        $pollId = InvitationPoll::where('poll_key', 'color-vestido')->value('id');

        $modules['encuestas']['preguntas'] = array_slice($modules['encuestas']['preguntas'], 0, 1);
        $modules['itinerario']['eventos'] = array_reverse($modules['itinerario']['eventos']);
        $service->syncAllModules($invitation->fresh(), $modules);

        $this->assertSame($pollId, InvitationPoll::where('poll_key', 'color-vestido')->value('id'));
        $this->assertDatabaseCount('invitation_polls', 1);
        $this->assertDatabaseCount('invitation_poll_options', 4);

        $resolved = $service->resolveModules(Invitation::find($invitation->id));
        $this->assertSame('Sorpresa Final', $resolved['itinerario']['eventos'][0]['titulo']);
    }

    public function test_command_migrates_legacy_json_and_links_existing_votes(): void
    {
        $invitation = $this->makeInvitation();

        foreach (XvSofiaModuleData::all() as $code => $data) {
            InvitationData::create(['invitation_id' => $invitation->id, 'feature_code' => $code, 'json_data' => $data]);
        }

        PollVote::create([
            'invitation_id' => $invitation->id,
            'poll_id' => 'nivel-fiesta',
            'option_index' => 4,
            'voter_key' => 'voter-1',
            'created_at' => now(),
        ]);

        // Sin migrar todavía: se lee desde JSON
        $resolved = app(InvitationModuleService::class)->resolveModules(Invitation::find($invitation->id));
        $this->assertCount(6, $resolved['itinerario']['eventos']);

        $this->artisan('invitations:migrate-json', ['--dry-run' => true])->assertSuccessful();
        $this->assertDatabaseCount('invitation_settings', 0);
        $this->assertDatabaseCount('invitation_polls', 0);

        $this->artisan('invitations:migrate-json')->assertSuccessful();
        $this->assertDatabaseHas('invitation_settings', ['invitation_id' => $invitation->id]);
        $this->assertDatabaseCount('invitation_itinerary_items', 6);
        $this->assertSame(
            InvitationPoll::where('poll_key', 'nivel-fiesta')->value('id'),
            PollVote::first()->invitation_poll_id
        );

        // Una segunda ejecución omite lo ya migrado
        $this->artisan('invitations:migrate-json')->assertSuccessful();
        $this->assertDatabaseCount('invitation_itinerary_items', 6);
    }

    public function test_poll_results_are_aggregated_per_poll(): void
    {
        $invitation = $this->makeInvitation();

        $votes = [['a', 0], ['a', 0], ['a', 0], ['a', 1], ['b', 2]];
        foreach ($votes as $i => [$pollId, $option]) {
            PollVote::create([
                'invitation_id' => $invitation->id,
                'poll_id' => $pollId,
                'option_index' => $option,
                'voter_key' => "voter-{$i}",
                'created_at' => now(),
            ]);
        }

        $results = app(InvitationModuleService::class)->pollResultsFor($invitation, ['a' => 2, 'b' => 3, 'c' => 2]);

        $this->assertEquals(['a' => [75, 25], 'b' => [0, 0, 100], 'c' => [0, 0]], $results);
        $this->assertEquals([75, 25], app(InvitationModuleService::class)->pollResults($invitation, 'a', 2));
    }

    protected function makeInvitation(): Invitation
    {
        $eventType = EventType::create(['name' => 'XV Años', 'slug' => 'xv-anos']);

        return Invitation::create([
            'event_type_id' => $eventType->id,
            'slug' => 'xv-sofia',
            'template' => 'invitations.templates.xv-premium',
            'title' => 'XV Años de Sofía Valentina',
            'event_date' => now()->addMonths(3)->setTime(18, 0),
            'status' => 'active',
            'expires_at' => now()->addMonths(9),
        ]);
    }
}
