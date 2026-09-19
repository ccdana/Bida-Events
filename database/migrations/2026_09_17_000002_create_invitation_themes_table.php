<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Colores y tipografías de la invitación, una columna por valor. Reemplaza las columnas JSON
 * colors y typography de invitation_settings. La plantilla vive en invitations.template.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->unique()->constrained('invitations')->cascadeOnDelete();
            $table->string('color_primary', 20)->nullable();
            $table->string('color_secondary', 20)->nullable();
            $table->string('color_accent', 20)->nullable();
            $table->string('color_text', 20)->nullable();
            $table->string('color_background', 20)->nullable();
            $table->string('font_titles', 100)->nullable();
            $table->string('font_body', 100)->nullable();
            $table->string('font_script', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_themes');
    }
};
