<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailSnoozeService;

class ProcessSnoozeEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:process-snoozes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired email snoozes and restore them to their original folders';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Processing expired email snoozes...');
        
        $snoozeService = new EmailSnoozeService();
        $processedCount = $snoozeService->processExpiredSnoozes();
        
        if ($processedCount > 0) {
            $this->info("Successfully processed {$processedCount} expired snoozes.");
        } else {
            $this->info('No expired snoozes to process.');
        }
        
        return 0;
    }
}