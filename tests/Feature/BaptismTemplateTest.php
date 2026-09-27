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
            // «Entre nubes»: se entra abriendo las nubes y la foto es el sol entre ellas
            ->assertSee('inv-page inv-nubes inv-themed', false)
            ->assertSee('nb-intro', false)
            ->assertSee('Toca la nube para abrir el cielo')
            ->assertSee('nb-sun', false)
            ->assertDontSee('inv-boda-cover', false)
            ->assertSee('Mateo Andrés')
            ->assertSee('Mis padrinos')
            ->assertDontSee('Chambelanes')
            // Sin palomas: ni en la portada ni en el ícono de los padrinos
            ->assertDontSee('data-lottie-icon="dove"', false)
            // Cada padrino en su nube y, después, la familia (abuelos y tíos)
            ->assertSeeInOrder(['nb-sponsor', 'Andrea Gutiérrez y Rodrigo Paz', 'Abuelos', 'Hugo y Rosa Paz', 'Tíos', 'Lucía Soria'], false)
            // El día sube de nube en nube
            ->assertSee('nb-step__time', false)
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
        $this->assertSame('Entre nubes', InvitationDefaults::templates()[InvitationTemplates::BAUTIZO_CIELO] ?? null);
    }
}
