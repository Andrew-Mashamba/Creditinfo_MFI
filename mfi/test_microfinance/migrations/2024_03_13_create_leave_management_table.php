<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_management', function (Blueprint $table) {
            $table->id();
            $table->integer('total_days')->notNullable();
            $table->integer('days_acquire')->notNullable();
            $table->integer('leave_days_taken')->notNullable();
            $table->integer('balance')->notNullable();
            $table->integer('employee_number')->notNullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_management');
    }
}; 