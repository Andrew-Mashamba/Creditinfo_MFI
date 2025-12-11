<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ViewPromptLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:prompt {--session= : Filter by session ID} {--tail=50 : Number of lines to show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View prompt chain logs from storage/logs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sessionId = $this->option('session');
        $tail = $this->option('tail');
        
        // Get today's log file
        $logFile = storage_path('logs/laravel-' . now()->format('Y-m-d') . '.log');
        
        if (!File::exists($logFile)) {
            $this->error("Log file not found: {$logFile}");
            return 1;
        }
        
        // Read the log file
        $content = File::get($logFile);
        $lines = explode("\n", $content);
        
        // Filter for PROMPT-CHAIN logs
        $promptLogs = [];
        foreach ($lines as $line) {
            if (strpos($line, '[PROMPT-CHAIN') !== false) {
                // If session filter is provided, check for it
                if ($sessionId && strpos($line, $sessionId) === false) {
                    continue;
                }
                
                // Parse the log entry
                $logEntry = $this->parseLogEntry($line);
                if ($logEntry) {
                    $promptLogs[] = $logEntry;
                }
            }
        }
        
        // Sort by timestamp (newest first)
        usort($promptLogs, function($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });
        
        // Limit to tail number
        $promptLogs = array_slice($promptLogs, 0, $tail);
        
        // Display the logs
        $this->displayLogs($promptLogs);
        
        return 0;
    }
    
    /**
     * Parse a log entry
     */
    private function parseLogEntry($line)
    {
        // Extract timestamp
        preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $timestampMatch);
        $timestamp = $timestampMatch[1] ?? '';
        
        // Extract log level
        preg_match('/local\.(INFO|WARNING|ERROR|DEBUG):/', $line, $levelMatch);
        $level = $levelMatch[1] ?? 'INFO';
        
        // Extract emoji and title
        preg_match('/([🔵🟣🔴🟡🟢🟠🔶🔷🔸🔹⚠️❌🔄✅🔁]) \[PROMPT-CHAIN[^\]]*\] ([^{]+)/', $line, $titleMatch);
        $emoji = $titleMatch[1] ?? '';
        $title = trim($titleMatch[2] ?? '');
        
        // Extract JSON data
        preg_match('/(\{.*\})/', $line, $jsonMatch);
        $data = [];
        if (isset($jsonMatch[1])) {
            $jsonData = json_decode($jsonMatch[1], true);
            if ($jsonData) {
                $data = $jsonData;
            }
        }
        
        return [
            'timestamp' => $timestamp,
            'level' => $level,
            'emoji' => $emoji,
            'title' => $title,
            'step' => $data['step'] ?? 0,
            'session_id' => $data['session_id'] ?? '',
            'location' => $data['location'] ?? '',
            'data' => $data
        ];
    }
    
    /**
     * Display the logs in a formatted way
     */
    private function displayLogs($logs)
    {
        if (empty($logs)) {
            $this->info('No prompt chain logs found.');
            return;
        }
        
        $this->info('');
        $this->info('════════════════════════════════════════════════════════════');
        $this->info('                    PROMPT CHAIN LOGS                       ');
        $this->info('════════════════════════════════════════════════════════════');
        $this->info('');
        
        foreach ($logs as $log) {
            // Color based on level
            $color = match($log['level']) {
                'ERROR' => 'error',
                'WARNING' => 'warn',
                default => 'info'
            };
            
            // Format the output
            $output = sprintf(
                "%s [%s] %s %s",
                $log['timestamp'],
                str_pad("Step {$log['step']}", 7),
                $log['emoji'],
                $log['title']
            );
            
            $this->line($output, $color);
            
            if ($log['session_id']) {
                $this->line("   Session: {$log['session_id']}", 'comment');
            }
            
            if ($log['location']) {
                $this->line("   Location: {$log['location']}", 'comment');
            }
            
            // Show additional data if present
            foreach ($log['data'] as $key => $value) {
                if (!in_array($key, ['step', 'session_id', 'location'])) {
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                    $this->line("   {$key}: {$value}", 'comment');
                }
            }
            
            $this->info('────────────────────────────────────────────────────────────');
        }
        
        $this->info('');
        $this->info('Total logs shown: ' . count($logs));
    }
}