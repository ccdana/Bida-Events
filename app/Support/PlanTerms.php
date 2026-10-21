<?php

namespace App\Support;

use App\Payments\PaymentGateways;

/**
 * Lo que se dice de cambiar de plan, cancelar y renovar. Se escribe una sola vez para que /precios, /unete y los
 * Términos no se contradigan, y se adapta a lo que el sitio puede hacer hoy:
 *
 *  - Con tarjeta (pago en línea activo) la suscripción se renueva sola y el cliente cambia de plan o cancela
 *    en el portal de pagos, desde «Mi plan».
 *  - Con QR no hay cobro automático: cada pago cubre meses, se cambia de plan al pagar el siguiente y se deja
 *    el plan simplemente no pagándolo.
 *  - Sin pago en línea, todo se coordina por WhatsApp o correo.
 *
 * No se afirma nada sobre cuándo ni cuánto cobra la pasarela al cambiar de plan: eso lo decide su portal.
 */
final class PlanTerms
{
    /** @return array{card: bool, qr: bool} los métodos de pago en línea que hoy se ofrecen */
    public static function methods(): array
    {
        $available = app(PaymentGateways::class)->availableMethods();

        return ['card' => isset($available['card']), 'qr' => isset($available['qr'])];
    }

    public static function online(): bool
    {
        return in_array(true, self::methods(), true);
    }

    public static function question(): string
    {
        return __('¿Puedo cambiar de plan o cancelarlo?');
    }

    /** La respuesta de las preguntas frecuentes de /precios y /unete. */
    public static function answer(): string
    {
        $methods = self::methods();

        if (! self::online()) {
            return __('Sí. Escríbenos por WhatsApp o al correo :email: el cambio se aplica con tu siguiente pago y, si quieres dejar el plan, solo no lo renuevas.', ['email' => config('bida.email')])
                .' '.__('Lo que ya publicaste sigue en línea.');
        }

        return implode(' ', array_filter([
            __('Sí.'),
            $methods['card'] ? __('Con tarjeta lo haces tú desde «Mi plan»: cambias de plan o cancelas en el portal de pagos.') : null,
            $methods['qr'] ? __('Con QR no hay cobro automático: eliges otro plan en tu próximo pago y, si no pagas el siguiente período, el plan termina.') : null,
            __('Si tu plan lo coordinamos por WhatsApp, escríbenos y lo cambiamos.'),
            __('Lo que ya publicaste sigue en línea.'),
        ]));
    }

    /** El cierre de «¿Cuánto cuesta y cómo se paga?» en /unete. */
    public static function payment(): string
    {
        $methods = self::methods();

        return match (true) {
            $methods['card'] && $methods['qr'] => __('Pagas con QR de cualquier banco de Bolivia o con tarjeta; con tarjeta se renueva solo y lo cancelas cuando quieras.'),
            $methods['card'] => __('Pagas con tarjeta; se renueva solo y lo cancelas cuando quieras.'),
            $methods['qr'] => __('Pagas con QR de cualquier banco de Bolivia, sin renovación automática.'),
            default => __('El pago lo coordinamos contigo por WhatsApp.'),
        };
    }

    /**
     * Lo que dicen los Términos sobre cómo se renueva el plan: un texto por renglón.
     *
     * @return list<string>
     */
    public static function renewal(): array
    {
        return array_values(array_filter([
            __('El plan se paga por mes o por 3 o 12 meses. Cada pago extiende la suscripción por ese tiempo desde la fecha de vencimiento vigente, o desde el día del pago si ya había vencido.')
                .(self::methods()['card'] ? ' '.__('Con tarjeta, la suscripción se renueva sola en el mismo ciclo hasta que la canceles.') : ''),
        ]));
    }

    /**
     * Lo que dicen los Términos sobre cambiar de plan y cancelar: un texto por renglón.
     *
     * @return list<string>
     */
    public static function termsChangeAndCancel(): array
    {
        $methods = self::methods();

        if (! self::online()) {
            return [
                __('Para cambiar de plan o dejarlo, escríbenos a :email. El cambio se aplica con tu siguiente pago y, al dejarlo, conservas el acceso hasta el final del período ya pagado.', ['email' => config('bida.email')]),
                __('No devolvemos meses parciales ni períodos ya pagados.'),
            ];
        }

        return array_values(array_filter([
            $methods['card'] ? __('Con tarjeta puedes cambiar de plan, cambiar de tarjeta o cancelar cuando quieras desde «Mi plan». Al cancelar no se hacen más cobros y conservas el acceso hasta el final del período ya pagado.') : null,
            $methods['qr'] ? __('Con QR no hay cobro automático: para cambiar de plan eliges otro en tu próximo pago y, para dejar el plan, no pagas el siguiente período. Conservas el acceso hasta el final del período ya pagado.') : null,
            __('No devolvemos meses parciales ni períodos ya pagados.'),
        ]));
    }
}
