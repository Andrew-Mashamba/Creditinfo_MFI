<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class AnalyzeAuditLogs extends Command
{
    protected $signature = 'logs:analyze
                            {--date= : Specific date (YYYY-MM-DD)}
                            {--from= : Start date (YYYY-MM-DD)}
                            {--to= : End date (YYYY-MM-DD)}
                            {--user= : Filter by user ID}
                            {--level= : Filter by log level (INFO, ERROR, WARNING)}
                            {--search= : Search term in log messages}
                            {--export= : Export format (csv, json, txt)}
                            {--type= : Log type (laravel, transactions, payments, otp, email)}
                            {--limit=100 : Limit results}';

    protected $description = 'Analyze and audit log files from storage/logs';

    protected $logBasePath;

    public function __construct()
    {
        parent::__construct();
        $this->logBasePath = storage_path('logs');
    }

    public function handle()
    {
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║        NBC SACCOS - Log-Based Audit Trail Analyzer        ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Determine date range
        $dateRange = $this->determineDateRange();
        $this->info("Analyzing logs from: {$dateRange['from']} to {$dateRange['to']}");
        $this->newLine();

        // Get log files to analyze
        $logFiles = $this->getLogFiles($dateRange);

        if (empty($logFiles)) {
            $this->warn('No log files found for the specified date range.');
            return 0;
        }

        $this->info("Found " . count($logFiles) . " log file(s) to analyze");
        $this->newLine();

        // Parse logs
        $this->info('Parsing log files...');
        $parsedLogs = $this->parseLogs($logFiles);

        if (empty($parsedLogs)) {
            $this->warn('No matching log entries found.');
            return 0;
        }

        $this->info("Parsed " . count($parsedLogs) . " log entries");
        $this->newLine();

        // Apply filters
        $filteredLogs = $this->applyFilters($parsedLogs);

        $this->info("After filtering: " . count($filteredLogs) . " entries");
        $this->newLine();

        // Display statistics
        $this->displayStatistics($filteredLogs);
        $this->newLine();

        // Display sample entries
        $this->displayEntries($filteredLogs);

        // Export if requested
        if ($exportFormat = $this->option('export')) {
            $this->exportLogs($filteredLogs, $exportFormat, $dateRange);
        }

        $this->newLine();
        $this->info('✓ Analysis completed successfully!');

        return 0;
    }

    protected function determineDateRange(): array
    {
        if ($date = $this->option('date')) {
            return [
                'from' => $date,
                'to' => $date
            ];
        }

        return [
            'from' => $this->option('from') ?: now()->subDays(7)->toDateString(),
            'to' => $this->option('to') ?: now()->toDateString()
        ];
    }

    protected function getLogFiles(array $dateRange): array
    {
        $logType = $this->option('type') ?: 'laravel';
        $files = [];

        $startDate = Carbon::parse($dateRange['from']);
        $endDate = Carbon::parse($dateRange['to']);

        while ($startDate->lte($endDate)) {
            $filename = "{$logType}-{$startDate->format('Y-m-d')}.log";
            $filepath = "{$this->logBasePath}/{$filename}";

            if (File::exists($filepath)) {
                $files[] = $filepath;
            }

            $startDate->addDay();
        }

        return $files;
    }

    protected function parseLogs(array $logFiles): array
    {
        $parsedLogs = [];

        foreach ($logFiles as $filepath) {
            $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $parsed = $this->parseLogLine($line);
                if ($parsed) {
                    $parsedLogs[] = $parsed;
                }
            }
        }

        return $parsedLogs;
    }

    protected function parseLogLine(string $line): ?array
    {
        // Laravel log format: [timestamp] environment.LEVEL: message [file:line] {context}
        $pattern = '/^\[(?<timestamp>.*?)\]\s+(?<environment>\w+)\.(?<level>\w+):\s+(?<message>.*?)(\s+\[:\s+in\s+\])?\s*(?<context>\{.*\})?$/';

        if (preg_match($pattern, $line, $matches)) {
            $context = [];
            if (!empty($matches['context'])) {
                $context = json_decode($matches['context'], true) ?? [];
            }

            return [
                'timestamp' => $matches['timestamp'],
                'environment' => $matches['environment'],
                'level' => $matches['level'],
                'message' => trim($matches['message']),
                'context' => $context,
                'raw' => $line
            ];
        }

        return null;
    }

    protected function applyFilters(array $logs): array
    {
        $filtered = $logs;

        // Filter by user
        if ($userId = $this->option('user')) {
            $filtered = array_filter($filtered, function ($log) use ($userId) {
                return isset($log['context']['user_id']) && $log['context']['user_id'] == $userId;
            });
        }

        // Filter by level
        if ($level = $this->option('level')) {
            $filtered = array_filter($filtered, function ($log) use ($level) {
                return strtoupper($log['level']) === strtoupper($level);
            });
        }

        // Filter by search term
        if ($search = $this->option('search')) {
            $filtered = array_filter($filtered, function ($log) use ($search) {
                return stripos($log['message'], $search) !== false ||
                       stripos(json_encode($log['context']), $search) !== false;
            });
        }

        // Apply limit
        if ($limit = $this->option('limit')) {
            $filtered = array_slice($filtered, 0, $limit);
        }

        return array_values($filtered);
    }

    protected function displayStatistics(array $logs): void
    {
        $this->info('═══════════════════════ STATISTICS ═══════════════════════');

        // Group by level
        $byLevel = [];
        $byUser = [];
        $byHour = [];

        foreach ($logs as $log) {
            // By level
            $level = $log['level'];
            $byLevel[$level] = ($byLevel[$level] ?? 0) + 1;

            // By user
            if (isset($log['context']['user_id'])) {
                $userId = $log['context']['user_id'];
                $byUser[$userId] = ($byUser[$userId] ?? 0) + 1;
            }

            // By hour
            $hour = date('H:00', strtotime($log['timestamp']));
            $byHour[$hour] = ($byHour[$hour] ?? 0) + 1;
        }

        // Display level distribution
        $this->line('  Log Levels:');
        foreach ($byLevel as $level => $count) {
            $color = match(strtoupper($level)) {
                'ERROR' => 'red',
                'WARNING' => 'yellow',
                'INFO' => 'green',
                default => 'white'
            };
            $this->line("    <fg={$color}>• {$level}: {$count}</>");
        }

        // Display top users
        if (!empty($byUser)) {
            $this->newLine();
            $this->line('  Top 5 Active Users:');
            arsort($byUser);
            $topUsers = array_slice($byUser, 0, 5, true);
            foreach ($topUsers as $userId => $count) {
                $this->line("    • User ID {$userId}: {$count} actions");
            }
        }

        // Display activity by hour
        if (!empty($byHour)) {
            $this->newLine();
            $this->line('  Activity by Hour:');
            ksort($byHour);
            $topHours = array_slice($byHour, 0, 5, true);
            foreach ($topHours as $hour => $count) {
                $bar = str_repeat('█', min($count / 10, 50));
                $this->line("    {$hour}: <fg=cyan>{$bar}</> ({$count})");
            }
        }
    }

    protected function displayEntries(array $logs): void
    {
        $this->info('═══════════════════════ LOG ENTRIES ═══════════════════════');

        $headers = ['Timestamp', 'Level', 'User', 'Message'];
        $rows = [];

        foreach (array_slice($logs, 0, 20) as $log) {
            $userId = $log['context']['user_id'] ?? 'system';
            $timestamp = Carbon::parse($log['timestamp'])->format('Y-m-d H:i:s');

            $rows[] = [
                $timestamp,
                $log['level'],
                $userId,
                \Str::limit($log['message'], 60),
            ];
        }

        $this->table($headers, $rows);

        if (count($logs) > 20) {
            $this->warn("  Showing first 20 of " . count($logs) . " entries. Use --export to get full data.");
        }
    }

    protected function exportLogs(array $logs, string $format, array $dateRange): void
    {
        $this->info("Exporting to {$format} format...");

        $filename = "audit-logs-{$dateRange['from']}-to-{$dateRange['to']}.{$format}";
        $filepath = storage_path("app/exports/{$filename}");

        // Ensure directory exists
        if (!File::isDirectory(dirname($filepath))) {
            File::makeDirectory(dirname($filepath), 0755, true);
        }

        switch ($format) {
            case 'csv':
                $this->exportToCsv($logs, $filepath);
                break;
            case 'json':
                $this->exportToJson($logs, $filepath);
                break;
            case 'txt':
                $this->exportToText($logs, $filepath);
                break;
            default:
                $this->error("Unsupported export format: {$format}");
                return;
        }

        $this->info("✓ Exported to: {$filepath}");
    }

    protected function exportToCsv(array $logs, string $filepath): void
    {
        $handle = fopen($filepath, 'w');

        // Headers
        fputcsv($handle, [
            'Timestamp',
            'Level',
            'Environment',
            'User ID',
            'Module',
            'Message',
            'Context (JSON)'
        ]);

        // Data
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log['timestamp'],
                $log['level'],
                $log['environment'],
                $log['context']['user_id'] ?? '',
                $log['context']['module'] ?? '',
                $log['message'],
                json_encode($log['context'])
            ]);
        }

        fclose($handle);
    }

    protected function exportToJson(array $logs, string $filepath): void
    {
        File::put($filepath, json_encode($logs, JSON_PRETTY_PRINT));
    }

    protected function exportToText(array $logs, string $filepath): void
    {
        $content = "NBC SACCOS - Audit Trail Report\n";
        $content .= "Generated: " . now()->toDateTimeString() . "\n";
        $content .= str_repeat('=', 80) . "\n\n";

        foreach ($logs as $log) {
            $content .= "[{$log['timestamp']}] {$log['level']}\n";
            $content .= "User: " . ($log['context']['user_id'] ?? 'System') . "\n";
            $content .= "Message: {$log['message']}\n";
            if (!empty($log['context'])) {
                $content .= "Context: " . json_encode($log['context'], JSON_PRETTY_PRINT) . "\n";
            }
            $content .= str_repeat('-', 80) . "\n\n";
        }

        File::put($filepath, $content);
    }
}
