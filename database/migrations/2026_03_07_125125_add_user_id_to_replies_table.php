<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('replies', function (Blueprint $table) {
            // make admin_id nullable (users can also reply)
            $table->foreignId('user_id')->nullable()->after('admin_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('replies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->foreignId('admin_id')->nullable(false)->change();
        });
    }
};
