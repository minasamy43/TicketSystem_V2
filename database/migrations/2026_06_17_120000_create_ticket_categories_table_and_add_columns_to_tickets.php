<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Create ticket_categories table
        if (!Schema::hasTable('ticket_categories')) {
            Schema::create('ticket_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        // 2. Seed initial categories
        $defaultCategories = ['live Egypt', 'live pro', 'demo Egypt', 'demo pro', 'other'];
        foreach ($defaultCategories as $categoryName) {
            DB::table('ticket_categories')->updateOrInsert(
                ['name' => $categoryName],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // 3. Add columns to tickets table if they don't exist
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'category')) {
                $table->string('category')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('tickets', 'solved_at')) {
                $table->timestamp('solved_at')->nullable()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'solved_at')) {
                $table->dropColumn('solved_at');
            }
            if (Schema::hasColumn('tickets', 'category')) {
                $table->dropColumn('category');
            }
        });

        Schema::dropIfExists('ticket_categories');
    }
};
