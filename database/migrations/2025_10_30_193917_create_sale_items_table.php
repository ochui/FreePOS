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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->integer('saleid');
            $table->integer('storeditemid');
            $table->string('saleitemid', 12);
            $table->integer('qty');
            $table->string('name', 66);
            $table->string('description', 128);
            $table->string('taxid', 11);
            $table->string('tax', 2048);
            $table->tinyInteger('tax_incl')->default(1);
            $table->decimal('tax_total', 12, 2)->default(0.00);
            $table->decimal('cost', 12, 2)->default(0.00);
            $table->decimal('unit_original', 12, 2)->default(0.00);
            $table->decimal('unit', 12, 2);
            $table->decimal('price', 12, 2);
            $table->integer('refundqty');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
