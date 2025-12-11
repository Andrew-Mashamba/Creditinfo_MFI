<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\YearEndCloserService;
use Carbon\Carbon;

class RunYearEndClosing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sacco:run-year-end-closing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all year-end closing activities for the current year';

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
    public function handle(YearEndCloserService $yearEndService)
    {
        $this->info('Starting year-end closing activities for ' . Carbon::now()->year);
        
        try {
            $result = $yearEndService->executeYearEndClosing();
            
            if ($result['status'] === 'success') {
                $this->info('Year-end closing activities completed successfully');
            } else {
                $this->error('Year-end closing activities failed: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->error('Error running year-end closing activities: ' . $e->getMessage());
        }
    }
} 