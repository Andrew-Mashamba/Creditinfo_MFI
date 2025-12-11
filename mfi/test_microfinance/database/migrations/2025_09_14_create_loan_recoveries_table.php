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
        // Create loan_recoveries table
        Schema::create('loan_recoveries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('loan_id');
            $table->string('loan_account_number', 100);
            $table->decimal('amount_recovered', 20, 2);
            $table->date('recovery_date');
            $table->string('recovery_method', 50); // cash, bank_transfer, cheque, mobile_money
            $table->string('transaction_id', 100)->nullable();
            $table->string('receipt_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->bigInteger('recovered_by')->nullable();
            $table->bigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('loan_id');
            $table->index('loan_account_number');
            $table->index('recovery_date');
            $table->index('transaction_id');
            
            // Foreign keys
            $table->foreign('loan_id')->references('id')->on('loans');
            $table->foreign('recovered_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
        });
        
        // Add total_recovered column to loans table if it doesn't exist
        if (!Schema::hasColumn('loans', 'total_recovered')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->decimal('total_recovered', 20, 2)->default(0)->after('total_interest_paid');
                $table->index('total_recovered');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_recoveries');
        
        if (Schema::hasColumn('loans', 'total_recovered')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->dropColumn('total_recovered');
            });
        }
    }
};