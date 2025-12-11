<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Billing Payment Due Alerts Service
 *
 * Sends payment reminders for:
 * 1. Member bills approaching due date
 * 2. Overdue member bills
 * 3. Trade receivables reminders
 *
 * Uses SMS and email notifications via MemberCommunicationService.
 *
 * @package App\Services
 * @version 1.0
 */
class BillingPaymentDueAlertsService
{
    /**
     * Days before due date to send upcoming reminder
     */
    protected $upcomingReminderDays = 3;

    /**
     * Minimum hours between reminders for the same bill
     */
    protected $reminderCooldownHours = 24;

    /**
     * Maximum reminders per bill
     */
    protected $maxReminders = 5;

    /**
     * Communication service
     */
    protected $communicationService;

    public function __construct()
    {
        // Use Laravel's container to resolve dependencies properly
        $this->communicationService = app(MemberCommunicationService::class);
        $this->loadSettings();
    }

    /**
     * Load configurable settings from institution
     */
    protected function loadSettings(): void
    {
        $institution = DB::table('institutions')->where('id', 1)->first();

        if ($institution) {
            $this->upcomingReminderDays = (int) ($institution->bill_reminder_days ?? 3);
            $this->reminderCooldownHours = (int) ($institution->bill_reminder_cooldown_hours ?? 24);
            $this->maxReminders = (int) ($institution->bill_max_reminders ?? 5);
        }

        Log::info('[BILLING_ALERTS] Loaded settings', [
            'upcoming_reminder_days' => $this->upcomingReminderDays,
            'reminder_cooldown_hours' => $this->reminderCooldownHours,
            'max_reminders' => $this->maxReminders
        ]);
    }

