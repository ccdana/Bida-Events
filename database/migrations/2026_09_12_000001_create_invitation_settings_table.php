<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_settings', function (Blueprint $table) {
            $table->id();
            // Una fila por invitación; su existencia indica que los módulos ya se leen desde tablas
            $table->foreignId('invitation_id')->unique()->constrained('invitations')->cascadeOnDelete();
            $table->string('template', 255)->nullable();
            $table->json('colors')->nullable();
            $table->json('typography')->nullable();
            $table->json('module_visibility')->nullable();
            // Claves de config específicas de la plantilla que aún no tienen columna propia
            $table->json('extra')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_settings');
    }
};
