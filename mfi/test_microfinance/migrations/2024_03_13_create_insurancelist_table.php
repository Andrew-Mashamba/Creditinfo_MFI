<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('insurancelist', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->string('type', 50)->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->string('calculating_type', 50)->nullable();
            $table->string('source', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('insurancelist');
    }
}; 