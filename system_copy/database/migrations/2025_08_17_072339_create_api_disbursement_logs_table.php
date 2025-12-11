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
        Schema::create('api_disbursement_logs', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('loan_id');
            $table->string('client_number');
            $table->string('status');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method');
            $table->string('payment_reference')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->string('api_user')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index('transaction_id');
            $table->index('loan_id');
            $table->index('client_number');
            $table->index('status');
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
        Schema::dropIfExists('api_disbursement_logs');
    }
};