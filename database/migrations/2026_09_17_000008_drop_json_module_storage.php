<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fin del almacenamiento en JSON: cada módulo guarda en sus tablas (app/Modules).
 * - invitation_data (un JSON por módulo) y invitation_settings (colores, tipografías y
 *   visibilidad en JSON) se eliminan.
 * Las invitaciones de muestra se regeneran con ShowcaseInvitationsSeeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('invitation_settings');
        Schema::dropIfExists('invitation_data');
    }

    public function down(): void
    {
        Schema::create('invitation_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('feature_code', 100)->index();
            $table->json('json_data');
            $table->unique(['invitation_id', 'feature_code']);
        });

        Schema::create('invitation_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->unique()->constrained('invitations')->cascadeOnDelete();
            $table->string('template', 255)->nullable();
            $table->json('colors')->nullable();
            $table->json('typography')->nullable();
            $table->json('module_visibility')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();
        });
    }
};
