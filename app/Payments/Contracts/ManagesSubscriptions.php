<?php

namespace App\Payments\Contracts;

use App\Models\User;

/**
 * Pasarelas que renuevan solas (la tarjeta): el cliente cambia de plan, de tarjeta o cancela desde
 * el portal de la pasarela, y la pasarela nos avisa cada cambio.
 */
interface ManagesSubscriptions
{
    /** Enlace al portal del cliente para su suscripción; null si no tiene una en esta pasarela. */
    public function portalUrl(User $user): ?string;

    /**
     * Los movimientos de su suscripción (cobros y devoluciones), del más nuevo al más viejo, para mostrarlos en «Mi cuenta»
     * sin que el cliente tenga que entrar al portal. Vacío si no tiene o si la pasarela no responde a tiempo.
     *
     * @return list<array{date: \Carbon\CarbonImmutable, amount: float, currency: string, status: 'paid'|'refunded'|'pending'|'void', reason: string, test: bool, url: ?string}>
     */
    public function invoices(User $user, int $limit = 8): array;

    /**
     * Cada cuántos meses puede renovarse sola la suscripción de este plan (1, 3 o 12): los ciclos que
     * la pasarela tiene configurados.
     *
     * @return list<int>
     */
    public function billingCycles(string $plan): array;
}
