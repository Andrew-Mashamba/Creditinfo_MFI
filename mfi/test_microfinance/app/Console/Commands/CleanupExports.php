<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanupExports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exports:cleanup {--days=7 : Number of days to keep files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old export files from the exports directory';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $exportsPath = storage_path('app/public/exports');
        $daysToKeep = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        
        if (!file_exists($exportsPath)) {
            $this->error('Exports directory does not exist');
            return 1;
        }

        $files = File::files($exportsPath);
        $deletedCount = 0;
        $totalSize = 0;

        foreach ($files as $file) {
            // Skip .gitkeep file
            if ($file->getFilename() === '.gitkeep') {
                continue;
            }

            $fileModified = Carbon::createFromTimestamp($file->getMTime());
            
            if ($fileModified->lt($cutoffDate)) {
                $fileSize = $file->getSize();
                if (File::delete($file->getPathname())) {
                    $deletedCount++;
                    $totalSize += $fileSize;
                    $this->line("Deleted: {$file->getFilename()} ({$this->formatBytes($fileSize)})");
                } else {
                    $this->warn("Failed to delete: {$file->getFilename()}");
                }
            }
        }

        if ($deletedCount > 0) {
            $this->info("Cleanup completed: {$deletedCount} files deleted, {$this->formatBytes($totalSize)} freed");
        } else {
            $this->info("No files older than {$daysToKeep} days found");
        }

        return 0;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
} 