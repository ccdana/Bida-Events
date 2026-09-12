<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            // Identificador estable usado por el editor y por poll_votes.poll_id
            $table->string('poll_key', 100);
            $table->text('question');
            $table->string('type', 20)->default('single');
            $table->boolean('is_enabled')->default(true);
            $table->json('meta')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['invitation_id', 'poll_key']);
            $table->index(['invitation_id', 'is_enabled', 'sort_order']);
        });

        Schema::create('invitation_poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('invitation_polls')->cascadeOnDelete();
            $table->text('label');
            $table->string('value', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            // También cubre las lecturas ordenadas por encuesta
            $table->unique(['poll_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_poll_options');
        Schema::dropIfExists('invitation_polls');
    }
};
