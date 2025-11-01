<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_map', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('deviceid');
            $table->string('uuid', 64);
            $table->string('ip', 66);
            $table->string('useragent', 256);
            $table->dateTime('dt');
            $table->unique('uuid');
            $table->index('deviceid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_map');
    }
};
