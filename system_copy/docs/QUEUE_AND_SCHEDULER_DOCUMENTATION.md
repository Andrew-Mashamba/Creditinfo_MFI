# Queue Workers and Scheduler Documentation
**SACCOS Core System - Background Job Processing**

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Queue Workers](#queue-workers)
4. [Laravel Scheduler](#laravel-scheduler)
5. [Systemd Services](#systemd-services)
6. [Event Listeners](#event-listeners)
7. [Monitoring and Logs](#monitoring-and-logs)
8. [Troubleshooting](#troubleshooting)
9. [Maintenance](#maintenance)

## Overview

The SACCOS system uses Laravel's queue system to handle background jobs and scheduled tasks. This ensures that time-intensive operations don't block the main application flow and provides reliable job processing with automatic retries and failure handling.

### Key Features
- **Asynchronous Processing**: Heavy tasks run in the background
- **Automatic Retries**: Failed jobs retry up to 3 times
- **Multiple Queues**: Separate queues for different job priorities
- **Persistent Services**: Systemd ensures services run 24/7
- **Auto-restart**: Services automatically restart on failure
- **Scheduled Tasks**: Cron-like task scheduling via Laravel

## Architecture

```
┌─────────────────────────────────────────────────────┐
│                   Application Layer                  │
├─────────────────────────────────────────────────────┤
│  Events  │  Jobs  │  Notifications  │  Commands     │
└──────────┴────────┴─────────────────┴───────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────┐
│                   Queue System                       │
├─────────────────────────────────────────────────────┤
│  Default  │  High  │  Emails  │  Notifications      │
└───────────┴────────┴──────────┴────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────┐
│                 Queue Workers                        │
├─────────────────────────────────────────────────────┤
│  Worker 1  │  Worker 2  │  Worker 3  │  Worker N    │
└────────────┴───────────┴────────────┴──────────────┘
```

## Queue Workers

### Available Queues

| Queue Name | Purpose | Priority | Timeout |
|------------|---------|----------|---------|
| `default` | General background jobs | Normal | 90s |
| `high` | Critical operations | High | 90s |
| `emails` | Email notifications | Normal | 60s |
| `notifications` | SMS/Push notifications | Normal | 60s |

### Queue Configuration

The queue system uses database driver for reliability:

```env
QUEUE_CONNECTION=database
QUEUE_DRIVER=database
```

### Worker Commands

#### Start a single worker
```bash
php artisan queue:work
```

#### Start worker for specific queue
```bash
php artisan queue:work --queue=high,default
```

#### Start worker with options
```bash
php artisan queue:work --daemon --tries=3 --timeout=90 --sleep=3
```

### Worker Options

| Option | Description | Default |
|--------|-------------|---------|
| `--queue` | Queue names to process | default |
| `--tries` | Number of retry attempts | 3 |
| `--timeout` | Maximum job runtime (seconds) | 90 |
| `--sleep` | Sleep when no jobs (seconds) | 3 |
| `--max-time` | Maximum worker runtime (seconds) | 3600 |
| `--daemon` | Run in daemon mode | false |

## Laravel Scheduler

### Overview
The scheduler runs every minute and executes scheduled commands defined in `app/Console/Kernel.php`.

### Scheduled Tasks

| Task | Schedule | Description |
|------|----------|-------------|
| `reports:generate-scheduled` | Every minute | Process scheduled reports |
| `reports:generate-daily` | Daily at 02:00 | Generate daily reports |
| `reports:generate-weekly` | Sundays at 06:00 | Generate weekly reports |
| `reports:generate-monthly` | 1st of month at 07:00 | Generate monthly reports |

### Manual Scheduler Run
```bash
php artisan schedule:run
```

### Scheduler Testing
```bash
php artisan schedule:list
php artisan schedule:test
```

## Systemd Services

### Service Files Location
All service files are located in `/etc/systemd/system/`

### 1. Main Queue Worker Service
**File**: `saccos-queue-worker.service`

```ini
[Unit]
Description=SACCOS Queue Worker
After=network.target

[Service]
Type=simple
User=apache
Group=apache
Restart=always
RestartSec=5
WorkingDirectory=/var/www/html/template
ExecStart=/usr/bin/php /var/www/html/template/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

### 2. Queue-Specific Workers
**File**: `saccos-queue-worker@.service` (Template service)

This template allows multiple instances for different queues:
- `saccos-queue-worker@high.service`
- `saccos-queue-worker@emails.service`
- `saccos-queue-worker@notifications.service`

### 3. Scheduler Service
**File**: `saccos-scheduler.service`

```ini
[Unit]
Description=SACCOS Laravel Scheduler
After=network.target

[Service]
Type=simple
User=apache
Group=apache
Restart=always
RestartSec=5
WorkingDirectory=/var/www/html/template
ExecStart=/bin/bash -c 'while true; do /usr/bin/php /var/www/html/template/artisan schedule:run >> /var/www/html/template/storage/logs/scheduler.log 2>&1; sleep 60; done'

[Install]
WantedBy=multi-user.target
```

### Service Management Commands

#### Enable services (auto-start on boot)
```bash
systemctl enable saccos-queue-worker.service
systemctl enable saccos-scheduler.service
systemctl enable saccos-queue-worker@high.service
systemctl enable saccos-queue-worker@emails.service
systemctl enable saccos-queue-worker@notifications.service
```

#### Start services
```bash
systemctl start saccos-queue-worker.service
systemctl start saccos-scheduler.service
systemctl start saccos-queue-worker@high.service
systemctl start saccos-queue-worker@emails.service
systemctl start saccos-queue-worker@notifications.service
```

#### Stop services
```bash
systemctl stop saccos-queue-worker.service
systemctl stop saccos-scheduler.service
```

#### Restart services
```bash
systemctl restart saccos-queue-worker.service
systemctl restart saccos-scheduler.service
```

#### Check service status
```bash
systemctl status saccos-queue-worker.service
systemctl status saccos-scheduler.service
```

#### View service logs
```bash
journalctl -u saccos-queue-worker.service -f
journalctl -u saccos-scheduler.service -f
```

## Event Listeners

### Available Listeners

| Listener | Event | Purpose |
|----------|-------|---------|
| `HandleApprovalProcessed` | `ApprovalProcessed` | Process approval workflows |
| `HandleLoanApproved` | `LoanApproved` | Handle loan approval actions |

### Listener Location
```
/var/www/html/template/app/Listeners/
├── HandleApprovalProcessed.php
└── HandleLoanApproved.php
```

### Creating New Listeners
```bash
php artisan make:listener ListenerName --event=EventName
```

## Monitoring and Logs

### Log Files

| Service | Log Location |
|---------|--------------|
| Main Queue Worker | System journal (`journalctl -u saccos-queue-worker`) |
| Scheduler | `/var/www/html/template/storage/logs/scheduler.log` |
| Laravel Queue | `/var/www/html/template/storage/logs/laravel.log` |
| Failed Jobs | Database table: `failed_jobs` |

### Monitoring Commands

#### Check queue status
```bash
php artisan queue:monitor default:100,high:50,emails:100,notifications:100
```

#### View failed jobs
```bash
php artisan queue:failed
```

#### Retry failed jobs
```bash
php artisan queue:retry all
php artisan queue:retry {job-id}
```

#### Clear failed jobs
```bash
php artisan queue:flush
```

#### Check pending jobs
```sql
SELECT queue, COUNT(*) as count 
FROM jobs 
GROUP BY queue;
```

### Real-time Monitoring
```bash
# Watch queue worker processes
watch -n 1 'ps aux | grep "artisan queue" | grep -v grep'

# Monitor service status
watch -n 5 'systemctl status saccos-queue-worker.service --no-pager'

# Tail scheduler log
tail -f /var/www/html/template/storage/logs/scheduler.log

# Monitor jobs table
watch -n 5 'php artisan tinker --execute="echo \"Pending: \" . DB::table(\"jobs\")->count() . \" | Failed: \" . DB::table(\"failed_jobs\")->count();"'
```

## Troubleshooting

### Common Issues and Solutions

#### 1. Queue Workers Not Processing Jobs

**Symptoms**: Jobs stuck in `jobs` table

**Solutions**:
```bash
# Check if workers are running
ps aux | grep "artisan queue" | grep -v grep

# Restart workers
systemctl restart saccos-queue-worker.service

# Check for errors
journalctl -u saccos-queue-worker.service -n 50

# Clear cache
php artisan cache:clear
php artisan config:clear
```

#### 2. Scheduler Not Running

**Symptoms**: Scheduled tasks not executing

**Solutions**:
```bash
# Check scheduler service
systemctl status saccos-scheduler.service

# Test scheduler manually
php artisan schedule:run

# Check scheduler log
tail -n 50 /var/www/html/template/storage/logs/scheduler.log

# Restart scheduler
systemctl restart saccos-scheduler.service
```

#### 3. Jobs Failing Repeatedly

**Symptoms**: Jobs in `failed_jobs` table

**Solutions**:
```bash
# View failed job details
php artisan queue:failed

# Retry specific job
php artisan queue:retry {job-id}

# Check job timeout
# Increase timeout in service file if needed

# Check memory limit
php -i | grep memory_limit
```

#### 4. Service Won't Start

**Symptoms**: Service fails to start

**Solutions**:
```bash
# Check service status
systemctl status saccos-queue-worker.service

# Check permissions
ls -la /var/www/html/template/
chown -R apache:apache /var/www/html/template/storage

# Reload systemd
systemctl daemon-reload

# Check PHP path
which php
```

## Maintenance

### Daily Tasks
1. Monitor failed jobs count
2. Check service status
3. Review error logs

### Weekly Tasks
1. Clear old logs
2. Analyze job performance
3. Review queue metrics

### Monthly Tasks
1. Optimize database tables
2. Archive old job records
3. Update queue configurations if needed

### Cleanup Commands

#### Clear old completed jobs (older than 30 days)
```sql
DELETE FROM jobs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

#### Archive failed jobs
```bash
php artisan tinker
>>> DB::table('failed_jobs_archive')->insert(
    DB::table('failed_jobs')->where('failed_at', '<', now()->subDays(30))->get()->toArray()
);
>>> DB::table('failed_jobs')->where('failed_at', '<', now()->subDays(30))->delete();
```

#### Rotate logs
```bash
# Add to crontab
0 0 * * 0 /usr/bin/find /var/www/html/template/storage/logs -name "*.log" -mtime +30 -delete
```

### Performance Optimization

#### 1. Queue Table Indexes
```sql
-- Add indexes for better performance
ALTER TABLE jobs ADD INDEX queue_reserved_at_index (queue, reserved_at);
ALTER TABLE failed_jobs ADD INDEX failed_at_index (failed_at);
```

#### 2. Worker Scaling
```bash
# Add more workers for high-load queues
systemctl enable saccos-queue-worker@high-2.service
systemctl enable saccos-queue-worker@high-3.service
systemctl start saccos-queue-worker@high-2.service
systemctl start saccos-queue-worker@high-3.service
```

#### 3. Memory Management
```ini
# Edit service file to limit memory
[Service]
MemoryLimit=512M
MemoryMax=512M
```

## Security Considerations

1. **User Permissions**: All services run as `apache` user
2. **File Permissions**: Ensure proper permissions on storage directories
3. **Log Security**: Regularly review logs for sensitive data
4. **Job Validation**: Always validate job data before processing
5. **Rate Limiting**: Implement rate limiting for job dispatching

## Best Practices

1. **Job Design**
   - Keep jobs small and focused
   - Use job chaining for complex workflows
   - Implement proper error handling
   - Add logging for debugging

2. **Queue Management**
   - Use appropriate queue for job type
   - Set reasonable timeouts
   - Monitor queue depth
   - Scale workers based on load

3. **Error Handling**
   - Implement retry logic
   - Log failures comprehensively
   - Set up alerts for critical failures
   - Have fallback mechanisms

4. **Performance**
   - Batch similar operations
   - Use database transactions wisely
   - Optimize database queries in jobs
   - Monitor memory usage

## Contact and Support

For issues or questions regarding the queue system:
1. Check this documentation
2. Review Laravel Queue documentation
3. Check system logs
4. Contact system administrator

---

**Last Updated**: September 2, 2025  
**Version**: 1.0  
**Maintained by**: SACCOS Development Team