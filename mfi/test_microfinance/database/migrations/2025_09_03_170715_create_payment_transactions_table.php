<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 100)->unique();
            $table->string('type', 50); // IFT, EFT, WALLET_TRANSFER
            $table->string('routing_system', 20)->nullable(); // TIPS, TISS
            $table->string('from_account', 50);
            $table->string('to_account', 50)->nullable();
            $table->string('to_wallet', 20)->nullable();
            $table->string('bank_code', 20)->nullable();
            $table->string('provider', 50)->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('TZS');
            $table->enum('status', ['PENDING', 'SUCCESS', 'FAILED', 'PROCESSING'])->default('PENDING');
            $table->string('response_code', 10)->nullable();
            $table->string('response_message', 500)->nullable();
            $table->string('nbc_reference', 100)->nullable();
            $table->string('engine_ref', 100)->nullable();
            $table->text('error_message')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->integer('user_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('reference');
            $table->index('type');
            $table->index('status');
            $table->index('from_account');
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
        Schema::dropIfExists('payment_transactions');
    }
}