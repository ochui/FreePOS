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
        Schema::create('sale_history', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('saleid');
            $table->integer('userid');
            $table->string('type', 66);
            $table->string('description', 256);
            $table->dateTime('dt');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_history');
    }
};
