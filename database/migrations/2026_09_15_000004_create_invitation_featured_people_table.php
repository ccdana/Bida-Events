<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personas destacadas: chambelanes, damitas, padrinos, cortejo. Cada grupo es una lista ordenada,
 * así que van en filas y no dentro de un JSON.
 *
 * "name_key" recuerda con qué nombre llegó el campo en el JSON ("nombre" o "nombres"), para que el
 * editor y las plantillas sigan viendo exactamente la misma forma de siempre.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_featured_people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('group', 50);
            $table->string('name_key', 20)->default('nombre');
            $table->string('name')->nullable();
            $table->string('initials', 10)->nullable();
            $table->string('role')->nullable();
            $table->text('detail')->nullable();
            $table->text('message')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'group', 'sort_order', 'id'], 'invitation_featured_people_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_featured_people');
    }
};
