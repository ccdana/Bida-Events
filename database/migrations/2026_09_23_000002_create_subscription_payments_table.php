<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pagos de suscripción de los revendedores. No hay pasarela: el administrador registra cada pago
 * a mano cuando lo recibe (transferencia, QR, efectivo) y eso extiende la fecha de renovación.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('plan', 50);
            $table->decimal('amount', 10, 2);
            $table->date('paid_at');
            $table->date('renews_until');
            // El administrador que lo registró; si se borra su cuenta, el pago queda igual
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
