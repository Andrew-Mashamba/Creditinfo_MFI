<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupOldReportFiles extends Command
{
    protected $signature = 'reports:cleanup-old-files {--days=30 : Number of days to keep files}';
    protected $description = 'Clean up old report files to manage storage';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Cleaning up report files older than {$days} days...");
        
        $reportDirectories = [
            storage_path('app/reports/scheduled'),
            storage_path('app/reports/generated'),
            storage_path('app/reports/temp')
        ];
        
        $totalDeleted = 0;
        $totalSize = 0;
        
        foreach ($reportDirectories as $directory) {
            if (!File::exists($directory)) {
                continue;
            }
            
            $files = File::files($directory);
            
            foreach ($files as $file) {
                $fileModified = Carbon::createFromTimestamp($file->getMTime());
                
                if ($fileModified->lt($cutoffDate)) {
                    $fileSize = $file->getSize();
                    $totalSize += $fileSize;
                    
                    if (File::delete($file->getPathname())) {
                        $totalDeleted++;
                        $this->line("Deleted: {$file->getFilename()} ({$this->formatBytes($fileSize)})");
                    }
                }
            }
        }
        
        $this->info("Cleanup completed: {$totalDeleted} files deleted, {$this->formatBytes($totalSize)} freed");
        
        Log::info("Report cleanup completed: {$totalDeleted} files deleted, {$this->formatBytes($totalSize)} freed");
        
        return 0;
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
