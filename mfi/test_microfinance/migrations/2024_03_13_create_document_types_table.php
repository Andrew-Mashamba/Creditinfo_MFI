<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->integer('document_id')->autoIncrement();
            $table->string('document_name', 255)->notNullable();
            $table->string('collateral_type', 255)->notNullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_types');
    }
}; 