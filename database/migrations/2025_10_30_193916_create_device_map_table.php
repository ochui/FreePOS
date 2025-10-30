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
        Schema::create('device_map', function (Blueprint $table) {
            $table->id();
            $table->integer('deviceid');
            $table->string('uuid', 64)->unique();
            $table->string('ip', 66);
            $table->string('useragent', 256);
            $table->dateTime('dt');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_map');
    }
};
