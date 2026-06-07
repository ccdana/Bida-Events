<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_features', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('invitation_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('feature_id')->constrained()->onDelete('cascade');
            $blueprint->boolean('is_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_features');
    }
};