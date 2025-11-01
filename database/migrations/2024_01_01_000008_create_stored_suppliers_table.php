<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stored_suppliers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 66);
            $table->dateTime('dt');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stored_suppliers');
    }
};
