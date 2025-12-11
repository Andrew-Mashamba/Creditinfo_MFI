<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanupOtpLogs extends Command
{
    protected $signature = 'otp:cleanup-logs {--days=30 : Number of days to keep logs}';
    protected $description = 'Clean up old OTP log files';

    public function handle()
    {
        $days = $this->option('days');
        $logPath = storage_path('logs/otp');
        $cutoffDate = Carbon::now()->subDays($days);

        if (!File::exists($logPath)) {
            $this->info('OTP logs directory does not exist.');
            return;
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

        $this->info("Cleanup completed. Deleted {$deletedCount} old log files.");
    }
}
