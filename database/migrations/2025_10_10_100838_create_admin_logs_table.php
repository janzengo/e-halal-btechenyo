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
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable(); // Admin user ID
            $table->string('role')->nullable(); // head, officer, etc.
            $table->string('action_type'); // create, update, delete, login, logout, etc.
            $table->string('model_type')->nullable(); // Position, Candidate, Voter, etc.
            $table->unsignedBigInteger('model_id')->nullable(); // ID of the affected model
            $table->text('action_description'); // Human-readable description
            $table->json('old_values')->nullable(); // Previous values (for updates)
            $table->json('new_values')->nullable(); // New values (for creates/updates)
            $table->json('metadata')->nullable(); // Additional context data
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('election_id')->nullable(); // Link to specific election
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'created_at']);
            $table->index(['action_type', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index('election_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
    }
};
