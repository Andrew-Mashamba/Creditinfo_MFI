<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ScheduledEmailService;

class SendScheduledEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled emails that are due';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Processing scheduled emails...');
        
        $scheduledEmailService = new ScheduledEmailService();
        $result = $scheduledEmailService->processScheduledEmails();
        
        if ($result['sent'] > 0 || $result['failed'] > 0) {
            $this->info("Scheduled emails processed - Sent: {$result['sent']}, Failed: {$result['failed']}");
        } else {
            $this->info('No scheduled emails to process.');
        }
        
        return 0;
    }
}