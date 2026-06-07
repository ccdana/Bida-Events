<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $blueprint) {
            $blueprint->id();
            // nullable() por si tú creas invitaciones rápidas desde consola sin asignar un cliente formal
            $blueprint->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $blueprint->foreignId('event_type_id')->constrained();
            $blueprint->foreignId('plan_id')->constrained();
            $blueprint->string('slug', 255)->unique(); // La URL de la invitación
            $blueprint->string('title', 255);
            $blueprint->dateTime('event_date'); // Para la cuenta regresiva
            $blueprint->enum('status', ['draft', 'active', 'suspended', 'expired'])->default('draft');
            $blueprint->date('expires_at'); // Fecha de expiración de la web al aire
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};