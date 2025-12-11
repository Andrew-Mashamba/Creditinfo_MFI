<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Approval;
use App\Models\LoansModel;
use App\Models\User;
use App\Models\Role;
use App\Mail\ApprovalSlaAlert;
use App\Mail\LoanSlaAlert;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CheckApprovalSLA extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'approvals:check-sla
                            {--dry-run : Run without sending emails or updating records}
                            {--notify-warnings : Send notifications for warnings (not just breaches)}
                            {--type=all : Type to check: all, general, loans}';

    /**
     * The console command description.
     */
    protected $description = 'Check SLA breaches for pending approvals and loan applications';

    /**
     * Statistics tracking for general approvals
     */
    protected $approvalStats = [
        'checked' => 0,
        'warnings' => 0,
        'breaches' => 0,
        'notifications_sent' => 0,
        'escalations' => 0,
        'errors' => []
    ];

    /**
     * Statistics tracking for loan approvals
     */
    protected $loanStats = [
        'checked' => 0,
        'warnings' => 0,
        'breaches' => 0,
        'notifications_sent' => 0,
        'escalations' => 0,
        'errors' => []
    ];

    /**
     * Executive roles for final escalation
     */
    protected $executiveRoles = [
        'CEO/General Manager',
        'Deputy CEO',
        'Chief Operations Officer'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $this->info('===========================================');
        $this->info('  SLA Checker - Approvals & Loans');
        $this->info('  Started: ' . now()->format('Y-m-d H:i:s'));
        $this->info('===========================================');

        $dryRun = $this->option('dry-run');
        $notifyWarnings = $this->option('notify-warnings');
        $type = $this->option('type');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No emails will be sent and no records will be updated');
        }

        // Process general approvals
        if ($type === 'all' || $type === 'general') {
            $this->newLine();
            $this->info('--- GENERAL APPROVALS ---');
            $this->processGeneralApprovals($dryRun, $notifyWarnings);
        }

        // Process loan approvals
        if ($type === 'all' || $type === 'loans') {
            $this->newLine();
            $this->info('--- LOAN APPROVALS ---');
            $this->processLoanApprovals($dryRun, $notifyWarnings);
        }

        $this->logSummary($startTime);

        // Log to system
        Log::info('SLA check completed', [
            'general_approvals' => $this->approvalStats,
            'loan_approvals' => $this->loanStats,
            'dry_run' => $dryRun,
        ]);

        $totalErrors = count($this->approvalStats['errors']) + count($this->loanStats['errors']);
        return $totalErrors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    // ==================== GENERAL APPROVALS ====================

    /**
     * Process general approvals SLA check
     */
    protected function processGeneralApprovals($dryRun, $notifyWarnings)
    {
        $pendingApprovals = Approval::pendingSlaCheck()
            ->with(['processConfig', 'user', 'firstChecker', 'secondChecker', 'approver'])
            ->get();

        if ($pendingApprovals->isEmpty()) {
            $this->info('No pending general approvals with SLA monitoring enabled.');
            return;
        }

        $this->info("Found {$pendingApprovals->count()} pending approval(s) to check");

        $warnings = collect();
        $breaches = collect();

        foreach ($pendingApprovals as $approval) {
            $this->approvalStats['checked']++;
            $slaStatus = $approval->getSlaStatus();

            if ($slaStatus === 'breached') {
                $breaches->push($approval);
                $this->approvalStats['breaches']++;

                if (!$dryRun && !$approval->sla_breached_at) {
                    $approval->markSlaBreached($approval->checker_level ?? 1);
                }
            } elseif ($slaStatus === 'warning') {
                $warnings->push($approval);
                $this->approvalStats['warnings']++;
            }
        }

        $this->info("  Warnings: {$this->approvalStats['warnings']}");
        $this->info("  Breaches: {$this->approvalStats['breaches']}");

        // Process breaches
        if ($breaches->isNotEmpty()) {
            $this->processApprovalBreaches($breaches, $dryRun);
        }

        // Process warnings
        if ($notifyWarnings && $warnings->isNotEmpty()) {
            $this->processApprovalWarnings($warnings, $dryRun);
        }

        // Process escalations
        $this->processApprovalEscalations($breaches, $dryRun);
    }

    /**
     * Process breached approvals - notify users with the assigned roles
     */
    protected function processApprovalBreaches($breaches, $dryRun)
    {
        $this->info('Processing approval SLA breaches...');

        $groupedApprovals = $breaches->groupBy(function ($approval) {
            return $approval->process_code . '_' . ($approval->checker_level ?? 1);
        });

        foreach ($groupedApprovals as $key => $approvals) {
            $unnotified = $approvals->filter(fn($a) => !$a->sla_breach_notified_at);

            if ($unnotified->isEmpty()) {
                continue;
            }

            $sampleApproval = $unnotified->first();
            $roleIds = $this->getRoleIdsForApprovalLevel($sampleApproval);

            if (empty($roleIds)) {
                $this->sendApprovalToTechIssues($unnotified, 'breach', $dryRun, 'No roles configured');
            } else {
                $users = $this->getUsersByRoleIds($roleIds);

                if ($users->isEmpty()) {
                    $roleNames = Role::whereIn('id', $roleIds)->pluck('name')->implode(', ');
                    $this->sendApprovalToTechIssues($unnotified, 'breach', $dryRun, "No users with roles: {$roleNames}");
                } else {
                    foreach ($users as $user) {
                        $this->sendApprovalToUser($user, $unnotified, 'breach', $dryRun, 'checker');
                    }
                }
            }

            if (!$dryRun) {
                foreach ($unnotified as $approval) {
                    $approval->markSlaBreachNotified();
                }
            }
        }
    }

    /**
     * Process approval warnings
     */
    protected function processApprovalWarnings($warnings, $dryRun)
    {
        $this->info('Processing approval SLA warnings...');

        $groupedApprovals = $warnings->groupBy(function ($approval) {
            return $approval->process_code . '_' . ($approval->checker_level ?? 1);
        });

        foreach ($groupedApprovals as $key => $approvals) {
            $unwarned = $approvals->filter(fn($a) => !$a->sla_warning_sent_at);

            if ($unwarned->isEmpty()) {
                continue;
            }

            $sampleApproval = $unwarned->first();
            $roleIds = $this->getRoleIdsForApprovalLevel($sampleApproval);

            if (!empty($roleIds)) {
                $users = $this->getUsersByRoleIds($roleIds);

                foreach ($users as $user) {
                    $this->sendApprovalToUser($user, $unwarned, 'warning', $dryRun, 'checker');
                }
            }

            if (!$dryRun) {
                foreach ($unwarned as $approval) {
                    $approval->markSlaWarningSent();
                }
            }
        }
    }

    /**
     * Process approval escalations
     */
    protected function processApprovalEscalations($breaches, $dryRun)
    {
        // Level 1: 1.5x SLA - Department managers
        $needsDeptEscalation = $breaches->filter(function ($approval) {
            if ($approval->sla_escalated) return false;
            $slaLimit = $approval->getSlaHoursLimit();
            if (!$slaLimit) return false;
            $hoursWaiting = $approval->getHoursAtCurrentLevel();
            return $hoursWaiting >= ($slaLimit * 1.5) && $hoursWaiting < ($slaLimit * 2);
        });

        if ($needsDeptEscalation->isNotEmpty()) {
            $this->info("Escalating {$needsDeptEscalation->count()} approval breach(es) to department managers...");
            $this->processApprovalDeptEscalation($needsDeptEscalation, $dryRun);
        }

        // Level 2: 2x SLA - Executives
        $needsExecEscalation = $breaches->filter(function ($approval) {
            if ($approval->sla_escalated) return false;
            $slaLimit = $approval->getSlaHoursLimit();
            if (!$slaLimit) return false;
            $hoursWaiting = $approval->getHoursAtCurrentLevel();
            return $hoursWaiting >= ($slaLimit * 2);
        });

        if ($needsExecEscalation->isNotEmpty()) {
            $this->info("Escalating {$needsExecEscalation->count()} critical approval breach(es) to executives...");
            $this->processApprovalExecEscalation($needsExecEscalation, $dryRun);

            if (!$dryRun) {
                foreach ($needsExecEscalation as $approval) {
                    $approval->markSlaEscalated();
                }
            }

            $this->approvalStats['escalations'] = $needsExecEscalation->count();
        }
    }

    protected function processApprovalDeptEscalation($approvals, $dryRun)
    {
        $grouped = $approvals->groupBy(function ($approval) {
            return $approval->process_code . '_' . ($approval->checker_level ?? 1);
        });

        foreach ($grouped as $key => $groupedApprovals) {
            $sampleApproval = $groupedApprovals->first();
            $roleIds = $this->getRoleIdsForApprovalLevel($sampleApproval);
            $managers = $this->getDepartmentManagers($roleIds);

            if ($managers->isEmpty()) {
                $this->info("  -> No department managers found for {$sampleApproval->process_code}");
                continue;
            }

            foreach ($managers as $manager) {
                $this->sendApprovalToUser($manager, $groupedApprovals, 'escalation', $dryRun, 'supervisor');
            }
        }
    }

    protected function processApprovalExecEscalation($approvals, $dryRun)
    {
        $executives = $this->getExecutives();

        if ($executives->isEmpty()) {
            $this->warn("  -> No executives found for escalation");
            $this->sendApprovalToTechIssues($approvals, 'escalation', $dryRun, 'No executives configured');
            return;
        }

        foreach ($executives as $executive) {
            $this->sendApprovalToUser($executive, $approvals, 'escalation', $dryRun, 'escalation');
        }
    }

    protected function getRoleIdsForApprovalLevel(Approval $approval): array
    {
        $config = $approval->processConfig;
        if (!$config) return [];

        $level = $approval->checker_level ?? 1;

        $roles = match($level) {
            1 => $config->first_checker_roles,
            2 => $config->second_checker_roles,
            3 => $config->approver_roles,
            default => null
        };

        if (empty($roles)) return [];

        if (is_string($roles)) {
            $roles = json_decode($roles, true) ?? [];
        }

        return is_array($roles) ? $roles : [];
    }

    protected function sendApprovalToUser($user, $approvals, $alertType, $dryRun, $recipientType = 'checker')
    {
        try {
            if (!$dryRun) {
                Mail::to($user->email)->send(new ApprovalSlaAlert(
                    $approvals,
                    $alertType,
                    $user->name,
                    $recipientType
                ));
            }

            $this->approvalStats['notifications_sent']++;
            $this->info("  -> Sent {$alertType} to {$user->email} ({$approvals->count()} approvals)");

        } catch (\Exception $e) {
            $this->approvalStats['errors'][] = "Failed to send to {$user->email}: " . $e->getMessage();
            $this->error("  -> Failed to send to {$user->email}: " . $e->getMessage());
            Log::error('Failed to send approval SLA notification', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendApprovalToTechIssues($approvals, $alertType, $dryRun, $reason = '')
    {
        $techEmail = config('mail.tech_issues_email', env('TECH_ISSUES_EMAIL', 'andrew.mashamba@nbc.co.tz'));

        try {
            if (!$dryRun) {
                Mail::to($techEmail)->send(new ApprovalSlaAlert(
                    $approvals,
                    $alertType,
                    'Technical Team',
                    'escalation'
                ));
            }

            $this->approvalStats['notifications_sent']++;
            $reasonText = $reason ? " ({$reason})" : '';
            $this->info("  -> Sent {$alertType} to tech{$reasonText} ({$approvals->count()} approvals)");

        } catch (\Exception $e) {
            $this->approvalStats['errors'][] = "Failed to send to tech: " . $e->getMessage();
            $this->error("  -> Failed to send to tech: " . $e->getMessage());
        }
    }

    // ==================== LOAN APPROVALS ====================

    /**
     * Process loan approvals SLA check
     */
    protected function processLoanApprovals($dryRun, $notifyWarnings)
    {
        $pendingLoans = LoansModel::pendingSlaCheck()
            ->with(['client'])
            ->get();

        if ($pendingLoans->isEmpty()) {
            $this->info('No pending loan approvals with SLA monitoring enabled.');
            return;
        }

        $this->info("Found {$pendingLoans->count()} pending loan(s) to check");

        $warnings = collect();
        $breaches = collect();

        foreach ($pendingLoans as $loan) {
            $this->loanStats['checked']++;
            $slaStatus = $loan->getSlaStatus();

            if ($slaStatus === 'breached') {
                $breaches->push($loan);
                $this->loanStats['breaches']++;

                if (!$dryRun && !$loan->sla_breached_at) {
                    $loan->markSlaBreached($loan->getCurrentApprovalLevel());
                }
            } elseif ($slaStatus === 'warning') {
                $warnings->push($loan);
                $this->loanStats['warnings']++;
            }
        }

        $this->info("  Warnings: {$this->loanStats['warnings']}");
        $this->info("  Breaches: {$this->loanStats['breaches']}");

        // Process breaches
        if ($breaches->isNotEmpty()) {
            $this->processLoanBreaches($breaches, $dryRun);
        }

        // Process warnings
        if ($notifyWarnings && $warnings->isNotEmpty()) {
            $this->processLoanWarnings($warnings, $dryRun);
        }

        // Process escalations
        $this->processLoanEscalations($breaches, $dryRun);
    }

    /**
     * Process breached loans - notify users with the assigned roles
     */
    protected function processLoanBreaches($breaches, $dryRun)
    {
        $this->info('Processing loan SLA breaches...');

        $groupedLoans = $breaches->groupBy(function ($loan) {
            return $loan->approval_stage ?? 'unknown';
        });

        foreach ($groupedLoans as $stage => $loans) {
            $unnotified = $loans->filter(fn($l) => !$l->sla_breach_notified_at);

            if ($unnotified->isEmpty()) {
                continue;
            }

            $sampleLoan = $unnotified->first();
            $roleIds = $sampleLoan->getCurrentStageRoleIds();

            if (empty($roleIds)) {
                $this->sendLoanToTechIssues($unnotified, 'breach', $dryRun, 'No roles configured');
            } else {
                $users = $this->getUsersByRoleIds($roleIds);

                if ($users->isEmpty()) {
                    $roleNames = Role::whereIn('id', $roleIds)->pluck('name')->implode(', ');
                    $this->sendLoanToTechIssues($unnotified, 'breach', $dryRun, "No users with roles: {$roleNames}");
                } else {
                    foreach ($users as $user) {
                        $this->sendLoanToUser($user, $unnotified, 'breach', $dryRun, 'checker');
                    }
                }
            }

            if (!$dryRun) {
                foreach ($unnotified as $loan) {
                    $loan->markSlaBreachNotified();
                }
            }
        }
    }

    /**
     * Process loan warnings
     */
    protected function processLoanWarnings($warnings, $dryRun)
    {
        $this->info('Processing loan SLA warnings...');

        $groupedLoans = $warnings->groupBy(function ($loan) {
            return $loan->approval_stage ?? 'unknown';
        });

        foreach ($groupedLoans as $stage => $loans) {
            $unwarned = $loans->filter(fn($l) => !$l->sla_warning_sent_at);

            if ($unwarned->isEmpty()) {
                continue;
            }

            $sampleLoan = $unwarned->first();
            $roleIds = $sampleLoan->getCurrentStageRoleIds();

            if (!empty($roleIds)) {
                $users = $this->getUsersByRoleIds($roleIds);

                foreach ($users as $user) {
                    $this->sendLoanToUser($user, $unwarned, 'warning', $dryRun, 'checker');
                }
            }

            if (!$dryRun) {
                foreach ($unwarned as $loan) {
                    $loan->markSlaWarningSent();
                }
            }
        }
    }

    /**
     * Process loan escalations
     */
    protected function processLoanEscalations($breaches, $dryRun)
    {
        // Level 1: 1.5x SLA - Department managers (Credit Manager)
        $needsDeptEscalation = $breaches->filter(function ($loan) {
            if ($loan->sla_escalated) return false;
            $slaLimit = $loan->getSlaHoursLimit();
            if (!$slaLimit) return false;
            $hoursWaiting = $loan->getHoursAtCurrentStage();
            return $hoursWaiting >= ($slaLimit * 1.5) && $hoursWaiting < ($slaLimit * 2);
        });

        if ($needsDeptEscalation->isNotEmpty()) {
            $this->info("Escalating {$needsDeptEscalation->count()} loan breach(es) to Credit Manager...");
            $this->processLoanDeptEscalation($needsDeptEscalation, $dryRun);
        }

        // Level 2: 2x SLA - Executives
        $needsExecEscalation = $breaches->filter(function ($loan) {
            if ($loan->sla_escalated) return false;
            $slaLimit = $loan->getSlaHoursLimit();
            if (!$slaLimit) return false;
            $hoursWaiting = $loan->getHoursAtCurrentStage();
            return $hoursWaiting >= ($slaLimit * 2);
        });

        if ($needsExecEscalation->isNotEmpty()) {
            $this->info("Escalating {$needsExecEscalation->count()} critical loan breach(es) to executives...");
            $this->processLoanExecEscalation($needsExecEscalation, $dryRun);

            if (!$dryRun) {
                foreach ($needsExecEscalation as $loan) {
                    $loan->markSlaEscalated();
                }
            }

            $this->loanStats['escalations'] = $needsExecEscalation->count();
        }
    }

    protected function processLoanDeptEscalation($loans, $dryRun)
    {
        // For loans, escalate to Credit Manager
        $creditManagerRoleIds = Role::where('name', 'LIKE', '%Credit Manager%')
            ->pluck('id')
            ->toArray();

        $managers = $this->getUsersByRoleIds($creditManagerRoleIds);

        if ($managers->isEmpty()) {
            $this->info("  -> No Credit Managers found for loan escalation");
            return;
        }

        foreach ($managers as $manager) {
            $this->sendLoanToUser($manager, $loans, 'escalation', $dryRun, 'supervisor');
        }
    }

    protected function processLoanExecEscalation($loans, $dryRun)
    {
        $executives = $this->getExecutives();

        if ($executives->isEmpty()) {
            $this->warn("  -> No executives found for loan escalation");
            $this->sendLoanToTechIssues($loans, 'escalation', $dryRun, 'No executives configured');
            return;
        }

        foreach ($executives as $executive) {
            $this->sendLoanToUser($executive, $loans, 'escalation', $dryRun, 'escalation');
        }
    }

    protected function sendLoanToUser($user, $loans, $alertType, $dryRun, $recipientType = 'checker')
    {
        try {
            if (!$dryRun) {
                Mail::to($user->email)->send(new LoanSlaAlert(
                    $loans,
                    $alertType,
                    $user->name,
                    $recipientType
                ));
            }

            $this->loanStats['notifications_sent']++;
            $this->info("  -> Sent loan {$alertType} to {$user->email} ({$loans->count()} loans)");

        } catch (\Exception $e) {
            $this->loanStats['errors'][] = "Failed to send to {$user->email}: " . $e->getMessage();
            $this->error("  -> Failed to send loan notification to {$user->email}: " . $e->getMessage());
            Log::error('Failed to send loan SLA notification', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendLoanToTechIssues($loans, $alertType, $dryRun, $reason = '')
    {
        $techEmail = config('mail.tech_issues_email', env('TECH_ISSUES_EMAIL', 'andrew.mashamba@nbc.co.tz'));

        try {
            if (!$dryRun) {
                Mail::to($techEmail)->send(new LoanSlaAlert(
                    $loans,
                    $alertType,
                    'Technical Team',
                    'escalation'
                ));
            }

            $this->loanStats['notifications_sent']++;
            $reasonText = $reason ? " ({$reason})" : '';
            $this->info("  -> Sent loan {$alertType} to tech{$reasonText} ({$loans->count()} loans)");

        } catch (\Exception $e) {
            $this->loanStats['errors'][] = "Failed to send to tech: " . $e->getMessage();
            $this->error("  -> Failed to send loan notification to tech: " . $e->getMessage());
        }
    }

    // ==================== SHARED HELPERS ====================

    /**
     * Get users who have a specific role
     */
    protected function getUsersByRoleIds(array $roleIds): \Illuminate\Support\Collection
    {
        if (empty($roleIds)) {
            return collect();
        }

        $cleanRoleIds = array_map(function ($id) {
            return is_numeric($id) ? (int)$id : $id;
        }, $roleIds);

        return User::whereHas('roles', function ($query) use ($cleanRoleIds) {
            $query->whereIn('roles.id', $cleanRoleIds);
        })
        ->whereNotNull('email')
        ->where('email', '!=', '')
        ->get();
    }

    /**
     * Get department manager users for escalation
     */
    protected function getDepartmentManagers(array $roleIds): \Illuminate\Support\Collection
    {
        if (empty($roleIds)) {
            return collect();
        }

        $departmentIds = Role::whereIn('id', $roleIds)
            ->whereNotNull('department_id')
            ->pluck('department_id')
            ->unique()
            ->toArray();

        if (empty($departmentIds)) {
            return collect();
        }

        $managerRoleIds = Role::whereIn('department_id', $departmentIds)
            ->where(function ($query) {
                $query->where('name', 'LIKE', '%Manager%')
                      ->orWhere('name', 'LIKE', '%Chief%')
                      ->orWhere('name', 'LIKE', '%Head%');
            })
            ->pluck('id')
            ->toArray();

        return $this->getUsersByRoleIds($managerRoleIds);
    }

    /**
     * Get executive users for final escalation
     */
    protected function getExecutives(): \Illuminate\Support\Collection
    {
        $executiveRoleIds = Role::whereIn('name', $this->executiveRoles)
            ->pluck('id')
            ->toArray();

        return $this->getUsersByRoleIds($executiveRoleIds);
    }

    /**
     * Log summary of the operation
     */
    protected function logSummary($startTime)
    {
        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info('===========================================');
        $this->info('  Summary');
        $this->info('===========================================');

        $this->info('  GENERAL APPROVALS:');
        $this->info("    Checked:           {$this->approvalStats['checked']}");
        $this->info("    Warnings:          {$this->approvalStats['warnings']}");
        $this->info("    Breaches:          {$this->approvalStats['breaches']}");
        $this->info("    Notifications:     {$this->approvalStats['notifications_sent']}");
        $this->info("    Escalations:       {$this->approvalStats['escalations']}");
        $this->info("    Errors:            " . count($this->approvalStats['errors']));

        $this->newLine();
        $this->info('  LOAN APPROVALS:');
        $this->info("    Checked:           {$this->loanStats['checked']}");
        $this->info("    Warnings:          {$this->loanStats['warnings']}");
        $this->info("    Breaches:          {$this->loanStats['breaches']}");
        $this->info("    Notifications:     {$this->loanStats['notifications_sent']}");
        $this->info("    Escalations:       {$this->loanStats['escalations']}");
        $this->info("    Errors:            " . count($this->loanStats['errors']));

        $this->newLine();
        $totalChecked = $this->approvalStats['checked'] + $this->loanStats['checked'];
        $totalBreaches = $this->approvalStats['breaches'] + $this->loanStats['breaches'];
        $totalNotifications = $this->approvalStats['notifications_sent'] + $this->loanStats['notifications_sent'];

        $this->info("  TOTALS:");
        $this->info("    Total Checked:     {$totalChecked}");
        $this->info("    Total Breaches:    {$totalBreaches}");
        $this->info("    Total Sent:        {$totalNotifications}");
        $this->info("    Duration:          {$duration}s");
        $this->info('===========================================');
        $this->info('  Completed: ' . now()->format('Y-m-d H:i:s'));
        $this->info('===========================================');

        // Display errors if any
        $allErrors = array_merge($this->approvalStats['errors'], $this->loanStats['errors']);
        if (!empty($allErrors)) {
            $this->newLine();
            $this->error('Errors encountered:');
            foreach ($allErrors as $error) {
                $this->error("  - {$error}");
            }
        }
    }
}
