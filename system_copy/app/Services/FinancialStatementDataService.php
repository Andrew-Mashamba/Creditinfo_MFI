<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

/**
 * Service to fetch data from actual accounting modules for financial statements
 * This maps to the actual database structure used by the accounting submodules
 */
class FinancialStatementDataService
{
    /**
     * Get PPE (Property, Plant & Equipment) data from PpeManagement module
     */
    public function getPpeData($asOfDate)
    {
        $ppeData = [
            'gross_value' => 0,
            'accumulated_depreciation' => 0,
            'net_value' => 0,
            'categories' => []
        ];

        if (Schema::hasTable('ppes')) {
            // Get PPE assets with their values
            $assets = DB::table('ppes')
                ->where('status', 'ACTIVE')
                ->whereDate('purchase_date', '<=', $asOfDate)
                ->get();

            foreach ($assets as $asset) {
                $ppeData['gross_value'] += $asset->initial_value ?? $asset->purchase_price ?? 0;
                $ppeData['accumulated_depreciation'] += $asset->accumulated_depreciation ?? 0;
            }

            $ppeData['net_value'] = $ppeData['gross_value'] - $ppeData['accumulated_depreciation'];
        }

        // Alternative: Check ppe_assets table
        if (Schema::hasTable('ppe_assets')) {
            $ppeAssets = DB::table('ppe_assets')
                ->whereDate('created_at', '<=', $asOfDate)
                ->select(
                    DB::raw('SUM(cost) as total_cost'),
                    DB::raw('SUM(accumulated_depreciation) as total_depreciation')
                )
                ->first();

            if ($ppeAssets) {
                $ppeData['gross_value'] = max($ppeData['gross_value'], $ppeAssets->total_cost ?? 0);
                $ppeData['accumulated_depreciation'] = max($ppeData['accumulated_depreciation'], $ppeAssets->total_depreciation ?? 0);
                $ppeData['net_value'] = $ppeData['gross_value'] - $ppeData['accumulated_depreciation'];
            }
        }

        return $ppeData;
    }

    /**
     * Get depreciation expense from Depreciation module
     */
    public function getDepreciationExpense($startDate, $endDate)
    {
        $depreciation = 0;

        // Check ppe_transactions for depreciation entries
        if (Schema::hasTable('ppe_transactions')) {
            $depreciation = DB::table('ppe_transactions')
                ->where('transaction_type', 'depreciation')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount');
        }

        // Alternative: Calculate from PPE depreciation fields
        if ($depreciation == 0 && Schema::hasTable('ppes')) {
            $depreciation = DB::table('ppes')
                ->where('status', 'ACTIVE')
                ->sum('depreciation_for_year');
        }

        return $depreciation;
    }

    /**
     * Get Trade Receivables from TradeAndOtherReceivables module
     */
    public function getTradeReceivables($asOfDate)
    {
        $receivables = [
            'gross_amount' => 0,
            'provision' => 0,
            'net_amount' => 0,
            'aging' => []
        ];

        if (Schema::hasTable('trade_receivables')) {
            // Get total receivables
            $gross = DB::table('trade_receivables')
                ->where('status', '!=', 'PAID')
                ->where('status', '!=', 'WRITTEN_OFF')
                ->whereDate('invoice_date', '<=', $asOfDate)
                ->sum(DB::raw('amount - COALESCE(paid_amount, 0)'));

            $receivables['gross_amount'] = $gross;

            // Get provision for bad debts
            $provision = DB::table('trade_receivables')
                ->where('status', '!=', 'PAID')
                ->where('status', '!=', 'WRITTEN_OFF')
                ->whereDate('invoice_date', '<=', $asOfDate)
                ->sum('provision_amount');

            $receivables['provision'] = $provision;
            $receivables['net_amount'] = $gross - $provision;

            // Get aging buckets
            $receivables['aging'] = $this->getReceivablesAging($asOfDate);
        }

        return $receivables;
    }

    /**
     * Get aging analysis for receivables
     */
    public function getReceivablesAging($asOfDate)
    {
        if (!Schema::hasTable('trade_receivables')) {
            return [];
        }

        $aging = [];
        $buckets = [
            'current' => ['from' => 0, 'to' => 30],
            '31_60_days' => ['from' => 31, 'to' => 60],
            '61_90_days' => ['from' => 61, 'to' => 90],
            'over_90_days' => ['from' => 91, 'to' => null]
        ];

        foreach ($buckets as $key => $range) {
            $query = DB::table('trade_receivables')
                ->where('status', '!=', 'PAID')
                ->where('status', '!=', 'WRITTEN_OFF')
                ->whereDate('invoice_date', '<=', $asOfDate)
                ->whereRaw("(?::date - invoice_date::date) >= ?", [$asOfDate, $range['from']]);

            if ($range['to'] !== null) {
                $query->whereRaw("(?::date - invoice_date::date) <= ?", [$asOfDate, $range['to']]);
            }

            $aging[$key] = $query->sum(DB::raw('amount - COALESCE(paid_amount, 0)'));
        }

        return $aging;
    }

