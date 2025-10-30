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
        Schema::create('tax_items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 66);
            $table->string('altname', 66);
            $table->string('type', 12);
            $table->string('value', 8);
            $table->string('multiplier', 8);
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_items');
    }
};
