<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lo que el administrador cambia sin tocar el código ni el .env: precios, promociones y qué
 * plantillas de temporada se ofrecen. Cada fila guarda un grupo de ajustes y se aplica sobre
 * config/bida.php al arrancar (ver App\Support\SiteSettings).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('key', 50)->primary();
            $table->json('value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
