<?php

namespace App\Payments\Gateways;

use App\Models\PaymentOrder;
use App\Payments\Checkout;
use App\Payments\Contracts\PaymentGateway;
use App\Payments\GatewayEvent;
use DomainException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Pago en Bolivia con Libélula: QR de cualquier banco, Tigo Money y tarjetas bolivianas. Cobra una
 * comisión por pago, sin mensualidad. Cada compra es una «deuda» en Libélula, en bolivianos, que
 * cubre los meses elegidos (no se renueva sola).
 *
 * Según su guía de integración (v2.7.1):
 * - REGISTRAR DEUDA (POST /rest/deuda/registrar): devuelve id_transaccion, la página de pago
 *   (url_pasarela_pagos) y, si el comercio tiene QR Simple, la imagen del QR (qr_simple_url).
 * - PAGO EXITOSO: Libélula hace un GET a nuestra callback_url agregando ?transaction_id=…
 *   (también en segundo plano, horas después, para pagos por banca por internet).
 * - CONSULTAR PAGOS (POST /rest/deuda/consultar_pagos): los pagos de un rango de fechas.
 *
 * El aviso de PAGO EXITOSO no viene firmado: cualquiera podría llamar a esa URL. Por eso no se le
 * cree: es un timbre que hace que preguntemos a Libélula (con nuestro appkey) si ese pago existe y
 * por cuánto. Lo mismo hace bida:verificar-pagos cada pocos minutos, por si el aviso no llega.
 */
class LibelulaGateway implements PaymentGateway
{
    public function isConfigured(): bool
    {
        return filled($this->config('appkey'));
    }

    public function supportsPlan(string $plan): bool
    {
        return true;
    }

    public function currency(): string
    {
        return 'BOB';
    }

    public function isRecurring(): bool
    {
        return false;
    }

    public function createCheckout(PaymentOrder $order): Checkout
    {
        $user = $order->user ?? throw new DomainException('La orden no tiene cuenta.');
        $planName = config("bida.reseller_plans.{$order->plan}.name", $order->plan);
        $concept = "Plan {$planName} de «Únete» · ".($order->months === 1 ? '1 mes' : "{$order->months} meses");
        [$firstName, $lastName] = array_pad(explode(' ', trim($user->name), 2), 2, '');
        // Vence a la medianoche de mañana (hora de Bolivia): da tiempo a pagar por banca por internet
        $expiresAt = now()->timezone(config('app.timezone'))->addDay()->endOfDay();

        $response = $this->api()->post('/rest/deuda/registrar', [
            'appkey' => $this->config('appkey'),
            'email_cliente' => $user->email,
            'identificador' => $order->public_id,
            // Libélula agrega ?transaction_id=… a esta URL cuando se paga
            'callback_url' => route('webhooks.payments', ['provider' => 'libelula', 'orden' => $order->public_id]),
            'url_retorno' => route('client.subscription.order', $order->public_id),
            'descripcion' => "{$concept} · {$order->reference_code}",
            'nombre_cliente' => $firstName,
            'apellido_cliente' => $lastName,
            'fecha_vencimiento' => $expiresAt->toDateString(),
            'moneda' => $order->currency,
            'emite_factura' => (bool) $this->config('invoice'),
            'lineas_detalle_deuda' => [
                ['concepto' => $concept, 'cantidad' => 1, 'costo_unitario' => (float) $order->amount],
            ],
        ])->throw();

        if ($this->failed($response->json()) || blank($response->json('id_transaccion'))) {
            throw new DomainException('Libélula no registró el cobro: '.Str::limit((string) $response->json('mensaje'), 200));
        }

        return new Checkout(
            // Sin QR Simple habilitado, el comprador paga en la página de Libélula
            redirectUrl: $response->json('qr_simple_url') ? null : $response->json('url_pasarela_pagos'),
            qrImage: $this->qrImage($response->json('qr_simple_url')),
            providerRef: (string) $response->json('id_transaccion'),
            expiresAt: $expiresAt,
            paymentPageUrl: $response->json('url_pasarela_pagos'),
        );
    }

