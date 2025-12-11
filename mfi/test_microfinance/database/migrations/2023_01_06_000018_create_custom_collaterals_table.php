<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('custom_collaterals', function (Blueprint $table) {
            $table->id();
            $table->string('inputs', 255)->notNullable();
            $table->integer('loan_id')->notNullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_collaterals');
    }
}; 