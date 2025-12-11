<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('liability_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('major_category_code', 255)->nullable();
            $table->string('category_code', 255)->nullable();
            $table->string('category_name', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('liability_accounts');
    }
}; 