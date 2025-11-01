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
        Schema::create('auth', function (Blueprint $table) {
            $table->id();
            $table->string('username', 256)->unique();
            $table->string('name', 66)->default('');
            $table->string('password', 256);
            $table->string('token', 64)->default('');
            $table->char('uuid', 16)->unique();
            $table->tinyInteger('admin');
            $table->tinyInteger('disabled')->default(0);
            $table->string('permissions', 2048);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth');
    }
};
