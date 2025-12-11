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
        Schema::create('loan_liquidations', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id');
            $table->string('client_number');
            $table->datetime('liquidation_date');
            $table->decimal('original_balance', 15, 2);
            $table->decimal('liquidation_amount', 15, 2);
            $table->boolean('penalty_waived')->default(false);
            $table->decimal('waiver_amount', 15, 2)->default(0);
            $table->text('waiver_reason')->nullable();
            $table->string('payment_method');
            $table->string('payment_reference')->nullable();
            $table->string('receipt_number')->unique();
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('processed_by');
            $table->timestamps();
            
            $table->index('loan_id');
            $table->index('client_number');
            $table->index('liquidation_date');
            $table->index('receipt_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_liquidations');
    }
};
