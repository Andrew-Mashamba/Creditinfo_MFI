<?php

namespace App\Http\Livewire\ProfileSetting;

use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use App\Models\ScheduledTaskLog;
use App\Models\ScheduledTaskSetting;
use Carbon\Carbon;
use App\Http\Livewire\ProfileSetting\ScheduledTaskDefinitions;

class ScheduledTasks extends Component
{
    public $tasks = [];
    public $lastRefresh;
    public $runningTask = null;
    public $taskOutput = '';
    public $showOutput = false;
    public $selectedTask = null;
    public $taskHistory = [];
    public $showHistory = false;

    // Calendar properties
    public $calendarYear;
    public $calendarMonth;
    public $calendarData = [];
    public $selectedDate = null;
    public $selectedDateTasks = [];
    public $showDateModal = false;

    // Statistics
    public $stats = [];

    // View mode: 'table' or 'calendar'
    public $viewMode = 'table';

    // Filter properties
    public $categoryFilter = '';
    public $frequencyFilter = '';
    public $searchTerm = '';
    public $enabledFilter = ''; // '', 'enabled', 'disabled'

    // Task settings cache
    public $taskSettings = [];

    protected $listeners = ['refreshTasks' => 'loadTasks'];

    public function mount()
    {
        $this->calendarYear = now()->year;
        $this->calendarMonth = now()->month;
        $this->loadTaskSettings();
        $this->loadTasks();
        $this->loadStats();
        $this->loadCalendarData();
    }

    public function loadTaskSettings()
    {
        $this->taskSettings = ScheduledTaskSetting::getEnabledStatusMap();
    }

    public function loadTasks()
    {
        $this->tasks = $this->getScheduledTasks();
        $this->lastRefresh = now()->format('Y-m-d H:i:s');
    }

    public function loadStats()
    {
        $this->stats = ScheduledTaskLog::getStatistics(7);
    }

    public function loadCalendarData()
    {
        $this->calendarData = ScheduledTaskLog::getCalendarData($this->calendarYear, $this->calendarMonth);
    }

    protected function getScheduledTasks()
    {
        // Get all 500+ scheduled task definitions
        $taskDefinitions = ScheduledTaskDefinitions::getAllTasks();

        // Apply filters
        $filteredTasks = collect($taskDefinitions);

        // Filter by category
        if (!empty($this->categoryFilter)) {
            $filteredTasks = $filteredTasks->filter(function ($task) {
                return $task['category'] === $this->categoryFilter;
            });
        }

        // Filter by frequency
        if (!empty($this->frequencyFilter)) {
            $frequencyTasks = $this->getTasksByFrequency($this->frequencyFilter);
            $frequencyIds = collect($frequencyTasks)->pluck('id')->toArray();
            $filteredTasks = $filteredTasks->filter(function ($task) use ($frequencyIds) {
                return in_array($task['id'], $frequencyIds);
            });
        }

        // Filter by search term
        if (!empty($this->searchTerm)) {
            $search = strtolower($this->searchTerm);
            $filteredTasks = $filteredTasks->filter(function ($task) use ($search) {
                return str_contains(strtolower($task['command']), $search) ||
                       str_contains(strtolower($task['description']), $search) ||
                       str_contains(strtolower($task['category']), $search);
            });
        }

        // Get last run info for each task
        $tasks = $filteredTasks->map(function ($task) {
            $task['nextDue'] = $this->calculateNextDue($task['cron']);
            $task['nextDueFormatted'] = $this->formatNextDue($task['nextDue']);
            $task['frequency'] = $this->determineFrequency($task['cron']);

            // Get enabled status from settings (default is enabled)
            $task['is_enabled'] = $this->taskSettings[$task['id']] ?? true;

            // Get last run from database
            $lastRun = ScheduledTaskLog::getLastRun($task['command']);
            if ($lastRun) {
                $task['lastRun'] = [
                    'time' => $lastRun->started_at->format('Y-m-d H:i:s'),
                    'timeAgo' => $lastRun->started_at->diffForHumans(),
                    'status' => $lastRun->status,
                    'duration' => $lastRun->formatted_duration,
                    'hasError' => !empty($lastRun->error_message),
                    'errorMessage' => $lastRun->error_message,
                ];
            } else {
                $task['lastRun'] = null;
            }

            return $task;
        })->values();

        // Filter by enabled status
        if (!empty($this->enabledFilter)) {
            $tasks = $tasks->filter(function ($task) {
                if ($this->enabledFilter === 'enabled') {
                    return $task['is_enabled'] === true;
                } elseif ($this->enabledFilter === 'disabled') {
                    return $task['is_enabled'] === false;
                }
                return true;
            });
        }

        return $tasks->values()->toArray();
    }

