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
        Schema::table('institutions', function (Blueprint $table) {
            // Asset accounts
            $table->string('trade_receivables_account')->nullable();
            $table->string('prepaid_expenses_account')->nullable();
            $table->string('short_term_investments_account')->nullable();
            $table->string('other_current_assets_account')->nullable();
            $table->string('intangible_assets_account')->nullable();
            $table->string('long_term_investments_account')->nullable();
            
            // Liability accounts
            $table->string('trade_payables_account')->nullable();
            $table->string('interest_payable_account')->nullable();
            $table->string('unearned_revenue_account')->nullable();
            $table->string('accrued_expenses_account')->nullable();
            $table->string('other_payables_account')->nullable();
            $table->string('deferred_tax_account')->nullable();
            $table->string('long_term_debt_account')->nullable();
            $table->string('provisions_account')->nullable();
            
            // Equity accounts
            $table->string('retained_earnings_account')->nullable();
            $table->string('reserves_account')->nullable();
            $table->string('share_capital_account')->nullable();
            $table->string('share_premium_account')->nullable();
            
            // Income accounts
            $table->string('fee_income_account')->nullable();
            $table->string('other_income_account')->nullable();
            $table->string('interest_income_account')->nullable();
            
            // Expense accounts
            $table->string('interest_expense_account')->nullable();
            $table->string('deposit_interest_account')->nullable();
            $table->string('loan_loss_provision_account')->nullable();
            $table->string('operating_expenses_account')->nullable();
            $table->string('administrative_expenses_account')->nullable();
            $table->string('personnel_expenses_account')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn([
                'trade_receivables_account',
                'prepaid_expenses_account',
                'short_term_investments_account',
                'other_current_assets_account',
                'intangible_assets_account',
                'long_term_investments_account',
                'trade_payables_account',
                'interest_payable_account',
                'unearned_revenue_account',
                'accrued_expenses_account',
                'other_payables_account',
                'deferred_tax_account',
                'long_term_debt_account',
                'provisions_account',
                'retained_earnings_account',
                'reserves_account',
                'share_capital_account',
                'share_premium_account',
                'fee_income_account',
                'other_income_account',
                'interest_income_account',
                'interest_expense_account',
                'deposit_interest_account',
                'loan_loss_provision_account',
                'operating_expenses_account',
                'administrative_expenses_account',
                'personnel_expenses_account'
            ]);
        });
    }
};