<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Opciones de la mesa de regalos (la lista pública). Los datos bancarios y la dirección de la
 * lluvia de sobres siguen en el JSON del módulo: son un bloque de configuración, no una lista,
 * y así quedan separados de lo que se puede ordenar y mostrar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_gift_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('url')->nullable();
            $table->text('image_url')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'sort_order', 'id'], 'invitation_gift_options_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_gift_options');
    }
};
