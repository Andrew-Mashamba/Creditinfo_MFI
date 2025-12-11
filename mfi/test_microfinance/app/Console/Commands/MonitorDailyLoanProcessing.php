<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MonitorDailyLoanProcessing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:monitor {--live : Show live updates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor daily loan processing progress and statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Loan Processing Monitor');
        $this->info('=' . str_repeat('=', 70));
        
        if ($this->option('live')) {
            $this->monitorLive();
        } else {
            $this->showStatistics();
        }
    }
    
    /**
     * Show current statistics
     */
    private function showStatistics()
    {
        // Portfolio Overview
        $this->info("\n📊 PORTFOLIO OVERVIEW");
        $this->info(str_repeat('-', 70));
        
        $portfolio = DB::table('loans')
            ->selectRaw("
                COUNT(*) as total_loans,
                COUNT(CASE WHEN loan_status = 'active' THEN 1 END) as active_loans,
                SUM(CASE WHEN loan_status = 'active' THEN principle - COALESCE(total_principal_paid, 0) ELSE 0 END) as outstanding,
                COUNT(CASE WHEN days_in_arrears > 0 THEN 1 END) as loans_in_arrears,
                SUM(total_arrears) as total_arrears
            ")
            ->first();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Loans', number_format($portfolio->total_loans)],
                ['Active Loans', number_format($portfolio->active_loans)],
                ['Outstanding Portfolio', 'TZS ' . number_format($portfolio->outstanding ?? 0, 2)],
                ['Loans in Arrears', number_format($portfolio->loans_in_arrears)],
                ['Total Arrears', 'TZS ' . number_format($portfolio->total_arrears ?? 0, 2)],
                ['PAR %', $portfolio->outstanding > 0 ? number_format(($portfolio->total_arrears / $portfolio->outstanding * 100), 2) . '%' : '0%'],
            ]
        );
        
        // Classification Distribution
        $this->info("\n🏷️ CLASSIFICATION DISTRIBUTION");
        $this->info(str_repeat('-', 70));
        
        $classifications = DB::table('loans')
            ->where('loan_status', 'active')
            ->select('loan_classification', DB::raw('COUNT(*) as count'))
            ->groupBy('loan_classification')
            ->orderBy('loan_classification')
            ->get();
        
        $classData = [];
        foreach ($classifications as $class) {
            $classData[] = [$class->loan_classification, number_format($class->count)];
        }
        $this->table(['Classification', 'Count'], $classData);
        
        // Today's Activity
        $this->info("\n📅 TODAY'S ACTIVITY");
        $this->info(str_repeat('-', 70));
        
        $todayActivity = DB::table('loan_payments')
            ->whereDate('payment_date', Carbon::today())
            ->selectRaw("
                COUNT(*) as payment_count,
                COUNT(CASE WHEN payment_method = 'auto_deduction' THEN 1 END) as auto_payments,
                SUM(amount) as total_collected,
                SUM(CASE WHEN payment_method = 'auto_deduction' THEN amount ELSE 0 END) as auto_collected
            ")
            ->first();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Payments Today', number_format($todayActivity->payment_count)],
                ['Automatic Payments', number_format($todayActivity->auto_payments)],
                ['Total Collected', 'TZS ' . number_format($todayActivity->total_collected ?? 0, 2)],
                ['Auto-Collected', 'TZS ' . number_format($todayActivity->auto_collected ?? 0, 2)],
            ]
        );
        
        // Processing Performance
        $this->info("\n⚡ PROCESSING PERFORMANCE");
        $this->info(str_repeat('-', 70));
        
        $lastRun = Cache::get('daily_loan_processing_stats');
        if ($lastRun) {
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Last Run', Carbon::parse($lastRun['timestamp'] ?? now())->format('Y-m-d H:i:s')],
                    ['Duration', ($lastRun['duration'] ?? 0) . ' seconds'],
                    ['Loans Processed', number_format($lastRun['loans_processed'] ?? 0)],
                    ['Repayments Processed', number_format($lastRun['repayments_processed'] ?? 0)],
                    ['Amount Collected', 'TZS ' . number_format($lastRun['total_amount_collected'] ?? 0, 2)],
                    ['Schedules Updated', number_format($lastRun['schedules_updated'] ?? 0)],
                    ['Notifications Sent', number_format($lastRun['notifications_queued'] ?? 0)],
                    ['Errors', $lastRun['errors'] ?? 0],
                ]
            );
        } else {
            $this->warn('No processing statistics available. Run daily processing first.');
        }
        
        // Queue Status
        $this->info("\n📬 QUEUE STATUS");
        $this->info(str_repeat('-', 70));
        
        $queueStatus = DB::table('jobs')
            ->select('queue', DB::raw('COUNT(*) as pending'))
            ->groupBy('queue')
            ->get();
        
        if ($queueStatus->count() > 0) {
            $queueData = [];
            foreach ($queueStatus as $queue) {
                $queueData[] = [$queue->queue, number_format($queue->pending)];
            }
            $this->table(['Queue', 'Pending Jobs'], $queueData);
        } else {
            $this->info('No pending jobs in queue');
        }
        
        // Failed Jobs
        $failedJobs = DB::table('failed_jobs')->count();
        if ($failedJobs > 0) {
            $this->error("⚠️ Failed Jobs: {$failedJobs}");
        }
    }
    
    /**
     * Monitor live updates
     */
    private function monitorLive()
    {
        $this->info('Live monitoring started. Press Ctrl+C to stop.');
        
        while (true) {
            $this->output->write("\033[2J\033[;H"); // Clear screen
            
            $this->info('🔴 LIVE MONITORING - ' . now()->format('Y-m-d H:i:s'));
            $this->info('=' . str_repeat('=', 70));
            
            // Current Processing Status
            $processingStatus = Cache::get('loan_processing_status', 'idle');
            $statusColor = $processingStatus === 'processing' ? 'comment' : 'info';
            $this->$statusColor("Status: " . strtoupper($processingStatus));
            
            // Live Statistics
            $stats = DB::table('loan_payments')
                ->whereDate('created_at', Carbon::today())
                ->where('payment_method', 'auto_deduction')
                ->selectRaw("
                    COUNT(*) as count,
                    SUM(amount) as total,
                    MAX(created_at) as last_processed
                ")
                ->first();
            
            $this->info("\n📊 TODAY'S AUTO-PROCESSING");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Payments Processed', number_format($stats->count)],
                    ['Amount Collected', 'TZS ' . number_format($stats->total ?? 0, 2)],
                    ['Last Processed', $stats->last_processed ? Carbon::parse($stats->last_processed)->diffForHumans() : 'N/A'],
                ]
            );
            
            // Active Queues
            $activeJobs = DB::table('jobs')
                ->select('queue', DB::raw('COUNT(*) as count'))
                ->groupBy('queue')
                ->get();
            
            if ($activeJobs->count() > 0) {
                $this->info("\n📬 ACTIVE QUEUES");
                foreach ($activeJobs as $job) {
                    $this->line("  {$job->queue}: {$job->count} jobs");
                }
            }
            
            // Memory Usage
            $memoryUsage = memory_get_usage(true) / 1024 / 1024;
            $memoryPeak = memory_get_peak_usage(true) / 1024 / 1024;
            
            $this->info("\n💾 MEMORY USAGE");
            $this->line("  Current: " . number_format($memoryUsage, 2) . " MB");
            $this->line("  Peak: " . number_format($memoryPeak, 2) . " MB");
            
            sleep(5); // Refresh every 5 seconds
        }
    }
}