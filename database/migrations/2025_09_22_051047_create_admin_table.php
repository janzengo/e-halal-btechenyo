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
        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50);
            $table->string('email', 100)->nullable();
            $table->string('password', 60);
            $table->string('firstname', 50);
            $table->string('lastname', 50);
            $table->string('photo', 150);
            $table->date('created_on');
            $table->string('role', 20)->default('officer');
            $table->string('gender', 10);

            $table->unique('email');
            $table->index('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};
