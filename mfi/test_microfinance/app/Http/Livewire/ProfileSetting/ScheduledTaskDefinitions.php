<?php

namespace App\Http\Livewire\ProfileSetting;

/**
 * Comprehensive Scheduled Task Definitions
 *
 * This file contains ALL scheduled activities identified in the
 * COMPREHENSIVE_SCHEDULED_ACTIVITIES.md document for the MFI Management System
 * banking system across 26 components.
 *
 * Document Reference: /docs/COMPREHENSIVE_SCHEDULED_ACTIVITIES.md
 * Generated: 2025-11-28
 */
class ScheduledTaskDefinitions
{
    /**
     * Get all scheduled task definitions
     */
    public static function getAllTasks(): array
    {
        return array_merge(
            self::getRealTimeTasks(),
            self::getHourlyTasks(),
            self::getDailyTasks(),
            self::getWeeklyTasks(),
            self::getMonthlyTasks(),
            self::getQuarterlyTasks(),
            self::getSemiAnnualTasks(),
            self::getAnnualTasks()
        );
    }

    /**
     * Real-Time/Minute Activities (Every 1-5 Minutes)
     * From: Real-Time/Minute Activities section
     */
    public static function getRealTimeTasks(): array
    {
        return [
            // === Transaction Processing (Every 1-5 Minutes) ===
            ['id' => 'payment-status-monitor', 'command' => 'payments:monitor-status', 'schedule' => 'Every 2 minutes', 'cron' => '*/2 * * * *', 'description' => 'Poll external APIs for pending payment status updates', 'category' => 'Payments'],
            ['id' => 'payment-retry-failed', 'command' => 'payments:retry-failed', 'schedule' => 'Every 5 minutes', 'cron' => '*/5 * * * *', 'description' => 'Process retry queue with exponential backoff', 'category' => 'Payments'],
            ['id' => 'transaction-validation', 'command' => 'transactions:validate-realtime', 'schedule' => 'Every 1 minute', 'cron' => '* * * * *', 'description' => 'Real-time fraud detection and validation', 'category' => 'Transactions'],
            ['id' => 'security-rate-limit', 'command' => 'security:rate-limit-monitor', 'schedule' => 'Every 1 minute', 'cron' => '* * * * *', 'description' => 'Monitor API rate limits, block violations', 'category' => 'Security'],
            ['id' => 'payment-circuit-breaker', 'command' => 'payments:circuit-breaker-check', 'schedule' => 'Every 1 minute', 'cron' => '* * * * *', 'description' => 'Monitor external service health', 'category' => 'Payments'],

            // === Security & Monitoring (Real-Time) ===
            ['id' => 'fraud-detection', 'command' => 'transactions:fraud-detection', 'schedule' => 'Every 1 minute', 'cron' => '* * * * *', 'description' => 'ML-based anomaly detection on transactions', 'category' => 'Security'],
            ['id' => 'security-alerts', 'command' => 'security:monitor-alerts', 'schedule' => 'Every 1 minute', 'cron' => '* * * * *', 'description' => 'Failed login attempts, unauthorized access', 'category' => 'Security'],
            ['id' => 'otp-delivery', 'command' => 'notifications:process-otp', 'schedule' => 'Every 1 minute', 'cron' => '* * * * *', 'description' => 'One-time password delivery', 'category' => 'Notifications'],
            ['id' => 'transaction-alerts', 'command' => 'notifications:transaction-alerts', 'schedule' => 'Every 1 minute', 'cron' => '* * * * *', 'description' => 'Large/unusual transaction alerts', 'category' => 'Notifications'],

            // === Component 12: Transactions - Real-time ===
            ['id' => 'transactions-realtime-validation', 'command' => 'transactions:realtime-validation', 'schedule' => 'Every 1 minute', 'cron' => '* * * * *', 'description' => 'Real-time transaction validation', 'category' => 'Transactions'],

            // === Component 20: Payments - Minutes ===
            ['id' => 'payments-status-monitoring', 'command' => 'payments:status-monitoring', 'schedule' => 'Every 2 minutes', 'cron' => '*/2 * * * *', 'description' => 'Payment status monitoring', 'category' => 'Payments'],
            ['id' => 'payments-retry-processing', 'command' => 'payments:retry-processing', 'schedule' => 'Every 5 minutes', 'cron' => '*/5 * * * *', 'description' => 'Payment retry processing', 'category' => 'Payments'],

            // === Component 26: Email/Notifications - Real-time ===
            ['id' => 'notifications-transaction-realtime', 'command' => 'notifications:realtime-transaction', 'schedule' => 'Every 1 minute', 'cron' => '* * * * *', 'description' => 'Real-time transaction alerts', 'category' => 'Notifications'],
            ['id' => 'notifications-security-realtime', 'command' => 'notifications:realtime-security', 'schedule' => 'Every 1 minute', 'cron' => '* * * * *', 'description' => 'Real-time security alerts', 'category' => 'Notifications'],

            // === Queue Processing ===
            ['id' => 'queue-high-priority', 'command' => 'queue:work --queue=high --stop-when-empty', 'schedule' => 'Every 1 minute', 'cron' => '* * * * *', 'description' => 'Process high priority queue', 'category' => 'System'],
            ['id' => 'queue-default', 'command' => 'queue:work --queue=default --stop-when-empty', 'schedule' => 'Every 1 minute', 'cron' => '* * * * *', 'description' => 'Process default queue', 'category' => 'System'],
        ];
    }

    /**
     * Hourly Activities
     * From: Hourly Activities section
     */
    public static function getHourlyTasks(): array
    {
        return [
            // === Financial Operations ===
            ['id' => 'cash-position-monitor', 'command' => 'cash:monitor-position', 'schedule' => 'Every hour at :00', 'cron' => '0 * * * *', 'description' => 'Monitor vault/till balances', 'category' => 'Cash'],
            ['id' => 'teller-limit-alerts', 'command' => 'teller:check-limits', 'schedule' => 'Every hour at :00', 'cron' => '0 * * * *', 'description' => 'Check transaction limit breaches', 'category' => 'Teller'],
            ['id' => 'vault-capacity-check', 'command' => 'cash:vault-capacity', 'schedule' => 'Every hour at :00', 'cron' => '0 * * * *', 'description' => 'Monitor vault thresholds', 'category' => 'Cash'],
            ['id' => 'payment-queue-process', 'command' => 'payments:process-queue', 'schedule' => 'Every hour at :00', 'cron' => '0 * * * *', 'description' => 'Process pending payment batches', 'category' => 'Payments'],
            ['id' => 'fsp-health-monitor', 'command' => 'payments:fsp-health', 'schedule' => 'Every hour at :00', 'cron' => '0 * * * *', 'description' => 'Check payment gateway status', 'category' => 'Payments'],

            // === Reporting & Notifications ===
            ['id' => 'reports-generate-scheduled', 'command' => 'reports:generate-scheduled', 'schedule' => 'Every hour at :00', 'cron' => '0 * * * *', 'description' => 'Generate hourly scheduled reports', 'category' => 'Reports'],
            ['id' => 'email-process-scheduled', 'command' => 'email:process-scheduled', 'schedule' => 'Every hour at :00', 'cron' => '0 * * * *', 'description' => 'Process scheduled email queue', 'category' => 'Notifications'],
            ['id' => 'dashboard-cache-refresh', 'command' => 'dashboard:refresh-cache', 'schedule' => 'Every hour at :30', 'cron' => '30 * * * *', 'description' => 'Update dashboard metrics cache', 'category' => 'Dashboard'],
            ['id' => 'approval-sla-tracking', 'command' => 'approvals:check-sla', 'schedule' => 'Every hour at :00', 'cron' => '0 * * * *', 'description' => 'Check SLA breaches', 'category' => 'Approvals'],

            // === Component 1: Dashboard - Hourly ===
            ['id' => 'dashboard-metric-aggregation', 'command' => 'dashboard:aggregate-metrics', 'schedule' => 'Every hour at :00', 'cron' => '0 * * * *', 'description' => 'Metric aggregation', 'category' => 'Dashboard'],
            ['id' => 'dashboard-chart-refresh', 'command' => 'dashboard:refresh-charts', 'schedule' => 'Every hour at :30', 'cron' => '30 * * * *', 'description' => 'Chart data refresh', 'category' => 'Dashboard'],

            // === Component 13: Teller Management - Hourly ===
            ['id' => 'teller-cash-position', 'command' => 'teller:cash-position', 'schedule' => 'Every hour at :00', 'cron' => '0 * * * *', 'description' => 'Teller cash position monitoring', 'category' => 'Teller'],

            // === Component 14: Cash Management - Hourly ===
            ['id' => 'cash-liquidity-monitor', 'command' => 'cash:liquidity-monitor', 'schedule' => 'Every hour at :00', 'cron' => '0 * * * *', 'description' => 'Liquidity monitoring', 'category' => 'Cash'],

            // === Component 15: Approvals - Hourly ===
            ['id' => 'approvals-pending-alerts', 'command' => 'approvals:pending-alerts', 'schedule' => 'Every hour at :00', 'cron' => '0 * * * *', 'description' => 'Pending approval alerts', 'category' => 'Approvals'],

            // === Component 16: Reports - Hourly ===
            ['id' => 'reports-scheduled-generation', 'command' => 'reports:scheduled-generation', 'schedule' => 'Every hour at :00', 'cron' => '0 * * * *', 'description' => 'Scheduled report generation', 'category' => 'Reports'],

            // === Component 18: Self Services - Hourly ===
            ['id' => 'selfservice-availability', 'command' => 'selfservice:check-availability', 'schedule' => 'Every hour at :00', 'cron' => '0 * * * *', 'description' => 'Service availability monitoring', 'category' => 'System'],

            // === Component 20: Payments - Hourly ===
            ['id' => 'payments-hourly-reconciliation', 'command' => 'payments:hourly-reconciliation', 'schedule' => 'Every hour at :30', 'cron' => '30 * * * *', 'description' => 'Payment hourly reconciliation', 'category' => 'Payments'],
        ];
    }

