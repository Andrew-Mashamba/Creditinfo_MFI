# Scheduled Reports System

This document describes the automated scheduled reports system for NBC SACCOS.

## Overview

The scheduled reports system automatically generates and distributes reports to designated recipients based on their roles. Reports are sent via email at configured intervals.

## Command

```bash
php artisan reports:scheduled-generation
```

### Options

| Option | Description |
|--------|-------------|
| `--dry-run` | Run without sending emails or updating records |
| `--type=all\|user\|system` | Type of reports to process (default: all) |
| `--force` | Force generation even if not due |
| `--report=<report_type>` | Generate specific system report type |

### Examples

```bash
# Run all scheduled reports
php artisan reports:scheduled-generation

# Dry run to preview what would be sent
php artisan reports:scheduled-generation --dry-run

# Force generation of all reports
php artisan reports:scheduled-generation --force

# Generate only system reports
php artisan reports:scheduled-generation --type=system

# Generate a specific report
php artisan reports:scheduled-generation --force --report=daily_operations
```

## System Reports Configuration

| Report | Frequency | Recipients |
|--------|-----------|------------|
| Daily Operations Report | Daily | Chief Operations Officer, Finance Manager, CEO/General Manager |
| Daily Arrears Report | Daily | Credit Manager, Recovery Officer, Collections Officer |
| Weekly Executive Summary | Weekly (Monday) | CEO/General Manager, Chief Finance Officer, Deputy CEO, Board Member |
| Weekly Arrears Analysis | Weekly (Monday) | Credit Manager, Senior Credit Officer, Collections Officer |
| Monthly Loan Portfolio Report | Monthly (1st) | CEO/General Manager, Chief Finance Officer, Credit Manager, Risk Manager |
| Monthly Delinquency Report | Monthly (1st) | Credit Manager, Risk Manager, Board Member |
| Monthly Trial Balance | Monthly (1st) | Chief Finance Officer, Finance Manager, Accountant, Chief Accountant |
| Monthly Income Statement | Monthly (5th) | CEO/General Manager, Chief Finance Officer, Finance Manager, Board Member |
| Quarterly Regulatory Report | Quarterly (15th) | CEO/General Manager, Chief Finance Officer, Compliance Officer, Compliance Manager |

## Report Types

### Daily Operations Report
- **Frequency:** Daily at 06:00
- **Content:** Summary of transactions, new loan applications, disbursements, and repayments
- **Compliance:** BOT, Internal

### Daily Arrears Report
- **Frequency:** Daily at 07:00
- **Content:** Daily update on loan arrears and collection status
- **Compliance:** BOT, IFRS9

### Weekly Executive Summary
- **Frequency:** Weekly on Monday at 08:00
- **Content:** Weekly performance summary including transactions, new loans, portfolio metrics, and delinquency
- **Compliance:** Internal

### Weekly Arrears Analysis
- **Frequency:** Weekly on Monday at 07:00
- **Content:** Detailed weekly arrears analysis with aging breakdown
- **Compliance:** BOT, IFRS9

### Monthly Loan Portfolio Report
- **Frequency:** Monthly on 1st at 08:00
- **Content:** Comprehensive loan portfolio analysis including product distribution and risk metrics
- **Compliance:** BOT, IFRS9

### Monthly Delinquency Report
- **Frequency:** Monthly on 1st at 08:00
- **Content:** Portfolio at Risk (PAR) analysis and delinquency metrics (PAR 1, PAR 30, PAR 60, PAR 90)
- **Compliance:** BOT, IFRS9

### Monthly Trial Balance
- **Frequency:** Monthly on 1st at 09:00
- **Content:** Summary of all GL account balances
- **Compliance:** IFRS, Internal

### Monthly Income Statement
- **Frequency:** Monthly on 5th at 09:00
- **Content:** Revenue and expense summary, net income, profit margin
- **Compliance:** IFRS, BOT

### Quarterly Regulatory Report
- **Frequency:** Quarterly on 15th (Jan, Apr, Jul, Oct) at 08:00
- **Content:** Comprehensive regulatory compliance report for Bank of Tanzania
- **Compliance:** BOT, TCDC

## Scheduler Configuration

Add the following to your scheduler (in `app/Console/Kernel.php`):

```php
protected function schedule(Schedule $schedule)
{
    // Run scheduled reports generation every hour
    $schedule->command('reports:scheduled-generation')
             ->hourly()
             ->withoutOverlapping();
}
```

## User-Scheduled Reports

In addition to system reports, users can schedule custom reports through the UI. These are stored in the `scheduled_reports` table and processed alongside system reports.

## Troubleshooting

### No Recipients Found
If you see "No recipients found for roles", ensure that:
1. Users are assigned to the appropriate roles in the system
2. Users have valid email addresses
3. User status is set to "ACTIVE"

### Report Generation Errors
Check the Laravel log file for detailed error messages:
```bash
tail -f storage/logs/laravel.log
```

## Related Files

- Command: `app/Console/Commands/GenerateScheduledReports.php`
- Mail Class: `app/Mail/SystemReportMail.php`
- Email Template: `resources/views/emails/system-report.blade.php`
- Scheduled Report Model: `app/Models/ScheduledReport.php`
