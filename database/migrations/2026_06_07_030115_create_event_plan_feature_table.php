<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_plan_feature', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('event_type_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('plan_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('feature_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_plan_feature');
    }
};