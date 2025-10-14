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
        Schema::create('election_status', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['setup', 'pending', 'active', 'paused', 'completed'])->default('setup');
            $table->string('election_name');
            $table->datetime('end_time')->nullable();
            $table->datetime('last_status_change')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->string('control_number', 20);
            $table->timestamps();

            $table->unique('control_number');
            $table->index(['status', 'end_time']);
            $table->index('control_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('election_status');
    }
};