    /**
     * Daily Activities
     * From: Daily Activities section + Component-Specific Daily tasks
     */
    public static function getDailyTasks(): array
    {
        return [
            // === Core Daily Schedule (00:05 - system:daily-activities) ===
            ['id' => 'system-daily-activities', 'command' => 'system:daily-activities', 'schedule' => '12:05 AM daily', 'cron' => '5 0 * * *', 'description' => 'Run daily system activities (interest accrual, arrears, NPL)', 'category' => 'System'],

            // === Loan Processing ===
            ['id' => 'loan-interest-accrual', 'command' => 'loans:accrue-interest', 'schedule' => '12:10 AM daily', 'cron' => '10 0 * * *', 'description' => 'Calculate daily interest on all active loans', 'category' => 'Loans'],
            ['id' => 'loan-repayment-process', 'command' => 'loans:process-repayments', 'schedule' => '12:15 AM daily', 'cron' => '15 0 * * *', 'description' => 'Process due repayments', 'category' => 'Loans'],
            ['id' => 'loan-arrears-calculate', 'command' => 'loans:calculate-arrears', 'schedule' => '12:20 AM daily', 'cron' => '20 0 * * *', 'description' => 'Update arrears status and aging', 'category' => 'Loans'],
            ['id' => 'loan-npl-classify', 'command' => 'loans:classify-npl', 'schedule' => '12:25 AM daily', 'cron' => '25 0 * * *', 'description' => 'Classify loans (WATCH, SUBSTANDARD, DOUBTFUL, LOSS)', 'category' => 'Loans'],
            ['id' => 'loan-provisions-daily', 'command' => 'loans:daily-provisions', 'schedule' => '12:30 AM daily', 'cron' => '30 0 * * *', 'description' => 'Update daily loan loss provisions', 'category' => 'Loans'],

            // === Savings & Deposits ===
            ['id' => 'savings-interest-accrual', 'command' => 'savings:accrue-interest', 'schedule' => '12:35 AM daily', 'cron' => '35 0 * * *', 'description' => 'Calculate daily savings interest', 'category' => 'Savings'],
            ['id' => 'deposit-maturity-process', 'command' => 'deposits:process-maturity', 'schedule' => '12:40 AM daily', 'cron' => '40 0 * * *', 'description' => 'Process maturing deposits', 'category' => 'Deposits'],
            ['id' => 'deposit-recurring-process', 'command' => 'deposits:process-recurring', 'schedule' => '12:45 AM daily', 'cron' => '45 0 * * *', 'description' => 'Process scheduled deposits', 'category' => 'Deposits'],
            ['id' => 'deposit-balance-update', 'command' => 'deposits:update-balances', 'schedule' => '12:50 AM daily', 'cron' => '50 0 * * *', 'description' => 'Update account balances', 'category' => 'Deposits'],

            // === Share Management ===
            ['id' => 'share-transaction-process', 'command' => 'shares:process-transactions', 'schedule' => '12:55 AM daily', 'cron' => '55 0 * * *', 'description' => 'Process share transactions', 'category' => 'Shares'],
            ['id' => 'share-balance-update', 'command' => 'shares:update-balances', 'schedule' => '1:00 AM daily', 'cron' => '0 1 * * *', 'description' => 'Update member share balances', 'category' => 'Shares'],
            ['id' => 'share-dividend-accrual', 'command' => 'shares:accrue-dividends', 'schedule' => '1:05 AM daily', 'cron' => '5 1 * * *', 'description' => 'Accrue daily dividends', 'category' => 'Shares'],

            // === Financial Reconciliation ===
            ['id' => 'nbc-daily-reconciliation', 'command' => 'nbc:daily-reconciliation', 'schedule' => '1:00 AM daily', 'cron' => '0 1 * * *', 'description' => 'Fetch and match bank statement', 'category' => 'Reconciliation'],
            ['id' => 'standing-instructions-am', 'command' => 'standing-instructions:execute', 'schedule' => '6:00 AM daily', 'cron' => '0 6 * * *', 'description' => 'Execute recurring transfers (morning)', 'category' => 'Transactions'],
            ['id' => 'standing-instructions-pm', 'command' => 'standing-instructions:execute', 'schedule' => '2:00 PM daily', 'cron' => '0 14 * * *', 'description' => 'Execute recurring transfers (afternoon)', 'category' => 'Transactions'],
            ['id' => 'gl-post-daily', 'command' => 'accounting:post-daily', 'schedule' => '1:15 AM daily', 'cron' => '15 1 * * *', 'description' => 'Post daily journal entries', 'category' => 'Accounting'],
            ['id' => 'trial-balance-daily', 'command' => 'accounting:trial-balance', 'schedule' => '1:20 AM daily', 'cron' => '20 1 * * *', 'description' => 'Generate daily trial balance', 'category' => 'Accounting'],

            // === Member Services ===
            ['id' => 'member-status-update', 'command' => 'members:update-status', 'schedule' => '1:25 AM daily', 'cron' => '25 1 * * *', 'description' => 'Update member activity status', 'category' => 'Members'],
            ['id' => 'member-kyc-alerts', 'command' => 'members:kyc-alerts', 'schedule' => '1:30 AM daily', 'cron' => '30 1 * * *', 'description' => 'Flag expiring documents', 'category' => 'Members'],
            ['id' => 'member-dormancy-check', 'command' => 'members:dormancy-check', 'schedule' => '1:35 AM daily', 'cron' => '35 1 * * *', 'description' => 'Identify inactive accounts', 'category' => 'Members'],
            ['id' => 'member-statement-generate', 'command' => 'members:generate-statements', 'schedule' => '1:40 AM daily', 'cron' => '40 1 * * *', 'description' => 'Prepare daily statements', 'category' => 'Members'],

            // === System Maintenance ===
            ['id' => 'backup-database', 'command' => 'backup:database', 'schedule' => '2:00 AM daily', 'cron' => '0 2 * * *', 'description' => 'Create daily backup', 'category' => 'Maintenance'],
            ['id' => 'reports-cleanup', 'command' => 'reports:cleanup-old-files', 'schedule' => '2:15 AM daily', 'cron' => '15 2 * * *', 'description' => 'Archive old report files', 'category' => 'Maintenance'],
            ['id' => 'logs-cleanup', 'command' => 'logs:cleanup', 'schedule' => '2:30 AM daily', 'cron' => '30 2 * * *', 'description' => 'Archive/clean old logs', 'category' => 'Maintenance'],
            ['id' => 'cache-clear-expired', 'command' => 'cache:clear-expired', 'schedule' => '2:45 AM daily', 'cron' => '45 2 * * *', 'description' => 'Clear expired cache', 'category' => 'Maintenance'],
            ['id' => 'temp-cleanup', 'command' => 'temp:cleanup', 'schedule' => '2:50 AM daily', 'cron' => '50 2 * * *', 'description' => 'Remove temporary files', 'category' => 'Maintenance'],

            // === Compliance & Reporting ===
            ['id' => 'audit-trail-generate', 'command' => 'audit:generate-trail', 'schedule' => '1:45 AM daily', 'cron' => '45 1 * * *', 'description' => 'Log daily activities', 'category' => 'Compliance'],
            ['id' => 'risk-assessment-update', 'command' => 'risk:update-assessment', 'schedule' => '1:50 AM daily', 'cron' => '50 1 * * *', 'description' => 'Update risk indicators', 'category' => 'Compliance'],
            ['id' => 'regulatory-reports-daily', 'command' => 'compliance:daily-reports', 'schedule' => '1:55 AM daily', 'cron' => '55 1 * * *', 'description' => 'Generate required reports', 'category' => 'Compliance'],

            // === Additional Daily Activities (from time-based schedule) ===
            ['id' => 'expense-accrual-process', 'command' => 'expenses:process-accruals', 'schedule' => '3:00 AM daily', 'cron' => '0 3 * * *', 'description' => 'Record daily accruals', 'category' => 'Expenses'],
            ['id' => 'budget-utilization-update', 'command' => 'budget:update-utilization', 'schedule' => '4:00 AM daily', 'cron' => '0 4 * * *', 'description' => 'Recalculate budget usage', 'category' => 'Budget'],
            ['id' => 'investment-value-update', 'command' => 'investment:update-values', 'schedule' => '5:00 AM daily', 'cron' => '0 5 * * *', 'description' => 'Update portfolio values', 'category' => 'Investment'],
            ['id' => 'billing-payment-alerts', 'command' => 'billing:payment-due-alerts', 'schedule' => '7:00 AM daily', 'cron' => '0 7 * * *', 'description' => 'Send payment reminders', 'category' => 'Billing'],
            ['id' => 'approval-reminders', 'command' => 'approvals:send-reminders', 'schedule' => '8:00 AM daily', 'cron' => '0 8 * * *', 'description' => 'Escalate pending approvals', 'category' => 'Approvals'],
            ['id' => 'teller-eod-review', 'command' => 'teller:eod-review', 'schedule' => '9:00 AM daily', 'cron' => '0 9 * * *', 'description' => 'Verify till closures', 'category' => 'Teller'],
            ['id' => 'insurance-expiry-alerts', 'command' => 'insurance:expiry-alerts', 'schedule' => '10:00 AM daily', 'cron' => '0 10 * * *', 'description' => 'Flag expiring policies', 'category' => 'Insurance'],
            ['id' => 'security-suspicious-check', 'command' => 'security:suspicious-activity', 'schedule' => '12:00 PM daily', 'cron' => '0 12 * * *', 'description' => 'Mid-day fraud review', 'category' => 'Security'],
            ['id' => 'reports-daily-summary', 'command' => 'reports:daily-summary', 'schedule' => '4:00 PM daily', 'cron' => '0 16 * * *', 'description' => 'Management summaries', 'category' => 'Reports'],

            // === Component 1: Dashboard - Daily ===
            ['id' => 'dashboard-kpi-update', 'command' => 'dashboard:update-kpis', 'schedule' => '6:00 AM daily', 'cron' => '0 6 * * *', 'description' => 'KPI cache updates', 'category' => 'Dashboard'],
            ['id' => 'dashboard-alert-generate', 'command' => 'dashboard:generate-alerts', 'schedule' => '7:00 AM daily', 'cron' => '0 7 * * *', 'description' => 'Alert generation', 'category' => 'Dashboard'],

            // === Component 3: Savings - Daily ===
            ['id' => 'savings-balance-update', 'command' => 'savings:update-balances', 'schedule' => '12:40 AM daily', 'cron' => '40 0 * * *', 'description' => 'Daily balance updates', 'category' => 'Savings'],

            // === Component 4: Deposits - Daily ===
            ['id' => 'deposits-interest-accrual', 'command' => 'deposits:accrue-interest', 'schedule' => '12:42 AM daily', 'cron' => '42 0 * * *', 'description' => 'Daily interest accrual', 'category' => 'Deposits'],
            ['id' => 'deposits-maturity-monitor', 'command' => 'deposits:monitor-maturity', 'schedule' => '12:44 AM daily', 'cron' => '44 0 * * *', 'description' => 'Maturity monitoring', 'category' => 'Deposits'],

            // === Component 6: Accounting - Daily ===
            ['id' => 'accounting-gl-posting', 'command' => 'accounting:gl-posting', 'schedule' => '1:10 AM daily', 'cron' => '10 1 * * *', 'description' => 'GL posting', 'category' => 'Accounting'],

            // === Component 7: Reconciliation - Daily ===
            ['id' => 'reconciliation-bank-fetch', 'command' => 'reconciliation:fetch-bank-statement', 'schedule' => '1:00 AM daily', 'cron' => '0 1 * * *', 'description' => 'Bank statement fetch', 'category' => 'Reconciliation'],
            ['id' => 'reconciliation-transaction-match', 'command' => 'reconciliation:match-transactions', 'schedule' => '1:30 AM daily', 'cron' => '30 1 * * *', 'description' => 'Transaction matching', 'category' => 'Reconciliation'],

            // === Component 8: HR/Payroll - Daily ===
            ['id' => 'hr-attendance-tracking', 'command' => 'hr:track-attendance', 'schedule' => '11:00 PM daily', 'cron' => '0 23 * * *', 'description' => 'Attendance tracking', 'category' => 'HR'],

            // === Component 9: Budget - Daily ===
            ['id' => 'budget-utilization-track', 'command' => 'budget:track-utilization', 'schedule' => '4:00 AM daily', 'cron' => '0 4 * * *', 'description' => 'Utilization tracking', 'category' => 'Budget'],
            ['id' => 'budget-alert-monitor', 'command' => 'budget:monitor-alerts', 'schedule' => '8:00 AM daily', 'cron' => '0 8 * * *', 'description' => 'Alert monitoring', 'category' => 'Budget'],

            // === Component 10: Insurance - Daily ===
            ['id' => 'insurance-claims-process', 'command' => 'insurance:process-claims', 'schedule' => '9:00 AM daily', 'cron' => '0 9 * * *', 'description' => 'Claims processing', 'category' => 'Insurance'],
            ['id' => 'insurance-policy-expiry', 'command' => 'insurance:check-expiry', 'schedule' => '10:00 AM daily', 'cron' => '0 10 * * *', 'description' => 'Policy expiry alerts', 'category' => 'Insurance'],

            // === Component 11: Billing - Daily ===
            ['id' => 'billing-invoice-generate', 'command' => 'billing:generate-invoices', 'schedule' => '6:00 AM daily', 'cron' => '0 6 * * *', 'description' => 'Invoice generation', 'category' => 'Billing'],
            ['id' => 'billing-payment-reminders', 'command' => 'billing:send-reminders', 'schedule' => '9:00 AM daily', 'cron' => '0 9 * * *', 'description' => 'Payment reminders', 'category' => 'Billing'],

            // === Component 12: Transactions - Daily ===
            ['id' => 'transactions-eod-process', 'command' => 'transactions:eod-process', 'schedule' => '11:30 PM daily', 'cron' => '30 23 * * *', 'description' => 'EOD processing', 'category' => 'Transactions'],
            ['id' => 'transactions-reconciliation', 'command' => 'transactions:daily-reconciliation', 'schedule' => '11:45 PM daily', 'cron' => '45 23 * * *', 'description' => 'Daily reconciliation', 'category' => 'Transactions'],

            // === Component 13: Teller Management - Daily ===
            ['id' => 'teller-till-balancing', 'command' => 'teller:balance-tills', 'schedule' => '6:00 PM daily', 'cron' => '0 18 * * *', 'description' => 'Till balancing', 'category' => 'Teller'],
            ['id' => 'teller-vault-reconciliation', 'command' => 'teller:reconcile-vault', 'schedule' => '6:30 PM daily', 'cron' => '30 18 * * *', 'description' => 'Vault reconciliation', 'category' => 'Teller'],

            // === Component 14: Cash Management - Daily ===
            ['id' => 'cash-vault-balancing', 'command' => 'cash:balance-vault', 'schedule' => '7:00 PM daily', 'cron' => '0 19 * * *', 'description' => 'Vault balancing', 'category' => 'Cash'],
            ['id' => 'cash-flow-forecast', 'command' => 'cash:forecast-flow', 'schedule' => '7:30 PM daily', 'cron' => '30 19 * * *', 'description' => 'Cash flow forecasting', 'category' => 'Cash'],

            // === Component 15: Approvals - Daily ===
            ['id' => 'approvals-aging-analysis', 'command' => 'approvals:aging-analysis', 'schedule' => '8:00 AM daily', 'cron' => '0 8 * * *', 'description' => 'Aging analysis', 'category' => 'Approvals'],
            ['id' => 'approvals-escalation', 'command' => 'approvals:process-escalation', 'schedule' => '9:00 AM daily', 'cron' => '0 9 * * *', 'description' => 'Escalation processing', 'category' => 'Approvals'],

            // === Component 16: Reports - Daily ===
            ['id' => 'reports-daily-operations', 'command' => 'reports:daily-operations', 'schedule' => '5:00 PM daily', 'cron' => '0 17 * * *', 'description' => 'Daily operations reports', 'category' => 'Reports'],

            // === Component 17: Members Portal - Daily ===
            ['id' => 'members-statement-generation', 'command' => 'members:portal-statements', 'schedule' => '6:00 AM daily', 'cron' => '0 6 * * *', 'description' => 'Statement generation', 'category' => 'Members'],
            ['id' => 'members-notification-delivery', 'command' => 'members:deliver-notifications', 'schedule' => '7:00 AM daily', 'cron' => '0 7 * * *', 'description' => 'Notification delivery', 'category' => 'Members'],

            // === Component 18: Self Services - Daily ===
            ['id' => 'selfservice-usage-report', 'command' => 'selfservice:usage-report', 'schedule' => '6:00 AM daily', 'cron' => '0 6 * * *', 'description' => 'Usage reports', 'category' => 'System'],
            ['id' => 'selfservice-failed-transactions', 'command' => 'selfservice:failed-transactions', 'schedule' => '7:00 AM daily', 'cron' => '0 7 * * *', 'description' => 'Failed transaction reviews', 'category' => 'System'],

            // === Component 19: Expenses - Daily ===
            ['id' => 'expenses-accrual-processing', 'command' => 'expenses:accrual-processing', 'schedule' => '3:00 AM daily', 'cron' => '0 3 * * *', 'description' => 'Accrual processing', 'category' => 'Expenses'],
            ['id' => 'expenses-payment-alerts', 'command' => 'expenses:payment-alerts', 'schedule' => '8:00 AM daily', 'cron' => '0 8 * * *', 'description' => 'Payment alerts', 'category' => 'Expenses'],

            // === Component 20: Payments - Daily ===
            ['id' => 'payments-settlement', 'command' => 'payments:daily-settlement', 'schedule' => '3:00 PM daily', 'cron' => '0 15 * * *', 'description' => 'Daily settlement', 'category' => 'Payments'],
            ['id' => 'payments-exception-handling', 'command' => 'payments:handle-exceptions', 'schedule' => '4:00 PM daily', 'cron' => '0 16 * * *', 'description' => 'Exception handling', 'category' => 'Payments'],

            // === Component 21: Investment - Daily ===
            ['id' => 'investment-interest-accrual', 'command' => 'investment:accrue-interest', 'schedule' => '5:00 AM daily', 'cron' => '0 5 * * *', 'description' => 'Interest accruals', 'category' => 'Investment'],
            ['id' => 'investment-maturity-monitor', 'command' => 'investment:monitor-maturity', 'schedule' => '5:30 AM daily', 'cron' => '30 5 * * *', 'description' => 'Maturity monitoring', 'category' => 'Investment'],

            // === Component 22: Procurement - Daily ===
            ['id' => 'procurement-po-status', 'command' => 'procurement:update-po-status', 'schedule' => '9:00 AM daily', 'cron' => '0 9 * * *', 'description' => 'PO status updates', 'category' => 'Procurement'],
            ['id' => 'procurement-delivery-tracking', 'command' => 'procurement:track-deliveries', 'schedule' => '10:00 AM daily', 'cron' => '0 10 * * *', 'description' => 'Delivery tracking', 'category' => 'Procurement'],

            // === Component 23: Products Management - Daily ===
            ['id' => 'products-rate-update', 'command' => 'products:update-rates', 'schedule' => '6:00 AM daily', 'cron' => '0 6 * * *', 'description' => 'Rate updates', 'category' => 'Products'],
            ['id' => 'products-availability-check', 'command' => 'products:check-availability', 'schedule' => '7:00 AM daily', 'cron' => '0 7 * * *', 'description' => 'Availability checks', 'category' => 'Products'],

            // === Component 24: Branches - Daily ===
            ['id' => 'branches-cash-position', 'command' => 'branches:cash-position', 'schedule' => '8:00 AM daily', 'cron' => '0 8 * * *', 'description' => 'Cash position', 'category' => 'Branches'],
            ['id' => 'branches-performance-tracking', 'command' => 'branches:track-performance', 'schedule' => '5:00 PM daily', 'cron' => '0 17 * * *', 'description' => 'Performance tracking', 'category' => 'Branches'],

            // === Component 25: Clients/Members - Daily ===
            ['id' => 'clients-status-update', 'command' => 'clients:update-status', 'schedule' => '6:00 AM daily', 'cron' => '0 6 * * *', 'description' => 'Status updates', 'category' => 'Members'],
            ['id' => 'clients-document-expiry', 'command' => 'clients:check-document-expiry', 'schedule' => '7:00 AM daily', 'cron' => '0 7 * * *', 'description' => 'Document expiry', 'category' => 'Members'],

            // === Component 26: Email/Notifications - Daily ===
            ['id' => 'notifications-batch-delivery', 'command' => 'notifications:batch-delivery', 'schedule' => '6:00 PM daily', 'cron' => '0 18 * * *', 'description' => 'Batch delivery', 'category' => 'Notifications'],
            ['id' => 'notifications-cleanup', 'command' => 'notifications:cleanup', 'schedule' => '2:00 AM daily', 'cron' => '0 2 * * *', 'description' => 'Notification cleanup', 'category' => 'Notifications'],
        ];
    }

