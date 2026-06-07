<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('features', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name', 255); // Ej: 'Playlist de Spotify'
            $blueprint->string('code', 100)->unique(); // Ej: 'spotify_playlist'
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};