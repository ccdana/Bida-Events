<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Payments\PaymentGateways;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Diagnóstico de «Administrar mi suscripción»: le pide a la pasarela el enlace al portal de un cliente y dice cómo
 * viene (de qué dirección, si trae firma y hasta cuándo vale), sin mostrar la firma. Un enlace firmado entra al portal
 * sin pedir iniciar sesión; uno sin firma (la dirección /billing a secas) manda al ingreso por correo de Lemon Squeezy.
 */
class CheckBillingPortal extends Command
{
    protected $signature = 'bida:portal-de-pagos {usuario : El usuario o el correo del cliente con tarjeta}';

    protected $description = 'Revisa el enlace al portal de pagos que la pasarela le da a un cliente (sin mostrar la firma)';

    public function handle(PaymentGateways $gateways): int
    {
        $login = mb_strtolower(trim((string) $this->argument('usuario')));
        $user = User::query()->where(str_contains($login, '@') ? 'email' : 'username', $login)->first();

        if (! $user) {
            $this->components->error("No hay un cliente con «{$login}».");

            return self::FAILURE;
        }

        $this->components->twoColumnDetail('Cliente', $user->username.' · plan '.($user->reseller_plan ?? 'ninguno'));
        $this->components->twoColumnDetail('Pasarela', (string) ($user->payment_provider ?? 'ninguna'));
        $this->components->twoColumnDetail('Suscripción', $user->provider_subscription_id ? '…'.substr((string) $user->provider_subscription_id, -4) : 'ninguna');
        $this->components->twoColumnDetail('Modo de prueba', config('payments.lemonsqueezy.test_mode') ? 'sí (LEMONSQUEEZY_TEST_MODE)' : 'no');

        $manager = $user->hasRecurringSubscription() ? $gateways->subscriptionManager($user->payment_provider) : null;

        if (! $manager) {
            $this->components->warn('Este cliente no tiene una suscripción con tarjeta que se renueve sola: «Administrar» no se le ofrece.');

            return self::SUCCESS;
        }

        try {
            $url = $manager->portalUrl($user);
        } catch (Throwable $exception) {
            $this->components->error('La pasarela no respondió: '.$exception->getMessage());

            return self::FAILURE;
        }

        if (! $url) {
            $this->components->error('La pasarela no devolvió un enlace al portal (la suscripción no existe en esta tienda o modo).');

            return self::FAILURE;
        }

        $parts = parse_url($url);
        parse_str((string) ($parts['query'] ?? ''), $query);
        $signed = filled($query['signature'] ?? null);
        $expires = filled($query['expires'] ?? null) ? Carbon::createFromTimestamp((int) $query['expires']) : null;

        $this->components->twoColumnDetail('Dirección', ($parts['host'] ?? '?').($parts['path'] ?? ''));
        $this->components->twoColumnDetail('Firmado', $signed ? 'sí' : 'no');
        $this->components->twoColumnDetail('Vale hasta', $expires ? $expires->toDateTimeString().($expires->isPast() ? ' (ya venció)' : '') : 'sin fecha');

        if (! $signed) {
            $this->components->warn('Sin firma, Lemon Squeezy pide iniciar sesión con un código al correo de la compra.');

            return self::FAILURE;
        }

        $this->components->info('El enlace viene firmado: debería entrar al portal sin pedir iniciar sesión. Si aun así pide entrar, ábrelo en el mismo navegador donde pagaste o escribe a Lemon Squeezy: puede ser cosa del modo de prueba de la tienda.');

        return self::SUCCESS;
    }
}
