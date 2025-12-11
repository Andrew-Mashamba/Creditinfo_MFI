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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bill_id');
            $table->string('payment_ref')->unique();
            $table->string('transaction_reference')->unique();
            $table->string('control_number');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('TZS');
            $table->string('payment_channel');
            $table->string('payer_name');
            $table->string('payer_msisdn');
            $table->string('payer_email')->nullable();
            $table->string('payer_tin')->nullable();
            $table->string('payer_nin')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->enum('status', ['Pending', 'Confirmed', 'Reversed'])->default('Pending');
            $table->json('raw_payload')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['bill_id', 'status']);
            $table->index('payment_ref');
            $table->index('transaction_reference');
            $table->index('control_number');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
