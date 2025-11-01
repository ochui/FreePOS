<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variant_stock_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('variant_id');
            $table->unsignedInteger('locationid');
            $table->integer('stocklevel');
            $table->dateTime('dt')->useCurrent();
            $table->unique(['variant_id', 'locationid']);
            $table->index('locationid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_stock_levels');
    }
};
