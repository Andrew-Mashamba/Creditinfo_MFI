# Scheduled Laravel Tasks

This document lists all scheduled tasks configured in the SACCOS system.

## Overview

The Laravel scheduler runs every minute via the `saccos-scheduler.service` systemd service and executes due tasks automatically.

## Task Schedule

| Schedule | Command | Description |
|----------|---------|-------------|
| Every hour (at :00) | `reports:generate-scheduled` | Generate scheduled reports |
| 2:00 PM daily | `standing-instructions:execute` | Execute standing instructions (afternoon run) |
| 2:00 AM daily | `reports:cleanup-old-files` | Clean up old report files |
| 12:05 AM daily | `system:daily-activities` | Run daily system activities (interest accrual, arrears calculation, etc.) |
| 1:00 AM daily | `nbc:daily-reconciliation` | Run NBC daily reconciliation |
| 6:00 AM daily | `standing-instructions:execute` | Execute standing instructions (morning run) |
| 6:00 AM Sundays | `reports:generate-weekly` | Generate weekly reports |
| 11:30 PM on 30th | `budget:period-close --type=monthly` | Close monthly budget period |
| 11:00 PM on 30th | `sacco:run-monthly-activities` | Run monthly SACCO activities |
| 7:00 AM on 1st | `reports:generate-monthly` | Generate monthly reports |
| 9:00 AM on 5th | `provision:cycle MONTHLY` | Run monthly loan provisioning cycle |
| 11:45 PM quarterly (1st) | `budget:period-close --type=quarterly` | Close quarterly budget period |
| 11:00 PM quarterly (1st) | `sacco:run-quarterly-activities` | Run quarterly SACCO activities |
| 9:30 AM quarterly (1st) | `provision:cycle QUARTERLY` | Run quarterly loan provisioning cycle |

## Task Categories

### Daily Tasks
- **system:daily-activities** - Core daily processing including interest accrual, arrears calculation, and account updates
- **nbc:daily-reconciliation** - Reconciliation with NBC systems
- **standing-instructions:execute** - Processes standing orders and recurring transactions (runs twice daily)
- **reports:cleanup-old-files** - Maintenance task to clean up old generated reports

### Hourly Tasks
- **reports:generate-scheduled** - Generates any reports scheduled for the current hour

### Weekly Tasks
- **reports:generate-weekly** - Generates weekly summary reports (Sundays at 6 AM)

### Monthly Tasks
- **reports:generate-monthly** - Generates monthly reports (1st of each month)
- **budget:period-close --type=monthly** - Closes the monthly budget period (30th at 11:30 PM)
- **sacco:run-monthly-activities** - Runs end-of-month SACCO processing (30th at 11:00 PM)
- **provision:cycle MONTHLY** - Calculates monthly loan provisions (5th at 9:00 AM)

### Quarterly Tasks
- **budget:period-close --type=quarterly** - Closes quarterly budget period
- **sacco:run-quarterly-activities** - Runs end-of-quarter SACCO processing
- **provision:cycle QUARTERLY** - Calculates quarterly loan provisions

## Monitoring

### Check Scheduler Status
```bash
sudo systemctl status saccos-scheduler.service
```

### View Scheduler Logs
```bash
tail -f /var/www/html/INSTANCES/nbc_saccos/core/storage/logs/scheduler.log
```

### List Scheduled Tasks
```bash
cd /var/www/html/INSTANCES/nbc_saccos/core
php artisan schedule:list
```

### Run Scheduler Manually
```bash
cd /var/www/html/INSTANCES/nbc_saccos/core
php artisan schedule:run
```

## Configuration

The scheduler is managed by systemd service located at:
```
/etc/systemd/system/saccos-scheduler.service
```

To restart the scheduler:
```bash
sudo systemctl restart saccos-scheduler.service
```

---
*Last updated: 2025-11-28*
