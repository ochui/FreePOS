<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('altname', 66);
            $table->string('data', 2048);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rules');
    }
};
