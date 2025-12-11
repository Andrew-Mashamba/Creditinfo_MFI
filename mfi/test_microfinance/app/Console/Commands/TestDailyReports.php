<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SimpleDailyLoanReportsService;
use Carbon\Carbon;

class TestDailyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:daily-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test daily loan reports generation';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing daily loan reports generation...');
        
        try {
            $reportsService = new SimpleDailyLoanReportsService();
            $date = Carbon::now();
            
            $this->info('Generating and sending reports...');
            $reportsService->generateAndSendReports($date);
            $this->info('✅ Reports generated and sent successfully!');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}