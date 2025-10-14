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
        // Add foreign key for admin_otp_requests
        Schema::table('admin_otp_requests', function (Blueprint $table) {
            $table->foreign('email')->references('email')->on('admin')->onDelete('cascade');
        });

        // Add foreign key for otp_requests
        Schema::table('otp_requests', function (Blueprint $table) {
            $table->foreign('student_number')->references('student_number')->on('voters')->onDelete('cascade');
        });

        // Add foreign key for Laravel's built-in password_reset_tokens
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->foreign('email')->references('email')->on('admin')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_otp_requests', function (Blueprint $table) {
            $table->dropForeign(['email']);
        });

        Schema::table('otp_requests', function (Blueprint $table) {
            $table->dropForeign(['student_number']);
        });

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropForeign(['email']);
        });
    }
};
