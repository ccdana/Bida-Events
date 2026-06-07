<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poll_votes', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('invitation_id')->constrained()->onDelete('cascade');
            $blueprint->string('poll_id', 100);
            $blueprint->unsignedTinyInteger('option_index');
            $blueprint->foreignId('guest_id')->nullable()->constrained()->onDelete('set null');
            $blueprint->string('voter_key', 64)->index();
            $blueprint->timestamp('created_at')->useCurrent();

            $blueprint->unique(['invitation_id', 'poll_id', 'voter_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
    }
};
