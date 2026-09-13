<?php

namespace Tests\Feature;

use App\Services\InvitationModuleService;
use App\Support\InvitationDefaults;
use App\Support\InvitationTemplates;
use Database\Seeders\BodaJardinDemoSeeder;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

class WeddingTemplateTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_wedding_template_renders_modules_with_wedding_copy_and_cover(): void
    {
        $invitation = $this->createInvitation(['slug' => 'boda-prueba', 'template' => InvitationTemplates::BODA_JARDIN]);
        app(InvitationModuleService::class)->syncAllModules($invitation, BodaJardinDemoSeeder::modules());

        $this->withoutVite()
            ->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('inv-page inv-boda', false)
            ->assertSee('inv-boda-cover', false)
            ->assertSeeInOrder(['inv-boda-hero__names', 'Ana', '&amp;', 'Luis'], false)
            ->assertSee('Damas de honor')
            ->assertSee('Caballeros de honor')
            ->assertDontSee('Chambelanes')
            ->assertSee('data-lottie-icon="rings"', false)
            ->assertSee('Nuestra historia')
            // Orden propio de la plantilla: la ubicación va antes del itinerario
            ->assertSeeInOrder(['id="galeria"', 'id="ubicacion"', 'id="itinerario"'], false);
    }

    public function test_xv_template_keeps_its_own_copy(): void
    {
        $invitation = $this->createInvitation();
        app(InvitationModuleService::class)->syncAllModules($invitation, XvSofiaModuleData::all());

        $this->withoutVite()
            ->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('Chambelanes')
            ->assertSee('data-lottie-icon="crown"', false)
            ->assertDontSee('inv-boda', false)
            ->assertSeeInOrder(['id="itinerario"', 'id="dress-code"', 'id="destacados"', 'id="ubicacion"'], false);
    }

    public function test_wedding_template_is_offered_in_the_editor(): void
    {
        $this->assertSame('Boda Jardín', InvitationDefaults::templates()[InvitationTemplates::BODA_JARDIN] ?? null);
        $this->assertSame(InvitationTemplates::BODA_JARDIN, InvitationDefaults::resolveTemplate(InvitationTemplates::BODA_JARDIN));
    }
}
