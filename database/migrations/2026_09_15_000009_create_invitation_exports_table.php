<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Archivos que el cliente pide desde su panel (Excel y PDF). Se generan en segundo plano para que
 * una lista grande no deje la petición esperando, y la fila guarda en qué estado va cada pedido.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 30);
            $table->string('status', 20)->default('pending');
            $table->text('path')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'invitation_exports_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_exports');
    }
};