    /**
     * Weekly Activities
     * From: Weekly Activities section + Component-Specific Weekly tasks
     */
    public static function getWeeklyTasks(): array
    {
        return [
            // === Sunday Activities (06:00 AM) ===
            ['id' => 'reports-generate-weekly', 'command' => 'reports:generate-weekly', 'schedule' => '6:00 AM Sundays', 'cron' => '0 6 * * 0', 'description' => 'Generate all weekly reports', 'category' => 'Reports'],
            ['id' => 'loan-portfolio-weekly', 'command' => 'loans:weekly-portfolio-summary', 'schedule' => '6:30 AM Sundays', 'cron' => '30 6 * * 0', 'description' => 'Comprehensive loan analysis', 'category' => 'Loans'],
            ['id' => 'loan-arrears-weekly', 'command' => 'loans:weekly-arrears-analysis', 'schedule' => '7:00 AM Sundays', 'cron' => '0 7 * * 0', 'description' => '7-sheet arrears report', 'category' => 'Loans'],
            ['id' => 'budget-variance-weekly', 'command' => 'budget:weekly-variance', 'schedule' => '7:30 AM Sundays', 'cron' => '30 7 * * 0', 'description' => 'Budget vs actual analysis', 'category' => 'Budget'],
            ['id' => 'budget-department-weekly', 'command' => 'budget:department-review', 'schedule' => '8:00 AM Sundays', 'cron' => '0 8 * * 0', 'description' => 'Department-level analysis', 'category' => 'Budget'],

            // === Monday Activities ===
            ['id' => 'payment-weekly-performance', 'command' => 'payments:weekly-performance', 'schedule' => '6:00 AM Mondays', 'cron' => '0 6 * * 1', 'description' => 'Payment metrics analysis', 'category' => 'Payments'],
            ['id' => 'branch-weekly-performance', 'command' => 'branches:weekly-performance', 'schedule' => '6:30 AM Mondays', 'cron' => '30 6 * * 1', 'description' => 'Branch comparison report', 'category' => 'Branches'],
            ['id' => 'hr-staff-allocation', 'command' => 'hr:staff-allocation-review', 'schedule' => '7:00 AM Mondays', 'cron' => '0 7 * * 1', 'description' => 'Staffing analysis', 'category' => 'HR'],

            // === Mid-Week Activities (Wednesday) ===
            ['id' => 'payment-failed-analysis', 'command' => 'payments:failed-analysis', 'schedule' => '6:00 AM Wednesdays', 'cron' => '0 6 * * 3', 'description' => 'Root cause analysis', 'category' => 'Payments'],
            ['id' => 'recon-exception-review', 'command' => 'reconciliation:exception-review', 'schedule' => '6:30 AM Wednesdays', 'cron' => '30 6 * * 3', 'description' => 'Review unmatched items', 'category' => 'Reconciliation'],
            ['id' => 'transaction-limit-review', 'command' => 'transactions:limit-review', 'schedule' => '7:00 AM Wednesdays', 'cron' => '0 7 * * 3', 'description' => 'Review limit exceptions', 'category' => 'Transactions'],
            ['id' => 'vendor-weekly-performance', 'command' => 'procurement:vendor-performance', 'schedule' => '7:30 AM Wednesdays', 'cron' => '30 7 * * 3', 'description' => 'Vendor scorecard', 'category' => 'Procurement'],

            // === Friday Activities ===
            ['id' => 'approval-weekly-audit', 'command' => 'approvals:weekly-audit', 'schedule' => '6:00 AM Fridays', 'cron' => '0 6 * * 5', 'description' => 'Approval system verification', 'category' => 'Approvals'],
            ['id' => 'cash-ordering-plan', 'command' => 'cash:ordering-plan', 'schedule' => '6:30 AM Fridays', 'cron' => '30 6 * * 5', 'description' => 'Next week cash requirements', 'category' => 'Cash'],
            ['id' => 'contract-renewal-alerts', 'command' => 'procurement:contract-alerts', 'schedule' => '7:00 AM Fridays', 'cron' => '0 7 * * 5', 'description' => 'Contracts expiring in 60 days', 'category' => 'Procurement'],

            // === Component 5: Loans - Weekly ===
            ['id' => 'loans-delinquency-reports', 'command' => 'loans:delinquency-reports', 'schedule' => '6:00 AM Mondays', 'cron' => '0 6 * * 1', 'description' => 'Delinquency reports', 'category' => 'Loans'],
            ['id' => 'loans-collection-tracking', 'command' => 'loans:collection-tracking', 'schedule' => '7:00 AM Mondays', 'cron' => '0 7 * * 1', 'description' => 'Collection tracking', 'category' => 'Loans'],

            // === Component 7: Reconciliation - Weekly ===
            ['id' => 'reconciliation-aging-report', 'command' => 'reconciliation:aging-report', 'schedule' => '6:00 AM Thursdays', 'cron' => '0 6 * * 4', 'description' => 'Aging reports', 'category' => 'Reconciliation'],

            // === Component 10: Insurance - Weekly ===
            ['id' => 'insurance-premium-followup', 'command' => 'insurance:premium-followup', 'schedule' => '9:00 AM Tuesdays', 'cron' => '0 9 * * 2', 'description' => 'Premium collection follow-ups', 'category' => 'Insurance'],

            // === Component 11: Billing - Weekly ===
            ['id' => 'billing-aging-report', 'command' => 'billing:aging-report', 'schedule' => '6:00 AM Tuesdays', 'cron' => '0 6 * * 2', 'description' => 'Aging reports', 'category' => 'Billing'],
            ['id' => 'billing-collection-followup', 'command' => 'billing:collection-followup', 'schedule' => '10:00 AM Tuesdays', 'cron' => '0 10 * * 2', 'description' => 'Collection follow-ups', 'category' => 'Billing'],

            // === Component 12: Transactions - Weekly ===
            ['id' => 'transactions-exception-review', 'command' => 'transactions:exception-review', 'schedule' => '6:00 AM Thursdays', 'cron' => '0 6 * * 4', 'description' => 'Exception reviews', 'category' => 'Transactions'],

            // === Component 13: Teller Management - Weekly ===
            ['id' => 'teller-performance-review', 'command' => 'teller:performance-review', 'schedule' => '6:00 AM Fridays', 'cron' => '0 6 * * 5', 'description' => 'Performance reviews', 'category' => 'Teller'],
            ['id' => 'teller-cash-ordering', 'command' => 'teller:cash-ordering', 'schedule' => '10:00 AM Fridays', 'cron' => '0 10 * * 5', 'description' => 'Cash ordering', 'category' => 'Teller'],

            // === Component 14: Cash Management - Weekly ===
            ['id' => 'cash-denomination-analysis', 'command' => 'cash:denomination-analysis', 'schedule' => '6:00 AM Fridays', 'cron' => '0 6 * * 5', 'description' => 'Denomination analysis', 'category' => 'Cash'],

            // === Component 15: Approvals - Weekly ===
            ['id' => 'approvals-performance-report', 'command' => 'approvals:performance-report', 'schedule' => '6:00 AM Fridays', 'cron' => '0 6 * * 5', 'description' => 'Performance reports', 'category' => 'Approvals'],

            // === Component 16: Reports - Weekly ===
            ['id' => 'reports-weekly-summaries', 'command' => 'reports:weekly-summaries', 'schedule' => '7:00 AM Sundays', 'cron' => '0 7 * * 0', 'description' => 'Weekly summaries', 'category' => 'Reports'],

            // === Component 17: Members Portal - Weekly ===
            ['id' => 'members-activity-report', 'command' => 'members:activity-report', 'schedule' => '6:00 AM Mondays', 'cron' => '0 6 * * 1', 'description' => 'Activity reports', 'category' => 'Members'],
            ['id' => 'members-dormancy-check', 'command' => 'members:dormancy-check-weekly', 'schedule' => '7:00 AM Mondays', 'cron' => '0 7 * * 1', 'description' => 'Dormancy checks', 'category' => 'Members'],

            // === Component 18: Self Services - Weekly ===
            ['id' => 'selfservice-performance-analysis', 'command' => 'selfservice:performance-analysis', 'schedule' => '6:00 AM Mondays', 'cron' => '0 6 * * 1', 'description' => 'Performance analysis', 'category' => 'System'],

            // === Component 19: Expenses - Weekly ===
            ['id' => 'expenses-weekly-report', 'command' => 'expenses:weekly-report', 'schedule' => '6:00 AM Mondays', 'cron' => '0 6 * * 1', 'description' => 'Weekly reports', 'category' => 'Expenses'],
            ['id' => 'expenses-budget-utilization', 'command' => 'expenses:budget-utilization', 'schedule' => '8:00 AM Mondays', 'cron' => '0 8 * * 1', 'description' => 'Budget utilization', 'category' => 'Expenses'],

            // === Component 20: Payments - Weekly ===
            ['id' => 'payments-performance-report', 'command' => 'payments:performance-report', 'schedule' => '6:00 AM Mondays', 'cron' => '0 6 * * 1', 'description' => 'Performance reports', 'category' => 'Payments'],

            // === Component 21: Investment - Weekly ===
            ['id' => 'investment-weekly-performance', 'command' => 'investment:weekly-performance', 'schedule' => '6:00 AM Mondays', 'cron' => '0 6 * * 1', 'description' => 'Performance reports', 'category' => 'Investment'],

            // === Component 22: Procurement - Weekly ===
            ['id' => 'procurement-pending-po-review', 'command' => 'procurement:pending-po-review', 'schedule' => '6:00 AM Thursdays', 'cron' => '0 6 * * 4', 'description' => 'Pending PO reviews', 'category' => 'Procurement'],

            // === Component 23: Products Management - Weekly ===
            ['id' => 'products-performance-report', 'command' => 'products:performance-report', 'schedule' => '6:00 AM Mondays', 'cron' => '0 6 * * 1', 'description' => 'Performance reports', 'category' => 'Products'],

            // === Component 24: Branches - Weekly ===
            ['id' => 'branches-staff-review', 'command' => 'branches:staff-review', 'schedule' => '6:00 AM Fridays', 'cron' => '0 6 * * 5', 'description' => 'Staff reviews', 'category' => 'Branches'],

            // === Component 25: Clients/Members - Weekly ===
            ['id' => 'clients-new-member-report', 'command' => 'clients:new-member-report', 'schedule' => '6:00 AM Mondays', 'cron' => '0 6 * * 1', 'description' => 'New member reports', 'category' => 'Members'],
            ['id' => 'clients-activity-analysis', 'command' => 'clients:activity-analysis', 'schedule' => '7:00 AM Mondays', 'cron' => '0 7 * * 1', 'description' => 'Activity analysis', 'category' => 'Members'],

            // === Component 26: Email/Notifications - Weekly ===
            ['id' => 'notifications-delivery-report', 'command' => 'notifications:delivery-report', 'schedule' => '6:00 AM Mondays', 'cron' => '0 6 * * 1', 'description' => 'Delivery reports', 'category' => 'Notifications'],
            ['id' => 'notifications-failed-retry', 'command' => 'notifications:retry-failed', 'schedule' => '8:00 AM Wednesdays', 'cron' => '0 8 * * 3', 'description' => 'Failed retries', 'category' => 'Notifications'],
        ];
    }

