<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ClientsModel;
use App\Jobs\ProcessMemberNotifications;
use Illuminate\Support\Facades\Log;

class TestNotificationService extends Command
{
    protected $signature = 'test:notifications {--member_id=} {--guarantor_id=}';
    protected $description = 'Test the notification service by simulating a member registration';

    public function handle()
    {
        try {
            $this->info('Starting notification service test...');

            // Get member and guarantor IDs from options or use defaults
            $memberId = $this->option('member_id');
            $guarantorId = $this->option('guarantor_id');

            if (!$memberId) {
                $memberId = $this->ask('Please enter the member ID to test with');
            }

            // Get member from database
            $member = ClientsModel::findOrFail($memberId);
            $this->info("Found member: {$member->full_name}");

            // Get guarantor if specified
            $guarantor = null;
            if ($guarantorId) {
                $guarantor = ClientsModel::findOrFail($guarantorId);
                $this->info("Found guarantor: {$guarantor->full_name}");
            }

            // Create a sample control number
            $controlNumbers = [
                [
                    'control_number' => 'CTRL-' . strtoupper(uniqid()),
                    'amount' => 100000,
                    'description' => 'Registration Fee'
                ]
            ];

            // Log the test start
            Log::info('Starting notification service test', [
                'member_id' => $member->id,
                'guarantor_id' => $guarantor?->id,
                'control_numbers' => $controlNumbers
            ]);

            // Dispatch the notification job
            ProcessMemberNotifications::dispatch($member, $controlNumbers)
                ->onQueue('notifications');

            $this->info('Notification job dispatched successfully!');
            $this->info('Check the logs and database for results.');
            $this->info('You can monitor the job status using: php artisan queue:monitor notifications');

        } catch (\Exception $e) {
            $this->error('Error testing notification service:');
            $this->error($e->getMessage());
            Log::error('Error in notification service test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 