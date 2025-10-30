<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('storeditemid');
            $table->unsignedInteger('locationid');
            $table->integer('stocklevel');
            $table->dateTime('dt');
            $table->unique(['storeditemid', 'locationid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_levels');
    }
};