    /**
     * Get tasks by frequency type
     */
    protected function getTasksByFrequency($frequency)
    {
        return match($frequency) {
            'real-time' => ScheduledTaskDefinitions::getRealTimeTasks(),
            'hourly' => ScheduledTaskDefinitions::getHourlyTasks(),
            'daily' => ScheduledTaskDefinitions::getDailyTasks(),
            'weekly' => ScheduledTaskDefinitions::getWeeklyTasks(),
            'monthly' => ScheduledTaskDefinitions::getMonthlyTasks(),
            'quarterly' => ScheduledTaskDefinitions::getQuarterlyTasks(),
            'annual' => ScheduledTaskDefinitions::getAnnualTasks(),
            default => [],
        };
    }

    /**
     * Determine frequency from cron expression
     */
    protected function determineFrequency($cron)
    {
        $parts = explode(' ', $cron);
        if (count($parts) !== 5) return 'Unknown';

        [$minute, $hour, $dayOfMonth, $month, $dayOfWeek] = $parts;

        // Real-time/minute
        if (str_contains($minute, '*/') || $minute === '*') {
            if ($hour === '*' && $dayOfMonth === '*') return 'Real-Time';
        }

        // Hourly
        if ($minute !== '*' && $hour === '*' && $dayOfMonth === '*' && $month === '*' && $dayOfWeek === '*') {
            return 'Hourly';
        }

        // Weekly
        if ($dayOfWeek !== '*' && $dayOfMonth === '*') {
            return 'Weekly';
        }

        // Monthly
        if ($dayOfMonth !== '*' && !str_contains($month, ',') && !str_contains($month, '/')) {
            return 'Monthly';
        }

        // Quarterly
        if (str_contains($month, ',') || str_contains($month, '/')) {
            return 'Quarterly';
        }

        // Annual
        if ($month !== '*' && is_numeric($month)) {
            return 'Annual';
        }

        // Daily (default for specific time tasks)
        if ($minute !== '*' && $hour !== '*' && $dayOfMonth === '*' && $month === '*' && $dayOfWeek === '*') {
            return 'Daily';
        }

        return 'Custom';
    }

    /**
     * Get available categories for filtering
     */
    public function getCategories()
    {
        return ScheduledTaskDefinitions::getCategories();
    }

    /**
     * Get available frequencies for filtering
     */
    public function getFrequencies()
    {
        return [
            'real-time' => 'Real-Time/Minute',
            'hourly' => 'Hourly',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'annual' => 'Annual',
        ];
    }

    /**
     * Clear all filters
     */
    public function clearFilters()
    {
        $this->categoryFilter = '';
        $this->frequencyFilter = '';
        $this->searchTerm = '';
        $this->enabledFilter = '';
        $this->loadTasks();
    }

    /**
     * Toggle a task's enabled status
     */
    public function toggleTask($taskId, $command)
    {
        try {
            $setting = ScheduledTaskSetting::toggleTask($taskId, $command, Auth::id());

            // Update local cache
            $this->taskSettings[$taskId] = $setting->is_enabled;

            // Reload tasks to reflect change
            $this->loadTasks();

            $status = $setting->is_enabled ? 'enabled' : 'disabled';
            session()->flash('success', "Task '{$command}' has been {$status}.");
        } catch (\Exception $e) {
            session()->flash('error', "Failed to toggle task: " . $e->getMessage());
        }
    }

    /**
     * Enable a specific task
     */
    public function enableTask($taskId, $command)
    {
        try {
            ScheduledTaskSetting::enableTask($taskId, $command);
            $this->taskSettings[$taskId] = true;
            $this->loadTasks();
            session()->flash('success', "Task '{$command}' has been enabled.");
        } catch (\Exception $e) {
            session()->flash('error', "Failed to enable task: " . $e->getMessage());
        }
    }

    /**
     * Disable a specific task
     */
    public function disableTask($taskId, $command)
    {
        try {
            ScheduledTaskSetting::disableTask($taskId, $command, Auth::id());
            $this->taskSettings[$taskId] = false;
            $this->loadTasks();
            session()->flash('success', "Task '{$command}' has been disabled.");
        } catch (\Exception $e) {
            session()->flash('error', "Failed to disable task: " . $e->getMessage());
        }
    }

    /**
     * Get count of enabled tasks
     */
    public function getEnabledTaskCount()
    {
        $allTasks = ScheduledTaskDefinitions::getAllTasks();
        $disabledCount = count(array_filter($this->taskSettings, fn($enabled) => !$enabled));
        return count($allTasks) - $disabledCount;
    }

