<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('interest_payables', function (Blueprint $table) {
            $table->id();
            $table->integer('member_id')->nullable();
            $table->string('account_type', 50)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->date('deposit_date')->nullable();
            $table->date('maturity_date')->nullable();
            $table->string('payment_frequency', 20)->nullable();
            $table->decimal('accrued_interest', 10, 2)->nullable();
            $table->decimal('interest_payable', 10, 2)->nullable();
            $table->string('loan_provider', 100)->nullable();
            $table->decimal('loan_interest_rate', 5, 2)->nullable();
            $table->string('loan_term', 50)->nullable();
            $table->date('loan_start_date')->nullable();
            $table->string('interest_payment_schedule', 50)->nullable();
            $table->decimal('accrued_interest_loan', 10, 2)->nullable();
            $table->decimal('interest_payable_loan', 10, 2)->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('interest_payables');
    }
}; 