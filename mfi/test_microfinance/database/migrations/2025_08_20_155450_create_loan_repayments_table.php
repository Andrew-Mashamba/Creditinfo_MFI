<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id');
            $table->string('client_number');
            $table->string('receipt_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->datetime('payment_date');
            $table->string('payment_method');
            $table->string('payment_reference')->nullable();
            $table->string('payment_type')->default('INSTALLMENT'); // INSTALLMENT, LIQUIDATION, PENALTY, etc.
            $table->string('status')->default('COMPLETED');
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('loan_id');
            $table->index('client_number');
            $table->index('payment_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_repayments');
    }
};