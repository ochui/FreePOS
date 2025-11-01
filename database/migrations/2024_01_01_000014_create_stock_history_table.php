<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('storeditemid');
            $table->unsignedInteger('locationid');
            $table->unsignedInteger('auxid');
            $table->tinyInteger('auxdir');
            $table->string('type', 66);
            $table->integer('amount');
            $table->dateTime('dt');
            $table->index('storeditemid');
            $table->index('locationid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_history');
    }
};
