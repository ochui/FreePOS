<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variant_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('variant_id');
            $table->unsignedInteger('attribute_id');
            $table->unsignedInteger('attribute_value_id');
            $table->unique(['variant_id', 'attribute_id']);
            $table->index('attribute_id');
            $table->index('attribute_value_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_attributes');
    }
};
