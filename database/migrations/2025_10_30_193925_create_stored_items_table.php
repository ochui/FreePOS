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
        Schema::create('stored_items', function (Blueprint $table) {
            $table->id();
            $table->string('data', 2048);
            $table->integer('supplierid')->index();
            $table->integer('categoryid');
            $table->string('code', 256);
            $table->string('name', 66);
            $table->string('price', 66);
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stored_items');
    }
};
