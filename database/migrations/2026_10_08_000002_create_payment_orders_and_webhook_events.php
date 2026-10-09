<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Base de los pagos en línea (tarjeta y QR).
 * - payment_orders: cada intento de compra. Se crea antes de ir a la pasarela y se marca pagada
 *   cuando la pasarela avisa; «public_id» es lo que viaja a la pasarela para reconocerla al volver.
 * - webhook_events: cada aviso recibido de una pasarela, una sola vez por (pasarela, id del aviso):
 *   así un aviso repetido no suma dos meses, y uno que falló se puede volver a procesar.
 * - subscription_payments: el historial de siempre, ahora con de dónde vino cada pago (a mano,
 *   tarjeta o QR) y el id del cobro en la pasarela (único, segunda barrera contra duplicados).
 * - users: la suscripción con tarjeta que se renueva sola vive en la pasarela; aquí se guarda cuál es.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            // Código corto y legible que ve el comprador (glosa del QR, soporte): BE-7K3P9Q
            $table->string('reference_code', 16)->unique();
            // Las órdenes son registros de dinero: si se borra la cuenta, la orden queda
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // Qué se compra: hoy solo «subscription» (plan de revendedor)
            $table->string('purpose', 20)->default('subscription');
            $table->string('plan', 50)->nullable();
            $table->unsignedTinyInteger('months')->default(1);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->string('provider', 30);
            // Id del cobro en la pasarela (checkout, QR…), cuando lo devuelve
            $table->string('provider_ref', 191)->nullable();
            // 'pending' | 'paid' | 'failed' | 'expired' | 'refunded'
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['provider', 'provider_ref']);
            $table->index(['status', 'expires_at']);
        });

        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30);
            $table->string('event_id', 191);
            $table->string('type', 100);
            // El aviso ya traducido por la pasarela (App\Payments\GatewayEvent) y el original, tal como llegó
            $table->json('data');
            $table->longText('payload')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'event_id']);
            $table->index('processed_at');
        });

        Schema::table('subscription_payments', function (Blueprint $table) {
            // 'manual' (lo registró el administrador), 'lemonsqueezy', 'cucu'…
            $table->string('provider', 30)->default('manual')->after('currency');
            $table->foreignId('payment_order_id')->nullable()->after('provider')->constrained('payment_orders')->nullOnDelete();
            $table->string('external_id', 191)->nullable()->after('payment_order_id');

            $table->unique(['provider', 'external_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('payment_provider', 30)->nullable()->after('subscription_renews_at');
            $table->string('provider_customer_id', 191)->nullable()->after('payment_provider');
            $table->string('provider_subscription_id', 191)->nullable()->after('provider_customer_id');
            // Canceló la renovación automática: sigue al día hasta subscription_renews_at y ahí termina
            $table->boolean('cancel_at_period_end')->default(false)->after('provider_subscription_id');

            $table->index(['payment_provider', 'provider_subscription_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['payment_provider', 'provider_subscription_id']);
            $table->dropColumn(['payment_provider', 'provider_customer_id', 'provider_subscription_id', 'cancel_at_period_end']);
        });

        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropUnique(['provider', 'external_id']);
            $table->dropConstrainedForeignId('payment_order_id');
            $table->dropColumn(['provider', 'external_id']);
        });

        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('payment_orders');
    }
};
