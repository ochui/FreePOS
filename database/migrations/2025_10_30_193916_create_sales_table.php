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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 128);
            $table->string('type', 12);
            $table->string('channel', 12);
            $table->string('data', 16384);
            $table->integer('userid');
            $table->integer('deviceid');
            $table->integer('locationid');
            $table->integer('custid');
            $table->decimal('discount', 4, 0);
            $table->decimal('rounding', 10, 2)->default(0);
            $table->decimal('cost', 12, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            $table->decimal('balance', 10, 2)->default(0);
            $table->tinyInteger('status');
            $table->bigInteger('processdt');
            $table->bigInteger('duedt')->default(0);
            $table->dateTime('dt');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
