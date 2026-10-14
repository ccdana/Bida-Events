<?php

namespace Tests\Feature;

use App\EventProfiles\EventProfiles;
use App\Models\User;
use App\Services\InvitationModuleService;
use App\Support\InvitationTemplates;
use Database\Seeders\CumpleFiestaDemoSeeder;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Editor «tocar y cambiar»: en la vista previa cada parte de la invitación se toca para abrir su panel.
 * El editor la encuentra por data-ed="código" o por el id de su sección (el código con guiones; la
 * portada es #inicio): admin/invitations/editor/script, previewTargets(). Si una plantilla pierde ese
 * id o una parte nueva no lo trae, la parte deja de poder tocarse y esta prueba lo dice.
 */
class TapToEditTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public static function templates(): array
    {
        return collect(InvitationTemplates::all())
            ->mapWithKeys(fn (array $entry, string $template) => [$entry['label'] => [$template]])
            ->all();
    }

    #[DataProvider('templates')]
    public function test_every_part_of_the_template_can_be_tapped_in_the_editor(string $template): void
    {
        $profile = app(EventProfiles::class)->forTemplate($template);
        $html = $this->renderWithEverythingOn($template);

        // Se ven en otro momento: «después de la fiesta» al pasar la fecha y una sola confirmación a la vez
        $elsewhere = ['post_evento', 'rsvp', 'rsvp_whatsapp'];

        foreach (array_diff($profile->modules(), $elsewhere) as $code) {
            $this->assertTrue($this->canBeTapped($html, $code), "En {$template} no se puede tocar «{$code}»: le falta data-ed=\"{$code}\" o el id de su sección");
        }

        if (in_array('rsvp', $profile->modules(), true)) {
            $this->assertTrue($this->canBeTapped($html, 'rsvp'), "En {$template} no se puede tocar la confirmación con pase");
        }
    }

    public function test_the_whatsapp_confirmation_opens_its_own_panel_and_not_the_pass_one(): void
    {
        $modules = XvSofiaModuleData::all();
        $modules['config']['modulos']['rsvp'] = false;
        $modules['config']['modulos']['rsvp_whatsapp'] = true;
        $modules['rsvp_whatsapp']['whatsapp'] = '+591 70000000';

        $html = $this->render('invitations.templates.xv-premium', $modules);

        // Comparte el id «rsvp» con la del pase: data-ed decide qué panel se abre
        $this->assertStringContainsString('data-ed="rsvp_whatsapp"', $html);
        $this->assertFalse($this->canBeTapped($html, 'rsvp'), 'El toque abriría la confirmación con pase');
    }

    public function test_after_the_party_the_thanks_can_be_tapped(): void
    {
        $modules = XvSofiaModuleData::all();
        $modules['config']['modulos']['post_evento'] = true;

        $html = $this->render('invitations.templates.xv-premium', $modules, ['event_date' => now()->subDays(2)]);

        $this->assertTrue($this->canBeTapped($html, 'post_evento'));
    }

    public function test_the_editor_works_the_same_on_computer_and_phone(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $invitation = $this->createInvitation();
        app(InvitationModuleService::class)->syncAllModules($invitation, XvSofiaModuleData::all());

        $page = $this->actingAs($admin)->withoutVite()->get(route('admin.invitations.edit', $invitation));

        $page->assertOk()
            // Las partes se encienden y apagan en la barra lateral, cada una con su «?»
            ->assertSee('Partes de tu invitación')
            ->assertSee('togglePart(tab)', false)
            ->assertSee('partHelp(tab)', false)
            // La invitación se toca: un texto o una foto abre una burbuja con solo ese campo, su «?» y «Listo»
            ->assertSee('para cambiarlo', false)
            ->assertSee('¿Para qué sirve?', false)
            ->assertSee('Cambiar otra cosa de')
            ->assertSee('¿Qué quieres cambiar?')
            ->assertSee('Ver todas las opciones de')
            ->assertSee('Listo')
            // Arriba: los datos, los colores, ver como invitado y publicar
            ->assertSee('Datos del evento')
            ->assertSee('Colores y letras')
            ->assertSee('Ver como invitado')
            ->assertSee('Publicar')
            ->assertDontSee('Banner principal')
            ->assertDontSee('ed-rail', false);
    }

    public function test_a_new_invitation_starts_with_the_wizard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->withoutVite()->get(route('admin.invitations.create'))
            ->assertOk()
            // El evento, el diseño y los datos, antes de ver la invitación
            ->assertSee('¿Qué vas a celebrar?')
            ->assertSee('Elige el diseño')
            ->assertSee('Los datos de tu evento')
            ->assertSee('finishWizard()', false)
            // El administrador elige además el cliente y el paquete
            ->assertSee('Sin cliente por ahora');
    }

    public function test_the_wizard_never_fails_because_the_address_is_taken(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $eventType = \App\Models\EventType::firstOrCreate(['slug' => 'xv-anos'], ['name' => 'XV Años', 'code' => 'xv', 'kind' => 'invitation']);
        $this->createInvitation(['slug' => 'xv-anos-de-sofia']);

        $this->actingAs($admin)->post(route('admin.invitations.store'), [
            'title' => 'XV años de Sofía',
            'slug' => 'xv-anos-de-sofia',
            'slug_auto' => '1',
            'template' => 'invitations.templates.xv-premium',
            'event_type_id' => $eventType->id,
            'user_id' => '',
            'event_date' => now()->addMonths(2)->format('Y-m-d H:i'),
            'status' => 'inactive',
            'expires_at' => now()->addYear()->toDateString(),
            'modulos' => ['bienvenida' => json_encode(['nombre' => 'Sofía'])],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('invitations', ['slug' => 'xv-anos-de-sofia-2', 'title' => 'XV años de Sofía']);
    }

    /** La plantilla con todas sus partes encendidas y con contenido (fotos, video, música y regalos de muestra). */
    private function renderWithEverythingOn(string $template): string
    {
        $content = (new \ReflectionMethod(TemplateRenderMatrixTest::class, 'completeContent'))->invoke(null, $template);
        $sample = XvSofiaModuleData::all();

        // Algunas muestras no traen fotos, video, música o regalos: sin contenido esas partes no se dibujan
        $hasContent = [
            'galeria' => fn (array $data) => ! empty($data['fotos']),
            'video' => fn (array $data) => ! empty($data['video_url']),
            'musica' => fn (array $data) => ! empty($data['audio_url']),
            'regalos' => fn (array $data) => ! empty($data['opciones']) || ! empty($data['tienda_url']),
        ];

        foreach ($hasContent as $module => $filled) {
            if (! $filled((array) ($content[$module] ?? []))) {
                $content[$module] = $sample[$module];
            }
        }

        $content['config']['modulos'] = array_fill_keys(array_keys($content['config']['modulos']), true);

        return $this->render($template, $content);
    }

    private function render(string $template, array $modules, array $attributes = []): string
    {
        $modules['config']['template'] = $template;
        $invitation = $this->createInvitation(array_merge([
            'slug' => 'toque-'.Str::lower(Str::random(6)),
            'template' => $template,
        ], $attributes));
        app(InvitationModuleService::class)->syncAllModules($invitation, $modules);
        $guest = $invitation->guests()->create(['name' => 'Familia Quispe', 'passes_allocated' => 2]);

        return $this->withoutVite()
            ->get(route('invitation.guest', ['slug' => $invitation->slug, 'token' => $guest->qr_code_token]))
            ->assertOk()
            ->getContent();
    }

    /** Lo mismo que busca el editor: data-ed="código" o la sección con su id que no esté marcada para otra parte. */
    private function canBeTapped(string $html, string $code): bool
    {
        if (str_contains($html, 'data-ed="'.$code.'"')) {
            return true;
        }

        $id = $code === 'bienvenida' ? 'inicio' : str_replace('_', '-', $code);

        preg_match_all('/<[a-z][^>]*\bid="'.preg_quote($id, '/').'"[^>]*>/i', $html, $tags);

        return collect($tags[0])->contains(fn (string $tag) => ! str_contains($tag, 'data-ed='));
    }
}
