<?php

namespace Tests\Feature;

use App\Models\InvitationText;
use App\Models\User;
use App\Services\InvitationModuleService;
use App\Support\EditableTexts;
use App\Support\InvitationTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Todos los textos de las secciones (títulos, frases, botones, mensajes de «todavía no hay datos»)
 * se pueden cambiar por invitación; lo que queda vacío sigue siendo el texto de la plantilla.
 */
class EditableTextsTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_every_text_in_the_catalog_is_used_by_the_invitations(): void
    {
        $sources = file_get_contents(app_path('Support/InvitationPage.php'));

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views/invitations'))) as $file) {
            if ($file->isFile()) {
                $sources .= file_get_contents($file->getPathname());
            }
        }

        foreach (EditableTexts::catalog() as $module => $texts) {
            foreach (array_keys($texts) as $key) {
                $this->assertTrue(
                    str_contains($sources, "Copy['{$key}']") || str_contains($sources, "copy['{$key}']"),
                    "El texto «{$module}.{$key}» está en el catálogo pero ninguna vista lo usa",
                );
            }
        }
    }

    public function test_the_editor_gets_the_texts_of_each_template_with_its_own_defaults(): void
    {
        $groups = EditableTexts::forTemplate('invitations.templates.xv-premium');

        $this->assertArrayHasKey('ubicacion', $groups);
        $this->assertArrayHasKey('rsvp', $groups);
        $fields = collect($groups['ubicacion'])->keyBy('key');
        $this->assertSame('Cómo llegar', $fields['location_button']['default']);

        // Lo que la plantilla ya trae como texto propio es el valor por defecto que ve el editor
        foreach (InvitationTemplates::all() as $template => $meta) {
            foreach (EditableTexts::forTemplate($template) as $fieldsOfModule) {
                foreach ($fieldsOfModule as $field) {
                    if (isset($meta['copy'][$field['key']]) && is_string($meta['copy'][$field['key']])) {
                        $this->assertSame($meta['copy'][$field['key']], $field['default']);
                    }
                }
            }
        }
    }

    public function test_sanitize_keeps_only_known_keys_with_real_text(): void
    {
        $clean = EditableTexts::sanitize([
            'location_button' => '  Ver   el mapa ',
            'rsvp_yes' => '   ',
            'desconocido' => 'no entra',
            'court_lottie' => 'tampoco',
            'rsvp_no' => ['no es texto'],
        ]);

        $this->assertSame(['location_button' => 'Ver el mapa'], $clean);
        $this->assertSame([], EditableTexts::sanitize('no es una lista'));
    }

    public function test_a_custom_text_is_saved_shown_and_reset_to_the_template(): void
    {
        $invitation = $this->createInvitation(['slug' => 'textos-propios']);
        $service = app(InvitationModuleService::class);

        $service->syncAllModules($invitation, [
            'ubicacion' => ['nombre_lugar' => 'Salón Aurora', 'direccion' => 'Av. Siempre Viva 123', 'maps_url' => '-17.39,-66.16'],
            'config' => ['textos' => ['location_button' => 'Ver el mapa', 'location_eyebrow' => 'Te esperamos en', 'inventado' => 'x']],
        ]);

        $this->assertSame(2, InvitationText::where('invitation_id', $invitation->id)->count());
        $this->assertSame(
            ['location_button' => 'Ver el mapa', 'location_eyebrow' => 'Te esperamos en'],
            collect($service->resolveModules($invitation->fresh())['config']['textos'])->sortKeys()->all(),
        );

        $this->withoutVite()->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('Ver el mapa')
            ->assertSee('Te esperamos en')
            ->assertDontSee('Cómo llegar');

        // Vaciar un campo lo devuelve al texto de la plantilla
        $service->syncAllModules($invitation->fresh(), ['config' => ['textos' => ['location_eyebrow' => 'Te esperamos en']]]);

        $this->assertSame(['location_eyebrow'], InvitationText::where('invitation_id', $invitation->id)->pluck('key')->all());
    }

    public function test_the_editor_receives_the_editable_texts(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->withoutVite()->get(route('admin.invitations.create'))
            ->assertOk()
            ->assertSee('editableTexts', false)
            ->assertSee('Textos de esta sección');
    }
}
