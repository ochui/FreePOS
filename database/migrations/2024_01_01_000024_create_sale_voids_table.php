<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_voids', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('saleid');
            $table->unsignedInteger('userid');
            $table->unsignedInteger('deviceid');
            $table->unsignedInteger('locationid');
            $table->string('reason', 1024);
            $table->string('method', 32);
            $table->decimal('amount', 12, 2);
            $table->string('items', 2048);
            $table->tinyInteger('void');
            $table->unsignedBigInteger('processdt');
            $table->dateTime('dt');
            $table->index('saleid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_voids');
    }
};
