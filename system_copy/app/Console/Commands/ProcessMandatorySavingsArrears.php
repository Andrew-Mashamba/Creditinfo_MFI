<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MandatorySavingsTracking;
use App\Models\MandatorySavingsSettings;
use App\Models\ClientsModel;
use App\Services\NotificationServicex;
use App\Services\PaymentLinkService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessMandatorySavingsArrears extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mandatory-savings:arrears
                            {--action=all : Action to perform (update, notify, report, all)}
                            {--interval=5 : Reminder interval in days}
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process mandatory savings arrears - update status, send escalated reminders, and manage defaulters (3+ months)';

    protected $notificationService;
    protected $paymentLinkService;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->option('action');
        $interval = (int) $this->option('interval');
        $dryRun = $this->option('dry-run');

        $this->info("======================================");
        $this->info("Mandatory Savings Arrears Management");
        $this->info("======================================");
        $this->info("Action: {$action}");
        $this->info("Reminder interval: {$interval} days");
        $this->info("Mode: " . ($dryRun ? "DRY RUN" : "LIVE"));
        $this->newLine();

        try {
            $this->notificationService = new NotificationServicex();
            $this->paymentLinkService = new PaymentLinkService();

            $results = [
                'arrears_updated' => 0,
                'reminders_sent' => 0,
                'defaulters_identified' => 0,
                'errors' => []
            ];

            switch ($action) {
                case 'update':
                    $results = array_merge($results, $this->updateArrearsStatus($dryRun));
                    break;
                case 'notify':
                    $results = array_merge($results, $this->sendArrearsReminders($interval, $dryRun));
                    break;
                case 'report':
                    $this->generateArrearsReport();
                    return 0;
                case 'all':
                default:
                    $results = array_merge($results, $this->updateArrearsStatus($dryRun));
                    $results = array_merge($results, $this->sendArrearsReminders($interval, $dryRun));
                    break;
            }

            // Final Summary
            $this->newLine();
            $this->info("======================================");
            $this->info("Processing Complete");
            $this->info("======================================");
            $this->info("Arrears status updated: {$results['arrears_updated']}");
            $this->info("Reminders sent: {$results['reminders_sent']}");
            $this->info("Defaulters identified: {$results['defaulters_identified']}");
            $this->info("Errors: " . count($results['errors']));

            if (!empty($results['errors'])) {
                $this->newLine();
                $this->warn("Errors encountered:");
                foreach (array_slice($results['errors'], 0, 5) as $error) {
                    $this->error("  - " . $error);
                }
                if (count($results['errors']) > 5) {
                    $this->error("  ... and " . (count($results['errors']) - 5) . " more errors");
                }
            }

            Log::info('Mandatory savings arrears processing completed', $results);

            return 0;

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error('Mandatory savings arrears processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Update arrears status for all unpaid records
     */
    protected function updateArrearsStatus($dryRun)
    {
        $this->info("Step 1: Updating Arrears Status");
        $this->info("--------------------------------");

        $results = [
            'arrears_updated' => 0,
            'defaulters_identified' => 0,
            'by_status' => [
                'CURRENT' => 0,
                'ARREARS_1M' => 0,
                'ARREARS_2M' => 0,
                'ARREARS_3M' => 0,
                'DEFAULTER' => 0
            ],
            'errors' => []
        ];

        // Get all unpaid records
        $unpaidRecords = MandatorySavingsTracking::whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE', 'DEFAULTED'])
            ->where('balance', '>', 0)
            ->get();

        $this->info("Found {$unpaidRecords->count()} unpaid records to process");

        $progressBar = $this->output->createProgressBar($unpaidRecords->count());
        $progressBar->start();

        foreach ($unpaidRecords as $record) {
            try {
                $oldStatus = $record->arrears_status;

                if (!$dryRun) {
                    $record->updateArrearsStatus();
                } else {
                    // Simulate status calculation
                    $monthsOverdue = $record->due_date ? $record->due_date->diffInMonths(now()) : 0;
                    if ($monthsOverdue >= 3) {
                        $record->arrears_status = MandatorySavingsTracking::ARREARS_DEFAULTER;
                    } elseif ($monthsOverdue >= 2) {
                        $record->arrears_status = MandatorySavingsTracking::ARREARS_3_MONTHS;
                    } elseif ($monthsOverdue >= 1) {
                        $record->arrears_status = MandatorySavingsTracking::ARREARS_2_MONTHS;
                    } elseif ($record->due_date && $record->due_date->isPast()) {
                        $record->arrears_status = MandatorySavingsTracking::ARREARS_1_MONTH;
                    }
                }

                $results['arrears_updated']++;
                $results['by_status'][$record->arrears_status] = ($results['by_status'][$record->arrears_status] ?? 0) + 1;

                if ($record->arrears_status === MandatorySavingsTracking::ARREARS_DEFAULTER) {
                    $results['defaulters_identified']++;
                }

            } catch (\Exception $e) {
                $results['errors'][] = "Record {$record->id}: " . $e->getMessage();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display status breakdown
        $this->table(
            ['Arrears Status', 'Count'],
            collect($results['by_status'])->map(function ($count, $status) {
                return [$status, $count];
            })->toArray()
        );

        return $results;
    }

    /**
     * Send escalated reminders based on arrears status
     */
    protected function sendArrearsReminders($interval, $dryRun)
    {
        $this->info("Step 2: Sending Escalated Reminders");
        $this->info("------------------------------------");

        $results = [
            'reminders_sent' => 0,
            'skipped' => 0,
            'by_level' => [
                'ARREARS_1M' => 0,
                'ARREARS_2M' => 0,
                'ARREARS_3M' => 0,
                'DEFAULTER' => 0
            ],
            'errors' => []
        ];

        // Get records that need reminders
        $recordsNeedingReminder = MandatorySavingsTracking::needsReminder($interval)
            ->with('client')
            ->get();

        $this->info("Found {$recordsNeedingReminder->count()} records needing reminders");
        $this->newLine();

        foreach ($recordsNeedingReminder as $record) {
            try {
                $member = $record->client;
                if (!$member) {
                    $results['errors'][] = "Member not found for record {$record->id}";
                    continue;
                }

                // Get cumulative arrears for this member
                $arrearsSummary = MandatorySavingsTracking::getMemberArrearsSummary($member->client_number);

                $this->line("Processing: {$member->first_name} {$member->last_name} ({$member->client_number})");
                $this->line("  - Total Arrears: TZS " . number_format($arrearsSummary['total_arrears'], 2));
                $this->line("  - Months in Arrears: {$arrearsSummary['months_in_arrears']}");
                $this->line("  - Status: {$record->arrears_status}");
                $this->line("  - Is Defaulter: " . ($arrearsSummary['is_defaulter'] ? 'YES' : 'NO'));

                if ($dryRun) {
                    $this->line("  [DRY RUN] Would send {$record->arrears_status} reminder");
                    $results['reminders_sent']++;
                    $results['by_level'][$record->arrears_status] = ($results['by_level'][$record->arrears_status] ?? 0) + 1;
                    $this->newLine();
                    continue;
                }

                // Send the appropriate reminder based on arrears status
                $reminderResult = $this->sendEscalatedReminder($member, $record, $arrearsSummary);

                if ($reminderResult['success']) {
                    $record->recordReminderSent();
                    $results['reminders_sent']++;
                    $results['by_level'][$record->arrears_status] = ($results['by_level'][$record->arrears_status] ?? 0) + 1;
                    $this->info("  Reminder sent successfully");
                } else {
                    $results['errors'][] = "Failed to send reminder for {$member->client_number}: " . ($reminderResult['error'] ?? 'Unknown error');
                    $this->error("  Failed to send reminder");
                }

                $this->newLine();

            } catch (\Exception $e) {
                $results['errors'][] = "Record {$record->id}: " . $e->getMessage();
                $this->error("  Error: " . $e->getMessage());
                $this->newLine();
            }
        }

        // Display reminder breakdown
        $this->table(
            ['Escalation Level', 'Reminders Sent'],
            collect($results['by_level'])->map(function ($count, $level) {
                return [$level, $count];
            })->toArray()
        );

        return $results;
    }

    /**
     * Send escalated reminder based on arrears level
     */
    protected function sendEscalatedReminder($member, $record, $arrearsSummary)
    {
        try {
            // Generate payment link for total arrears
            $paymentLink = $this->generateArrearsPaymentLink($member, $arrearsSummary);

            // Get control number from bills
            $bill = DB::table('bills')
                ->where('client_number', $member->client_number)
                ->where('status', '!=', 'PAID')
                ->whereRaw("service_id IN (SELECT id FROM services WHERE code = 'SAV')")
                ->orderBy('created_at', 'desc')
                ->first();

            $controlNumber = $bill ? $bill->control_number : 'N/A';

            // Determine message content based on escalation level
            $messageContent = $this->buildEscalatedMessage($member, $record, $arrearsSummary, $controlNumber, $paymentLink);

            // Send notifications
            $emailSent = false;
            $smsSent = false;

            // Send Email
            if ($member->email) {
                try {
                    \Mail::raw($messageContent['email'], function($message) use ($member, $messageContent) {
                        $message->to($member->email)
                                ->subject($messageContent['subject']);
                    });
                    $emailSent = true;
                } catch (\Exception $e) {
                    Log::error('Arrears reminder email failed', [
                        'member' => $member->client_number,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Send SMS
            $phoneNumber = $member->mobile_phone ?? $member->contact_number;
            if ($phoneNumber) {
                try {
                    $smsService = new \App\Services\SmsService();
                    $smsResult = $smsService->sendSms($phoneNumber, $messageContent['sms']);
                    $smsSent = $smsResult['success'] ?? false;
                } catch (\Exception $e) {
                    Log::error('Arrears reminder SMS failed', [
                        'member' => $member->client_number,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Log the notification
            DB::table('mandatory_savings_notifications')->insert([
                'client_number' => $member->client_number,
                'account_number' => $record->account_number,
                'year' => $record->year,
                'month' => $record->month,
                'notification_type' => 'ARREARS_' . $record->arrears_status,
                'notification_method' => 'BOTH',
                'message' => $messageContent['sms'],
                'status' => ($emailSent || $smsSent) ? 'SENT' : 'FAILED',
                'scheduled_at' => now(),
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info('Escalated arrears reminder sent', [
                'client_number' => $member->client_number,
                'arrears_status' => $record->arrears_status,
                'total_arrears' => $arrearsSummary['total_arrears'],
                'months_in_arrears' => $arrearsSummary['months_in_arrears'],
                'email_sent' => $emailSent,
                'sms_sent' => $smsSent,
                'payment_link' => $paymentLink
            ]);

            return [
                'success' => $emailSent || $smsSent,
                'email_sent' => $emailSent,
                'sms_sent' => $smsSent
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send escalated reminder', [
                'member' => $member->client_number,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build friendly reminder message content based on arrears level
     * Note: These are member contributions/savings - tone is always friendly and supportive
     */
    protected function buildEscalatedMessage($member, $record, $arrearsSummary, $controlNumber, $paymentLink)
    {
        $memberName = trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name);
        $totalArrears = number_format($arrearsSummary['total_arrears'], 2);
        $monthsInArrears = $arrearsSummary['months_in_arrears'];
        $currentPeriod = $record->period;

        // All reminders are friendly - just different reminder numbers
        $reminderNumber = min($monthsInArrears, 4);
        $subject = "Friendly Reminder #{$reminderNumber} - Mandatory Savings Contribution";

        // Build email body - always friendly and supportive
        $emailBody = "Dear {$memberName},\n\n";
        $emailBody .= "We hope this message finds you well.\n\n";
        $emailBody .= "This is a friendly reminder about your mandatory savings contributions. ";
        $emailBody .= "Your savings help secure your financial future and strengthen our SACCO community.\n\n";

        $emailBody .= "CONTRIBUTION SUMMARY:\n";
        $emailBody .= "=====================\n";
        $emailBody .= "- Outstanding Amount: TZS {$totalArrears}\n";
        $emailBody .= "- Periods Pending: {$monthsInArrears} month(s)\n";
        if ($arrearsSummary['oldest_unpaid_period']) {
            $emailBody .= "- From: {$arrearsSummary['oldest_unpaid_period']}\n";
        }
        $emailBody .= "\n";

        $emailBody .= "EASY PAYMENT OPTIONS:\n";
        $emailBody .= "=====================\n";
        $emailBody .= "Control Number: {$controlNumber}\n\n";

        if ($paymentLink) {
            $emailBody .= "PAY ONLINE (Quick & Easy):\n";
            $emailBody .= "{$paymentLink}\n\n";
        }

        $emailBody .= "WHY YOUR CONTRIBUTION MATTERS:\n";
        $emailBody .= "==============================\n";
        $emailBody .= "- Builds your personal savings for the future\n";
        $emailBody .= "- Increases your loan eligibility and limits\n";
        $emailBody .= "- Earns you dividends at year-end\n";
        $emailBody .= "- Supports fellow SACCO members\n\n";

        if ($monthsInArrears >= 3) {
            $emailBody .= "We noticed your contributions have been pending for a while. ";
            $emailBody .= "If you're facing any challenges, please don't hesitate to reach out - ";
            $emailBody .= "we're here to help and can discuss flexible payment arrangements.\n\n";
        }

        $emailBody .= "If you have already made this payment, please disregard this reminder.\n\n";
        $emailBody .= "Need assistance? We're here to help:\n";
        $emailBody .= "- Phone: +255 22 219 7000\n";
        $emailBody .= "- Email: support@nbcsaccos.co.tz\n";
        $emailBody .= "- Visit any NBC branch\n\n";
        $emailBody .= "Thank you for being a valued member of NBC SACCO!\n\n";
        $emailBody .= "Warm regards,\n";
        $emailBody .= "NBC SACCO Member Services Team";

        // Build SMS message (multi-part supported) - friendly tone
        $smsBody = "NBC SACCO\n";
        $smsBody .= "Dear {$member->first_name},\n\n";
        $smsBody .= "Friendly Reminder #{$reminderNumber}\n";
        $smsBody .= "Your savings contribution of TZS {$totalArrears} ({$monthsInArrears} month(s)) is pending.\n\n";
        $smsBody .= "Control#: {$controlNumber}\n";

        if ($paymentLink) {
            $smsBody .= "\nPay easily online:\n{$paymentLink}\n";
        }

        $smsBody .= "\nYour savings build your future! Thank you for being a valued member.";

        return [
            'subject' => $subject,
            'email' => $emailBody,
            'sms' => $smsBody,
            'urgency' => "REMINDER #{$reminderNumber}"
        ];
    }

    /**
     * Generate payment link for total arrears
     */
    protected function generateArrearsPaymentLink($member, $arrearsSummary)
    {
        try {
            $memberName = trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name);
            $phoneNumber = $member->mobile_phone ?? $member->contact_number ?? '';
            $email = $member->email ?? '';

            // Build items for each unpaid period
            $items = [];
            foreach ($arrearsSummary['records'] as $record) {
                $items[] = [
                    'type' => 'service',
                    'product_service_reference' => 'SAV_' . $member->client_number . '_' . $record->year . str_pad($record->month, 2, '0', STR_PAD_LEFT),
                    'product_service_name' => "Mandatory Savings - {$record->period}",
                    'amount' => round($record->balance, 2),
                    'is_required' => true,
                    'allow_partial' => true
                ];
            }

            if (empty($items)) {
                return null;
            }

            $data = [
                'description' => "Mandatory Savings Arrears - {$arrearsSummary['months_in_arrears']} months",
                'target' => 'individual',
                'customer_reference' => $member->client_number,
                'customer_name' => $memberName,
                'customer_phone' => $phoneNumber,
                'customer_email' => $email,
                'expires_at' => Carbon::now()->addDays(30)->toIso8601String(),
                'items' => $items
            ];

            $response = $this->paymentLinkService->generateUniversalPaymentLink($data);

            return $response['data']['payment_url'] ?? null;

        } catch (\Exception $e) {
            Log::error('Failed to generate arrears payment link', [
                'member' => $member->client_number,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Generate arrears report
     */
    protected function generateArrearsReport()
    {
        $this->info("Mandatory Savings Arrears Report");
        $this->info("================================");
        $this->newLine();

        // Get summary by status
        $statusSummary = MandatorySavingsTracking::whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE', 'DEFAULTED'])
            ->where('balance', '>', 0)
            ->select('arrears_status', DB::raw('COUNT(*) as count'), DB::raw('SUM(balance) as total'))
            ->groupBy('arrears_status')
            ->get();

        $this->info("Arrears by Status:");
        $this->table(
            ['Status', 'Count', 'Total Amount (TZS)'],
            $statusSummary->map(function ($item) {
                return [
                    $item->arrears_status ?? 'N/A',
                    $item->count,
                    number_format($item->total, 2)
                ];
            })->toArray()
        );

        $this->newLine();

        // Get defaulters list
        $defaulters = MandatorySavingsTracking::where('is_defaulter', true)
            ->orWhere('arrears_status', MandatorySavingsTracking::ARREARS_DEFAULTER)
            ->with('client')
            ->get()
            ->groupBy('client_number');

        $this->info("Defaulters (3+ months in arrears): " . $defaulters->count());
        $this->newLine();

        if ($defaulters->count() > 0) {
            $defaulterData = [];
            foreach ($defaulters as $clientNumber => $records) {
                $summary = MandatorySavingsTracking::getMemberArrearsSummary($clientNumber);
                $client = $records->first()->client;
                $defaulterData[] = [
                    $clientNumber,
                    $client ? "{$client->first_name} {$client->last_name}" : 'Unknown',
                    $summary['months_in_arrears'],
                    number_format($summary['total_arrears'], 2),
                    $summary['oldest_unpaid_period'] ?? 'N/A'
                ];
            }

            $this->table(
                ['Client #', 'Name', 'Months', 'Total Arrears (TZS)', 'Oldest Period'],
                $defaulterData
            );
        }

        // Grand totals
        $this->newLine();
        $grandTotal = MandatorySavingsTracking::whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE', 'DEFAULTED'])
            ->where('balance', '>', 0)
            ->sum('balance');

        $totalMembers = MandatorySavingsTracking::whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE', 'DEFAULTED'])
            ->where('balance', '>', 0)
            ->distinct('client_number')
            ->count('client_number');

        $this->info("Grand Total Arrears: TZS " . number_format($grandTotal, 2));
        $this->info("Total Members in Arrears: {$totalMembers}");
        $this->info("Total Defaulters: " . $defaulters->count());
    }
}
