<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Portada de la invitación. Los nombres van separados (la pareja de una boda son dos personas)
 * y la edad es un número, en vez de adivinarla del subtítulo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_heroes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->unique()->constrained('invitations')->cascadeOnDelete();
            $table->string('primary_name')->nullable();
            $table->string('secondary_name')->nullable();
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('message')->nullable();
            $table->string('date_text')->nullable();
            $table->text('image_url')->nullable();
            $table->string('image_alt')->nullable();
            $table->text('post_event_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_heroes');
    }
};
