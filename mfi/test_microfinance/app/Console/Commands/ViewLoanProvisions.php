<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LoanLossProvisionService;
use Illuminate\Support\Facades\DB;

class ViewLoanProvisions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'provisions:view {--trends : Show provision trends} {--details : Show detailed provisions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View loan loss provisions status and trends';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 LOAN LOSS PROVISIONS STATUS');
        $this->info('=' . str_repeat('=', 70));
        
        $provisionService = new LoanLossProvisionService();
        
        // Get current status
        $status = $provisionService->getCurrentProvisionStatus();
        
        if (!$status) {
            $this->warn('No provision data available. Run daily activities first.');
            return 0;
        }
        
        // Display current status
        $this->displayCurrentStatus($status);
        
        // Show trends if requested
        if ($this->option('trends')) {
            $this->displayTrends($provisionService);
        }
        
        // Show details if requested
        if ($this->option('details')) {
            $this->displayDetailedProvisions();
        }
        
        return 0;
    }
    
    /**
     * Display current provision status
     */
    private function displayCurrentStatus($status)
    {
        $this->info("\n📅 CURRENT STATUS (as of {$status->summary_date})");
        $this->info(str_repeat('-', 70));
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Loans', number_format($status->total_loans)],
                ['Total Outstanding', $status->formatted_outstanding],
                ['General Provisions (1%)', 'TZS ' . number_format($status->general_provisions, 2)],
                ['Specific Provisions', 'TZS ' . number_format($status->specific_provisions, 2)],
                ['Total Provisions', $status->formatted_total],
                ['NPL Ratio', $status->formatted_npl_ratio],
                ['Provision Coverage', $status->formatted_coverage],
            ]
        );
        
        // Classification breakdown
        $this->info("\n🏷️ PORTFOLIO BY CLASSIFICATION");
        $this->info(str_repeat('-', 70));
        
        $classifications = [
            'PERFORMING' => ['balance' => $status->performing_balance, 'rate' => '1%'],
            'WATCH' => ['balance' => $status->watch_balance, 'rate' => '5%'],
            'SUBSTANDARD' => ['balance' => $status->substandard_balance, 'rate' => '25%'],
            'DOUBTFUL' => ['balance' => $status->doubtful_balance, 'rate' => '50%'],
            'LOSS' => ['balance' => $status->loss_balance, 'rate' => '100%'],
        ];
        
        $classData = [];
        foreach ($classifications as $class => $data) {
            if ($data['balance'] > 0) {
                $provision = $data['balance'] * (floatval($data['rate']) / 100);
                $classData[] = [
                    $class,
                    'TZS ' . number_format($data['balance'], 2),
                    $data['rate'],
                    'TZS ' . number_format($provision, 2)
                ];
            }
        }
        
        if (!empty($classData)) {
            $this->table(
                ['Classification', 'Outstanding', 'Rate', 'Provision'],
                $classData
            );
        }
        
        // Risk indicators
        $this->info("\n⚠️ RISK INDICATORS");
        $this->info(str_repeat('-', 70));
        
        $nplRatio = floatval($status->npl_ratio);
        $coverageRatio = floatval($status->provision_coverage_ratio);
        
        if ($nplRatio > 5.0) {
            $this->error("❌ NPL Ratio ({$status->formatted_npl_ratio}) exceeds 5% threshold");
        } else {
            $this->info("✅ NPL Ratio ({$status->formatted_npl_ratio}) within acceptable range");
        }
        
        if ($coverageRatio < 100.0) {
            $this->warn("⚠️ Provision coverage ({$status->formatted_coverage}) below 100%");
        } else {
            $this->info("✅ Provision coverage ({$status->formatted_coverage}) is adequate");
        }
    }
    
    /**
     * Display provision trends
     */
    private function displayTrends($provisionService)
    {
        $this->info("\n📈 30-DAY PROVISION TRENDS");
        $this->info(str_repeat('-', 70));
        
        $trends = $provisionService->getProvisionTrends(30);
        
        if ($trends->count() === 0) {
            $this->warn('No trend data available.');
            return;
        }
        
        $trendData = [];
        foreach ($trends as $trend) {
            $trendData[] = [
                $trend->summary_date,
                'TZS ' . number_format($trend->total_provisions, 2),
                number_format($trend->npl_ratio, 2) . '%',
                number_format($trend->provision_coverage_ratio, 2) . '%'
            ];
        }
        
        $this->table(
            ['Date', 'Total Provisions', 'NPL Ratio', 'Coverage'],
            $trendData
        );
        
        // Calculate trend direction
        if ($trends->count() >= 2) {
            $first = $trends->first();
            $last = $trends->last();
            
            $provisionChange = (($last->total_provisions - $first->total_provisions) / $first->total_provisions) * 100;
            $nplChange = $last->npl_ratio - $first->npl_ratio;
            
            $this->info("\n📊 TREND ANALYSIS");
            $this->info(str_repeat('-', 70));
            
            if ($provisionChange > 0) {
                $this->warn("📈 Provisions increased by " . number_format(abs($provisionChange), 2) . "%");
            } else {
                $this->info("📉 Provisions decreased by " . number_format(abs($provisionChange), 2) . "%");
            }
            
            if ($nplChange > 0) {
                $this->warn("📈 NPL Ratio increased by " . number_format(abs($nplChange), 2) . " percentage points");
            } else {
                $this->info("📉 NPL Ratio decreased by " . number_format(abs($nplChange), 2) . " percentage points");
            }
        }
    }
    
    /**
     * Display detailed provisions by loan
     */
    private function displayDetailedProvisions()
    {
        $this->info("\n📋 TOP 10 PROVISIONS (By Amount)");
        $this->info(str_repeat('-', 70));
        
        $topProvisions = DB::table('loan_loss_provisions')
            ->where('provision_date', DB::raw('(SELECT MAX(provision_date) FROM loan_loss_provisions)'))
            ->where('status', 'active')
            ->orderBy('provision_amount', 'desc')
            ->limit(10)
            ->get();
        
        $detailData = [];
        foreach ($topProvisions as $provision) {
            $detailData[] = [
                $provision->loan_id,
                $provision->client_number,
                $provision->loan_classification,
                $provision->days_in_arrears . ' days',
                'TZS ' . number_format($provision->outstanding_balance, 2),
                number_format($provision->provision_rate, 2) . '%',
                'TZS ' . number_format($provision->provision_amount, 2)
            ];
        }
        
        if (!empty($detailData)) {
            $this->table(
                ['Loan ID', 'Client', 'Class', 'Arrears', 'Outstanding', 'Rate', 'Provision'],
                $detailData
            );
        }
        
        // Summary by provision type
        $this->info("\n📊 PROVISION SUMMARY BY TYPE");
        $this->info(str_repeat('-', 70));
        
        $byType = DB::table('loan_loss_provisions')
            ->where('provision_date', DB::raw('(SELECT MAX(provision_date) FROM loan_loss_provisions)'))
            ->where('status', 'active')
            ->selectRaw('provision_type, COUNT(*) as count, SUM(provision_amount) as total')
            ->groupBy('provision_type')
            ->get();
        
        $typeData = [];
        foreach ($byType as $type) {
            $typeData[] = [
                ucfirst($type->provision_type),
                number_format($type->count),
                'TZS ' . number_format($type->total, 2)
            ];
        }
        
        if (!empty($typeData)) {
            $this->table(
                ['Type', 'Count', 'Total Amount'],
                $typeData
            );
        }
    }
}