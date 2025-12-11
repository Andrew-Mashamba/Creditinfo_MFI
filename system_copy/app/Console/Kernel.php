<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ===========================================
        // SCHEDULED REPORTS - DAILY
        // ===========================================
        // Daily reports generated at 6:30 AM before business hours
        // Includes: Operations, Arrears, Cash Position, Disbursements, Collections, etc.
        $schedule->command('reports:scheduled-generation --frequency=daily')
                ->dailyAt('06:30')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/scheduled-reports-daily.log'))
                ->onSuccess(function () {
                    \Log::info('Daily scheduled reports generated successfully');
                })
                ->onFailure(function () {
                    \Log::error('Daily scheduled reports generation failed');
                });

        // ===========================================
        // SCHEDULED REPORTS - WEEKLY
        // ===========================================
        // Weekly reports generated every Monday at 7:00 AM
        // Includes: Executive Summary, Arrears Analysis, Credit Committee, Collections Performance, etc.
        $schedule->command('reports:scheduled-generation --frequency=weekly')
                ->weeklyOn(1, '07:00') // Monday at 7:00 AM
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/scheduled-reports-weekly.log'))
                ->onSuccess(function () {
                    \Log::info('Weekly scheduled reports generated successfully');
                })
                ->onFailure(function () {
                    \Log::error('Weekly scheduled reports generation failed');
                });

        // ===========================================
        // SCHEDULED REPORTS - MONTHLY
        // ===========================================
        // Monthly reports generated on the 1st of each month at 8:00 AM
        // Includes: Loan Portfolio, Delinquency, NPL, Provision, Finance Reports, Membership, Risk, etc.
        $schedule->command('reports:scheduled-generation --frequency=monthly')
                ->monthlyOn(1, '08:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/scheduled-reports-monthly.log'))
                ->onSuccess(function () {
                    \Log::info('Monthly scheduled reports generated successfully');
                })
                ->onFailure(function () {
                    \Log::error('Monthly scheduled reports generation failed');
                });

        // ===========================================
        // SCHEDULED REPORTS - QUARTERLY
        // ===========================================
        // Quarterly reports generated on the 5th of each quarter's first month at 9:00 AM
        // (January 5, April 5, July 5, October 5)
        // Includes: BOT Regulatory, Capital Adequacy, Large Exposures, TCDC Reports, Board Pack, etc.
        $schedule->command('reports:scheduled-generation --frequency=quarterly')
                ->quarterlyOn(5, '09:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/scheduled-reports-quarterly.log'))
                ->onSuccess(function () {
                    \Log::info('Quarterly scheduled reports generated successfully');
                })
                ->onFailure(function () {
                    \Log::error('Quarterly scheduled reports generation failed');
                });

        // ===========================================
        // SCHEDULED REPORTS - ANNUAL
        // ===========================================
        // Annual reports generated on January 15th at 10:00 AM
        // Allows time for year-end closing before generating annual reports
        // Includes: Annual Financial Statements, AGM Pack, BOT Return, TCDC Return
        $schedule->command('reports:scheduled-generation --frequency=annual')
                ->yearlyOn(1, 15, '10:00') // January 15th at 10:00 AM
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/scheduled-reports-annual.log'))
                ->onSuccess(function () {
                    \Log::info('Annual scheduled reports generated successfully');
                })
                ->onFailure(function () {
                    \Log::error('Annual scheduled reports generation failed');
                });

        // Daily cleanup of old report files (older than 30 days)
        $schedule->command('reports:cleanup-old-files')
                ->dailyAt('02:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/reports-cleanup.log'));
        
        // Monthly Loan Loss Provision Cycle - Run on the 5th of each month at 9:00 AM
        // CALCULATE and COMPARE only - Manual ADJUSTMENT required via UI
        $schedule->command('provision:cycle MONTHLY')
                ->monthlyOn(5, '09:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/provision-cycle.log'))
                ->emailOutputTo('andrew.s.mashamba@gmail.com') // Always send report
                ->onSuccess(function () {
                    \Log::info('Monthly provision cycle calculated - awaiting manual adjustment approval');
                })
                ->onFailure(function () {
                    \Log::error('Monthly provision cycle calculation failed');
                });
        
        // Monthly budget close - Run on the last day of each month at 11:30 PM
        $schedule->command('budget:period-close --type=monthly')
                ->monthlyOn(date('t'), '23:30')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/budget-monthly-close.log'));
        
        // Quarterly Loan Loss Provision Cycle - Run on the 5th day of each quarter at 9:30 AM
        // (Jan 5, Apr 5, Jul 5, Oct 5)
        $schedule->command('provision:cycle QUARTERLY')
                ->quarterly()
                ->at('09:30')
                ->when(function () {
                    // Only run if not already run this month (avoid duplicate with monthly)
                    $lastCycle = \DB::table('provision_cycles')
                        ->where('frequency', 'QUARTERLY')
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->first();
                    return !$lastCycle;
                })
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/provision-cycle-quarterly.log'));
        
        // Quarterly budget close - Run on the last day of each quarter at 11:45 PM
        $schedule->command('budget:period-close --type=quarterly')
                ->quarterly()
                ->at('23:45')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/budget-quarterly-close.log'));
        
        // Daily system activities at the start of each day - includes budget monitoring
        $schedule->command('system:daily-activities')
                ->dailyAt('00:05')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/daily-activities.log'));

        // NBC Daily Bank Reconciliation - Fetch NBC statement and reconcile with cashbook
        $schedule->command('nbc:daily-reconciliation')
                ->dailyAt('01:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/nbc-daily-reconciliation.log'))
                ->onSuccess(function () {
                    \Log::info('NBC Daily Reconciliation completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('NBC Daily Reconciliation failed - check logs for details');
                });
        
        // Loan Repayment Processing - Daily at 6:00 AM
        // Sends repayment reminders, processes arrears, calculates penalties, generates legal documents
        $schedule->command('loans:process-repayments')
                ->dailyAt('06:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/loan-arrears-management.log'))
                ->onSuccess(function () {
                    \Log::info('Loan Repayment Processing completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Loan Repayment Processing failed - check logs for details');
                });

        // Loan NPL Classification - Daily at 00:30 AM
        // Classifies loans based on days in arrears (CURRENT, WATCH, SUBSTANDARD, DOUBTFUL, LOSS)
        // and calculates loan loss provisions
        $schedule->command('loans:classify-npl')
                ->dailyAt('00:30')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/loan-npl-classification.log'))
                ->onSuccess(function () {
                    \Log::info('Loan NPL Classification completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Loan NPL Classification failed - check logs for details');
                });

        // Monthly system activities on the last day of each month
        $schedule->command('sacco:run-monthly-activities')
                ->monthlyOn(date('t'), '23:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/monthly-activities.log'));
        
        // Quarterly system activities
        $schedule->command('sacco:run-quarterly-activities')
                ->quarterly()
                ->at('23:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/quarterly-activities.log'));
        
        // Execute standing instructions - Run daily at 6:00 AM
        $schedule->command('standing-instructions:execute')
                ->dailyAt('06:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/standing-instructions.log'));
        
        // Execute standing instructions - Additional run at 2:00 PM for same-day instructions
        $schedule->command('standing-instructions:execute')
                ->dailyAt('14:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/standing-instructions.log'));

        // Report failed transactions - Run every 10 minutes
        // Sends email alerts for unreported failed transactions to tech team and initiating users
        $schedule->command('payments:report-failed')
                ->everyTenMinutes()
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/failed-transactions-report.log'))
                ->onSuccess(function () {
                    \Log::info('Failed transactions report completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Failed transactions report encountered errors');
                });

        // Approval SLA Check - Run every 30 minutes
        // Monitors pending approvals for SLA breaches and sends notifications
        $schedule->command('approvals:check-sla')
                ->everyThirtyMinutes()
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/approval-sla-check.log'))
                ->onSuccess(function () {
                    \Log::info('Approval SLA check completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Approval SLA check encountered errors');
                });

        // Daily Savings & Deposits Interest Accrual - Run at 00:15 AM
        // Calculates and accrues daily interest on all active savings (2000) and deposit (3000) accounts
        // Uses Actual/365 method with rates from sub_products.interest field
        // Posts separate GL entries: INTEREST ON SAVINGS (0101500050005030) and INTEREST ON DEPOSITS (0101500050005025)
        $schedule->command('savings:accrue-interest')
                ->dailyAt('00:15')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/savings-interest-accrual.log'))
                ->onSuccess(function () {
                    \Log::info('Daily savings interest accrual completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Daily savings interest accrual failed - check logs for details');
                });

        // Daily Share Dividend Accrual - Run at 00:20 AM
        // Calculates and accrues daily dividends on all active share capital accounts
        // Uses Actual/365 method with rate from institutions.share_dividend_rate
        // GL Entry: DR Retained Earnings (0101300031003110), CR Dividends Payable (0101300036003640)
        $schedule->command('shares:accrue-dividends')
                ->dailyAt('00:20')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/share-dividend-accrual.log'))
                ->onSuccess(function () {
                    \Log::info('Daily share dividend accrual completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Daily share dividend accrual failed - check logs for details');
                });

        // Weekly Member Status Update - Run every Sunday at 02:00 AM
        // Marks members as DORMANT if all their accounts have no activity for 6+ months
        // Checks accounts.updated_at for each member's accounts
        $schedule->command('members:update-status')
                ->weeklyOn(0, '02:00') // Sunday at 2:00 AM
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/member-status-update.log'))
                ->onSuccess(function () {
                    \Log::info('Weekly member status update completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Weekly member status update failed - check logs for details');
                });

        // Billing Payment Due Alerts - Run daily at 08:00 AM
        // Sends SMS reminders for:
        // - Bills due within 3 days (upcoming)
        // - Overdue bills
        // - Trade receivables due within 3 days
        // - Overdue trade receivables
        // Respects cooldown period (24h) and max reminders (5) per bill
        $schedule->command('billing:payment-due-alerts')
                ->dailyAt('08:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/billing-payment-due-alerts.log'))
                ->onSuccess(function () {
                    \Log::info('Billing payment due alerts completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Billing payment due alerts failed - check logs for details');
                });

        // Monthly Mandatory Savings Bill Generation - Run on the 21st of each month at 07:00 AM
        // This timing aligns with salary payment period (21st-31st) so members can pay promptly
        // Generates mandatory savings tracking records and bills (TZS 60,000) for all active members
        // Creates control numbers, service bills, and sends payment notifications
        // Due date is set to end of month with reminders sent every 5 days until paid
        $schedule->command('mandatory-savings:process --action=all')
                ->monthlyOn(21, '07:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/mandatory-savings-process.log'))
                ->onSuccess(function () {
                    \Log::info('Monthly mandatory savings bill generation completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Monthly mandatory savings bill generation failed - check logs for details');
                });

        // Mandatory Savings Reminders - Run daily at 08:00 AM
        // Sends payment reminders to members with unpaid mandatory savings every 5 days
        // Continues sending reminders until the payment is complete
        // Urgency level increases with each reminder (REMINDER → IMPORTANT → URGENT → FINAL NOTICE)
        $schedule->command('mandatory-savings:reminders --interval=5')
                ->dailyAt('08:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/mandatory-savings-reminders.log'))
                ->onSuccess(function () {
                    \Log::info('Mandatory savings reminders sent successfully');
                })
                ->onFailure(function () {
                    \Log::error('Mandatory savings reminders failed - check logs for details');
                });

        // Mandatory Savings Arrears Management - Run daily at 09:00 AM
        // Updates arrears status for all unpaid records (1M, 2M, 3M, DEFAULTER)
        // Sends escalated reminders based on arrears level
        // Follows up non-paying members for at least 3 months before marking as defaulter
        // Escalation: ARREARS_1M → ARREARS_2M → ARREARS_3M → DEFAULTER
        $schedule->command('mandatory-savings:arrears --action=all --interval=5')
                ->dailyAt('09:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/mandatory-savings-arrears.log'))
                ->onSuccess(function () {
                    \Log::info('Mandatory savings arrears processing completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Mandatory savings arrears processing failed - check logs for details');
                });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
