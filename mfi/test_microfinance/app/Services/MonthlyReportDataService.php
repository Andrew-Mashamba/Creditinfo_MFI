<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service class for generating monthly scheduled report data
 * All reports use real data from the database
 */
class MonthlyReportDataService
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
    // MONTHLY CREDIT REPORTS
    // ========================================

    /**
     * Monthly Loan Portfolio Report
     */
    public function generateMonthlyLoanPortfolioReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();
        $monthStart = $this->reportDate->copy()->startOfMonth();
        $prevMonthEnd = $monthStart->copy()->subDay();
        $prevMonthStart = $prevMonthEnd->copy()->startOfMonth();

        // Current portfolio
        $portfolio = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->selectRaw('
                COUNT(*) as total_loans,
                SUM(CAST(principle AS DECIMAL(15,2))) as total_principal,
                SUM(CAST(COALESCE(total_interest, 0) AS DECIMAL(15,2))) as total_interest,
                SUM(CAST(COALESCE(total_payment, 0) AS DECIMAL(15,2))) as total_expected,
                AVG(CAST(principle AS DECIMAL(15,2))) as avg_loan_size,
                AVG(tenure) as avg_tenure
            ')
            ->first();

        // By product
        $byProduct = DB::table('loans as l')
            ->leftJoin('sub_products as sp', 'l.loan_sub_product', '=', 'sp.sub_product_id')
            ->whereIn('l.status', ['DISBURSED', 'ACTIVE'])
            ->groupBy('sp.sub_product_name', 'sp.sub_product_id')
            ->selectRaw('
                sp.sub_product_name as product_name,
                sp.sub_product_id as product_code,
                COUNT(*) as loan_count,
                SUM(CAST(l.principle AS DECIMAL(15,2))) as total_principal,
                SUM(CASE WHEN l.days_in_arrears > 0 THEN 1 ELSE 0 END) as in_arrears,
                SUM(CASE WHEN l.days_in_arrears > 0 THEN CAST(COALESCE(l.arrears_in_amount, \'0\') AS DECIMAL(15,2)) ELSE 0 END) as arrears_amount
            ')
            ->orderByDesc('total_principal')
            ->get();

        // By branch
        $byBranch = DB::table('loans as l')
            ->leftJoin('branches as b', DB::raw('CAST(l.branch_id AS BIGINT)'), '=', 'b.id')
            ->whereIn('l.status', ['DISBURSED', 'ACTIVE'])
            ->groupBy('b.name', 'l.branch_id')
            ->selectRaw('
                COALESCE(b.name, \'Head Office\') as branch_name,
                COUNT(*) as loan_count,
                SUM(CAST(l.principle AS DECIMAL(15,2))) as total_principal,
                SUM(CASE WHEN l.days_in_arrears > 0 THEN 1 ELSE 0 END) as in_arrears
            ')
            ->orderByDesc('total_principal')
            ->get();

        // By loan officer
        $byOfficer = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->groupBy('supervisor_name', 'supervisor_id')
            ->selectRaw('
                supervisor_name as officer_name,
                COUNT(*) as loan_count,
                SUM(CAST(principle AS DECIMAL(15,2))) as total_principal,
                SUM(CASE WHEN days_in_arrears > 0 THEN 1 ELSE 0 END) as in_arrears,
                SUM(CASE WHEN days_in_arrears > 0 THEN CAST(COALESCE(arrears_in_amount, \'0\') AS DECIMAL(15,2)) ELSE 0 END) as arrears_amount
            ')
            ->orderByDesc('total_principal')
            ->get();

        // Month's disbursements
        $monthDisbursements = DB::table('loans')
            ->whereBetween('disbursement_date', [$monthStart, $monthEnd])
            ->where('status', 'DISBURSED')
            ->selectRaw('
                COUNT(*) as count,
                SUM(CAST(principle AS DECIMAL(15,2))) as total
            ')
            ->first();

        // Month's repayments - using loans_schedules
        $monthRepayments = DB::table('loans_schedules')
            ->whereBetween('last_payment_date', [$monthStart, $monthEnd])
            ->where('payment', '>', 0)
            ->selectRaw('
                COUNT(*) as count,
                COALESCE(SUM(payment), 0) as total,
                COALESCE(SUM(principle_payment), 0) as principal,
                COALESCE(SUM(interest_payment), 0) as interest
            ')
            ->first();

        // Loans closed this month
        $loansClosed = DB::table('loans')
            ->whereBetween('closure_date', [$monthStart, $monthEnd])
            ->count();

        return [
            'report_type' => 'Monthly Loan Portfolio Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'portfolio_summary' => [
                'total_loans' => $portfolio->total_loans ?? 0,
                'total_principal' => number_format($portfolio->total_principal ?? 0, 2),
                'total_interest' => number_format($portfolio->total_interest ?? 0, 2),
                'avg_loan_size' => number_format($portfolio->avg_loan_size ?? 0, 2),
                'avg_tenure_months' => number_format($portfolio->avg_tenure ?? 0, 1),
            ],
            'month_activity' => [
                'disbursements' => [
                    'count' => $monthDisbursements->count ?? 0,
                    'total' => number_format($monthDisbursements->total ?? 0, 2),
                ],
                'repayments' => [
                    'count' => $monthRepayments->count ?? 0,
                    'total' => number_format($monthRepayments->total ?? 0, 2),
                    'principal' => number_format($monthRepayments->principal ?? 0, 2),
                    'interest' => number_format($monthRepayments->interest ?? 0, 2),
                ],
                'loans_closed' => $loansClosed,
            ],
            'by_product' => $byProduct->map(function ($p) use ($portfolio) {
                $portfolioShare = ($portfolio->total_principal ?? 0) > 0
                    ? ($p->total_principal / $portfolio->total_principal) * 100
                    : 0;
                $parRatio = $p->total_principal > 0
                    ? ($p->arrears_amount / $p->total_principal) * 100
                    : 0;

                return [
                    'product_code' => $p->product_code,
                    'product_name' => $p->product_name ?? 'Unknown',
                    'loan_count' => $p->loan_count,
                    'total_principal' => number_format($p->total_principal, 2),
                    'portfolio_share' => number_format($portfolioShare, 1) . '%',
                    'in_arrears' => $p->in_arrears,
                    'par_ratio' => number_format($parRatio, 2) . '%',
                ];
            })->toArray(),
            'by_branch' => $byBranch->map(function ($b) {
                return [
                    'branch_name' => $b->branch_name,
                    'loan_count' => $b->loan_count,
                    'total_principal' => number_format($b->total_principal, 2),
                    'in_arrears' => $b->in_arrears,
                ];
            })->toArray(),
            'by_officer' => $byOfficer->map(function ($o) {
                $parRatio = $o->total_principal > 0
                    ? ($o->arrears_amount / $o->total_principal) * 100
                    : 0;

                return [
                    'officer_name' => $o->officer_name ?? 'Unknown',
                    'loan_count' => $o->loan_count,
                    'total_principal' => number_format($o->total_principal, 2),
                    'in_arrears' => $o->in_arrears,
                    'arrears_amount' => number_format($o->arrears_amount, 2),
                    'par_ratio' => number_format($parRatio, 2) . '%',
                ];
            })->toArray(),
        ];
    }

    /**
     * Monthly Delinquency Report
     */
    public function generateMonthlyDelinquencyReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();

        // PAR Analysis
        $parAnalysis = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->selectRaw('
                COUNT(*) as total_loans,
                SUM(CAST(principle AS DECIMAL(15,2))) as total_portfolio,
                SUM(CASE WHEN days_in_arrears > 0 THEN 1 ELSE 0 END) as par_loans,
                SUM(CASE WHEN days_in_arrears > 0 THEN CAST(principle AS DECIMAL(15,2)) ELSE 0 END) as par_amount,
                SUM(CASE WHEN days_in_arrears BETWEEN 1 AND 30 THEN CAST(principle AS DECIMAL(15,2)) ELSE 0 END) as par1_30,
                SUM(CASE WHEN days_in_arrears BETWEEN 31 AND 60 THEN CAST(principle AS DECIMAL(15,2)) ELSE 0 END) as par31_60,
                SUM(CASE WHEN days_in_arrears BETWEEN 61 AND 90 THEN CAST(principle AS DECIMAL(15,2)) ELSE 0 END) as par61_90,
                SUM(CASE WHEN days_in_arrears > 90 THEN CAST(principle AS DECIMAL(15,2)) ELSE 0 END) as par90_plus,
                SUM(CASE WHEN days_in_arrears BETWEEN 1 AND 30 THEN 1 ELSE 0 END) as par1_30_count,
                SUM(CASE WHEN days_in_arrears BETWEEN 31 AND 60 THEN 1 ELSE 0 END) as par31_60_count,
                SUM(CASE WHEN days_in_arrears BETWEEN 61 AND 90 THEN 1 ELSE 0 END) as par61_90_count,
                SUM(CASE WHEN days_in_arrears > 90 THEN 1 ELSE 0 END) as par90_plus_count
            ')
            ->first();

        // Trend - last 6 months
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $this->reportDate->copy()->subMonths($i);
            $monthName = $monthDate->format('M Y');

            // This is a simplified approach - in production you'd track historical snapshots
            $monthData = DB::table('loans')
                ->whereIn('status', ['DISBURSED', 'ACTIVE'])
                ->where('days_in_arrears', '>', 0)
                ->where(function ($q) use ($monthDate) {
                    // Loans that were active during that month
                    $q->where('disbursement_date', '<=', $monthDate->endOfMonth())
                        ->where(function ($q2) use ($monthDate) {
                            $q2->whereNull('closure_date')
                                ->orWhere('closure_date', '>', $monthDate->endOfMonth());
                        });
                })
                ->selectRaw('
                    COUNT(*) as loans,
                    SUM(CAST(COALESCE(arrears_in_amount, \'0\') AS DECIMAL(15,2))) as amount
                ')
                ->first();

            $trend[] = [
                'month' => $monthName,
                'loans_in_arrears' => $monthData->loans ?? 0,
                'arrears_amount' => number_format($monthData->amount ?? 0, 2),
            ];
        }

        // Top delinquent accounts
        $topDelinquent = DB::table('loans as l')
            ->join('clients as c', 'l.client_number', '=', 'c.client_number')
            ->whereIn('l.status', ['DISBURSED', 'ACTIVE'])
            ->where('l.days_in_arrears', '>', 0)
            ->orderByDesc('l.days_in_arrears')
            ->limit(20)
            ->select([
                'l.loan_id',
                'l.loan_account_number',
                'c.full_name',
                'c.phone_number',
                'l.principle',
                'l.days_in_arrears',
                'l.arrears_in_amount',
                'l.npl_classification',
                'l.supervisor_name'
            ])
            ->get();

        $totalPortfolio = $parAnalysis->total_portfolio ?? 0;

        return [
            'report_type' => 'Monthly Delinquency Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_portfolio' => number_format($totalPortfolio, 2),
                'total_loans' => $parAnalysis->total_loans ?? 0,
                'par_loans' => $parAnalysis->par_loans ?? 0,
                'par_amount' => number_format($parAnalysis->par_amount ?? 0, 2),
                'overall_par_ratio' => $totalPortfolio > 0
                    ? number_format(($parAnalysis->par_amount / $totalPortfolio) * 100, 2) . '%'
                    : '0.00%',
            ],
            'par_breakdown' => [
                'par_1_30' => [
                    'loans' => $parAnalysis->par1_30_count ?? 0,
                    'amount' => number_format($parAnalysis->par1_30 ?? 0, 2),
                    'ratio' => $totalPortfolio > 0
                        ? number_format((($parAnalysis->par1_30 ?? 0) / $totalPortfolio) * 100, 2) . '%'
                        : '0.00%',
                ],
                'par_31_60' => [
                    'loans' => $parAnalysis->par31_60_count ?? 0,
                    'amount' => number_format($parAnalysis->par31_60 ?? 0, 2),
                    'ratio' => $totalPortfolio > 0
                        ? number_format((($parAnalysis->par31_60 ?? 0) / $totalPortfolio) * 100, 2) . '%'
                        : '0.00%',
                ],
                'par_61_90' => [
                    'loans' => $parAnalysis->par61_90_count ?? 0,
                    'amount' => number_format($parAnalysis->par61_90 ?? 0, 2),
                    'ratio' => $totalPortfolio > 0
                        ? number_format((($parAnalysis->par61_90 ?? 0) / $totalPortfolio) * 100, 2) . '%'
                        : '0.00%',
                ],
                'par_90_plus' => [
                    'loans' => $parAnalysis->par90_plus_count ?? 0,
                    'amount' => number_format($parAnalysis->par90_plus ?? 0, 2),
                    'ratio' => $totalPortfolio > 0
                        ? number_format((($parAnalysis->par90_plus ?? 0) / $totalPortfolio) * 100, 2) . '%'
                        : '0.00%',
                ],
            ],
            'trend' => $trend,
            'top_delinquent' => $topDelinquent->map(function ($d) {
                return [
                    'loan_id' => $d->loan_id,
                    'account' => $d->loan_account_number,
                    'member' => $d->full_name,
                    'phone' => $d->phone_number,
                    'principal' => number_format($d->principle, 2),
                    'days_overdue' => $d->days_in_arrears,
                    'arrears' => number_format($d->arrears_in_amount ?? 0, 2),
                    'classification' => $d->npl_classification,
                    'officer' => $d->supervisor_name,
                ];
            })->toArray(),
        ];
    }

    /**
     * Monthly NPL Classification Report (BOT Guidelines)
     */
    public function generateMonthlyNPLClassificationReport(): array
    {
        // BOT NPL Classification:
        // CURRENT: 0 days
        // WATCH: 1-30 days (Special Mention)
        // SUBSTANDARD: 31-90 days
        // DOUBTFUL: 91-180 days
        // LOSS: >180 days

        $nplData = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->selectRaw('
                COUNT(*) as total_loans,
                SUM(CAST(principle AS DECIMAL(15,2))) as total_portfolio,

                -- Current (Performing)
                SUM(CASE WHEN days_in_arrears = 0 OR days_in_arrears IS NULL THEN 1 ELSE 0 END) as current_count,
                SUM(CASE WHEN days_in_arrears = 0 OR days_in_arrears IS NULL THEN CAST(principle AS DECIMAL(15,2)) ELSE 0 END) as current_amount,

                -- Watch (1-30 days)
                SUM(CASE WHEN days_in_arrears BETWEEN 1 AND 30 THEN 1 ELSE 0 END) as watch_count,
                SUM(CASE WHEN days_in_arrears BETWEEN 1 AND 30 THEN CAST(principle AS DECIMAL(15,2)) ELSE 0 END) as watch_amount,

                -- Substandard (31-90 days)
                SUM(CASE WHEN days_in_arrears BETWEEN 31 AND 90 THEN 1 ELSE 0 END) as substandard_count,
                SUM(CASE WHEN days_in_arrears BETWEEN 31 AND 90 THEN CAST(principle AS DECIMAL(15,2)) ELSE 0 END) as substandard_amount,

                -- Doubtful (91-180 days)
                SUM(CASE WHEN days_in_arrears BETWEEN 91 AND 180 THEN 1 ELSE 0 END) as doubtful_count,
                SUM(CASE WHEN days_in_arrears BETWEEN 91 AND 180 THEN CAST(principle AS DECIMAL(15,2)) ELSE 0 END) as doubtful_amount,

                -- Loss (>180 days)
                SUM(CASE WHEN days_in_arrears > 180 THEN 1 ELSE 0 END) as loss_count,
                SUM(CASE WHEN days_in_arrears > 180 THEN CAST(principle AS DECIMAL(15,2)) ELSE 0 END) as loss_amount
            ')
            ->first();

        // NPL ratio (Substandard + Doubtful + Loss)
        $nplAmount = ($nplData->substandard_amount ?? 0) + ($nplData->doubtful_amount ?? 0) + ($nplData->loss_amount ?? 0);
        $nplRatio = ($nplData->total_portfolio ?? 0) > 0
            ? ($nplAmount / $nplData->total_portfolio) * 100
            : 0;

        // BOT Provision rates
        $provisionRates = [
            'current' => 1,      // 1% general provision
            'watch' => 5,        // 5%
            'substandard' => 25, // 25%
            'doubtful' => 50,    // 50%
            'loss' => 100,       // 100%
        ];

        // Calculate required provisions
        $provisions = [
            'current' => ($nplData->current_amount ?? 0) * ($provisionRates['current'] / 100),
            'watch' => ($nplData->watch_amount ?? 0) * ($provisionRates['watch'] / 100),
            'substandard' => ($nplData->substandard_amount ?? 0) * ($provisionRates['substandard'] / 100),
            'doubtful' => ($nplData->doubtful_amount ?? 0) * ($provisionRates['doubtful'] / 100),
            'loss' => ($nplData->loss_amount ?? 0) * ($provisionRates['loss'] / 100),
        ];

        $totalProvision = array_sum($provisions);

        // Get actual provision from provision_cycles
        $actualProvision = DB::table('provision_cycles')
            ->orderByDesc('created_at')
            ->value('required_reserve');

        return [
            'report_type' => 'Monthly NPL Classification Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'classification_standard' => 'Bank of Tanzania Prudential Guidelines',
            'summary' => [
                'total_portfolio' => number_format($nplData->total_portfolio ?? 0, 2),
                'total_loans' => $nplData->total_loans ?? 0,
                'npl_amount' => number_format($nplAmount, 2),
                'npl_ratio' => number_format($nplRatio, 2) . '%',
                'bot_npl_threshold' => '5%',
                'status' => $nplRatio <= 5 ? 'COMPLIANT' : 'NON-COMPLIANT',
            ],
            'classification' => [
                'current' => [
                    'description' => 'Performing (0 days overdue)',
                    'count' => $nplData->current_count ?? 0,
                    'amount' => number_format($nplData->current_amount ?? 0, 2),
                    'provision_rate' => $provisionRates['current'] . '%',
                    'provision_required' => number_format($provisions['current'], 2),
                ],
                'watch' => [
                    'description' => 'Special Mention (1-30 days)',
                    'count' => $nplData->watch_count ?? 0,
                    'amount' => number_format($nplData->watch_amount ?? 0, 2),
                    'provision_rate' => $provisionRates['watch'] . '%',
                    'provision_required' => number_format($provisions['watch'], 2),
                ],
                'substandard' => [
                    'description' => 'Substandard (31-90 days)',
                    'count' => $nplData->substandard_count ?? 0,
                    'amount' => number_format($nplData->substandard_amount ?? 0, 2),
                    'provision_rate' => $provisionRates['substandard'] . '%',
                    'provision_required' => number_format($provisions['substandard'], 2),
                ],
                'doubtful' => [
                    'description' => 'Doubtful (91-180 days)',
                    'count' => $nplData->doubtful_count ?? 0,
                    'amount' => number_format($nplData->doubtful_amount ?? 0, 2),
                    'provision_rate' => $provisionRates['doubtful'] . '%',
                    'provision_required' => number_format($provisions['doubtful'], 2),
                ],
                'loss' => [
                    'description' => 'Loss (>180 days)',
                    'count' => $nplData->loss_count ?? 0,
                    'amount' => number_format($nplData->loss_amount ?? 0, 2),
                    'provision_rate' => $provisionRates['loss'] . '%',
                    'provision_required' => number_format($provisions['loss'], 2),
                ],
            ],
            'provision_summary' => [
                'total_required' => number_format($totalProvision, 2),
                'actual_provision' => number_format($actualProvision ?? 0, 2),
                'provision_coverage' => $totalProvision > 0
                    ? number_format((($actualProvision ?? 0) / $totalProvision) * 100, 1) . '%'
                    : 'N/A',
            ],
        ];
    }

    /**
     * Monthly Loan Loss Provision Report
     */
    public function generateMonthlyProvisionReport(): array
    {
        // Get latest provision cycle
        $latestCycle = DB::table('provision_cycles')
            ->orderByDesc('created_at')
            ->first();

        // Get provision details
        $provisionDetails = DB::table('provision_cycle_details as pcd')
            ->where('pcd.provision_cycle_id', $latestCycle->id ?? 0)
            ->selectRaw('
                pcd.risk_classification as classification,
                COUNT(*) as loan_count,
                SUM(pcd.outstanding_principal + pcd.outstanding_interest) as outstanding,
                SUM(pcd.required_provision) as provision,
                AVG(pcd.provision_rate) as avg_rate
            ')
            ->groupBy('pcd.risk_classification')
            ->get();

        // Previous month provision
        $prevMonthProvision = DB::table('provision_cycles')
            ->where('created_at', '<', $latestCycle->created_at ?? now())
            ->orderByDesc('created_at')
            ->value('required_reserve');

        // Movement analysis - count by risk classification changes
        // Note: provision_cycle_details doesn't track previous classification,
        // so we count totals by risk category
        $movementUp = DB::table('provision_cycle_details')
            ->where('provision_cycle_id', $latestCycle->id ?? 0)
            ->whereIn('risk_classification', ['SUBSTANDARD', 'DOUBTFUL', 'LOSS'])
            ->count();

        $movementDown = DB::table('provision_cycle_details')
            ->where('provision_cycle_id', $latestCycle->id ?? 0)
            ->whereIn('risk_classification', ['CURRENT', 'WATCH'])
            ->count();

        return [
            'report_type' => 'Monthly Loan Loss Provision Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'provision_cycle' => [
                'cycle_id' => $latestCycle->id ?? 'N/A',
                'frequency' => $latestCycle->frequency ?? 'MONTHLY',
                'status' => $latestCycle->status ?? 'PENDING',
                'calculated_at' => $latestCycle->created_at ?? 'N/A',
            ],
            'summary' => [
                'total_provision_required' => number_format($latestCycle->required_reserve ?? 0, 2),
                'previous_provision' => number_format($prevMonthProvision ?? 0, 2),
                'change' => number_format(($latestCycle->required_reserve ?? 0) - ($prevMonthProvision ?? 0), 2),
                'change_percent' => ($prevMonthProvision ?? 0) > 0
                    ? number_format(((($latestCycle->required_reserve ?? 0) - $prevMonthProvision) / $prevMonthProvision) * 100, 1) . '%'
                    : 'N/A',
            ],
            'by_classification' => $provisionDetails->map(function ($p) {
                return [
                    'classification' => $p->classification,
                    'loan_count' => $p->loan_count,
                    'outstanding_balance' => number_format($p->outstanding, 2),
                    'provision_amount' => number_format($p->provision, 2),
                    'avg_provision_rate' => number_format($p->avg_rate, 1) . '%',
                ];
            })->toArray(),
            'movement' => [
                'upgrades' => $movementDown,
                'downgrades' => $movementUp,
            ],
        ];
    }

    /**
     * Monthly Loan Officer Performance Report
     */
    public function generateMonthlyLoanOfficerPerformanceReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();
        $monthStart = $this->reportDate->copy()->startOfMonth();

        $officers = DB::table('loans as l')
            ->join('users as u', 'l.supervisor_id', '=', 'u.id')
            ->groupBy('l.supervisor_id', 'u.name', 'l.supervisor_name')
            ->selectRaw('
                l.supervisor_id,
                COALESCE(u.name, l.supervisor_name) as officer_name,

                -- Portfolio metrics
                COUNT(*) as total_loans,
                SUM(CAST(l.principle AS DECIMAL(15,2))) as portfolio_value,

                -- Arrears metrics
                SUM(CASE WHEN l.days_in_arrears > 0 THEN 1 ELSE 0 END) as loans_in_arrears,
                SUM(CASE WHEN l.days_in_arrears > 0 THEN CAST(COALESCE(l.arrears_in_amount, \'0\') AS DECIMAL(15,2)) ELSE 0 END) as arrears_amount,
                SUM(CASE WHEN l.days_in_arrears > 30 THEN 1 ELSE 0 END) as par30_loans
            ')
            ->get();

        // Get month's activity per officer
        $monthActivity = [];
        foreach ($officers as $officer) {
            // Disbursements this month
            $disbursed = DB::table('loans')
                ->where('supervisor_id', $officer->supervisor_id)
                ->whereBetween('disbursement_date', [$monthStart, $monthEnd])
                ->where('status', 'DISBURSED')
                ->selectRaw('COUNT(*) as count, SUM(CAST(principle AS DECIMAL(15,2))) as amount')
                ->first();

            // Collections this month - using loans_schedules
            $collected = DB::table('loans_schedules as ls')
                ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
                ->where('l.supervisor_id', $officer->supervisor_id)
                ->whereBetween('ls.last_payment_date', [$monthStart, $monthEnd])
                ->where('ls.payment', '>', 0)
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(ls.payment), 0) as amount')
                ->first();

            // Expected collections
            $expected = DB::table('loans_schedules as ls')
                ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
                ->where('l.supervisor_id', $officer->supervisor_id)
                ->whereBetween('ls.installment_date', [$monthStart, $monthEnd])
                ->selectRaw('SUM(ls.principle + ls.interest) as amount')
                ->first();

            $collectionRate = ($expected->amount ?? 0) > 0
                ? (($collected->amount ?? 0) / $expected->amount) * 100
                : 0;

            $parRatio = $officer->portfolio_value > 0
                ? ($officer->arrears_amount / $officer->portfolio_value) * 100
                : 0;

            $monthActivity[$officer->supervisor_id] = [
                'officer_id' => $officer->supervisor_id,
                'officer_name' => $officer->officer_name,
                'portfolio' => [
                    'total_loans' => $officer->total_loans,
                    'portfolio_value' => number_format($officer->portfolio_value, 2),
                    'loans_in_arrears' => $officer->loans_in_arrears,
                    'arrears_amount' => number_format($officer->arrears_amount, 2),
                    'par_ratio' => number_format($parRatio, 2) . '%',
                    'par30_loans' => $officer->par30_loans,
                ],
                'month_performance' => [
                    'disbursements_count' => $disbursed->count ?? 0,
                    'disbursements_amount' => number_format($disbursed->amount ?? 0, 2),
                    'collections_count' => $collected->count ?? 0,
                    'collections_amount' => number_format($collected->amount ?? 0, 2),
                    'expected_collections' => number_format($expected->amount ?? 0, 2),
                    'collection_rate' => number_format($collectionRate, 1) . '%',
                ],
            ];
        }

        return [
            'report_type' => 'Monthly Loan Officer Performance',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'officers' => array_values($monthActivity),
        ];
    }

    /**
     * Monthly Loan Product Performance Report
     */
    public function generateMonthlyProductAnalysisReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();
        $monthStart = $this->reportDate->copy()->startOfMonth();

        $products = DB::table('loans as l')
            ->leftJoin('sub_products as sp', 'l.loan_sub_product', '=', 'sp.sub_product_id')
            ->whereIn('l.status', ['DISBURSED', 'ACTIVE'])
            ->groupBy('sp.sub_product_id', 'sp.sub_product_name', 'sp.interest')
            ->selectRaw('
                sp.sub_product_id as product_code,
                sp.sub_product_name as product_name,
                sp.interest as interest_rate,
                COUNT(*) as active_loans,
                SUM(CAST(l.principle AS DECIMAL(15,2))) as outstanding_principal,
                AVG(CAST(l.principle AS DECIMAL(15,2))) as avg_loan_size,
                AVG(l.tenure) as avg_tenure,
                SUM(CASE WHEN l.days_in_arrears > 0 THEN 1 ELSE 0 END) as in_arrears,
                SUM(CASE WHEN l.days_in_arrears > 0 THEN CAST(COALESCE(l.arrears_in_amount, \'0\') AS DECIMAL(15,2)) ELSE 0 END) as arrears_amount
            ')
            ->get();

        // Get month's disbursements by product
        $monthDisbursements = DB::table('loans as l')
            ->leftJoin('sub_products as sp', 'l.loan_sub_product', '=', 'sp.sub_product_id')
            ->whereBetween('l.disbursement_date', [$monthStart, $monthEnd])
            ->where('l.status', 'DISBURSED')
            ->groupBy('sp.sub_product_id')
            ->selectRaw('
                sp.sub_product_id as product_code,
                COUNT(*) as count,
                SUM(CAST(l.principle AS DECIMAL(15,2))) as amount
            ')
            ->get()
            ->keyBy('product_code');

        // Interest income by product this month - using loans_schedules
        $interestIncome = DB::table('loans_schedules as ls')
            ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
            ->leftJoin('sub_products as sp', 'l.loan_sub_product', '=', 'sp.sub_product_id')
            ->whereBetween('ls.last_payment_date', [$monthStart, $monthEnd])
            ->where('ls.payment', '>', 0)
            ->groupBy('sp.sub_product_id')
            ->selectRaw('
                sp.sub_product_id as product_code,
                COALESCE(SUM(ls.interest_payment), 0) as interest_collected
            ')
            ->get()
            ->keyBy('product_code');

        $totalPortfolio = $products->sum('outstanding_principal');

        return [
            'report_type' => 'Monthly Loan Product Performance',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_products' => $products->count(),
                'total_portfolio' => number_format($totalPortfolio, 2),
            ],
            'products' => $products->map(function ($p) use ($totalPortfolio, $monthDisbursements, $interestIncome) {
                $disbursement = $monthDisbursements->get($p->product_code);
                $income = $interestIncome->get($p->product_code);
                $parRatio = $p->outstanding_principal > 0
                    ? ($p->arrears_amount / $p->outstanding_principal) * 100
                    : 0;
                $portfolioShare = $totalPortfolio > 0
                    ? ($p->outstanding_principal / $totalPortfolio) * 100
                    : 0;

                return [
                    'product_code' => $p->product_code,
                    'product_name' => $p->product_name ?? 'Unknown',
                    'interest_rate' => $p->interest_rate . '%',
                    'portfolio' => [
                        'active_loans' => $p->active_loans,
                        'outstanding' => number_format($p->outstanding_principal, 2),
                        'portfolio_share' => number_format($portfolioShare, 1) . '%',
                        'avg_loan_size' => number_format($p->avg_loan_size, 2),
                        'avg_tenure' => number_format($p->avg_tenure, 0) . ' months',
                    ],
                    'quality' => [
                        'in_arrears' => $p->in_arrears,
                        'arrears_amount' => number_format($p->arrears_amount, 2),
                        'par_ratio' => number_format($parRatio, 2) . '%',
                    ],
                    'month_activity' => [
                        'disbursements_count' => $disbursement->count ?? 0,
                        'disbursements_amount' => number_format($disbursement->amount ?? 0, 2),
                        'interest_income' => number_format($income->interest_collected ?? 0, 2),
                    ],
                ];
            })->toArray(),
        ];
    }

    // ========================================
    // MONTHLY FINANCE REPORTS
    // ========================================

    /**
     * Monthly Trial Balance Report
     */
    public function generateMonthlyTrialBalanceReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();

        // Get all accounts with balances from accounts table
        $accounts = DB::table('accounts')
            ->whereNotNull('account_type')
            ->where('balance', '!=', 0)
            ->select([
                'account_number',
                'account_name',
                'account_type',
                'parent_account_number as parent_account',
                'balance'
            ])
            ->orderBy('account_number')
            ->get();

        // Calculate totals by account type
        $totals = [
            'assets' => ['debit' => 0, 'credit' => 0],
            'liabilities' => ['debit' => 0, 'credit' => 0],
            'equity' => ['debit' => 0, 'credit' => 0],
            'income' => ['debit' => 0, 'credit' => 0],
            'expense' => ['debit' => 0, 'credit' => 0],
        ];

        $trialBalanceEntries = [];
        foreach ($accounts as $acc) {
            $balance = floatval($acc->balance);
            $debit = 0;
            $credit = 0;

            // Determine if balance goes to debit or credit based on account type
            switch (strtolower($acc->account_type ?? '')) {
                case 'asset':
                case 'expense':
                    if ($balance >= 0) {
                        $debit = $balance;
                    } else {
                        $credit = abs($balance);
                    }
                    break;
                case 'liability':
                case 'equity':
                case 'income':
                    if ($balance >= 0) {
                        $credit = $balance;
                    } else {
                        $debit = abs($balance);
                    }
                    break;
            }

            if ($balance != 0) {
                $trialBalanceEntries[] = [
                    'account_number' => $acc->account_number,
                    'account_name' => $acc->account_name,
                    'account_type' => $acc->account_type,
                    'debit' => number_format($debit, 2),
                    'credit' => number_format($credit, 2),
                ];

                $type = strtolower($acc->account_type ?? 'asset') . 's';
                if (isset($totals[$type])) {
                    $totals[$type]['debit'] += $debit;
                    $totals[$type]['credit'] += $credit;
                }
            }
        }

        $totalDebit = array_sum(array_column($totals, 'debit'));
        $totalCredit = array_sum(array_column($totals, 'credit'));

        return [
            'report_type' => 'Monthly Trial Balance',
            'report_month' => $this->reportDate->format('F Y'),
            'as_at' => $monthEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_debit' => number_format($totalDebit, 2),
                'total_credit' => number_format($totalCredit, 2),
                'difference' => number_format(abs($totalDebit - $totalCredit), 2),
                'balanced' => abs($totalDebit - $totalCredit) < 0.01 ? 'YES' : 'NO',
            ],
            'by_category' => [
                'assets' => [
                    'debit' => number_format($totals['assets']['debit'], 2),
                    'credit' => number_format($totals['assets']['credit'], 2),
                ],
                'liabilities' => [
                    'debit' => number_format($totals['liabilities']['debit'], 2),
                    'credit' => number_format($totals['liabilities']['credit'], 2),
                ],
                'equity' => [
                    'debit' => number_format($totals['equity']['debit'], 2),
                    'credit' => number_format($totals['equity']['credit'], 2),
                ],
                'income' => [
                    'debit' => number_format($totals['income']['debit'], 2),
                    'credit' => number_format($totals['income']['credit'], 2),
                ],
                'expenses' => [
                    'debit' => number_format($totals['expense']['debit'], 2),
                    'credit' => number_format($totals['expense']['credit'], 2),
                ],
            ],
            'entries' => array_slice($trialBalanceEntries, 0, 100), // Limit for email
        ];
    }

    /**
     * Monthly Income Statement Report
     */
    public function generateMonthlyIncomeStatementReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();
        $monthStart = $this->reportDate->copy()->startOfMonth();

        // Interest Income - using loans_schedules
        $interestIncome = DB::table('loans_schedules')
            ->whereBetween('last_payment_date', [$monthStart, $monthEnd])
            ->where('payment', '>', 0)
            ->sum('interest_payment');

        // Fee Income
        $feeIncome = DB::table('general_ledger')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('trans_status', 'Successful')
            ->where('credit', '>', 0)
            ->whereIn('beneficiary_product_id', ['4000', '4100', '4200']) // Income accounts
            ->sum('credit');

        // Interest Expense (on savings and deposits)
        $interestExpense = DB::table('general_ledger')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('trans_status', 'Successful')
            ->where('debit', '>', 0)
            ->whereIn('sender_product_id', ['5000', '5030']) // Interest expense accounts
            ->sum('debit');

        // Operating Expenses
        $operatingExpenses = DB::table('expenses')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('status', 'APPROVED')
            ->sum('amount');

        // Provision Expense
        $provisionExpense = DB::table('provision_cycles')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('required_reserve');

        // Calculate totals
        $totalIncome = $interestIncome + $feeIncome;
        $totalExpenses = $interestExpense + $operatingExpenses + $provisionExpense;
        $netIncome = $totalIncome - $totalExpenses;

        // Previous month for comparison
        $prevMonthStart = $monthStart->copy()->subMonth();
        $prevMonthEnd = $prevMonthStart->copy()->endOfMonth();

        $prevInterestIncome = DB::table('loans_schedules')
            ->whereBetween('last_payment_date', [$prevMonthStart, $prevMonthEnd])
            ->where('payment', '>', 0)
            ->sum('interest_payment');

        return [
            'report_type' => 'Monthly Income Statement',
            'report_month' => $this->reportDate->format('F Y'),
            'period' => $monthStart->format('Y-m-d') . ' to ' . $monthEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'income' => [
                'interest_on_loans' => number_format($interestIncome, 2),
                'fees_and_commissions' => number_format($feeIncome, 2),
                'total_income' => number_format($totalIncome, 2),
            ],
            'expenses' => [
                'interest_expense' => number_format($interestExpense, 2),
                'operating_expenses' => number_format($operatingExpenses, 2),
                'provision_expense' => number_format($provisionExpense, 2),
                'total_expenses' => number_format($totalExpenses, 2),
            ],
            'net_income' => number_format($netIncome, 2),
            'comparison' => [
                'prev_month_interest_income' => number_format($prevInterestIncome, 2),
                'interest_income_change' => ($prevInterestIncome ?? 0) > 0
                    ? number_format((($interestIncome - $prevInterestIncome) / $prevInterestIncome) * 100, 1) . '%'
                    : 'N/A',
            ],
            'ratios' => [
                'net_interest_margin' => $totalIncome > 0
                    ? number_format((($interestIncome - $interestExpense) / $totalIncome) * 100, 2) . '%'
                    : 'N/A',
                'cost_to_income_ratio' => $totalIncome > 0
                    ? number_format(($totalExpenses / $totalIncome) * 100, 2) . '%'
                    : 'N/A',
            ],
        ];
    }

    /**
     * Monthly Balance Sheet Report
     */
    public function generateMonthlyBalanceSheetReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();

        // ASSETS
        // Cash and bank balances
        $cashAndBank = DB::table('accounts')
            ->where(function ($q) {
                $q->where('is_bank_account', true)
                    ->orWhere('product_number', '0100');
            })
            ->sum('balance');

        // Loans portfolio
        $loansPortfolio = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->sum(DB::raw('CAST(principle AS DECIMAL(15,2))'));

        // Less: Loan loss provision
        $loanProvision = DB::table('provision_cycles')
            ->orderByDesc('created_at')
            ->value('required_reserve') ?? 0;

        // Fixed assets
        $fixedAssets = DB::table('accounts')
            ->whereIn('product_number', ['1300', '1310', '1320'])
            ->sum('balance');

        // Other assets
        $otherAssets = DB::table('accounts')
            ->where('account_type', 'asset')
            ->whereNotIn('product_number', ['0100', '1000', '1300', '1310', '1320'])
            ->sum('balance');

        $totalAssets = $cashAndBank + $loansPortfolio - $loanProvision + $fixedAssets + $otherAssets;

        // LIABILITIES
        // Member savings
        $memberSavings = DB::table('accounts')
            ->whereIn('type', ['SAV'])
            ->where('status', 'ACTIVE')
            ->sum('balance');

        // Member deposits
        $memberDeposits = DB::table('accounts')
            ->whereIn('type', ['DEP'])
            ->where('status', 'ACTIVE')
            ->sum('balance');

        // Other liabilities
        $otherLiabilities = DB::table('accounts')
            ->where('account_type', 'liability')
            ->sum('balance');

        $totalLiabilities = $memberSavings + $memberDeposits + $otherLiabilities;

        // EQUITY
        // Share capital
        $shareCapital = DB::table('accounts')
            ->whereIn('type', ['SHA'])
            ->where('status', 'ACTIVE')
            ->sum('balance');

        // Retained earnings (from equity accounts)
        $retainedEarnings = DB::table('accounts')
            ->where('account_type', 'equity')
            ->whereNotIn('type', ['SHA'])
            ->sum('balance');

        $totalEquity = $shareCapital + $retainedEarnings;

        return [
            'report_type' => 'Monthly Balance Sheet',
            'report_month' => $this->reportDate->format('F Y'),
            'as_at' => $monthEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'assets' => [
                'cash_and_bank' => number_format($cashAndBank, 2),
                'loans_portfolio' => number_format($loansPortfolio, 2),
                'less_loan_provision' => number_format($loanProvision, 2),
                'net_loans' => number_format($loansPortfolio - $loanProvision, 2),
                'fixed_assets' => number_format($fixedAssets, 2),
                'other_assets' => number_format($otherAssets, 2),
                'total_assets' => number_format($totalAssets, 2),
            ],
            'liabilities' => [
                'member_savings' => number_format($memberSavings, 2),
                'member_deposits' => number_format($memberDeposits, 2),
                'other_liabilities' => number_format($otherLiabilities, 2),
                'total_liabilities' => number_format($totalLiabilities, 2),
            ],
            'equity' => [
                'share_capital' => number_format($shareCapital, 2),
                'retained_earnings' => number_format($retainedEarnings, 2),
                'total_equity' => number_format($totalEquity, 2),
            ],
            'total_liabilities_and_equity' => number_format($totalLiabilities + $totalEquity, 2),
            'balance_check' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 1 ? 'BALANCED' : 'UNBALANCED',
        ];
    }

    /**
     * Monthly Cash Flow Statement
     */
    public function generateMonthlyCashFlowReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();
        $monthStart = $this->reportDate->copy()->startOfMonth();

        // OPERATING ACTIVITIES
        // Loan repayments received - using loans_schedules
        $loanRepayments = DB::table('loans_schedules')
            ->whereBetween('last_payment_date', [$monthStart, $monthEnd])
            ->where('payment', '>', 0)
            ->sum('payment');

        // Interest received - using loans_schedules
        $interestReceived = DB::table('loans_schedules')
            ->whereBetween('last_payment_date', [$monthStart, $monthEnd])
            ->where('payment', '>', 0)
            ->sum('interest_payment');

        // Fees received
        $feesReceived = DB::table('general_ledger')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('trans_status', 'Successful')
            ->where('credit', '>', 0)
            ->whereIn('beneficiary_product_id', ['4000', '4100', '4200'])
            ->sum('credit');

        // Operating expenses paid
        $operatingExpenses = DB::table('expenses')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('status', 'APPROVED')
            ->sum('amount');

        // Interest paid on savings/deposits
        $interestPaid = DB::table('general_ledger')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('trans_status', 'Successful')
            ->where('debit', '>', 0)
            ->whereIn('sender_product_id', ['5000', '5030'])
            ->sum('debit');

        $netOperating = $loanRepayments + $feesReceived - $operatingExpenses - $interestPaid;

        // INVESTING ACTIVITIES
        // Loans disbursed
        $loansDisbursed = DB::table('loans')
            ->whereBetween('disbursement_date', [$monthStart, $monthEnd])
            ->where('status', 'DISBURSED')
            ->sum(DB::raw('CAST(principle AS DECIMAL(15,2))'));

        $netInvesting = -$loansDisbursed; // Outflow

        // FINANCING ACTIVITIES
        // Member deposits received
        $depositsReceived = DB::table('general_ledger')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('trans_status', 'Successful')
            ->where('credit', '>', 0)
            ->whereIn('beneficiary_product_id', ['2000', '2200', '3000'])
            ->sum('credit');

        // Member withdrawals
        $withdrawals = DB::table('general_ledger')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('trans_status', 'Successful')
            ->where('debit', '>', 0)
            ->whereIn('sender_product_id', ['2000', '2200', '3000'])
            ->sum('debit');

        // Share capital received
        $shareCapital = DB::table('general_ledger')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('trans_status', 'Successful')
            ->where('credit', '>', 0)
            ->whereIn('beneficiary_product_id', ['1000'])
            ->sum('credit');

        $netFinancing = $depositsReceived - $withdrawals + $shareCapital;

        // Net change in cash
        $netCashChange = $netOperating + $netInvesting + $netFinancing;

        return [
            'report_type' => 'Monthly Cash Flow Statement',
            'report_month' => $this->reportDate->format('F Y'),
            'period' => $monthStart->format('Y-m-d') . ' to ' . $monthEnd->format('Y-m-d'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'operating_activities' => [
                'loan_repayments' => number_format($loanRepayments, 2),
                'fees_received' => number_format($feesReceived, 2),
                'operating_expenses' => number_format(-$operatingExpenses, 2),
                'interest_paid' => number_format(-$interestPaid, 2),
                'net_operating' => number_format($netOperating, 2),
            ],
            'investing_activities' => [
                'loans_disbursed' => number_format(-$loansDisbursed, 2),
                'net_investing' => number_format($netInvesting, 2),
            ],
            'financing_activities' => [
                'deposits_received' => number_format($depositsReceived, 2),
                'withdrawals' => number_format(-$withdrawals, 2),
                'share_capital' => number_format($shareCapital, 2),
                'net_financing' => number_format($netFinancing, 2),
            ],
            'net_change_in_cash' => number_format($netCashChange, 2),
        ];
    }

    /**
     * Monthly Budget Variance Report
     */
    public function generateMonthlyBudgetVarianceReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();
        $monthStart = $this->reportDate->copy()->startOfMonth();

        // Get budget allocations for this month
        $budgetItems = DB::table('budget_allocations as ba')
            ->join('budget_managements as bm', 'ba.budget_id', '=', 'bm.id')
            ->where('ba.period', $this->reportDate->month)
            ->where('ba.year', $this->reportDate->year)
            ->select([
                'bm.budget_name as account_name',
                'bm.id as account_code',
                'ba.allocation_type as category',
                'ba.allocated_amount as budget',
                'ba.utilized_amount as actual',
            ])
            ->get();

        // If no budget data, get from expenses
        if ($budgetItems->isEmpty()) {
            $budgetItems = DB::table('expenses as e')
                ->join('accounts as a', 'e.account_id', '=', 'a.id')
                ->whereBetween('e.created_at', [$monthStart, $monthEnd])
                ->groupBy('a.account_name', 'a.account_number')
                ->selectRaw('
                    a.account_name,
                    a.account_number as account_code,
                    \'Expense\' as category,
                    0 as budget,
                    SUM(e.amount) as actual
                ')
                ->get();
        }

        $totalBudget = $budgetItems->sum('budget');
        $totalActual = $budgetItems->sum('actual');
        $totalVariance = $totalBudget - $totalActual;

        return [
            'report_type' => 'Monthly Budget Variance Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_budget' => number_format($totalBudget, 2),
                'total_actual' => number_format($totalActual, 2),
                'total_variance' => number_format($totalVariance, 2),
                'utilization_rate' => $totalBudget > 0
                    ? number_format(($totalActual / $totalBudget) * 100, 1) . '%'
                    : 'N/A',
            ],
            'line_items' => $budgetItems->map(function ($item) {
                $variance = ($item->budget ?? 0) - ($item->actual ?? 0);
                $variancePercent = ($item->budget ?? 0) > 0
                    ? (($variance / $item->budget) * 100)
                    : 0;

                return [
                    'account_name' => $item->account_name,
                    'account_code' => $item->account_code,
                    'category' => $item->category,
                    'budget' => number_format($item->budget ?? 0, 2),
                    'actual' => number_format($item->actual ?? 0, 2),
                    'variance' => number_format($variance, 2),
                    'variance_percent' => number_format($variancePercent, 1) . '%',
                    'status' => $variance >= 0 ? 'Under Budget' : 'Over Budget',
                ];
            })->toArray(),
        ];
    }

    /**
     * Monthly Bank Reconciliation Report
     */
    public function generateMonthlyBankReconciliationReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();
        $monthStart = $this->reportDate->copy()->startOfMonth();

        // Get bank accounts
        $bankAccounts = DB::table('accounts')
            ->where('is_bank_account', true)
            ->select(['id', 'account_number', 'account_name', 'balance'])
            ->get();

        $reconciliations = [];
        foreach ($bankAccounts as $bank) {
            // Get reconciliation status
            $recon = DB::table('transaction_reconciliations')
                ->where('bank_account_id', $bank->id)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->selectRaw('
                    COUNT(*) as total_items,
                    SUM(CASE WHEN status = \'MATCHED\' THEN 1 ELSE 0 END) as matched,
                    SUM(CASE WHEN status = \'UNMATCHED\' THEN 1 ELSE 0 END) as unmatched
                ')
                ->first();

            // Get unreconciled items
            $unreconciledItems = DB::table('general_ledger')
                ->where('partner_bank_account_number', $bank->account_number)
                ->where('recon_status', 'Pending')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $reconciliations[] = [
                'account_number' => $bank->account_number,
                'account_name' => $bank->account_name,
                'book_balance' => number_format($bank->balance, 2),
                'total_items' => $recon->total_items ?? 0,
                'matched' => $recon->matched ?? 0,
                'unmatched' => $recon->unmatched ?? 0,
                'unreconciled_gl' => $unreconciledItems,
            ];
        }

        return [
            'report_type' => 'Monthly Bank Reconciliation Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'bank_accounts' => $reconciliations,
        ];
    }

    /**
     * Monthly Interest Accrual Report
     */
    public function generateMonthlyInterestAccrualReport(): array
    {
        $monthEnd = $this->reportDate->copy()->endOfMonth();
        $monthStart = $this->reportDate->copy()->startOfMonth();

        // Loan interest accrued
        $loanInterest = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE'])
            ->selectRaw('
                COUNT(*) as loan_count,
                SUM(CAST(principle AS DECIMAL(15,2))) as portfolio,
                SUM(CAST(COALESCE(total_interest, 0) AS DECIMAL(15,2)) - CAST(COALESCE(total_interest_paid, 0) AS DECIMAL(15,2))) as accrued_interest
            ')
            ->first();

        // Savings interest accrued
        $savingsInterest = DB::table('general_ledger')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('narration', 'like', '%interest accrual%')
            ->where('credit', '>', 0)
            ->whereIn('beneficiary_product_id', ['2000', '2200'])
            ->sum('credit');

        // Deposit interest accrued
        $depositInterest = DB::table('general_ledger')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('narration', 'like', '%interest accrual%')
            ->where('credit', '>', 0)
            ->whereIn('beneficiary_product_id', ['3000'])
            ->sum('credit');

        // Dividend accrued
        $dividendAccrued = DB::table('share_dividend_accruals')
            ->whereBetween('accrual_date', [$monthStart, $monthEnd])
            ->sum('dividend_amount');

        return [
            'report_type' => 'Monthly Interest Accrual Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'loan_interest' => [
                'loan_count' => $loanInterest->loan_count ?? 0,
                'portfolio' => number_format($loanInterest->portfolio ?? 0, 2),
                'accrued_interest' => number_format($loanInterest->accrued_interest ?? 0, 2),
            ],
            'savings_interest' => number_format($savingsInterest, 2),
            'deposit_interest' => number_format($depositInterest, 2),
            'total_interest_expense' => number_format($savingsInterest + $depositInterest, 2),
            'dividend_accrued' => number_format($dividendAccrued, 2),
            'net_interest_income' => number_format(($loanInterest->accrued_interest ?? 0) - $savingsInterest - $depositInterest, 2),
        ];
    }

    /**
     * Monthly Liquidity Report
     */
    public function generateMonthlyLiquidityReport(): array
    {
        // Liquid assets
        $cashAndBank = DB::table('accounts')
            ->where(function ($q) {
                $q->where('is_bank_account', true)
                    ->orWhere('product_number', '0100');
            })
            ->sum('balance');

        // Short-term liabilities (savings and deposits)
        $shortTermLiabilities = DB::table('accounts')
            ->whereIn('type', ['SAV', 'DEP'])
            ->where('status', 'ACTIVE')
            ->sum('balance');

        // Liquidity ratio
        $liquidityRatio = $shortTermLiabilities > 0
            ? ($cashAndBank / $shortTermLiabilities) * 100
            : 0;

        // BOT minimum requirement is 20%
        $botMinimum = 20;

        // Maturity profile (simplified)
        $savingsBalance = DB::table('accounts')
            ->where('type', 'SAV')
            ->where('status', 'ACTIVE')
            ->sum('balance');

        $depositBalance = DB::table('accounts')
            ->where('type', 'DEP')
            ->where('status', 'ACTIVE')
            ->sum('balance');

        return [
            'report_type' => 'Monthly Liquidity Report',
            'report_month' => $this->reportDate->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'liquidity_position' => [
                'liquid_assets' => number_format($cashAndBank, 2),
                'short_term_liabilities' => number_format($shortTermLiabilities, 2),
                'liquidity_ratio' => number_format($liquidityRatio, 2) . '%',
                'bot_minimum' => $botMinimum . '%',
                'compliance_status' => $liquidityRatio >= $botMinimum ? 'COMPLIANT' : 'NON-COMPLIANT',
                'buffer' => number_format($liquidityRatio - $botMinimum, 2) . '%',
            ],
            'liability_breakdown' => [
                'savings' => number_format($savingsBalance, 2),
                'deposits' => number_format($depositBalance, 2),
            ],
        ];
    }

    /**
     * Generate report data by key
     */
    public function generateReportData(string $reportKey): array
    {
        $methodMap = [
            // Monthly Credit Reports
            'monthly_loan_portfolio' => 'generateMonthlyLoanPortfolioReport',
            'monthly_delinquency' => 'generateMonthlyDelinquencyReport',
            'monthly_npl_classification' => 'generateMonthlyNPLClassificationReport',
            'monthly_provision' => 'generateMonthlyProvisionReport',
            'monthly_loan_officer_performance' => 'generateMonthlyLoanOfficerPerformanceReport',
            'monthly_loan_product_analysis' => 'generateMonthlyProductAnalysisReport',

            // Monthly Finance Reports
            'monthly_trial_balance' => 'generateMonthlyTrialBalanceReport',
            'monthly_income_statement' => 'generateMonthlyIncomeStatementReport',
            'monthly_balance_sheet' => 'generateMonthlyBalanceSheetReport',
            'monthly_cash_flow' => 'generateMonthlyCashFlowReport',
            'monthly_budget_variance' => 'generateMonthlyBudgetVarianceReport',
            'monthly_bank_reconciliation' => 'generateMonthlyBankReconciliationReport',
            'monthly_interest_accrual' => 'generateMonthlyInterestAccrualReport',
            'monthly_liquidity' => 'generateMonthlyLiquidityReport',
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
