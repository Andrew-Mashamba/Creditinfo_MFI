# Jobs Manager

This directory contains scripts and utilities for managing Laravel queue workers and background jobs.

## Files

### queue-manager.sh
Main script for managing queue workers.

## Usage

Navigate to the project root directory and run:

```bash
# From project root
./jobs_manager/queue-manager.sh start    # Start all queue workers
./jobs_manager/queue-manager.sh stop     # Stop all queue workers
./jobs_manager/queue-manager.sh restart  # Restart all queue workers
./jobs_manager/queue-manager.sh status   # Check worker status
./jobs_manager/queue-manager.sh logs     # View recent logs
./jobs_manager/queue-manager.sh clear    # Clear failed jobs
```

Or navigate to this directory:

```bash
cd jobs_manager
./queue-manager.sh status
```

## Queue Workers

The script manages 4 queue workers:

1. **Default Queue Worker**
   - Queue: `default`
   - For general background jobs
   - Log: `storage/logs/queue-default.log`

2. **High Priority Queue Worker**
   - Queue: `high`
   - For urgent/priority tasks
   - Log: `storage/logs/queue-high.log`

3. **Notifications Queue Worker**
   - Queue: `notifications`
   - For SMS and push notifications
   - Log: `storage/logs/queue-notifications.log`

4. **Emails Queue Worker**
   - Queue: `emails`
   - For email processing
   - Log: `storage/logs/queue-emails.log`

## Files Created

- `queue-workers.pid` - Stores process IDs of running workers (in this directory)
- `storage/logs/queue-*.log` - Log files for each queue worker

## Available Jobs

The system includes the following job classes:

### Core Jobs
- `CalculatePpeDepreciation` - Calculate depreciation for PPE assets
- `EndOfDay` - End of day processing
- `FundsTransfer` - Process fund transfers
- `GenerateDailyLoanReports` - Generate daily loan reports

### Loan Jobs
- `ProcessAutomaticLoans` - Process automatic loan disbursements
- `ProcessDividendPayment` - Process dividend payments to members

### Notification Jobs
- `ProcessMemberNotifications` - Send member notifications
- `SendOtpNotification` - Send OTP notifications
- `ProcessQueuedEmails` - Process queued emails
- `SendAccountInformation` - Send account information
- `SendRepaymentEmail` - Send loan repayment emails
- `SendRepaymentSMS` - Send loan repayment SMS
- `SendTransactionNotification` - Send transaction notifications

### Reconciliation Jobs
- `Reconcilliation/*` - Various reconciliation jobs

### Other Jobs
- `ProcessCallback` - Process API callbacks
- `ProcessReversalRetry` - Retry failed reversals
- `ProcessScheduledReports` - Generate scheduled reports
- `ProcessTransactionRetry` - Retry failed transactions
- `StandingOrder/*` - Process standing orders

## Event Listeners

The system also includes event listeners that may dispatch jobs:

- `HandleApprovalProcessed` - Handles approval events
- `HandleLoanApproved` - Handles loan approval events

## Troubleshooting

### Workers not starting
- Check if workers are already running: `./queue-manager.sh status`
- Check for permission issues: `chmod +x queue-manager.sh`
- Check PHP is in PATH: `which php`

### Jobs not processing
- Check queue configuration in `.env`: `QUEUE_CONNECTION=database`
- Check database connection
- Check job table: `php artisan queue:table` (if not exists)
- Check for failed jobs: `php artisan queue:failed`

### Viewing logs
- Queue logs: `tail -f storage/logs/queue-*.log`
- Laravel logs: `tail -f storage/logs/laravel.log`

### Clearing issues
- Clear failed jobs: `./queue-manager.sh clear`
- Restart workers: `./queue-manager.sh restart`
- Clear cache: `php artisan cache:clear`

## Production Deployment

For production, consider using:
- **Supervisor** for process monitoring
- **Horizon** for Redis queues (if using Redis)
- **Systemd** service for auto-start on boot

Example supervisor config:
```ini
[program:saccos-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/project/artisan queue:work database --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/worker.log
```