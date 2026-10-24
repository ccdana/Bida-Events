<?php

namespace Tests\Feature;

use App\Models\PaymentOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\LemonSqueezyPayloads;
use Tests\TestCase;

/**
 * Pagar un plan desde el panel: elegir, ir a Lemon Squeezy, volver a esperar el aviso y administrar
 * la suscripción en su portal.
 */
class SubscriptionCheckoutTest extends TestCase
{
    use LemonSqueezyPayloads, RefreshDatabase;

    private const CHECKOUT_URL = 'https://bida.lemonsqueezy.com/checkout/custom/abc?signature=x';

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-15 12:00:00', 'America/La_Paz'));
        // El flujo de la tarjeta con el plan Inicial y sin descuento por adelantado
        // (PlansAndCyclesTest prueba el catálogo y los ciclos)
        config(['payments.prepay' => [1 => 0, 3 => 0, 12 => 0]]);
        $this->configureLemonSqueezy();
        Http::fake([
            'api.lemonsqueezy.com/v1/checkouts' => Http::response(['data' => ['id' => 'chk-1', 'attributes' => ['url' => self::CHECKOUT_URL]]], 201),
            'api.lemonsqueezy.com/v1/subscriptions/*' => Http::response(['data' => ['attributes' => ['urls' => ['customer_portal' => 'https://bida.lemonsqueezy.com/billing?x=1']]]]),
        ]);
    }

    public function test_without_online_payments_there_is_nothing_to_buy_here(): void
    {
        config(['payments.enabled' => false]);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('client.subscription'))->assertNotFound();
        $this->actingAs($user)->post(route('client.subscription.store'), ['plan' => 'inicial', 'method' => 'card'])->assertNotFound();

        // Los planes de /precios siguen llevando a WhatsApp
        $this->withoutVite()->get(route('pricing'))->assertOk()->assertDontSee(route('client.subscription', ['plan' => 'inicial']), false);
    }

    public function test_the_plans_page_offers_the_card_and_hazlo_tu_links_to_it(): void
    {
        // Quien no tiene cuenta pasa primero por crearla, con su plan, y de ahí sigue al pago
        $this->withoutVite()->get(route('pricing'))->assertOk()->assertSee(route('register', ['plan' => 'inicial']), false);

        $this->actingAs(User::factory()->create())->withoutVite()->get(route('client.subscription', ['plan' => 'emprendedor']))
            ->assertOk()
            ->assertSee('Elige tu plan')
            ->assertSee('Tarjeta de crédito o débito')
            ->assertSee('value="emprendedor" x-model="plan" class="mt-1 size-4 accent-[var(--site-accent)]" checked', false)
            // Sin appkey de Libélula no se ofrece el pago en Bolivia
            ->assertDontSee('Pago en Bolivia');

        // Con tarjeta y sin variantes de 3 o 12 meses, no hay ciclos que elegir (PlansAndCyclesTest
        // prueba la tarjeta anual)
        config(['payments.lemonsqueezy.variants.inicial:12' => null]);
        $this->actingAs(User::factory()->create())->withoutVite()->get(route('client.subscription'))
            ->assertOk()
            ->assertDontSee('Cuántos meses pagas');

        // Quien ya entró va directo a pagar
        $this->withoutVite()->get(route('pricing'))->assertOk()->assertSee(route('client.subscription', ['plan' => 'inicial']), false);

        // Sin credenciales de Lemon Squeezy no se ofrece la tarjeta
        config(['payments.lemonsqueezy.api_key' => null]);
        $this->actingAs(User::factory()->create())->withoutVite()->get(route('client.subscription'))
            ->assertOk()
            ->assertSee('El pago en línea no está disponible');
    }

    public function test_guests_are_sent_to_sign_in_first(): void
    {
        $this->get(route('client.subscription', ['plan' => 'inicial']))->assertRedirect(route('login'));
    }

    public function test_paying_opens_an_order_and_goes_to_the_checkout(): void
    {
        $user = User::factory()->create(['email' => 'carla@correo.com']);
        $stale = PaymentOrder::forSubscription($user, 'inicial', 1, '9.00', 'USD', 'lemonsqueezy');

        $this->actingAs($user)
            ->post(route('client.subscription.store'), ['plan' => 'emprendedor', 'method' => 'card'])
            ->assertRedirect(self::CHECKOUT_URL);

        $order = PaymentOrder::where('plan', 'emprendedor')->sole();
        $this->assertTrue($order->isPending());
        $this->assertSame('lemonsqueezy', $order->provider);
        $this->assertSame('chk-1', $order->provider_ref);
        // El precio de hoy del plan
        $this->assertSame('20.00', $order->amount);
        $this->assertNotNull($order->expires_at);

        // La compra anterior sin pagar quedó vencida
        $this->assertSame(PaymentOrder::EXPIRED, $stale->fresh()->status);

        // Volver de la pasarela no activa nada: hay que esperar su aviso
        $this->assertFalse($user->fresh()->hasActiveSubscription());

        Http::assertSent(fn (HttpRequest $request) => str_ends_with($request->url(), '/checkouts')
            && $request->data()['data']['attributes']['checkout_data']['custom']['order_id'] === $order->public_id);
    }

    public function test_an_unconfirmed_email_must_be_confirmed_before_paying(): void
    {
        $unverified = User::factory()->unverified()->create();
        $withoutEmail = User::factory()->create(['email' => null, 'email_verified_at' => null]);

        foreach ([$unverified, $withoutEmail] as $user) {
            $this->actingAs($user)
                ->post(route('client.subscription.store'), ['plan' => 'inicial', 'method' => 'card'])
                ->assertRedirect(route('client.account'))
                ->assertSessionHasErrors('email');
        }

        $this->assertSame(0, PaymentOrder::count());
        Http::assertNothingSent();
    }

    public function test_someone_already_paying_by_card_changes_plan_in_the_portal(): void
    {
        $user = User::factory()->reseller()->create([
            'payment_provider' => 'lemonsqueezy', 'provider_subscription_id' => '3001',
        ]);

        $this->actingAs($user)->from(route('client.subscription'))
            ->post(route('client.subscription.store'), ['plan' => 'agencia', 'method' => 'card'])
            ->assertRedirect(route('client.subscription'))
            ->assertSessionHasErrors('plan');
        $this->assertSame(0, PaymentOrder::count());

        $this->actingAs($user)->withoutVite()->get(route('client.subscription'))
            ->assertOk()
            ->assertSee('Tu suscripción con tarjeta')
            ->assertSee('se renueva solo el')
            ->assertSee(route('client.subscription.portal'), false)
            ->assertDontSee('Continuar al pago');

        $this->actingAs($user)->get(route('client.subscription.portal'))->assertRedirect('https://bida.lemonsqueezy.com/billing?x=1');
    }

    public function test_a_gateway_error_does_not_leave_an_open_order(): void
    {
        // Las respuestas del setUp ganan sobre las nuevas: se empieza de cero
        Http::swap(new HttpFactory);
        Http::fake(['api.lemonsqueezy.com/*' => Http::response(['errors' => [['detail' => 'Bad variant']]], 422)]);
        $user = User::factory()->create();

        $this->actingAs($user)->from(route('client.subscription'))
            ->post(route('client.subscription.store'), ['plan' => 'inicial', 'method' => 'card'])
            ->assertRedirect(route('client.subscription'))
            ->assertSessionHasErrors('method');

        $this->assertSame(PaymentOrder::FAILED, PaymentOrder::sole()->status);
    }

    public function test_a_plan_without_its_variant_cannot_be_paid_by_card(): void
    {
        config(['payments.lemonsqueezy.variants.agencia' => null]);

        $this->actingAs(User::factory()->create())->from(route('client.subscription'))
            ->post(route('client.subscription.store'), ['plan' => 'agencia', 'method' => 'card'])
            ->assertSessionHasErrors('method');

        $this->assertSame(0, PaymentOrder::count());
    }

    public function test_the_return_page_waits_for_the_notice_and_shows_the_active_plan(): void
    {
        $user = User::factory()->create();
        $order = PaymentOrder::forSubscription($user, 'inicial', 1, '9.00', 'USD', 'lemonsqueezy');

        $this->actingAs($user)->withoutVite()->get(route('client.subscription.order', $order->public_id))
            ->assertOk()
            ->assertSee('Estamos confirmando tu pago')
            ->assertSee($order->reference_code);

        $this->actingAs($user)->getJson(route('client.subscription.order', $order->public_id))->assertExactJson(['status' => 'pending']);

        // Llega el aviso del cobro
        $this->lsSend($this->lsOrderCreated($order->public_id))->assertOk();

        $this->actingAs($user)->getJson(route('client.subscription.order', $order->public_id))->assertExactJson(['status' => 'paid']);
        $this->actingAs($user->fresh())->withoutVite()->get(route('client.subscription.order', $order->public_id))
            ->assertOk()
            ->assertSee('Listo: tu plan Inicial está activo')
            ->assertSee(route('client.invitations.create'), false);

        // La orden de otra persona no se ve
        $this->actingAs(User::factory()->create())->get(route('client.subscription.order', $order->public_id))->assertNotFound();
        $this->actingAs($user)->get('/client/suscripcion/orden/no-es-un-uuid')->assertNotFound();
    }

    public function test_the_panel_invites_to_pay_and_warns_during_the_grace_days(): void
    {
        config(['payments.grace_days' => 3]);

        $new = User::factory()->create(['is_reseller' => true, 'reseller_plan' => 'inicial']);
        $this->actingAs($new)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Elige tu plan y paga en línea para empezar.')
            ->assertSee(route('client.subscription'), false);

        $late = User::factory()->reseller('inicial', '2026-10-14')->create();
        $this->actingAs($late)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('venció el 14 de octubre')
            ->assertSee('Tienes hasta el 16 de octubre para renovarla')
            ->assertSee(route('client.invitations.create'), false)
            // «Mi plan» de la barra lateral lleva a su plan y sus pagos
            ->assertSee('href="'.route('client.subscription').'"', false);

        // Con tarjeta que se renueva sola, no hace falta avisar del vencimiento
        $card = User::factory()->reseller('inicial', '2026-10-18')->create(['payment_provider' => 'lemonsqueezy', 'provider_subscription_id' => '3001']);
        $this->actingAs($card)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('se renueva solo el')
            ->assertDontSee('Tu suscripción vence');
    }

    public function test_the_account_shows_the_plan_and_its_payments(): void
    {
        $user = User::factory()->create();
        $this->lsSend($this->lsOrderCreated(PaymentOrder::forSubscription($user, 'inicial', 1, '9.00', 'USD', 'lemonsqueezy')->public_id))->assertOk();

        $this->actingAs($user->fresh())->withoutVite()->get(route('client.account'))
            ->assertOk()
            ->assertSee('Plan y pagos')
            ->assertSee('Inicial')
            ->assertSee('· tarjeta')
            ->assertSee(route('client.subscription'), false);
    }
}
