# Audit Trail - Quick Reference Card

## What is an Audit Trail?

A **chronological record** of all system activities that tracks:
- **WHO** did it (user)
- **WHAT** was done (action)
- **WHEN** it happened (timestamp)
- **WHERE** from (IP address)
- **WHY** it was done (context/reason)

---

## How It Works

Your system already logs everything to `storage/logs/laravel-YYYY-MM-DD.log` files.

We've added:
1. **AuditLogService** - Easy way to log actions
2. **logs:analyze command** - Tool to analyze logs

---

## Quick Start - 3 Steps

### 1. Add Logging to Your Code

```php
use App\Services\AuditLogService;

// Log creation
AuditLogService::created('Member', $member->id, $member->toArray());

// Log update
AuditLogService::updated('Account', $account->id, $oldData, $newData);

// Log deletion
AuditLogService::deleted('Transaction', $transaction->id, $transaction->toArray());

// Log custom action
AuditLogService::loan('APPROVED', $loan->id, [
    'amount' => $loan->amount,
    'approver' => auth()->user()->name
]);
```

### 2. View Logs

```bash
# Analyze today's logs
php artisan logs:analyze --date=2025-10-11

# Analyze last 7 days
php artisan logs:analyze

# Find specific user's actions
php artisan logs:analyze --user=12

# Export to CSV
php artisan logs:analyze --from=2025-10-01 --to=2025-10-31 --export=csv
```

### 3. Generate Reports

```bash
# Monthly report
php artisan logs:analyze --from=2025-10-01 --to=2025-10-31 --export=csv

# Security report (errors and warnings)
php artisan logs:analyze --level=WARNING --from=2025-10-01 --export=csv

# User activity report
php artisan logs:analyze --user=12 --from=2025-10-01 --export=csv
```

---

## Common Commands

```bash
# Today's activity
php artisan logs:analyze --date=$(date +%Y-%m-%d)

# Last 7 days
php artisan logs:analyze

# Last 30 days
php artisan logs:analyze --from=$(date -d '30 days ago' +%Y-%m-%d)

# Find deletions
php artisan logs:analyze --search="DELETED"

# Find approvals
php artisan logs:analyze --search="APPROVED"

# Find errors
php artisan logs:analyze --level=ERROR

# User 12's actions today
php artisan logs:analyze --user=12 --date=$(date +%Y-%m-%d)

# Export October 2025 report
php artisan logs:analyze --from=2025-10-01 --to=2025-10-31 --export=csv
```

---

## Available Methods

### AuditLogService Methods

```php
// General logging
AuditLogService::log($action, $entity, $entityId, $context);

// Specific actions
AuditLogService::created($entity, $id, $data);
AuditLogService::updated($entity, $id, $oldData, $newData);
AuditLogService::deleted($entity, $id, $data);

// User actions
AuditLogService::login($userId, $successful);
AuditLogService::logout($userId);
AuditLogService::roleChanged($userId, $oldRole, $newRole);
AuditLogService::permissionChanged($userId, $oldPerms, $newPerms);

// Business actions
AuditLogService::transaction($action, $transactionId, $context);
AuditLogService::loan($action, $loanId, $context);
AuditLogService::account($action, $accountId, $context);
AuditLogService::member($action, $memberId, $context);

// System actions
AuditLogService::dataExport($type, $context);
AuditLogService::securityEvent($event, $context);
AuditLogService::systemEvent($event, $context);
```

---

## Command Options

```
logs:analyze
  --date=YYYY-MM-DD       Specific date
  --from=YYYY-MM-DD       Start date
  --to=YYYY-MM-DD         End date
  --user=123              Filter by user ID
  --level=ERROR           Filter by level (INFO, WARNING, ERROR)
  --search=term           Search in messages
  --export=format         Export (csv, json, txt)
  --type=log_type         Log type (laravel, transactions, payments)
  --limit=100             Limit results
```

---

## Usage Examples

### Track Member Changes

```php
public function update(Request $request, Member $member)
{
    $oldData = $member->only(['status', 'email']);

    $member->update($request->validated());

    AuditLogService::updated('Member', $member->id, $oldData, [
        'status' => $member->status,
        'email' => $member->email
    ]);
}
```

### Track Loan Approvals

```php
public function approve(Loan $loan)
{
    AuditLogService::loan('APPROVED', $loan->id, [
        'amount' => $loan->amount,
        'approver' => auth()->user()->name,
        'comments' => request('comments')
    ]);

    $loan->update(['status' => 'approved']);
}
```

