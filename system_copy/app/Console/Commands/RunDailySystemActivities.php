<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DailySystemActivitiesService;
use Illuminate\Support\Facades\Log;

class RunDailySystemActivities extends Command
{
    protected $signature = 'system:daily-activities
                            {--date= : Specific date to process (Y-m-d format)}
                            {--force : Force execution even if already run today}';

    protected $description = 'Run all daily system activities including dividend calculations';

    protected $activitiesService;

    public function __construct(DailySystemActivitiesService $activitiesService)
    {
        parent::__construct();
        $this->activitiesService = $activitiesService;
    }

    public function handle()
    {
        $startTime = microtime(true);
        $date = $this->option('date');
        $force = $this->option('force');

        $this->info('===========================================');
        $this->info('   Daily System Activities - Starting');
        $this->info('===========================================');
        $this->info('Timestamp: ' . now()->format('Y-m-d H:i:s'));

        if ($date) {
            $this->info('Processing date: ' . $date);
        }

        Log::channel('daily')->info('Daily System Activities Command Started', [
            'date' => $date,
            'force' => $force,
            'started_at' => now()->toIso8601String()
        ]);

        try {
            $result = $this->activitiesService->executeDailyActivities(
                'scheduled'
            );

            $duration = round(microtime(true) - $startTime, 2);

            // Handle both old format (success key) and new format (status key)
            $isSuccess = ($result['status'] ?? null) === 'success' || ($result['success'] ?? false);
            $processedDate = $result['date'] ?? $result['processed_date'] ?? now()->subDay()->format('Y-m-d');

            if ($isSuccess) {
                $this->newLine();
                $this->info('===========================================');
                $this->info('   Daily Activities Completed Successfully');
                $this->info('===========================================');
                $this->info('Date Processed: ' . $processedDate);
                $this->info('Duration: ' . $duration . ' seconds');

                // Display activities if available
                if (isset($result['activities']) && is_array($result['activities'])) {
                    $this->displayActivityResults($result['activities']);
                }

                // Display statistics if available
                if (isset($result['statistics']) && is_array($result['statistics'])) {
                    $this->newLine();
                    $this->info('--- Statistics ---');
                    foreach ($result['statistics'] as $key => $value) {
                        $this->info("  {$key}: {$value}");
                    }
                }

                Log::channel('daily')->info('Daily System Activities Completed', [
                    'date' => $processedDate,
                    'duration_seconds' => $duration,
                    'statistics' => $result['statistics'] ?? []
                ]);

                return Command::SUCCESS;

            } else {
                $errorMessage = $result['message'] ?? $result['error'] ?? 'Unknown error';

                $this->newLine();
                $this->error('===========================================');
                $this->error('   Daily Activities Failed');
                $this->error('===========================================');
                $this->error('Date: ' . $processedDate);
                $this->error('Error: ' . $errorMessage);

                Log::channel('daily')->error('Daily System Activities Failed', [
                    'date' => $processedDate,
                    'error' => $errorMessage,
                    'duration_seconds' => $duration
                ]);

                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->error('===========================================');
            $this->error('   Daily Activities Exception');
            $this->error('===========================================');
            $this->error('Exception: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());

            Log::channel('daily')->critical('Daily System Activities Exception', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'duration_seconds' => $duration
            ]);

            return Command::FAILURE;
        }
    }

    protected function displayActivityResults($activities)
    {
        $this->newLine();
        $this->info('--- Activity Results ---');
        
        $rows = collect($activities)->map(function ($result, $activity) {
            return [
                'Activity' => ucwords(str_replace('_', ' ', $activity)),
                'Status' => $result['status'] ?? 'N/A',
                'Date' => $result['date'] ?? 'N/A'
            ];
        })->toArray();

        $this->table(['Activity', 'Status', 'Date'], $rows);
    }
}
