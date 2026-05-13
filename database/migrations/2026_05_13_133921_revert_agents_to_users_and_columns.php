<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('agents')) {
            Schema::rename('agents', 'users');
        }

        if (Schema::hasColumn('tickets', 'agent_id')) {
            DB::statement('ALTER TABLE tickets CHANGE agent_id user_id BIGINT UNSIGNED NOT NULL');
        }
        if (Schema::hasColumn('tickets', 'has_agent_read')) {
            DB::statement('ALTER TABLE tickets CHANGE has_agent_read has_user_read TINYINT(1) DEFAULT 1 NOT NULL');
        }

        if (Schema::hasColumn('replies', 'agent_id')) {
            DB::statement('ALTER TABLE replies CHANGE agent_id user_id BIGINT UNSIGNED NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::rename('users', 'agents');
        }

        if (Schema::hasColumn('tickets', 'user_id')) {
            DB::statement('ALTER TABLE tickets CHANGE user_id agent_id BIGINT UNSIGNED NOT NULL');
        }
        if (Schema::hasColumn('tickets', 'has_user_read')) {
            DB::statement('ALTER TABLE tickets CHANGE has_user_read has_agent_read TINYINT(1) DEFAULT 1 NOT NULL');
        }

        if (Schema::hasColumn('replies', 'user_id')) {
            DB::statement('ALTER TABLE replies CHANGE user_id agent_id BIGINT UNSIGNED NULL');
        }
    }
};