    /**
     * Monthly Activities
     * From: Monthly Activities section + Component-Specific Monthly tasks
     */
    public static function getMonthlyTasks(): array
    {
        return [
            // === Month-End Close (Last Day, 23:00-23:30) ===
            ['id' => 'sacco-run-monthly-activities', 'command' => 'sacco:run-monthly-activities', 'schedule' => '11:00 PM on 30th', 'cron' => '0 23 30 * *', 'description' => 'End-of-month processing', 'category' => 'System'],
            ['id' => 'budget-period-close-monthly', 'command' => 'budget:period-close --type=monthly', 'schedule' => '11:30 PM on 30th', 'cron' => '30 23 30 * *', 'description' => 'Close monthly budget', 'category' => 'Budget'],
            ['id' => 'accounting-period-close', 'command' => 'accounting:period-close', 'schedule' => '11:45 PM on 30th', 'cron' => '45 23 30 * *', 'description' => 'Close accounting period', 'category' => 'Accounting'],
            ['id' => 'expense-monthly-reconciliation', 'command' => 'expenses:monthly-reconciliation', 'schedule' => '11:15 PM on 30th', 'cron' => '15 23 30 * *', 'description' => 'Monthly expense close', 'category' => 'Expenses'],

            // === First Week of Month ===
            ['id' => 'reports-generate-monthly', 'command' => 'reports:generate-monthly', 'schedule' => '7:00 AM on 1st', 'cron' => '0 7 1 * *', 'description' => 'Generate all monthly reports', 'category' => 'Reports'],
            ['id' => 'member-statements-monthly', 'command' => 'members:monthly-statements', 'schedule' => '8:00 AM on 1st', 'cron' => '0 8 1 * *', 'description' => 'Generate/distribute statements', 'category' => 'Members'],
            ['id' => 'member-fee-processing', 'command' => 'members:process-monthly-fees', 'schedule' => '9:00 AM on 1st', 'cron' => '0 9 1 * *', 'description' => 'Process monthly fees', 'category' => 'Members'],
            ['id' => 'share-dividend-monthly', 'command' => 'shares:monthly-dividend', 'schedule' => '6:00 AM on 2nd', 'cron' => '0 6 2 * *', 'description' => 'Monthly dividend calculations', 'category' => 'Shares'],
            ['id' => 'investment-nav-calculation', 'command' => 'investment:calculate-nav', 'schedule' => '7:00 AM on 3rd', 'cron' => '0 7 3 * *', 'description' => 'Update fund values', 'category' => 'Investment'],
            ['id' => 'deposit-maturity-report', 'command' => 'deposits:maturity-report', 'schedule' => '9:00 AM on 3rd', 'cron' => '0 9 3 * *', 'description' => 'Maturing deposits review', 'category' => 'Deposits'],
            ['id' => 'loan-portfolio-review', 'command' => 'loans:portfolio-review', 'schedule' => '8:00 AM on 4th', 'cron' => '0 8 4 * *', 'description' => 'Monthly portfolio analysis', 'category' => 'Loans'],
            ['id' => 'provision-cycle-monthly', 'command' => 'provision:cycle MONTHLY', 'schedule' => '9:00 AM on 5th', 'cron' => '0 9 5 * *', 'description' => 'Monthly loan provisioning', 'category' => 'Loans'],

            // === Mid-Month Activities ===
            ['id' => 'teller-monthly-audit', 'command' => 'teller:monthly-audit', 'schedule' => '8:00 AM on 10th', 'cron' => '0 8 10 * *', 'description' => 'Variance analysis', 'category' => 'Teller'],
            ['id' => 'budget-variance-monthly', 'command' => 'budget:monthly-variance', 'schedule' => '8:00 AM on 15th', 'cron' => '0 8 15 * *', 'description' => 'Mid-month budget review', 'category' => 'Budget'],
            ['id' => 'vendor-payment-run', 'command' => 'procurement:vendor-payments', 'schedule' => '9:00 AM on 15th', 'cron' => '0 9 15 * *', 'description' => 'Monthly vendor payments', 'category' => 'Procurement'],
            ['id' => 'hr-payroll-process', 'command' => 'hr:process-payroll', 'schedule' => '10:00 AM on 15th', 'cron' => '0 10 15 * *', 'description' => 'Process monthly payroll', 'category' => 'HR'],
            ['id' => 'compliance-monthly-report', 'command' => 'compliance:monthly-report', 'schedule' => '8:00 AM on 20th', 'cron' => '0 8 20 * *', 'description' => 'Monthly compliance status', 'category' => 'Compliance'],

            // === Financial Statements (Monthly) ===
            ['id' => 'accounting-balance-sheet', 'command' => 'accounting:balance-sheet', 'schedule' => '7:00 AM on 3rd', 'cron' => '0 7 3 * *', 'description' => 'Statement of Financial Position (Balance Sheet)', 'category' => 'Accounting'],
            ['id' => 'accounting-income-statement', 'command' => 'accounting:income-statement', 'schedule' => '7:30 AM on 3rd', 'cron' => '30 7 3 * *', 'description' => 'Statement of Comprehensive Income', 'category' => 'Accounting'],
            ['id' => 'accounting-cash-flow-statement', 'command' => 'accounting:cash-flow-statement', 'schedule' => '8:00 AM on 3rd', 'cron' => '0 8 3 * *', 'description' => 'Statement of Cash Flow', 'category' => 'Accounting'],
            ['id' => 'accounting-trial-balance-monthly', 'command' => 'accounting:trial-balance-monthly', 'schedule' => '8:30 AM on 3rd', 'cron' => '30 8 3 * *', 'description' => 'Trial Balance (Account balances)', 'category' => 'Accounting'],
            ['id' => 'accounting-capital-adequacy', 'command' => 'accounting:capital-adequacy', 'schedule' => '9:00 AM on 3rd', 'cron' => '0 9 3 * *', 'description' => 'Capital Adequacy Report (BOT requirement)', 'category' => 'Accounting'],
            ['id' => 'accounting-liquid-assets', 'command' => 'accounting:liquid-assets', 'schedule' => '9:30 AM on 3rd', 'cron' => '30 9 3 * *', 'description' => 'Liquid Assets Report (Liquidity compliance)', 'category' => 'Accounting'],

            // === Regulatory Reports (Monthly) ===
            ['id' => 'compliance-sectoral-loans', 'command' => 'compliance:sectoral-loans', 'schedule' => '8:00 AM on 5th', 'cron' => '0 8 5 * *', 'description' => 'Sectoral Classification of Loans (BOT)', 'category' => 'Compliance'],
            ['id' => 'compliance-insider-loans', 'command' => 'compliance:insider-loans', 'schedule' => '8:30 AM on 5th', 'cron' => '30 8 5 * *', 'description' => 'Loans to Insiders (BOT)', 'category' => 'Compliance'],
            ['id' => 'compliance-interest-rates', 'command' => 'compliance:interest-rates', 'schedule' => '9:00 AM on 5th', 'cron' => '0 9 5 * *', 'description' => 'Interest Rates Structure (BOT)', 'category' => 'Compliance'],
            ['id' => 'compliance-geographical', 'command' => 'compliance:geographical', 'schedule' => '9:30 AM on 5th', 'cron' => '30 9 5 * *', 'description' => 'Geographical Distribution (BOT)', 'category' => 'Compliance'],
            ['id' => 'compliance-delinquency', 'command' => 'compliance:delinquency', 'schedule' => '10:00 AM on 5th', 'cron' => '0 10 5 * *', 'description' => 'Delinquency Report (BOT)', 'category' => 'Compliance'],

            // === Component 2: Shares - Monthly ===
            ['id' => 'shares-value-update', 'command' => 'shares:value-update', 'schedule' => '7:00 AM on 2nd', 'cron' => '0 7 2 * *', 'description' => 'Share value updates', 'category' => 'Shares'],

            // === Component 3: Savings - Monthly ===
            ['id' => 'savings-interest-capitalize', 'command' => 'savings:capitalize-interest', 'schedule' => '6:00 AM on 1st', 'cron' => '0 6 1 * *', 'description' => 'Interest capitalization', 'category' => 'Savings'],
            ['id' => 'savings-statement-generate', 'command' => 'savings:generate-statements', 'schedule' => '7:00 AM on 1st', 'cron' => '0 7 1 * *', 'description' => 'Statement generation', 'category' => 'Savings'],

            // === Component 4: Deposits - Monthly ===
            ['id' => 'deposits-interest-capitalize', 'command' => 'deposits:capitalize-interest', 'schedule' => '6:00 AM on 1st', 'cron' => '0 6 1 * *', 'description' => 'Interest capitalization', 'category' => 'Deposits'],
            ['id' => 'deposits-renewal-process', 'command' => 'deposits:process-renewals', 'schedule' => '7:00 AM on 1st', 'cron' => '0 7 1 * *', 'description' => 'Renewal processing', 'category' => 'Deposits'],

            // === Component 5: Loans - Monthly ===
            ['id' => 'loans-portfolio-analysis', 'command' => 'loans:portfolio-analysis', 'schedule' => '8:00 AM on 4th', 'cron' => '0 8 4 * *', 'description' => 'Portfolio analysis', 'category' => 'Loans'],

            // === Component 7: Reconciliation - Monthly ===
            ['id' => 'reconciliation-full-monthly', 'command' => 'reconciliation:full-monthly', 'schedule' => '6:00 AM on 5th', 'cron' => '0 6 5 * *', 'description' => 'Full reconciliation', 'category' => 'Reconciliation'],
            ['id' => 'reconciliation-discrepancy', 'command' => 'reconciliation:discrepancy-resolution', 'schedule' => '10:00 AM on 5th', 'cron' => '0 10 5 * *', 'description' => 'Discrepancy resolution', 'category' => 'Reconciliation'],

            // === Component 8: HR/Payroll - Monthly ===
            ['id' => 'hr-deductions-process', 'command' => 'hr:process-deductions', 'schedule' => '11:00 AM on 15th', 'cron' => '0 11 15 * *', 'description' => 'Process deductions', 'category' => 'HR'],

            // === Component 9: Budget - Monthly ===
            ['id' => 'budget-reforecast', 'command' => 'budget:reforecast', 'schedule' => '9:00 AM on 20th', 'cron' => '0 9 20 * *', 'description' => 'Budget reforecast', 'category' => 'Budget'],

            // === Component 10: Insurance - Monthly ===
            ['id' => 'insurance-premium-reconciliation', 'command' => 'insurance:premium-reconciliation', 'schedule' => '8:00 AM on 10th', 'cron' => '0 8 10 * *', 'description' => 'Premium reconciliation', 'category' => 'Insurance'],
            ['id' => 'insurance-commission-calc', 'command' => 'insurance:commission-calculation', 'schedule' => '9:00 AM on 10th', 'cron' => '0 9 10 * *', 'description' => 'Commission calculations', 'category' => 'Insurance'],

            // === Component 11: Billing - Monthly ===
            ['id' => 'billing-statement-generate', 'command' => 'billing:generate-statements', 'schedule' => '7:00 AM on 1st', 'cron' => '0 7 1 * *', 'description' => 'Statement generation', 'category' => 'Billing'],
            ['id' => 'billing-fee-calculation', 'command' => 'billing:fee-calculation', 'schedule' => '8:00 AM on 1st', 'cron' => '0 8 1 * *', 'description' => 'Fee calculations', 'category' => 'Billing'],

            // === Component 12: Transactions - Monthly ===
            ['id' => 'transactions-monthly-summary', 'command' => 'transactions:monthly-summary', 'schedule' => '6:00 AM on 1st', 'cron' => '0 6 1 * *', 'description' => 'Transaction summaries', 'category' => 'Transactions'],

            // === Component 13: Teller Management - Monthly ===
            ['id' => 'teller-shortage-analysis', 'command' => 'teller:shortage-analysis', 'schedule' => '9:00 AM on 10th', 'cron' => '0 9 10 * *', 'description' => 'Shortage analysis', 'category' => 'Teller'],

            // === Component 14: Cash Management - Monthly ===
            ['id' => 'cash-cost-analysis', 'command' => 'cash:cost-analysis', 'schedule' => '8:00 AM on 15th', 'cron' => '0 8 15 * *', 'description' => 'Cost analysis', 'category' => 'Cash'],
            ['id' => 'cash-insurance-review', 'command' => 'cash:insurance-review', 'schedule' => '9:00 AM on 15th', 'cron' => '0 9 15 * *', 'description' => 'Insurance reviews', 'category' => 'Cash'],

            // === Component 15: Approvals - Monthly ===
            ['id' => 'approvals-bottleneck-analysis', 'command' => 'approvals:bottleneck-analysis', 'schedule' => '8:00 AM on 15th', 'cron' => '0 8 15 * *', 'description' => 'Bottleneck analysis', 'category' => 'Approvals'],

            // === Component 16: Reports - Monthly ===
            ['id' => 'reports-regulatory-monthly', 'command' => 'reports:regulatory-monthly', 'schedule' => '6:00 AM on 5th', 'cron' => '0 6 5 * *', 'description' => 'Regulatory reports', 'category' => 'Reports'],
            ['id' => 'reports-financial-statements', 'command' => 'reports:financial-statements', 'schedule' => '7:00 AM on 3rd', 'cron' => '0 7 3 * *', 'description' => 'Financial statements', 'category' => 'Reports'],

            // === Component 17: Members Portal - Monthly ===
            ['id' => 'members-fee-processing', 'command' => 'members:fee-processing', 'schedule' => '9:00 AM on 1st', 'cron' => '0 9 1 * *', 'description' => 'Fee processing', 'category' => 'Members'],
            ['id' => 'members-renewals', 'command' => 'members:process-renewals', 'schedule' => '10:00 AM on 1st', 'cron' => '0 10 1 * *', 'description' => 'Membership renewals', 'category' => 'Members'],

            // === Component 18: Self Services - Monthly ===
            ['id' => 'selfservice-adoption-metrics', 'command' => 'selfservice:adoption-metrics', 'schedule' => '6:00 AM on 5th', 'cron' => '0 6 5 * *', 'description' => 'Adoption metrics', 'category' => 'System'],

            // === Component 19: Expenses - Monthly ===
            ['id' => 'expenses-vendor-payments', 'command' => 'expenses:vendor-payments', 'schedule' => '9:00 AM on 15th', 'cron' => '0 9 15 * *', 'description' => 'Vendor payments', 'category' => 'Expenses'],

            // === Component 20: Payments - Monthly ===
            ['id' => 'payments-fee-calculation', 'command' => 'payments:fee-calculation', 'schedule' => '8:00 AM on 1st', 'cron' => '0 8 1 * *', 'description' => 'Fee calculations', 'category' => 'Payments'],
            ['id' => 'payments-statement-generate', 'command' => 'payments:generate-statements', 'schedule' => '9:00 AM on 1st', 'cron' => '0 9 1 * *', 'description' => 'Payment statements', 'category' => 'Payments'],

            // === Component 21: Investment - Monthly ===
            ['id' => 'investment-dividend-process', 'command' => 'investment:dividend-process', 'schedule' => '8:00 AM on 3rd', 'cron' => '0 8 3 * *', 'description' => 'Dividend processing', 'category' => 'Investment'],

            // === Component 22: Procurement - Monthly ===
            ['id' => 'procurement-reconciliation', 'command' => 'procurement:reconciliation', 'schedule' => '8:00 AM on 15th', 'cron' => '0 8 15 * *', 'description' => 'Vendor reconciliation', 'category' => 'Procurement'],

            // === Component 23: Products Management - Monthly ===
            ['id' => 'products-profitability-analysis', 'command' => 'products:profitability-analysis', 'schedule' => '8:00 AM on 10th', 'cron' => '0 8 10 * *', 'description' => 'Profitability analysis', 'category' => 'Products'],

            // === Component 24: Branches - Monthly ===
            ['id' => 'branches-profitability-analysis', 'command' => 'branches:profitability-analysis', 'schedule' => '8:00 AM on 10th', 'cron' => '0 8 10 * *', 'description' => 'Profitability analysis', 'category' => 'Branches'],

            // === Component 25: Clients/Members - Monthly ===
            ['id' => 'clients-retention-report', 'command' => 'clients:retention-report', 'schedule' => '8:00 AM on 15th', 'cron' => '0 8 15 * *', 'description' => 'Retention reports', 'category' => 'Members'],
            ['id' => 'clients-dormancy-check-monthly', 'command' => 'clients:dormancy-check-monthly', 'schedule' => '9:00 AM on 15th', 'cron' => '0 9 15 * *', 'description' => 'Monthly dormancy checks', 'category' => 'Members'],

            // === Component 26: Email/Notifications - Monthly ===
            ['id' => 'notifications-analytics', 'command' => 'notifications:analytics', 'schedule' => '8:00 AM on 5th', 'cron' => '0 8 5 * *', 'description' => 'Notification analytics', 'category' => 'Notifications'],
            ['id' => 'notifications-template-review', 'command' => 'notifications:template-review', 'schedule' => '9:00 AM on 5th', 'cron' => '0 9 5 * *', 'description' => 'Template reviews', 'category' => 'Notifications'],
        ];
    }

