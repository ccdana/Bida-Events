<?php

namespace App\Payments;

use Carbon\CarbonInterface;

/**
 * Cómo sigue el comprador después de abrir el cobro en la pasarela: ir a su página de pago (tarjeta)
 * o escanear un QR que mostramos nosotros, con la página de la pasarela como alternativa (Tigo Money,
 * tarjeta boliviana…).
 */
final class Checkout
{
    public function __construct(
        public readonly ?string $redirectUrl = null,
        // Imagen del QR (URL o data:image/png;base64,…)
        public readonly ?string $qrImage = null,
        // Id del cobro en la pasarela (se guarda en la orden)
        public readonly ?string $providerRef = null,
        public readonly ?CarbonInterface $expiresAt = null,
        // Junto al QR: la página de la pasarela con sus otras formas de pago
        public readonly ?string $paymentPageUrl = null,
    ) {}
}
