<?php

namespace App\Listeners;

use App\Events\ApprovalProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleApprovalProcessed implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ApprovalProcessed $event)
    {
        try {
            // Log the approval event
            Log::info('Approval processed', [
                'approval_id' => $event->approval->id,
                'process_name' => $event->approval->process_name,
                'action' => $event->action,
                'processed_by' => $event->processedBy->name
            ]);

            // Update any related statistics or metrics
            $this->updateApprovalMetrics($event);

            // Send notifications if needed
            $this->sendNotifications($event);

        } catch (\Exception $e) {
            Log::error('Error handling approval processed event', [
                'error' => $e->getMessage(),
                'approval_id' => $event->approval->id
            ]);
        }
    }

    private function updateApprovalMetrics($event)
    {
        // Update approval statistics in cache or database
        $stats = cache()->get('approval_stats', [
            'total_processed' => 0,
            'approved' => 0,
            'rejected' => 0
        ]);

        $stats['total_processed']++;
        if ($event->action === 'APPROVED') {
            $stats['approved']++;
        } else if ($event->action === 'REJECTED') {
            $stats['rejected']++;
        }

        cache()->put('approval_stats', $stats, now()->addDay());
    }

    private function sendNotifications($event)
    {
        // Additional notification logic if needed
        // This could include sending to external systems or other notification channels
    }
} 