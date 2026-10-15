<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\InvitationTemplates;
use App\Support\ResellerSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Reglas de la suscripción del revendedor: cuándo está al día, cuánto cupo le queda en el mes y
 * hasta cuándo se extiende cuando paga.
 */
class ResellerSubscriptionTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-15 12:00:00', 'America/La_Paz'));
    }

    public function test_the_subscription_is_active_only_while_it_is_paid_and_not_expired(): void
    {
        config(['payments.grace_days' => 0]);

        $this->assertTrue(User::factory()->reseller(renewsAt: '2026-11-15')->make()->hasActiveSubscription());

        // Sin días de gracia, el día de la renovación ya hay que pagar, y los días anteriores tampoco cuentan
        $this->assertFalse(User::factory()->reseller(renewsAt: '2026-10-15')->make()->hasActiveSubscription());
        $this->assertFalse(User::factory()->reseller(renewsAt: '2026-10-01')->make()->hasActiveSubscription());

        // Con el cobro atrasado (la tarjeta no pasó) vale hasta la fecha; cancelada o terminada, no
        $this->assertTrue(User::factory()->reseller(renewsAt: '2026-11-15', status: 'past_due')->make()->hasActiveSubscription());
        $this->assertFalse(User::factory()->reseller(renewsAt: '2026-11-15', status: 'canceled')->make()->hasActiveSubscription());

        // Nunca pagó, o no es revendedor
        $this->assertFalse(User::factory()->make(['is_reseller' => true, 'subscription_status' => 'active'])->hasActiveSubscription());
        $this->assertFalse(User::factory()->make()->hasActiveSubscription());
    }

    public function test_the_grace_days_keep_the_editor_open_after_the_due_date(): void
    {
        config(['payments.grace_days' => 3]);

        // Venció ayer: sigue pudiendo crear, y se le avisa hasta cuándo
        $yesterday = User::factory()->reseller(renewsAt: '2026-10-14')->make();
        $this->assertTrue($yesterday->hasActiveSubscription());
        $this->assertTrue(ResellerSubscription::inGrace($yesterday));
        $this->assertSame('2026-10-16', ResellerSubscription::graceEndsAt($yesterday)->toDateString());

        // Vence hoy: también en gracia; con cobro atrasado, igual
        $this->assertTrue(ResellerSubscription::inGrace(User::factory()->reseller(renewsAt: '2026-10-15')->make()));
        $this->assertTrue(User::factory()->reseller(renewsAt: '2026-10-13', status: 'past_due')->make()->hasActiveSubscription());

        // Tres días después del vencimiento ya se bloquea
        $this->assertFalse(User::factory()->reseller(renewsAt: '2026-10-12')->make()->hasActiveSubscription());
        $this->assertFalse(ResellerSubscription::inGrace(User::factory()->reseller(renewsAt: '2026-10-12')->make()));

        // Al día no está en gracia
        $this->assertFalse(ResellerSubscription::inGrace(User::factory()->reseller(renewsAt: '2026-11-15')->make()));
    }

    public function test_the_plan_comes_from_the_configuration(): void
    {
        $reseller = User::factory()->reseller('emprendedor')->make();

        $this->assertSame(20, $reseller->planConfig()['quota_per_month']);
        $this->assertTrue($reseller->planConfig()['white_label']);
        $this->assertNull(User::factory()->make()->planConfig());
        $this->assertNull(User::factory()->reseller('plan-que-no-existe')->make()->planConfig());
    }

    public function test_the_quota_counts_only_the_invitations_published_this_month(): void
    {
        $reseller = User::factory()->reseller()->create();

        // Dos publicadas el mes pasado no cuentan; tres publicadas este mes sí; los borradores, nunca
        foreach (['2026-09-20', '2026-09-30 23:30'] as $date) {
            $this->createInvitation(['reseller_id' => $reseller->id])->forceFill(['first_published_at' => Carbon::parse($date, 'America/La_Paz')])->save();
        }
        foreach (range(1, 3) as $ignored) {
            $this->createInvitation(['reseller_id' => $reseller->id]);
        }
        foreach (range(1, 4) as $ignored) {
            $this->createInvitation(['reseller_id' => $reseller->id, 'status' => 'inactive']);
        }

        $this->assertSame(3, ResellerSubscription::quotaUsed($reseller));
        $this->assertSame(5, ResellerSubscription::quotaLimit($reseller));
        $this->assertSame(2, ResellerSubscription::quotaLeft($reseller));
        $this->assertTrue(ResellerSubscription::hasQuotaLeft($reseller));

        foreach (range(1, 2) as $ignored) {
            $this->createInvitation(['reseller_id' => $reseller->id]);
        }

        $this->assertSame(0, ResellerSubscription::quotaLeft($reseller));
        $this->assertFalse(ResellerSubscription::hasQuotaLeft($reseller));

        // Al mes siguiente el cupo vuelve a empezar
        $this->assertSame(0, ResellerSubscription::quotaUsed($reseller, Carbon::parse('2026-11-02', 'America/La_Paz')));
    }

    public function test_a_plan_without_a_limit_never_runs_out(): void
    {
        $reseller = User::factory()->reseller('agencia')->create();

        foreach (range(1, 30) as $ignored) {
            $this->createInvitation(['reseller_id' => $reseller->id]);
        }

        $this->assertNull(ResellerSubscription::quotaLimit($reseller));
        $this->assertNull(ResellerSubscription::quotaLeft($reseller));
        $this->assertTrue(ResellerSubscription::hasQuotaLeft($reseller));

        // Sin plan no hay cupo
        $this->assertSame(0, ResellerSubscription::quotaLimit(User::factory()->make(['is_reseller' => true])));
    }

    public function test_a_payment_extends_from_the_current_due_date_or_from_today_if_it_already_expired(): void
    {
        // Todavía no venció: el mes se suma al vencimiento, no se pierden días por pagar antes
        $early = User::factory()->reseller(renewsAt: '2026-10-20')->make();
        $this->assertSame('2026-11-20', ResellerSubscription::renewalDate($early, 'inicial')->toDateString());

        // Ya venció: cuenta desde hoy
        $late = User::factory()->reseller(renewsAt: '2026-09-01')->make();
        $this->assertSame('2026-11-15', ResellerSubscription::renewalDate($late, 'inicial')->toDateString());

        // Nunca pagó
        $new = User::factory()->make(['is_reseller' => true]);
        $this->assertSame('2026-11-15', ResellerSubscription::renewalDate($new, 'inicial')->toDateString());

        // Fin de mes: del 31 de enero al 28 de febrero, sin saltar a marzo
        $endOfMonth = User::factory()->reseller(renewsAt: '2027-01-31')->make();
        $this->assertSame('2027-02-28', ResellerSubscription::renewalDate($endOfMonth, 'inicial')->toDateString());
    }

    public function test_the_warning_starts_five_days_before_the_due_date(): void
    {
        $this->assertFalse(ResellerSubscription::expiresSoon(User::factory()->reseller(renewsAt: '2026-10-21')->make()));
        $this->assertTrue(ResellerSubscription::expiresSoon(User::factory()->reseller(renewsAt: '2026-10-20')->make()));
        $this->assertTrue(ResellerSubscription::expiresSoon(User::factory()->reseller(renewsAt: '2026-10-10')->make()));
        $this->assertSame(-5, ResellerSubscription::daysLeft(User::factory()->reseller(renewsAt: '2026-10-10')->make()));
    }

    public function test_the_catalog_follows_the_plan(): void
    {
        $reseller = User::factory()->reseller()->make();

        // Cada plan ve las familias que incluye: el Inicial suma las de temporada (Halloween y tarjetas)
        $inicial = array_keys(ResellerSubscription::allowedTemplates($reseller));
        $this->assertContains(InvitationTemplates::XV_PREMIUM, $inicial);
        $this->assertContains(InvitationTemplates::TARJETA_AMOR, $inicial);
        $this->assertContains('invitations.templates.halloween-calabazas', $inicial);

        // Las nuevas de cada evento, desde Emprendedor
        $this->assertContains('nueva', config('bida.reseller_plans.emprendedor.collections'));
        $this->assertNotContains('nueva', config('bida.reseller_plans.inicial.collections'));

        // El Emprendedor y la Agencia, todo el catálogo
        $this->assertSame(array_keys(InvitationTemplates::all()), array_keys(ResellerSubscription::allowedTemplates(User::factory()->reseller('emprendedor')->make())));

        // Y se puede recortar desde la configuración sin tocar código
        config(['bida.reseller_plans.inicial.templates' => [InvitationTemplates::BODA_JARDIN]]);
        $this->assertSame([InvitationTemplates::BODA_JARDIN], array_keys(ResellerSubscription::allowedTemplates($reseller)));
    }
}
