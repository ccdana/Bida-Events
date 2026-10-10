<?php

namespace Tests\Feature;

use App\Models\PaymentOrder;
use App\Models\User;
use App\Payments\GatewayEvent;
use App\Payments\Gateways\LibelulaGateway;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\LibelulaApi;
use Tests\TestCase;

/**
 * El driver de Libélula por dentro: el cobro que registra y cómo confirma cada pago preguntándole a
 * Libélula, porque su aviso no viene firmado.
 */
class LibelulaGatewayTest extends TestCase
{
    use LibelulaApi, RefreshDatabase;

    private LibelulaGateway $gateway;

    private PaymentOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-15 12:00:00', 'America/La_Paz'));
        $this->configureLibelula();
        $this->fakeLibelula();
        $this->gateway = app(LibelulaGateway::class);

        $user = User::factory()->create(['name' => 'Carla Mendoza Rojas', 'email' => 'carla@correo.com']);
        $this->order = PaymentOrder::forSubscription($user, 'aliado', 3, '292.00', 'BOB', 'libelula');
    }

    public function test_it_charges_in_bolivianos_without_renewing_by_itself(): void
    {
        $this->assertTrue($this->gateway->isConfigured());
        $this->assertSame('BOB', $this->gateway->currency());
        $this->assertFalse($this->gateway->isRecurring());

        config(['payments.libelula.appkey' => null]);
        $this->assertFalse($this->gateway->isConfigured());
    }

    public function test_the_charge_is_registered_with_our_order_and_shows_its_qr(): void
    {
        $checkout = $this->gateway->createCheckout($this->order);

        // El QR se muestra en nuestra página (guardado como imagen); la de Libélula queda como alternativa
        $this->assertNull($checkout->redirectUrl);
        $this->assertStringStartsWith('data:image/png;base64,', $checkout->qrImage);
        $this->assertSame(self::LIBELULA_TRANSACTION, $checkout->providerRef);
        $this->assertSame('https://pagos.libelula.bo/pagar/'.self::LIBELULA_TRANSACTION, $checkout->paymentPageUrl);
        $this->assertSame('2026-10-16', $checkout->expiresAt->toDateString());

        Http::assertSent(function (HttpRequest $request) {
            if ($request->url() !== 'https://api.todotix.test/rest/deuda/registrar') {
                return false;
            }
            $data = $request->data();

            return $data['appkey'] === '588e5e10-d794-4910-91d2-b3952b54df4d'
                && $data['email_cliente'] === 'carla@correo.com'
                && $data['identificador'] === $this->order->public_id
                && $data['callback_url'] === route('webhooks.payments', ['provider' => 'libelula', 'orden' => $this->order->public_id])
                && $data['url_retorno'] === route('client.subscription.order', $this->order->public_id)
                && $data['moneda'] === 'BOB'
                && $data['nombre_cliente'] === 'Carla'
                && $data['apellido_cliente'] === 'Mendoza Rojas'
                && $data['fecha_vencimiento'] === '2026-10-16'
                && $data['lineas_detalle_deuda'][0]['costo_unitario'] === 292.0
                && str_contains($data['lineas_detalle_deuda'][0]['concepto'], '3 meses');
        });
    }

    public function test_without_qr_simple_the_buyer_goes_to_the_libelula_page(): void
    {
        $this->fakeLibelula(withQr: false);

        $checkout = $this->gateway->createCheckout($this->order);

        $this->assertSame('https://pagos.libelula.bo/pagar/'.self::LIBELULA_TRANSACTION, $checkout->redirectUrl);
        $this->assertNull($checkout->qrImage);
    }

    public function test_a_rejected_registration_is_an_error(): void
    {
        $this->fakeLibelula(registerFails: true);

        $this->expectException(DomainException::class);
        $this->gateway->createCheckout($this->order);
    }

    /** El aviso de Libélula llega por GET, sin firma: solo cuenta si Libélula confirma el pago. */
    private function notice(array $query): ?GatewayEvent
    {
        return $this->gateway->parseWebhook(Request::create('/webhooks/libelula', 'GET', $query));
    }

    public function test_a_notice_counts_only_if_libelula_confirms_the_payment(): void
    {
        $this->order->forceFill(['provider_ref' => self::LIBELULA_TRANSACTION])->save();
        $query = ['orden' => $this->order->public_id, 'transaction_id' => self::LIBELULA_TRANSACTION];

        // Alguien llama a la URL sin que se haya pagado: no pasa nada
        $this->assertNull($this->notice($query));

        $this->libelulaReceives($this->order->public_id, '292');
        $event = $this->notice($query);

        $this->assertSame(GatewayEvent::ORDER_PAID, $event->type);
        $this->assertSame($this->order->public_id, $event->orderId);
        $this->assertSame(self::LIBELULA_TRANSACTION, $event->externalId);
        $this->assertSame('292.00', $event->amount);
        $this->assertSame('BOB', $event->currency);
        $this->assertSame('pago:'.self::LIBELULA_TRANSACTION, $event->id);

        Http::assertSent(fn (HttpRequest $request) => str_ends_with($request->url(), '/rest/deuda/consultar_pagos')
            && $request->data()['appkey'] === '588e5e10-d794-4910-91d2-b3952b54df4d'
            && $request->data()['fecha_inicial'] === '2026-10-14 12:00:00');
    }

    public function test_a_notice_for_another_order_or_transaction_is_ignored(): void
    {
        $this->order->forceFill(['provider_ref' => self::LIBELULA_TRANSACTION])->save();
        $this->libelulaReceives($this->order->public_id, '292');

        $this->assertNull($this->notice(['orden' => $this->order->public_id, 'transaction_id' => 'otra-transaccion']));
        $this->assertNull($this->notice(['orden' => 'no-es-uuid', 'transaction_id' => self::LIBELULA_TRANSACTION]));
        $this->assertNull($this->notice(['transaction_id' => self::LIBELULA_TRANSACTION]));
    }
}
