<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 66);
            $table->unsignedInteger('locationid');
            $table->string('data', 2048);
            $table->dateTime('dt')->useCurrent();
            $table->tinyInteger('disabled')->default(0);
            $table->index('locationid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
