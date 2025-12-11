<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service class for generating scheduled report data
 * All reports use real data from the database
 */
class ScheduledReportDataService
{
    protected $institutionId;
    protected $reportDate;

    public function __construct()
    {
        // Default to first institution if not specified
        $this->institutionId = DB::table('institutions')->value('id') ?? 1;
        $this->reportDate = now();
    }

    /**
     * Set the institution ID for reports
     */
    public function setInstitutionId($institutionId)
    {
        $this->institutionId = $institutionId;
        return $this;
    }

    /**
     * Set the report date
     */
    public function setReportDate($date)
    {
        $this->reportDate = Carbon::parse($date);
        return $this;
    }

    // ========================================
    // DAILY REPORTS
    // ========================================

    /**
     * Daily Operations Report
     * Summary of transactions, new loans, disbursements, and collections
     */
    public function generateDailyOperationsReport(): array
    {
        $today = $this->reportDate->format('Y-m-d');
        $yesterday = $this->reportDate->copy()->subDay()->format('Y-m-d');

        // Today's transactions summary
        $todayTransactions = DB::table('transactions')
            ->whereDate('created_at', $today)
            ->where('status', 'COMPLETED')
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN type = \'CREDIT\' THEN amount ELSE 0 END) as total_credits,
                SUM(CASE WHEN type = \'DEBIT\' THEN amount ELSE 0 END) as total_debits,
                COUNT(DISTINCT client_number) as unique_members
            ')
            ->first();

        // Yesterday's transactions for comparison
        $yesterdayTransactions = DB::table('transactions')
            ->whereDate('created_at', $yesterday)
            ->where('status', 'COMPLETED')
            ->selectRaw('COUNT(*) as total_count, SUM(amount) as total_amount')
            ->first();

        // New loans disbursed today
        $newDisbursements = DB::table('loans')
            ->whereDate('disbursement_date', $today)
            ->where('status', 'DISBURSED')
            ->selectRaw('
                COUNT(*) as count,
                SUM(CAST(principle AS DECIMAL(15,2))) as total_principal,
                AVG(CAST(principle AS DECIMAL(15,2))) as avg_loan_size
            ')
            ->first();

        // Loan collections today - using loans_schedules table
        // payment = total payment, principle_payment = principal paid, interest_payment = interest paid
        $collections = DB::table('loans_schedules')
            ->whereDate('last_payment_date', $today)
            ->where('payment', '>', 0)
            ->selectRaw('
                COUNT(*) as count,
                COALESCE(SUM(payment), 0) as total_amount,
                COALESCE(SUM(principle_payment), 0) as principal_collected,
                COALESCE(SUM(interest_payment), 0) as interest_collected,
                COALESCE(SUM(CASE WHEN penalties > 0 AND payment > (COALESCE(principle_payment, 0) + COALESCE(interest_payment, 0))
                    THEN payment - COALESCE(principle_payment, 0) - COALESCE(interest_payment, 0) ELSE 0 END), 0) as penalties_collected
            ')
            ->first();

        // Member deposits today
        $deposits = DB::table('general_ledger')
            ->whereDate('created_at', $today)
            ->where('trans_status', 'Successful')
            ->where('credit', '>', 0)
            ->whereIn('beneficiary_product_id', ['2000', '2200', '3000']) // Savings and Deposits
            ->selectRaw('
                COUNT(*) as count,
                SUM(credit) as total_amount
            ')
            ->first();

        // Member withdrawals today
        $withdrawals = DB::table('general_ledger')
            ->whereDate('created_at', $today)
            ->where('trans_status', 'Successful')
            ->where('debit', '>', 0)
            ->whereIn('sender_product_id', ['2000', '2200', '3000'])
            ->selectRaw('
                COUNT(*) as count,
                SUM(debit) as total_amount
            ')
            ->first();

        // New member registrations
        $newMembers = DB::table('clients')
            ->whereDate('created_at', $today)
            ->where('client_status', 'ACTIVE')
            ->count();

        // Pending approvals
        $pendingApprovals = DB::table('approvals')
            ->where('process_status', 'Pending')
            ->count();

        return [
            'report_type' => 'Daily Operations Report',
            'report_date' => $today,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'transactions' => [
                    'total_count' => $todayTransactions->total_count ?? 0,
                    'total_credits' => number_format($todayTransactions->total_credits ?? 0, 2),
                    'total_debits' => number_format($todayTransactions->total_debits ?? 0, 2),
                    'unique_members' => $todayTransactions->unique_members ?? 0,
                    'yesterday_count' => $yesterdayTransactions->total_count ?? 0,
                    'change_percent' => $yesterdayTransactions->total_count > 0
                        ? round((($todayTransactions->total_count - $yesterdayTransactions->total_count) / $yesterdayTransactions->total_count) * 100, 1)
                        : 0,
                ],
                'disbursements' => [
                    'count' => $newDisbursements->count ?? 0,
                    'total_principal' => number_format($newDisbursements->total_principal ?? 0, 2),
                    'avg_loan_size' => number_format($newDisbursements->avg_loan_size ?? 0, 2),
                ],
                'collections' => [
                    'count' => $collections->count ?? 0,
                    'total_amount' => number_format($collections->total_amount ?? 0, 2),
                    'principal_collected' => number_format($collections->principal_collected ?? 0, 2),
                    'interest_collected' => number_format($collections->interest_collected ?? 0, 2),
                    'penalties_collected' => number_format($collections->penalties_collected ?? 0, 2),
                ],
                'deposits' => [
                    'count' => $deposits->count ?? 0,
                    'total_amount' => number_format($deposits->total_amount ?? 0, 2),
                ],
                'withdrawals' => [
                    'count' => $withdrawals->count ?? 0,
                    'total_amount' => number_format($withdrawals->total_amount ?? 0, 2),
                ],
                'new_members' => $newMembers,
                'pending_approvals' => $pendingApprovals,
            ],
        ];
    }

    /**
     * Daily Arrears Report
     * Daily update on loan arrears and collection status
     */
    public function generateDailyArrearsReport(): array
    {
        $today = $this->reportDate->format('Y-m-d');

        // Loans in arrears summary by days
        $arrearsAging = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->where('days_in_arrears', '>', 0)
            ->selectRaw('
                COUNT(*) as total_loans,
                SUM(CAST(COALESCE(arrears_in_amount, \'0\') AS DECIMAL(15,2))) as total_arrears,
                SUM(CASE WHEN days_in_arrears BETWEEN 1 AND 30 THEN 1 ELSE 0 END) as par_1_30,
                SUM(CASE WHEN days_in_arrears BETWEEN 1 AND 30 THEN CAST(COALESCE(arrears_in_amount, \'0\') AS DECIMAL(15,2)) ELSE 0 END) as par_1_30_amount,
                SUM(CASE WHEN days_in_arrears BETWEEN 31 AND 60 THEN 1 ELSE 0 END) as par_31_60,
                SUM(CASE WHEN days_in_arrears BETWEEN 31 AND 60 THEN CAST(COALESCE(arrears_in_amount, \'0\') AS DECIMAL(15,2)) ELSE 0 END) as par_31_60_amount,
                SUM(CASE WHEN days_in_arrears BETWEEN 61 AND 90 THEN 1 ELSE 0 END) as par_61_90,
                SUM(CASE WHEN days_in_arrears BETWEEN 61 AND 90 THEN CAST(COALESCE(arrears_in_amount, \'0\') AS DECIMAL(15,2)) ELSE 0 END) as par_61_90_amount,
                SUM(CASE WHEN days_in_arrears > 90 THEN 1 ELSE 0 END) as par_over_90,
                SUM(CASE WHEN days_in_arrears > 90 THEN CAST(COALESCE(arrears_in_amount, \'0\') AS DECIMAL(15,2)) ELSE 0 END) as par_over_90_amount
            ')
            ->first();

        // New arrears today (loans that became overdue today)
        $newArrearsToday = DB::table('loans_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
            ->whereDate('ls.installment_date', $today)
            ->where('ls.status', '!=', 'Paid')
            ->where('l.status', 'DISBURSED')
            ->selectRaw('
                COUNT(DISTINCT ls.loan_id) as loans_count,
                SUM(ls.principle + ls.interest) as amount_due
            ')
            ->first();

        // Payments received today for arrears - using loans_schedules
        $arrearsPaymentsToday = DB::table('loans_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
            ->whereDate('ls.last_payment_date', $today)
            ->where('ls.payment', '>', 0)
            ->where('l.days_in_arrears', '>', 0)
            ->selectRaw('
                COUNT(*) as payments_count,
                COALESCE(SUM(ls.payment), 0) as total_collected
            ')
            ->first();

        // Top 10 arrears accounts
        $topArrearsAccounts = DB::table('loans as l')
            ->join('clients as c', 'l.client_number', '=', 'c.client_number')
            ->whereIn('l.status', ['DISBURSED', 'ACTIVE'])
            ->where('l.days_in_arrears', '>', 0)
            ->orderByDesc(DB::raw('CAST(COALESCE(l.arrears_in_amount, \'0\') AS DECIMAL(15,2))'))
            ->limit(10)
            ->select([
                'l.loan_id',
                'l.loan_account_number',
                'c.full_name',
                'c.phone_number',
                'l.days_in_arrears',
                'l.arrears_in_amount',
                'l.supervisor_name'
            ])
            ->get()
            ->toArray();

        // Total portfolio for PAR calculation
        $totalPortfolio = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->sum(DB::raw('CAST(COALESCE(principle, 0) AS DECIMAL(15,2))'));

        // Calculate PAR ratios
        $totalArrears = $arrearsAging->total_arrears ?? 0;
        $parRatio = $totalPortfolio > 0 ? ($totalArrears / $totalPortfolio) * 100 : 0;

        return [
            'report_type' => 'Daily Arrears Report',
            'report_date' => $today,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_loans_in_arrears' => $arrearsAging->total_loans ?? 0,
                'total_arrears_amount' => number_format($arrearsAging->total_arrears ?? 0, 2),
                'total_portfolio' => number_format($totalPortfolio, 2),
                'par_ratio' => number_format($parRatio, 2) . '%',
            ],
            'aging_analysis' => [
                'par_1_30' => [
                    'count' => $arrearsAging->par_1_30 ?? 0,
                    'amount' => number_format($arrearsAging->par_1_30_amount ?? 0, 2),
                ],
                'par_31_60' => [
                    'count' => $arrearsAging->par_31_60 ?? 0,
                    'amount' => number_format($arrearsAging->par_31_60_amount ?? 0, 2),
                ],
                'par_61_90' => [
                    'count' => $arrearsAging->par_61_90 ?? 0,
                    'amount' => number_format($arrearsAging->par_61_90_amount ?? 0, 2),
                ],
                'par_over_90' => [
                    'count' => $arrearsAging->par_over_90 ?? 0,
                    'amount' => number_format($arrearsAging->par_over_90_amount ?? 0, 2),
                ],
            ],
            'today_activity' => [
                'new_arrears_loans' => $newArrearsToday->loans_count ?? 0,
                'new_arrears_amount' => number_format($newArrearsToday->amount_due ?? 0, 2),
                'arrears_payments_count' => $arrearsPaymentsToday->payments_count ?? 0,
                'arrears_payments_amount' => number_format($arrearsPaymentsToday->total_collected ?? 0, 2),
            ],
            'top_arrears_accounts' => $topArrearsAccounts,
        ];
    }

    /**
     * Daily Cash Position Report
     * Daily cash and bank balances including NBC account reconciliation
     */
    public function generateDailyCashPositionReport(): array
    {
        $today = $this->reportDate->format('Y-m-d');
        $yesterday = $this->reportDate->copy()->subDay()->format('Y-m-d');

        // Get all bank accounts
        $bankAccounts = DB::table('accounts')
            ->where('is_bank_account', true)
            ->orWhere('product_number', '0100') // Cash accounts
            ->select(['account_number', 'account_name', 'balance', 'type'])
            ->get();

        // Teller cash positions
        $tellerCash = DB::table('tellers as t')
            ->leftJoin('accounts as a', 't.account_id', '=', 'a.id')
            ->leftJoin('users as u', 't.user_id', '=', 'u.id')
            ->where('t.status', 'ACTIVE')
            ->select([
                DB::raw('COALESCE(u.name, t.teller_name) as teller_name'),
                'a.account_number',
                DB::raw('COALESCE(a.balance, 0) as current_balance'),
                't.transaction_limit as float_limit'
            ])
            ->get();

        // Vault balance
        $vaultBalance = DB::table('vaults')
            ->where('status', 'ACTIVE')
            ->sum('current_balance');

        // Cash in transit
        $cashInTransit = DB::table('cash_movements')
            ->where('status', 'IN_TRANSIT')
            ->sum('amount');

        // Today's cash inflows
        $cashInflows = DB::table('general_ledger')
            ->whereDate('created_at', $today)
            ->where('trans_status', 'Successful')
            ->where('credit', '>', 0)
            ->whereIn('beneficiary_product_id', ['0100', '0101']) // Cash accounts
            ->sum('credit');

        // Today's cash outflows
        $cashOutflows = DB::table('general_ledger')
            ->whereDate('created_at', $today)
            ->where('trans_status', 'Successful')
            ->where('debit', '>', 0)
            ->whereIn('sender_product_id', ['0100', '0101'])
            ->sum('debit');

        // Yesterday's closing balance for comparison
        // Using major_category_code for ASSET accounts (category 01) that are cash/bank
        $yesterdayBalance = DB::table('account_historical_balances')
            ->whereDate('snapshot_date', $yesterday)
            ->where('major_category_code', '01') // Assets
            ->whereIn('account_level', ['sub_product', 'product'])
            ->sum('balance');

        // Total liquid assets
        $totalCash = $bankAccounts->sum('balance') + $vaultBalance;

        return [
            'report_type' => 'Daily Cash Position Report',
            'report_date' => $today,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_cash_and_bank' => number_format($totalCash, 2),
                'yesterday_closing' => number_format($yesterdayBalance, 2),
                'today_inflows' => number_format($cashInflows, 2),
                'today_outflows' => number_format($cashOutflows, 2),
                'net_movement' => number_format($cashInflows - $cashOutflows, 2),
            ],
            'bank_accounts' => $bankAccounts->map(function ($acc) {
                return [
                    'account_number' => $acc->account_number,
                    'account_name' => $acc->account_name,
                    'balance' => number_format($acc->balance, 2),
                ];
            })->toArray(),
            'teller_positions' => $tellerCash->map(function ($t) {
                return [
                    'teller_name' => $t->teller_name,
                    'account_number' => $t->account_number ?? 'N/A',
                    'current_balance' => number_format($t->current_balance ?? 0, 2),
                    'transaction_limit' => number_format($t->float_limit ?? 0, 2),
                ];
            })->toArray(),
            'vault_balance' => number_format($vaultBalance, 2),
            'cash_in_transit' => number_format($cashInTransit, 2),
        ];
    }

    /**
     * Daily Loan Disbursements Report
     */
    public function generateDailyDisbursementsReport(): array
    {
        $today = $this->reportDate->format('Y-m-d');

        // Today's disbursements
        $disbursements = DB::table('loans as l')
            ->join('clients as c', 'l.client_number', '=', 'c.client_number')
            ->leftJoin('sub_products as sp', 'l.loan_sub_product', '=', 'sp.sub_product_id')
            ->whereDate('l.disbursement_date', $today)
            ->where('l.status', 'DISBURSED')
            ->select([
                'l.loan_id',
                'l.loan_account_number',
                'c.full_name as member_name',
                'c.phone_number',
                'sp.sub_product_name as product_name',
                'l.principle',
                'l.interest',
                'l.tenure',
                'l.disbursement_method',
                'l.supervisor_name as loan_officer',
                'l.disbursement_date'
            ])
            ->orderByDesc('l.principle')
            ->get();

        // Summary by product
        $byProduct = DB::table('loans as l')
            ->leftJoin('sub_products as sp', 'l.loan_sub_product', '=', 'sp.sub_product_id')
            ->whereDate('l.disbursement_date', $today)
            ->where('l.status', 'DISBURSED')
            ->groupBy('sp.sub_product_name')
            ->selectRaw('
                sp.sub_product_name as product_name,
                COUNT(*) as count,
                SUM(CAST(l.principle AS DECIMAL(15,2))) as total_amount
            ')
            ->get();

        // Summary by loan officer
        $byOfficer = DB::table('loans')
            ->whereDate('disbursement_date', $today)
            ->where('status', 'DISBURSED')
            ->groupBy('supervisor_name')
            ->selectRaw('
                supervisor_name as loan_officer,
                COUNT(*) as count,
                SUM(CAST(principle AS DECIMAL(15,2))) as total_amount
            ')
            ->get();

        // Summary by disbursement method
        $byMethod = DB::table('loans')
            ->whereDate('disbursement_date', $today)
            ->where('status', 'DISBURSED')
            ->groupBy('disbursement_method')
            ->selectRaw('
                COALESCE(disbursement_method, \'Not Specified\') as method,
                COUNT(*) as count,
                SUM(CAST(principle AS DECIMAL(15,2))) as total_amount
            ')
            ->get();

        $totalDisbursed = $disbursements->sum(function ($d) {
            return floatval($d->principle);
        });

        return [
            'report_type' => 'Daily Loan Disbursements Report',
            'report_date' => $today,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_loans' => $disbursements->count(),
                'total_amount' => number_format($totalDisbursed, 2),
                'average_loan_size' => $disbursements->count() > 0
                    ? number_format($totalDisbursed / $disbursements->count(), 2)
                    : '0.00',
            ],
            'by_product' => $byProduct->map(function ($p) {
                return [
                    'product_name' => $p->product_name ?? 'Unknown',
                    'count' => $p->count,
                    'total_amount' => number_format($p->total_amount, 2),
                ];
            })->toArray(),
            'by_loan_officer' => $byOfficer->map(function ($o) {
                return [
                    'loan_officer' => $o->loan_officer ?? 'Unknown',
                    'count' => $o->count,
                    'total_amount' => number_format($o->total_amount, 2),
                ];
            })->toArray(),
            'by_method' => $byMethod->map(function ($m) {
                return [
                    'method' => $m->method,
                    'count' => $m->count,
                    'total_amount' => number_format($m->total_amount, 2),
                ];
            })->toArray(),
            'disbursements' => $disbursements->map(function ($d) {
                return [
                    'loan_id' => $d->loan_id,
                    'account_number' => $d->loan_account_number,
                    'member_name' => $d->member_name,
                    'phone' => $d->phone_number,
                    'product' => $d->product_name,
                    'principal' => number_format($d->principle, 2),
                    'tenure' => $d->tenure . ' months',
                    'method' => $d->disbursement_method,
                    'loan_officer' => $d->loan_officer,
                ];
            })->toArray(),
        ];
    }

    /**
     * Daily Collections Report
     * Uses loans_schedules table for loan repayment data
     */
    public function generateDailyCollectionsReport(): array
    {
        $today = $this->reportDate->format('Y-m-d');

        // Today's loan payments from loans_schedules
        $payments = DB::table('loans_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
            ->join('clients as c', 'l.client_number', '=', 'c.client_number')
            ->whereDate('ls.last_payment_date', $today)
            ->where('ls.payment', '>', 0)
            ->select([
                'ls.loan_id',
                'l.loan_account_number',
                'c.full_name as member_name',
                'ls.payment as amount',
                'ls.principle_payment as principal_paid',
                'ls.interest_payment as interest_paid',
                DB::raw('CASE WHEN ls.penalties > 0 AND ls.payment > (COALESCE(ls.principle_payment, 0) + COALESCE(ls.interest_payment, 0))
                    THEN ls.payment - COALESCE(ls.principle_payment, 0) - COALESCE(ls.interest_payment, 0) ELSE 0 END as penalties_paid'),
                DB::raw("'Bank Transfer' as payment_method"),
                'l.loan_account_number as reference_number',
                'l.supervisor_name as loan_officer'
            ])
            ->get();

        // Summary totals from loans_schedules
        $summary = DB::table('loans_schedules')
            ->whereDate('last_payment_date', $today)
            ->where('payment', '>', 0)
            ->selectRaw('
                COUNT(*) as total_payments,
                COALESCE(SUM(payment), 0) as total_collected,
                COALESCE(SUM(principle_payment), 0) as principal_collected,
                COALESCE(SUM(interest_payment), 0) as interest_collected,
                COALESCE(SUM(CASE WHEN penalties > 0 AND payment > (COALESCE(principle_payment, 0) + COALESCE(interest_payment, 0))
                    THEN payment - COALESCE(principle_payment, 0) - COALESCE(interest_payment, 0) ELSE 0 END), 0) as penalties_collected
            ')
            ->first();

        // By payment method - since loans_schedules doesn't track method, group by loan sub product
        $byMethod = DB::table('loans_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
            ->leftJoin('sub_products as sp', 'l.loan_sub_product', '=', 'sp.sub_product_id')
            ->whereDate('ls.last_payment_date', $today)
            ->where('ls.payment', '>', 0)
            ->groupBy('sp.sub_product_name')
            ->selectRaw('
                COALESCE(sp.sub_product_name, \'Unknown\') as payment_method,
                COUNT(*) as count,
                COALESCE(SUM(ls.payment), 0) as total_amount
            ')
            ->get();

        // By loan officer
        $byOfficer = DB::table('loans_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
            ->whereDate('ls.last_payment_date', $today)
            ->where('ls.payment', '>', 0)
            ->groupBy('l.supervisor_name')
            ->selectRaw('
                l.supervisor_name as loan_officer,
                COUNT(*) as count,
                COALESCE(SUM(ls.payment), 0) as total_amount
            ')
            ->get();

        // Expected vs Collected (from schedules due today)
        $expectedToday = DB::table('loans_schedules')
            ->whereDate('installment_date', $today)
            ->where('status', '!=', 'Paid')
            ->selectRaw('
                COUNT(*) as due_count,
                SUM(principle + interest) as due_amount
            ')
            ->first();

        return [
            'report_type' => 'Daily Collections Report',
            'report_date' => $today,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_payments' => $summary->total_payments ?? 0,
                'total_collected' => number_format($summary->total_collected ?? 0, 2),
                'principal_collected' => number_format($summary->principal_collected ?? 0, 2),
                'interest_collected' => number_format($summary->interest_collected ?? 0, 2),
                'penalties_collected' => number_format($summary->penalties_collected ?? 0, 2),
            ],
            'expected_vs_actual' => [
                'expected_count' => $expectedToday->due_count ?? 0,
                'expected_amount' => number_format($expectedToday->due_amount ?? 0, 2),
                'actual_collected' => number_format($summary->total_collected ?? 0, 2),
                'collection_rate' => ($expectedToday->due_amount ?? 0) > 0
                    ? number_format((($summary->total_collected ?? 0) / $expectedToday->due_amount) * 100, 1) . '%'
                    : 'N/A',
            ],
            'by_payment_method' => $byMethod->map(function ($m) {
                return [
                    'method' => $m->payment_method ?? 'Unknown',
                    'count' => $m->count,
                    'total_amount' => number_format($m->total_amount, 2),
                ];
            })->toArray(),
            'by_loan_officer' => $byOfficer->map(function ($o) {
                return [
                    'loan_officer' => $o->loan_officer ?? 'Unknown',
                    'count' => $o->count,
                    'total_amount' => number_format($o->total_amount, 2),
                ];
            })->toArray(),
            'payments' => $payments->take(50)->map(function ($p) {
                return [
                    'loan_id' => $p->loan_id,
                    'account_number' => $p->loan_account_number,
                    'member_name' => $p->member_name,
                    'amount' => number_format($p->amount, 2),
                    'principal' => number_format($p->principal_paid, 2),
                    'interest' => number_format($p->interest_paid, 2),
                    'method' => $p->payment_method,
                    'reference' => $p->reference_number,
                    'receipt' => $p->receipt_number,
                ];
            })->toArray(),
        ];
    }

    /**
     * Daily Member Activity Report
     */
    public function generateDailyMemberActivityReport(): array
    {
        $today = $this->reportDate->format('Y-m-d');

        // New member registrations today
        $newMembers = DB::table('clients')
            ->whereDate('created_at', $today)
            ->select([
                'client_number',
                'full_name',
                'phone_number',
                'email',
                'branch',
                'membership_type',
                'registering_officer'
            ])
            ->get();

        // New accounts opened today
        $newAccounts = DB::table('accounts as a')
            ->join('clients as c', 'a.client_number', '=', 'c.client_number')
            ->whereDate('a.created_at', $today)
            ->select([
                'a.account_number',
                'a.account_name',
                'a.type',
                'c.full_name',
                'a.balance'
            ])
            ->get();

        // Deposits by members today
        $deposits = DB::table('general_ledger as gl')
            ->leftJoin('clients as c', 'gl.beneficiary_id', '=', 'c.id')
            ->whereDate('gl.created_at', $today)
            ->where('gl.trans_status', 'Successful')
            ->where('gl.credit', '>', 0)
            ->whereIn('gl.beneficiary_product_id', ['2000', '2200', '3000', '1000'])
            ->groupBy('gl.beneficiary_id', 'c.full_name', 'c.client_number')
            ->selectRaw('
                c.client_number,
                c.full_name,
                COUNT(*) as transaction_count,
                SUM(gl.credit) as total_deposits
            ')
            ->orderByDesc('total_deposits')
            ->limit(20)
            ->get();

        // Withdrawals by members today
        $withdrawals = DB::table('general_ledger as gl')
            ->leftJoin('clients as c', 'gl.sender_id', '=', 'c.id')
            ->whereDate('gl.created_at', $today)
            ->where('gl.trans_status', 'Successful')
            ->where('gl.debit', '>', 0)
            ->whereIn('gl.sender_product_id', ['2000', '2200', '3000'])
            ->groupBy('gl.sender_id', 'c.full_name', 'c.client_number')
            ->selectRaw('
                c.client_number,
                c.full_name,
                COUNT(*) as transaction_count,
                SUM(gl.debit) as total_withdrawals
            ')
            ->orderByDesc('total_withdrawals')
            ->limit(20)
            ->get();

        // Summary stats
        $depositsSummary = DB::table('general_ledger')
            ->whereDate('created_at', $today)
            ->where('trans_status', 'Successful')
            ->where('credit', '>', 0)
            ->whereIn('beneficiary_product_id', ['2000', '2200', '3000', '1000'])
            ->selectRaw('COUNT(*) as count, SUM(credit) as total')
            ->first();

        $withdrawalsSummary = DB::table('general_ledger')
            ->whereDate('created_at', $today)
            ->where('trans_status', 'Successful')
            ->where('debit', '>', 0)
            ->whereIn('sender_product_id', ['2000', '2200', '3000'])
            ->selectRaw('COUNT(*) as count, SUM(debit) as total')
            ->first();

        return [
            'report_type' => 'Daily Member Activity Report',
            'report_date' => $today,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'new_members' => $newMembers->count(),
                'new_accounts' => $newAccounts->count(),
                'total_deposits' => number_format($depositsSummary->total ?? 0, 2),
                'deposit_transactions' => $depositsSummary->count ?? 0,
                'total_withdrawals' => number_format($withdrawalsSummary->total ?? 0, 2),
                'withdrawal_transactions' => $withdrawalsSummary->count ?? 0,
                'net_deposits' => number_format(($depositsSummary->total ?? 0) - ($withdrawalsSummary->total ?? 0), 2),
            ],
            'new_members' => $newMembers->map(function ($m) {
                return [
                    'client_number' => $m->client_number,
                    'name' => $m->full_name,
                    'phone' => $m->phone_number,
                    'email' => $m->email,
                    'branch' => $m->branch,
                    'type' => $m->membership_type,
                    'registered_by' => $m->registering_officer,
                ];
            })->toArray(),
            'new_accounts' => $newAccounts->map(function ($a) {
                return [
                    'account_number' => $a->account_number,
                    'account_name' => $a->account_name,
                    'type' => $a->type,
                    'member_name' => $a->full_name,
                    'opening_balance' => number_format($a->balance, 2),
                ];
            })->toArray(),
            'top_depositors' => $deposits->map(function ($d) {
                return [
                    'client_number' => $d->client_number,
                    'name' => $d->full_name,
                    'transactions' => $d->transaction_count,
                    'total_deposits' => number_format($d->total_deposits, 2),
                ];
            })->toArray(),
        ];
    }

    /**
     * Daily Loan Officer Portfolio Report
     */
    public function generateDailyLoanOfficerPortfolioReport(): array
    {
        $today = $this->reportDate->format('Y-m-d');

        // Get portfolio by loan officer
        $portfolios = DB::table('loans as l')
            ->join('users as u', 'l.supervisor_id', '=', 'u.id')
            ->whereIn('l.status', ['DISBURSED', 'ACTIVE'])
            ->groupBy('l.supervisor_id', 'u.name', 'l.supervisor_name')
            ->selectRaw('
                l.supervisor_id,
                COALESCE(u.name, l.supervisor_name) as officer_name,
                COUNT(*) as total_loans,
                SUM(CAST(l.principle AS DECIMAL(15,2))) as total_portfolio,
                SUM(CASE WHEN l.days_in_arrears > 0 THEN 1 ELSE 0 END) as loans_in_arrears,
                SUM(CASE WHEN l.days_in_arrears > 0 THEN CAST(COALESCE(l.arrears_in_amount, \'0\') AS DECIMAL(15,2)) ELSE 0 END) as arrears_amount,
                SUM(CASE WHEN l.days_in_arrears > 30 THEN 1 ELSE 0 END) as par30_loans
            ')
            ->get();

        // Today's actions needed per officer
        $actionsNeeded = DB::table('loans_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
            ->whereDate('ls.installment_date', '<=', $today)
            ->where('ls.status', '!=', 'Paid')
            ->groupBy('l.supervisor_id', 'l.supervisor_name')
            ->selectRaw('
                l.supervisor_id,
                l.supervisor_name,
                COUNT(DISTINCT l.loan_id) as loans_due,
                SUM(ls.principle + ls.interest) as amount_due
            ')
            ->get()
            ->keyBy('supervisor_id');

        // Collections today per officer - using loans_schedules
        $todayCollections = DB::table('loans_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
            ->whereDate('ls.last_payment_date', $today)
            ->where('ls.payment', '>', 0)
            ->groupBy('l.supervisor_id')
            ->selectRaw('
                l.supervisor_id,
                COUNT(*) as payments_count,
                COALESCE(SUM(ls.payment), 0) as collected_amount
            ')
            ->get()
            ->keyBy('supervisor_id');

        return [
            'report_type' => 'Daily Loan Officer Portfolio',
            'report_date' => $today,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'officers' => $portfolios->map(function ($p) use ($actionsNeeded, $todayCollections) {
                $action = $actionsNeeded->get($p->supervisor_id);
                $collection = $todayCollections->get($p->supervisor_id);
                $parRatio = $p->total_portfolio > 0
                    ? ($p->arrears_amount / $p->total_portfolio) * 100
                    : 0;

                return [
                    'officer_id' => $p->supervisor_id,
                    'officer_name' => $p->officer_name,
                    'portfolio' => [
                        'total_loans' => $p->total_loans,
                        'total_value' => number_format($p->total_portfolio, 2),
                        'loans_in_arrears' => $p->loans_in_arrears,
                        'arrears_amount' => number_format($p->arrears_amount, 2),
                        'par30_loans' => $p->par30_loans,
                        'par_ratio' => number_format($parRatio, 2) . '%',
                    ],
                    'today_actions' => [
                        'loans_due' => $action->loans_due ?? 0,
                        'amount_due' => number_format($action->amount_due ?? 0, 2),
                    ],
                    'today_collections' => [
                        'payments_count' => $collection->payments_count ?? 0,
                        'collected_amount' => number_format($collection->collected_amount ?? 0, 2),
                    ],
                ];
            })->toArray(),
        ];
    }

    /**
     * Daily Recovery Action List
     */
    public function generateDailyRecoveryActionReport(): array
    {
        $today = $this->reportDate->format('Y-m-d');

        // Loans requiring follow-up (overdue)
        $overdueLoans = DB::table('loans as l')
            ->join('clients as c', 'l.client_number', '=', 'c.client_number')
            ->whereIn('l.status', ['DISBURSED', 'ACTIVE'])
            ->where('l.days_in_arrears', '>', 0)
            ->orderByDesc('l.days_in_arrears')
            ->select([
                'l.loan_id',
                'l.loan_account_number',
                'c.full_name',
                'c.phone_number',
                'c.email',
                'l.principle',
                'l.days_in_arrears',
                'l.arrears_in_amount',
                'l.loan_classification',
                'l.supervisor_name as loan_officer'
            ])
            ->limit(100)
            ->get();

        // Group by classification/urgency
        $byUrgency = [
            'critical' => $overdueLoans->filter(fn($l) => $l->days_in_arrears > 90)->count(),
            'high' => $overdueLoans->filter(fn($l) => $l->days_in_arrears > 60 && $l->days_in_arrears <= 90)->count(),
            'medium' => $overdueLoans->filter(fn($l) => $l->days_in_arrears > 30 && $l->days_in_arrears <= 60)->count(),
            'low' => $overdueLoans->filter(fn($l) => $l->days_in_arrears <= 30)->count(),
        ];

        // Promises due today
        $promisesDue = DB::table('loans_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
            ->join('clients as c', 'l.client_number', '=', 'c.client_number')
            ->whereDate('ls.promise_date', $today)
            ->whereNotNull('ls.promise_date')
            ->select([
                'l.loan_id',
                'l.loan_account_number',
                'c.full_name',
                'c.phone_number',
                'ls.principle',
                'ls.interest',
                'ls.comment as promise_notes'
            ])
            ->get();

        return [
            'report_type' => 'Daily Recovery Action List',
            'report_date' => $today,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_overdue_loans' => $overdueLoans->count(),
                'total_arrears_amount' => number_format($overdueLoans->sum(fn($l) => floatval($l->arrears_in_amount ?? 0)), 2),
                'promises_due_today' => $promisesDue->count(),
            ],
            'by_urgency' => $byUrgency,
            'action_items' => $overdueLoans->map(function ($l) {
                $urgency = 'Low';
                if ($l->days_in_arrears > 90) $urgency = 'Critical';
                elseif ($l->days_in_arrears > 60) $urgency = 'High';
                elseif ($l->days_in_arrears > 30) $urgency = 'Medium';

                return [
                    'loan_id' => $l->loan_id,
                    'account_number' => $l->loan_account_number,
                    'member_name' => $l->full_name,
                    'phone' => $l->phone_number,
                    'email' => $l->email,
                    'days_overdue' => $l->days_in_arrears,
                    'arrears_amount' => number_format($l->arrears_in_amount ?? 0, 2),
                    'classification' => $l->loan_classification,
                    'loan_officer' => $l->loan_officer,
                    'urgency' => $urgency,
                    'recommended_action' => $this->getRecommendedAction($l->days_in_arrears),
                ];
            })->toArray(),
            'promises_due' => $promisesDue->map(function ($p) {
                return [
                    'loan_id' => $p->loan_id,
                    'account_number' => $p->loan_account_number,
                    'member_name' => $p->full_name,
                    'phone' => $p->phone_number,
                    'promised_amount' => number_format($p->principal + $p->interest, 2),
                    'notes' => $p->promise_notes,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get recommended action based on days in arrears
     */
    protected function getRecommendedAction($daysInArrears): string
    {
        if ($daysInArrears > 90) {
            return 'Legal action / Write-off consideration';
        } elseif ($daysInArrears > 60) {
            return 'Formal demand letter / Collateral seizure preparation';
        } elseif ($daysInArrears > 30) {
            return 'Physical visit / Guarantor contact';
        } elseif ($daysInArrears > 7) {
            return 'Phone follow-up / SMS reminder';
        } else {
            return 'Friendly reminder call';
        }
    }

    /**
     * Daily GL Posting Summary
     */
    public function generateDailyGLSummaryReport(): array
    {
        $today = $this->reportDate->format('Y-m-d');

        // Today's GL entries summary
        $glSummary = DB::table('general_ledger')
            ->whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*) as total_entries,
                SUM(credit) as total_credits,
                SUM(debit) as total_debits,
                COUNT(CASE WHEN trans_status = \'Successful\' THEN 1 END) as successful,
                COUNT(CASE WHEN trans_status = \'Pending\' THEN 1 END) as pending,
                COUNT(CASE WHEN trans_status = \'Failed\' THEN 1 END) as failed
            ')
            ->first();

        // By transaction type
        $byType = DB::table('general_ledger')
            ->whereDate('created_at', $today)
            ->groupBy('transaction_type')
            ->selectRaw('
                transaction_type,
                COUNT(*) as count,
                SUM(credit) as total_credits,
                SUM(debit) as total_debits
            ')
            ->get();

        // Journal entries today
        $journalEntries = DB::table('journal_entries')
            ->whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN is_posted = true THEN 1 END) as posted,
                COUNT(CASE WHEN is_posted = false THEN 1 END) as unposted
            ')
            ->first();

        // Unposted transactions
        $unpostedTransactions = DB::table('general_ledger')
            ->whereDate('created_at', $today)
            ->where('trans_status', 'Pending')
            ->select(['id', 'reference_number', 'sender_account_number', 'beneficiary_account_number', 'credit', 'debit', 'narration'])
            ->limit(20)
            ->get();

        return [
            'report_type' => 'Daily GL Posting Summary',
            'report_date' => $today,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_entries' => $glSummary->total_entries ?? 0,
                'total_credits' => number_format($glSummary->total_credits ?? 0, 2),
                'total_debits' => number_format($glSummary->total_debits ?? 0, 2),
                'balance_check' => abs(($glSummary->total_credits ?? 0) - ($glSummary->total_debits ?? 0)) < 0.01 ? 'BALANCED' : 'UNBALANCED',
                'successful_entries' => $glSummary->successful ?? 0,
                'pending_entries' => $glSummary->pending ?? 0,
                'failed_entries' => $glSummary->failed ?? 0,
            ],
            'journal_entries' => [
                'total' => $journalEntries->total ?? 0,
                'posted' => $journalEntries->posted ?? 0,
                'unposted' => $journalEntries->unposted ?? 0,
            ],
            'by_transaction_type' => $byType->map(function ($t) {
                return [
                    'type' => $t->transaction_type ?? 'Unknown',
                    'count' => $t->count,
                    'credits' => number_format($t->total_credits ?? 0, 2),
                    'debits' => number_format($t->total_debits ?? 0, 2),
                ];
            })->toArray(),
            'unposted_transactions' => $unpostedTransactions->map(function ($t) {
                return [
                    'id' => $t->id,
                    'reference' => $t->reference_number,
                    'from_account' => $t->sender_account_number,
                    'to_account' => $t->beneficiary_account_number,
                    'amount' => number_format(max($t->credit, $t->debit), 2),
                    'narration' => $t->narration,
                ];
            })->toArray(),
        ];
    }

    /**
     * Daily Large Transactions Report (AML)
     */
    public function generateDailyLargeTransactionsReport(): array
    {
        $today = $this->reportDate->format('Y-m-d');
        $threshold = 10000000; // TZS 10 million AML threshold

        // Large transactions today
        $largeTransactions = DB::table('transactions as t')
            ->leftJoin('clients as c', 't.client_number', '=', 'c.client_number')
            ->whereDate('t.created_at', $today)
            ->where('t.amount', '>=', $threshold)
            ->select([
                't.transaction_uuid',
                't.client_number',
                'c.full_name',
                'c.phone_number',
                't.amount',
                't.type',
                't.source',
                't.narration',
                't.payment_type',
                't.payer_name',
                't.status',
                't.created_at'
            ])
            ->orderByDesc('t.amount')
            ->get();

        // Also check GL for large movements
        $largeGLEntries = DB::table('general_ledger as gl')
            ->leftJoin('clients as c', 'gl.beneficiary_id', '=', 'c.id')
            ->whereDate('gl.created_at', $today)
            ->where(function ($q) use ($threshold) {
                $q->where('gl.credit', '>=', $threshold)
                    ->orWhere('gl.debit', '>=', $threshold);
            })
            ->select([
                'gl.reference_number',
                'c.client_number',
                'c.full_name',
                'gl.credit',
                'gl.debit',
                'gl.transaction_type',
                'gl.narration',
                'gl.beneficiary_account_number',
                'gl.sender_account_number'
            ])
            ->orderByDesc(DB::raw('GREATEST(gl.credit, gl.debit)'))
            ->limit(50)
            ->get();

        // Structuring detection (multiple transactions just under threshold)
        $potentialStructuring = DB::table('transactions as t')
            ->whereDate('t.created_at', $today)
            ->where('t.amount', '>=', $threshold * 0.8)
            ->where('t.amount', '<', $threshold)
            ->groupBy('t.client_number')
            ->havingRaw('COUNT(*) > 1')
            ->selectRaw('
                t.client_number,
                COUNT(*) as transaction_count,
                SUM(t.amount) as total_amount
            ')
            ->get();

        return [
            'report_type' => 'Daily Large Transactions Report',
            'report_date' => $today,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'aml_threshold' => number_format($threshold, 2),
            'summary' => [
                'large_transactions_count' => $largeTransactions->count(),
                'large_gl_entries_count' => $largeGLEntries->count(),
                'potential_structuring_cases' => $potentialStructuring->count(),
                'total_large_amounts' => number_format($largeTransactions->sum('amount'), 2),
            ],
            'large_transactions' => $largeTransactions->map(function ($t) {
                return [
                    'transaction_id' => $t->transaction_uuid,
                    'client_number' => $t->client_number,
                    'client_name' => $t->full_name ?? 'Unknown',
                    'phone' => $t->phone_number,
                    'amount' => number_format($t->amount, 2),
                    'type' => $t->type,
                    'source' => $t->source,
                    'narration' => $t->narration,
                    'status' => $t->status,
                    'time' => Carbon::parse($t->created_at)->format('H:i:s'),
                ];
            })->toArray(),
            'large_gl_entries' => $largeGLEntries->map(function ($gl) {
                return [
                    'reference' => $gl->reference_number,
                    'client_name' => $gl->full_name ?? 'Unknown',
                    'amount' => number_format(max($gl->credit, $gl->debit), 2),
                    'type' => $gl->credit > 0 ? 'CREDIT' : 'DEBIT',
                    'transaction_type' => $gl->transaction_type,
                    'narration' => $gl->narration,
                ];
            })->toArray(),
            'potential_structuring' => $potentialStructuring->map(function ($s) {
                return [
                    'client_number' => $s->client_number,
                    'transaction_count' => $s->transaction_count,
                    'total_amount' => number_format($s->total_amount, 2),
                    'flag' => 'REVIEW REQUIRED - Multiple transactions near threshold',
                ];
            })->toArray(),
        ];
    }

    // ========================================
    // WEEKLY REPORTS
    // ========================================

    /**
     * Weekly Executive Summary
     */
    public function generateWeeklyExecutiveSummaryReport(): array
    {
        $weekEnd = $this->reportDate->copy()->endOfWeek();
        $weekStart = $this->reportDate->copy()->startOfWeek();
        $prevWeekStart = $weekStart->copy()->subWeek();
        $prevWeekEnd = $weekEnd->copy()->subWeek();

        // This week's metrics
        $thisWeek = $this->getWeeklyMetrics($weekStart, $weekEnd);
        $prevWeek = $this->getWeeklyMetrics($prevWeekStart, $prevWeekEnd);

        // Portfolio summary
        $portfolio = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->selectRaw('
                COUNT(*) as total_loans,
                SUM(CAST(principle AS DECIMAL(15,2))) as total_portfolio,
                SUM(CASE WHEN days_in_arrears > 0 THEN 1 ELSE 0 END) as loans_in_arrears,
                SUM(CASE WHEN days_in_arrears > 0 THEN CAST(COALESCE(arrears_in_amount, \'0\') AS DECIMAL(15,2)) ELSE 0 END) as arrears_amount
            ')
            ->first();

        // Member summary
        $members = DB::table('clients')
            ->selectRaw('
                COUNT(*) as total_members,
                COUNT(CASE WHEN client_status = \'ACTIVE\' THEN 1 END) as active_members
            ')
            ->first();

        // Savings summary
        $savings = DB::table('accounts')
            ->whereIn('type', ['SAV', 'DEP'])
            ->where('status', 'ACTIVE')
            ->selectRaw('
                COUNT(*) as total_accounts,
                SUM(balance) as total_balance
            ')
            ->first();

        return [
            'report_type' => 'Weekly Executive Summary',
            'week_period' => $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'kpi_summary' => [
                'disbursements' => [
                    'this_week' => number_format($thisWeek['disbursements'], 2),
                    'last_week' => number_format($prevWeek['disbursements'], 2),
                    'change' => $this->calculateChange($thisWeek['disbursements'], $prevWeek['disbursements']),
                ],
                'collections' => [
                    'this_week' => number_format($thisWeek['collections'], 2),
                    'last_week' => number_format($prevWeek['collections'], 2),
                    'change' => $this->calculateChange($thisWeek['collections'], $prevWeek['collections']),
                ],
                'deposits' => [
                    'this_week' => number_format($thisWeek['deposits'], 2),
                    'last_week' => number_format($prevWeek['deposits'], 2),
                    'change' => $this->calculateChange($thisWeek['deposits'], $prevWeek['deposits']),
                ],
                'new_members' => [
                    'this_week' => $thisWeek['new_members'],
                    'last_week' => $prevWeek['new_members'],
                    'change' => $this->calculateChange($thisWeek['new_members'], $prevWeek['new_members']),
                ],
            ],
            'portfolio_status' => [
                'total_loans' => $portfolio->total_loans ?? 0,
                'total_portfolio' => number_format($portfolio->total_portfolio ?? 0, 2),
                'loans_in_arrears' => $portfolio->loans_in_arrears ?? 0,
                'arrears_amount' => number_format($portfolio->arrears_amount ?? 0, 2),
                'par_ratio' => $portfolio->total_portfolio > 0
                    ? number_format(($portfolio->arrears_amount / $portfolio->total_portfolio) * 100, 2) . '%'
                    : '0.00%',
            ],
            'membership_status' => [
                'total_members' => $members->total_members ?? 0,
                'active_members' => $members->active_members ?? 0,
            ],
            'savings_status' => [
                'total_accounts' => $savings->total_accounts ?? 0,
                'total_balance' => number_format($savings->total_balance ?? 0, 2),
            ],
        ];
    }

    /**
     * Get weekly metrics helper
     */
    protected function getWeeklyMetrics($start, $end): array
    {
        $disbursements = DB::table('loans')
            ->whereBetween('disbursement_date', [$start, $end])
            ->where('status', 'DISBURSED')
            ->sum(DB::raw('CAST(principle AS DECIMAL(15,2))'));

        $collections = DB::table('loans_schedules')
            ->whereBetween('last_payment_date', [$start, $end])
            ->where('payment', '>', 0)
            ->sum('payment');

        $deposits = DB::table('general_ledger')
            ->whereBetween('created_at', [$start, $end])
            ->where('trans_status', 'Successful')
            ->where('credit', '>', 0)
            ->whereIn('beneficiary_product_id', ['2000', '2200', '3000'])
            ->sum('credit');

        $newMembers = DB::table('clients')
            ->whereBetween('created_at', [$start, $end])
            ->where('client_status', 'ACTIVE')
            ->count();

        return [
            'disbursements' => $disbursements ?? 0,
            'collections' => $collections ?? 0,
            'deposits' => $deposits ?? 0,
            'new_members' => $newMembers,
        ];
    }

    /**
     * Calculate percentage change
     */
    protected function calculateChange($current, $previous): string
    {
        if ($previous == 0) {
            return $current > 0 ? '+100%' : '0%';
        }
        $change = (($current - $previous) / $previous) * 100;
        return ($change >= 0 ? '+' : '') . number_format($change, 1) . '%';
    }

    /**
     * Weekly Arrears Analysis
     */
    public function generateWeeklyArrearsReport(): array
    {
        $weekEnd = $this->reportDate->copy()->endOfWeek();
        $weekStart = $this->reportDate->copy()->startOfWeek();

        // Current arrears position
        $currentArrears = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->where('days_in_arrears', '>', 0)
            ->selectRaw('
                COUNT(*) as total_loans,
                SUM(CAST(COALESCE(arrears_in_amount, \'0\') AS DECIMAL(15,2))) as total_arrears,
                SUM(CASE WHEN npl_classification = \'WATCH\' THEN 1 ELSE 0 END) as watch_count,
                SUM(CASE WHEN npl_classification = \'SUBSTANDARD\' THEN 1 ELSE 0 END) as substandard_count,
                SUM(CASE WHEN npl_classification = \'DOUBTFUL\' THEN 1 ELSE 0 END) as doubtful_count,
                SUM(CASE WHEN npl_classification = \'LOSS\' THEN 1 ELSE 0 END) as loss_count
            ')
            ->first();

        // Collections this week on arrears - using loans_schedules
        $arrearsCollected = DB::table('loans_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
            ->whereBetween('ls.last_payment_date', [$weekStart, $weekEnd])
            ->where('ls.payment', '>', 0)
            ->selectRaw('
                COUNT(*) as payments,
                COALESCE(SUM(ls.payment), 0) as collected
            ')
            ->first();

        // New loans entering arrears this week
        $newInArrears = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->where('days_in_arrears', '>', 0)
            ->where('days_in_arrears', '<=', 7)
            ->count();

        // PAR by loan officer
        $parByOfficer = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->groupBy('supervisor_name')
            ->selectRaw('
                supervisor_name as loan_officer,
                COUNT(*) as total_loans,
                SUM(CAST(principle AS DECIMAL(15,2))) as portfolio,
                SUM(CASE WHEN days_in_arrears > 0 THEN 1 ELSE 0 END) as arrears_loans,
                SUM(CASE WHEN days_in_arrears > 0 THEN CAST(COALESCE(arrears_in_amount, \'0\') AS DECIMAL(15,2)) ELSE 0 END) as arrears_amount
            ')
            ->havingRaw('SUM(CASE WHEN days_in_arrears > 0 THEN 1 ELSE 0 END) > 0')
            ->orderByDesc('arrears_amount')
            ->get();

        return [
            'report_type' => 'Weekly Arrears Analysis',
            'week_period' => $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'current_position' => [
                'total_loans_in_arrears' => $currentArrears->total_loans ?? 0,
                'total_arrears_amount' => number_format($currentArrears->total_arrears ?? 0, 2),
            ],
            'npl_classification' => [
                'watch' => $currentArrears->watch_count ?? 0,
                'substandard' => $currentArrears->substandard_count ?? 0,
                'doubtful' => $currentArrears->doubtful_count ?? 0,
                'loss' => $currentArrears->loss_count ?? 0,
            ],
            'week_activity' => [
                'collections_count' => $arrearsCollected->payments ?? 0,
                'collections_amount' => number_format($arrearsCollected->collected ?? 0, 2),
                'new_in_arrears' => $newInArrears,
            ],
            'par_by_officer' => $parByOfficer->map(function ($o) {
                return [
                    'officer' => $o->loan_officer ?? 'Unknown',
                    'total_loans' => $o->total_loans,
                    'portfolio' => number_format($o->portfolio, 2),
                    'arrears_loans' => $o->arrears_loans,
                    'arrears_amount' => number_format($o->arrears_amount, 2),
                    'par_ratio' => $o->portfolio > 0
                        ? number_format(($o->arrears_amount / $o->portfolio) * 100, 2) . '%'
                        : '0.00%',
                ];
            })->toArray(),
        ];
    }

    /**
     * Weekly Credit Committee Report
     */
    public function generateWeeklyCreditCommitteeReport(): array
    {
        $weekEnd = $this->reportDate->copy()->endOfWeek();
        $weekStart = $this->reportDate->copy()->startOfWeek();

        // Loans approved this week
        $approved = DB::table('loans')
            ->whereBetween('approved_at', [$weekStart, $weekEnd])
            ->where('status', '!=', 'DECLINED')
            ->selectRaw('
                COUNT(*) as count,
                SUM(CAST(principle AS DECIMAL(15,2))) as total_amount,
                AVG(CAST(principle AS DECIMAL(15,2))) as avg_amount
            ')
            ->first();

        // Loans declined this week
        $declined = DB::table('loans')
            ->whereBetween('declined_at', [$weekStart, $weekEnd])
            ->where('status', 'DECLINED')
            ->selectRaw('
                COUNT(*) as count,
                SUM(CAST(principle AS DECIMAL(15,2))) as total_amount
            ')
            ->first();

        // Pending loans (in pipeline)
        $pending = DB::table('loans')
            ->whereIn('status', ['PENDING', 'PENDING_APPROVAL'])
            ->selectRaw('
                COUNT(*) as count,
                SUM(CAST(principle AS DECIMAL(15,2))) as total_amount
            ')
            ->first();

        // By product
        $byProduct = DB::table('loans as l')
            ->leftJoin('sub_products as sp', 'l.loan_sub_product', '=', 'sp.sub_product_id')
            ->whereBetween('l.approved_at', [$weekStart, $weekEnd])
            ->where('l.status', '!=', 'DECLINED')
            ->groupBy('sp.sub_product_name')
            ->selectRaw('
                sp.sub_product_name as product,
                COUNT(*) as count,
                SUM(CAST(l.principle AS DECIMAL(15,2))) as amount
            ')
            ->get();

        // Average processing time
        $avgProcessingTime = DB::table('loans')
            ->whereBetween('approved_at', [$weekStart, $weekEnd])
            ->whereNotNull('created_at')
            ->whereNotNull('approved_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (approved_at - created_at)) / 86400) as avg_days')
            ->value('avg_days');

        return [
            'report_type' => 'Weekly Credit Committee Report',
            'week_period' => $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'decisions' => [
                'approved' => [
                    'count' => $approved->count ?? 0,
                    'total_amount' => number_format($approved->total_amount ?? 0, 2),
                    'avg_amount' => number_format($approved->avg_amount ?? 0, 2),
                ],
                'declined' => [
                    'count' => $declined->count ?? 0,
                    'total_amount' => number_format($declined->total_amount ?? 0, 2),
                ],
                'approval_rate' => ($approved->count ?? 0) + ($declined->count ?? 0) > 0
                    ? number_format(($approved->count / (($approved->count ?? 0) + ($declined->count ?? 0))) * 100, 1) . '%'
                    : 'N/A',
            ],
            'pipeline' => [
                'pending_count' => $pending->count ?? 0,
                'pending_amount' => number_format($pending->total_amount ?? 0, 2),
            ],
            'avg_processing_days' => number_format($avgProcessingTime ?? 0, 1),
            'by_product' => $byProduct->map(function ($p) {
                return [
                    'product' => $p->product ?? 'Unknown',
                    'count' => $p->count,
                    'amount' => number_format($p->amount, 2),
                ];
            })->toArray(),
        ];
    }

    /**
     * Weekly Collections Performance
     */
    public function generateWeeklyCollectionsPerformanceReport(): array
    {
        $weekEnd = $this->reportDate->copy()->endOfWeek();
        $weekStart = $this->reportDate->copy()->startOfWeek();

        // Expected collections this week
        $expected = DB::table('loans_schedules')
            ->whereBetween('installment_date', [$weekStart, $weekEnd])
            ->selectRaw('
                COUNT(*) as installments_due,
                SUM(principle + interest) as amount_due
            ')
            ->first();

        // Actual collections from loans_schedules
        $actual = DB::table('loans_schedules')
            ->whereBetween('last_payment_date', [$weekStart, $weekEnd])
            ->where('payment', '>', 0)
            ->selectRaw('
                COUNT(*) as payments_received,
                COALESCE(SUM(payment), 0) as amount_collected
            ')
            ->first();

        // By officer from loans_schedules
        $byOfficer = DB::table('loans_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
            ->whereBetween('ls.last_payment_date', [$weekStart, $weekEnd])
            ->where('ls.payment', '>', 0)
            ->groupBy('l.supervisor_name')
            ->selectRaw('
                l.supervisor_name as officer,
                COUNT(*) as payments,
                COALESCE(SUM(ls.payment), 0) as collected
            ')
            ->orderByDesc('collected')
            ->get();

        // Recovery rate calculation
        $collectionRate = ($expected->amount_due ?? 0) > 0
            ? (($actual->amount_collected ?? 0) / $expected->amount_due) * 100
            : 0;

        return [
            'report_type' => 'Weekly Collections Performance',
            'week_period' => $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'expected_count' => $expected->installments_due ?? 0,
                'expected_amount' => number_format($expected->amount_due ?? 0, 2),
                'actual_count' => $actual->payments_received ?? 0,
                'actual_amount' => number_format($actual->amount_collected ?? 0, 2),
                'collection_rate' => number_format($collectionRate, 1) . '%',
                'shortfall' => number_format(max(0, ($expected->amount_due ?? 0) - ($actual->amount_collected ?? 0)), 2),
            ],
            'by_officer' => $byOfficer->map(function ($o) {
                return [
                    'officer' => $o->officer ?? 'Unknown',
                    'payments' => $o->payments,
                    'collected' => number_format($o->collected, 2),
                ];
            })->toArray(),
        ];
    }

    /**
     * Weekly Risk Alerts Report
     */
    public function generateWeeklyRiskAlertsReport(): array
    {
        $weekEnd = $this->reportDate->copy()->endOfWeek();
        $weekStart = $this->reportDate->copy()->startOfWeek();

        // Large loan concentration
        $largeLoans = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->orderByDesc(DB::raw('CAST(principle AS DECIMAL(15,2))'))
            ->limit(10)
            ->selectRaw('
                loan_id,
                loan_account_number,
                client_number,
                CAST(principle AS DECIMAL(15,2)) as principal
            ')
            ->get();

        $totalPortfolio = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->sum(DB::raw('CAST(principle AS DECIMAL(15,2))'));

        // NPL trend
        $nplLoans = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->whereIn('npl_classification', ['SUBSTANDARD', 'DOUBTFUL', 'LOSS'])
            ->selectRaw('
                COUNT(*) as count,
                SUM(CAST(principle AS DECIMAL(15,2))) as amount
            ')
            ->first();

        // Liquidity check
        $liquidAssets = DB::table('accounts')
            ->where(function ($q) {
                $q->where('is_bank_account', true)
                    ->orWhere('product_number', '0100');
            })
            ->sum('balance');

        $shortTermLiabilities = DB::table('accounts')
            ->whereIn('type', ['DEP', 'SAV'])
            ->sum('balance');

        $liquidityRatio = $shortTermLiabilities > 0
            ? ($liquidAssets / $shortTermLiabilities) * 100
            : 0;

        // SLA breaches
        $slaBreaches = DB::table('loans')
            ->where('sla_breach_level', '>', 0)
            ->whereBetween('sla_breached_at', [$weekStart, $weekEnd])
            ->count();

        // Concentration alerts
        $concentrationAlerts = [];
        $top10Exposure = $largeLoans->sum('principal');
        if ($totalPortfolio > 0 && ($top10Exposure / $totalPortfolio) > 0.25) {
            $concentrationAlerts[] = [
                'type' => 'High Concentration',
                'description' => 'Top 10 borrowers represent ' . number_format(($top10Exposure / $totalPortfolio) * 100, 1) . '% of portfolio',
                'threshold' => '25%',
                'actual' => number_format(($top10Exposure / $totalPortfolio) * 100, 1) . '%',
            ];
        }

        if ($liquidityRatio < 20) {
            $concentrationAlerts[] = [
                'type' => 'Low Liquidity',
                'description' => 'Liquidity ratio below minimum threshold',
                'threshold' => '20%',
                'actual' => number_format($liquidityRatio, 1) . '%',
            ];
        }

        return [
            'report_type' => 'Weekly Risk Alerts Report',
            'week_period' => $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'risk_indicators' => [
                'npl_ratio' => $totalPortfolio > 0
                    ? number_format((($nplLoans->amount ?? 0) / $totalPortfolio) * 100, 2) . '%'
                    : '0.00%',
                'liquidity_ratio' => number_format($liquidityRatio, 2) . '%',
                'concentration_top10' => $totalPortfolio > 0
                    ? number_format(($top10Exposure / $totalPortfolio) * 100, 2) . '%'
                    : '0.00%',
                'sla_breaches' => $slaBreaches,
            ],
            'alerts' => $concentrationAlerts,
            'npl_summary' => [
                'npl_loans' => $nplLoans->count ?? 0,
                'npl_amount' => number_format($nplLoans->amount ?? 0, 2),
            ],
            'top_exposures' => $largeLoans->map(function ($l) use ($totalPortfolio) {
                return [
                    'loan_id' => $l->loan_id,
                    'account' => $l->loan_account_number,
                    'principal' => number_format($l->principal, 2),
                    'portfolio_share' => $totalPortfolio > 0
                        ? number_format(($l->principal / $totalPortfolio) * 100, 2) . '%'
                        : '0.00%',
                ];
            })->toArray(),
        ];
    }

    /**
     * Weekly Savings Mobilization Report
     */
    public function generateWeeklySavingsMobilizationReport(): array
    {
        $weekEnd = $this->reportDate->copy()->endOfWeek();
        $weekStart = $this->reportDate->copy()->startOfWeek();

        // New savings accounts opened
        $newAccounts = DB::table('accounts')
            ->whereIn('type', ['SAV', 'DEP'])
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->selectRaw('
                type,
                COUNT(*) as count,
                SUM(balance) as total_balance
            ')
            ->groupBy('type')
            ->get();

        // Deposits this week
        $deposits = DB::table('general_ledger')
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->where('trans_status', 'Successful')
            ->where('credit', '>', 0)
            ->whereIn('beneficiary_product_id', ['2000', '2200', '3000'])
            ->selectRaw('
                COUNT(*) as count,
                SUM(credit) as total
            ')
            ->first();

        // Withdrawals this week
        $withdrawals = DB::table('general_ledger')
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->where('trans_status', 'Successful')
            ->where('debit', '>', 0)
            ->whereIn('sender_product_id', ['2000', '2200', '3000'])
            ->selectRaw('
                COUNT(*) as count,
                SUM(debit) as total
            ')
            ->first();

        // Current total savings
        $totalSavings = DB::table('accounts')
            ->whereIn('type', ['SAV', 'DEP'])
            ->where('status', 'ACTIVE')
            ->selectRaw('
                type,
                COUNT(*) as accounts,
                SUM(balance) as total
            ')
            ->groupBy('type')
            ->get();

        return [
            'report_type' => 'Weekly Savings Mobilization Report',
            'week_period' => $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'week_activity' => [
                'new_accounts' => $newAccounts->sum('count'),
                'deposits_count' => $deposits->count ?? 0,
                'deposits_amount' => number_format($deposits->total ?? 0, 2),
                'withdrawals_count' => $withdrawals->count ?? 0,
                'withdrawals_amount' => number_format($withdrawals->total ?? 0, 2),
                'net_mobilization' => number_format(($deposits->total ?? 0) - ($withdrawals->total ?? 0), 2),
            ],
            'new_accounts_by_type' => $newAccounts->map(function ($a) {
                return [
                    'type' => $a->type,
                    'count' => $a->count,
                    'total_balance' => number_format($a->total_balance, 2),
                ];
            })->toArray(),
            'current_position' => $totalSavings->map(function ($s) {
                return [
                    'type' => $s->type,
                    'accounts' => $s->accounts,
                    'total' => number_format($s->total, 2),
                ];
            })->toArray(),
        ];
    }

    /**
     * Weekly Loan Officer Targets Report
     */
    public function generateWeeklyLoanOfficerTargetsReport(): array
    {
        $weekEnd = $this->reportDate->copy()->endOfWeek();
        $weekStart = $this->reportDate->copy()->startOfWeek();

        // Get officer performance
        $performance = DB::table('loans as l')
            ->join('users as u', 'l.supervisor_id', '=', 'u.id')
            ->whereBetween('l.disbursement_date', [$weekStart, $weekEnd])
            ->where('l.status', 'DISBURSED')
            ->groupBy('l.supervisor_id', 'u.name', 'l.supervisor_name')
            ->selectRaw('
                l.supervisor_id,
                COALESCE(u.name, l.supervisor_name) as officer_name,
                COUNT(*) as loans_disbursed,
                SUM(CAST(l.principle AS DECIMAL(15,2))) as amount_disbursed
            ')
            ->get();

        // Collections by officer - using loans_schedules
        $collections = DB::table('loans_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
            ->whereBetween('ls.last_payment_date', [$weekStart, $weekEnd])
            ->where('ls.payment', '>', 0)
            ->groupBy('l.supervisor_id')
            ->selectRaw('
                l.supervisor_id,
                COUNT(*) as payments,
                COALESCE(SUM(ls.payment), 0) as collected
            ')
            ->get()
            ->keyBy('supervisor_id');

        return [
            'report_type' => 'Weekly Loan Officer Targets Report',
            'week_period' => $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'officers' => $performance->map(function ($p) use ($collections) {
                $col = $collections->get($p->supervisor_id);
                return [
                    'officer_id' => $p->supervisor_id,
                    'officer_name' => $p->officer_name,
                    'disbursements' => [
                        'count' => $p->loans_disbursed,
                        'amount' => number_format($p->amount_disbursed, 2),
                    ],
                    'collections' => [
                        'count' => $col->payments ?? 0,
                        'amount' => number_format($col->collected ?? 0, 2),
                    ],
                ];
            })->toArray(),
        ];
    }

    /**
     * Weekly Suspense Account Report
     */
    public function generateWeeklySuspenseAccountReport(): array
    {
        $weekEnd = $this->reportDate->copy()->endOfWeek();
        $weekStart = $this->reportDate->copy()->startOfWeek();

        // Suspense account balances
        $suspenseAccounts = DB::table('accounts')
            ->where('account_name', 'like', '%Suspense%')
            ->orWhere('suspense_account', 'IS NOT', null)
            ->select(['account_number', 'account_name', 'balance', 'updated_at'])
            ->get();

        // Uncleared items aging
        $unclearedItems = DB::table('general_ledger')
            ->where('recon_status', 'Pending')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN created_at >= NOW() - INTERVAL \'7 days\' THEN 1 ELSE 0 END) as within_7_days,
                SUM(CASE WHEN created_at < NOW() - INTERVAL \'7 days\' AND created_at >= NOW() - INTERVAL \'30 days\' THEN 1 ELSE 0 END) as within_30_days,
                SUM(CASE WHEN created_at < NOW() - INTERVAL \'30 days\' THEN 1 ELSE 0 END) as over_30_days
            ')
            ->first();

        return [
            'report_type' => 'Weekly Suspense Account Report',
            'week_period' => $weekStart->format('Y-m-d') . ' to ' . $weekEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'suspense_accounts' => $suspenseAccounts->map(function ($a) {
                return [
                    'account_number' => $a->account_number,
                    'account_name' => $a->account_name,
                    'balance' => number_format($a->balance, 2),
                    'last_updated' => Carbon::parse($a->updated_at)->format('Y-m-d H:i'),
                ];
            })->toArray(),
            'uncleared_items' => [
                'total' => $unclearedItems->total ?? 0,
                'within_7_days' => $unclearedItems->within_7_days ?? 0,
                'within_30_days' => $unclearedItems->within_30_days ?? 0,
                'over_30_days' => $unclearedItems->over_30_days ?? 0,
            ],
        ];
    }

    // Additional helper method for the main report generator
    public function generateReportData(string $reportKey): array
    {
        $methodMap = [
            // Daily Reports
            'daily_operations' => 'generateDailyOperationsReport',
            'daily_arrears' => 'generateDailyArrearsReport',
            'daily_cash_position' => 'generateDailyCashPositionReport',
            'daily_disbursements' => 'generateDailyDisbursementsReport',
            'daily_collections' => 'generateDailyCollectionsReport',
            'daily_member_activity' => 'generateDailyMemberActivityReport',
            'daily_loan_officer_portfolio' => 'generateDailyLoanOfficerPortfolioReport',
            'daily_recovery_action' => 'generateDailyRecoveryActionReport',
            'daily_gl_summary' => 'generateDailyGLSummaryReport',
            'daily_large_transactions' => 'generateDailyLargeTransactionsReport',

            // Weekly Reports
            'weekly_executive_summary' => 'generateWeeklyExecutiveSummaryReport',
            'weekly_arrears' => 'generateWeeklyArrearsReport',
            'weekly_credit_committee' => 'generateWeeklyCreditCommitteeReport',
            'weekly_collections_performance' => 'generateWeeklyCollectionsPerformanceReport',
            'weekly_risk_alerts' => 'generateWeeklyRiskAlertsReport',
            'weekly_savings_mobilization' => 'generateWeeklySavingsMobilizationReport',
            'weekly_loan_officer_targets' => 'generateWeeklyLoanOfficerTargetsReport',
            'weekly_suspense_account' => 'generateWeeklySuspenseAccountReport',
        ];

        if (isset($methodMap[$reportKey]) && method_exists($this, $methodMap[$reportKey])) {
            return $this->{$methodMap[$reportKey]}();
        }

        return [
            'report_type' => $reportKey,
            'error' => 'Report generator not implemented yet',
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
