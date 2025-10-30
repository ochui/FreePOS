<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('saleid');
            $table->string('method', 32);
            $table->decimal('amount', 12, 2);
            $table->unsignedBigInteger('processdt');
            $table->index('saleid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
