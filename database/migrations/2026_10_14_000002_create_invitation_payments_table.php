<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El cobro de «Hazlo tú»: un pago único por invitación (publicarla con un nivel o subirla de nivel).
 * - payment_orders.invitation_id: la invitación que se paga (purpose «invitation»; «plan» guarda el
 *   nivel que se compra). Si se borra la invitación, la orden queda: es un registro de dinero.
 * - invitation_payments: cada cobro aplicado, con el nivel que dejó y el que tenía antes (para
 *   deshacerlo si se devuelve). (provider, external_id) único: un aviso repetido no cobra dos veces.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            $table->foreignId('invitation_id')->nullable()->after('user_id')->constrained('invitations')->nullOnDelete();
        });

        Schema::create('invitation_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained('invitations')->nullOnDelete();
            $table->foreignId('payment_order_id')->nullable()->constrained('payment_orders')->nullOnDelete();
            // Nivel con el que quedó la invitación y el que tenía antes (null: borrador; «gratis»)
            $table->string('tier', 20);
            $table->string('from_tier', 20)->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->string('provider', 30);
            $table->string('external_id', 191)->nullable();
            $table->timestamp('paid_at');
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
            $table->index(['invitation_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_payments');

        Schema::table('payment_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invitation_id');
        });
    }
};
