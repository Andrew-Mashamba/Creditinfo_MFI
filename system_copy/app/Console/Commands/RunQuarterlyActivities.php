<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QuarterlySystemActivitiesService;
use Carbon\Carbon;

class RunQuarterlyActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sacco:run-quarterly-activities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all quarterly activities for the previous quarter';

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
    public function handle(QuarterlySystemActivitiesService $quarterlyService)
    {
        $this->info('Starting quarterly activities for ' . Carbon::now()->subQuarter()->format('Y-Q'));
        
        try {
            $result = $quarterlyService->executeQuarterlyActivities();
            
            if ($result['status'] === 'success') {
                $this->info('Quarterly activities completed successfully');
            } else {
                $this->error('Quarterly activities failed: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->error('Error running quarterly activities: ' . $e->getMessage());
        }
    }
} 