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
        Schema::create('sale_voids', function (Blueprint $table) {
            $table->id();
            $table->integer('saleid');
            $table->integer('userid');
            $table->integer('deviceid');
            $table->integer('locationid');
            $table->string('reason', 1024);
            $table->string('method', 32);
            $table->decimal('amount', 12, 2);
            $table->string('items', 2048);
            $table->tinyInteger('void');
            $table->bigInteger('processdt');
            $table->dateTime('dt');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_voids');
    }
};
