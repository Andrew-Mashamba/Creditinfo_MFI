<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loans_originated', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id')->notNullable();
            $table->integer('num_loans')->notNullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loans_originated');
    }
}; 