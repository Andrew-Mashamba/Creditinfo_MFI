<?php

namespace App\Http\Livewire\ProfileSetting;

use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class AuditTrail extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;
    public $logLevel = '';
    public $searchTerm = '';
    public $selectedUser = '';
    public $logType = 'laravel';
    public $showingDetails = false;
    public $selectedLog = null;

    // Statistics
    public $totalLogs = 0;
    public $totalUsers = 0;
    public $totalErrors = 0;
    public $totalWarnings = 0;

    protected $queryString = [
        'dateFrom',
        'dateTo',
        'logLevel',
        'searchTerm',
        'selectedUser'
    ];

    public function mount()
    {
        // Default date range: last 7 days
        $this->dateFrom = now()->subDays(7)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');

        $this->calculateStatistics();
    }

    public function updatedDateFrom()
    {
        $this->calculateStatistics();
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->calculateStatistics();
        $this->resetPage();
    }

    public function updatedLogLevel()
    {
        $this->resetPage();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function calculateStatistics()
    {
        $logs = $this->parseLogsFromFiles();

        $this->totalLogs = count($logs);
        $this->totalUsers = count(array_unique(array_column($logs, 'user_id')));
        $this->totalErrors = count(array_filter($logs, fn($log) => $log['level'] === 'ERROR'));
        $this->totalWarnings = count(array_filter($logs, fn($log) => $log['level'] === 'WARNING'));
    }

    protected function parseLogsFromFiles(): array
    {
        $logs = [];
        $logBasePath = storage_path('logs');

        $startDate = Carbon::parse($this->dateFrom);
        $endDate = Carbon::parse($this->dateTo);

        while ($startDate->lte($endDate)) {
            $filename = "{$this->logType}-{$startDate->format('Y-m-d')}.log";
            $filepath = "{$logBasePath}/{$filename}";

            if (File::exists($filepath)) {
                $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

                foreach ($lines as $line) {
                    $parsed = $this->parseLogLine($line);
                    if ($parsed) {
                        $logs[] = $parsed;
                    }
                }
            }

            $startDate->addDay();
        }

        return $logs;
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
                'user_id' => $context['user_id'] ?? null,
                'user_name' => $context['user_name'] ?? 'System',
                'action' => $context['action'] ?? null,
                'entity' => $context['entity'] ?? null,
                'entity_id' => $context['entity_id'] ?? null,
                'raw' => $line
            ];
        }

        return null;
    }

    public function getFilteredLogs()
    {
        $logs = $this->parseLogsFromFiles();

        // Apply filters
        if ($this->logLevel) {
            $logs = array_filter($logs, fn($log) => strtoupper($log['level']) === strtoupper($this->logLevel));
        }

        if ($this->selectedUser) {
            $logs = array_filter($logs, fn($log) => $log['user_id'] == $this->selectedUser);
        }

        if ($this->searchTerm) {
            $logs = array_filter($logs, function($log) {
                return stripos($log['message'], $this->searchTerm) !== false ||
                       stripos(json_encode($log['context']), $this->searchTerm) !== false;
            });
        }

        // Sort by timestamp descending
        usort($logs, fn($a, $b) => strcmp($b['timestamp'], $a['timestamp']));

        return $logs;
    }

    public function showDetails($index)
    {
        $logs = $this->getFilteredLogs();
        $this->selectedLog = $logs[$index] ?? null;
        $this->showingDetails = true;
    }

    public function closeDetails()
    {
        $this->showingDetails = false;
        $this->selectedLog = null;
    }

    public function exportLogs($format = 'csv')
    {
        $logs = $this->getFilteredLogs();

        if ($format === 'csv') {
            $filename = "audit-logs-{$this->dateFrom}-to-{$this->dateTo}.csv";
            $filepath = storage_path("app/exports/{$filename}");

            // Ensure directory exists
            if (!File::isDirectory(dirname($filepath))) {
                File::makeDirectory(dirname($filepath), 0755, true);
            }

            $handle = fopen($filepath, 'w');

            // Headers
            fputcsv($handle, ['Timestamp', 'Level', 'User ID', 'User Name', 'Action', 'Entity', 'Message']);

            // Data
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log['timestamp'],
                    $log['level'],
                    $log['user_id'] ?? '',
                    $log['user_name'] ?? 'System',
                    $log['action'] ?? '',
                    $log['entity'] ?? '',
                    $log['message']
                ]);
            }

            fclose($handle);

            return response()->download($filepath)->deleteFileAfterSend(true);
        }

        session()->flash('message', 'Export format not supported yet.');
    }

    public function clearFilters()
    {
        $this->dateFrom = now()->subDays(7)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->logLevel = '';
        $this->searchTerm = '';
        $this->selectedUser = '';
        $this->calculateStatistics();
        $this->resetPage();
    }

    public function render()
    {
        $logs = $this->getFilteredLogs();

        // Paginate manually
        $perPage = 20;
        $currentPage = $this->page ?? 1;
        $offset = ($currentPage - 1) * $perPage;
        $paginatedLogs = array_slice($logs, $offset, $perPage);

        return view('livewire.profile-setting.audit-trail', [
            'logs' => $paginatedLogs,
            'totalRecords' => count($logs),
            'perPage' => $perPage
        ]);
    }
}
