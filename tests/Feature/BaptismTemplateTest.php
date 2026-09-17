<?php

namespace Tests\Feature;

use App\Services\InvitationModuleService;
use App\Support\InvitationDefaults;
use App\Support\InvitationTemplates;
use Database\Seeders\BautizoCieloDemoSeeder;
use Database\Seeders\BodaJardinDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

class BaptismTemplateTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_baptism_template_renders_modules_with_baptism_copy_and_intro(): void
    {
        $invitation = $this->createInvitation(['slug' => 'bautizo-prueba', 'template' => InvitationTemplates::BAUTIZO_CIELO]);
        app(InvitationModuleService::class)->syncAllModules($invitation, BautizoCieloDemoSeeder::modules());

        $this->withoutVite()
            ->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('inv-page inv-bautizo', false)
            ->assertSee('inv-bautizo-intro', false)
            ->assertDontSee('inv-boda-cover', false)
            ->assertSee('Mateo Andrés')
            ->assertSee('Mis padrinos')
            ->assertSee('Abuelos')
            ->assertDontSee('Chambelanes')
            ->assertSee('data-lottie-icon="dove"', false)
            // Los padrinos van primero y el cortejo se presenta como la familia
            ->assertSeeInOrder(["tab = 'padrinos'", "tab = 'cortejo'"], false)
            ->assertSee('>Familia</button>', false)
            ->assertSeeInOrder(['id="ubicacion"', 'id="itinerario"', 'id="destacados"', 'id="galeria"'], false);
    }

    public function test_other_templates_keep_the_cortejo_tab_first(): void
    {
        $invitation = $this->createInvitation(['template' => InvitationTemplates::BODA_JARDIN]);
        app(InvitationModuleService::class)->syncAllModules($invitation, BodaJardinDemoSeeder::modules());

        $this->withoutVite()
            ->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSeeInOrder(["tab = 'cortejo'", "tab = 'padrinos'"], false);
    }

    public function test_baptism_template_is_offered_in_the_editor(): void
    {
        $this->assertSame('Bautizo Cielo', InvitationDefaults::templates()[InvitationTemplates::BAUTIZO_CIELO] ?? null);
    }
}
