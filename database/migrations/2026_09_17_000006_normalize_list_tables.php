<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Las tablas de listas dejan de guardar datos sueltos en JSON:
 * - regalos: la tienda y la lluvia de sobres pasan a ser filas con su tipo;
 * - medios: el artista de la canción tiene su columna;
 * - vestimenta: los ejemplos de cada sugerencia van en su propia tabla;
 * - se quitan las columnas meta, que no se usaban.
 * La visibilidad de módulos usa invitation_features: una fila por módulo, sin repetir.
 */
return new class extends Migration
{
    private const META_TABLES = [
        'invitation_itinerary_items',
        'invitation_gallery_images',
        'invitation_polls',
        'invitation_locations',
        'invitation_featured_people',
        'invitation_dress_code_items',
        'invitation_gift_options',
        'invitation_media',
    ];

    public function up(): void
    {
        Schema::table('invitation_gift_options', function (Blueprint $table) {
            $table->string('type', 20)->default('option')->after('invitation_id');
            $table->string('address', 500)->nullable()->after('url');
        });

        Schema::table('invitation_media', function (Blueprint $table) {
            $table->string('artist')->nullable()->after('title');
        });

        Schema::create('invitation_dress_code_examples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dress_code_item_id')->constrained('invitation_dress_code_items')->cascadeOnDelete();
            $table->string('text');
            $table->unsignedInteger('sort_order')->default(0);

            $table->index(['dress_code_item_id', 'sort_order']);
        });

        Schema::table('invitation_dress_code_items', function (Blueprint $table) {
            $table->dropColumn('examples');
        });

        foreach (self::META_TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn('meta');
            });
        }

        Schema::table('invitation_features', function (Blueprint $table) {
            $table->unique(['invitation_id', 'feature_id']);
        });
    }

    public function down(): void
    {
        Schema::table('invitation_features', function (Blueprint $table) {
            $table->dropUnique(['invitation_id', 'feature_id']);
        });

        foreach (self::META_TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->json('meta')->nullable();
            });
        }

        Schema::table('invitation_dress_code_items', function (Blueprint $table) {
            $table->json('examples')->nullable();
        });

        Schema::dropIfExists('invitation_dress_code_examples');

        Schema::table('invitation_media', function (Blueprint $table) {
            $table->dropColumn('artist');
        });

        Schema::table('invitation_gift_options', function (Blueprint $table) {
            $table->dropColumn(['type', 'address']);
        });
    }
};
