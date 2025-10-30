<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('config', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 66);
            $table->string('data', 2048);
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('config');
    }
};
