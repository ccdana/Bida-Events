<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\InvitationModuleService;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * El pie de la invitación: el crédito de Bida Events para todos, salvo los revendedores con marca
 * blanca, que muestran su nombre comercial y no llevan a la portada de Bida.
 */
class WhiteLabelFooterTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_a_normal_client_keeps_the_bida_credit(): void
    {
        $this->publicPage(User::factory()->create(['business_name' => 'No aplica']))
            ->assertSee('Hecho con cariño por <span class="inv-footer__brand">Bida Events</span>', false)
            ->assertSee('<a href="'.route('home').'" class="inv-footer__credit"', false)
            ->assertDontSee('No aplica');
    }

    public function test_a_reseller_without_white_label_keeps_the_bida_credit(): void
    {
        $this->publicPage(User::factory()->reseller('aliado')->create(['business_name' => 'Estudio Aliado']))
            ->assertSee('<span class="inv-footer__brand">Bida Events</span>', false)
            ->assertSee('<a href="'.route('home').'" class="inv-footer__credit"', false)
            ->assertDontSee('Estudio Aliado');
    }

    public function test_a_white_label_reseller_signs_with_its_business_name(): void
    {
        $this->publicPage(User::factory()->reseller('emprendedor')->create(['business_name' => 'Estudio Luz de Tarde']))
            ->assertSee('Hecho con cariño por <span class="inv-footer__brand">Estudio Luz de Tarde</span>', false)
            ->assertDontSee('<span class="inv-footer__brand">Bida Events</span>', false)
            ->assertDontSee('<a href="'.route('home').'" class="inv-footer__credit"', false)
            ->assertDontSee('Crea la tuya');
    }

    public function test_without_a_business_name_the_bida_credit_stays(): void
    {
        $this->publicPage(User::factory()->reseller('agencia')->create(['business_name' => null]))
            ->assertSee('<span class="inv-footer__brand">Bida Events</span>', false);
    }

    private function publicPage(User $owner)
    {
        $invitation = $this->createInvitation(['reseller_id' => $owner->id]);
        app(InvitationModuleService::class)->syncAllModules($invitation, XvSofiaModuleData::all());

        return $this->withoutVite()->get(route('invitation.show', $invitation->slug))->assertOk();
    }
}
