<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_data', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('invitation_id')->constrained()->onDelete('cascade');
            $blueprint->string('feature_code', 100)->index(); // Ej: 'regalos', 'itinerario'
            $blueprint->json('json_data'); // Guarda configuraciones y URLs de Cloudinary
            
            // Un módulo solo puede tener un bloque de datos por invitación
            $blueprint->unique(['invitation_id', 'feature_code']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_data');
    }
};