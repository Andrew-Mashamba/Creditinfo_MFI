<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('current_loans_stages', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_id')->notNullable();
            $table->integer('product_id')->notNullable();
            $table->integer('stage_id')->notNullable();
            $table->string('stage_type', 255)->notNullable();
            $table->string('stage_name', 255)->notNullable();
            $table->string('status', 50)->notNullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('current_loans_stages');
    }
}; 