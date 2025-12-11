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
            // Add missing columns for Trade and Other Receivables
            $table->string('vat_payable_account')->nullable()->after('trade_receivables_account');
            $table->string('sales_revenue_account')->nullable()->after('vat_payable_account');
            $table->string('service_revenue_account')->nullable()->after('sales_revenue_account');
        });

        // Update the existing institution with default account numbers
        DB::table('institutions')
            ->where('id', 1)
            ->update([
                'trade_receivables_account' => '0101100015001510', // TRADE RECEIVABLES
                'sales_revenue_account' => '0101400045004520',     // MISCELLANEOUS INCOME (will use as sales)
                'service_revenue_account' => '010140004500',       // OTHER INCOME
                'vat_payable_account' => '0101200025002520',       // ACCRUED TAXES (will use for VAT)
                'other_income_account' => '010140004500',          // OTHER INCOME
                'fee_income_account' => '0101100014001440',        // FEE RECEIVABLE
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['vat_payable_account', 'sales_revenue_account', 'service_revenue_account']);
        });
    }
};