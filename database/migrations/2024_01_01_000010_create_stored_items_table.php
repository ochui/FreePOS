<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stored_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('data', 2048);
            $table->unsignedInteger('supplierid');
            $table->unsignedInteger('categoryid');
            $table->string('code', 256);
            $table->string('name', 66);
            $table->string('price', 66);
            $table->tinyInteger('is_variant_parent')->default(0);
            $table->string('variant_attributes', 2048)->nullable();
            $table->index('supplierid');
            $table->index('categoryid');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stored_items');
    }
};