    /**
     * Get count of disabled tasks
     */
    public function getDisabledTaskCount()
    {
        return count(array_filter($this->taskSettings, fn($enabled) => !$enabled));
    }

    /**
     * Get task counts by frequency
     */
    public function getTaskCountsByFrequency()
    {
        return [
            'Real-Time' => count(ScheduledTaskDefinitions::getRealTimeTasks()),
            'Hourly' => count(ScheduledTaskDefinitions::getHourlyTasks()),
            'Daily' => count(ScheduledTaskDefinitions::getDailyTasks()),
            'Weekly' => count(ScheduledTaskDefinitions::getWeeklyTasks()),
            'Monthly' => count(ScheduledTaskDefinitions::getMonthlyTasks()),
            'Quarterly' => count(ScheduledTaskDefinitions::getQuarterlyTasks()),
            'Annual' => count(ScheduledTaskDefinitions::getAnnualTasks()),
        ];
    }

    /**
     * Get task counts by category
     */
    public function getTaskCountsByCategory()
    {
        $tasks = ScheduledTaskDefinitions::getAllTasks();
        $counts = [];

        foreach ($tasks as $task) {
            $category = $task['category'];
            $counts[$category] = ($counts[$category] ?? 0) + 1;
        }

        arsort($counts);
        return $counts;
    }

    /**
     * Get total task count
     */
    public function getTotalTaskCount()
    {
        return count(ScheduledTaskDefinitions::getAllTasks());
    }

