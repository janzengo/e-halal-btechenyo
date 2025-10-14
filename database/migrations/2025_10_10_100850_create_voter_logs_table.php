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
        Schema::create('voter_logs', function (Blueprint $table) {
            $table->id();
            $table->string('voter_id')->nullable(); // Voter student number
            $table->string('action_type'); // login, logout, vote_submitted, etc.
            $table->text('action_description'); // Human-readable description
            $table->json('metadata')->nullable(); // Additional context data (vote reference, etc.)
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('election_id')->nullable(); // Link to specific election
            $table->timestamps();

            // Indexes for better performance
            $table->index(['voter_id', 'created_at']);
            $table->index(['action_type', 'created_at']);
            $table->index('election_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voter_logs');
    }
};
