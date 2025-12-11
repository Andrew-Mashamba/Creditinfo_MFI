<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ResellerApiLogger;
use Carbon\Carbon;

class ResellerApiLogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reseller-api:logs 
                            {action : The action to perform (stats|clean|view)}
                            {--days=7 : Number of days for stats or clean operations}
                            {--limit=50 : Number of recent entries to show for view action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Reseller API logs - view stats, clean old logs, or view recent entries';

    /**
     * The Reseller API logger instance.
     *
     * @var ResellerApiLogger
     */
    private $logger;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->logger = new ResellerApiLogger();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');
        $days = (int) $this->option('days');
        $limit = (int) $this->option('limit');

        switch ($action) {
            case 'stats':
                return $this->showStats($days);
            case 'clean':
                return $this->cleanLogs($days);
            case 'view':
                return $this->viewLogs($limit);
            default:
                $this->error("Invalid action: {$action}");
                $this->info('Available actions: stats, clean, view');
                return 1;
        }
    }

    /**
     * Show log statistics
     */
    private function showStats($days)
    {
        $this->info("Reseller API Log Statistics (Last {$days} days)");
        $this->line('');

        $stats = $this->logger->getLogStats($days);

        // Create a table for the statistics
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Requests', $stats['total_requests']],
                ['Successful Requests', $stats['successful_requests']],
                ['Failed Requests', $stats['failed_requests']],
                ['Success Rate', $this->calculateSuccessRate($stats)],
                ['Average Response Time', $stats['average_response_time'] . ' ms'],
            ]
        );

        if (!empty($stats['most_common_operations'])) {
            $this->line('');
            $this->info('Most Common Operations:');
            $operations = [];
            foreach ($stats['most_common_operations'] as $operation => $count) {
                $operations[] = [$operation, $count];
            }
            $this->table(['Operation', 'Count'], $operations);
        }

        return 0;
    }

    /**
     * Clean old logs
     */
    private function cleanLogs($days)
    {
        $this->info("Cleaning Reseller API logs older than {$days} days...");
        
        $this->logger->cleanOldLogs($days);
        
        $this->info('Log cleanup completed successfully!');
        
        return 0;
    }

    /**
     * View recent log entries
     */
    private function viewLogs($limit)
    {
        $logPath = storage_path('logs/reseller-api/reseller-api.log');
        
        if (!file_exists($logPath)) {
            $this->warn('No Reseller API log file found.');
            return 0;
        }

        $this->info("Recent Reseller API Log Entries (Last {$limit} entries)");
        $this->line('');

        $logs = file_get_contents($logPath);
        $entries = $this->parseLogEntries($logs);
        
        // Get the most recent entries
        $recentEntries = array_slice($entries, -$limit);
        
        if (empty($recentEntries)) {
            $this->warn('No log entries found.');
            return 0;
        }

        foreach ($recentEntries as $entry) {
            $this->displayLogEntry($entry);
            $this->line('');
        }

        return 0;
    }

    /**
     * Parse log entries from the log file
     */
    private function parseLogEntries($logs)
    {
        $entries = [];
        $lines = explode("\n", $logs);
        $currentEntry = '';
        $inEntry = false;

        foreach ($lines as $line) {
            if (strpos($line, '=') === 0) {
                if ($inEntry && !empty(trim($currentEntry))) {
                    $logData = json_decode($currentEntry, true);
                    if ($logData) {
                        $entries[] = $logData;
                    }
                }
                $currentEntry = '';
                $inEntry = true;
            } else {
                $currentEntry .= $line . "\n";
            }
        }

        // Handle the last entry
        if ($inEntry && !empty(trim($currentEntry))) {
            $logData = json_decode($currentEntry, true);
            if ($logData) {
                $entries[] = $logData;
            }
        }

        return $entries;
    }

    /**
     * Display a log entry in a formatted way
     */
    private function displayLogEntry($entry)
    {
        $timestamp = $entry['timestamp'] ?? 'Unknown';
        $type = $entry['type'] ?? 'Unknown';
        
        // Format timestamp
        try {
            $formattedTime = Carbon::parse($timestamp)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            $formattedTime = $timestamp;
        }

        // Color code based on type
        $typeColor = $this->getTypeColor($type);
        
        $this->line("<{$typeColor}>{$type}</{$typeColor}> - {$formattedTime}");
        
        if (isset($entry['request_id'])) {
            $this->line("Request ID: {$entry['request_id']}");
        }
        
        if (isset($entry['domain'])) {
            $this->line("Domain: {$entry['domain']}");
        }
        
        if (isset($entry['operation'])) {
            $this->line("Operation: {$entry['operation']}");
        }
        
        if (isset($entry['status_code'])) {
            $statusColor = $entry['status_code'] >= 200 && $entry['status_code'] < 300 ? 'green' : 'red';
            $this->line("Status: <{$statusColor}>{$entry['status_code']}</{$statusColor}>");
        }
        
        if (isset($entry['response_time_ms'])) {
            $this->line("Response Time: {$entry['response_time_ms']} ms");
        }
        
        if (isset($entry['error'])) {
            $this->line("<red>Error: {$entry['error']}</red>");
        }
        
        if (isset($entry['message'])) {
            $this->line("Message: {$entry['message']}");
        }
    }

    /**
     * Get color for log type
     */
    private function getTypeColor($type)
    {
        switch ($type) {
            case 'REQUEST':
                return 'blue';
            case 'RESPONSE':
                return 'green';
            case 'ERROR':
                return 'red';
            case 'DOMAIN_OPERATION':
                return 'yellow';
            case 'RATE_LIMIT':
                return 'magenta';
            default:
                return 'white';
        }
    }

    /**
     * Calculate success rate percentage
     */
    private function calculateSuccessRate($stats)
    {
        if ($stats['total_requests'] == 0) {
            return '0%';
        }
        
        $successRate = ($stats['successful_requests'] / $stats['total_requests']) * 100;
        return round($successRate, 2) . '%';
    }
}