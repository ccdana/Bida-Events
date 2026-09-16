<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Código de vestimenta. Tres listas en una tabla, separadas por "kind":
 * sugerencias (con foto y ejemplos), colores permitidos (nombre y hex) y cosas a evitar (texto).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_dress_code_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('kind', 20);
            $table->string('audience', 100)->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('image_url')->nullable();
            $table->string('color_hex', 20)->nullable();
            $table->json('examples')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'kind', 'sort_order', 'id'], 'invitation_dress_code_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_dress_code_items');
    }
};
