# Daily System Activities Service

## Overview

The Daily System Activities Service is a comprehensive end-of-day (EOD) processing system that runs automated financial operations, compliance checks, and system maintenance tasks. It is designed to run daily at 00:05 and handles critical banking operations including:

- Loan interest accrual
- Savings interest calculations
- Loan repayment processing
- Financial reconciliation
- System maintenance
- Compliance reporting

## Architecture

### Components

```
┌─────────────────────────────────────────────────────────────┐
│                    Systemd Timer                            │
│              saccos-daily@nbc_saccos.timer                  │
│              Runs daily at 00:05                            │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│                    Wrapper Script                           │
│           /usr/local/bin/saccos-daily-activities.sh         │
│  - Retry logic (3 attempts, 5-min delay)                    │
│  - Lock file to prevent concurrent runs                     │
│  - Detailed logging                                         │
│  - Failure notifications                                    │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│                  Artisan Command                            │
│           php artisan system:daily-activities               │
│           RunDailySystemActivities.php                      │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│              DailySystemActivitiesService                   │
│    app/Services/DailySystemActivitiesService.php            │
│                                                             │
│  Orchestrates all EOD activities:                           │
│  1. Financial Core Activities                               │
│  2. Member and Compliance Activities                        │
│  3. System and Security Activities                          │
│  4. Asset and Investment Activities                         │
│  5. Document and Performance Activities                     │
│  6. Communication and Notifications                         │
│  7. Payment Notifications                                   │
│  8. Monthly Payroll Reset (1st of month)                    │
└─────────────────────────────────────────────────────────────┘
```

## Systemd Service Configuration

### Service File: `/etc/systemd/system/saccos-daily@.service`

```ini
[Unit]
Description=SACCOS Daily System Activities for %i
After=network.target postgresql.service
Wants=postgresql.service

[Service]
Type=oneshot
User=apache
Group=apache
ExecStart=/usr/local/bin/saccos-daily-activities.sh %i
TimeoutStartSec=3600
MemoryMax=1G
CPUQuota=80%
StandardOutput=journal
StandardError=journal
SyslogIdentifier=saccos-daily-%i

[Install]
WantedBy=multi-user.target
```

### Timer File: `/etc/systemd/system/saccos-daily@.timer`

```ini
[Unit]
Description=Run SACCOS Daily Activities for %i at 00:05 daily

[Timer]
OnCalendar=*-*-* 00:05:00
Persistent=true
RandomizedDelaySec=120
AccuracySec=1s
Unit=saccos-daily@%i.service

[Install]
WantedBy=timers.target
```

## Wrapper Script Features

**Location:** `/usr/local/bin/saccos-daily-activities.sh`

### Key Features:

1. **Retry Logic**
   - Maximum 3 attempts
   - 5-minute delay between retries
   - Graceful failure handling

2. **Lock File Prevention**
   - Prevents concurrent execution
   - Automatic stale lock cleanup
   - Location: `/tmp/saccos-daily-{instance}.lock`

3. **Comprehensive Logging**
   - Daily log files: `/var/www/html/INSTANCES/{instance}/core/storage/logs/daily-activities/YYYY-MM-DD.log`
   - Systemd journal integration
   - Failure alerts: `/var/log/saccos-daily-alerts.log`

## Log Files

| Log Type | Location |
|----------|----------|
| Daily Activity Logs | `/var/www/html/INSTANCES/nbc_saccos/core/storage/logs/daily-activities/YYYY-MM-DD.log` |
| Laravel Daily Log | `/var/www/html/INSTANCES/nbc_saccos/core/storage/logs/laravel-YYYY-MM-DD.log` |
| Failure Alerts | `/var/log/saccos-daily-alerts.log` |
| Systemd Journal | `journalctl -u saccos-daily@nbc_saccos.service` |

## Management Commands

### Check Timer Status
```bash
sudo systemctl status saccos-daily@nbc_saccos.timer
```

### List All Timers
```bash
sudo systemctl list-timers --all | grep saccos
```

### Run Manually
```bash
sudo systemctl start saccos-daily@nbc_saccos.service
```

### View Logs
```bash
# Systemd journal
journalctl -u saccos-daily@nbc_saccos.service -f

# Daily activity log
cat /var/www/html/INSTANCES/nbc_saccos/core/storage/logs/daily-activities/$(date +%Y-%m-%d).log

# Failure alerts
cat /var/log/saccos-daily-alerts.log
```

### Enable/Disable Timer
```bash
# Enable
sudo systemctl enable saccos-daily@nbc_saccos.timer
sudo systemctl start saccos-daily@nbc_saccos.timer

# Disable
sudo systemctl stop saccos-daily@nbc_saccos.timer
sudo systemctl disable saccos-daily@nbc_saccos.timer
```

### Reload Configuration
```bash
sudo systemctl daemon-reload
```

## Activities Performed

### 1. Financial Core Activities

