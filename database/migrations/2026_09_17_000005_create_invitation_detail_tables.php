<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bloques que no son listas y que vivían solo en JSON: hashtag, textos de confirmación y
 * cuenta bancaria para regalos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_hashtags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->unique()->constrained('invitations')->cascadeOnDelete();
            $table->string('tag', 100)->nullable();
            $table->string('platform', 30)->nullable();
            $table->string('button_text')->nullable();
            $table->timestamps();
        });

        Schema::create('invitation_rsvp_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->unique()->constrained('invitations')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->text('confirmed_text')->nullable();
            $table->text('declined_text')->nullable();
            $table->timestamps();
        });

        Schema::create('invitation_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained('invitations')->cascadeOnDelete();
            $table->string('bank_name')->nullable();
            $table->string('holder')->nullable();
            $table->string('document_id', 50)->nullable();
            $table->string('account_number', 100)->nullable();
            $table->text('qr_image_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_bank_accounts');
        Schema::dropIfExists('invitation_rsvp_settings');
        Schema::dropIfExists('invitation_hashtags');
    }
};