### Track Suspensions

```php
public function suspend(Account $account, $reason)
{
    AuditLogService::account('SUSPENDED', $account->id, [
        'reason' => $reason,
        'balance' => $account->balance,
        'previous_status' => $account->status
    ]);

    $account->update(['status' => 'suspended']);
}
```

### Track Data Exports

```php
public function export()
{
    $members = Member::all();

    AuditLogService::dataExport('Members', [
        'count' => $members->count(),
        'format' => 'Excel'
    ]);

    return Excel::download(new MembersExport($members), 'members.xlsx');
}
```

---

## Compliance Reports

### Bank of Tanzania (BOT) - 7 Year Retention

```bash
# Check log retention
find storage/logs -name "*.log" -type f -mtime +2555 | wc -l

# Archive old logs
tar -czf logs-archive-$(date +%Y%m%d).tar.gz storage/logs/*.log

# Clean logs older than 7 years
find storage/logs -name "*.log" -mtime +2555 -delete
```

### Monthly Audit Report

```bash
# Generate monthly report
php artisan logs:analyze \
    --from=2025-10-01 \
    --to=2025-10-31 \
    --export=csv

# Location: storage/app/exports/audit-logs-2025-10-01-to-2025-10-31.csv
```

### Critical Actions Report

```bash
# All deletions
php artisan logs:analyze --search="DELETED" --from=2025-10-01 --export=csv

# All approvals
php artisan logs:analyze --search="APPROVED" --from=2025-10-01 --export=csv

# All errors
php artisan logs:analyze --level=ERROR --from=2025-10-01 --export=csv

# All warnings
php artisan logs:analyze --level=WARNING --from=2025-10-01 --export=csv
```

---

## What to Log

### ✅ High Priority (Must Log)

- User login/logout
- Permission/role changes
- Financial transactions (all operations)
- Loan approvals/rejections
- Account status changes
- Data exports
- Record deletions
- Failed authentication
- System configuration changes

### ⚠️ Medium Priority

- Member record updates
- Report generation
- Bulk operations
- Payment processing

### ℹ️ Low Priority (Optional)

- Page views
- Search queries
- Menu navigation

---

## Log Locations

```
storage/logs/
├── laravel-2025-10-11.log          # Main logs (daily rotation)
├── email-2025-10-11.log            # Email logs
├── otp-2025-10-11.log              # OTP logs
├── transactions-2025-10-11.log     # Transaction logs
├── payments/
│   └── payments-2025-10-11.log     # Payment logs
├── gepg/
│   └── gepg-2025-10-11.log         # GEPG logs
├── queue-worker.log                 # Queue logs
└── nbc-daily-reconciliation.log     # Reconciliation logs
```

---

## Troubleshooting

### No logs found

```bash
# Check log directory
ls -la storage/logs/

# Check permissions
chmod -R 755 storage/logs/
chown -R apache:apache storage/logs/
```

### Command not found

```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# List commands
php artisan list | grep logs
```

### Logs too large

```bash
# Check log sizes
du -sh storage/logs/*.log

# Clean old logs
find storage/logs -name "laravel-*.log" -mtime +365 -delete
```

---

## Best Practices

### ✅ DO:
- Log all critical business actions
- Include user_id in context
- Add meaningful descriptions
- Export monthly reports
- Set up log retention (7 years for BOT)

### ❌ DON'T:
- Log passwords or sensitive data
- Log every read operation
- Delete logs before retention period
- Forget to include context
- Log without user identification

---

## Files Created

1. **app/Services/AuditLogService.php** - Logging service
2. **app/Console/Commands/AnalyzeAuditLogs.php** - Analysis command
3. **LOG_BASED_AUDIT_TRAIL.md** - Full documentation
4. **AUDIT_TRAIL_QUICK_REFERENCE.md** - This quick reference

---

## Quick Test

```bash
# 1. Test the command
php artisan logs:analyze --date=$(date +%Y-%m-%d) --limit=10

# 2. Add logging to code
# See examples above

# 3. Generate report
php artisan logs:analyze --from=$(date -d '7 days ago' +%Y-%m-%d) --export=csv

# Done!
```

---

## Support

- **Full Docs:** `LOG_BASED_AUDIT_TRAIL.md`
- **Service:** `app/Services/AuditLogService.php`
- **Command:** `app/Console/Commands/AnalyzeAuditLogs.php`

---

**Remember:** Your logs are already recording activity! Just add `AuditLogService` calls to critical operations for structured audit tracking.
