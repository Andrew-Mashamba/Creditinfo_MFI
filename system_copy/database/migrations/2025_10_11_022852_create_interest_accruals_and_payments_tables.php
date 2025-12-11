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
        // Table for daily interest accruals
        Schema::create('interest_accruals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('account_number', 50);
            $table->date('accrual_date');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->decimal('annual_rate', 10, 4)->default(0); // e.g., 5.0000 for 5%
            $table->decimal('daily_rate', 10, 8)->default(0); // e.g., 0.00013699 for 5%/365
            $table->decimal('interest_amount', 15, 2)->default(0);
            $table->integer('year');
            $table->integer('month');
            $table->integer('day');
            $table->string('status', 20)->default('ACCRUED'); // ACCRUED, PAID, REVERSED
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('account_id');
            $table->index('account_number');
            $table->index('accrual_date');
            $table->index(['year', 'month']);
            $table->index('status');
            $table->unique(['account_number', 'accrual_date']); // Prevent duplicate accruals
        });

        // Table for annual interest payments
        Schema::create('interest_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('account_number', 50);
            $table->string('client_number', 50)->nullable();
            $table->integer('year');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('gross_interest', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0); // e.g., 15.00 for 15%
            $table->decimal('tax_withheld', 15, 2)->default(0);
            $table->decimal('net_interest', 15, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->string('reference_number', 50)->unique();
            $table->string('transaction_reference', 50)->nullable(); // Link to general_ledger
            $table->string('status', 20)->default('PENDING'); // PENDING, PAID, REVERSED
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('account_id');
            $table->index('account_number');
            $table->index('client_number');
            $table->index('year');
            $table->index('status');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('interest_payments');
        Schema::dropIfExists('interest_accruals');
    }
};
