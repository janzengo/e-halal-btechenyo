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
        Schema::table('voters', function (Blueprint $table) {
            // Add index for course_id and has_voted for faster filtering
            $table->index(['course_id', 'has_voted'], 'voters_course_voted_index');
            // Add index for student_number ordering
            $table->index('student_number', 'voters_student_number_index');
        });

        Schema::table('candidates', function (Blueprint $table) {
            // Add index for partylist_id and created_at for faster filtering and ordering
            $table->index(['partylist_id', 'created_at'], 'candidates_partylist_created_index');
            // Add index for position_id for faster joins
            $table->index('position_id', 'candidates_position_index');
        });

        Schema::table('admin_logs', function (Blueprint $table) {
            // Add indexes for better admin logs performance
            $table->index(['created_at', 'action_type'], 'admin_logs_created_action_index');
            $table->index(['user_id', 'created_at'], 'admin_logs_user_created_index');
            $table->index('role', 'admin_logs_role_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voters', function (Blueprint $table) {
            $table->dropIndex('voters_course_voted_index');
            $table->dropIndex('voters_student_number_index');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropIndex('candidates_partylist_created_index');
            $table->dropIndex('candidates_position_index');
        });

        Schema::table('admin_logs', function (Blueprint $table) {
            $table->dropIndex('admin_logs_created_action_index');
            $table->dropIndex('admin_logs_user_created_index');
            $table->dropIndex('admin_logs_role_index');
        });
    }
};