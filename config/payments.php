<?php

use App\Payments\Gateways\LemonSqueezyGateway;

/*
|--------------------------------------------------------------------------
| Pagos en línea
|--------------------------------------------------------------------------
|
| Cada forma de pagar («método») usa una pasarela: la tarjeta internacional y el QR de Bolivia. Cada
| pasarela es una clase que cumple App\Payments\Contracts\PaymentGateway («drivers»): cambiar de
| pasarela es escribir su clase y apuntar el método a ella, sin tocar el resto.
|
| El registro a mano del administrador (Revendedores › Registrar pago) sigue funcionando siempre,
| esté o no encendido el cobro en línea.
|
*/

return [

    // Con false no se ofrece pagar en línea (los botones de compra siguen yendo a WhatsApp)
    'enabled' => (bool) env('PAYMENTS_ENABLED', false),

    // Pasarela de cada método de pago (clave de «drivers»)
    'methods' => [
        'card' => env('PAYMENTS_CARD_PROVIDER', 'lemonsqueezy'),
        'qr' => env('PAYMENTS_QR_PROVIDER', 'cucu'),
    ],

    // Clase de cada pasarela. CUCU (QR) se suma al integrarla.
    'drivers' => [
        'lemonsqueezy' => LemonSqueezyGateway::class,
    ],

    /*
    | Días después del vencimiento en que el revendedor todavía puede crear y editar: el margen para
    | que se reintente un cobro con tarjeta o llegue un pago. Pasados, queda bloqueado hasta pagar
    | (sus invitaciones publicadas siguen en línea). 0 = se bloquea el mismo día del vencimiento.
    */
    'grace_days' => (int) env('PAYMENTS_GRACE_DAYS', 3),

    /*
    | Lemon Squeezy (tarjeta, internacional). Cobra como vendedor oficial y renueva solo cada mes.
    | - api_key: Settings › API (una de prueba mientras la tienda está en modo de prueba).
    | - store_id: Settings › Stores, el número de la tienda.
    | - signing_secret: el «Signing secret» del webhook (Settings › Webhooks), apuntado a
    |   APP_URL/webhooks/lemonsqueezy.
    | - variants: un producto de suscripción mensual con una variante por plan, cada una con el
    |   mismo precio que el plan tiene en el sitio (Ajustes). La variante dice qué plan se compró.
    */
    'lemonsqueezy' => [
        'api_key' => env('LEMONSQUEEZY_API_KEY'),
        'store_id' => env('LEMONSQUEEZY_STORE_ID'),
        'signing_secret' => env('LEMONSQUEEZY_SIGNING_SECRET'),
        // Crea los cobros en modo de prueba (tarjetas de prueba, sin dinero real)
        'test_mode' => (bool) env('LEMONSQUEEZY_TEST_MODE', false),
        'variants' => [
            'inicial' => env('LEMONSQUEEZY_VARIANT_INICIAL'),
            'aliado' => env('LEMONSQUEEZY_VARIANT_ALIADO'),
            'emprendedor' => env('LEMONSQUEEZY_VARIANT_EMPRENDEDOR'),
            'agencia' => env('LEMONSQUEEZY_VARIANT_AGENCIA'),
        ],
    ],

    // Cómo se nombra el origen de cada pago en el historial
    'labels' => [
        'manual' => 'registrado a mano',
        'lemonsqueezy' => 'tarjeta',
        'cucu' => 'QR',
    ],

];