    /**
     * Quarterly Activities
     * From: Quarterly Activities section + Component-Specific Quarterly tasks
     */
    public static function getQuarterlyTasks(): array
    {
        return [
            // === Quarter-End Close ===
            ['id' => 'sacco-run-quarterly-activities', 'command' => 'sacco:run-quarterly-activities', 'schedule' => '11:00 PM quarterly (1st)', 'cron' => '0 23 1 1,4,7,10 *', 'description' => 'End-of-quarter processing', 'category' => 'System'],
            ['id' => 'budget-period-close-quarterly', 'command' => 'budget:period-close --type=quarterly', 'schedule' => '11:45 PM quarterly', 'cron' => '45 23 1 1,4,7,10 *', 'description' => 'Close quarterly budget', 'category' => 'Budget'],
            ['id' => 'provision-cycle-quarterly', 'command' => 'provision:cycle QUARTERLY', 'schedule' => '9:30 AM quarterly (5th)', 'cron' => '30 9 5 1,4,7,10 *', 'description' => 'Loan loss provisions', 'category' => 'Loans'],

            // === First Week of Quarter ===
            ['id' => 'accounting-quarterly-statements', 'command' => 'accounting:quarterly-statements', 'schedule' => '8:00 AM quarterly (1st)', 'cron' => '0 8 1 1,4,7,10 *', 'description' => 'Full financial statements', 'category' => 'Accounting'],
            ['id' => 'investment-rebalancing', 'command' => 'investment:rebalancing', 'schedule' => '8:00 AM quarterly (2nd)', 'cron' => '0 8 2 1,4,7,10 *', 'description' => 'Asset allocation review', 'category' => 'Investment'],
            ['id' => 'performance-quarterly-review', 'command' => 'reports:quarterly-performance', 'schedule' => '8:00 AM quarterly (3rd)', 'cron' => '0 8 3 1,4,7,10 *', 'description' => 'Comprehensive metrics', 'category' => 'Reports'],

            // === Strategic Reviews ===
            ['id' => 'risk-quarterly-assessment', 'command' => 'risk:quarterly-assessment', 'schedule' => '8:00 AM quarterly (5th)', 'cron' => '0 8 5 1,4,7,10 *', 'description' => 'Credit, market, operational risk', 'category' => 'Compliance'],
            ['id' => 'budget-revision-assessment', 'command' => 'budget:revision-assessment', 'schedule' => '9:00 AM quarterly (5th)', 'cron' => '0 9 5 1,4,7,10 *', 'description' => 'Budget performance review', 'category' => 'Budget'],
            ['id' => 'approval-matrix-review', 'command' => 'approvals:matrix-review', 'schedule' => '10:00 AM quarterly (5th)', 'cron' => '0 10 5 1,4,7,10 *', 'description' => 'Authority levels review', 'category' => 'Approvals'],
            ['id' => 'vendor-evaluation', 'command' => 'procurement:vendor-evaluation', 'schedule' => '11:00 AM quarterly (5th)', 'cron' => '0 11 5 1,4,7,10 *', 'description' => 'Vendor performance ratings', 'category' => 'Procurement'],
            ['id' => 'product-portfolio-review', 'command' => 'products:portfolio-review', 'schedule' => '8:00 AM quarterly (6th)', 'cron' => '0 8 6 1,4,7,10 *', 'description' => 'Product performance analysis', 'category' => 'Products'],
            ['id' => 'branch-performance-review', 'command' => 'branches:performance-review', 'schedule' => '9:00 AM quarterly (6th)', 'cron' => '0 9 6 1,4,7,10 *', 'description' => 'Strategic branch assessment', 'category' => 'Branches'],
            ['id' => 'member-status-review', 'command' => 'members:status-review', 'schedule' => '10:00 AM quarterly (6th)', 'cron' => '0 10 6 1,4,7,10 *', 'description' => 'Client risk profiling', 'category' => 'Members'],

            // === Compliance Activities ===
            ['id' => 'compliance-aml-quarterly', 'command' => 'compliance:aml-quarterly', 'schedule' => '8:00 AM quarterly (7th)', 'cron' => '0 8 7 1,4,7,10 *', 'description' => 'Anti-money laundering review', 'category' => 'Compliance'],
            ['id' => 'audit-internal-controls', 'command' => 'audit:internal-controls', 'schedule' => '9:00 AM quarterly (7th)', 'cron' => '0 9 7 1,4,7,10 *', 'description' => 'Control effectiveness', 'category' => 'Compliance'],
            ['id' => 'compliance-regulatory-quarterly', 'command' => 'compliance:regulatory-quarterly', 'schedule' => '10:00 AM quarterly (7th)', 'cron' => '0 10 7 1,4,7,10 *', 'description' => 'BOT compliance status', 'category' => 'Compliance'],
            ['id' => 'insurance-coverage-review', 'command' => 'insurance:coverage-review', 'schedule' => '11:00 AM quarterly (7th)', 'cron' => '0 11 7 1,4,7,10 *', 'description' => 'Policy adequacy', 'category' => 'Insurance'],

            // === Component 2: Shares - Quarterly ===
            ['id' => 'shares-dividend-eligibility', 'command' => 'shares:dividend-eligibility', 'schedule' => '8:00 AM quarterly (10th)', 'cron' => '0 8 10 1,4,7,10 *', 'description' => 'Dividend eligibility review', 'category' => 'Shares'],

            // === Component 3: Savings - Quarterly ===
            ['id' => 'savings-dormancy-review', 'command' => 'savings:dormancy-review', 'schedule' => '8:00 AM quarterly (10th)', 'cron' => '0 8 10 1,4,7,10 *', 'description' => 'Dormancy checks', 'category' => 'Savings'],
            ['id' => 'savings-compliance-review', 'command' => 'savings:compliance-review', 'schedule' => '9:00 AM quarterly (10th)', 'cron' => '0 9 10 1,4,7,10 *', 'description' => 'Compliance reviews', 'category' => 'Savings'],

            // === Component 4: Deposits - Quarterly ===
            ['id' => 'deposits-rate-competitiveness', 'command' => 'deposits:rate-competitiveness', 'schedule' => '8:00 AM quarterly (10th)', 'cron' => '0 8 10 1,4,7,10 *', 'description' => 'Rate competitiveness review', 'category' => 'Deposits'],

            // === Component 5: Loans - Quarterly ===
            ['id' => 'loans-stress-testing', 'command' => 'loans:stress-testing', 'schedule' => '8:00 AM quarterly (8th)', 'cron' => '0 8 8 1,4,7,10 *', 'description' => 'Stress testing', 'category' => 'Loans'],
            ['id' => 'loans-risk-assessment', 'command' => 'loans:risk-assessment', 'schedule' => '9:00 AM quarterly (8th)', 'cron' => '0 9 8 1,4,7,10 *', 'description' => 'Risk assessment', 'category' => 'Loans'],

            // === Component 6: Accounting - Quarterly ===
            ['id' => 'accounting-audit-prep', 'command' => 'accounting:audit-prep', 'schedule' => '8:00 AM quarterly (10th)', 'cron' => '0 8 10 1,4,7,10 *', 'description' => 'Audit prep', 'category' => 'Accounting'],

            // === Component 7: Reconciliation - Quarterly ===
            ['id' => 'reconciliation-comprehensive-audit', 'command' => 'reconciliation:comprehensive-audit', 'schedule' => '8:00 AM quarterly (12th)', 'cron' => '0 8 12 1,4,7,10 *', 'description' => 'Comprehensive audit', 'category' => 'Reconciliation'],

            // === Component 8: HR/Payroll - Quarterly ===
            ['id' => 'hr-performance-review', 'command' => 'hr:performance-review', 'schedule' => '8:00 AM quarterly (15th)', 'cron' => '0 8 15 1,4,7,10 *', 'description' => 'Performance reviews', 'category' => 'HR'],

            // === Component 9: Budget - Quarterly ===
            ['id' => 'budget-revisions', 'command' => 'budget:quarterly-revisions', 'schedule' => '8:00 AM quarterly (10th)', 'cron' => '0 8 10 1,4,7,10 *', 'description' => 'Budget revisions', 'category' => 'Budget'],

            // === Component 10: Insurance - Quarterly ===
            ['id' => 'insurance-loss-ratio', 'command' => 'insurance:loss-ratio', 'schedule' => '8:00 AM quarterly (15th)', 'cron' => '0 8 15 1,4,7,10 *', 'description' => 'Loss ratio analysis', 'category' => 'Insurance'],

            // === Component 11: Billing - Quarterly ===
            ['id' => 'billing-fee-review', 'command' => 'billing:fee-review', 'schedule' => '8:00 AM quarterly (10th)', 'cron' => '0 8 10 1,4,7,10 *', 'description' => 'Fee reviews', 'category' => 'Billing'],
            ['id' => 'billing-audit', 'command' => 'billing:quarterly-audit', 'schedule' => '9:00 AM quarterly (10th)', 'cron' => '0 9 10 1,4,7,10 *', 'description' => 'Billing audits', 'category' => 'Billing'],

            // === Component 12: Transactions - Quarterly/Annual ===
            ['id' => 'transactions-quarterly-audit', 'command' => 'transactions:quarterly-audit', 'schedule' => '8:00 AM quarterly (12th)', 'cron' => '0 8 12 1,4,7,10 *', 'description' => 'Transaction audits', 'category' => 'Transactions'],
            ['id' => 'transactions-pattern-analysis', 'command' => 'transactions:pattern-analysis', 'schedule' => '9:00 AM quarterly (12th)', 'cron' => '0 9 12 1,4,7,10 *', 'description' => 'Pattern analysis', 'category' => 'Transactions'],

            // === Component 13: Teller Management - Quarterly ===
            ['id' => 'teller-security-review', 'command' => 'teller:security-review', 'schedule' => '8:00 AM quarterly (15th)', 'cron' => '0 8 15 1,4,7,10 *', 'description' => 'Security reviews', 'category' => 'Teller'],

            // === Component 14: Cash Management - Quarterly ===
            ['id' => 'cash-handling-audit', 'command' => 'cash:handling-audit', 'schedule' => '8:00 AM quarterly (15th)', 'cron' => '0 8 15 1,4,7,10 *', 'description' => 'Handling audits', 'category' => 'Cash'],

            // === Component 15: Approvals - Quarterly ===
            ['id' => 'approvals-matrix-quarterly-review', 'command' => 'approvals:matrix-quarterly-review', 'schedule' => '8:00 AM quarterly (10th)', 'cron' => '0 8 10 1,4,7,10 *', 'description' => 'Matrix reviews', 'category' => 'Approvals'],

            // === Component 16: Reports - Quarterly ===
            ['id' => 'reports-quarterly-filings', 'command' => 'reports:quarterly-filings', 'schedule' => '8:00 AM quarterly (10th)', 'cron' => '0 8 10 1,4,7,10 *', 'description' => 'Quarterly filings', 'category' => 'Reports'],

            // === Component 17: Members Portal - Quarterly ===
            ['id' => 'members-status-review-quarterly', 'command' => 'members:status-review-quarterly', 'schedule' => '8:00 AM quarterly (10th)', 'cron' => '0 8 10 1,4,7,10 *', 'description' => 'Status reviews', 'category' => 'Members'],
            ['id' => 'members-compliance-check', 'command' => 'members:compliance-check', 'schedule' => '9:00 AM quarterly (10th)', 'cron' => '0 9 10 1,4,7,10 *', 'description' => 'Compliance checks', 'category' => 'Members'],

            // === Component 18: Self Services - Quarterly ===
            ['id' => 'selfservice-ux-assessment', 'command' => 'selfservice:ux-assessment', 'schedule' => '8:00 AM quarterly (15th)', 'cron' => '0 8 15 1,4,7,10 *', 'description' => 'UX assessments', 'category' => 'System'],

            // === Component 19: Expenses - Quarterly ===
            ['id' => 'expenses-quarterly-audit', 'command' => 'expenses:quarterly-audit', 'schedule' => '8:00 AM quarterly (10th)', 'cron' => '0 8 10 1,4,7,10 *', 'description' => 'Expense audits', 'category' => 'Expenses'],
            ['id' => 'expenses-budget-review', 'command' => 'expenses:budget-review', 'schedule' => '9:00 AM quarterly (10th)', 'cron' => '0 9 10 1,4,7,10 *', 'description' => 'Budget reviews', 'category' => 'Expenses'],

            // === Component 20: Payments - Quarterly ===
            ['id' => 'payments-system-audit', 'command' => 'payments:system-audit', 'schedule' => '8:00 AM quarterly (12th)', 'cron' => '0 8 12 1,4,7,10 *', 'description' => 'System audits', 'category' => 'Payments'],

            // === Component 21: Investment - Quarterly ===
            ['id' => 'investment-performance-review', 'command' => 'investment:performance-review', 'schedule' => '8:00 AM quarterly (10th)', 'cron' => '0 8 10 1,4,7,10 *', 'description' => 'Performance reviews', 'category' => 'Investment'],

            // === Component 22: Procurement - Quarterly ===
            ['id' => 'procurement-contract-renewals', 'command' => 'procurement:contract-renewals', 'schedule' => '8:00 AM quarterly (15th)', 'cron' => '0 8 15 1,4,7,10 *', 'description' => 'Contract renewals', 'category' => 'Procurement'],

            // === Component 23: Products Management - Quarterly ===
            ['id' => 'products-portfolio-quarterly-review', 'command' => 'products:portfolio-quarterly-review', 'schedule' => '8:00 AM quarterly (10th)', 'cron' => '0 8 10 1,4,7,10 *', 'description' => 'Portfolio reviews', 'category' => 'Products'],

            // === Component 24: Branches - Quarterly ===
            ['id' => 'branches-quarterly-audit', 'command' => 'branches:quarterly-audit', 'schedule' => '8:00 AM quarterly (15th)', 'cron' => '0 8 15 1,4,7,10 *', 'description' => 'Branch audits', 'category' => 'Branches'],
            ['id' => 'branches-compliance-check', 'command' => 'branches:compliance-check', 'schedule' => '9:00 AM quarterly (15th)', 'cron' => '0 9 15 1,4,7,10 *', 'description' => 'Compliance checks', 'category' => 'Branches'],

            // === Component 25: Clients/Members - Quarterly ===
            ['id' => 'clients-risk-review', 'command' => 'clients:risk-review', 'schedule' => '8:00 AM quarterly (10th)', 'cron' => '0 8 10 1,4,7,10 *', 'description' => 'Risk reviews', 'category' => 'Members'],
            ['id' => 'clients-aml-check', 'command' => 'clients:aml-check', 'schedule' => '9:00 AM quarterly (10th)', 'cron' => '0 9 10 1,4,7,10 *', 'description' => 'AML/CFT checks', 'category' => 'Members'],

            // === Component 26: Email/Notifications - Quarterly ===
            ['id' => 'notifications-channel-optimization', 'command' => 'notifications:channel-optimization', 'schedule' => '8:00 AM quarterly (15th)', 'cron' => '0 8 15 1,4,7,10 *', 'description' => 'Channel optimization', 'category' => 'Notifications'],
        ];
    }

