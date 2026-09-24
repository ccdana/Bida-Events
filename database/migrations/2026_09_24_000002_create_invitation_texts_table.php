<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Textos propios de una invitación: cada fila reemplaza un texto de la plantilla (el título de una
 * sección, la frase de arriba, lo que se muestra cuando todavía no hay datos, el texto de un botón…).
 * Solo se guardan los que el editor cambió; el resto sigue saliendo de la plantilla.
 * El catálogo de claves está en App\Support\EditableTexts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_texts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('key', 80);
            $table->string('value', 600);
            $table->timestamps();

            $table->unique(['invitation_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_texts');
    }
};
