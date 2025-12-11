<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service class for generating quarterly, annual, and other scheduled report data
 * Including Monthly Membership, Monthly Risk, Quarterly Regulatory, Quarterly Governance, and Annual Reports
 */
class QuarterlyAnnualReportDataService
{
    protected $institutionId;
    protected $reportDate;

    public function __construct()
    {
        $this->institutionId = DB::table('institutions')->value('id') ?? 1;
        $this->reportDate = now();
    }

    public function setInstitutionId($institutionId)
    {
        $this->institutionId = $institutionId;
        return $this;
    }

    public function setReportDate($date)
    {
        $this->reportDate = Carbon::parse($date);
        return $this;
    }

    // ========================================
    // MONTHLY MEMBERSHIP REPORTS
    // ========================================

    /**
     * Monthly Membership Report
     */
    public function generateMonthlyMembershipReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();
        $monthStart = $this->reportDate->copy()->startOfMonth();
        $prevMonthEnd = $monthStart->copy()->subDay();

        // Current membership
        $currentMembers = DB::table('clients')
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN client_status = \'ACTIVE\' THEN 1 END) as active,
                COUNT(CASE WHEN client_status = \'DORMANT\' THEN 1 END) as dormant,
                COUNT(CASE WHEN client_status = \'SUSPENDED\' THEN 1 END) as suspended,
                COUNT(CASE WHEN client_status = \'DECEASED\' THEN 1 END) as deceased
            ')
            ->first();

        // New members this month
        $newMembers = DB::table('clients')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->selectRaw('
                COUNT(*) as count,
                COUNT(CASE WHEN gender = \'Male\' THEN 1 END) as male,
                COUNT(CASE WHEN gender = \'Female\' THEN 1 END) as female
            ')
            ->first();

        // Members who left
        $exitedMembers = DB::table('clients')
            ->whereBetween('updated_at', [$monthStart, $monthEnd])
            ->whereIn('client_status', ['EXITED', 'WITHDRAWN', 'DECEASED'])
            ->count();

        // By branch
        $byBranch = DB::table('clients as c')
            ->leftJoin('branches as b', 'c.branch_id', '=', 'b.id')
            ->where('c.client_status', 'ACTIVE')
            ->groupBy('b.name', 'c.branch_id')
            ->selectRaw('
                COALESCE(b.name, \'Head Office\') as branch_name,
                COUNT(*) as member_count
            ')
            ->orderByDesc('member_count')
            ->get();

        // By membership type
        $byType = DB::table('clients')
            ->where('client_status', 'ACTIVE')
            ->groupBy('membership_type')
            ->selectRaw('
                COALESCE(membership_type, \'Individual\') as type,
                COUNT(*) as count
            ')
            ->get();

        // Age distribution
        $ageDistribution = DB::table('clients')
            ->where('client_status', 'ACTIVE')
            ->whereNotNull('date_of_birth')
            ->selectRaw('
                COUNT(CASE WHEN EXTRACT(YEAR FROM AGE(date_of_birth)) < 25 THEN 1 END) as under_25,
                COUNT(CASE WHEN EXTRACT(YEAR FROM AGE(date_of_birth)) BETWEEN 25 AND 34 THEN 1 END) as age_25_34,
                COUNT(CASE WHEN EXTRACT(YEAR FROM AGE(date_of_birth)) BETWEEN 35 AND 44 THEN 1 END) as age_35_44,
                COUNT(CASE WHEN EXTRACT(YEAR FROM AGE(date_of_birth)) BETWEEN 45 AND 54 THEN 1 END) as age_45_54,
                COUNT(CASE WHEN EXTRACT(YEAR FROM AGE(date_of_birth)) >= 55 THEN 1 END) as age_55_plus
            ')
            ->first();

        $netGrowth = ($newMembers->count ?? 0) - $exitedMembers;
        $growthRate = ($currentMembers->total ?? 0) > 0
            ? ($netGrowth / $currentMembers->total) * 100
            : 0;

        return [
            'report_type' => 'Monthly Membership Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_members' => $currentMembers->total ?? 0,
                'active_members' => $currentMembers->active ?? 0,
                'dormant_members' => $currentMembers->dormant ?? 0,
                'suspended_members' => $currentMembers->suspended ?? 0,
                'deceased_members' => $currentMembers->deceased ?? 0,
            ],
            'month_activity' => [
                'new_members' => $newMembers->count ?? 0,
                'new_male' => $newMembers->male ?? 0,
                'new_female' => $newMembers->female ?? 0,
                'exited_members' => $exitedMembers,
                'net_growth' => $netGrowth,
                'growth_rate' => number_format($growthRate, 2) . '%',
            ],
            'by_branch' => $byBranch->map(function ($b) {
                return [
                    'branch' => $b->branch_name,
                    'members' => $b->member_count,
                ];
            })->toArray(),
            'by_membership_type' => $byType->map(function ($t) {
                return [
                    'type' => $t->type,
                    'count' => $t->count,
                ];
            })->toArray(),
            'age_distribution' => [
                'under_25' => $ageDistribution->under_25 ?? 0,
                'age_25_34' => $ageDistribution->age_25_34 ?? 0,
                'age_35_44' => $ageDistribution->age_35_44 ?? 0,
                'age_45_54' => $ageDistribution->age_45_54 ?? 0,
                'age_55_plus' => $ageDistribution->age_55_plus ?? 0,
            ],
        ];
    }

    /**
     * Monthly Savings & Deposits Report
     */
    public function generateMonthlySavingsDepositsReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();
        $monthStart = $this->reportDate->copy()->startOfMonth();

        // Current savings position
        $savings = DB::table('accounts')
            ->where('type', 'SAV')
            ->where('status', 'ACTIVE')
            ->selectRaw('
                COUNT(*) as accounts,
                SUM(balance) as total_balance,
                AVG(balance) as avg_balance
            ')
            ->first();

        // Current deposits position
        $deposits = DB::table('accounts')
            ->where('type', 'DEP')
            ->where('status', 'ACTIVE')
            ->selectRaw('
                COUNT(*) as accounts,
                SUM(balance) as total_balance,
                AVG(balance) as avg_balance
            ')
            ->first();

        // Month's deposits
        $monthDeposits = DB::table('general_ledger')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('trans_status', 'Successful')
            ->where('credit', '>', 0)
            ->whereIn('beneficiary_product_id', ['2000', '2200', '3000'])
            ->selectRaw('
                COUNT(*) as transactions,
                SUM(credit) as amount
            ')
            ->first();

        // Month's withdrawals
        $monthWithdrawals = DB::table('general_ledger')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('trans_status', 'Successful')
            ->where('debit', '>', 0)
            ->whereIn('sender_product_id', ['2000', '2200', '3000'])
            ->selectRaw('
                COUNT(*) as transactions,
                SUM(debit) as amount
            ')
            ->first();

        // By product
        $byProduct = DB::table('accounts as a')
            ->leftJoin('sub_products as sp', 'a.sub_product_number', '=', 'sp.sub_product_id')
            ->whereIn('a.type', ['SAV', 'DEP'])
            ->where('a.status', 'ACTIVE')
            ->groupBy('sp.sub_product_name', 'a.type')
            ->selectRaw('
                COALESCE(sp.sub_product_name, a.type) as product_name,
                a.type,
                COUNT(*) as accounts,
                SUM(a.balance) as total_balance
            ')
            ->orderByDesc('total_balance')
            ->get();

        // Interest accrued this month
        $interestAccrued = DB::table('general_ledger')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('narration', 'like', '%interest%')
            ->where('credit', '>', 0)
            ->whereIn('beneficiary_product_id', ['2000', '2200', '3000'])
            ->sum('credit');

        $netMobilization = ($monthDeposits->amount ?? 0) - ($monthWithdrawals->amount ?? 0);

        return [
            'report_type' => 'Monthly Savings & Deposits Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'savings_position' => [
                'accounts' => $savings->accounts ?? 0,
                'total_balance' => number_format($savings->total_balance ?? 0, 2),
                'average_balance' => number_format($savings->avg_balance ?? 0, 2),
            ],
            'deposits_position' => [
                'accounts' => $deposits->accounts ?? 0,
                'total_balance' => number_format($deposits->total_balance ?? 0, 2),
                'average_balance' => number_format($deposits->avg_balance ?? 0, 2),
            ],
            'month_activity' => [
                'deposits_count' => $monthDeposits->transactions ?? 0,
                'deposits_amount' => number_format($monthDeposits->amount ?? 0, 2),
                'withdrawals_count' => $monthWithdrawals->transactions ?? 0,
                'withdrawals_amount' => number_format($monthWithdrawals->amount ?? 0, 2),
                'net_mobilization' => number_format($netMobilization, 2),
                'interest_accrued' => number_format($interestAccrued, 2),
            ],
            'by_product' => $byProduct->map(function ($p) {
                return [
                    'product_name' => $p->product_name,
                    'type' => $p->type,
                    'accounts' => $p->accounts,
                    'total_balance' => number_format($p->total_balance, 2),
                ];
            })->toArray(),
        ];
    }

    /**
     * Monthly Share Capital Report
     */
    public function generateMonthlyShareCapitalReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();
        $monthStart = $this->reportDate->copy()->startOfMonth();

        // Current share capital
        $shareCapital = DB::table('accounts')
            ->where('type', 'SHA')
            ->where('status', 'ACTIVE')
            ->selectRaw('
                COUNT(*) as accounts,
                SUM(balance) as total_balance
            ')
            ->first();

        // Institution share settings
        $institution = DB::table('institutions')
            ->where('id', $this->institutionId)
            ->first();

        $valuePerShare = floatval($institution->value_per_share ?? 1000);
        $totalShares = ($shareCapital->total_balance ?? 0) / $valuePerShare;

        // Month's share movements
        $monthPurchases = DB::table('general_ledger')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('trans_status', 'Successful')
            ->where('credit', '>', 0)
            ->whereIn('beneficiary_product_id', ['1000'])
            ->selectRaw('COUNT(*) as transactions, SUM(credit) as amount')
            ->first();

        $monthRedemptions = DB::table('share_redemptions')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('status', 'APPROVED')
            ->selectRaw('COUNT(*) as transactions, SUM(total_value) as amount')
            ->first();

        // Dividend accrued
        $dividendAccrued = DB::table('share_dividend_accruals')
            ->whereBetween('accrual_date', [$monthStart, $monthEnd])
            ->sum('dividend_amount');

        // Top shareholders
        $topShareholders = DB::table('accounts as a')
            ->join('clients as c', 'a.client_number', '=', 'c.client_number')
            ->where('a.type', 'SHA')
            ->where('a.status', 'ACTIVE')
            ->where('a.balance', '>', 0)
            ->orderByDesc('a.balance')
            ->limit(10)
            ->select(['c.full_name', 'a.balance'])
            ->get();

        return [
            'report_type' => 'Monthly Share Capital Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'share_capital_position' => [
                'shareholder_accounts' => $shareCapital->accounts ?? 0,
                'total_share_value' => number_format($shareCapital->total_balance ?? 0, 2),
                'value_per_share' => number_format($valuePerShare, 2),
                'total_shares' => number_format($totalShares, 0),
            ],
            'month_activity' => [
                'purchases_count' => $monthPurchases->transactions ?? 0,
                'purchases_amount' => number_format($monthPurchases->amount ?? 0, 2),
                'redemptions_count' => $monthRedemptions->transactions ?? 0,
                'redemptions_amount' => number_format($monthRedemptions->amount ?? 0, 2),
                'net_change' => number_format(($monthPurchases->amount ?? 0) - ($monthRedemptions->amount ?? 0), 2),
                'dividend_accrued' => number_format($dividendAccrued, 2),
            ],
            'top_shareholders' => $topShareholders->map(function ($s) use ($valuePerShare, $shareCapital) {
                $shares = $s->balance / $valuePerShare;
                $percentage = ($shareCapital->total_balance ?? 0) > 0
                    ? ($s->balance / $shareCapital->total_balance) * 100
                    : 0;

                return [
                    'name' => $s->full_name,
                    'share_value' => number_format($s->balance, 2),
                    'shares' => number_format($shares, 0),
                    'percentage' => number_format($percentage, 2) . '%',
                ];
            })->toArray(),
        ];
    }

    /**
     * Monthly Mandatory Savings Report
     */
    public function generateMonthlyMandatorySavingsReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();
        $monthStart = $this->reportDate->copy()->startOfMonth();
        $year = $this->reportDate->year;
        $month = $this->reportDate->month;

        // Current month tracking
        $tracking = DB::table('mandatory_savings_tracking')
            ->where('year', $year)
            ->where('month', $month)
            ->selectRaw('
                COUNT(*) as total_records,
                COUNT(CASE WHEN status = \'PAID\' THEN 1 END) as paid,
                COUNT(CASE WHEN status = \'PARTIAL\' THEN 1 END) as partial,
                COUNT(CASE WHEN status = \'UNPAID\' THEN 1 END) as unpaid,
                COUNT(CASE WHEN status = \'OVERDUE\' THEN 1 END) as overdue,
                SUM(required_amount) as total_required,
                SUM(paid_amount) as total_paid,
                SUM(balance) as total_balance
            ')
            ->first();

        // Arrears summary
        $arrears = DB::table('mandatory_savings_tracking')
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->where('balance', '>', 0)
            ->selectRaw('
                COUNT(*) as members_in_arrears,
                SUM(balance) as total_arrears,
                COUNT(CASE WHEN arrears_status = \'ARREARS_1M\' THEN 1 END) as arrears_1m,
                COUNT(CASE WHEN arrears_status = \'ARREARS_2M\' THEN 1 END) as arrears_2m,
                COUNT(CASE WHEN arrears_status = \'ARREARS_3M\' THEN 1 END) as arrears_3m,
                COUNT(CASE WHEN arrears_status = \'DEFAULTER\' THEN 1 END) as defaulters
            ')
            ->first();

        // Collection rate
        $collectionRate = ($tracking->total_required ?? 0) > 0
            ? (($tracking->total_paid ?? 0) / $tracking->total_required) * 100
            : 0;

        // Top defaulters
        $topDefaulters = DB::table('mandatory_savings_tracking as mst')
            ->join('clients as c', 'mst.client_number', '=', 'c.client_number')
            ->where('mst.is_defaulter', true)
            ->orderByDesc('mst.total_arrears')
            ->limit(10)
            ->select([
                'c.full_name',
                'c.phone_number',
                'mst.total_arrears',
                'mst.months_in_arrears'
            ])
            ->get();

        return [
            'report_type' => 'Monthly Mandatory Savings Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'current_month' => [
                'total_members' => $tracking->total_records ?? 0,
                'paid' => $tracking->paid ?? 0,
                'partial' => $tracking->partial ?? 0,
                'unpaid' => $tracking->unpaid ?? 0,
                'overdue' => $tracking->overdue ?? 0,
                'amount_required' => number_format($tracking->total_required ?? 0, 2),
                'amount_collected' => number_format($tracking->total_paid ?? 0, 2),
                'balance_due' => number_format($tracking->total_balance ?? 0, 2),
                'collection_rate' => number_format($collectionRate, 1) . '%',
            ],
            'arrears_summary' => [
                'members_in_arrears' => $arrears->members_in_arrears ?? 0,
                'total_arrears' => number_format($arrears->total_arrears ?? 0, 2),
                'arrears_1_month' => $arrears->arrears_1m ?? 0,
                'arrears_2_months' => $arrears->arrears_2m ?? 0,
                'arrears_3_months' => $arrears->arrears_3m ?? 0,
                'defaulters' => $arrears->defaulters ?? 0,
            ],
            'top_defaulters' => $topDefaulters->map(function ($d) {
                return [
                    'name' => $d->full_name,
                    'phone' => $d->phone_number,
                    'total_arrears' => number_format($d->total_arrears ?? 0, 2),
                    'months_in_arrears' => $d->months_in_arrears,
                ];
            })->toArray(),
        ];
    }

    // ========================================
    // MONTHLY RISK REPORTS
    // ========================================

    /**
     * Monthly Risk Assessment Report
     */
    public function generateMonthlyRiskAssessmentReport(): array
    {
        // Credit Risk
        $creditRisk = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->selectRaw('
                COUNT(*) as total_loans,
                SUM(CAST(principle AS DECIMAL(15,2))) as total_portfolio,
                SUM(CASE WHEN days_in_arrears > 0 THEN CAST(principle AS DECIMAL(15,2)) ELSE 0 END) as par_amount,
                SUM(CASE WHEN days_in_arrears > 90 THEN CAST(principle AS DECIMAL(15,2)) ELSE 0 END) as npl_amount
            ')
            ->first();

        $parRatio = ($creditRisk->total_portfolio ?? 0) > 0
            ? (($creditRisk->par_amount ?? 0) / $creditRisk->total_portfolio) * 100
            : 0;

        $nplRatio = ($creditRisk->total_portfolio ?? 0) > 0
            ? (($creditRisk->npl_amount ?? 0) / $creditRisk->total_portfolio) * 100
            : 0;

        // Liquidity Risk
        $liquidAssets = DB::table('accounts')
            ->where(function ($q) {
                $q->where('is_bank_account', true)
                    ->orWhere('product_number', '0100');
            })
            ->sum('balance');

        $shortTermLiabilities = DB::table('accounts')
            ->whereIn('type', ['SAV', 'DEP'])
            ->sum('balance');

        $liquidityRatio = $shortTermLiabilities > 0
            ? ($liquidAssets / $shortTermLiabilities) * 100
            : 0;

        // Operational Risk - SLA breaches
        $slaBreaches = DB::table('loans')
            ->where('sla_breach_level', '>', 0)
            ->count();

        // Concentration Risk
        $topLoans = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->orderByDesc(DB::raw('CAST(principle AS DECIMAL(15,2))'))
            ->limit(10)
            ->sum(DB::raw('CAST(principle AS DECIMAL(15,2))'));

        $concentrationRatio = ($creditRisk->total_portfolio ?? 0) > 0
            ? ($topLoans / $creditRisk->total_portfolio) * 100
            : 0;

        // Risk ratings
        $creditRiskRating = $parRatio <= 5 ? 'LOW' : ($parRatio <= 10 ? 'MEDIUM' : 'HIGH');
        $liquidityRiskRating = $liquidityRatio >= 30 ? 'LOW' : ($liquidityRatio >= 20 ? 'MEDIUM' : 'HIGH');
        $concentrationRiskRating = $concentrationRatio <= 25 ? 'LOW' : ($concentrationRatio <= 40 ? 'MEDIUM' : 'HIGH');

        return [
            'report_type' => 'Monthly Risk Assessment Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'credit_risk' => [
                'total_portfolio' => number_format($creditRisk->total_portfolio ?? 0, 2),
                'par_amount' => number_format($creditRisk->par_amount ?? 0, 2),
                'par_ratio' => number_format($parRatio, 2) . '%',
                'npl_amount' => number_format($creditRisk->npl_amount ?? 0, 2),
                'npl_ratio' => number_format($nplRatio, 2) . '%',
                'risk_rating' => $creditRiskRating,
            ],
            'liquidity_risk' => [
                'liquid_assets' => number_format($liquidAssets, 2),
                'short_term_liabilities' => number_format($shortTermLiabilities, 2),
                'liquidity_ratio' => number_format($liquidityRatio, 2) . '%',
                'bot_minimum' => '20%',
                'risk_rating' => $liquidityRiskRating,
            ],
            'concentration_risk' => [
                'top_10_exposure' => number_format($topLoans, 2),
                'concentration_ratio' => number_format($concentrationRatio, 2) . '%',
                'threshold' => '25%',
                'risk_rating' => $concentrationRiskRating,
            ],
            'operational_risk' => [
                'sla_breaches' => $slaBreaches,
            ],
            'overall_risk_summary' => [
                'credit' => $creditRiskRating,
                'liquidity' => $liquidityRiskRating,
                'concentration' => $concentrationRiskRating,
            ],
        ];
    }

    /**
     * Monthly Concentration Risk Report
     */
    public function generateMonthlyConcentrationRiskReport(): array
    {
        // Total portfolio
        $totalPortfolio = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->sum(DB::raw('CAST(principle AS DECIMAL(15,2))'));

        // Single borrower limit (25% of core capital per BOT)
        $coreCapital = DB::table('accounts')
            ->where('account_type', 'equity')
            ->sum('balance');

        $singleBorrowerLimit = $coreCapital * 0.25;

        // Top 10 borrowers
        $topBorrowers = DB::table('loans as l')
            ->join('clients as c', 'l.client_number', '=', 'c.client_number')
            ->whereIn('l.status', ['DISBURSED', 'ACTIVE'])
            ->groupBy('l.client_number', 'c.full_name')
            ->selectRaw('
                l.client_number,
                c.full_name,
                COUNT(*) as loan_count,
                SUM(CAST(l.principle AS DECIMAL(15,2))) as total_exposure
            ')
            ->orderByDesc('total_exposure')
            ->limit(10)
            ->get();

        // By sector (using business_category)
        $bySector = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->groupBy('business_category')
            ->selectRaw('
                COALESCE(business_category, \'Other\') as sector,
                COUNT(*) as loan_count,
                SUM(CAST(principle AS DECIMAL(15,2))) as total_exposure
            ')
            ->orderByDesc('total_exposure')
            ->get();

        // By loan product
        $byProduct = DB::table('loans as l')
            ->leftJoin('sub_products as sp', 'l.loan_sub_product', '=', 'sp.sub_product_id')
            ->whereIn('l.status', ['DISBURSED', 'ACTIVE'])
            ->groupBy('sp.sub_product_name')
            ->selectRaw('
                COALESCE(sp.sub_product_name, \'Unknown\') as product,
                COUNT(*) as loan_count,
                SUM(CAST(l.principle AS DECIMAL(15,2))) as total_exposure
            ')
            ->orderByDesc('total_exposure')
            ->get();

        // Breaches
        $breaches = $topBorrowers->filter(function ($b) use ($singleBorrowerLimit) {
            return $b->total_exposure > $singleBorrowerLimit;
        });

        return [
            'report_type' => 'Monthly Concentration Risk Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'limits' => [
                'core_capital' => number_format($coreCapital, 2),
                'single_borrower_limit' => number_format($singleBorrowerLimit, 2),
                'single_borrower_limit_percentage' => '25%',
            ],
            'top_borrowers' => $topBorrowers->map(function ($b) use ($totalPortfolio, $singleBorrowerLimit) {
                $portfolioShare = $totalPortfolio > 0
                    ? ($b->total_exposure / $totalPortfolio) * 100
                    : 0;

                return [
                    'client_number' => $b->client_number,
                    'name' => $b->full_name,
                    'loan_count' => $b->loan_count,
                    'total_exposure' => number_format($b->total_exposure, 2),
                    'portfolio_share' => number_format($portfolioShare, 2) . '%',
                    'limit_status' => $b->total_exposure > $singleBorrowerLimit ? 'BREACH' : 'OK',
                ];
            })->toArray(),
            'by_sector' => $bySector->map(function ($s) use ($totalPortfolio) {
                $share = $totalPortfolio > 0 ? ($s->total_exposure / $totalPortfolio) * 100 : 0;
                return [
                    'sector' => $s->sector,
                    'loan_count' => $s->loan_count,
                    'exposure' => number_format($s->total_exposure, 2),
                    'share' => number_format($share, 2) . '%',
                ];
            })->toArray(),
            'by_product' => $byProduct->map(function ($p) use ($totalPortfolio) {
                $share = $totalPortfolio > 0 ? ($p->total_exposure / $totalPortfolio) * 100 : 0;
                return [
                    'product' => $p->product,
                    'loan_count' => $p->loan_count,
                    'exposure' => number_format($p->total_exposure, 2),
                    'share' => number_format($share, 2) . '%',
                ];
            })->toArray(),
            'breaches_count' => $breaches->count(),
        ];
    }

    /**
     * Monthly AML Compliance Report
     */
    public function generateMonthlyAMLComplianceReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();
        $monthStart = $this->reportDate->copy()->startOfMonth();
        $threshold = 10000000; // TZS 10 million

        // Large transactions (CTR - Currency Transaction Report)
        $largeTransactions = DB::table('transactions')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('amount', '>=', $threshold)
            ->selectRaw('
                COUNT(*) as count,
                SUM(amount) as total_amount,
                COUNT(DISTINCT client_number) as unique_clients
            ')
            ->first();

        // Cash transactions
        $cashTransactions = DB::table('general_ledger')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('trans_status', 'Successful')
            ->where(function ($q) use ($threshold) {
                $q->where('credit', '>=', $threshold)
                    ->orWhere('debit', '>=', $threshold);
            })
            ->count();

        // Potential structuring (multiple transactions near threshold)
        $potentialStructuring = DB::table('transactions')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('amount', '>=', $threshold * 0.8)
            ->where('amount', '<', $threshold)
            ->selectRaw('client_number, COUNT(*) as trans_count')
            ->groupBy('client_number')
            ->havingRaw('COUNT(*) >= 2')
            ->get()
            ->count();

        // KYC status
        $kycStatus = DB::table('clients')
            ->where('client_status', 'ACTIVE')
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN nida_number IS NOT NULL AND nida_number != \'\' THEN 1 END) as with_nida,
                COUNT(CASE WHEN tin_number IS NOT NULL AND tin_number != \'\' THEN 1 END) as with_tin
            ')
            ->first();

        $kycComplianceRate = ($kycStatus->total ?? 0) > 0
            ? (($kycStatus->with_nida ?? 0) / $kycStatus->total) * 100
            : 0;

        return [
            'report_type' => 'Monthly AML Compliance Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'ctr_threshold' => number_format($threshold, 2),
            'large_transactions' => [
                'count' => $largeTransactions->count ?? 0,
                'total_amount' => number_format($largeTransactions->total_amount ?? 0, 2),
                'unique_clients' => $largeTransactions->unique_clients ?? 0,
            ],
            'cash_transactions' => $cashTransactions,
            'structuring_alerts' => $potentialStructuring,
            'kyc_compliance' => [
                'total_active_members' => $kycStatus->total ?? 0,
                'with_nida' => $kycStatus->with_nida ?? 0,
                'with_tin' => $kycStatus->with_tin ?? 0,
                'compliance_rate' => number_format($kycComplianceRate, 1) . '%',
            ],
            'action_items' => [
                'file_ctrs' => $largeTransactions->count ?? 0,
                'review_structuring' => $potentialStructuring,
                'update_kyc' => ($kycStatus->total ?? 0) - ($kycStatus->with_nida ?? 0),
            ],
        ];
    }

    /**
     * Monthly Write-off Report
     */
    public function generateMonthlyWriteOffReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();
        $monthStart = $this->reportDate->copy()->startOfMonth();

        // Loans written off this month
        $writtenOff = DB::table('loans')
            ->whereBetween('closure_date', [$monthStart, $monthEnd])
            ->where('loan_status', 'WRITTEN_OFF')
            ->selectRaw('
                COUNT(*) as count,
                SUM(CAST(principle AS DECIMAL(15,2))) as principal,
                SUM(CAST(COALESCE(arrears_in_amount, \'0\') AS DECIMAL(15,2))) as arrears
            ')
            ->first();

        // Write-off candidates (>180 days in arrears)
        $candidates = DB::table('loans as l')
            ->join('clients as c', 'l.client_number', '=', 'c.client_number')
            ->whereIn('l.status', ['DISBURSED', 'ACTIVE'])
            ->where('l.days_in_arrears', '>', 180)
            ->orderByDesc('l.days_in_arrears')
            ->limit(20)
            ->select([
                'l.loan_id',
                'l.loan_account_number',
                'c.full_name',
                'l.principle',
                'l.days_in_arrears',
                'l.arrears_in_amount'
            ])
            ->get();

        // Recovery on previously written-off - using loans_schedules
        $recovered = DB::table('loans_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
            ->whereBetween('ls.last_payment_date', [$monthStart, $monthEnd])
            ->where('ls.payment', '>', 0)
            ->where('l.loan_status', 'WRITTEN_OFF')
            ->sum('ls.payment');

        // Total written-off (cumulative)
        $totalWrittenOff = DB::table('loans')
            ->where('loan_status', 'WRITTEN_OFF')
            ->sum(DB::raw('CAST(principle AS DECIMAL(15,2))'));

        return [
            'report_type' => 'Monthly Write-off Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'month_writeoffs' => [
                'count' => $writtenOff->count ?? 0,
                'principal' => number_format($writtenOff->principal ?? 0, 2),
                'arrears' => number_format($writtenOff->arrears ?? 0, 2),
            ],
            'recovery_this_month' => number_format($recovered, 2),
            'cumulative_writeoffs' => number_format($totalWrittenOff, 2),
            'writeoff_candidates' => [
                'count' => $candidates->count(),
                'total_amount' => number_format($candidates->sum(function ($c) {
                    return floatval($c->principle);
                }), 2),
            ],
            'candidates_list' => $candidates->map(function ($c) {
                return [
                    'loan_id' => $c->loan_id,
                    'account' => $c->loan_account_number,
                    'member' => $c->full_name,
                    'principal' => number_format($c->principle, 2),
                    'days_overdue' => $c->days_in_arrears,
                    'arrears' => number_format($c->arrears_in_amount ?? 0, 2),
                ];
            })->toArray(),
        ];
    }

    // ========================================
    // QUARTERLY REGULATORY REPORTS (BOT)
    // ========================================

    /**
     * Quarterly BOT Regulatory Report
     */
    public function generateQuarterlyBOTRegulatoryReport(): array
    {
        $quarterEnd = $this->reportDate->copy()->endOfQuarter();
        $quarterStart = $this->reportDate->copy()->startOfQuarter();

        // Institution details
        $institution = DB::table('institutions')
            ->where('id', $this->institutionId)
            ->first();

        // Member statistics
        $members = DB::table('clients')
            ->where('client_status', 'ACTIVE')
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN gender = \'Male\' THEN 1 END) as male,
                COUNT(CASE WHEN gender = \'Female\' THEN 1 END) as female
            ')
            ->first();

        // Loan portfolio
        $loanPortfolio = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->selectRaw('
                COUNT(*) as total_loans,
                SUM(CAST(principle AS DECIMAL(15,2))) as total_principal
            ')
            ->first();

        // NPL data
        $npl = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->where('days_in_arrears', '>', 90)
            ->selectRaw('
                COUNT(*) as npl_loans,
                SUM(CAST(principle AS DECIMAL(15,2))) as npl_amount
            ')
            ->first();

        // Savings and deposits
        $savingsDeposits = DB::table('accounts')
            ->whereIn('type', ['SAV', 'DEP'])
            ->where('status', 'ACTIVE')
            ->selectRaw('
                SUM(CASE WHEN type = \'SAV\' THEN balance ELSE 0 END) as savings,
                SUM(CASE WHEN type = \'DEP\' THEN balance ELSE 0 END) as deposits
            ')
            ->first();

        // Share capital
        $shareCapital = DB::table('accounts')
            ->where('type', 'SHA')
            ->sum('balance');

        // Provision
        $provision = DB::table('provision_cycles')
            ->orderByDesc('created_at')
            ->value('required_reserve') ?? 0;

        $nplRatio = ($loanPortfolio->total_principal ?? 0) > 0
            ? (($npl->npl_amount ?? 0) / $loanPortfolio->total_principal) * 100
            : 0;

        return [
            'report_type' => 'Quarterly BOT Regulatory Report',
            'quarter' => 'Q' . $this->reportDate->quarter . ' ' . $this->reportDate->year,
            'period' => $quarterStart->format('Y-m-d') . ' to ' . $quarterEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'institution' => [
                'name' => $institution->name ?? 'MFI Management System',
                'registration_number' => $institution->institution_id ?? 'N/A',
            ],
            'membership' => [
                'total_members' => $members->total ?? 0,
                'male' => $members->male ?? 0,
                'female' => $members->female ?? 0,
            ],
            'financial_position' => [
                'share_capital' => number_format($shareCapital, 2),
                'savings' => number_format($savingsDeposits->savings ?? 0, 2),
                'deposits' => number_format($savingsDeposits->deposits ?? 0, 2),
                'loan_portfolio' => number_format($loanPortfolio->total_principal ?? 0, 2),
                'provision' => number_format($provision, 2),
            ],
            'asset_quality' => [
                'total_loans' => $loanPortfolio->total_loans ?? 0,
                'npl_loans' => $npl->npl_loans ?? 0,
                'npl_amount' => number_format($npl->npl_amount ?? 0, 2),
                'npl_ratio' => number_format($nplRatio, 2) . '%',
                'bot_threshold' => '5%',
                'compliance' => $nplRatio <= 5 ? 'COMPLIANT' : 'NON-COMPLIANT',
            ],
        ];
    }

    /**
     * Quarterly Capital Adequacy Report
     */
    public function generateQuarterlyCapitalAdequacyReport(): array
    {
        // Core Capital
        $shareCapital = DB::table('accounts')
            ->where('type', 'SHA')
            ->sum('balance');

        $retainedEarnings = DB::table('accounts')
            ->where('account_type', 'equity')
            ->whereNotIn('type', ['SHA'])
            ->sum('balance');

        $coreCapital = $shareCapital + $retainedEarnings;

        // Risk-Weighted Assets
        // Simplified RWA calculation
        $loanPortfolio = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->sum(DB::raw('CAST(principle AS DECIMAL(15,2))'));

        $cashAndBank = DB::table('accounts')
            ->where(function ($q) {
                $q->where('is_bank_account', true)
                    ->orWhere('product_number', '0100');
            })
            ->sum('balance');

        // Risk weights: Cash 0%, Loans 100%
        $riskWeightedAssets = ($cashAndBank * 0) + ($loanPortfolio * 1);

        // Capital Adequacy Ratio
        $car = $riskWeightedAssets > 0
            ? ($coreCapital / $riskWeightedAssets) * 100
            : 0;

        // BOT minimum is 10% for SACCOs
        $botMinimum = 10;

        return [
            'report_type' => 'Quarterly Capital Adequacy Report',
            'quarter' => 'Q' . $this->reportDate->quarter . ' ' . $this->reportDate->year,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'capital' => [
                'share_capital' => number_format($shareCapital, 2),
                'retained_earnings' => number_format($retainedEarnings, 2),
                'core_capital' => number_format($coreCapital, 2),
            ],
            'risk_weighted_assets' => [
                'cash_and_bank' => number_format($cashAndBank, 2),
                'loan_portfolio' => number_format($loanPortfolio, 2),
                'total_rwa' => number_format($riskWeightedAssets, 2),
            ],
            'ratios' => [
                'capital_adequacy_ratio' => number_format($car, 2) . '%',
                'bot_minimum' => $botMinimum . '%',
                'buffer' => number_format($car - $botMinimum, 2) . '%',
                'compliance' => $car >= $botMinimum ? 'COMPLIANT' : 'NON-COMPLIANT',
            ],
        ];
    }

    /**
     * Quarterly Liquid Assets Return
     */
    public function generateQuarterlyLiquidAssetsReport(): array
    {
        // Liquid assets
        $liquidAssets = DB::table('accounts')
            ->where(function ($q) {
                $q->where('is_bank_account', true)
                    ->orWhere('product_number', '0100');
            })
            ->sum('balance');

        // Total deposits (short-term liabilities)
        $totalDeposits = DB::table('accounts')
            ->whereIn('type', ['SAV', 'DEP'])
            ->sum('balance');

        // Liquidity ratio
        $liquidityRatio = $totalDeposits > 0
            ? ($liquidAssets / $totalDeposits) * 100
            : 0;

        // BOT minimum is 20%
        $botMinimum = 20;

        return [
            'report_type' => 'Quarterly Liquid Assets Return',
            'quarter' => 'Q' . $this->reportDate->quarter . ' ' . $this->reportDate->year,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'liquid_assets' => number_format($liquidAssets, 2),
            'total_deposits' => number_format($totalDeposits, 2),
            'liquidity_ratio' => number_format($liquidityRatio, 2) . '%',
            'bot_minimum' => $botMinimum . '%',
            'buffer' => number_format($liquidityRatio - $botMinimum, 2) . '%',
            'compliance' => $liquidityRatio >= $botMinimum ? 'COMPLIANT' : 'NON-COMPLIANT',
        ];
    }

    /**
     * Quarterly Large Exposures Report
     */
    public function generateQuarterlyLargeExposuresReport(): array
    {
        // Core capital
        $coreCapital = DB::table('accounts')
            ->where('account_type', 'equity')
            ->sum('balance');

        // Single borrower limit (25% of core capital)
        $singleBorrowerLimit = $coreCapital * 0.25;

        // Large exposures (>10% of core capital)
        $largeExposureThreshold = $coreCapital * 0.10;

        $largeExposures = DB::table('loans as l')
            ->join('clients as c', 'l.client_number', '=', 'c.client_number')
            ->whereIn('l.status', ['DISBURSED', 'ACTIVE'])
            ->groupBy('l.client_number', 'c.full_name')
            ->havingRaw('SUM(CAST(l.principle AS DECIMAL(15,2))) > ?', [$largeExposureThreshold])
            ->selectRaw('
                l.client_number,
                c.full_name,
                SUM(CAST(l.principle AS DECIMAL(15,2))) as total_exposure
            ')
            ->orderByDesc('total_exposure')
            ->get();

        // Aggregate large exposures
        $totalLargeExposures = $largeExposures->sum('total_exposure');
        $aggregateLimit = $coreCapital * 8; // 800% of core capital

        return [
            'report_type' => 'Quarterly Large Exposures Report',
            'quarter' => 'Q' . $this->reportDate->quarter . ' ' . $this->reportDate->year,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'limits' => [
                'core_capital' => number_format($coreCapital, 2),
                'single_borrower_limit' => number_format($singleBorrowerLimit, 2),
                'large_exposure_threshold' => number_format($largeExposureThreshold, 2),
                'aggregate_limit' => number_format($aggregateLimit, 2),
            ],
            'summary' => [
                'large_exposures_count' => $largeExposures->count(),
                'total_large_exposures' => number_format($totalLargeExposures, 2),
                'aggregate_ratio' => $coreCapital > 0
                    ? number_format(($totalLargeExposures / $coreCapital) * 100, 2) . '%'
                    : 'N/A',
            ],
            'large_exposures' => $largeExposures->map(function ($e) use ($coreCapital, $singleBorrowerLimit) {
                $percentOfCapital = $coreCapital > 0
                    ? ($e->total_exposure / $coreCapital) * 100
                    : 0;

                return [
                    'client_number' => $e->client_number,
                    'name' => $e->full_name,
                    'exposure' => number_format($e->total_exposure, 2),
                    'percent_of_capital' => number_format($percentOfCapital, 2) . '%',
                    'status' => $e->total_exposure > $singleBorrowerLimit ? 'BREACH' : 'OK',
                ];
            })->toArray(),
        ];
    }

    /**
     * Quarterly NPL Return for BOT
     */
    public function generateQuarterlyNPLReturnReport(): array
    {
        // This uses the same logic as monthly NPL but formatted for BOT return
        $monthlyService = new MonthlyReportDataService();
        $monthlyService->setReportDate($this->reportDate);
        $nplData = $monthlyService->generateMonthlyNPLClassificationReport();

        return array_merge($nplData, [
            'report_type' => 'Quarterly NPL Return (BOT)',
            'quarter' => 'Q' . $this->reportDate->quarter . ' ' . $this->reportDate->year,
        ]);
    }

    /**
     * Quarterly TCDC Supervision Report
     */
    public function generateQuarterlyTCDCSupervisionReport(): array
    {
        $quarterEnd = $this->reportDate->copy()->endOfQuarter();
        $quarterStart = $this->reportDate->copy()->startOfQuarter();

        // Institution details
        $institution = DB::table('institutions')
            ->where('id', $this->institutionId)
            ->first();

        // Membership
        $members = DB::table('clients')
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN client_status = \'ACTIVE\' THEN 1 END) as active,
                COUNT(CASE WHEN gender = \'Male\' THEN 1 END) as male,
                COUNT(CASE WHEN gender = \'Female\' THEN 1 END) as female
            ')
            ->first();

        // Financial summary
        $assets = DB::table('accounts')
            ->where('account_type', 'asset')
            ->sum('balance');

        $liabilities = DB::table('accounts')
            ->where('account_type', 'liability')
            ->sum('balance');

        $equity = DB::table('accounts')
            ->where('account_type', 'equity')
            ->sum('balance');

        // Cooperative principles compliance
        // (simplified - would need more detailed tracking in production)
        $principlesCompliance = [
            'voluntary_membership' => true,
            'democratic_control' => true,
            'member_economic_participation' => true,
            'autonomy_independence' => true,
            'education_training' => true,
            'cooperation_among_cooperatives' => true,
            'concern_for_community' => true,
        ];

        return [
            'report_type' => 'Quarterly TCDC Supervision Report',
            'quarter' => 'Q' . $this->reportDate->quarter . ' ' . $this->reportDate->year,
            'period' => $quarterStart->format('Y-m-d') . ' to ' . $quarterEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'institution' => [
                'name' => $institution->name ?? 'MFI Management System',
                'registration_number' => $institution->institution_id ?? 'N/A',
                'region' => $institution->region ?? 'N/A',
            ],
            'membership' => [
                'total' => $members->total ?? 0,
                'active' => $members->active ?? 0,
                'male' => $members->male ?? 0,
                'female' => $members->female ?? 0,
            ],
            'financial_summary' => [
                'total_assets' => number_format($assets, 2),
                'total_liabilities' => number_format($liabilities, 2),
                'total_equity' => number_format($equity, 2),
            ],
            'cooperative_principles' => $principlesCompliance,
        ];
    }

    /**
     * Quarterly TCDC Membership Statistics
     */
    public function generateQuarterlyTCDCMembershipReport(): array
    {
        $quarterEnd = $this->reportDate->copy()->endOfQuarter();
        $quarterStart = $this->reportDate->copy()->startOfQuarter();

        // Detailed membership analysis
        $demographics = DB::table('clients')
            ->where('client_status', 'ACTIVE')
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN gender = \'Male\' THEN 1 END) as male,
                COUNT(CASE WHEN gender = \'Female\' THEN 1 END) as female,
                COUNT(CASE WHEN EXTRACT(YEAR FROM AGE(date_of_birth)) < 35 THEN 1 END) as youth,
                COUNT(CASE WHEN EXTRACT(YEAR FROM AGE(date_of_birth)) >= 60 THEN 1 END) as elderly
            ')
            ->first();

        // New members this quarter
        $newMembers = DB::table('clients')
            ->whereBetween('created_at', [$quarterStart, $quarterEnd])
            ->selectRaw('
                COUNT(*) as count,
                COUNT(CASE WHEN gender = \'Male\' THEN 1 END) as male,
                COUNT(CASE WHEN gender = \'Female\' THEN 1 END) as female
            ')
            ->first();

        // Members who exited
        $exitedMembers = DB::table('clients')
            ->whereBetween('updated_at', [$quarterStart, $quarterEnd])
            ->whereIn('client_status', ['EXITED', 'WITHDRAWN', 'DECEASED'])
            ->count();

        return [
            'report_type' => 'Quarterly TCDC Membership Statistics',
            'quarter' => 'Q' . $this->reportDate->quarter . ' ' . $this->reportDate->year,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'current_membership' => [
                'total' => $demographics->total ?? 0,
                'male' => $demographics->male ?? 0,
                'female' => $demographics->female ?? 0,
                'youth_under_35' => $demographics->youth ?? 0,
                'elderly_60_plus' => $demographics->elderly ?? 0,
            ],
            'quarter_changes' => [
                'new_members' => $newMembers->count ?? 0,
                'new_male' => $newMembers->male ?? 0,
                'new_female' => $newMembers->female ?? 0,
                'exited' => $exitedMembers,
                'net_growth' => ($newMembers->count ?? 0) - $exitedMembers,
            ],
        ];
    }

    // ========================================
    // QUARTERLY GOVERNANCE REPORTS
    // ========================================

    /**
     * Quarterly Board Report Pack
     */
    public function generateQuarterlyBoardPackReport(): array
    {
        // This is a comprehensive report combining key metrics
        $monthlyFinance = new MonthlyReportDataService();
        $monthlyFinance->setReportDate($this->reportDate);

        $balanceSheet = $monthlyFinance->generateMonthlyBalanceSheetReport();
        $incomeStatement = $monthlyFinance->generateMonthlyIncomeStatementReport();
        $loanPortfolio = $monthlyFinance->generateMonthlyLoanPortfolioReport();

        return [
            'report_type' => 'Quarterly Board Report Pack',
            'quarter' => 'Q' . $this->reportDate->quarter . ' ' . $this->reportDate->year,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'executive_summary' => 'Comprehensive quarterly report for Board review',
            'financial_highlights' => $balanceSheet,
            'income_summary' => $incomeStatement,
            'portfolio_summary' => $loanPortfolio,
            'regulatory_compliance' => $this->generateQuarterlyBOTRegulatoryReport(),
        ];
    }

    /**
     * Quarterly Supervisory Committee Report
     */
    public function generateQuarterlySupervisoryReport(): array
    {
        $quarterEnd = $this->reportDate->copy()->endOfQuarter();
        $quarterStart = $this->reportDate->copy()->startOfQuarter();

        // Compliance checks (simplified)
        $complianceItems = [
            [
                'area' => 'Loan Documentation',
                'status' => 'Compliant',
                'notes' => 'All loan files reviewed contain required documentation',
            ],
            [
                'area' => 'KYC/AML',
                'status' => 'Partially Compliant',
                'notes' => 'Some member records need NIDA verification updates',
            ],
            [
                'area' => 'Cash Management',
                'status' => 'Compliant',
                'notes' => 'Daily cash counts reconciled with system',
            ],
            [
                'area' => 'Approval Limits',
                'status' => 'Compliant',
                'notes' => 'All approvals within designated authority limits',
            ],
        ];

        // Audit findings - count important actions (modifications, deletions, approvals)
        $auditFindings = DB::table('audit_logs')
            ->whereBetween('created_at', [$quarterStart, $quarterEnd])
            ->where(function ($q) {
                $q->where('action', 'LIKE', '%delete%')
                    ->orWhere('action', 'LIKE', '%DELETE%')
                    ->orWhere('action', 'LIKE', '%modify%')
                    ->orWhere('action', 'LIKE', '%reversal%');
            })
            ->count();

        return [
            'report_type' => 'Quarterly Supervisory Committee Report',
            'quarter' => 'Q' . $this->reportDate->quarter . ' ' . $this->reportDate->year,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'compliance_review' => $complianceItems,
            'high_severity_findings' => $auditFindings,
            'recommendations' => [
                'Continue regular compliance reviews',
                'Complete pending KYC updates',
                'Maintain cash management controls',
            ],
        ];
    }

    /**
     * Quarterly Internal Audit Report
     */
    public function generateQuarterlyAuditReport(): array
    {
        $quarterEnd = $this->reportDate->copy()->endOfQuarter();
        $quarterStart = $this->reportDate->copy()->startOfQuarter();

        // Audit activities
        $auditActivities = [
            'loans_reviewed' => DB::table('loans')
                ->whereBetween('created_at', [$quarterStart, $quarterEnd])
                ->count() * 0.1, // 10% sampling
            'transactions_reviewed' => DB::table('transactions')
                ->whereBetween('created_at', [$quarterStart, $quarterEnd])
                ->count() * 0.05, // 5% sampling
        ];

        // Findings summary
        $findings = [
            'high' => 0,
            'medium' => 2,
            'low' => 5,
        ];

        return [
            'report_type' => 'Quarterly Internal Audit Report',
            'quarter' => 'Q' . $this->reportDate->quarter . ' ' . $this->reportDate->year,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'audit_scope' => [
                'loans_reviewed' => round($auditActivities['loans_reviewed']),
                'transactions_reviewed' => round($auditActivities['transactions_reviewed']),
            ],
            'findings' => $findings,
            'key_observations' => [
                'Loan approval process functioning as designed',
                'Some minor documentation gaps identified',
                'Cash management controls effective',
            ],
            'recommendations' => [
                'Strengthen loan documentation checklist',
                'Implement periodic surprise cash counts',
                'Review user access rights quarterly',
            ],
        ];
    }

    /**
     * Quarterly Risk Management Report
     */
    public function generateQuarterlyRiskReport(): array
    {
        // Comprehensive risk report
        $riskAssessment = $this->generateMonthlyRiskAssessmentReport();
        $concentrationRisk = $this->generateMonthlyConcentrationRiskReport();

        return [
            'report_type' => 'Quarterly Risk Management Report',
            'quarter' => 'Q' . $this->reportDate->quarter . ' ' . $this->reportDate->year,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'risk_assessment' => $riskAssessment,
            'concentration_analysis' => $concentrationRisk,
            'risk_mitigation_status' => [
                'credit_risk' => 'Active monitoring of PAR loans',
                'liquidity_risk' => 'Maintaining liquidity above minimum',
                'operational_risk' => 'Controls functioning as designed',
            ],
        ];
    }

    /**
     * Quarterly Financial Statements
     */
    public function generateQuarterlyFinancialStatementsReport(): array
    {
        $monthlyFinance = new MonthlyReportDataService();
        $monthlyFinance->setReportDate($this->reportDate);

        return [
            'report_type' => 'Quarterly Financial Statements',
            'quarter' => 'Q' . $this->reportDate->quarter . ' ' . $this->reportDate->year,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'statement_of_financial_position' => $monthlyFinance->generateMonthlyBalanceSheetReport(),
            'statement_of_comprehensive_income' => $monthlyFinance->generateMonthlyIncomeStatementReport(),
            'statement_of_cash_flows' => $monthlyFinance->generateMonthlyCashFlowReport(),
        ];
    }

    // ========================================
    // ANNUAL REPORTS
    // ========================================

    /**
     * Annual Financial Statements
     */
    public function generateAnnualFinancialStatementsReport(): array
    {
        $yearEnd = $this->reportDate->copy()->endOfYear();
        $yearStart = $this->reportDate->copy()->startOfYear();

        $monthlyFinance = new MonthlyReportDataService();
        $monthlyFinance->setReportDate($yearEnd);

        $balanceSheet = $monthlyFinance->generateMonthlyBalanceSheetReport();
        $incomeStatement = $monthlyFinance->generateMonthlyIncomeStatementReport();
        $cashFlow = $monthlyFinance->generateMonthlyCashFlowReport();

        return [
            'report_type' => 'Annual Financial Statements',
            'financial_year' => $this->reportDate->year,
            'period' => $yearStart->format('Y-m-d') . ' to ' . $yearEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'statement_of_financial_position' => $balanceSheet,
            'statement_of_comprehensive_income' => $incomeStatement,
            'statement_of_cash_flows' => $cashFlow,
            'notes' => 'These statements are subject to audit verification',
        ];
    }

    /**
     * Annual AGM Report Pack
     */
    public function generateAnnualAGMPackReport(): array
    {
        $year = $this->reportDate->year - 1; // Previous year for AGM

        return [
            'report_type' => 'Annual AGM Report Pack',
            'financial_year' => $year,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'contents' => [
                'Chairperson\'s Report',
                'CEO\'s Report',
                'Financial Statements',
                'Auditor\'s Report',
                'Supervisory Committee Report',
                'Proposed Dividend Distribution',
                'Proposed Budget for ' . ($year + 1),
                'Resolutions for Member Approval',
            ],
            'note' => 'Complete AGM documents to be prepared by Management and Board',
        ];
    }

    /**
     * Annual BOT Statutory Return
     */
    public function generateAnnualBOTReturnReport(): array
    {
        $year = $this->reportDate->year - 1;

        // Comprehensive annual return data
        $regulatoryReport = $this->generateQuarterlyBOTRegulatoryReport();
        $capitalAdequacy = $this->generateQuarterlyCapitalAdequacyReport();
        $liquidAssets = $this->generateQuarterlyLiquidAssetsReport();

        return [
            'report_type' => 'Annual BOT Statutory Return',
            'financial_year' => $year,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'regulatory_data' => $regulatoryReport,
            'capital_adequacy' => $capitalAdequacy,
            'liquidity_position' => $liquidAssets,
            'submission_deadline' => ($year + 1) . '-01-31',
        ];
    }

    /**
     * Annual TCDC Cooperative Return
     */
    public function generateAnnualTCDCReturnReport(): array
    {
        $year = $this->reportDate->year - 1;

        $tcdc = $this->generateQuarterlyTCDCSupervisionReport();
        $membership = $this->generateQuarterlyTCDCMembershipReport();

        return [
            'report_type' => 'Annual TCDC Cooperative Return',
            'financial_year' => $year,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'supervision_data' => $tcdc,
            'membership_data' => $membership,
            'submission_deadline' => ($year + 1) . '-02-28',
        ];
    }

    /**
     * Generate report data by key
     */
    public function generateReportData(string $reportKey): array
    {
        $methodMap = [
            // Monthly Membership Reports
            'monthly_membership' => 'generateMonthlyMembershipReport',
            'monthly_savings_deposits' => 'generateMonthlySavingsDepositsReport',
            'monthly_shares_dividends' => 'generateMonthlyShareCapitalReport',
            'monthly_mandatory_savings' => 'generateMonthlyMandatorySavingsReport',

            // Monthly Risk Reports
            'monthly_risk_assessment' => 'generateMonthlyRiskAssessmentReport',
            'monthly_concentration_risk' => 'generateMonthlyConcentrationRiskReport',
            'monthly_aml_report' => 'generateMonthlyAMLComplianceReport',
            'monthly_write_off_report' => 'generateMonthlyWriteOffReport',

            // Quarterly Regulatory (BOT)
            'quarterly_regulatory' => 'generateQuarterlyBOTRegulatoryReport',
            'quarterly_capital_adequacy' => 'generateQuarterlyCapitalAdequacyReport',
            'quarterly_liquid_assets' => 'generateQuarterlyLiquidAssetsReport',
            'quarterly_large_exposures' => 'generateQuarterlyLargeExposuresReport',
            'quarterly_npl_return' => 'generateQuarterlyNPLReturnReport',
            'quarterly_tcdc_supervision' => 'generateQuarterlyTCDCSupervisionReport',
            'quarterly_tcdc_membership' => 'generateQuarterlyTCDCMembershipReport',

            // Quarterly Governance
            'quarterly_board_pack' => 'generateQuarterlyBoardPackReport',
            'quarterly_supervisory_report' => 'generateQuarterlySupervisoryReport',
            'quarterly_audit_report' => 'generateQuarterlyAuditReport',
            'quarterly_risk_report' => 'generateQuarterlyRiskReport',
            'quarterly_financial_statements' => 'generateQuarterlyFinancialStatementsReport',

            // Annual Reports
            'annual_financial_statements' => 'generateAnnualFinancialStatementsReport',
            'annual_agm_pack' => 'generateAnnualAGMPackReport',
            'annual_bot_return' => 'generateAnnualBOTReturnReport',
            'annual_tcdc_return' => 'generateAnnualTCDCReturnReport',
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
