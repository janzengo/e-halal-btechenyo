<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained('election_status');
            $table->string('vote_ref', 20);
            $table->longText('votes_data');
            $table->timestamps();

            $table->unique('vote_ref');
            $table->index(['election_id', 'created_at']);
        });

        // Add JSON validation constraint
        DB::statement('ALTER TABLE votes ADD CONSTRAINT chk_votes_data_json CHECK (JSON_VALID(votes_data))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the JSON validation constraint first
        DB::statement('ALTER TABLE votes DROP CONSTRAINT chk_votes_data_json');
        Schema::dropIfExists('votes');
    }
};
