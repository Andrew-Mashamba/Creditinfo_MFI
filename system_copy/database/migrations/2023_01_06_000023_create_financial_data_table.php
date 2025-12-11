<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('financial_data', function (Blueprint $table) {
            $table->id();
            $table->string('description', 255)->nullable();
            $table->string('category', 100)->nullable();
            $table->decimal('value', 15, 2)->nullable();
            $table->date('end_of_business_year')->nullable();
            $table->string('unit', 20)->default('Tshs.');
        });
    }

    public function down()
    {
        Schema::dropIfExists('financial_data');
    }
}; 