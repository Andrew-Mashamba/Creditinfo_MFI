<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class GetNotificationStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:stats {--days=30 : Number of days to get statistics for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get notification statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        
        $this->info("Getting notification statistics for the last {$days} days...");

        try {
            $notificationService = new NotificationService();
            $stats = $notificationService->getNotificationStats($days);
            
            $this->info('Notification Statistics:');
            $this->newLine();
            
            // Overall statistics
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Notifications', $stats['total']],
                    ['Delivered', $stats['delivered']],
                    ['Failed', $stats['failed']],
                    ['Pending', $stats['pending']],
                ]
            );
            
            $this->newLine();
            
            // By channel statistics
            $this->info('By Channel:');
            $this->table(
                ['Channel', 'Total', 'Delivered', 'Failed'],
                [
                    ['SMS', $stats['by_channel']['sms']['total'], $stats['by_channel']['sms']['delivered'], $stats['by_channel']['sms']['failed']],
                    ['Email', $stats['by_channel']['email']['total'], $stats['by_channel']['email']['delivered'], $stats['by_channel']['email']['failed']],
                ]
            );
            
            // Calculate success rates
            $smsSuccessRate = $stats['by_channel']['sms']['total'] > 0 
                ? round(($stats['by_channel']['sms']['delivered'] / $stats['by_channel']['sms']['total']) * 100, 2)
                : 0;
                
            $emailSuccessRate = $stats['by_channel']['email']['total'] > 0 
                ? round(($stats['by_channel']['email']['delivered'] / $stats['by_channel']['email']['total']) * 100, 2)
                : 0;
                
            $overallSuccessRate = $stats['total'] > 0 
                ? round(($stats['delivered'] / $stats['total']) * 100, 2)
                : 0;
            
            $this->newLine();
            $this->info('Success Rates:');
            $this->table(
                ['Channel', 'Success Rate'],
                [
                    ['SMS', "{$smsSuccessRate}%"],
                    ['Email', "{$emailSuccessRate}%"],
                    ['Overall', "{$overallSuccessRate}%"],
                ]
            );

        } catch (\Exception $e) {
            $this->error('Error getting notification statistics: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
} 