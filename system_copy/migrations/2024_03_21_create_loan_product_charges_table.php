<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loan_product_charges', function (Blueprint $table) {
            $table->id();
            $table->string('loan_product_id')->nullable();
            $table->string('type')->nullable(); // charge or insurance
            $table->string('name')->nullable();
            $table->string('value_type')->nullable(); // fixed or percentage
            $table->decimal('value', 10, 2)->nullable();
            $table->string('account_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_product_charges');
    }
}; 