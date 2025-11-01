<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 128);
            $table->string('name', 66);
            $table->string('phone', 66);
            $table->string('mobile', 66);
            $table->string('address', 192);
            $table->string('suburb', 66);
            $table->string('postcode', 12)->default('');
            $table->string('state', 66);
            $table->string('country', 66);
            $table->string('notes', 2048)->default('');
            $table->string('googleid', 1024);
            $table->string('pass', 512)->default('');
            $table->string('token', 256)->default('');
            $table->tinyInteger('activated')->default(0);
            $table->tinyInteger('disabled')->default(0);
            $table->dateTime('lastlogin')->nullable();
            $table->dateTime('dt')->useCurrent();
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
