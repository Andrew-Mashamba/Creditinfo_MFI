<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reconciliation_staging_table', function (Blueprint $table) {
            $table->id();
            $table->string('Reference_Number', 80)->nullable();
            $table->string('Account_code', 20)->nullable();
            $table->string('Details', 255)->nullable();
            $table->date('Value_Date')->nullable();
            $table->decimal('Debit', 10, 2)->nullable();
            $table->decimal('Credit', 10, 2)->nullable();
            $table->decimal('Book_Balance', 10, 2)->nullable();
            $table->string('Institution_Id', 20)->nullable();
            $table->string('Process_Status', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reconciliation_staging_table');
    }
}; 