<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class ProcessFailedNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process-failed {--days=7 : Number of days to look back for failed notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process failed notifications and retry them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to process failed notifications...');

        try {
            $notificationService = new NotificationService();
            
            $result = $notificationService->processFailedNotifications();
            
            $this->info("Processed {$result['retried']} failed notifications");
            $this->info("Successfully retried {$result['successful']} notifications");
            
            if ($result['retried'] > 0) {
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Total Retried', $result['retried']],
                        ['Successfully Retried', $result['successful']],
                        ['Still Failed', $result['retried'] - $result['successful']]
                    ]
                );
            } else {
                $this->info('No failed notifications found to process.');
            }

            Log::info('Failed notifications processing completed via command', [
                'retried' => $result['retried'],
                'successful' => $result['successful']
            ]);

        } catch (\Exception $e) {
            $this->error('Error processing failed notifications: ' . $e->getMessage());
            Log::error('Failed notifications processing command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }

        return 0;
    }
} 