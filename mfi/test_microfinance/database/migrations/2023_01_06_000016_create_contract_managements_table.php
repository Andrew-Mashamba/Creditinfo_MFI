<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contract_managements', function (Blueprint $table) {
            $table->id();
            $table->string('contract_name', 40)->notNullable();
            $table->string('contract_description', 255)->notNullable();
            $table->string('contract_file_path', 255)->nullable();
            $table->string('endDate', 255)->nullable();
            $table->string('startDate', 255)->nullable();
            $table->string('vendorId', 255)->nullable();
            $table->string('status', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_managements');
    }
}; 