<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loans_arreas', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id', 255)->nullable();
            $table->double('installment')->nullable();
            $table->double('interest')->nullable();
            $table->double('principle')->nullable();
            $table->double('balance')->nullable();
            $table->string('bank_account_number', 255)->nullable();
            $table->string('completion_status', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->date('installment_date')->nullable();
            $table->date('last_check_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loans_arreas');
    }
}; 