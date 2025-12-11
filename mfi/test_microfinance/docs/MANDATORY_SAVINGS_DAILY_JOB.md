# Mandatory Savings Daily Processing

## Overview

The mandatory savings daily processing is now **integrated into the existing `system:daily-activities` command** that runs automatically every day at 11:55 PM.

## What the Integration Does

The `processMandatorySavings()` method in `DailySystemActivitiesService` handles:

### 1. **Generate Tracking Records** (if needed)
- Checks if tracking records exist for the current month
- Creates new tracking records for all active members if none exist
- Updates existing records if mandatory savings amount has changed

### 2. **Update from New Payments**
- Scans for new savings deposits made in the last 24 hours
- Updates tracking records with payment information
- Recalculates balances and status

### 3. **Process Overdue Records**
- Identifies payments that are past their due date
- Updates status to 'OVERDUE' for unpaid/partial payments
- Calculates days overdue

### 4. **Generate Notifications**
- Sends first, second, and final reminders based on settings
- Sends overdue notices for past due payments
- Creates notification records in the database

### 5. **Process Arrears**
- Calculates total arrears for each member
- Updates months in arrears count
- Tracks cumulative outstanding amounts

## Scheduling

The processing is now part of the existing daily system activities schedule in `app/Console/Kernel.php`:

```php
// Run daily system activities at the end of each day
$schedule->command('system:daily-activities')
    ->dailyAt('23:55')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/daily-activities.log'));
```

## Manual Execution

You can manually run the complete daily activities (including mandatory savings) using:

```bash
# Run all daily activities including mandatory savings
php artisan system:daily-activities

# Or run just the mandatory savings processing
php artisan mandatory-savings:process
```

## Integration Details

The mandatory savings processing is integrated into `DailySystemActivitiesService`:

1. **Service Injection**: `MandatorySavingsService` is injected into the constructor
2. **Method**: `processMandatorySavings()` method handles all mandatory savings logic
3. **Execution**: Called as part of `executeDailyActivities()` method
4. **Logging**: All activities are logged to the daily activities log

## Prerequisites

Before the processing can run successfully, ensure:

1. **Settings are configured** in `mandatory_savings_settings` table
2. **Institution has mandatory savings account** configured
3. **Cron job is set up** to run Laravel scheduler

## Cron Setup

For the schedule to actually run, add this cron entry on your server:

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## Logging

All mandatory savings processing activities are logged to:
- `storage/logs/daily-activities.log` (as part of daily activities)
- `storage/logs/laravel.log` (general Laravel logs)

## Error Handling

- Errors are caught and logged within the daily activities transaction
- If mandatory savings processing fails, it won't affect other daily activities
- All errors are logged for debugging

## Testing

Test the integration:

```bash
# Test the complete daily activities
php artisan system:daily-activities

# Check logs
tail -f storage/logs/daily-activities.log

# Verify database changes
php artisan tinker
>>> App\Models\MandatorySavingsTracking::count()
>>> App\Models\MandatorySavingsNotification::count()
```

## Benefits of Integration

1. **Single Point of Execution**: All daily activities run together
2. **Consistent Logging**: All activities logged to the same file
3. **Transaction Safety**: Runs within the daily activities transaction
4. **Simplified Scheduling**: No need for separate job scheduling
5. **Better Monitoring**: Can monitor all daily activities together 