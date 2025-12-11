<?php

namespace App\Http\Livewire\ProfileSetting;

use Livewire\Component;
use App\Models\DailyActivityStatus;
use App\Services\DailySystemActivitiesService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EndOfDay extends Component
{
    public $activities = [];
    public $lastRunDate;
    public $nextRunTime;
    public $isRunning = false;
    public $overallProgress = 0;
    public $autoRefresh = true;
    public $refreshInterval = 5; // seconds
    public $showLogs = false;
    public $logContent = '';
    public $selectedDate = null;
    public $logType = 'summary'; // summary, full, errors
    
    protected $listeners = [
        'refreshActivities' => 'loadActivities',
        'showActivityLogs' => 'loadLogs'
    ];

    public function mount()
    {
        $this->initializeActivities();
        $this->loadActivities();
        $this->loadScheduleInfo();
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function initializeActivities()
    {
        // Define all daily activities with their keys and names
        $activityDefinitions = [
            ['key' => 'inactive_members', 'name' => 'Inactive Members'],
            ['key' => 'share_accounts', 'name' => 'Share accounts maintenance'],
            ['key' => 'savings_maintenance', 'name' => 'Savings accounts maintenance'],
            ['key' => 'savings_interest', 'name' => 'Savings accounts interest calculation'],
            ['key' => 'maturing_savings', 'name' => 'Maturing Savings accounts'],
            ['key' => 'deposit_maintenance', 'name' => 'Deposit accounts maintenance'],
            ['key' => 'deposit_interest', 'name' => 'Deposit accounts interest calculation'],
            ['key' => 'maturing_deposits', 'name' => 'Maturing Deposit accounts'],
            ['key' => 'loan_notifications', 'name' => 'Loan repayment notifications'],
            ['key' => 'repayments_collection', 'name' => 'Repayments collection'],
            ['key' => 'till_maintenance', 'name' => 'Till accounts maintenance'],
            ['key' => 'reconciliation', 'name' => 'Reconciliation'],
            ['key' => 'payroll_processing', 'name' => 'Payroll processing'],
            ['key' => 'depreciation', 'name' => 'Depreciation calculation'],
            ['key' => 'pending_approvals', 'name' => 'Pending approvals'],
            ['key' => 'compliance_reports', 'name' => 'Compliance reports generation'],
            ['key' => 'financial_year_check', 'name' => 'Financial Year Date Check'],
            ['key' => 'expiring_passwords', 'name' => 'Expiring Passwords'],
        ];

        // Create or get activities for today
        foreach ($activityDefinitions as $definition) {
            DailyActivityStatus::getOrCreateActivity($definition['key'], $definition['name']);
        }
    }

    public function loadActivities()
    {
        $this->activities = DailyActivityStatus::getTodayActivities()->map(function ($activity) {
            return [
                'id' => $activity->id,
                'key' => $activity->activity_key,
                'name' => $activity->activity_name,
                'status' => $activity->status,
                'progress' => $activity->progress,
                'statusColor' => $activity->status_color,
                'totalRecords' => $activity->total_records,
                'processedRecords' => $activity->processed_records,
                'failedRecords' => $activity->failed_records,
                'executionTime' => $activity->formatted_execution_time,
                'lastError' => $activity->last_error,
                'startedAt' => $activity->started_at ? $activity->started_at->format('H:i:s') : null,
                'completedAt' => $activity->completed_at ? $activity->completed_at->format('H:i:s') : null,
            ];
        })->toArray();

        // Calculate overall progress
        $totalActivities = count($this->activities);
        $completedActivities = collect($this->activities)->where('status', 'completed')->count();
        $this->overallProgress = $totalActivities > 0 ? round(($completedActivities / $totalActivities) * 100) : 0;

        // Check if any activity is running
        $this->isRunning = collect($this->activities)->contains('status', 'running');
    }

    public function loadScheduleInfo()
    {
        // Get last run info from cache or database
        $lastRun = Cache::get('last_daily_activities_run');
        if ($lastRun) {
            $this->lastRunDate = Carbon::parse($lastRun)->format('Y-m-d H:i:s');
        } else {
            // Check from database
            $lastCompleted = DailyActivityStatus::where('status', 'completed')
                ->orderBy('completed_at', 'desc')
                ->first();
            
            if ($lastCompleted) {
                $this->lastRunDate = $lastCompleted->completed_at->format('Y-m-d H:i:s');
            }
        }

        // Next run is at 00:05
        $now = Carbon::now();
        $nextRun = Carbon::today()->addDay()->addMinutes(5); // Tomorrow at 00:05
        
        if ($now->hour < 1 || ($now->hour == 0 && $now->minute < 5)) {
            // If it's before 00:05 today, next run is today
            $nextRun = Carbon::today()->addMinutes(5);
        }
        
        $this->nextRunTime = $nextRun->format('Y-m-d H:i:s');
    }

    public function runManually()
    {
        try {
            // Check if already running
            if ($this->isRunning) {
                session()->flash('error', 'Daily activities are already running. Please wait for completion.');
                return;
            }

            // Dispatch the job to run in background
            dispatch(function () {
                $service = app(DailySystemActivitiesService::class);
                $service->executeDailyActivities('manual');
            })->onQueue('default');

            session()->flash('success', 'Daily activities started successfully. The page will auto-refresh to show progress.');
            
            // Mark as running
            $this->isRunning = true;
            
            // Enable auto-refresh
            $this->autoRefresh = true;
            
            // Refresh immediately
            $this->loadActivities();
            
        } catch (\Exception $e) {
            Log::error('Failed to start daily activities manually: ' . $e->getMessage());
            session()->flash('error', 'Failed to start daily activities: ' . $e->getMessage());
        }
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    public function getProgressBarClass($progress)
    {
        if ($progress >= 80) {
            return 'bg-green-500';
        } elseif ($progress >= 60) {
            return 'bg-yellow-500';
        } else {
            return 'bg-red-500';
        }
    }

    public function getStatusBadgeClass($status)
    {
        return match($status) {
            'completed' => 'bg-green-50 text-green-700',
            'running' => 'bg-blue-50 text-blue-700',
            'failed' => 'bg-red-50 text-red-700',
            'skipped' => 'bg-gray-50 text-gray-700',
            default => 'bg-yellow-50 text-yellow-700'
        };
    }

    public function getStatusIcon($status)
    {
        return match($status) {
            'completed' => '✓',
            'running' => '↻',
            'failed' => '✗',
            'skipped' => '−',
            default => '○'
        };
    }

    public function viewLogs()
    {
        $this->showLogs = true;
        $this->loadLogs();
    }

    public function closeLogs()
    {
        $this->showLogs = false;
        $this->logContent = '';
    }

    public function loadLogs()
    {
        try {
            $logPath = storage_path('logs/end-of-day/end-of-day-' . $this->selectedDate . '.log');
            
            if (!file_exists($logPath)) {
                $this->logContent = "No logs found for " . $this->selectedDate;
                return;
            }

            $content = file_get_contents($logPath);
            
            switch ($this->logType) {
                case 'summary':
                    $this->logContent = $this->extractSummary($content);
                    break;
                case 'errors':
                    $this->logContent = $this->extractErrors($content);
                    break;
                default:
                    $this->logContent = $content;
            }
        } catch (\Exception $e) {
            $this->logContent = "Error loading logs: " . $e->getMessage();
        }
    }

    protected function extractSummary($content)
    {
        $lines = explode("\n", $content);
        $summary = [];
        $inSummary = false;
        
        foreach ($lines as $line) {
            if (str_contains($line, 'END OF DAY SUMMARY')) {
                $inSummary = true;
            }
            
            if ($inSummary) {
                $summary[] = $line;
            }
            
            if ($inSummary && str_contains($line, '================================================================================') && count($summary) > 5) {
                break;
            }
        }
        
        return empty($summary) ? "No summary found in logs" : implode("\n", $summary);
    }

    protected function extractErrors($content)
    {
        $lines = explode("\n", $content);
        $errors = [];
        
        foreach ($lines as $line) {
            if (str_contains($line, 'ERROR') || str_contains($line, 'FAILED') || str_contains($line, '✗')) {
                $errors[] = $line;
            }
        }
        
        return empty($errors) ? "No errors found in logs" : implode("\n", $errors);
    }

    public function downloadLogs()
    {
        $logPath = storage_path('logs/end-of-day/end-of-day-' . $this->selectedDate . '.log');
        
        if (file_exists($logPath)) {
            return response()->download($logPath);
        }
        
        session()->flash('error', 'Log file not found');
    }

    public function render()
    {
        return view('livewire.profile-setting.end-of-day');
    }
}