    /**
     * El aviso trae nuestra orden (en la callback_url) y el id de Libélula. Solo se convierte en
     * «pagado» si Libélula confirma ese pago al consultarle; si no, no pasa nada.
     */
    public function parseWebhook(Request $request): ?GatewayEvent
    {
        $transactionId = trim((string) ($request->query('transaction_id') ?? $request->input('transaction_id', '')));
        $orderId = (string) $request->query('orden', '');

        if ($transactionId === '' || ! Str::isUuid($orderId)) {
            return null;
        }

        $order = PaymentOrder::where('public_id', $orderId)
            ->where('provider', 'libelula')
            ->where('provider_ref', $transactionId)
            ->first();

        return $order ? $this->fetchStatus($order) : null;
    }

    /** ¿Libélula recibió el pago de esta orden? Se busca entre los pagos desde que se creó. */
    public function fetchStatus(PaymentOrder $order): ?GatewayEvent
    {
        if (! $order->provider_ref) {
            return null;
        }

        $payment = collect($this->paymentsBetween($order->created_at->copy()->subDay(), now()->addDay()))
            ->first(fn (array $payment) => (string) ($payment['id_transaccion'] ?? '') === $order->provider_ref
                || (string) ($payment['identificador'] ?? '') === $order->public_id);

        if (! $payment) {
            return null;
        }

        return new GatewayEvent(
            id: 'pago:'.$order->provider_ref,
            type: GatewayEvent::ORDER_PAID,
            orderId: $order->public_id,
            externalId: $order->provider_ref,
            // Se compara con la orden al centavo: Libélula cobra exactamente lo registrado
            amount: isset($payment['monto_pagado']) ? number_format((float) $payment['monto_pagado'], 2, '.', '') : null,
            currency: strtoupper((string) ($payment['moneda'] ?? $order->currency)),
            extra: array_filter([
                'forma_pago' => $payment['forma_pago'] ?? null,
                'fecha_pago' => $payment['fecha_pago'] ?? null,
            ]),
        );
    }

    /** Pagos recibidos en un rango (hora de Bolivia). Si Libélula no responde, ninguno: se reintenta luego. */
    private function paymentsBetween($from, $to): array
    {
        $format = fn ($date) => $date->copy()->timezone(config('app.timezone'))->format('Y-m-d H:i:s');

        try {
            $response = $this->api()->post('/rest/deuda/consultar_pagos', [
                'appkey' => $this->config('appkey'),
                'fecha_inicial' => $format($from),
                'fecha_final' => $format($to),
            ]);
        } catch (Throwable $e) {
            report($e);

            return [];
        }

        if (! $response->successful() || $this->failed($response->json())) {
            Log::channel('operations')->warning('Libélula no respondió la consulta de pagos', [
                'status' => $response->status(),
                'mensaje' => Str::limit((string) $response->json('mensaje'), 200),
            ]);

            return [];
        }

        return array_values(array_filter((array) $response->json('datos', []), 'is_array'));
    }

    /**
     * La imagen del QR se guarda en la orden (data: URI): se ve en la página de la orden sin depender
     * de que el servidor de Libélula sirva la imagen por HTTPS ni de la política de contenido.
     */
    private function qrImage(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        try {
            $image = Http::timeout(10)->get($url);
        } catch (Throwable) {
            return $url;
        }

        $type = (string) $image->header('Content-Type');

        return $image->successful() && str_starts_with($type, 'image/') && strlen($image->body()) < 200_000
            ? 'data:'.$type.';base64,'.base64_encode($image->body())
            : $url;
    }

    /** «error» llega como 0/1 o false/true. */
    private function failed(mixed $json): bool
    {
        return ! is_array($json) || filter_var($json['error'] ?? true, FILTER_VALIDATE_BOOL);
    }

    private function api(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) $this->config('base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }

    private function config(string $key): mixed
    {
        return config("payments.libelula.{$key}");
    }
}