    /**
     * Semi-Annual Activities (15+ activities)
     */
    public static function getSemiAnnualTasks(): array
    {
        return [
            ['id' => 'policy-semi-annual-review', 'command' => 'compliance:policy-review', 'schedule' => '8:00 AM on Jul 1 & Jan 1', 'cron' => '0 8 1 1,7 *', 'description' => 'Semi-annual policy reviews', 'category' => 'Compliance'],
            ['id' => 'risk-semi-annual-assessment', 'command' => 'risk:semi-annual-assessment', 'schedule' => '9:00 AM on Jul 1 & Jan 1', 'cron' => '0 9 1 1,7 *', 'description' => 'Major risk assessments', 'category' => 'Compliance'],
            ['id' => 'security-semi-annual-audit', 'command' => 'security:semi-annual-audit', 'schedule' => '8:00 AM on Jul 15 & Jan 15', 'cron' => '0 8 15 1,7 *', 'description' => 'Semi-annual security audit', 'category' => 'Security'],
            ['id' => 'hr-semi-annual-review', 'command' => 'hr:semi-annual-review', 'schedule' => '8:00 AM on Jul 1 & Jan 1', 'cron' => '0 8 1 1,7 *', 'description' => 'Semi-annual HR review', 'category' => 'HR'],
            ['id' => 'budget-semi-annual-review', 'command' => 'budget:semi-annual-review', 'schedule' => '8:00 AM on Jul 1 & Jan 1', 'cron' => '0 8 1 1,7 *', 'description' => 'Semi-annual budget review', 'category' => 'Budget'],
            ['id' => 'loans-semi-annual-policy', 'command' => 'loans:semi-annual-policy', 'schedule' => '8:00 AM on Jul 1 & Jan 1', 'cron' => '0 8 1 1,7 *', 'description' => 'Semi-annual loan policy review', 'category' => 'Loans'],
            ['id' => 'savings-semi-annual-review', 'command' => 'savings:semi-annual-review', 'schedule' => '8:00 AM on Jul 1 & Jan 1', 'cron' => '0 8 1 1,7 *', 'description' => 'Semi-annual savings review', 'category' => 'Savings'],
            ['id' => 'deposits-semi-annual-review', 'command' => 'deposits:semi-annual-review', 'schedule' => '8:00 AM on Jul 1 & Jan 1', 'cron' => '0 8 1 1,7 *', 'description' => 'Semi-annual deposit review', 'category' => 'Deposits'],
            ['id' => 'investment-semi-annual-review', 'command' => 'investment:semi-annual-review', 'schedule' => '8:00 AM on Jul 1 & Jan 1', 'cron' => '0 8 1 1,7 *', 'description' => 'Semi-annual investment review', 'category' => 'Investment'],
            ['id' => 'products-semi-annual-review', 'command' => 'products:semi-annual-review', 'schedule' => '8:00 AM on Jul 1 & Jan 1', 'cron' => '0 8 1 1,7 *', 'description' => 'Semi-annual product review', 'category' => 'Products'],
            ['id' => 'branches-semi-annual-assessment', 'command' => 'branches:semi-annual-assessment', 'schedule' => '8:00 AM on Jul 1 & Jan 1', 'cron' => '0 8 1 1,7 *', 'description' => 'Semi-annual branch assessment', 'category' => 'Branches'],
            ['id' => 'procurement-semi-annual-audit', 'command' => 'procurement:semi-annual-audit', 'schedule' => '8:00 AM on Jul 1 & Jan 1', 'cron' => '0 8 1 1,7 *', 'description' => 'Semi-annual procurement audit', 'category' => 'Procurement'],
            ['id' => 'insurance-semi-annual-review', 'command' => 'insurance:semi-annual-review', 'schedule' => '8:00 AM on Jul 1 & Jan 1', 'cron' => '0 8 1 1,7 *', 'description' => 'Semi-annual insurance review', 'category' => 'Insurance'],
            ['id' => 'system-semi-annual-review', 'command' => 'system:semi-annual-review', 'schedule' => '8:00 AM on Jul 1 & Jan 1', 'cron' => '0 8 1 1,7 *', 'description' => 'Semi-annual system review', 'category' => 'System'],
            ['id' => 'accounting-semi-annual-audit', 'command' => 'accounting:semi-annual-audit', 'schedule' => '8:00 AM on Jul 1 & Jan 1', 'cron' => '0 8 1 1,7 *', 'description' => 'Semi-annual accounting audit', 'category' => 'Accounting'],
        ];
    }