    /**
     * Get Trade Payables from TradeAndOtherPayables module
     */
    public function getTradePayables($asOfDate)
    {
        $payables = [
            'total_amount' => 0,
            'overdue' => 0,
            'current' => 0,
            'categories' => []
        ];

        if (Schema::hasTable('trade_payables')) {
            // Total outstanding payables
            $payables['total_amount'] = DB::table('trade_payables')
                ->where('status', '!=', 'PAID')
                ->whereDate('bill_date', '<=', $asOfDate)
                ->sum(DB::raw('amount - COALESCE(paid_amount, 0)'));

            // Overdue payables
            $payables['overdue'] = DB::table('trade_payables')
                ->where('status', '!=', 'PAID')
                ->where('due_date', '<', $asOfDate)
                ->sum(DB::raw('amount - COALESCE(paid_amount, 0)'));

            $payables['current'] = $payables['total_amount'] - $payables['overdue'];
        }

        return $payables;
    }

    /**
     * Get Creditors data from Creditors module
     */
    public function getCreditors($asOfDate)
    {
        $creditors = [
            'total_outstanding' => 0,
            'current_portion' => 0,
            'long_term_portion' => 0,
            'by_type' => []
        ];

        if (Schema::hasTable('creditors')) {
            // Total outstanding to creditors
            $creditors['total_outstanding'] = DB::table('creditors')
                ->where('status', 'ACTIVE')
                ->whereDate('created_at', '<=', $asOfDate)
                ->sum('outstanding_amount');

            // Current portion (due within 12 months)
            $creditors['current_portion'] = DB::table('creditors')
                ->where('status', 'ACTIVE')
                ->whereDate('created_at', '<=', $asOfDate)
                ->whereRaw("(maturity_date::date - ?::date) <= 365", [$asOfDate])
                ->sum('outstanding_amount');

            $creditors['long_term_portion'] = $creditors['total_outstanding'] - $creditors['current_portion'];

            // By creditor type
            $types = DB::table('creditors')
                ->where('status', 'ACTIVE')
                ->whereDate('created_at', '<=', $asOfDate)
                ->groupBy('creditor_type')
                ->select('creditor_type', DB::raw('SUM(outstanding_amount) as total'))
                ->get();

            foreach ($types as $type) {
                $creditors['by_type'][$type->creditor_type] = $type->total;
            }
        }

        return $creditors;
    }

    /**
     * Get Loan data from loans table
     */
    public function getLoanPortfolio($asOfDate)
    {
        $portfolio = [
            'gross_loans' => 0,
            'loan_loss_provision' => 0,
            'net_loans' => 0,
            'current_portion' => 0,
            'long_term_portion' => 0,
            'non_performing' => 0
        ];

        if (Schema::hasTable('loans')) {
            // Gross loan portfolio
            $portfolio['gross_loans'] = DB::table('loans')
                ->whereIn('status', ['ACTIVE', 'DISBURSED'])
                ->whereDate('disbursement_date', '<=', $asOfDate)
                ->sum('principle');

            // Current portion (tenure <= 12 months)
            $portfolio['current_portion'] = DB::table('loans')
                ->whereIn('status', ['ACTIVE', 'DISBURSED'])
                ->whereDate('disbursement_date', '<=', $asOfDate)
                ->whereRaw("(tenure::integer) <= 12")
                ->sum('principle');

            // Long-term portion
            $portfolio['long_term_portion'] = DB::table('loans')
                ->whereIn('status', ['ACTIVE', 'DISBURSED'])
                ->whereDate('disbursement_date', '<=', $asOfDate)
                ->whereRaw("(tenure::integer) > 12")
                ->sum('principle');

            // Non-performing loans (days in arrears > 90)
            $portfolio['non_performing'] = DB::table('loans')
                ->whereIn('status', ['ACTIVE', 'DISBURSED'])
                ->whereDate('disbursement_date', '<=', $asOfDate)
                ->where('days_in_arrears', '>', 90)
                ->sum('principle');
        }

        // Get loan loss provisions
        if (Schema::hasTable('loan_loss_provisions')) {
            $portfolio['loan_loss_provision'] = DB::table('loan_loss_provisions')
                ->where('status', 'ACTIVE')
                ->whereDate('provision_date', '<=', $asOfDate)
                ->sum('provision_amount');
        }

        $portfolio['net_loans'] = $portfolio['gross_loans'] - $portfolio['loan_loss_provision'];

        return $portfolio;
    }

    /**
     * Get Insurance data from Insurance module
     */
    public function getInsuranceData($asOfDate)
    {
        $insurance = [
            'prepaid_insurance' => 0,
            'insurance_payable' => 0,
            'insurance_expense' => 0
        ];

        if (Schema::hasTable('insurance')) {
            // Prepaid insurance (asset)
            $insurance['prepaid_insurance'] = DB::table('insurance')
                ->where('status', 'ACTIVE')
                ->whereDate('start_date', '<=', $asOfDate)
                ->whereDate('end_date', '>=', $asOfDate)
                ->sum(DB::raw('premium_amount * (end_date::date - ?::date) / (end_date::date - start_date::date)'));

            // Insurance payable (liability)
            $insurance['insurance_payable'] = DB::table('insurance')
                ->where('status', 'PENDING')
                ->whereDate('due_date', '<=', $asOfDate)
                ->sum('premium_amount');
        }

        return $insurance;
    }

