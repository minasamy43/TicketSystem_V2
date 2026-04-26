<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->json('images')->nullable()->after('image');
        });

        // Migrate existing data
        DB::statement('UPDATE tickets SET images = JSON_ARRAY(image) WHERE image IS NOT NULL');

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('image')->nullable();
        });

        // Migrate back
        DB::statement('UPDATE tickets SET image = JSON_UNQUOTE(JSON_EXTRACT(images, "$[0]")) WHERE JSON_LENGTH(images) > 0');

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
