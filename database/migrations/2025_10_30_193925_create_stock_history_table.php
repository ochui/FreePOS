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
        Schema::create('stock_history', function (Blueprint $table) {
            $table->id();
            $table->integer('storeditemid');
            $table->integer('locationid');
            $table->integer('auxid');
            $table->tinyInteger('auxdir');
            $table->string('type', 66);
            $table->integer('amount');
            $table->dateTime('dt');
            $table->unique('id');
            $table->index('id');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_history');
    }
};
