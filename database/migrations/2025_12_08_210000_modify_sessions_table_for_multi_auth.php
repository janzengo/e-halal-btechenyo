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
        Schema::table('sessions', function (Blueprint $table) {
            // Drop the old user_id column
            $table->dropColumn('user_id');
        });

        Schema::table('sessions', function (Blueprint $table) {
            // Add new columns for multi-auth support
            $table->unsignedBigInteger('authenticatable_id')->nullable()->after('id');
            $table->string('authenticatable_type')->nullable()->after('authenticatable_id');
            
            // Add index for faster lookups
            $table->index(['authenticatable_id', 'authenticatable_type'], 'sessions_auth_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex('sessions_auth_index');
            $table->dropColumn(['authenticatable_id', 'authenticatable_type']);
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->index();
        });
    }
};
