<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ubicación del evento. Es una tabla (y no una columna) porque un evento puede tener varias sedes:
 * ceremonia y fiesta, o iglesia y salón.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('address', 500)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('map_url')->nullable();
            $table->text('image_url')->nullable();
            $table->text('note')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'sort_order', 'id'], 'invitation_locations_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_locations');
    }
};
