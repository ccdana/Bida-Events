<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            // 'gallery' por ahora; permite mover luego las fotos de post_evento a la misma tabla
            $table->string('collection', 30)->default('gallery');
            $table->text('url');
            $table->string('media_type', 20)->default('image');
            $table->string('alt_text', 255)->nullable();
            $table->boolean('is_cover')->default(false);
            $table->string('status', 20)->default('active');
            $table->json('meta')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'collection', 'sort_order', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_gallery_images');
    }
};
