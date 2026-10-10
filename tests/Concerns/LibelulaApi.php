<?php

namespace Tests\Concerns;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;

/**
 * La API de Libélula simulada con la forma de su guía de integración (v2.7.1): registrar deuda,
 * consultar pagos y la imagen del QR Simple.
 */
trait LibelulaApi
{
    protected const LIBELULA_TRANSACTION = 'dd5a5391-34bf-4cb4-ad13-be6621a54979';

    /** Pagos que Libélula dice haber recibido (lo que devuelve consultar_pagos). */
    protected array $libelulaPayments = [];

    protected function configureLibelula(): void
    {
        config([
            'payments.enabled' => true,
            'payments.libelula.appkey' => '588e5e10-d794-4910-91d2-b3952b54df4d',
            'payments.libelula.base_url' => 'https://api.todotix.test',
        ]);
    }

    /** $withQr: el comercio tiene QR Simple habilitado (si no, se paga en la página de Libélula). */
    protected function fakeLibelula(bool $withQr = true, bool $registerFails = false): void
    {
        Http::swap(new HttpFactory);
        Http::fake([
            'api.todotix.test/rest/deuda/registrar' => $registerFails
                ? Http::response(['error' => 1, 'mensaje' => 'appkey inválido'])
                : Http::response([
                    'error' => 0,
                    'mensaje' => 'La deuda se registró con éxito',
                    'id_transaccion' => self::LIBELULA_TRANSACTION,
                    'url_pasarela_pagos' => 'https://pagos.libelula.bo/pagar/'.self::LIBELULA_TRANSACTION,
                    'qr_simple_url' => $withQr ? 'https://pagos.libelula.bo/qr/'.self::LIBELULA_TRANSACTION.'.png' : null,
                ]),
            'pagos.libelula.bo/qr/*' => Http::response("\x89PNG\r\n\x1a\nqr-de-prueba", 200, ['Content-Type' => 'image/png']),
            'api.todotix.test/rest/deuda/consultar_pagos' => fn () => Http::response([
                'error' => 0,
                'mensaje' => count($this->libelulaPayments).' pagos encontrados.',
                'id_transaccion' => null,
                'datos' => $this->libelulaPayments,
            ]),
        ]);
    }

    /** Libélula recibió el pago de una orden. */
    protected function libelulaReceives(string $orderPublicId, string $amount, string $transactionId = self::LIBELULA_TRANSACTION): void
    {
        $this->libelulaPayments[] = [
            'descripcion' => 'Plan',
            'id_transaccion' => $transactionId,
            'identificador' => $orderPublicId,
            'email_cliente' => 'comprador@correo.com',
            'fecha_pago' => now()->format('Y-m-d H:i:s'),
            'monto_pagado' => (float) $amount,
            'moneda' => 'BOB',
            'forma_pago' => 'QR SIMPLE',
        ];
    }
}
