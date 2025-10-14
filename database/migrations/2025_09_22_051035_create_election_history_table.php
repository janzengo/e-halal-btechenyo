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
        Schema::create('election_history', function (Blueprint $table) {
            $table->id();
            $table->string('election_name');
            $table->enum('status', ['setup', 'pending', 'active', 'paused', 'completed'])->default('completed');
            $table->datetime('end_time');
            $table->datetime('last_status_change')->nullable();
            $table->string('details_pdf', 255);
            $table->string('results_pdf', 255);
            $table->string('control_number', 20);
            $table->timestamps();

            $table->unique('control_number');
            $table->index(['status', 'end_time']);
            $table->index(['end_time', 'status']);
            $table->index('control_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('election_history');
    }
};
