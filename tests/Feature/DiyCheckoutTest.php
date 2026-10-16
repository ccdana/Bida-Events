<?php

namespace Tests\Feature;

use App\Models\EventType;
use App\Models\Invitation;
use App\Models\InvitationPayment;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Payments\InvitationPrice;
use App\Services\InvitationModuleService;
use App\Support\DiyInvitations;
use App\Support\Money;
use App\Support\Packages;
use App\Support\SiteSettings;
use Database\Seeders\XvSofiaModuleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CreatesInvitations;
use Tests\Concerns\LemonSqueezyPayloads;
use Tests\Concerns\LibelulaApi;
use Tests\TestCase;

/**
 * El cobro de «Hazlo tú»: publicar con el nivel que piden sus partes es un pago único (tarjeta con
 * Lemon Squeezy o QR con Libélula), subir de nivel cobra la diferencia y la invitación se publica
 * recién cuando la pasarela avisa que cobró. Una devolución la vuelve al nivel que tenía.
 */
class DiyCheckoutTest extends TestCase
{
    use CreatesInvitations, LemonSqueezyPayloads, LibelulaApi, RefreshDatabase;

    private const CHECKOUT_URL = 'https://bida.lemonsqueezy.com/checkout/custom/inv?signature=x';

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['email' => 'duena@correo.com']);
        EventType::firstOrCreate(['slug' => 'xv-anos'], ['name' => 'XV Años']);
        $this->configureLemonSqueezy();
        config(['bida.diy.prices' => [
            'basico' => ['price' => 9, 'promo_price' => null],
            'estandar' => ['price' => 19, 'promo_price' => null],
            'premium' => ['price' => 35, 'promo_price' => null],
        ]]);
        Http::fake(['api.lemonsqueezy.com/v1/checkouts' => Http::response(['data' => ['id' => 'chk-inv', 'attributes' => ['url' => self::CHECKOUT_URL]]], 201)]);
    }

    public function test_prices_come_from_the_level_and_an_upgrade_costs_the_difference(): void
    {
        $draft = $this->draft();
        $this->assertSame(35, InvitationPrice::due($draft, Packages::PREMIUM));
        $this->assertSame('35.00', InvitationPrice::amount(35, 'USD'));
        // En bolivianos, a Bs enteros (35 × 6,96 = 243,60)
        $this->assertSame('244.00', InvitationPrice::amount(35, 'BOB'));

        // Con Básico pagado, llegar a Premium cuesta la diferencia; la gratuita no cuenta como pagada
        $this->assertSame(26, InvitationPrice::due($this->draft(['package' => Packages::BASIC, 'status' => 'active']), Packages::PREMIUM));
        $this->assertSame(35, InvitationPrice::due($this->draft(['package' => Packages::FREE, 'status' => 'active']), Packages::PREMIUM));

        // Con descuento cargado (Ajustes), se cobra el descuento
        SiteSettings::put('diy_prices', ['premium' => ['price' => 35, 'promo_price' => 29]]);
        SiteSettings::apply();
        $this->assertSame(29, InvitationPrice::tierPrice(Packages::PREMIUM));
    }

    public function test_the_publish_page_offers_paying_online(): void
    {
        $draft = $this->draft();

        $this->actingAs($this->owner)->withoutVite()->get(route('client.diy.publish', $draft))
            ->assertOk()
            ->assertSee(Money::format(35))
            ->assertSee('pago único')
            ->assertSee('Tarjeta de crédito o débito')
            ->assertSee('Pagar y publicar')
            ->assertDontSee('Pedir Premium por WhatsApp')
            // Sin appkey de Libélula no se ofrece el QR
            ->assertDontSee('QR de Bolivia');
    }

    public function test_paying_with_the_card_opens_an_order_with_the_level_price(): void
    {
        $draft = $this->draft();

        $this->actingAs($this->owner)
            ->post(route('client.diy.checkout', $draft), ['method' => 'card'])
            ->assertRedirect(self::CHECKOUT_URL);

        $order = PaymentOrder::sole();
        $this->assertSame(PaymentOrder::PURPOSE_INVITATION, $order->purpose);
        $this->assertSame($draft->id, $order->invitation_id);
        $this->assertSame(Packages::PREMIUM, $order->plan);
        $this->assertSame('35.00', $order->amount);
        $this->assertSame('USD', $order->currency);
        $this->assertSame('chk-inv', $order->provider_ref);
        $this->assertNull($order->meta['from_tier']);
        // Sin el aviso de la pasarela no se publica
        $this->assertSame('inactive', $draft->fresh()->status);

        Http::assertSent(function (HttpRequest $request) use ($order) {
            $attributes = $request->data()['data']['attributes'];

            return $attributes['custom_price'] === 3500
                && $attributes['product_options']['name'] === 'Invitación Premium · «XV de prueba»'
                && $attributes['product_options']['redirect_url'] === route('client.diy.order', $order->public_id)
                && $request->data()['data']['relationships']['variant']['data']['id'] === '199';
        });
    }

    public function test_the_paid_notice_publishes_the_invitation_once(): void
    {
        $draft = $this->draft();
        $this->actingAs($this->owner)->post(route('client.diy.checkout', $draft), ['method' => 'card']);
        $order = PaymentOrder::sole();

        $paid = $this->lsOrderCreated($order->public_id, '9100', 'invitacion', 3500);
        $this->lsSend($paid)->assertOk();
        $this->lsSend($paid)->assertOk();

        $draft->refresh();
        $this->assertSame('active', $draft->status);
        $this->assertSame(Packages::PREMIUM, $draft->package);
        $this->assertSame($draft->event_date->copy()->startOfDay()->addDays(30)->toDateString(), $draft->expires_at->toDateString());
        $this->assertSame(PaymentOrder::PAID, $order->fresh()->status);

        $payment = InvitationPayment::sole();
        $this->assertSame(Packages::PREMIUM, $payment->tier);
        $this->assertNull($payment->from_tier);
        $this->assertSame('order:9100', $payment->external_id);
        $this->assertSame('35.00', $payment->amount);

        $this->actingAs($this->owner)->withoutVite()->get(route('client.diy.order', $order->public_id))
            ->assertOk()
            ->assertSee('Listo: tu invitación está publicada con el nivel Premium')
            ->assertSee(route('invitation.show', $draft->slug), false);
    }

    public function test_a_notice_for_another_product_is_left_for_review(): void
    {
        $draft = $this->draft();
        $this->actingAs($this->owner)->post(route('client.diy.checkout', $draft), ['method' => 'card']);
        $order = PaymentOrder::sole();

        // Cobró la variante de un plan mensual y no la de la invitación: no se publica
        $this->lsSend($this->lsOrderCreated($order->public_id, '9101', 'inicial', 3500))->assertOk();

        $this->assertSame('inactive', $draft->fresh()->status);
        $this->assertSame(0, InvitationPayment::count());
    }

    public function test_upgrading_charges_the_difference_and_a_refund_goes_back(): void
    {
        $invitation = $this->draft(['package' => Packages::BASIC, 'status' => 'active']);

        $this->actingAs($this->owner)->withoutVite()->get(route('client.diy.publish', $invitation))
            ->assertOk()
            ->assertSee('Sube tu invitación a Premium')
            ->assertSee(Money::format(26))
            ->assertSee('la diferencia con tu nivel Básico')
            ->assertSee('Pagar y subir a Premium');

        $this->actingAs($this->owner)->post(route('client.diy.checkout', $invitation), ['method' => 'card']);
        $order = PaymentOrder::sole();
        $this->assertSame('26.00', $order->amount);
        $this->assertSame(Packages::BASIC, $order->meta['from_tier']);

        $this->lsSend($this->lsOrderCreated($order->public_id, '9200', 'invitacion', 2600))->assertOk();
        $this->assertSame(Packages::PREMIUM, $invitation->fresh()->package);

        // Devolución completa: vuelve a Básico y sigue publicada
        $this->lsSend($this->lsEvent('order_refunded', 'orders', '9200', ['refunded' => true, 'refunded_amount' => 2600, 'status' => 'refunded']))->assertOk();

        $this->assertSame(Packages::BASIC, $invitation->fresh()->package);
        $this->assertSame('active', $invitation->fresh()->status);
        $this->assertSame(PaymentOrder::REFUNDED, $order->fresh()->status);
        $this->assertSame(0, InvitationPayment::count());
    }

    public function test_a_refunded_first_payment_turns_it_back_into_a_draft(): void
    {
        $draft = $this->draft();
        $this->actingAs($this->owner)->post(route('client.diy.checkout', $draft), ['method' => 'card']);
        $this->lsSend($this->lsOrderCreated(PaymentOrder::sole()->public_id, '9300', 'invitacion', 3500))->assertOk();

        $this->lsSend($this->lsEvent('order_refunded', 'orders', '9300', ['refunded' => true, 'refunded_amount' => 3500, 'status' => 'refunded']))->assertOk();

        $this->assertNull($draft->fresh()->package);
        $this->assertSame('inactive', $draft->fresh()->status);
    }

    public function test_the_qr_charges_bolivianos_and_publishes_when_libelula_confirms(): void
    {
        config(['payments.methods.card' => 'ninguna']);
        $this->configureLibelula();
        $this->fakeLibelula();
        $draft = $this->draft();

        $this->actingAs($this->owner)->withoutVite()->get(route('client.diy.publish', $draft))
            ->assertOk()
            ->assertSee('QR de Bolivia, Tigo Money o tarjeta boliviana')
            ->assertSee('Bs 244');

        $this->actingAs($this->owner)->post(route('client.diy.checkout', $draft), ['method' => 'qr']);
        $order = PaymentOrder::sole();
        $this->assertSame('244.00', $order->amount);
        $this->assertSame('BOB', $order->currency);

        Http::assertSent(fn (HttpRequest $request) => str_ends_with($request->url(), '/rest/deuda/registrar')
            && $request['url_retorno'] === route('client.diy.order', $order->public_id)
            && str_starts_with($request['descripcion'], 'Invitación Premium · «XV de prueba»'));

        $this->actingAs($this->owner)->withoutVite()->get(route('client.diy.order', $order->public_id))
            ->assertOk()
            ->assertSee('Escanea el QR para pagar');

        $this->libelulaReceives($order->public_id, '244.00');
        $this->artisan('bida:verificar-pagos')->assertSuccessful();

        $this->assertSame('active', $draft->fresh()->status);
        $this->assertSame(Packages::PREMIUM, $draft->fresh()->package);
    }

    public function test_without_online_payments_it_is_asked_on_whatsapp(): void
    {
        config(['payments.enabled' => false]);
        $draft = $this->draft();

        $this->actingAs($this->owner)->withoutVite()->get(route('client.diy.publish', $draft))
            ->assertOk()
            ->assertSee('Pedir Premium por WhatsApp');

        $this->actingAs($this->owner)
            ->post(route('client.diy.checkout', $draft), ['method' => 'card'])
            ->assertSessionHasErrors(['publish' => 'Esa forma de pago no está disponible. Elige otra o escríbenos.']);
        $this->assertSame(0, PaymentOrder::count());
    }

    public function test_a_difference_of_zero_upgrades_without_charging(): void
    {
        config(['bida.diy.prices.basico.price' => 19]);
        $invitation = $this->draft(['package' => Packages::BASIC, 'status' => 'active'], ['galeria' => true, 'rsvp' => false, 'encuestas' => false, 'playlist' => false, 'fotomural' => false, 'post_evento' => false]);

        $this->actingAs($this->owner)
            ->post(route('client.diy.checkout', $invitation))
            ->assertRedirect(route('client.dashboard'));

        $this->assertSame(Packages::STANDARD, $invitation->fresh()->package);
        $this->assertSame(0, PaymentOrder::count());
    }

    public function test_only_the_owner_pays_and_sees_the_order(): void
    {
        $draft = $this->draft();
        $this->actingAs($this->owner)->post(route('client.diy.checkout', $draft), ['method' => 'card']);
        $order = PaymentOrder::sole();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->post(route('client.diy.checkout', $draft), ['method' => 'card'])->assertRedirect()->assertSessionHas('access_notice');
        $this->actingAs($stranger)->get(route('client.diy.order', $order->public_id))->assertNotFound();
    }

    public function test_season_designs_and_cards(): void
    {
        // Una temporada con precio propio: sus diseños se publican con todo por ese precio
        config([
            'bida.seasons.halloween.diy_price' => 15,
            'bida.seasons.halloween.active' => true,
            'bida.seasons.halloween.ends_at' => now()->addWeek()->toDateTimeString(),
        ]);
        $halloween = $this->draft(['template' => 'invitations.templates.halloween-expediente']);
        $modules = app(InvitationModuleService::class)->resolveModules($halloween);

        $this->assertSame(Packages::PREMIUM, DiyInvitations::publishTier($halloween, $modules));
        $this->assertSame(15, InvitationPrice::due($halloween, Packages::PREMIUM));

        // Una tarjeta no tiene versión gratuita: por lo menos Básico
        $card = $this->createInvitation(['user_id' => $this->owner->id, 'is_diy' => true, 'status' => 'inactive', 'template' => 'invitations.templates.tarjeta-amor']);
        $this->assertFalse(DiyInvitations::allowsFree($card));
        $this->assertSame(Packages::BASIC, DiyInvitations::publishTier($card, ['config' => ['modulos' => []]]));
    }

    public function test_the_admin_sets_the_level_prices_in_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->withoutVite()->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Hazlo tú: publicar con cada nivel')
            ->assertSee('name="diy_prices[estandar][price]"', false)
            ->assertSee('name="seasons[halloween][diy_price]"', false);
    }

    private function draft(array $attributes = [], array $flags = []): Invitation
    {
        $invitation = $this->createInvitation(array_merge([
            'user_id' => $this->owner->id,
            'is_diy' => true,
            'package' => null,
            'status' => 'inactive',
            'title' => 'XV de prueba',
        ], $attributes));

        $modules = XvSofiaModuleData::all();
        $modules['config']['modulos'] = array_merge($modules['config']['modulos'], $flags);
        // Los módulos de ejemplo traen su plantilla: la de la invitación manda
        $modules['config']['template'] = $invitation->template;
        app(InvitationModuleService::class)->syncAllModules($invitation, $modules);

        return $invitation->fresh();
    }
}
