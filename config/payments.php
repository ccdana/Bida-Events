<?php

use App\Payments\Gateways\LemonSqueezyGateway;
use App\Payments\Gateways\LibelulaGateway;

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
        'qr' => env('PAYMENTS_QR_PROVIDER', 'libelula'),
    ],

    // Clase de cada pasarela
    'drivers' => [
        'lemonsqueezy' => LemonSqueezyGateway::class,
        'libelula' => LibelulaGateway::class,
    ],

    /*
    | Pagar un plan por 3 o 12 meses de una vez: meses => descuento en % sobre el precio mensual de
    | hoy (se cambia en Ajustes). Con QR se paga por adelantado (hay que escanear en cada pago, así
    | que el anual ahorra pasos y es el que más conviene); con tarjeta se renueva sola cada 3 o 12
    | meses si Lemon Squeezy tiene la variante de ese ciclo («variants», abajo).
    |
    | (Supuesto) 10 % el trimestral y 20 % el anual, pendientes de confirmar con el dueño.
    */
    'prepay' => [
        1 => 0,
        3 => (int) env('PAYMENTS_PREPAY_3_DISCOUNT', 10),
        12 => (int) env('PAYMENTS_PREPAY_12_DISCOUNT', 20),
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
    | - variants: un producto de suscripción con una variante por plan y ciclo: «inicial» (mensual),
    |   «inicial:3» (cada 3 meses) e «inicial:12» (anual), cada una con el mismo precio que el sitio
    |   cobra por ese plan y ese ciclo (Ajustes: el precio del plan y el descuento por adelantado). La
    |   variante dice qué plan y qué ciclo se compró. Un ciclo sin variante no se ofrece con tarjeta.
    | - variants.invitacion: un producto de pago único («Invitación») con una sola variante. Cada
    |   checkout lleva su precio (custom_price): el nivel de «Hazlo tú» o la diferencia al subir de nivel.
    */
    'lemonsqueezy' => [
        'api_key' => env('LEMONSQUEEZY_API_KEY'),
        'store_id' => env('LEMONSQUEEZY_STORE_ID'),
        'signing_secret' => env('LEMONSQUEEZY_SIGNING_SECRET'),
        // Crea los cobros en modo de prueba (tarjetas de prueba, sin dinero real)
        'test_mode' => (bool) env('LEMONSQUEEZY_TEST_MODE', false),
        'variants' => [
            'inicial' => env('LEMONSQUEEZY_VARIANT_INICIAL'),
            'inicial:3' => env('LEMONSQUEEZY_VARIANT_INICIAL_TRIMESTRAL'),
            'inicial:12' => env('LEMONSQUEEZY_VARIANT_INICIAL_ANUAL'),
            'emprendedor' => env('LEMONSQUEEZY_VARIANT_EMPRENDEDOR'),
            'emprendedor:3' => env('LEMONSQUEEZY_VARIANT_EMPRENDEDOR_TRIMESTRAL'),
            'emprendedor:12' => env('LEMONSQUEEZY_VARIANT_EMPRENDEDOR_ANUAL'),
            'agencia' => env('LEMONSQUEEZY_VARIANT_AGENCIA'),
            'agencia:3' => env('LEMONSQUEEZY_VARIANT_AGENCIA_TRIMESTRAL'),
            'agencia:12' => env('LEMONSQUEEZY_VARIANT_AGENCIA_ANUAL'),
            // «Hazlo tú»: pago único, el precio va en cada checkout
            'invitacion' => env('LEMONSQUEEZY_VARIANT_INVITACION'),
        ],
    ],

    /*
    | Libélula (QR de cualquier banco de Bolivia, Tigo Money y tarjetas bolivianas). Cobra una
    | comisión por cada pago, sin mensualidad. Cobra en bolivianos (precio del plan × «bob_rate» de
    | config/bida.php). Guía de integración v2.7.1 (2020), confirmar con Libélula:
    | - appkey: la clave que Libélula da a cada comercio.
    | - base_url: producción https://api.todotix.com; pruebas http://www.todotix.com:10888.
    | - Su aviso de pago (GET a /webhooks/libelula) no viene firmado: no se le cree por sí solo, se
    |   confirma cada pago consultando a Libélula con el appkey (consultar_pagos).
    */
    'libelula' => [
        'appkey' => env('LIBELULA_APPKEY'),
        'base_url' => env('LIBELULA_BASE_URL', 'https://api.todotix.com'),
        // Libélula emite la factura del pago (requiere tener la facturación configurada con ellos)
        'invoice' => (bool) env('LIBELULA_EMITE_FACTURA', false),
    ],

    // Cómo se nombra el origen de cada pago en el historial
    'labels' => [
        'manual' => 'registrado a mano',
        'lemonsqueezy' => 'tarjeta',
        'libelula' => 'QR',
    ],

];
