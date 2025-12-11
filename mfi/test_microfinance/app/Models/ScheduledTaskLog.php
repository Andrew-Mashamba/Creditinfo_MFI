<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ScheduledTaskLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'command',
        'category',
        'status',
        'trigger_type',
        'started_at',
        'completed_at',
        'duration_seconds',
        'output',
        'error_message',
        'triggered_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user who triggered the task (for manual runs)
     */
    public function triggeredByUser()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    /**
     * Scope for successful tasks
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed tasks
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for a specific command
     */
    public function scopeForCommand($query, $command)
    {
        return $query->where('command', $command);
    }

    /**
     * Scope for tasks on a specific date
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('started_at', $date);
    }

    /**
     * Scope for tasks in a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('started_at', [$startDate, $endDate]);
    }

    /**
     * Get the last run for a specific command
     */
    public static function getLastRun($command)
    {
        return self::forCommand($command)
            ->orderBy('started_at', 'desc')
            ->first();
    }

    /**
     * Get execution history for a command
     */
    public static function getHistory($command, $limit = 10)
    {
        return self::forCommand($command)
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all tasks for a specific date (for calendar)
     */
    public static function getTasksForDate($date)
    {
        return self::onDate($date)
            ->orderBy('started_at', 'asc')
            ->get();
    }

    /**
     * Get calendar data for a month
     */
    public static function getCalendarData($year, $month)
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $logs = self::betweenDates($startOfMonth, $endOfMonth)
            ->select('id', 'command', 'status', 'started_at', 'duration_seconds')
            ->get();

        $calendarData = [];
        foreach ($logs as $log) {
            $date = $log->started_at->format('Y-m-d');
            if (!isset($calendarData[$date])) {
                $calendarData[$date] = [
                    'total' => 0,
                    'success' => 0,
                    'failed' => 0,
                    'tasks' => [],
                ];
            }
            $calendarData[$date]['total']++;
            $calendarData[$date][$log->status]++;
            $calendarData[$date]['tasks'][] = [
                'id' => $log->id,
                'command' => $log->command,
                'status' => $log->status,
                'time' => $log->started_at->format('H:i'),
                'duration' => $log->duration_seconds,
            ];
        }

        return $calendarData;
    }

    /**
     * Get statistics for dashboard
     */
    public static function getStatistics($days = 7)
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        $stats = self::where('started_at', '>=', $startDate)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total' => array_sum($stats),
            'success' => $stats['success'] ?? 0,
            'failed' => $stats['failed'] ?? 0,
            'running' => $stats['running'] ?? 0,
        ];
    }

    /**
     * Start logging a task execution
     */
    public static function startTask($command, $category = null, $triggerType = 'scheduled', $triggeredBy = null)
    {
        return self::create([
            'command' => $command,
            'category' => $category,
            'status' => 'running',
            'trigger_type' => $triggerType,
            'started_at' => now(),
            'triggered_by' => $triggeredBy,
        ]);
    }

    /**
     * Complete a task execution
     */
    public function completeTask($status, $output = null, $errorMessage = null)
    {
        $completedAt = now();
        $duration = $this->started_at ? $completedAt->diffInSeconds($this->started_at) : 0;

        $this->update([
            'status' => $status,
            'completed_at' => $completedAt,
            'duration_seconds' => $duration,
            'output' => $output,
            'error_message' => $errorMessage,
        ]);

        return $this;
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration_seconds) {
            return '-';
        }

        if ($this->duration_seconds < 60) {
            return $this->duration_seconds . 's';
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        return $minutes . 'm ' . $seconds . 's';
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'success' => 'green',
            'failed' => 'red',
            'running' => 'red',
            default => 'gray',
        };
    }
}
