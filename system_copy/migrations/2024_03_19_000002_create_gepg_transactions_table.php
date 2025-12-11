<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gepg_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('control_number');
            $table->string('account_no');
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('currency');
            $table->string('response_code');
            $table->string('response_description');
            $table->json('payload');
            $table->string('transaction_type'); // verification, postpaid, prepaid
            $table->string('quote_reference')->nullable();
            $table->timestamps();

            $table->index('control_number');
            $table->index('account_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gepg_transactions');
    }
};
