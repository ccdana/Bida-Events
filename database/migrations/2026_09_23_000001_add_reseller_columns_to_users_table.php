<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Revendedores: usuarios que pagan una suscripción mensual y arman sus propias invitaciones
 * dentro del cupo de su plan (config/bida.php, clave «reseller_plans»). No tiene nada que ver con
 * la vieja tabla «plans», que era por invitación y se eliminó en 2026_06_12.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_reseller')->default(false)->index();
            $table->string('reseller_plan', 50)->nullable();
            // 'active' | 'past_due' | 'canceled'
            $table->string('subscription_status', 20)->nullable();
            $table->date('subscription_renews_at')->nullable()->index();
            // Marca del revendedor: el nombre va en el pie de sus invitaciones si el plan es de marca blanca
            $table->string('business_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('brand_primary_color', 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_reseller']);
            $table->dropIndex(['subscription_renews_at']);
            $table->dropColumn([
                'is_reseller', 'reseller_plan', 'subscription_status', 'subscription_renews_at',
                'business_name', 'logo_path', 'brand_primary_color',
            ]);
        });
    }
};
