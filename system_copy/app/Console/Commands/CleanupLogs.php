<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanupLogs extends Command
{
    protected $signature = 'logs:cleanup {--days=30 : Number of days to keep logs}';
    protected $description = 'Clean up old log files from all channels';

    protected $logDirectories = [
        'logs',
        'logs/otp',
        'logs/auth',
        'logs/api',
        'logs/database',
        'logs/queue',
        'logs/emergency'
    ];

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        $totalDeleted = 0;

        foreach ($this->logDirectories as $directory) {
            $logPath = storage_path($directory);

            if (!File::exists($logPath)) {
                $this->info("Directory does not exist: {$directory}");
                continue;
            }

            $files = File::files($logPath);
            $deletedCount = 0;

            foreach ($files as $file) {
                $fileDate = Carbon::createFromTimestamp($file->getMTime());

                if ($fileDate->lt($cutoffDate)) {
                    File::delete($file);
                    $deletedCount++;
                    $this->info("Deleted old log file: {$file->getFilename()}");
                }
            }

            $totalDeleted += $deletedCount;
            $this->info("Cleaned up {$deletedCount} files from {$directory}");
        }

        $this->info("Cleanup completed. Total deleted files: {$totalDeleted}");
    }
}
