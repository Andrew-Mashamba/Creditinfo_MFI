<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\User;
use App\Mail\FailedTransactionReport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ReportFailedTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:report-failed
                            {--dry-run : Run without sending emails or updating records}
                            {--limit=100 : Maximum number of transactions to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report failed transactions that have not been reported. Sends email to tech_issues and the initiating user (if not system-initiated).';

    /**
     * The tech issues email address
     */
    protected $techEmail;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $startTime = microtime(true);
        $this->info('===========================================');
        $this->info('  Failed Transaction Reporter');
        $this->info('  Started: ' . now()->format('Y-m-d H:i:s'));
        $this->info('===========================================');

        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No emails will be sent and no records will be updated');
        }

        // Get tech issues email from .env
        $this->techEmail = config('mail.tech_issues_email', env('TECH_ISSUES_EMAIL', 'andrew.mashamba@nbc.co.tz'));

        // Fetch failed transactions that haven't been reported
        $failedTransactions = Transaction::failedUnreported()
            ->with('initiatedByUser')
            ->orderBy('failed_at', 'asc')
            ->limit($limit)
            ->get();

        if ($failedTransactions->isEmpty()) {
            $this->info('No unreported failed transactions found.');
            $this->logSummary(0, 0, 0, $startTime);
            return Command::SUCCESS;
        }

        $this->info("Found {$failedTransactions->count()} unreported failed transaction(s)");
        $this->newLine();

        // Track statistics
        $techEmailsSent = 0;
        $userEmailsSent = 0;
        $transactionsReported = 0;
        $errors = [];

        // Group transactions by initiating user
        $systemTransactions = collect();
        $userTransactions = collect();

        foreach ($failedTransactions as $transaction) {
            // Check if system-initiated (no user or initiated_by is null/system)
            if (empty($transaction->initiated_by) || $transaction->is_system_generated) {
                $systemTransactions->push($transaction);
            } else {
                // Group by user
                $userId = $transaction->initiated_by;
                if (!$userTransactions->has($userId)) {
                    $userTransactions->put($userId, collect());
                }
                $userTransactions->get($userId)->push($transaction);
            }
        }

        // 1. Send consolidated tech email with ALL failed transactions
        $this->info('Sending consolidated report to tech team...');
        try {
            if (!$dryRun) {
                Mail::to($this->techEmail)->send(new FailedTransactionReport($failedTransactions, 'tech'));
            }
            $techEmailsSent++;
            $this->info("  -> Sent to: {$this->techEmail} ({$failedTransactions->count()} transactions)");
        } catch (\Exception $e) {
            $errors[] = "Tech email failed: " . $e->getMessage();
            $this->error("  -> Failed to send to tech: " . $e->getMessage());
            Log::error('Failed to send tech email for failed transactions', [
                'error' => $e->getMessage(),
                'transaction_count' => $failedTransactions->count(),
            ]);
        }

        // 2. Send individual user emails (only for user-initiated transactions)
        $this->newLine();
        $this->info('Sending user notifications...');

        foreach ($userTransactions as $userId => $transactions) {
            $user = User::find($userId);

            if (!$user || empty($user->email)) {
                $this->warn("  -> User ID {$userId}: No email found, skipping user notification");
                continue;
            }

            try {
                if (!$dryRun) {
                    Mail::to($user->email)->send(new FailedTransactionReport($transactions, 'user', $user->name));
                }
                $userEmailsSent++;
                $this->info("  -> Sent to: {$user->email} ({$transactions->count()} transactions)");
            } catch (\Exception $e) {
                $errors[] = "User email to {$user->email} failed: " . $e->getMessage();
                $this->error("  -> Failed to send to {$user->email}: " . $e->getMessage());
                Log::error('Failed to send user email for failed transactions', [
                    'user_id' => $userId,
                    'user_email' => $user->email,
                    'error' => $e->getMessage(),
                    'transaction_count' => $transactions->count(),
                ]);
            }
        }

        // Log info about system transactions
        if ($systemTransactions->isNotEmpty()) {
            $this->info("  -> {$systemTransactions->count()} system-initiated transaction(s) - tech team only");
        }

        // 3. Mark transactions as reported
        $this->newLine();
        $this->info('Marking transactions as reported...');

        if (!$dryRun) {
            $reportedAt = now();
            $transactionsReported = Transaction::whereIn('id', $failedTransactions->pluck('id'))
                ->update(['failure_reported_at' => $reportedAt]);
            $this->info("  -> Marked {$transactionsReported} transaction(s) as reported");
        } else {
            $transactionsReported = $failedTransactions->count();
            $this->info("  -> Would mark {$transactionsReported} transaction(s) as reported (dry run)");
        }

        // Display summary
        $this->logSummary($techEmailsSent, $userEmailsSent, $transactionsReported, $startTime, $errors);

        // Log to system
        Log::info('Failed transactions report completed', [
            'transactions_found' => $failedTransactions->count(),
            'transactions_reported' => $transactionsReported,
            'tech_emails_sent' => $techEmailsSent,
            'user_emails_sent' => $userEmailsSent,
            'errors' => count($errors),
            'dry_run' => $dryRun,
        ]);

        return count($errors) > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Log summary of the operation
     */
    protected function logSummary(int $techEmailsSent, int $userEmailsSent, int $transactionsReported, float $startTime, array $errors = [])
    {
        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info('===========================================');
        $this->info('  Summary');
        $this->info('===========================================');
        $this->info("  Transactions Reported: {$transactionsReported}");
        $this->info("  Tech Emails Sent:      {$techEmailsSent}");
        $this->info("  User Emails Sent:      {$userEmailsSent}");
        $this->info("  Errors:                " . count($errors));
        $this->info("  Duration:              {$duration}s");
        $this->info('===========================================');
        $this->info('  Completed: ' . now()->format('Y-m-d H:i:s'));
        $this->info('===========================================');

        if (!empty($errors)) {
            $this->newLine();
            $this->error('Errors encountered:');
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
        }
    }
}
