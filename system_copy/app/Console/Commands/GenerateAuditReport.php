<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateAuditReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:report
                            {--from= : Start date (YYYY-MM-DD)}
                            {--to= : End date (YYYY-MM-DD)}
                            {--user= : Filter by user ID}
                            {--action= : Filter by action type}
                            {--entity= : Filter by entity type}
                            {--critical : Show only critical actions}
                            {--export= : Export to file (csv, json, xlsx)}
                            {--limit=100 : Limit number of records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate audit trail report with various filters';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║          NBC SACCOS - Audit Trail Report Generator        ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Parse date range
        $fromDate = $this->option('from') ? Carbon::parse($this->option('from')) : Carbon::now()->subDays(7);
        $toDate = $this->option('to') ? Carbon::parse($this->option('to')) : Carbon::now();

        $this->info("Report Period: {$fromDate->toDateString()} to {$toDate->toDateString()}");
        $this->newLine();

        // Build query
        $query = AuditLog::query()
            ->with(['user', 'branch'])
            ->whereBetween('performed_at', [$fromDate, $toDate]);

        // Apply filters
        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
            $this->info("Filter: User ID = {$userId}");
        }

        if ($action = $this->option('action')) {
            $query->where('action', $action);
            $this->info("Filter: Action = {$action}");
        }

        if ($entity = $this->option('entity')) {
            $query->where('entity_type', 'LIKE', "%{$entity}%");
            $this->info("Filter: Entity = {$entity}");
        }

        if ($this->option('critical')) {
            $query->where('severity', 'critical')
                  ->orWhereIn('action', [
                      'DELETE',
                      'PERMISSION_CHANGED',
                      'ROLE_CHANGED',
                      'TRANSACTION_REVERSED'
                  ]);
            $this->info("Filter: Critical actions only");
        }

        $query->orderBy('performed_at', 'desc')
              ->limit($this->option('limit'));

        // Execute query
        $this->info("Fetching audit logs...");
        $audits = $query->get();

        if ($audits->isEmpty()) {
            $this->warn('No audit logs found matching the criteria.');
            return 0;
        }

        $this->info("Found {$audits->count()} audit log entries");
        $this->newLine();

        // Generate statistics
        $this->displayStatistics($audits);
        $this->newLine();

        // Display records
        $this->displayRecords($audits);

        // Export if requested
        if ($exportFormat = $this->option('export')) {
            $this->export($audits, $exportFormat, $fromDate, $toDate);
        }

        $this->newLine();
        $this->info('Report generation completed successfully!');

        return 0;
    }

    /**
     * Display audit statistics
     */
    protected function displayStatistics($audits)
    {
        $this->info('═══════════════════════ STATISTICS ═══════════════════════');

        $stats = [
            'Total Records' => $audits->count(),
            'Unique Users' => $audits->pluck('user_id')->unique()->count(),
            'Creates' => $audits->where('action', 'CREATE')->count(),
            'Updates' => $audits->where('action', 'UPDATE')->count(),
            'Deletes' => $audits->where('action', 'DELETE')->count(),
            'Critical Actions' => $audits->where('severity', 'critical')->count(),
        ];

        foreach ($stats as $label => $value) {
            $this->line(sprintf("  %-20s : %s", $label, $value));
        }

        // Top users
        $this->newLine();
        $this->info('Top 5 Active Users:');
        $topUsers = $audits->groupBy('user_id')
            ->map(function ($group) {
                return [
                    'user' => $group->first()->user->name ?? 'Unknown',
                    'count' => $group->count()
                ];
            })
            ->sortByDesc('count')
            ->take(5);

        foreach ($topUsers as $user) {
            $this->line("  • {$user['user']}: {$user['count']} actions");
        }

        // Top actions
        $this->newLine();
        $this->info('Top 5 Actions:');
        $topActions = $audits->groupBy('action')
            ->map(function ($group, $action) {
                return [
                    'action' => $action,
                    'count' => $group->count()
                ];
            })
            ->sortByDesc('count')
            ->take(5);

        foreach ($topActions as $action) {
            $this->line("  • {$action['action']}: {$action['count']} occurrences");
        }
    }

    /**
     * Display audit records in table format
     */
    protected function displayRecords($audits)
    {
        $this->info('═══════════════════════ AUDIT LOGS ═══════════════════════');

        $headers = ['ID', 'Date/Time', 'User', 'Action', 'Entity', 'Description'];
        $rows = [];

        foreach ($audits->take(20) as $audit) {
            $rows[] = [
                $audit->id,
                $audit->performed_at->format('Y-m-d H:i:s'),
                $audit->user->name ?? 'System',
                $audit->action,
                class_basename($audit->entity_type),
                \Str::limit($audit->description, 40),
            ];
        }

        $this->table($headers, $rows);

        if ($audits->count() > 20) {
            $this->warn("Showing first 20 of {$audits->count()} records. Use --export to get full data.");
        }
    }

    /**
     * Export audit logs to file
     */
    protected function export($audits, $format, $fromDate, $toDate)
    {
        $this->info("Exporting to {$format} format...");

        $filename = "audit-report-{$fromDate->format('Ymd')}-to-{$toDate->format('Ymd')}.{$format}";
        $filepath = storage_path("app/exports/{$filename}");

        // Ensure directory exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        switch ($format) {
            case 'csv':
                $this->exportToCsv($audits, $filepath);
                break;
            case 'json':
                $this->exportToJson($audits, $filepath);
                break;
            case 'xlsx':
                $this->warn('XLSX export requires maatwebsite/excel package. Using CSV instead.');
                $this->exportToCsv($audits, str_replace('.xlsx', '.csv', $filepath));
                break;
            default:
                $this->error("Unsupported export format: {$format}");
                return;
        }

        $this->info("✓ Export completed: {$filepath}");
    }

    /**
     * Export to CSV
     */
    protected function exportToCsv($audits, $filepath)
    {
        $handle = fopen($filepath, 'w');

        // Headers
        fputcsv($handle, [
            'ID',
            'Date/Time',
            'User ID',
            'User Name',
            'Action',
            'Entity Type',
            'Entity ID',
            'Description',
            'IP Address',
            'Severity',
            'Branch ID'
        ]);

        // Data
        foreach ($audits as $audit) {
            fputcsv($handle, [
                $audit->id,
                $audit->performed_at->toDateTimeString(),
                $audit->user_id,
                $audit->user->name ?? 'Unknown',
                $audit->action,
                $audit->entity_type,
                $audit->entity_id,
                $audit->description,
                $audit->ip_address,
                $audit->severity ?? 'medium',
                $audit->branch_id
            ]);
        }

        fclose($handle);
    }

    /**
     * Export to JSON
     */
    protected function exportToJson($audits, $filepath)
    {
        $data = $audits->map(function ($audit) {
            return [
                'id' => $audit->id,
                'performed_at' => $audit->performed_at->toIso8601String(),
                'user' => [
                    'id' => $audit->user_id,
                    'name' => $audit->user->name ?? 'Unknown',
                ],
                'action' => $audit->action,
                'entity' => [
                    'type' => $audit->entity_type,
                    'id' => $audit->entity_id,
                ],
                'description' => $audit->description,
                'old_values' => $audit->old_values,
                'new_values' => $audit->new_values,
                'context' => $audit->context,
                'ip_address' => $audit->ip_address,
                'user_agent' => $audit->user_agent,
                'severity' => $audit->severity ?? 'medium',
                'branch_id' => $audit->branch_id,
            ];
        });

        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
    }
}
