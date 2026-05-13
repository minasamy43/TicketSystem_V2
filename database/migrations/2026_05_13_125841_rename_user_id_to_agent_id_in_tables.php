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
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'user_id')) {
                $table->renameColumn('user_id', 'agent_id');
            }
        });
        Schema::table('replies', function (Blueprint $table) {
            if (Schema::hasColumn('replies', 'user_id')) {
                $table->renameColumn('user_id', 'agent_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'agent_id')) {
                $table->renameColumn('agent_id', 'user_id');
            }
        });
        Schema::table('replies', function (Blueprint $table) {
            if (Schema::hasColumn('replies', 'agent_id')) {
                $table->renameColumn('agent_id', 'user_id');
            }
        });
    }
};
