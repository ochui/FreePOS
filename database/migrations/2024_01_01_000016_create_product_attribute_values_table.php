<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('attribute_id');
            $table->string('value', 66);
            $table->string('display_value', 66);
            $table->integer('sort_order')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['attribute_id', 'value']);
            $table->index('attribute_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};
