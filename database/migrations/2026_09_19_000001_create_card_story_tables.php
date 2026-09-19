<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de la tarjeta «Nuestra historia» (plantilla we-story-together).
 * - card_stories: primeras impresiones, cita elegida, la anécdota, la reflexión y la promesa.
 * - card_story_moments: los momentos clave del Acto II.
 * Nombres, fecha, fotos de pareja, canción, dedicatoria y respuesta salen de módulos existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->unique()->constrained('invitations')->cascadeOnDelete();
            $table->text('first_impression')->nullable();
            $table->string('quote_key', 50)->nullable();
            $table->string('anecdote_title')->nullable();
            $table->text('anecdote')->nullable();
            $table->string('anecdote_photo', 2048)->nullable();
            $table->string('anecdote_photo_alt')->nullable();
            $table->text('reflection')->nullable();
            $table->text('promise')->nullable();
            $table->timestamps();
        });

        Schema::create('card_story_moments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('when_label', 100)->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('photo', 2048)->nullable();
            $table->string('photo_alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_story_moments');
        Schema::dropIfExists('card_stories');
    }
};
