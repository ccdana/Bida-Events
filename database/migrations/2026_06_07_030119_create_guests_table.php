<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('invitation_id')->constrained()->onDelete('cascade');
            $blueprint->string('name', 255)->index(); // Ej: 'Familia Quispe Perez'
            $blueprint->string('phone', 50)->nullable();
            $blueprint->integer('passes_allocated')->default(1);
            $blueprint->integer('passes_confirmed')->default(0);
            $blueprint->enum('status', ['pending', 'confirmed', 'declined'])->default('pending');
            $blueprint->string('table_number', 50)->nullable();
            $blueprint->text('dietary_restrictions')->nullable();
            $blueprint->string('qr_code_token', 255)->unique(); // Token para el QR de puerta
            $blueprint->timestamp('confirmed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};