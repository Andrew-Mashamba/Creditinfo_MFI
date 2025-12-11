<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('investment_types', function (Blueprint $table) {
            $table->id();
            $table->string('investment_type', 100)->notNullable();
            $table->string('investment_name', 100)->notNullable();
            $table->string('description', 100)->notNullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('investment_types');
    }
}; 