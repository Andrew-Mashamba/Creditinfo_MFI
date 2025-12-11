<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('collateral_types', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_product_id')->notNullable();
            $table->string('type', 255)->notNullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('collateral_types');
    }
}; 