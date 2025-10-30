<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ref', 128);
            $table->string('type', 12);
            $table->string('channel', 12);
            $table->string('data', 16384);
            $table->unsignedInteger('userid');
            $table->unsignedInteger('deviceid');
            $table->unsignedInteger('locationid');
            $table->unsignedInteger('custid');
            $table->decimal('discount', 4, 0);
            $table->decimal('rounding', 10, 2)->default(0);
            $table->decimal('cost', 12, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            $table->decimal('balance', 10, 2)->default(0);
            $table->tinyInteger('status');
            $table->unsignedBigInteger('processdt');
            $table->unsignedBigInteger('duedt')->default(0);
            $table->dateTime('dt');
            $table->index('ref');
            $table->index('custid');
            $table->index('dt');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
