<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos propios de las tarjetas estacionales (primera: Día del Amor).
 * - card_dedications: quién la manda, para quién, el mensaje y la firma.
 * - card_milestones: una fecha importante con su contador («juntos desde»).
 * Las respuestas del destinatario usan guest_contributions (type = card_reply).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_dedications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->unique()->constrained('invitations')->cascadeOnDelete();
            $table->string('from_name')->nullable();
            $table->string('to_name')->nullable();
            $table->text('message')->nullable();
            $table->string('signature')->nullable();
            $table->timestamps();
        });

        Schema::create('card_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->date('started_on')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_milestones');
        Schema::dropIfExists('card_dedications');
    }
};
