<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('benefits', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->double('amount');
            $table->string('updated_at', 20);
            $table->string('created_at', 20);
            $table->string('status', 10);
        });
    }

    public function down()
    {
        Schema::dropIfExists('benefits');
    }
}; 