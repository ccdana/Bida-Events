<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name', 100); // Ej: 'Premium Imperial'
            $blueprint->string('slug', 100)->unique();
            $blueprint->integer('max_photos')->default(5);
            $blueprint->integer('months_online')->default(3);
            $blueprint->decimal('price', 10, 2); // Precio en Bolivianos (Bs.)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};