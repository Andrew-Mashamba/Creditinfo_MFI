<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ViewSystemLogs extends Command
{
    protected $signature = 'system:logs 
                            {--days=7 : Number of days to show logs for}
                            {--resource= : Filter by specific resource (cpu, memory, network, storage, web-server)}
                            {--format=table : Output format (table, json)}';

    protected $description = 'View system monitoring logs';

    public function handle()
    {
        $days = $this->option('days');
        $resource = $this->option('resource');
        $format = $this->option('format');

        $logs = $this->getLogs($days, $resource);

        if (empty($logs)) {
            $this->info('No logs found for the specified criteria.');
            return;
        }

        if ($format === 'json') {
            $this->outputJson($logs);
        } else {
            $this->outputTable($logs);
        }
    }

    protected function getLogs($days, $resource = null)
    {
        $disk = Storage::disk(config('stethoscope.storage.driver'));
        $path = config('stethoscope.storage.path');
        $files = $disk->files($path);
        
        $logs = [];
        $cutoffDate = Carbon::now()->subDays($days);

        foreach ($files as $file) {
            if (!preg_match('/\.log$/', $file)) {
                continue;
            }

            $fileDate = Carbon::createFromTimestamp($disk->lastModified($file));
            if ($fileDate->lt($cutoffDate)) {
                continue;
            }

            $content = $disk->get($file);
            $lines = explode("\n", $content);

            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }

                $logEntry = json_decode($line, true);
                if (!$logEntry) {
                    continue;
                }

                if ($resource && !isset($logEntry[$resource])) {
                    continue;
                }

                $logs[] = $logEntry;
            }
        }

        return $logs;
    }

    protected function outputTable($logs)
    {
        $headers = ['Timestamp', 'Resource', 'Value', 'Status'];
        $rows = [];

        foreach ($logs as $log) {
            foreach ($log as $resource => $value) {
                if ($resource === 'signature') {
                    continue;
                }

                $status = $this->getStatus($resource, $value);
                $rows[] = [
                    Carbon::parse($log['timestamp'])->format('Y-m-d H:i:s'),
                    $resource,
                    $value,
                    $status,
                ];
            }
        }

        $this->table($headers, $rows);
    }

    protected function outputJson($logs)
    {
        $this->line(json_encode($logs, JSON_PRETTY_PRINT));
    }

    protected function getStatus($resource, $value)
    {
        $threshold = config("stethoscope.thresholds.{$resource}");
        
        if (!$threshold) {
            return 'N/A';
        }

        if ($resource === 'network' && $value === 'disconnected') {
            return 'Critical';
        }

        if ($resource === 'web_server' && $value !== 'active') {
            return 'Critical';
        }

        if (is_numeric($value) && $value > $threshold) {
            return 'Warning';
        }

        return 'Normal';
    }
} 