<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar FK de plan_id en invitations
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });

        // Eliminar tabla event_plan_feature
        Schema::dropIfExists('event_plan_feature');

        // Eliminar tabla plans
        Schema::dropIfExists('plans');
    }

    public function down(): void
    {
        // Recrear tabla plans
        Schema::create('plans', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name', 100);
            $blueprint->string('slug', 100)->unique();
            $blueprint->integer('max_photos')->default(5);
            $blueprint->integer('months_online')->default(3);
            $blueprint->decimal('price', 10, 2);
        });

        // Recrear tabla event_plan_feature
        Schema::create('event_plan_feature', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('event_type_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('plan_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('feature_id')->constrained()->onDelete('cascade');
        });

        // Recrear FK en invitations
        Schema::table('invitations', function (Blueprint $table) {
            $table->foreignId('plan_id')->constrained()->after('event_type_id');
        });
    }
};
