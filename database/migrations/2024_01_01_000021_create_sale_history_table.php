<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('saleid');
            $table->unsignedInteger('userid');
            $table->string('type', 66);
            $table->string('description', 256);
            $table->dateTime('dt');
            $table->index('saleid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_history');
    }
};
