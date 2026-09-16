<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de medios de la invitación: la canción de fondo (audio) y el video del evento.
 * Tenerlos en filas permite saber qué archivos usa cada invitación sin abrir el JSON, que es lo
 * que hace falta para limpiar Cloudinary o cambiar de proveedor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('title')->nullable();
            $table->text('url')->nullable();
            $table->text('poster_url')->nullable();
            $table->boolean('autoplay')->default(false);
            $table->string('status', 20)->default('active');
            $table->json('meta')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'type', 'status'], 'invitation_media_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_media');
    }
};
