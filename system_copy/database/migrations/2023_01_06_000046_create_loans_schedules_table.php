<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loans_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id', 255)->nullable()->default(null);
            $table->double('installment')->nullable();
            $table->double('interest')->nullable();
            $table->double('principle')->nullable();
            $table->double('opening_balance')->nullable();
            $table->double('closing_balance')->nullable();
            $table->string('bank_account_number', 255)->nullable()->default(null);
            $table->string('completion_status', 255)->nullable()->default(null);
            $table->string('status', 255)->nullable()->default(null);
            $table->date('installment_date')->nullable();
            $table->date('next_check_date')->nullable();
            $table->double('penalties')->nullable();
            $table->double('amount_in_arrears')->nullable();
            $table->bigInteger('days_in_arrears')->nullable();
            $table->double('payment')->nullable();
            $table->double('interest_payment')->nullable();
            $table->double('principle_payment')->nullable();
            $table->date('promise_date')->nullable();
            $table->text('comment')->nullable();
            $table->string('member_number', 50)->nullable()->default(null);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loans_schedules');
    }
}; 