    protected function calculateNextDue($cron)
    {
        try {
            $parts = explode(' ', $cron);
            if (count($parts) !== 5) {
                return null;
            }

            [$minute, $hour, $dayOfMonth, $month, $dayOfWeek] = $parts;
            $now = Carbon::now();

            if ($minute !== '*' && $hour !== '*' && $dayOfMonth === '*' && $month === '*' && $dayOfWeek === '*') {
                $next = Carbon::today()->setTime((int)$hour, (int)$minute);
                if ($next->isPast()) {
                    $next->addDay();
                }
                return $next;
            }

            if ($minute === '0' && $hour === '*') {
                $next = Carbon::now()->startOfHour()->addHour();
                return $next;
            }

            if ($dayOfWeek !== '*' && $dayOfWeek !== '0-6') {
                $next = Carbon::today()->setTime((int)$hour, (int)$minute);
                $targetDay = (int)$dayOfWeek;
                while ($next->dayOfWeek !== $targetDay || $next->isPast()) {
                    $next->addDay();
                }
                return $next;
            }

            if ($dayOfMonth !== '*' && is_numeric($dayOfMonth)) {
                $next = Carbon::today()->setDay((int)$dayOfMonth)->setTime((int)$hour, (int)$minute);
                if ($next->isPast()) {
                    $next->addMonth();
                }
                return $next;
            }

            if (str_contains($month, '/')) {
                $next = Carbon::today()->setTime((int)$hour, (int)$minute);
                $quarterlyMonths = [1, 4, 7, 10];
                $day = is_numeric($dayOfMonth) ? (int)$dayOfMonth : 1;
                $next->setDay($day);

                while (!in_array($next->month, $quarterlyMonths) || $next->isPast()) {
                    $next->addMonth();
                }
                return $next;
            }

            return Carbon::now()->addHour();
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function formatNextDue($nextDue)
    {
        if (!$nextDue) {
            return 'Unknown';
        }

        $now = Carbon::now();
        $diff = $now->diff($nextDue);

        if ($diff->days > 30) {
            return $nextDue->format('M d, Y H:i');
        } elseif ($diff->days > 1) {
            return $diff->days . ' days';
        } elseif ($diff->days === 1) {
            return '1 day';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
        } elseif ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        } else {
            return 'Less than a minute';
        }
    }

    public function runTaskById($taskId)
    {
        // Find the task by ID
        $task = collect($this->tasks)->firstWhere('id', $taskId);

        if (!$task) {
            session()->flash('error', "Task not found: {$taskId}");
            return;
        }

        $this->runTask($task['command'], $task['category']);
    }

    public function runTask($command, $category = null)
    {
        try {
            $this->runningTask = $command;
            $this->taskOutput = '';

            // Start logging
            $log = ScheduledTaskLog::startTask(
                $command,
                $category,
                'manual',
                Auth::id()
            );

            // Use shell exec to capture full output including nested commands
            $artisanPath = base_path('artisan');
            $phpBinary = '/usr/bin/php'; // Use CLI php, not php-fpm

            // Build the full command
            $fullCommand = "{$phpBinary} {$artisanPath} {$command} 2>&1";

            // Execute and capture output
            $output = [];
            $exitCode = 0;
            exec($fullCommand, $output, $exitCode);

            $this->taskOutput = implode("\n", $output);

            // Complete logging
            if ($exitCode === 0) {
                $log->completeTask('success', $this->taskOutput);
                session()->flash('success', "Task '{$command}' executed successfully.");
            } else {
                $log->completeTask('failed', $this->taskOutput, "Exit code: {$exitCode}");
                session()->flash('error', "Task '{$command}' completed with exit code: {$exitCode}");
            }

            $this->showOutput = true;

        } catch (\Exception $e) {
            $this->taskOutput = "Error: " . $e->getMessage();

            if (isset($log)) {
                $log->completeTask('failed', null, $e->getMessage());
            }

            $this->showOutput = true;
            session()->flash('error', "Failed to run task: " . $e->getMessage());
        } finally {
            $this->runningTask = null;
            $this->loadTasks(); // Refresh to show updated last run
            $this->loadStats();
            $this->loadCalendarData();
        }
    }

    public function viewHistory($command)
    {
        $this->selectedTask = $command;
        $this->taskHistory = ScheduledTaskLog::getHistory($command, 20)->toArray();
        $this->showHistory = true;
    }

    public function closeHistory()
    {
        $this->showHistory = false;
        $this->selectedTask = null;
        $this->taskHistory = [];
    }

    public function closeOutput()
    {
        $this->showOutput = false;
        $this->taskOutput = '';
    }

    // Calendar navigation
    public function previousMonth()
    {
        $date = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarYear = $date->year;
        $this->calendarMonth = $date->month;
        $this->loadCalendarData();
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarYear = $date->year;
        $this->calendarMonth = $date->month;
        $this->loadCalendarData();
    }

    public function goToToday()
    {
        $this->calendarYear = now()->year;
        $this->calendarMonth = now()->month;
        $this->loadCalendarData();
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->selectedDateTasks = ScheduledTaskLog::getTasksForDate($date)->toArray();
        $this->showDateModal = true;
    }

    public function closeDateModal()
    {
        $this->showDateModal = false;
        $this->selectedDate = null;
        $this->selectedDateTasks = [];
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function getCalendarDays()
    {
        $firstDay = Carbon::create($this->calendarYear, $this->calendarMonth, 1);
        $lastDay = $firstDay->copy()->endOfMonth();

        $days = [];

        // Add empty cells for days before the first of month
        $startDayOfWeek = $firstDay->dayOfWeek;
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $days[] = null;
        }

        // Add all days of the month
        for ($day = 1; $day <= $lastDay->day; $day++) {
            $date = Carbon::create($this->calendarYear, $this->calendarMonth, $day)->format('Y-m-d');
            $days[] = [
                'day' => $day,
                'date' => $date,
                'isToday' => $date === now()->format('Y-m-d'),
                'data' => $this->calendarData[$date] ?? null,
            ];
        }

        return $days;
    }

    public function getMonthName()
    {
        return Carbon::create($this->calendarYear, $this->calendarMonth, 1)->format('F Y');
    }

    public function getCategoryColor($category)
    {
        $colors = ScheduledTaskDefinitions::getCategoryColors();
        return $colors[$category] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get frequency badge color
     */
    public function getFrequencyColor($frequency)
    {
        return match($frequency) {
            'Real-Time' => 'bg-red-100 text-red-800',
            'Hourly' => 'bg-orange-100 text-orange-800',
            'Daily' => 'bg-yellow-100 text-yellow-800',
            'Weekly' => 'bg-green-100 text-green-800',
            'Monthly' => 'bg-blue-100 text-blue-800',
            'Quarterly' => 'bg-indigo-100 text-indigo-800',
            'Annual' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function render()
    {
        return view('livewire.profile-setting.scheduled-tasks', [
            'calendarDays' => $this->getCalendarDays(),
            'monthName' => $this->getMonthName(),
            'categories' => $this->getCategories(),
            'frequencies' => $this->getFrequencies(),
            'taskCountsByFrequency' => $this->getTaskCountsByFrequency(),
            'taskCountsByCategory' => $this->getTaskCountsByCategory(),
            'totalTaskCount' => $this->getTotalTaskCount(),
            'enabledTaskCount' => $this->getEnabledTaskCount(),
            'disabledTaskCount' => $this->getDisabledTaskCount(),
        ]);
    }
}
