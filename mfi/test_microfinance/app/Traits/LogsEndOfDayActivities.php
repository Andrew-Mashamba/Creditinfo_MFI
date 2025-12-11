<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

trait LogsEndOfDayActivities
{
    protected $eodLogger;
    protected $activityStartTime;
    protected $currentActivityName;

    /**
     * Initialize the EOD logger
     */
    protected function initializeEodLogger()
    {
        $this->eodLogger = Log::channel('end_of_day');
    }

    /**
     * Log the start of the end-of-day process
     */
    protected function logEodStart($triggeredBy = 'system')
    {
        $this->initializeEodLogger();
        
        $this->eodLogger->info('================================================================================');
        $this->eodLogger->info('END OF DAY PROCESS STARTED');
        $this->eodLogger->info('================================================================================');
        $this->eodLogger->info('Date: ' . Carbon::now()->format('Y-m-d H:i:s'));
        $this->eodLogger->info('Triggered By: ' . $triggeredBy);
        $this->eodLogger->info('Process Date: ' . $this->previousDay->format('Y-m-d'));
        $this->eodLogger->info('================================================================================');
    }

    /**
     * Log the completion of the end-of-day process
     */
    protected function logEodComplete($status = 'success', $message = null)
    {
        $this->eodLogger->info('================================================================================');
        $this->eodLogger->info('END OF DAY PROCESS COMPLETED');
        $this->eodLogger->info('Status: ' . strtoupper($status));
        if ($message) {
            $this->eodLogger->info('Message: ' . $message);
        }
        $this->eodLogger->info('Completion Time: ' . Carbon::now()->format('Y-m-d H:i:s'));
        $this->eodLogger->info('================================================================================');
        $this->eodLogger->info('');
    }

    /**
     * Log the start of an activity
     */
    protected function logActivityStart($activityName, $details = [])
    {
        $this->activityStartTime = microtime(true);
        $this->currentActivityName = $activityName;
        
        $this->eodLogger->info('');
        $this->eodLogger->info('--- ' . strtoupper($activityName) . ' STARTED ---');
        $this->eodLogger->info('Start Time: ' . Carbon::now()->format('H:i:s'));
        
        if (!empty($details)) {
            foreach ($details as $key => $value) {
                $this->eodLogger->info($key . ': ' . (is_array($value) ? json_encode($value) : $value));
            }
        }
    }

    /**
     * Log activity progress
     */
    protected function logActivityProgress($message, $current = null, $total = null)
    {
        $logMessage = '  → ' . $message;
        
        if ($current !== null && $total !== null) {
            $percentage = $total > 0 ? round(($current / $total) * 100, 2) : 0;
            $logMessage .= " [{$current}/{$total}] ({$percentage}%)";
        }
        
        $this->eodLogger->info($logMessage);
    }

    /**
     * Log the completion of an activity
     */
    protected function logActivityComplete($status = 'success', $details = [])
    {
        $executionTime = round(microtime(true) - $this->activityStartTime, 2);
        
        $statusSymbol = $status === 'success' ? '✓' : ($status === 'failed' ? '✗' : '⚠');
        
        $this->eodLogger->info('Status: ' . $statusSymbol . ' ' . strtoupper($status));
        $this->eodLogger->info('Execution Time: ' . $executionTime . ' seconds');
        
        if (!empty($details)) {
            foreach ($details as $key => $value) {
                $this->eodLogger->info($key . ': ' . (is_array($value) ? json_encode($value) : $value));
            }
        }
        
        $this->eodLogger->info('--- ' . strtoupper($this->currentActivityName) . ' COMPLETED ---');
    }

    /**
     * Log an error
     */
    protected function logActivityError($error, $context = [])
    {
        $this->eodLogger->error('ERROR: ' . $error);
        
        if (!empty($context)) {
            $this->eodLogger->error('Context: ' . json_encode($context));
        }
        
        if ($error instanceof \Exception) {
            $this->eodLogger->error('Stack Trace: ' . $error->getTraceAsString());
        }
    }

    /**
     * Log a warning
     */
    protected function logActivityWarning($message, $context = [])
    {
        $this->eodLogger->warning('WARNING: ' . $message);
        
        if (!empty($context)) {
            $this->eodLogger->warning('Context: ' . json_encode($context));
        }
    }

    /**
     * Log activity statistics
     */
    protected function logActivityStatistics($stats)
    {
        $this->eodLogger->info('');
        $this->eodLogger->info('=== ACTIVITY STATISTICS ===');
        
        foreach ($stats as $key => $value) {
            $formattedKey = str_replace('_', ' ', ucfirst($key));
            $formattedValue = is_numeric($value) ? number_format($value) : $value;
            $this->eodLogger->info($formattedKey . ': ' . $formattedValue);
        }
        
        $this->eodLogger->info('===========================');
    }

    /**
     * Create a summary log entry
     */
    protected function logEodSummary($activities)
    {
        $this->eodLogger->info('');
        $this->eodLogger->info('================================================================================');
        $this->eodLogger->info('END OF DAY SUMMARY');
        $this->eodLogger->info('================================================================================');
        
        $completed = 0;
        $failed = 0;
        $skipped = 0;
        $totalTime = 0;
        
        foreach ($activities as $activity) {
            $status = $activity['status'] ?? 'pending';
            $name = $activity['name'] ?? $activity['activity_name'] ?? 'Unknown';
            $time = $activity['execution_time_seconds'] ?? 0;
            
            $statusSymbol = match($status) {
                'completed' => '✓',
                'failed' => '✗',
                'skipped' => '−',
                default => '○'
            };
            
            $this->eodLogger->info(sprintf(
                "%s %-40s %s (%ds)",
                $statusSymbol,
                $name,
                strtoupper($status),
                $time
            ));
            
            if ($status === 'completed') $completed++;
            elseif ($status === 'failed') $failed++;
            elseif ($status === 'skipped') $skipped++;
            
            $totalTime += $time;
        }
        
        $this->eodLogger->info('--------------------------------------------------------------------------------');
        $this->eodLogger->info('Total Activities: ' . count($activities));
        $this->eodLogger->info('Completed: ' . $completed);
        $this->eodLogger->info('Failed: ' . $failed);
        $this->eodLogger->info('Skipped: ' . $skipped);
        $this->eodLogger->info('Total Execution Time: ' . $this->formatExecutionTime($totalTime));
        $this->eodLogger->info('================================================================================');
    }

    /**
     * Format execution time
     */
    protected function formatExecutionTime($seconds)
    {
        if ($seconds < 60) {
            return $seconds . ' seconds';
        }
        
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        return $minutes . ' minutes ' . $remainingSeconds . ' seconds';
    }
}