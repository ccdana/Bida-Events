<?php

namespace Tests\Feature;

use App\Models\GuestContribution;
use App\Models\Invitation;
use App\Models\InvitationItineraryItem;
use App\Services\InvitationModuleService;
use App\Services\InvitationStructuredDataService;
use App\Services\MediaUploadService;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

class ModuleSyncAndRetentionTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_saving_the_editor_is_all_or_nothing(): void
    {
        $invitation = $this->createInvitation();
        $service = app(InvitationModuleService::class);
        $service->syncAllModules($invitation, XvSofiaModuleData::all());

        $before = $invitation->modulesData()->where('feature_code', 'bienvenida')->value('json_data');
        $itineraryBefore = $invitation->itineraryItems()->count();

        // Si algo falla al guardar las tablas, tampoco debe quedar el JSON nuevo
        $this->instance(InvitationStructuredDataService::class, new class extends InvitationStructuredDataService
        {
            public function sync(Invitation $invitation, array $modules): array
            {
                throw new RuntimeException('falla al guardar');
            }
        });

        $modules = XvSofiaModuleData::all();
        $modules['bienvenida']['nombre_quinceanera'] = 'Nombre cambiado';

        try {
            app(InvitationModuleService::class)->syncAllModules($invitation->fresh(), $modules);
            $this->fail('Se esperaba una excepción al guardar.');
        } catch (RuntimeException) {
            // esperado
        }

        $this->assertSame($before, $invitation->modulesData()->where('feature_code', 'bienvenida')->value('json_data'));
        $this->assertSame($itineraryBefore, $invitation->itineraryItems()->count());
    }

    public function test_saving_twice_keeps_the_same_rows_instead_of_recreating_them(): void
    {
        $invitation = $this->createInvitation();
        $service = app(InvitationModuleService::class);
        $modules = XvSofiaModuleData::all();

        $service->syncAllModules($invitation, $modules);
        $firstIds = $invitation->itineraryItems()->orderBy('sort_order')->pluck('id')->all();
        $galleryIds = $invitation->galleryImages()->orderBy('sort_order')->pluck('id')->all();

        // Se cambia un texto y se vuelve a guardar: las filas se actualizan, no se borran y recrean
        $modules['itinerario']['eventos'][0]['titulo'] = 'Recepción de invitados';
        $service->syncAllModules($invitation->fresh(), $modules);

        $this->assertSame($firstIds, $invitation->itineraryItems()->orderBy('sort_order')->pluck('id')->all());
        $this->assertSame($galleryIds, $invitation->galleryImages()->orderBy('sort_order')->pluck('id')->all());
        $this->assertSame('Recepción de invitados', InvitationItineraryItem::find($firstIds[0])->title);
    }

    public function test_removing_items_deletes_only_the_extra_rows(): void
    {
        $invitation = $this->createInvitation();
        $service = app(InvitationModuleService::class);
        $modules = XvSofiaModuleData::all();

        $service->syncAllModules($invitation, $modules);
        $total = $invitation->itineraryItems()->count();
        $keptIds = $invitation->itineraryItems()->orderBy('sort_order')->pluck('id')->take($total - 1)->all();

        array_pop($modules['itinerario']['eventos']);
        $service->syncAllModules($invitation->fresh(), $modules);

        $this->assertSame($keptIds, $invitation->itineraryItems()->orderBy('sort_order')->pluck('id')->all());
    }

    public function test_old_photos_are_purged_with_their_remote_file(): void
    {
        $past = $this->createInvitation(['event_date' => now()->subYear(), 'expires_at' => now()->addMonth()]);
        $recent = $this->createInvitation(['event_date' => now()->subDays(5)]);

        foreach ([$past, $recent] as $invitation) {
            GuestContribution::create([
                'invitation_id' => $invitation->id,
                'type' => 'live_photo',
                'file_path' => 'https://res.cloudinary.com/demo/image/upload/v1/bida-events/foto.jpg',
                'created_at' => now(),
            ]);
            GuestContribution::create([
                'invitation_id' => $invitation->id,
                'type' => 'song_request',
                'content_text' => 'Una canción',
                'created_at' => now(),
            ]);
        }

        $deleted = [];
        $this->instance(MediaUploadService::class, new class($deleted) extends MediaUploadService
        {
            public function __construct(public array &$borrados) {}

            public function delete(?string $url): bool
            {
                $this->borrados[] = $url;

                return true;
            }
        });

        $this->artisan('invitations:purge-contributions', ['--days' => 180])->assertSuccessful();

        // Solo la foto del evento viejo: la canción y lo reciente siguen ahí
        $this->assertSame(1, count($deleted));
        $this->assertDatabaseMissing('guest_contributions', ['invitation_id' => $past->id, 'type' => 'live_photo']);
        $this->assertDatabaseHas('guest_contributions', ['invitation_id' => $past->id, 'type' => 'song_request']);
        $this->assertDatabaseHas('guest_contributions', ['invitation_id' => $recent->id, 'type' => 'live_photo']);
    }

    public function test_deleting_a_contribution_removes_its_cloudinary_file(): void
    {
        $invitation = $this->createInvitation();
        $photo = GuestContribution::create([
            'invitation_id' => $invitation->id,
            'type' => 'live_photo',
            'file_path' => 'https://res.cloudinary.com/demo/image/upload/v1/bida-events/foto.jpg',
            'created_at' => now(),
        ]);

        $deleted = [];
        $this->instance(MediaUploadService::class, new class($deleted) extends MediaUploadService
        {
            public function __construct(public array &$borrados) {}

            public function delete(?string $url): bool
            {
                $this->borrados[] = $url;

                return true;
            }
        });

        $photo->delete();

        $this->assertSame(['https://res.cloudinary.com/demo/image/upload/v1/bida-events/foto.jpg'], $deleted);
    }
}
