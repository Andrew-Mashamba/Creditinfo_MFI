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
        Schema::table('member_exits', function (Blueprint $table) {
            $table->decimal('deposits_balance', 15, 2)->default(0)->after('savings_balance');
            $table->decimal('loan_balance', 15, 2)->default(0)->after('deposits_balance');
            $table->decimal('unpaid_bills', 15, 2)->default(0)->after('loan_balance');
            $table->decimal('dividends', 15, 2)->default(0)->after('unpaid_bills');
            $table->decimal('interest_on_savings', 15, 2)->default(0)->after('dividends');
            $table->decimal('total_credits', 15, 2)->default(0)->after('interest_on_savings');
            $table->decimal('total_debits', 15, 2)->default(0)->after('total_credits');
            $table->integer('accounts_count')->default(0)->after('total_debits');
            $table->integer('loans_count')->default(0)->after('accounts_count');
            $table->integer('unpaid_bills_count')->default(0)->after('loans_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_exits', function (Blueprint $table) {
            $table->dropColumn([
                'deposits_balance',
                'loan_balance',
                'unpaid_bills',
                'dividends',
                'interest_on_savings',
                'total_credits',
                'total_debits',
                'accounts_count',
                'loans_count',
                'unpaid_bills_count'
            ]);
        });
    }
};