    /**
     * Process all payment due alerts
     *
     * @param bool $dryRun If true, don't send actual notifications
     * @return array Results summary
     */
    public function processAllAlerts(bool $dryRun = false): array
    {
        Log::info('[BILLING_ALERTS] Starting payment due alerts processing', [
            'dry_run' => $dryRun,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);

        $results = [
            'status' => 'success',
            'dry_run' => $dryRun,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'bills' => [
                'upcoming' => ['count' => 0, 'sent' => 0, 'skipped' => 0, 'errors' => 0],
                'overdue' => ['count' => 0, 'sent' => 0, 'skipped' => 0, 'errors' => 0],
            ],
            'trade_receivables' => [
                'upcoming' => ['count' => 0, 'sent' => 0, 'skipped' => 0, 'errors' => 0],
                'overdue' => ['count' => 0, 'sent' => 0, 'skipped' => 0, 'errors' => 0],
            ],
            'errors' => []
        ];

        // Process member bills
        try {
            $upcomingBillsResult = $this->processUpcomingBills($dryRun);
            $results['bills']['upcoming'] = $upcomingBillsResult;
        } catch (\Exception $e) {
            $results['errors'][] = ['type' => 'upcoming_bills', 'error' => $e->getMessage()];
            Log::error('[BILLING_ALERTS] Error processing upcoming bills', ['error' => $e->getMessage()]);
        }

        try {
            $overdueBillsResult = $this->processOverdueBills($dryRun);
            $results['bills']['overdue'] = $overdueBillsResult;
        } catch (\Exception $e) {
            $results['errors'][] = ['type' => 'overdue_bills', 'error' => $e->getMessage()];
            Log::error('[BILLING_ALERTS] Error processing overdue bills', ['error' => $e->getMessage()]);
        }

        // Process trade receivables
        try {
            $upcomingReceivablesResult = $this->processUpcomingReceivables($dryRun);
            $results['trade_receivables']['upcoming'] = $upcomingReceivablesResult;
        } catch (\Exception $e) {
            $results['errors'][] = ['type' => 'upcoming_receivables', 'error' => $e->getMessage()];
            Log::error('[BILLING_ALERTS] Error processing upcoming receivables', ['error' => $e->getMessage()]);
        }

        try {
            $overdueReceivablesResult = $this->processOverdueReceivables($dryRun);
            $results['trade_receivables']['overdue'] = $overdueReceivablesResult;
        } catch (\Exception $e) {
            $results['errors'][] = ['type' => 'overdue_receivables', 'error' => $e->getMessage()];
            Log::error('[BILLING_ALERTS] Error processing overdue receivables', ['error' => $e->getMessage()]);
        }

        // Calculate totals
        $results['totals'] = [
            'total_found' => $results['bills']['upcoming']['count'] + $results['bills']['overdue']['count'] +
                            $results['trade_receivables']['upcoming']['count'] + $results['trade_receivables']['overdue']['count'],
            'total_sent' => $results['bills']['upcoming']['sent'] + $results['bills']['overdue']['sent'] +
                           $results['trade_receivables']['upcoming']['sent'] + $results['trade_receivables']['overdue']['sent'],
            'total_skipped' => $results['bills']['upcoming']['skipped'] + $results['bills']['overdue']['skipped'] +
                              $results['trade_receivables']['upcoming']['skipped'] + $results['trade_receivables']['overdue']['skipped'],
            'total_errors' => count($results['errors']) + $results['bills']['upcoming']['errors'] + $results['bills']['overdue']['errors'] +
                             $results['trade_receivables']['upcoming']['errors'] + $results['trade_receivables']['overdue']['errors']
        ];

        Log::info('[BILLING_ALERTS] Processing completed', $results['totals']);

        return $results;
    }

    /**
     * Process upcoming bills (due within X days)
     */
    protected function processUpcomingBills(bool $dryRun): array
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays($this->upcomingReminderDays);

        $bills = DB::table('bills')
            ->where('status', 'PENDING')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->whereRaw('amount_paid < amount_due')
            ->get();

        $result = ['count' => $bills->count(), 'sent' => 0, 'skipped' => 0, 'errors' => 0];

        foreach ($bills as $bill) {
            try {
                $sendResult = $this->sendBillReminder($bill, 'upcoming', $dryRun);
                if ($sendResult === 'sent') {
                    $result['sent']++;
                } elseif ($sendResult === 'skipped') {
                    $result['skipped']++;
                } else {
                    $result['errors']++;
                }
            } catch (\Exception $e) {
                $result['errors']++;
                Log::error('[BILLING_ALERTS] Error sending upcoming bill reminder', [
                    'bill_id' => $bill->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $result;
    }

    /**
     * Process overdue bills
     */
    protected function processOverdueBills(bool $dryRun): array
    {
        $bills = DB::table('bills')
            ->whereIn('status', ['PENDING', 'OVERDUE'])
            ->where('due_date', '<', Carbon::today())
            ->whereRaw('amount_paid < amount_due')
            ->get();

        $result = ['count' => $bills->count(), 'sent' => 0, 'skipped' => 0, 'errors' => 0];

        foreach ($bills as $bill) {
            try {
                $sendResult = $this->sendBillReminder($bill, 'overdue', $dryRun);
                if ($sendResult === 'sent') {
                    $result['sent']++;

                    // Update bill status to OVERDUE if not already
                    if (!$dryRun && $bill->status !== 'OVERDUE') {
                        DB::table('bills')
                            ->where('id', $bill->id)
                            ->update(['status' => 'OVERDUE', 'updated_at' => now()]);
                    }
                } elseif ($sendResult === 'skipped') {
                    $result['skipped']++;
                } else {
                    $result['errors']++;
                }
            } catch (\Exception $e) {
                $result['errors']++;
                Log::error('[BILLING_ALERTS] Error sending overdue bill reminder', [
                    'bill_id' => $bill->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $result;
    }

    /**
     * Send reminder for a bill
     */
    protected function sendBillReminder($bill, string $type, bool $dryRun): string
    {
        // Check cooldown and max reminders
        if (!$this->canSendReminder($bill)) {
            return 'skipped';
        }

        // Get member details
        $member = DB::table('clients')
            ->where('client_number', $bill->client_number)
            ->first();

        if (!$member) {
            Log::warning('[BILLING_ALERTS] Member not found for bill', [
                'bill_id' => $bill->id,
                'client_number' => $bill->client_number
            ]);
            return 'error';
        }

        // Get service details
        $service = DB::table('services')
            ->where('id', $bill->service_id)
            ->first();

        $serviceName = $service->name ?? 'Bill Payment';
        $memberName = trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name);
        $balance = $bill->amount_due - $bill->amount_paid;
        $dueDate = Carbon::parse($bill->due_date)->format('d M Y');

        // Build message based on type
        if ($type === 'upcoming') {
            $message = "Dear {$memberName}, your {$serviceName} payment of TZS " . number_format($balance, 2) .
                      " is due on {$dueDate}. Control Number: {$bill->control_number}. Please pay before the due date.";
        } else {
            $daysOverdue = Carbon::parse($bill->due_date)->diffInDays(Carbon::today());
            $message = "Dear {$memberName}, your {$serviceName} payment of TZS " . number_format($balance, 2) .
                      " was due on {$dueDate} ({$daysOverdue} days ago). Control Number: {$bill->control_number}. Please pay immediately to avoid penalties.";
        }

        if ($dryRun) {
            Log::info('[BILLING_ALERTS] DRY RUN - Would send bill reminder', [
                'bill_id' => $bill->id,
                'type' => $type,
                'member' => $memberName,
                'phone' => $member->mobile_phone_number ?? 'N/A',
                'message' => $message
            ]);
            return 'sent';
        }

        // Send SMS if phone number exists
        $smsSent = false;
        if (!empty($member->mobile_phone_number)) {
            try {
                $this->communicationService->sendSms(
                    $member->mobile_phone_number,
                    $message,
                    'bill_reminder'
                );
                $smsSent = true;
            } catch (\Exception $e) {
                Log::error('[BILLING_ALERTS] Failed to send SMS', [
                    'bill_id' => $bill->id,
                    'phone' => $member->mobile_phone_number,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Update reminder tracking
        $this->updateBillReminderTracking($bill->id);

        Log::info('[BILLING_ALERTS] Bill reminder sent', [
            'bill_id' => $bill->id,
            'type' => $type,
            'member' => $memberName,
            'sms_sent' => $smsSent
        ]);

        return 'sent';
    }

    /**
     * Check if a reminder can be sent (cooldown and max reminders)
     */
    protected function canSendReminder($bill): bool
    {
        // Check reminder count from bills table or a tracking column
        $reminderCount = $bill->reminder_count ?? 0;
        if ($reminderCount >= $this->maxReminders) {
            return false;
        }

        // Check cooldown
        if (!empty($bill->last_reminder_sent_at)) {
            $lastReminder = Carbon::parse($bill->last_reminder_sent_at);
            $hoursSinceLastReminder = $lastReminder->diffInHours(now());
            if ($hoursSinceLastReminder < $this->reminderCooldownHours) {
                return false;
            }
        }

        return true;
    }

    /**
     * Update reminder tracking for a bill
     */
    protected function updateBillReminderTracking(int $billId): void
    {
        DB::table('bills')
            ->where('id', $billId)
            ->update([
                'last_reminder_sent_at' => now(),
                'reminder_count' => DB::raw('COALESCE(reminder_count, 0) + 1'),
                'updated_at' => now()
            ]);
    }

    /**
     * Process upcoming trade receivables
     */
    protected function processUpcomingReceivables(bool $dryRun): array
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays($this->upcomingReminderDays);

        $receivables = DB::table('trade_receivables')
            ->whereIn('status', ['pending', 'partial'])
            ->whereBetween('due_date', [$startDate, $endDate])
            ->where('balance', '>', 0)
            ->get();

        $result = ['count' => $receivables->count(), 'sent' => 0, 'skipped' => 0, 'errors' => 0];

        foreach ($receivables as $receivable) {
            try {
                $sendResult = $this->sendReceivableReminder($receivable, 'upcoming', $dryRun);
                if ($sendResult === 'sent') {
                    $result['sent']++;
                } elseif ($sendResult === 'skipped') {
                    $result['skipped']++;
                } else {
                    $result['errors']++;
                }
            } catch (\Exception $e) {
                $result['errors']++;
                Log::error('[BILLING_ALERTS] Error sending upcoming receivable reminder', [
                    'receivable_id' => $receivable->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $result;
    }

    /**
     * Process overdue trade receivables
     */
    protected function processOverdueReceivables(bool $dryRun): array
    {
        $receivables = DB::table('trade_receivables')
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->where('due_date', '<', Carbon::today())
            ->where('balance', '>', 0)
            ->get();

        $result = ['count' => $receivables->count(), 'sent' => 0, 'skipped' => 0, 'errors' => 0];

        foreach ($receivables as $receivable) {
            try {
                $sendResult = $this->sendReceivableReminder($receivable, 'overdue', $dryRun);
                if ($sendResult === 'sent') {
                    $result['sent']++;

                    // Update status to overdue if not already
                    if (!$dryRun && $receivable->status !== 'overdue') {
                        DB::table('trade_receivables')
                            ->where('id', $receivable->id)
                            ->update(['status' => 'overdue', 'updated_at' => now()]);
                    }
                } elseif ($sendResult === 'skipped') {
                    $result['skipped']++;
                } else {
                    $result['errors']++;
                }
            } catch (\Exception $e) {
                $result['errors']++;
                Log::error('[BILLING_ALERTS] Error sending overdue receivable reminder', [
                    'receivable_id' => $receivable->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $result;
    }

    /**
     * Send reminder for a trade receivable
     */
    protected function sendReceivableReminder($receivable, string $type, bool $dryRun): string
    {
        // Check cooldown and max reminders
        $reminderCount = $receivable->reminder_count ?? 0;
        if ($reminderCount >= $this->maxReminders) {
            return 'skipped';
        }

        if (!empty($receivable->last_reminder_sent_at)) {
            $lastReminder = Carbon::parse($receivable->last_reminder_sent_at);
            $hoursSinceLastReminder = $lastReminder->diffInHours(now());
            if ($hoursSinceLastReminder < $this->reminderCooldownHours) {
                return 'skipped';
            }
        }

        $customerName = $receivable->customer_name;
        $dueDate = Carbon::parse($receivable->due_date)->format('d M Y');
        $balance = $receivable->balance;

        // Build message based on type
        if ($type === 'upcoming') {
            $message = "Dear {$customerName}, your invoice {$receivable->invoice_number} for TZS " . number_format($balance, 2) .
                      " is due on {$dueDate}. Please pay before the due date.";
        } else {
            $daysOverdue = Carbon::parse($receivable->due_date)->diffInDays(Carbon::today());
            $message = "Dear {$customerName}, your invoice {$receivable->invoice_number} for TZS " . number_format($balance, 2) .
                      " was due on {$dueDate} ({$daysOverdue} days overdue). Please pay immediately.";
        }

        if ($dryRun) {
            Log::info('[BILLING_ALERTS] DRY RUN - Would send receivable reminder', [
                'receivable_id' => $receivable->id,
                'type' => $type,
                'customer' => $customerName,
                'phone' => $receivable->customer_phone ?? 'N/A',
                'message' => $message
            ]);
            return 'sent';
        }

        // Send SMS if phone number exists
        $smsSent = false;
        if (!empty($receivable->customer_phone)) {
            try {
                $this->communicationService->sendSms(
                    $receivable->customer_phone,
                    $message,
                    'invoice_reminder'
                );
                $smsSent = true;
            } catch (\Exception $e) {
                Log::error('[BILLING_ALERTS] Failed to send SMS for receivable', [
                    'receivable_id' => $receivable->id,
                    'phone' => $receivable->customer_phone,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Update reminder tracking
        DB::table('trade_receivables')
            ->where('id', $receivable->id)
            ->update([
                'last_reminder_sent_at' => now(),
                'reminder_count' => DB::raw('COALESCE(reminder_count, 0) + 1'),
                'updated_at' => now()
            ]);

        Log::info('[BILLING_ALERTS] Receivable reminder sent', [
            'receivable_id' => $receivable->id,
            'invoice_number' => $receivable->invoice_number,
            'type' => $type,
            'customer' => $customerName,
            'sms_sent' => $smsSent
        ]);

        return 'sent';
    }

    /**
     * Get summary of pending payments for reporting
     */
    public function getPendingPaymentsSummary(): array
    {
        $billsSummary = DB::table('bills')
            ->selectRaw("
                COUNT(CASE WHEN status = 'PENDING' AND due_date >= CURRENT_DATE THEN 1 END) as pending_current,
                COUNT(CASE WHEN status IN ('PENDING', 'OVERDUE') AND due_date < CURRENT_DATE THEN 1 END) as overdue,
                SUM(CASE WHEN status = 'PENDING' AND due_date >= CURRENT_DATE THEN amount_due - amount_paid ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status IN ('PENDING', 'OVERDUE') AND due_date < CURRENT_DATE THEN amount_due - amount_paid ELSE 0 END) as overdue_amount
            ")
            ->first();

        $receivablesSummary = DB::table('trade_receivables')
            ->selectRaw("
                COUNT(CASE WHEN status IN ('pending', 'partial') AND due_date >= CURRENT_DATE THEN 1 END) as pending_current,
                COUNT(CASE WHEN status IN ('pending', 'partial', 'overdue') AND due_date < CURRENT_DATE THEN 1 END) as overdue,
                SUM(CASE WHEN status IN ('pending', 'partial') AND due_date >= CURRENT_DATE THEN balance ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status IN ('pending', 'partial', 'overdue') AND due_date < CURRENT_DATE THEN balance ELSE 0 END) as overdue_amount
            ")
            ->first();

        return [
            'bills' => [
                'pending_count' => $billsSummary->pending_current ?? 0,
                'overdue_count' => $billsSummary->overdue ?? 0,
                'pending_amount' => (float) ($billsSummary->pending_amount ?? 0),
                'overdue_amount' => (float) ($billsSummary->overdue_amount ?? 0),
            ],
            'trade_receivables' => [
                'pending_count' => $receivablesSummary->pending_current ?? 0,
                'overdue_count' => $receivablesSummary->overdue ?? 0,
                'pending_amount' => (float) ($receivablesSummary->pending_amount ?? 0),
                'overdue_amount' => (float) ($receivablesSummary->overdue_amount ?? 0),
            ]
        ];
    }
}
