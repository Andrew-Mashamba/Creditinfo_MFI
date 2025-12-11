<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MonthlySystemActivitiesService;
use Carbon\Carbon;

class RunMonthlyActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sacco:run-monthly-activities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all monthly activities for the previous month';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(MonthlySystemActivitiesService $monthlyService)
    {
        $this->info('Starting monthly activities for ' . Carbon::now()->subMonth()->format('F Y'));
        
        try {
            $result = $monthlyService->executeMonthlyActivities();
            
            if ($result['status'] === 'success') {
                $this->info('Monthly activities completed successfully');
            } else {
                $this->error('Monthly activities failed: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->error('Error running monthly activities: ' . $e->getMessage());
        }
    }
} 