<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Textos del encabezado de cada sección (título, subtítulo, introducción, marcador del campo y
 * botón). Una fila por invitación y módulo; el módulo es una fila de features.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained('features')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('intro')->nullable();
            $table->string('placeholder')->nullable();
            $table->string('cta_text')->nullable();
            $table->text('cta_url')->nullable();
            $table->timestamps();

            $table->unique(['invitation_id', 'feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_sections');
    }
};
