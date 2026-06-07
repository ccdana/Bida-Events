<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_contributions', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('invitation_id')->constrained()->onDelete('cascade');
            // Nullable si el invitado interactúa sin registrar su nombre (p. ej. libro de firmas libre)
            $blueprint->foreignId('guest_id')->nullable()->constrained()->onDelete('set null');
            $blueprint->enum('type', ['song_request', 'live_photo', 'guestbook_message']);
            $blueprint->text('content_text')->nullable(); // Mensaje o nombre de la canción
            $blueprint->string('file_path', 255)->nullable(); // URL de Cloudinary de la foto de la fiesta
            $blueprint->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_contributions');
    }
};