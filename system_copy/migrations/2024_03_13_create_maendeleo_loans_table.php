<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('maendeleo_loans', function (Blueprint $table) {
            $table->id();
            $table->string('category_code', 255)->nullable();
            $table->string('sub_category_code', 255)->nullable();
            $table->string('sub_category_name', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('maendeleo_loans');
    }
}; 