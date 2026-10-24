<?php

namespace Tests\Feature;

use App\Models\EventType;
use App\Models\Invitation;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Payments\SubscriptionPrice;
use App\Support\InvitationTemplates;
use App\Support\Offers;
use App\Support\ResellerSubscription;
use App\Support\SiteSettings;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CreatesInvitations;
use Tests\Concerns\LemonSqueezyPayloads;
use Tests\Concerns\LibelulaApi;
use Tests\TestCase;

/**
 * Los planes para quien arma invitaciones para otros: tres a la venta (los que ya no se venden los
 * conserva quien los tiene), el cupo cuenta las publicadas y se paga por mes, por 3 o por 12 meses
 * con descuento (con QR por adelantado; con tarjeta, se renueva sola en ese ciclo).
 */
class PlansAndCyclesTest extends TestCase
{
    use CreatesInvitations, LemonSqueezyPayloads, LibelulaApi, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-15 12:00:00', 'America/La_Paz'));
        config(['payments.prepay' => [1 => 0, 3 => 10, 12 => 20]]);
    }

    public function test_three_plans_are_for_sale_and_the_page_offers_paying_by_the_year(): void
    {
        $this->assertSame(['inicial', 'emprendedor', 'agencia'], array_keys(Offers::resellerPlans()));

        // Los planes con su selector de ciclo viven en /precios; «Únete» solo los resume y lleva allá
        $response = $this->withoutVite()->get(route('pricing'))
            ->assertOk()
            ->assertSee('Emprendedor')
            // El selector de ciclo, con el descuento de cada uno
            ->assertSee('Cada 3 meses')
            ->assertSee('20 % menos')
            ->assertSee('los borradores no');

        // Inicial anual: 10 × 12 − 20 % = 96 (8 por mes), y «Elegir» pasa a pagar ese ciclo
        $inicial = collect($response->viewData('plans'))->firstWhere('key', 'inicial');
        $this->assertSame('8', $inicial['cycles'][12]['price']);
        $this->assertSame('US$ 10', $inicial['cycles'][12]['old']);
        $this->assertSame('US$ 96 al año · ahorras US$ 24', $inicial['cycles'][12]['saving']);
        $this->assertSame('US$ 27 cada 3 meses · ahorras US$ 3', $inicial['cycles'][3]['saving']);
        $this->assertNull($inicial['cycles'][1]['saving']);
    }

    public function test_a_plan_that_is_not_for_sale_cannot_be_bought(): void
    {
        $this->configureLemonSqueezy();

        $this->actingAs(User::factory()->create())
            ->post(route('client.subscription.store'), ['plan' => 'aliado', 'method' => 'card'])
            ->assertSessionHasErrors(['plan' => 'Ese plan no existe.']);
        $this->assertSame(0, PaymentOrder::count());
    }

    public function test_the_quota_counts_published_invitations_not_drafts(): void
    {
        $reseller = User::factory()->reseller('inicial')->create();
        config(['bida.reseller_plans.inicial.quota_per_month' => 2]);
        $eventType = EventType::firstOrCreate(['slug' => 'xv-anos'], ['name' => 'XV Años']);

        // Tres borradores: crear no gasta cupo
        foreach (['uno', 'dos', 'tres'] as $slug) {
            $this->actingAs($reseller)->post(route('client.invitations.store'), $this->payload($eventType, "xv-{$slug}", 'inactive'))->assertRedirect();
        }
        $this->assertSame(0, ResellerSubscription::quotaUsed($reseller));

        // Publica dos: el cupo se llena
        foreach (['uno', 'dos'] as $slug) {
            $invitation = Invitation::where('slug', "xv-{$slug}")->sole();
            $this->actingAs($reseller)->put(route('client.invitations.update', $invitation), $this->payload($eventType, "xv-{$slug}", 'active'))
                ->assertSessionHas('success');
            $this->assertSame('active', $invitation->fresh()->status);
        }
        $this->assertSame(2, ResellerSubscription::quotaUsed($reseller));

        // La tercera se guarda, pero no se publica
        $third = Invitation::where('slug', 'xv-tres')->sole();
        $this->actingAs($reseller)->put(route('client.invitations.update', $third), $this->payload($eventType, 'xv-tres', 'active', 'Título nuevo'))
            ->assertRedirect(route('client.invitations.edit', $third))
            ->assertSessionHas('warning');
        $this->assertSame('inactive', $third->fresh()->status);
        $this->assertSame('Título nuevo', $third->fresh()->title);

        // Ocultar y volver a publicar una que ya contó no gasta cupo otra vez
        $first = Invitation::where('slug', 'xv-uno')->sole();
        $this->actingAs($reseller)->put(route('client.invitations.update', $first), $this->payload($eventType, 'xv-uno', 'inactive'));
        $this->actingAs($reseller)->put(route('client.invitations.update', $first), $this->payload($eventType, 'xv-uno', 'active'))
            ->assertSessionHas('success');
        $this->assertSame('active', $first->fresh()->status);
        $this->assertSame(2, ResellerSubscription::quotaUsed($reseller));

        // El mes que viene vuelve a tener cupo
        $this->assertSame(0, ResellerSubscription::quotaUsed($reseller, now()->addMonthNoOverflow()));

        // Sin cupo igual puede crear borradores desde el panel
        $this->actingAs($reseller)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('invitaciones publicadas este mes')
            ->assertSee(route('client.invitations.create'), false)
            ->assertSee('Ya publicaste todas las de este mes');
    }

    public function test_paying_twelve_months_with_qr_has_the_annual_discount(): void
    {
        $this->configureLibelula();
        $this->fakeLibelula();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('client.subscription.store'), ['plan' => 'inicial', 'method' => 'qr', 'months' => 12])->assertRedirect();

        // 10 × 12 − 20 % = US$ 96 → Bs 668 (× 6,96, redondeado)
        $order = PaymentOrder::sole();
        $this->assertSame(12, $order->months);
        $this->assertSame('668.00', $order->amount);
        $this->assertSame('96.00', SubscriptionPrice::amount('inicial', 12, 'USD'));
    }

    public function test_the_card_renews_yearly_when_the_plan_has_that_variant(): void
    {
        $this->configureLemonSqueezy();
        Http::fake(['api.lemonsqueezy.com/v1/checkouts' => Http::response(['data' => ['id' => 'chk-anual', 'attributes' => ['url' => 'https://pago.test/anual']]], 201)]);
        $user = User::factory()->create();

        // Sin la variante de cada 3 meses, ese ciclo no se ofrece con tarjeta
        $this->actingAs($user)
            ->post(route('client.subscription.store'), ['plan' => 'inicial', 'method' => 'card', 'months' => 3])
            ->assertSessionHasErrors('months');

        $this->actingAs($user)
            ->post(route('client.subscription.store'), ['plan' => 'inicial', 'method' => 'card', 'months' => 12])
            ->assertRedirect('https://pago.test/anual');

        $order = PaymentOrder::sole();
        $this->assertSame(12, $order->months);
        $this->assertSame('96.00', $order->amount);
        Http::assertSent(fn (HttpRequest $request) => $request->data()['data']['relationships']['variant']['data']['id'] === '112');

        // El cobro de la variante anual activa 12 meses y deja anotado el ciclo
        $this->lsSend($this->lsOrderCreated($order->public_id, '9500', 'inicial:12', 9600))->assertOk();
        $user->refresh();
        $this->assertSame('inicial', $user->reseller_plan);
        $this->assertSame(12, $user->subscription_cycle_months);
        $this->assertSame('2027-10-15', $user->subscription_renews_at->toDateString());

        // Cada renovación anual suma 12 meses
        $user->forceFill(['payment_provider' => 'lemonsqueezy', 'provider_subscription_id' => '77'])->save();
        $this->lsSend($this->lsInvoice('subscription_payment_success', '8800', '77', total: 9600))->assertOk();
        $this->assertSame('2028-10-15', $user->fresh()->subscription_renews_at->toDateString());
    }

    public function test_a_card_charge_for_another_cycle_is_left_for_review(): void
    {
        $this->configureLemonSqueezy();
        Http::fake(['api.lemonsqueezy.com/v1/checkouts' => Http::response(['data' => ['id' => 'chk-1', 'attributes' => ['url' => 'https://pago.test']]], 201)]);
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('client.subscription.store'), ['plan' => 'inicial', 'method' => 'card']);

        // Se compró el mensual y se cobró la variante anual: no se activa
        $this->lsSend($this->lsOrderCreated(PaymentOrder::sole()->public_id, '9600', 'inicial:12', 9600))->assertOk();

        $this->assertNull($user->fresh()->subscription_renews_at);
    }

    public function test_the_discounts_are_set_in_settings(): void
    {
        SiteSettings::put('prepay', [3 => 5, 12 => 15]);
        SiteSettings::apply();

        $this->assertSame([1 => 0, 3 => 5, 12 => 15], SubscriptionPrice::prepayOptions());

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->withoutVite()->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Pagar por 3 o 12 meses')
            ->assertSee('name="prepay[12]" value="15"', false);
    }

    private function payload(EventType $eventType, string $slug, string $status, string $title = 'XV de prueba'): array
    {
        return [
            'title' => $title,
            'slug' => $slug,
            'template' => InvitationTemplates::XV_PREMIUM,
            'event_type_id' => $eventType->id,
            'event_date' => now()->addMonths(2)->format('Y-m-d H:i'),
            'status' => $status,
            'expires_at' => now()->addYear()->toDateString(),
            'modulos' => array_map(fn ($module) => json_encode($module), XvSofiaModuleData::all()),
        ];
    }
}