    /**
     * Annual Activities
     * From: Annual Activities section + Component-Specific Annual tasks
     */
    public static function getAnnualTasks(): array
    {
        return [
            // === Year-End Processing (December) ===
            ['id' => 'product-annual-rate-reset', 'command' => 'products:annual-rate-reset', 'schedule' => '8:00 AM on Dec 15', 'cron' => '0 8 15 12 *', 'description' => 'Interest rate reviews', 'category' => 'Products'],
            ['id' => 'loans-year-end-provisions', 'command' => 'loans:year-end-provisions', 'schedule' => '8:00 AM on Dec 20', 'cron' => '0 8 20 12 *', 'description' => 'Final provision calculations', 'category' => 'Loans'],
            ['id' => 'reports-annual-statements', 'command' => 'reports:annual-statements', 'schedule' => '8:00 AM on Dec 28', 'cron' => '0 8 28 12 *', 'description' => 'Year-end financials', 'category' => 'Reports'],
            ['id' => 'accounting-year-close', 'command' => 'accounting:year-close', 'schedule' => '11:00 PM on Dec 31', 'cron' => '0 23 31 12 *', 'description' => 'Close financial year', 'category' => 'Accounting'],

            // === January Activities ===
            ['id' => 'accounting-new-year-open', 'command' => 'accounting:new-year-open', 'schedule' => '8:00 AM on Jan 1', 'cron' => '0 8 1 1 *', 'description' => 'Open new financial year', 'category' => 'Accounting'],
            ['id' => 'budget-annual-kickoff', 'command' => 'budget:annual-kickoff', 'schedule' => '8:00 AM on Jan 2', 'cron' => '0 8 2 1 *', 'description' => 'Annual budget cycle', 'category' => 'Budget'],
            ['id' => 'members-kyc-refresh', 'command' => 'members:kyc-refresh-cycle', 'schedule' => '8:00 AM on Jan 5', 'cron' => '0 8 5 1 *', 'description' => 'Annual KYC review', 'category' => 'Members'],
            ['id' => 'compliance-annual-filing', 'command' => 'compliance:annual-filing', 'schedule' => '8:00 AM on Jan 10', 'cron' => '0 8 10 1 *', 'description' => 'BOT annual returns', 'category' => 'Compliance'],
            ['id' => 'investment-policy-annual-review', 'command' => 'investment:policy-annual-review', 'schedule' => '8:00 AM on Jan 15', 'cron' => '0 8 15 1 *', 'description' => 'Annual policy review', 'category' => 'Investment'],

            // === Annual Reviews & Audits ===
            ['id' => 'accounting-annual-audit', 'command' => 'accounting:annual-audit', 'schedule' => '8:00 AM on Feb 1', 'cron' => '0 8 1 2 *', 'description' => 'External audit support', 'category' => 'Accounting'],
            ['id' => 'compliance-annual-audit', 'command' => 'compliance:annual-audit', 'schedule' => '8:00 AM on Feb 15', 'cron' => '0 8 15 2 *', 'description' => 'Full compliance review', 'category' => 'Compliance'],
            ['id' => 'policy-annual-review', 'command' => 'system:policy-annual-review', 'schedule' => '8:00 AM on Mar 1', 'cron' => '0 8 1 3 *', 'description' => 'Policy document updates', 'category' => 'System'],
            ['id' => 'strategic-planning', 'command' => 'management:strategic-planning', 'schedule' => '8:00 AM on Mar 15', 'cron' => '0 8 15 3 *', 'description' => 'Annual strategy setting', 'category' => 'System'],
            ['id' => 'accounting-tax-reporting', 'command' => 'accounting:tax-reporting', 'schedule' => '8:00 AM on Apr 1', 'cron' => '0 8 1 4 *', 'description' => 'Annual tax filings', 'category' => 'Accounting'],
            ['id' => 'share-dividend-distribution', 'command' => 'shares:annual-dividend-distribution', 'schedule' => '8:00 AM on Apr 15', 'cron' => '0 8 15 4 *', 'description' => 'Annual member dividends', 'category' => 'Shares'],
            ['id' => 'members-agm-notification', 'command' => 'members:agm-notification', 'schedule' => '8:00 AM on May 1', 'cron' => '0 8 1 5 *', 'description' => 'Annual General Meeting', 'category' => 'Members'],
            ['id' => 'procurement-vendor-renewals', 'command' => 'procurement:vendor-renewals', 'schedule' => '8:00 AM on Jun 1', 'cron' => '0 8 1 6 *', 'description' => 'Annual vendor reviews', 'category' => 'Procurement'],
            ['id' => 'system-capacity-planning', 'command' => 'system:capacity-planning', 'schedule' => '8:00 AM on Jul 1', 'cron' => '0 8 1 7 *', 'description' => 'Infrastructure planning', 'category' => 'System'],
            ['id' => 'hr-training-programs', 'command' => 'hr:training-programs', 'schedule' => '8:00 AM on Aug 1', 'cron' => '0 8 1 8 *', 'description' => 'Annual training plans', 'category' => 'HR'],

            // === Component 2: Shares - Annually ===
            ['id' => 'shares-annual-distribution', 'command' => 'shares:annual-distribution', 'schedule' => '8:00 AM on Apr 15', 'cron' => '0 8 15 4 *', 'description' => 'Annual dividend distribution', 'category' => 'Shares'],

            // === Component 3: Savings - Annually ===
            ['id' => 'savings-tax-reporting', 'command' => 'savings:tax-reporting', 'schedule' => '8:00 AM on Apr 1', 'cron' => '0 8 1 4 *', 'description' => 'Tax reporting', 'category' => 'Savings'],
            ['id' => 'savings-rate-review', 'command' => 'savings:rate-review', 'schedule' => '8:00 AM on Dec 15', 'cron' => '0 8 15 12 *', 'description' => 'Rate reviews', 'category' => 'Savings'],

            // === Component 4: Deposits - Annually ===
            ['id' => 'deposits-policy-review', 'command' => 'deposits:policy-review', 'schedule' => '8:00 AM on Mar 1', 'cron' => '0 8 1 3 *', 'description' => 'Policy reviews', 'category' => 'Deposits'],
            ['id' => 'deposits-rate-optimization', 'command' => 'deposits:rate-optimization', 'schedule' => '8:00 AM on Dec 15', 'cron' => '0 8 15 12 *', 'description' => 'Rate optimization', 'category' => 'Deposits'],

            // === Component 5: Loans - Annually ===
            ['id' => 'loans-write-off-review', 'command' => 'loans:write-off-review', 'schedule' => '8:00 AM on Nov 1', 'cron' => '0 8 1 11 *', 'description' => 'Write-off reviews', 'category' => 'Loans'],
            ['id' => 'loans-policy-updates', 'command' => 'loans:policy-updates', 'schedule' => '8:00 AM on Dec 1', 'cron' => '0 8 1 12 *', 'description' => 'Policy updates', 'category' => 'Loans'],

            // === Component 6: Accounting - Annually ===
            ['id' => 'accounting-audit-support', 'command' => 'accounting:audit-support', 'schedule' => '8:00 AM on Feb 1', 'cron' => '0 8 1 2 *', 'description' => 'Audit support', 'category' => 'Accounting'],

            // === Component 8: HR/Payroll - Annually ===
            ['id' => 'hr-tax-returns', 'command' => 'hr:tax-returns', 'schedule' => '8:00 AM on Apr 1', 'cron' => '0 8 1 4 *', 'description' => 'Tax returns', 'category' => 'HR'],
            ['id' => 'hr-benefits-enrollment', 'command' => 'hr:benefits-enrollment', 'schedule' => '8:00 AM on Nov 15', 'cron' => '0 8 15 11 *', 'description' => 'Benefits enrollment', 'category' => 'HR'],

            // === Component 9: Budget - Annually ===
            ['id' => 'budget-preparation-cycle', 'command' => 'budget:preparation-cycle', 'schedule' => '8:00 AM on Oct 1', 'cron' => '0 8 1 10 *', 'description' => 'Budget preparation cycle', 'category' => 'Budget'],

            // === Component 10: Insurance - Annually ===
            ['id' => 'insurance-policy-renewals', 'command' => 'insurance:policy-renewals', 'schedule' => '8:00 AM on Dec 1', 'cron' => '0 8 1 12 *', 'description' => 'Policy renewals', 'category' => 'Insurance'],
            ['id' => 'insurance-actuarial-review', 'command' => 'insurance:actuarial-review', 'schedule' => '8:00 AM on Jun 1', 'cron' => '0 8 1 6 *', 'description' => 'Actuarial reviews', 'category' => 'Insurance'],

            // === Component 11: Billing - Annually ===
            ['id' => 'billing-rate-review', 'command' => 'billing:rate-review', 'schedule' => '8:00 AM on Dec 1', 'cron' => '0 8 1 12 *', 'description' => 'Rate reviews', 'category' => 'Billing'],
            ['id' => 'billing-policy-updates', 'command' => 'billing:policy-updates', 'schedule' => '8:00 AM on Dec 15', 'cron' => '0 8 15 12 *', 'description' => 'Policy updates', 'category' => 'Billing'],

            // === Component 16: Reports - Annually ===
            ['id' => 'reports-annual-reports', 'command' => 'reports:annual-reports', 'schedule' => '8:00 AM on Feb 1', 'cron' => '0 8 1 2 *', 'description' => 'Annual reports', 'category' => 'Reports'],
            ['id' => 'reports-audit-packages', 'command' => 'reports:audit-packages', 'schedule' => '8:00 AM on Feb 15', 'cron' => '0 8 15 2 *', 'description' => 'Audit packages', 'category' => 'Reports'],

            // === Component 17: Members Portal - Annually ===
            ['id' => 'members-anniversary-communications', 'command' => 'members:anniversary-communications', 'schedule' => '8:00 AM on Jan 1', 'cron' => '0 8 1 1 *', 'description' => 'Anniversary communications', 'category' => 'Members'],

            // === Component 19: Expenses - Annually ===
            ['id' => 'expenses-policy-review', 'command' => 'expenses:policy-review', 'schedule' => '8:00 AM on Dec 1', 'cron' => '0 8 1 12 *', 'description' => 'Policy reviews', 'category' => 'Expenses'],

            // === Component 21: Investment - Annually ===
            ['id' => 'investment-tax-reporting', 'command' => 'investment:tax-reporting', 'schedule' => '8:00 AM on Apr 1', 'cron' => '0 8 1 4 *', 'description' => 'Tax reporting', 'category' => 'Investment'],

            // === Component 22: Procurement - Annually ===
            ['id' => 'procurement-vendor-audit', 'command' => 'procurement:vendor-audit', 'schedule' => '8:00 AM on Jul 1', 'cron' => '0 8 1 7 *', 'description' => 'Vendor audits', 'category' => 'Procurement'],
            ['id' => 'procurement-policy-review', 'command' => 'procurement:policy-review', 'schedule' => '8:00 AM on Dec 1', 'cron' => '0 8 1 12 *', 'description' => 'Policy reviews', 'category' => 'Procurement'],

            // === Component 23: Products Management - Annually ===
            ['id' => 'products-policy-review', 'command' => 'products:policy-review', 'schedule' => '8:00 AM on Dec 1', 'cron' => '0 8 1 12 *', 'description' => 'Policy reviews', 'category' => 'Products'],
            ['id' => 'products-new-planning', 'command' => 'products:new-planning', 'schedule' => '8:00 AM on Oct 1', 'cron' => '0 8 1 10 *', 'description' => 'New product planning', 'category' => 'Products'],

            // === Component 24: Branches - Annually ===
            ['id' => 'branches-strategic-planning', 'command' => 'branches:strategic-planning', 'schedule' => '8:00 AM on Nov 1', 'cron' => '0 8 1 11 *', 'description' => 'Strategic planning', 'category' => 'Branches'],
        ];
    }

