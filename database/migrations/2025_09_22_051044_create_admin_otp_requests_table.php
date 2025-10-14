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
        Schema::create('admin_otp_requests', function (Blueprint $table) {
            $table->id();
            $table->string('email', 100)->nullable();
            $table->string('otp', 6);
            $table->integer('attempts')->default(0);
            $table->datetime('expires_at');
            $table->timestamps();

            $table->index('otp');
            $table->index('expires_at');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_otp_requests');
    }
};
