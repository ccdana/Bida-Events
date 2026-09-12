<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_itinerary_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('time', 50)->nullable();
            $table->string('title', 255)->default('');
            $table->string('icon', 50)->nullable();
            $table->text('description')->nullable();
            // Claves extra del JSON original para no perder datos
            $table->json('meta')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'sort_order', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_itinerary_items');
    }
};