    /**
     * Get all unique categories
     */
    public static function getCategories(): array
    {
        return [
            'Accounting', 'Approvals', 'Billing', 'Branches', 'Budget',
            'Cash', 'Compliance', 'Dashboard', 'Deposits', 'Expenses',
            'HR', 'Insurance', 'Investment', 'Loans', 'Maintenance',
            'Members', 'Notifications', 'Payments', 'Procurement',
            'Products', 'Reconciliation', 'Reports', 'Savings',
            'Security', 'Shares', 'System', 'Teller', 'Transactions'
        ];
    }

    /**
     * Get category color mappings
     */
    public static function getCategoryColors(): array
    {
        return [
            'Accounting' => 'bg-emerald-100 text-emerald-800',
            'Approvals' => 'bg-amber-100 text-amber-800',
            'Billing' => 'bg-lime-100 text-lime-800',
            'Branches' => 'bg-cyan-100 text-cyan-800',
            'Budget' => 'bg-pink-100 text-pink-800',
            'Cash' => 'bg-green-100 text-green-800',
            'Compliance' => 'bg-red-100 text-red-800',
            'Dashboard' => 'bg-sky-100 text-sky-800',
            'Deposits' => 'bg-teal-100 text-teal-800',
            'Expenses' => 'bg-rose-100 text-rose-800',
            'HR' => 'bg-fuchsia-100 text-fuchsia-800',
            'Insurance' => 'bg-violet-100 text-violet-800',
            'Investment' => 'bg-indigo-100 text-indigo-800',
            'Loans' => 'bg-orange-100 text-orange-800',
            'Maintenance' => 'bg-yellow-100 text-yellow-800',
            'Members' => 'bg-red-100 text-red-800',
            'Notifications' => 'bg-purple-100 text-purple-800',
            'Payments' => 'bg-green-100 text-green-800',
            'Procurement' => 'bg-stone-100 text-stone-800',
            'Products' => 'bg-slate-100 text-slate-800',
            'Reconciliation' => 'bg-indigo-100 text-indigo-800',
            'Reports' => 'bg-red-100 text-red-800',
            'Savings' => 'bg-teal-100 text-teal-800',
            'Security' => 'bg-red-100 text-red-800',
            'Shares' => 'bg-amber-100 text-amber-800',
            'System' => 'bg-purple-100 text-purple-800',
            'Teller' => 'bg-cyan-100 text-cyan-800',
            'Transactions' => 'bg-green-100 text-green-800',
        ];
    }
}
