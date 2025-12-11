<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reconciled_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('Institution_Id', 20)->nullable();
            $table->string('Account_Code', 10)->nullable();
            $table->string('Reference_Number', 80)->nullable();
            $table->date('Value_Date')->nullable();
            $table->string('Gl_Details', 255)->nullable();
            $table->decimal('Gl_Debit', 10, 2)->nullable();
            $table->decimal('Gl_Credit', 10, 2)->nullable();
            $table->string('Bank_Details', 255)->nullable();
            $table->decimal('Bank_Debit', 10, 2)->nullable();
            $table->decimal('Bank_Credit', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reconciled_transactions');
    }
}; 