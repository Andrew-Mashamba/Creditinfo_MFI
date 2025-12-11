<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class LogManager extends Command
{
    protected $signature = 'logs:manage {action=status : The action to perform (status|clean|list)} {--days=30 : Number of days to keep logs}';
    protected $description = 'Manage application logs';

    public function handle()
    {
        $action = $this->argument('action');
        $days = $this->option('days');

        switch ($action) {
            case 'status':
                $this->showLogStatus();
                break;
            case 'clean':
                $this->cleanOldLogs($days);
                break;
            case 'list':
                $this->listLogs();
                break;
            default:
                $this->error("Unknown action: {$action}");
                return 1;
        }

        return 0;
    }

    private function showLogStatus()
    {
        $logPath = storage_path('logs');
        $this->info('Log Status:');
        $this->info('-----------');

        if (!File::exists($logPath)) {
            $this->error('Logs directory does not exist!');
            return;
        }

        $files = File::files($logPath);
        $totalSize = 0;

        foreach ($files as $file) {
            if ($file->getExtension() === 'log') {
                $size = $file->getSize();
                $totalSize += $size;
                $lastModified = Carbon::createFromTimestamp($file->getMTime());

                $this->line(sprintf(
                    "%s - Size: %s - Last Modified: %s",
                    $file->getFilename(),
                    $this->formatSize($size),
                    $lastModified->format('Y-m-d H:i:s')
                ));
            }
        }

        $this->info('-----------');
        $this->info("Total Log Size: {$this->formatSize($totalSize)}");
    }

    private function cleanOldLogs($days)
    {
        $logPath = storage_path('logs');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Cleaning logs older than {$days} days...");

        if (!File::exists($logPath)) {
            $this->error('Logs directory does not exist!');
            return;
        }

        $files = File::files($logPath);
        $deletedCount = 0;

        foreach ($files as $file) {
            if ($file->getExtension() === 'log') {
                $lastModified = Carbon::createFromTimestamp($file->getMTime());

                if ($lastModified->lt($cutoffDate)) {
                    File::delete($file);
                    $deletedCount++;
                    $this->line("Deleted: {$file->getFilename()}");
                }
            }
        }

        $this->info("Cleaned {$deletedCount} old log files.");
    }

    private function listLogs()
    {
        $logPath = storage_path('logs');

        if (!File::exists($logPath)) {
            $this->error('Logs directory does not exist!');
            return;
        }

        $files = File::files($logPath);

        $this->info('Available Log Files:');
        $this->info('-------------------');

        foreach ($files as $file) {
            if ($file->getExtension() === 'log') {
                $this->line($file->getFilename());
            }
        }
    }

    private function formatSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }
}
