<?php

namespace Tests\Feature;

use App\Models\EventType;
use App\Models\Invitation;
use App\Models\User;
use App\Support\EditableTexts;
use App\Support\InvitationTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Lo que encontró el análisis QA del flujo de crear y editar invitaciones: cada caso es un fallo real que
 * existía (direcciones que daban error 500, plantillas inventadas, el servidor visitando cualquier dirección).
 */
class EditorFlowQaTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private function payload(array $over = []): array
    {
        $type = EventType::firstOrCreate(['slug' => 'xv-anos'], ['name' => 'XV Años', 'code' => 'xv', 'kind' => 'invitation']);

        return array_merge([
            'title' => 'XV de Sofía', 'slug' => 'xv-de-sofia', 'template' => 'invitations.templates.xv-premium',
            'event_type_id' => $type->id, 'user_id' => '', 'event_date' => '2027-03-14 19:00',
            'status' => 'inactive', 'expires_at' => '2027-12-31',
            'modulos' => ['bienvenida' => json_encode(['nombre' => 'Sofía'])],
        ], $over);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_a_slug_with_capitals_that_collides_after_cleaning_is_a_validation_error_not_a_500(): void
    {
        $this->createInvitation(['slug' => 'mi-boda']);

        $this->actingAs($this->admin())
            ->post(route('admin.invitations.store'), $this->payload(['slug' => 'Mi Boda']))
            ->assertSessionHasErrors('slug');

        $this->assertSame(1, Invitation::where('slug', 'mi-boda')->count());
    }

    public function test_the_slug_is_saved_the_way_it_was_checked(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.invitations.store'), $this->payload(['slug' => 'Mi Boda Linda']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('invitations', ['slug' => 'mi-boda-linda']);
    }

    public function test_editing_keeps_working_when_the_slug_does_not_change(): void
    {
        $invitation = $this->createInvitation(['slug' => 'mi-boda', 'expires_at' => '2027-12-31']);

        $this->actingAs($this->admin())
            ->put(route('admin.invitations.update', $invitation), $this->payload(['slug' => 'mi-boda', 'title' => 'Otro título']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Otro título', $invitation->fresh()->title);
    }

    public function test_a_template_that_does_not_exist_is_refused(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.invitations.store'), $this->payload(['template' => 'invitations.templates.no-existe']))
            ->assertSessionHasErrors('template');

        $this->assertDatabaseMissing('invitations', ['slug' => 'xv-de-sofia']);
    }

    public function test_the_link_has_to_outlive_the_event(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.invitations.store'), $this->payload(['event_date' => '2027-06-20 19:00', 'expires_at' => '2027-06-01']))
            ->assertSessionHasErrors('expires_at');

        // El mismo día del evento sí vale
        $this->actingAs($admin)
            ->post(route('admin.invitations.store'), $this->payload(['event_date' => '2027-06-20 19:00', 'expires_at' => '2027-06-20']))
            ->assertSessionHasNoErrors();
    }

    public function test_the_maps_resolver_does_not_visit_internal_addresses(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $admin = $this->admin();

        foreach (['http://169.254.169.254/latest/meta-data/', 'http://127.0.0.1:8080/admin', 'https://localhost/x', 'file:///etc/passwd', 'https://evil.example.com/redirect', 'http://www.google.com/x'] as $url) {
            $this->actingAs($admin)->postJson(route('admin.maps.resolve'), ['url' => $url])->assertStatus(422);
        }

        Http::assertNothingSent();
    }

    public function test_a_short_maps_link_is_followed_but_only_through_map_sites(): void
    {
        Http::fake([
            'maps.app.goo.gl/*' => Http::response('', 302, ['Location' => 'https://www.google.com/maps/place/Salon/@-17.3895,-66.1568,17z']),
        ]);

        $this->actingAs($this->admin())
            ->postJson(route('admin.maps.resolve'), ['url' => 'https://maps.app.goo.gl/abc123'])
            ->assertOk()
            ->assertJsonPath('lat', -17.3895)
            ->assertJsonPath('lng', -66.1568);

    }

    public function test_a_redirect_that_leaves_the_map_sites_stops_there(): void
    {
        Http::fake([
            'maps.app.goo.gl/*' => Http::response('', 302, ['Location' => 'http://169.254.169.254/latest/meta-data/']),
        ]);

        $this->actingAs($this->admin())
            ->postJson(route('admin.maps.resolve'), ['url' => 'https://maps.app.goo.gl/otro'])
            ->assertStatus(422);

        Http::assertNotSent(fn ($request) => str_contains($request->url(), '169.254'));
    }

    public function test_the_compact_editable_texts_rebuild_exactly_what_each_template_had(): void
    {
        $templates = array_keys(InvitationTemplates::all());
        $compact = EditableTexts::forEditor($templates);

        foreach ($templates as $template) {
            $this->assertSame(EditableTexts::forTemplate($template), EditableTexts::expand($compact, $template), "Los textos de {$template} cambiaron al compactarlos");
        }

        // Y de verdad pesan mucho menos que repetir el catálogo por plantilla
        $full = strlen(json_encode(array_map(fn ($t) => EditableTexts::forTemplate($t), $templates)));
        $this->assertLessThan($full / 3, strlen(json_encode($compact)));
    }

    public function test_the_editor_page_stays_light(): void
    {
        $html = $this->actingAs($this->admin())->withoutVite()->get(route('admin.invitations.create'))->assertOk()->getContent();

        $this->assertLessThan(1_000_000, strlen($html), 'El editor pesa más de 1 MB de HTML');
    }
}
