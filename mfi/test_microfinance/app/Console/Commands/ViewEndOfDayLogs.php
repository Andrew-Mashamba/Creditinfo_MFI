<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class ViewEndOfDayLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eod:logs 
                            {--date= : Specific date to view logs (Y-m-d format)}
                            {--today : View today\'s logs}
                            {--yesterday : View yesterday\'s logs}
                            {--tail= : Number of lines to show from the end}
                            {--search= : Search for specific text in logs}
                            {--errors : Show only errors}
                            {--summary : Show summary only}
                            {--live : Follow the log in real-time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View End of Day process logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logPath = $this->getLogPath();
        
        if (!File::exists($logPath)) {
            $this->error("Log file not found: {$logPath}");
            $this->info("No end-of-day activities have been logged for the specified date.");
            return 1;
        }

        if ($this->option('live')) {
            $this->followLog($logPath);
            return 0;
        }

        $content = File::get($logPath);
        
        if ($this->option('search')) {
            $content = $this->searchInLog($content, $this->option('search'));
        }
        
        if ($this->option('errors')) {
            $content = $this->filterErrors($content);
        }
        
        if ($this->option('summary')) {
            $this->displaySummary($content);
            return 0;
        }
        
        if ($this->option('tail')) {
            $content = $this->tailLog($content, (int)$this->option('tail'));
        }
        
        $this->displayLog($content);
        
        return 0;
    }

    /**
     * Get the log file path
     */
    protected function getLogPath(): string
    {
        $date = $this->getTargetDate();
        $filename = 'end-of-day-' . $date->format('Y-m-d') . '.log';
        
        return storage_path('logs/end-of-day/' . $filename);
    }

    /**
     * Get the target date for logs
     */
    protected function getTargetDate(): Carbon
    {
        if ($this->option('date')) {
            return Carbon::parse($this->option('date'));
        }
        
        if ($this->option('yesterday')) {
            return Carbon::yesterday();
        }
        
        // Default to today
        return Carbon::today();
    }

    /**
     * Display the log content with formatting
     */
    protected function displayLog(string $content): void
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                $this->line('');
                continue;
            }
            
            // Color code based on content
            if (str_contains($line, 'ERROR')) {
                $this->error($line);
            } elseif (str_contains($line, 'WARNING')) {
                $this->warn($line);
            } elseif (str_contains($line, '===') || str_contains($line, '---')) {
                $this->info($line);
            } elseif (str_contains($line, 'STARTED') || str_contains($line, 'COMPLETED')) {
                $this->comment($line);
            } elseif (str_contains($line, '✓')) {
                $this->line("<fg=green>{$line}</>");
            } elseif (str_contains($line, '✗')) {
                $this->line("<fg=red>{$line}</>");
            } elseif (str_contains($line, '→')) {
                $this->line("<fg=cyan>{$line}</>");
            } else {
                $this->line($line);
            }
        }
    }

    /**
     * Search for specific text in logs
     */
    protected function searchInLog(string $content, string $search): string
    {
        $lines = explode("\n", $content);
        $matched = [];
        $context = 2; // Lines before and after match
        
        foreach ($lines as $index => $line) {
            if (stripos($line, $search) !== false) {
                // Add context lines
                for ($i = max(0, $index - $context); $i <= min(count($lines) - 1, $index + $context); $i++) {
                    if ($i === $index) {
                        $matched[] = ">>> " . $lines[$i] . " <<<";
                    } else {
                        $matched[] = $lines[$i];
                    }
                }
                $matched[] = "---";
            }
        }
        
        if (empty($matched)) {
            return "No matches found for: {$search}";
        }
        
        return implode("\n", $matched);
    }

    /**
     * Filter only error messages
     */
    protected function filterErrors(string $content): string
    {
        $lines = explode("\n", $content);
        $errors = [];
        $inError = false;
        
        foreach ($lines as $line) {
            if (str_contains($line, 'ERROR') || str_contains($line, 'FAILED') || str_contains($line, '✗')) {
                $inError = true;
                $errors[] = $line;
            } elseif ($inError && (str_contains($line, '---') || str_contains($line, '==='))) {
                $errors[] = $line;
                $inError = false;
            } elseif ($inError && !empty(trim($line))) {
                $errors[] = $line;
            }
        }
        
        if (empty($errors)) {
            return "No errors found in the log.";
        }
        
        return implode("\n", $errors);
    }

    /**
     * Display summary of the log
     */
    protected function displaySummary(string $content): void
    {
        $this->info('================================================================================');
        $this->info('END OF DAY LOG SUMMARY');
        $this->info('================================================================================');
        
        // Extract key information
        $lines = explode("\n", $content);
        
        $startTime = null;
        $endTime = null;
        $triggeredBy = null;
        $status = null;
        $completed = 0;
        $failed = 0;
        $totalActivities = 0;
        
        foreach ($lines as $line) {
            if (str_contains($line, 'Date:') && !$startTime) {
                $startTime = trim(str_replace('Date:', '', $line));
            }
            if (str_contains($line, 'Triggered By:')) {
                $triggeredBy = trim(str_replace('Triggered By:', '', $line));
            }
            if (str_contains($line, 'Completion Time:')) {
                $endTime = trim(str_replace('Completion Time:', '', $line));
            }
            if (str_contains($line, 'Status:') && str_contains($line, 'SUCCESS')) {
                $status = 'SUCCESS';
            } elseif (str_contains($line, 'Status:') && str_contains($line, 'FAILED')) {
                $status = 'FAILED';
            }
            if (str_contains($line, '✓')) {
                $completed++;
                $totalActivities++;
            } elseif (str_contains($line, '✗')) {
                $failed++;
                $totalActivities++;
            }
        }
        
        $this->table(
            ['Property', 'Value'],
            [
                ['Start Time', $startTime ?? 'N/A'],
                ['End Time', $endTime ?? 'N/A'],
                ['Triggered By', $triggeredBy ?? 'N/A'],
                ['Overall Status', $status ?? 'UNKNOWN'],
                ['Total Activities', $totalActivities],
                ['Completed', $completed],
                ['Failed', $failed],
                ['Success Rate', $totalActivities > 0 ? round(($completed / $totalActivities) * 100, 2) . '%' : 'N/A'],
            ]
        );
        
        // Show failed activities if any
        if ($failed > 0) {
            $this->error("\nFailed Activities:");
            foreach ($lines as $line) {
                if (str_contains($line, '✗')) {
                    $this->error($line);
                }
            }
        }
        
        $this->info('================================================================================');
    }

    /**
     * Get the last N lines of the log
     */
    protected function tailLog(string $content, int $lines): string
    {
        $allLines = explode("\n", $content);
        $tailLines = array_slice($allLines, -$lines);
        
        return implode("\n", $tailLines);
    }

    /**
     * Follow the log file in real-time
     */
    protected function followLog(string $logPath): void
    {
        $this->info("Following log file: {$logPath}");
        $this->info("Press Ctrl+C to stop...\n");
        
        $lastPosition = 0;
        
        while (true) {
            clearstatcache(false, $logPath);
            $currentSize = filesize($logPath);
            
            if ($currentSize > $lastPosition) {
                $handle = fopen($logPath, 'r');
                fseek($handle, $lastPosition);
                
                while (!feof($handle)) {
                    $line = fgets($handle);
                    if ($line !== false) {
                        $this->displayLog($line);
                    }
                }
                
                $lastPosition = ftell($handle);
                fclose($handle);
            }
            
            sleep(1);
        }
    }
}