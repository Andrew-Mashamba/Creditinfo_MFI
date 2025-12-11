<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Member Status Service
 *
 * Checks member activity based on account activity and updates member status.
 * A member is marked as DORMANT if ALL their accounts have had no activity
 * (updated_at) for more than the configured dormant period.
 *
 * The dormant period is configurable via Institution Settings (dormant_period_months).
 *
 * @package App\Services
 * @version 1.0
 */
class MemberStatusService
{
    /**
     * Inactivity threshold in months
     */
    protected $inactivityMonths;

    /**
     * Constructor - loads institution settings
     */
    public function __construct()
    {
        $this->loadInstitutionSettings();
    }

    /**
     * Load dormant period from institution settings
     */
    protected function loadInstitutionSettings(): void
    {
        $institution = DB::table('institutions')->where('id', 1)->first();
        $this->inactivityMonths = (int) ($institution->dormant_period_months ?? 6);

        Log::info('[MEMBER_STATUS_SERVICE] Loaded settings', [
            'dormant_period_months' => $this->inactivityMonths
        ]);
    }

    /**
     * Get the configured dormant period in months
     */
    public function getDormantPeriodMonths(): int
    {
        return $this->inactivityMonths;
    }

    /**
     * Set the dormant period (for command-line override)
     */
    public function setDormantPeriodMonths(int $months): void
    {
        $this->inactivityMonths = $months;
    }

    /**
     * Process member status updates
     *
     * @param bool $dryRun If true, don't make actual changes
     * @return array Results of the processing
     */
    public function processStatusUpdate(bool $dryRun = false): array
    {
        $cutoffDate = Carbon::now()->subMonths($this->inactivityMonths);

        Log::info('[MEMBER_STATUS_SERVICE] Starting status update', [
            'dormant_period_months' => $this->inactivityMonths,
            'cutoff_date' => $cutoffDate->format('Y-m-d'),
            'dry_run' => $dryRun
        ]);

        // Get all active members
        $activeMembers = DB::table('clients')
            ->where('status', 'ACTIVE')
            ->whereNotNull('client_number')
            ->where('client_number', '!=', '')
            ->where('client_number', '!=', '0000')
            ->select('id', 'client_number', 'first_name', 'last_name', 'status')
            ->get();

        $activeMemberCount = 0;
        $dormantMemberCount = 0;
        $noAccountsCount = 0;
        $errors = [];
        $dormantMembers = [];
        $stillActiveMembers = [];

        foreach ($activeMembers as $member) {
            try {
                $result = $this->checkMemberActivity($member, $cutoffDate);

                if ($result['status'] === 'no_accounts') {
                    $noAccountsCount++;
                } elseif ($result['status'] === 'dormant') {
                    $dormantMemberCount++;
                    $dormantMembers[] = [
                        'id' => $member->id,
                        'client_number' => $member->client_number,
                        'name' => trim($member->first_name . ' ' . $member->last_name),
                        'last_activity' => $result['last_activity'],
                        'accounts_count' => $result['accounts_count'],
                    ];

                    if (!$dryRun) {
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
                    $stillActiveMembers[] = [
                        'client_number' => $member->client_number,
                        'name' => trim($member->first_name . ' ' . $member->last_name),
                        'last_activity' => $result['last_activity'],
                        'accounts_count' => $result['accounts_count'],
                    ];
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'client_number' => $member->client_number,
                    'error' => $e->getMessage()
                ];
                Log::error('[MEMBER_STATUS_SERVICE] Error checking member', [
                    'client_number' => $member->client_number,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $result = [
            'status' => 'success',
            'dry_run' => $dryRun,
            'dormant_period_months' => $this->inactivityMonths,
            'cutoff_date' => $cutoffDate->format('Y-m-d'),
            'total_checked' => $activeMembers->count(),
            'still_active' => $activeMemberCount,
            'marked_dormant' => $dormantMemberCount,
            'no_accounts' => $noAccountsCount,
            'errors_count' => count($errors),
            'errors' => $errors,
            'dormant_members' => $dormantMembers,
            'active_members' => $stillActiveMembers
        ];

        Log::info('[MEMBER_STATUS_SERVICE] Status update completed', [
            'total_checked' => $result['total_checked'],
            'still_active' => $result['still_active'],
            'marked_dormant' => $result['marked_dormant'],
            'no_accounts' => $result['no_accounts'],
            'errors' => $result['errors_count'],
            'dry_run' => $dryRun
        ]);

        return $result;
    }

    /**
     * Check if a member has had any account activity within the threshold
     *
     * @param object $member The member record
     * @param Carbon $cutoffDate The date before which activity is considered stale
     * @return array Activity status information
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

    /**
     * Get summary statistics for reporting
     *
     * @return array Summary statistics
     */
    public function getStatusSummary(): array
    {
        $totalMembers = DB::table('clients')
            ->whereNotNull('client_number')
            ->where('client_number', '!=', '')
            ->where('client_number', '!=', '0000')
            ->count();

        $statusCounts = DB::table('clients')
            ->whereNotNull('client_number')
            ->where('client_number', '!=', '')
            ->where('client_number', '!=', '0000')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total_members' => $totalMembers,
            'by_status' => $statusCounts,
            'dormant_period_months' => $this->inactivityMonths,
            'active_count' => $statusCounts['ACTIVE'] ?? 0,
            'dormant_count' => $statusCounts['DORMANT'] ?? 0,
            'inactive_count' => $statusCounts['INACTIVE'] ?? 0,
            'pending_count' => $statusCounts['PENDING'] ?? 0,
        ];
    }

    /**
     * Reactivate a dormant member (when they have new activity)
     *
     * @param string $clientNumber The client number
     * @return bool Success status
     */
    public function reactivateMember(string $clientNumber): bool
    {
        $member = DB::table('clients')
            ->where('client_number', $clientNumber)
            ->where('status', 'DORMANT')
            ->first();

        if (!$member) {
            return false;
        }

        DB::table('clients')
            ->where('id', $member->id)
            ->update([
                'status' => 'ACTIVE',
                'updated_at' => now()
            ]);

        Log::info('[MEMBER_STATUS_SERVICE] Member reactivated', [
            'client_number' => $clientNumber
        ]);

        return true;
    }
}
