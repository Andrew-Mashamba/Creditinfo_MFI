<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('requisition_description', 255)->notNullable();
            $table->string('status', 10)->notNullable();
            $table->string('invoice', 200)->notNullable();
            $table->integer('employeeId')->notNullable();
            $table->integer('vendorId')->notNullable();
            $table->integer('branchId')->notNullable();
            $table->integer('quantity')->notNullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}; 