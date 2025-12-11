<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessQueuedEmails;

class ProcessEmailQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:process-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process queued emails that are ready to be sent (undo window expired)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing queued emails...');
        
        // Dispatch the job immediately (sync queue) - Force synchronous execution
        ProcessQueuedEmails::dispatchSync();
        
        $this->info('Email queue processing completed. Check logs for details.');
        
        return Command::SUCCESS;
    }
}