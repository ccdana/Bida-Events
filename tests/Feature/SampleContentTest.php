<?php

namespace Tests\Feature;

use App\EventProfiles\EventProfiles;
use App\Models\EventType;
use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationModuleService;
use App\Support\InvitationDefaults;
use App\Support\InvitationModuleRules;
use App\Support\InvitationTemplates;
use App\Support\SampleContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Contenido de ejemplo (App\Support\SampleContent): cada parte nace con textos y fotos genéricos
 * para tocarlos y cambiarlos en el editor. Tienen que pasar la validación del guardado, verse en
 * cada plantilla y poder tocarse en la vista previa.
 */
class SampleContentTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public static function templates(): array
    {
        return collect(InvitationTemplates::all())
            ->mapWithKeys(fn (array $entry, string $template) => [$entry['label'] => [$template]])
            ->all();
    }

    public function test_every_module_has_sample_content(): void
    {
        $this->assertSame([], SampleContent::missing(), 'Módulos sin contenido de ejemplo en App\Support\SampleContent');
    }

    public function test_the_sample_photos_exist(): void
    {
        foreach ([SampleContent::PHOTO_VERTICAL, SampleContent::PHOTO_HORIZONTAL, ...SampleContent::PHOTOS_SQUARE] as $photo) {
            $this->assertFileExists(public_path($photo));
        }
    }

    public function test_the_samples_of_every_event_pass_the_validation_of_the_editor(): void
    {
        foreach (app(EventProfiles::class)->all() as $profile) {
            $modules = SampleContent::forProfile($profile);
            $validator = Validator::make(['modulos_data' => $modules], InvitationModuleRules::rules('modulos_data'));

            $this->assertFalse($validator->fails(), $profile->code().': '.implode(' ', $validator->errors()->all()));
        }
    }

    #[DataProvider('templates')]
    public function test_every_part_shows_its_sample_and_can_be_tapped(string $template): void
    {
        $profile = app(EventProfiles::class)->forTemplate($template);
        $modules = array_replace(InvitationDefaults::emptyModules(), SampleContent::forProfile($profile));
        $modules['bienvenida'] = [...(array) $modules['bienvenida'], 'nombre' => 'Nombre de prueba', 'nombre_quinceanera' => 'Nombre de prueba'];
        $modules['config']['template'] = $template;
        // La confirmación por WhatsApp necesita un número que el ejemplo no trae: se prueba la del pase
        $modules['config']['modulos'] = ['rsvp_whatsapp' => false] + array_fill_keys($profile->modules(), true);

        $invitation = $this->createInvitation(['slug' => 'ejemplo-'.Str::lower(Str::random(6)), 'template' => $template]);
        app(InvitationModuleService::class)->syncAllModules($invitation, $modules);
        $guest = $invitation->guests()->create(['name' => 'Familia Quispe', 'passes_allocated' => 2]);

        $html = $this->withoutVite()
            ->get(route('invitation.guest', ['slug' => $invitation->slug, 'token' => $guest->qr_code_token]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(SampleContent::PHOTO_VERTICAL, $html, 'La portada no muestra la foto de ejemplo');

        // Sin canción ni video propios no hay qué reproducir; el editor los pide al encenderlos
        $needsOwnFile = ['musica', 'rsvp_whatsapp', 'post_evento'];
        foreach (array_diff($profile->modules(), $needsOwnFile) as $code) {
            $id = $code === 'bienvenida' ? 'inicio' : str_replace('_', '-', $code);
            $this->assertTrue(
                str_contains($html, 'data-ed="'.$code.'"') || str_contains($html, 'id="'.$id.'"'),
                "En {$template} la parte «{$code}» no aparece con su ejemplo",
            );
        }
    }

    public function test_a_new_invitation_is_created_with_the_sample_content(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $eventType = EventType::firstOrCreate(['slug' => 'xv-anos'], ['name' => 'XV Años', 'code' => 'xv', 'kind' => 'invitation']);
        $profile = app(EventProfiles::class)->forTemplate('invitations.templates.xv-premium');

        $modules = array_replace(InvitationDefaults::emptyModules(), SampleContent::forProfile($profile));
        $modules['bienvenida'] = [...$modules['bienvenida'], 'nombre' => 'Sofía', 'nombre_quinceanera' => 'Sofía'];
        $modules['config']['modulos']['itinerario'] = true;

        $this->actingAs($admin)->post(route('admin.invitations.store'), [
            'title' => 'XV años de Sofía',
            'slug' => 'xv-sofia-ejemplo',
            'template' => 'invitations.templates.xv-premium',
            'event_type_id' => $eventType->id,
            'user_id' => '',
            'event_date' => now()->addMonths(2)->format('Y-m-d H:i'),
            'status' => 'inactive',
            'expires_at' => now()->addYear()->toDateString(),
            'modulos' => array_map(fn ($module) => json_encode($module), $modules),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $invitation = Invitation::where('slug', 'xv-sofia-ejemplo')->firstOrFail();
        $stored = app(InvitationModuleService::class)->storedModules($invitation);

        $this->assertSame(SampleContent::PHOTO_VERTICAL, $stored['bienvenida']['imagen_hero']);
        $this->assertCount(4, $stored['itinerario']['eventos']);
        $this->assertSame('Recepción', $stored['itinerario']['eventos'][0]['titulo']);
    }
}
