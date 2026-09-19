<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entradas escritas de las tarjetas tipo cuaderno («Libro de aventuras»): los capítulos de su
 * historia y los recuerdos especiales, separados por sección. Cada una con título, fecha, texto y
 * una foto opcional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('section', 30);
            $table->string('title')->nullable();
            $table->date('happened_on')->nullable();
            $table->text('body')->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->string('image_alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'section', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_entries');
    }
};
