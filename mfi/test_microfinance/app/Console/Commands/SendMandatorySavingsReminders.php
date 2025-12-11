<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MandatorySavingsTracking;
use App\Models\MandatorySavingsSettings;
use App\Models\ClientsModel;
use App\Services\NotificationServicex;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SendMandatorySavingsReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mandatory-savings:reminders
                            {--interval=5 : Reminder interval in days (default: 5)}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to members with unpaid mandatory savings every N days until payment is complete';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $interval = (int) $this->option('interval');
        $dryRun = $this->option('dry-run');

        $this->info("Mandatory Savings Reminder System");
        $this->info("================================");
        $this->info("Reminder interval: {$interval} days");
        $this->info("Mode: " . ($dryRun ? "DRY RUN (no messages will be sent)" : "LIVE"));
        $this->newLine();

        try {
            // Get settings
            $settings = MandatorySavingsSettings::forInstitution('1');
            if (!$settings) {
                $this->error('Mandatory savings settings not configured.');
                return 1;
            }

            // Get all unpaid tracking records (UNPAID, PARTIAL, or OVERDUE)
            $unpaidRecords = MandatorySavingsTracking::whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
                ->get();

            if ($unpaidRecords->isEmpty()) {
                $this->info('No unpaid mandatory savings records found. All members are up to date!');
                return 0;
            }

            $this->info("Found {$unpaidRecords->count()} unpaid records to check.");
            $this->newLine();

            $remindersSent = 0;
            $remindersSkipped = 0;
            $errors = 0;

            // Initialize notification service
            $notificationService = new NotificationServicex();

            foreach ($unpaidRecords as $record) {
                try {
                    // Determine if a reminder should be sent based on:
                    // 1. Days since bill was generated (created_at)
                    // 2. Days since last notification was sent
                    $daysSinceBillGenerated = Carbon::parse($record->created_at)->diffInDays(now());

                    // Get last notification sent for this record
                    $lastNotification = DB::table('mandatory_savings_notifications')
                        ->where('client_number', $record->client_number)
                        ->where('year', $record->year)
                        ->where('month', $record->month)
                        ->where('status', 'SENT')
                        ->orderBy('sent_at', 'desc')
                        ->first();

                    $daysSinceLastNotification = $lastNotification
                        ? Carbon::parse($lastNotification->sent_at)->diffInDays(now())
                        : $interval; // If no notification sent, consider it eligible

                    // Skip if last notification was sent less than $interval days ago
                    if ($daysSinceLastNotification < $interval) {
                        $this->line("  Skipping {$record->client_number} - Last reminder sent {$daysSinceLastNotification} days ago");
                        $remindersSkipped++;
                        continue;
                    }

                    // Get member details
                    $member = ClientsModel::where('client_number', $record->client_number)->first();
                    if (!$member) {
                        $this->warn("  Member not found: {$record->client_number}");
                        $errors++;
                        continue;
                    }

                    $memberName = trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name);
                    $period = Carbon::createFromDate($record->year, $record->month, 1)->format('F Y');

                    // Prepare reminder message
                    $reminderNumber = $lastNotification
                        ? DB::table('mandatory_savings_notifications')
                            ->where('client_number', $record->client_number)
                            ->where('year', $record->year)
                            ->where('month', $record->month)
                            ->where('status', 'SENT')
                            ->count() + 1
                        : 1;

                    // Get control number from bills
                    $bill = DB::table('bills')
                        ->where('client_number', $record->client_number)
                        ->where('status', '!=', 'PAID')
                        ->whereRaw("service_id IN (SELECT id FROM services WHERE code = 'SAV')")
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $controlNumber = $bill ? $bill->control_number : 'N/A';

                    $this->info("Processing reminder for: {$memberName} ({$record->client_number})");
                    $this->line("  Period: {$period}");
                    $this->line("  Balance: TZS " . number_format($record->balance, 2));
                    $this->line("  Status: {$record->status}");
                    $this->line("  Days since billing: {$daysSinceBillGenerated}");
                    $this->line("  Days since last reminder: {$daysSinceLastNotification}");
                    $this->line("  Reminder #: {$reminderNumber}");
                    $this->line("  Control Number: {$controlNumber}");

                    if ($dryRun) {
                        $this->line("  [DRY RUN] Would send reminder");
                        $remindersSent++;
                        continue;
                    }

                    // Send the reminder notification
                    $result = $notificationService->sendMandatorySavingsReminder(
                        $member,
                        $controlNumber,
                        $record->balance,
                        $record->due_date,
                        $record->year,
                        $record->month,
                        $record->account_number,
                        $reminderNumber
                    );

                    // Record the notification
                    $notificationId = DB::table('mandatory_savings_notifications')->insertGetId([
                        'client_number' => $record->client_number,
                        'account_number' => $record->account_number,
                        'year' => $record->year,
                        'month' => $record->month,
                        'notification_type' => 'RECURRING_REMINDER',
                        'notification_method' => 'BOTH',
                        'message' => $this->buildReminderMessage($member, $record, $reminderNumber),
                        'status' => ($result['email_sent'] || $result['sms_sent']) ? 'SENT' : 'FAILED',
                        'scheduled_at' => now(),
                        'sent_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    if ($result['email_sent'] || $result['sms_sent']) {
                        $this->info("  ✓ Reminder sent successfully");
                        $remindersSent++;
                    } else {
                        $this->warn("  ✗ Failed to send reminder");
                        $this->line("    Email error: " . ($result['email_error'] ?? 'N/A'));
                        $this->line("    SMS error: " . ($result['sms_error'] ?? 'N/A'));
                        $errors++;
                    }

                    Log::info('Mandatory savings reminder processed', [
                        'client_number' => $record->client_number,
                        'year' => $record->year,
                        'month' => $record->month,
                        'reminder_number' => $reminderNumber,
                        'balance' => $record->balance,
                        'email_sent' => $result['email_sent'],
                        'sms_sent' => $result['sms_sent'],
                        'notification_id' => $notificationId
                    ]);

                } catch (\Exception $e) {
                    $this->error("  Error processing {$record->client_number}: " . $e->getMessage());
                    Log::error('Error sending mandatory savings reminder', [
                        'client_number' => $record->client_number,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $errors++;
                }

                $this->newLine();
            }

            // Summary
            $this->newLine();
            $this->info("================================");
            $this->info("Summary:");
            $this->info("  Reminders sent: {$remindersSent}");
            $this->info("  Skipped (recent reminder): {$remindersSkipped}");
            $this->info("  Errors: {$errors}");
            $this->info("================================");

            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Mandatory savings reminders error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Build the friendly reminder message content
     */
    protected function buildReminderMessage($member, $record, $reminderNumber)
    {
        $memberName = trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name);
        $period = Carbon::createFromDate($record->year, $record->month, 1)->format('F Y');
        $balance = number_format($record->balance, 2);

        return "Friendly Reminder #{$reminderNumber}: Dear {$memberName}, your savings contribution of TZS {$balance} for {$period} is pending. Your savings build your future! Thank you for being a valued NBC SACCO member.";
    }
}
