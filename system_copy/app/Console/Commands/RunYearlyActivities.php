<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\YearlySystemActivitiesService;
use Carbon\Carbon;

class RunYearlyActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sacco:run-yearly-activities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all yearly activities for the previous year';

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
    public function handle(YearlySystemActivitiesService $yearlyService)
    {
        $this->info('Starting yearly activities for ' . Carbon::now()->subYear()->format('Y'));
        
        try {
            $result = $yearlyService->executeYearlyActivities();
            
            if ($result['status'] === 'success') {
                $this->info('Yearly activities completed successfully');
            } else {
                $this->error('Yearly activities failed: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->error('Error running yearly activities: ' . $e->getMessage());
        }
    }
} 