#### Loan Activities
- **Loan Repayment Processing**: Automatic loan repayments from member accounts
- **Loan Status Updates**: Updates loan statuses based on arrears
- **Loan Loss Provisions**: Calculates daily provisions per BoT regulations
- **Loan Interest Accrual**: Accrual-based interest recognition (see below)
- **Daily Loan Reports**: Generates and emails loan reports

#### Savings and Deposits
- **Interest Accrual**: Daily interest calculation for savings accounts
- **Fixed Deposit Maturities**: Processes maturing fixed deposits
- **Recurring Deposits**: Processes scheduled recurring deposits

#### Share Management
- **Share Transactions**: Processes share purchases/sales
- **Member Share Balances**: Updates share balances
- **Share Movement Reports**: Generates daily reports

### 2. Loan Interest Accrual (IFRS/GAAP Compliant)

The `LoanInterestAccrualService` performs daily interest accrual:

**Calculation Formula:**
```
Daily Interest = Outstanding Principal × (Annual Rate / 365 / 100)
```

**GL Entries:**
```
DR: Interest Receivable - Current (0101100014001410)
CR: Interest Income - Current (0101400040004010)
```

**NPL Interest Suspension:**
- Loans >90 days in arrears have interest suspended
- Suspended interest tracked but not posted to GL

**Database Tables:**
- `loan_interest_accruals` - Daily accrual records per loan
- `loan_interest_accrual_summaries` - Daily summary for reporting

### 3. Member and Compliance Activities
- Member withdrawals processing
- Account status updates
- Regulatory report generation
- Tax calculations
- Audit trail generation
- Risk assessments

### 4. System and Security Activities
- Database backup
- System log cleanup
- Cache clearing
- Temporary file cleanup
- Security audit
- Access log updates
- Suspicious activity detection

### 5. Asset and Investment Activities
- Asset depreciation
- Maintenance schedules
- Investment valuations
- Portfolio updates
- Insurance policy updates

### 6. Communication and Notifications
- Trade receivables reminders
- Payment notifications
- Overdue invoice alerts

## Error Handling

### Retry Mechanism
1. First attempt runs immediately
2. On failure, waits 5 minutes
3. Second attempt
4. On failure, waits 5 minutes
5. Third (final) attempt
6. On final failure, logs alert to `/var/log/saccos-daily-alerts.log`

### Alert Notifications
When all retries fail:
```
[YYYY-MM-DD HH:MM:SS] [ALERT] Daily activities failed after 3 attempts for instance nbc_saccos
```

To add email/SMS notifications, edit `/usr/local/bin/saccos-daily-activities.sh`:
```bash
notify_failure() {
    local message="$1"
    log "ALERT" "$message"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [ALERT] $message" >> /var/log/saccos-daily-alerts.log
    
    # Add email notification
    mail -s "SACCOS Daily Activities Failed" admin@example.com <<< "$message"
}
```

## Troubleshooting

### Service Won't Start
```bash
# Check service status
sudo systemctl status saccos-daily@nbc_saccos.service

# Check for lock file
ls -la /tmp/saccos-daily-*.lock

# Remove stale lock
rm /tmp/saccos-daily-nbc_saccos.lock
```

### Check for Errors
```bash
# Recent journal entries
journalctl -u saccos-daily@nbc_saccos.service --since "1 hour ago"

# Check Laravel logs
tail -100 /var/www/html/INSTANCES/nbc_saccos/core/storage/logs/laravel-$(date +%Y-%m-%d).log
```

### Timer Not Triggering
```bash
# Check timer status
sudo systemctl status saccos-daily@nbc_saccos.timer

# Verify timer is enabled
sudo systemctl is-enabled saccos-daily@nbc_saccos.timer

# Check next trigger time
sudo systemctl list-timers saccos-daily@nbc_saccos.timer
```

### Permission Issues
```bash
# Check log directory permissions
ls -la /var/www/html/INSTANCES/nbc_saccos/core/storage/logs/

# Fix permissions
sudo chown -R apache:apache /var/www/html/INSTANCES/nbc_saccos/core/storage/logs/
```

## Performance Considerations

- **Memory Limit**: 1GB max (configurable in service file)
- **CPU Quota**: 80% max (prevents system overload)
- **Timeout**: 1 hour (includes retry attempts)
- **Random Delay**: Up to 2 minutes (prevents thundering herd)

## Adding New Instances

To add daily activities for a new SACCOS instance:

```bash
# Enable timer for new instance
sudo systemctl enable saccos-daily@new_instance.timer
sudo systemctl start saccos-daily@new_instance.timer

# Verify
sudo systemctl status saccos-daily@new_instance.timer
```

## Related Services

- **Queue Workers**: Handle async jobs dispatched during EOD
- **Laravel Scheduler**: Alternative scheduling mechanism
- **NBC Daily Reconciliation**: Bank statement reconciliation

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2025-11-27 | Initial systemd service setup |
| 1.1 | 2025-11-27 | Added retry logic and comprehensive logging |
| 1.2 | 2025-11-27 | Fixed command return format mismatch |