    /**
     * Get Interest Payable from InterestPayable module
     */
    public function getInterestPayable($asOfDate)
    {
        $interestPayable = 0;

        if (Schema::hasTable('interest_payable')) {
            $interestPayable = DB::table('interest_payable')
                ->where('status', 'ACTIVE')
                ->whereDate('accrual_date', '<=', $asOfDate)
                ->sum('amount');
        }

        // Alternative: Calculate from loans
        if ($interestPayable == 0 && Schema::hasTable('loans')) {
            $interestPayable = DB::table('loans')
                ->whereIn('status', ['ACTIVE', 'DISBURSED'])
                ->whereDate('disbursement_date', '<=', $asOfDate)
                ->sum('interest');
        }

        return $interestPayable;
    }

    /**
     * Get Investments data
     */
    public function getInvestments($asOfDate)
    {
        $investments = [
            'short_term' => 0,
            'long_term' => 0,
            'total' => 0
        ];

        if (Schema::hasTable('investments')) {
            // Short-term investments (maturity <= 1 year)
            $investments['short_term'] = DB::table('investments')
                ->where('status', 'ACTIVE')
                ->whereDate('purchase_date', '<=', $asOfDate)
                ->whereRaw("(maturity_date::date - ?::date) <= 365", [$asOfDate])
                ->sum('current_value');

            // Long-term investments
            $investments['long_term'] = DB::table('investments')
                ->where('status', 'ACTIVE')
                ->whereDate('purchase_date', '<=', $asOfDate)
                ->whereRaw("(maturity_date::date - ?::date) > 365", [$asOfDate])
                ->sum('current_value');

            $investments['total'] = $investments['short_term'] + $investments['long_term'];
        }

        return $investments;
    }

    /**
     * Get Other Income from OtherIncome module
     */
    public function getOtherIncome($startDate, $endDate)
    {
        $otherIncome = 0;

        if (Schema::hasTable('other_income')) {
            $otherIncome = DB::table('other_income')
                ->where('status', 'RECEIVED')
                ->whereBetween('income_date', [$startDate, $endDate])
                ->sum('amount');
        }

        return $otherIncome;
    }

    /**
     * Get Unearned/Deferred Revenue
     */
    public function getUnearnedRevenue($asOfDate)
    {
        $unearned = 0;

        if (Schema::hasTable('unearned_revenue')) {
            $unearned = DB::table('unearned_revenue')
                ->where('status', 'ACTIVE')
                ->whereDate('receipt_date', '<=', $asOfDate)
                ->whereDate('recognition_date', '>', $asOfDate)
                ->sum('unearned_amount');
        }

        return $unearned;
    }

    /**
     * Get comprehensive financial position data
     */
    public function getFinancialPositionData($asOfDate)
    {
        return [
            'assets' => [
                'current' => [
                    'cash_and_equivalents' => $this->getCashAndBankBalances($asOfDate),
                    'trade_receivables' => $this->getTradeReceivables($asOfDate),
                    'loans_current_portion' => $this->getLoanPortfolio($asOfDate)['current_portion'],
                    'investments_short_term' => $this->getInvestments($asOfDate)['short_term'],
                    'prepaid_insurance' => $this->getInsuranceData($asOfDate)['prepaid_insurance'],
                ],
                'non_current' => [
                    'ppe' => $this->getPpeData($asOfDate),
                    'loans_long_term' => $this->getLoanPortfolio($asOfDate)['long_term_portion'],
                    'investments_long_term' => $this->getInvestments($asOfDate)['long_term'],
                ]
            ],
            'liabilities' => [
                'current' => [
                    'trade_payables' => $this->getTradePayables($asOfDate),
                    'creditors_current' => $this->getCreditors($asOfDate)['current_portion'],
                    'interest_payable' => $this->getInterestPayable($asOfDate),
                    'unearned_revenue' => $this->getUnearnedRevenue($asOfDate),
                ],
                'non_current' => [
                    'creditors_long_term' => $this->getCreditors($asOfDate)['long_term_portion'],
                ]
            ]
        ];
    }

    /**
     * Get Cash and Bank Balances
     */
    private function getCashAndBankBalances($asOfDate)
    {
        $cash = 0;
        $bank = 0;

        // From bank_accounts table
        if (Schema::hasTable('bank_accounts')) {
            $bank = DB::table('bank_accounts')
                ->whereDate('created_at', '<=', $asOfDate)
                ->sum('current_balance');
        }

        // From accounts table for cash accounts
        if (Schema::hasTable('accounts')) {
            $cash = DB::table('accounts')
                ->where('account_name', 'LIKE', '%CASH%')
                ->where('status', 'ACTIVE')
                ->sum('balance');
        }

        return $cash + $bank;
    }
}