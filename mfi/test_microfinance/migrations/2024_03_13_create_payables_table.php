<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payables', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name', 255)->nullable();
            $table->date('due_date')->nullable();
            $table->string('invoice_number', 50)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('liability_account', 50)->nullable();
            $table->string('cash_account', 50)->nullable();
            $table->string('expense_account', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payables');
    }
}; 