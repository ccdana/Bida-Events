<?php

namespace Tests\Feature;

use App\Services\InvitationModuleService;
use App\Support\InvitationDefaults;
use App\Support\InvitationTemplates;
use Database\Seeders\CumpleFiestaDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

class BirthdayTemplateTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_birthday_template_renders_modules_with_party_details(): void
    {
        $invitation = $this->createInvitation(['slug' => 'cumple-prueba', 'template' => InvitationTemplates::CUMPLE_FIESTA]);
        app(InvitationModuleService::class)->syncAllModules($invitation, CumpleFiestaDemoSeeder::modules());

        $this->withoutVite()
            ->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertSee('inv-page inv-cumple', false)
            ->assertSee('inv-cumple-intro', false)
            // La edad del subtítulo aparece en grande y como velas con forma de número
            ->assertSee('<span class="inv-cumple-age" aria-hidden="true">30</span>', false)
            ->assertSee('inv-cumple-candle is-digit', false)
            ->assertSee('Mi gente favorita')
            ->assertSee('Amigos')
            ->assertSee('>Anfitriones</button>', false)
            ->assertDontSee('Chambelanes')
            ->assertSee('data-lottie-icon="cake"', false)
            ->assertSee('data-lottie-icon="balloon"', false)
            ->assertSee('inv-cumple-trio', false)
            ->assertSeeInOrder(['id="itinerario"', 'id="playlist"', 'id="destacados"'], false);
    }

    public function test_without_an_age_the_cake_uses_plain_candles(): void
    {
        $modules = CumpleFiestaDemoSeeder::modules();
        $modules['bienvenida']['subtitulo'] = '¡Celebremos!';

        $invitation = $this->createInvitation(['slug' => 'cumple-sin-edad', 'template' => InvitationTemplates::CUMPLE_FIESTA]);
        app(InvitationModuleService::class)->syncAllModules($invitation, $modules);

        $this->withoutVite()
            ->get(route('invitation.show', $invitation->slug))
            ->assertOk()
            ->assertDontSee('inv-cumple-age', false)
            ->assertDontSee('is-digit', false)
            ->assertSee('inv-cumple-candle', false);
    }

    public function test_birthday_template_is_offered_in_the_editor(): void
    {
        $this->assertSame('Sopla las velas', InvitationDefaults::templates()[InvitationTemplates::CUMPLE_FIESTA] ?? null);
    }
}
