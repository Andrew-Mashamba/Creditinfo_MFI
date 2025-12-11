<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Update Member Activity Status Command
 *
 * Checks all active members and marks them as DORMANT if all their accounts
 * have had no activity (updated_at) for more than 6 months.
 *
 * A member is considered ACTIVE if at least one of their accounts has been
 * updated within the last 6 months.
 *
 * A member is marked DORMANT if ALL their accounts have not been updated
 * in the last 6 months.
 */
class UpdateMemberStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:update-status
                            {--months= : Override dormant period in months (defaults to institution setting)}
                            {--dry-run : Show what would be updated without making changes}
                            {--report : Show detailed report of member activity status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update member activity status based on account activity (marks as DORMANT if no activity for 6+ months)';

    /**
     * Inactivity threshold in months
     */
    protected $inactivityMonths;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get months from command option first, then fall back to institution setting
        $monthsOption = $this->option('months');
        $settingSource = 'institution setting';

        if ($monthsOption !== null && $monthsOption !== '') {
            // User explicitly specified months via command line
            $this->inactivityMonths = (int) $monthsOption;
            $settingSource = 'command option';
        } else {
            // Get from institution settings (dormant_period_months column)
            $institution = DB::table('institutions')->where('id', 1)->first();
            $this->inactivityMonths = (int) ($institution->dormant_period_months ?? 6);
        }

        $isDryRun = $this->option('dry-run');
        $showReport = $this->option('report');

        $this->info("========================================");
        $this->info("   MEMBER ACTIVITY STATUS UPDATE");
        $this->info("   Inactivity Threshold: {$this->inactivityMonths} months ({$settingSource})");
        if ($isDryRun) {
            $this->warn("   MODE: DRY RUN (no changes will be made)");
        }
        $this->info("========================================");
        $this->newLine();

        $cutoffDate = Carbon::now()->subMonths($this->inactivityMonths);
        $this->info("Cutoff Date: {$cutoffDate->format('Y-m-d')} (accounts not updated since this date are considered inactive)");
        $this->newLine();

        try {
            // Get all active members
            $activeMembers = DB::table('clients')
                ->where('status', 'ACTIVE')
                ->whereNotNull('client_number')
                ->where('client_number', '!=', '')
                ->where('client_number', '!=', '0000')
                ->select('id', 'client_number', 'first_name', 'last_name', 'status')
                ->get();

            $this->info("Found {$activeMembers->count()} active members to check");
            $this->newLine();

            $activeMemberCount = 0;
            $dormantMemberCount = 0;
            $noAccountsCount = 0;
            $errors = [];
            $dormantMembers = [];
            $stillActiveMembers = [];

            $this->output->progressStart($activeMembers->count());

            foreach ($activeMembers as $member) {
                try {
                    $result = $this->checkMemberActivity($member, $cutoffDate);

                    if ($result['status'] === 'no_accounts') {
                        $noAccountsCount++;
                    } elseif ($result['status'] === 'dormant') {
                        $dormantMemberCount++;
                        $dormantMembers[] = [
                            'client_number' => $member->client_number,
                            'name' => trim($member->first_name . ' ' . $member->last_name),
                            'last_activity' => $result['last_activity'],
                            'accounts_count' => $result['accounts_count'],
                        ];

                        if (!$isDryRun) {
                            // Update member status to DORMANT
                            DB::table('clients')
                                ->where('id', $member->id)
                                ->update([
                                    'status' => 'DORMANT',
                                    'updated_at' => now()
                                ]);
                        }
                    } else {
                        $activeMemberCount++;
                        if ($showReport) {
                            $stillActiveMembers[] = [
                                'client_number' => $member->client_number,
                                'name' => trim($member->first_name . ' ' . $member->last_name),
                                'last_activity' => $result['last_activity'],
                                'accounts_count' => $result['accounts_count'],
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = [
                        'client_number' => $member->client_number,
                        'error' => $e->getMessage()
                    ];
                }

                $this->output->progressAdvance();
            }

            $this->output->progressFinish();
            $this->newLine();

            // Display results
            $this->info("========================================");
            $this->info("   RESULTS");
            $this->info("========================================");

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Active Members Checked', $activeMembers->count()],
                    ['Still Active (recent activity)', $activeMemberCount],
                    ['Marked as Dormant', $dormantMemberCount],
                    ['Members with No Accounts', $noAccountsCount],
                    ['Errors', count($errors)],
                ]
            );

            // Show dormant members if any
            if (!empty($dormantMembers)) {
                $this->newLine();
                $this->warn("Members marked as DORMANT:");
                $this->table(
                    ['Client Number', 'Name', 'Last Activity', 'Accounts'],
                    array_map(function ($m) {
                        return [
                            $m['client_number'],
                            $m['name'],
                            $m['last_activity'] ? Carbon::parse($m['last_activity'])->format('Y-m-d') : 'Never',
                            $m['accounts_count'],
                        ];
                    }, array_slice($dormantMembers, 0, 20)) // Show first 20
                );

                if (count($dormantMembers) > 20) {
                    $this->info("... and " . (count($dormantMembers) - 20) . " more");
                }
            }

            // Show active members if report mode
            if ($showReport && !empty($stillActiveMembers)) {
                $this->newLine();
                $this->info("Members still ACTIVE (sample):");
                $this->table(
                    ['Client Number', 'Name', 'Last Activity', 'Accounts'],
                    array_map(function ($m) {
                        return [
                            $m['client_number'],
                            $m['name'],
                            $m['last_activity'] ? Carbon::parse($m['last_activity'])->format('Y-m-d') : 'Never',
                            $m['accounts_count'],
                        ];
                    }, array_slice($stillActiveMembers, 0, 10)) // Show first 10
                );
            }

            // Show errors if any
            if (!empty($errors)) {
                $this->newLine();
                $this->error("Errors encountered:");
                foreach (array_slice($errors, 0, 10) as $error) {
                    $this->error("  Member {$error['client_number']}: {$error['error']}");
                }
            }

            // Log results
            Log::info('[MEMBER_STATUS_UPDATE] Completed', [
                'total_checked' => $activeMembers->count(),
                'still_active' => $activeMemberCount,
                'marked_dormant' => $dormantMemberCount,
                'no_accounts' => $noAccountsCount,
                'errors' => count($errors),
                'dry_run' => $isDryRun,
                'inactivity_months' => $this->inactivityMonths,
            ]);

            $this->newLine();
            if ($isDryRun) {
                $this->warn("DRY RUN complete. No changes were made.");
                $this->info("Run without --dry-run to apply changes.");
            } else {
                $this->info("Member status update completed successfully!");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Failed: " . $e->getMessage());
            Log::error('[MEMBER_STATUS_UPDATE] Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Check if a member has had any account activity within the threshold
     */
    protected function checkMemberActivity($member, Carbon $cutoffDate): array
    {
        // Get all accounts for this member
        $accounts = DB::table('accounts')
            ->where('client_number', $member->client_number)
            ->select('account_number', 'updated_at')
            ->get();

        if ($accounts->isEmpty()) {
            return [
                'status' => 'no_accounts',
                'last_activity' => null,
                'accounts_count' => 0,
            ];
        }

        // Find the most recent activity across all accounts
        $lastActivity = $accounts->max('updated_at');

        if (!$lastActivity) {
            // No updated_at dates found, consider dormant
            return [
                'status' => 'dormant',
                'last_activity' => null,
                'accounts_count' => $accounts->count(),
            ];
        }

        $lastActivityDate = Carbon::parse($lastActivity);

        // If ANY account has been updated after the cutoff, member is still active
        if ($lastActivityDate->greaterThanOrEqualTo($cutoffDate)) {
            return [
                'status' => 'active',
                'last_activity' => $lastActivity,
                'accounts_count' => $accounts->count(),
            ];
        }

        // All accounts are inactive (updated before cutoff)
        return [
            'status' => 'dormant',
            'last_activity' => $lastActivity,
            'accounts_count' => $accounts->count(),
        ];
    }
}
