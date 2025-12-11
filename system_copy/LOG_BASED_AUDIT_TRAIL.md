# Log-Based Audit Trail - NBC SACCOS System

**Date:** October 11, 2025
**Approach:** Utilizing existing Laravel log files for audit tracking
**Advantage:** Lightweight, no database overhead, already implemented

---

## Overview

Instead of creating a separate database audit system, this approach leverages **Laravel's existing daily log rotation** to create a comprehensive audit trail. Your system is already configured with:

```env
LOG_CHANNEL=daily           # Automatic daily log rotation
LOG_LEVEL=info             # Captures INFO, WARNING, ERROR
```

This means every day, a new log file is created: `storage/logs/laravel-2025-10-11.log`

---

## What is an Audit Trail?

An **Audit Trail** is a chronological record of system activities that captures:

- **WHO**: Which user performed the action
- **WHAT**: What action was performed (created, updated, deleted, approved, etc.)
- **WHEN**: Timestamp of the action
- **WHERE**: IP address and user agent
- **WHY**: Business context (approval reasons, amounts, etc.)
- **HOW**: Before/after values

---

## Current Log Structure

Your logs already contain rich contextual information:

```
[2025-10-11T13:08:14.592797+00:00] production.INFO: Menu item clicked [: in ] {
    "menu_id":16,
    "menu_name":"Reconciliation",
    "user_id":12
}
```

**Structure:**
- **Timestamp**: ISO 8601 format with timezone
- **Environment**: production
- **Level**: INFO, WARNING, ERROR
- **Message**: Human-readable description
- **Context**: JSON object with detailed data (user_id, permissions, actions, etc.)

---

## Existing Log Files

Your system already creates specialized logs:

| Log File | Purpose | Auto-Created |
|----------|---------|--------------|
| `laravel-YYYY-MM-DD.log` | Main application logs | ✅ Daily |
| `email-YYYY-MM-DD.log` | Email sending logs | ✅ Daily |
| `otp-YYYY-MM-DD.log` | OTP generation/validation | ✅ Daily |
| `transactions-YYYY-MM-DD.log` | Transaction logs | ✅ Daily |
| `payments/payments-YYYY-MM-DD.log` | Payment gateway logs | ✅ Daily |
| `ai-chat-YYYY-MM-DD.log` | AI interactions | ✅ Daily |
| `queue-worker.log` | Queue processing | ✅ |
| `nbc-daily-reconciliation.log` | Bank reconciliation | ✅ |

---

## Implementation - 3 Files Created

### 1. AuditLogService (Helper Service)

**File:** `/app/Services/AuditLogService.php`

A simple service to log audit events in a structured format.

**Usage:**

```php
use App\Services\AuditLogService;

// Log record creation
AuditLogService::created('Member', $member->id, $member->toArray());

// Log record update
AuditLogService::updated('Account', $account->id, $oldData, $newData);

// Log record deletion
AuditLogService::deleted('Transaction', $transaction->id, $transaction->toArray());

// Log login
AuditLogService::login($userId, true); // successful login

// Log custom transaction action
AuditLogService::transaction('REVERSED', $transaction->id, [
    'amount' => $transaction->amount,
    'reason' => 'Fraudulent transaction',
    'approved_by' => auth()->id()
]);

// Log loan approval
AuditLogService::loan('APPROVED', $loan->id, [
    'amount' => $loan->amount,
    'approver_comments' => 'Meets all criteria',
    'approval_level' => 'MANAGER'
]);

// Log data export
AuditLogService::dataExport('Members', [
    'count' => 500,
    'format' => 'Excel',
    'filters' => ['status' => 'active']
]);

// Log security event
AuditLogService::securityEvent('MULTIPLE_FAILED_LOGINS', [
    'ip_address' => $ip,
    'attempt_count' => 5
]);
```

### 2. AnalyzeAuditLogs (Command)

**File:** `/app/Console/Commands/AnalyzeAuditLogs.php`

Command-line tool to analyze and generate reports from log files.

**Usage:**

```bash
# Analyze logs from last 7 days (default)
php artisan logs:analyze

# Analyze specific date
php artisan logs:analyze --date=2025-10-11

# Analyze date range
php artisan logs:analyze --from=2025-10-01 --to=2025-10-11

# Filter by user
php artisan logs:analyze --user=12

# Filter by log level
php artisan logs:analyze --level=ERROR

# Search for specific term
php artisan logs:analyze --search="DELETED"

# Analyze different log types
php artisan logs:analyze --type=transactions
php artisan logs:analyze --type=payments
php artisan logs:analyze --type=otp

# Export to CSV
php artisan logs:analyze --export=csv --from=2025-10-01 --to=2025-10-11

# Export to JSON
php artisan logs:analyze --export=json --date=2025-10-11

# Export to text file
php artisan logs:analyze --export=txt --search="APPROVED"

# Combined filters
php artisan logs:analyze --user=12 --level=WARNING --from=2025-10-01 --export=csv
```

**Output Example:**

```
╔════════════════════════════════════════════════════════════╗
║        NBC SACCOS - Log-Based Audit Trail Analyzer        ║
╚════════════════════════════════════════════════════════════╝

Analyzing logs from: 2025-10-01 to 2025-10-11

Found 11 log file(s) to analyze

Parsing log files...
Parsed 3391 log entries

After filtering: 245 entries

═══════════════════════ STATISTICS ═══════════════════════
  Log Levels:
    • INFO: 220
    • WARNING: 15
    • ERROR: 10

  Top 5 Active Users:
    • User ID 12: 150 actions
    • User ID 5: 45 actions
    • User ID 8: 30 actions
    • User ID 15: 12 actions
    • User ID 3: 8 actions

  Activity by Hour:
    09:00: ████████████ (45)
    10:00: ██████████████████ (62)
    11:00: ███████████████ (51)
    13:00: ██████████ (38)
    14:00: ████████ (29)

═══════════════════════ LOG ENTRIES ═══════════════════════
┌─────────────────────┬─────────┬──────┬──────────────────────────────┐
│ Timestamp           │ Level   │ User │ Message                      │
├─────────────────────┼─────────┼──────┼──────────────────────────────┤
│ 2025-10-11 13:08:14 │ INFO    │ 12   │ Menu item clicked            │
│ 2025-10-11 13:13:18 │ INFO    │ 12   │ NBC Statement Service...     │
│ 2025-10-11 13:16:42 │ INFO    │ 12   │ NBC Statement Service...     │
└─────────────────────┴─────────┴──────┴──────────────────────────────┘

✓ Exported to: storage/app/exports/audit-logs-2025-10-01-to-2025-10-11.csv
```

### 3. Documentation

**Files:**
- `LOG_BASED_AUDIT_TRAIL.md` (this file)

---

## How to Use

### Step 1: Add Audit Logging to Your Code

**In Controllers:**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function store(Request $request)
    {
        $member = Member::create($request->validated());

        // Log the creation
        AuditLogService::created('Member', $member->id, [
            'name' => $member->name,
            'email' => $member->email,
            'member_number' => $member->member_number
        ]);

        return redirect()->route('members.index')
            ->with('success', 'Member created successfully');
    }

    public function update(Request $request, Member $member)
    {
        $oldData = $member->only(['status', 'email', 'phone']);

        $member->update($request->validated());

        $newData = $member->only(['status', 'email', 'phone']);

        // Log the update
        AuditLogService::updated('Member', $member->id, $oldData, $newData);

        return redirect()->route('members.index')
            ->with('success', 'Member updated successfully');
    }

    public function destroy(Member $member)
    {
        $memberData = $member->toArray();

        $member->delete();

        // Log the deletion
        AuditLogService::deleted('Member', $member->id, $memberData);

        return redirect()->route('members.index')
            ->with('success', 'Member deleted successfully');
    }
}
```

**In Services:**

```php
<?php

namespace App\Services;

use App\Models\Loan;
use App\Services\AuditLogService;

class LoanApprovalService
{
    public function approve(Loan $loan, string $comments)
    {
        // Log the approval BEFORE making changes
        AuditLogService::loan('APPROVED', $loan->id, [
            'loan_number' => $loan->loan_number,
            'amount' => $loan->amount,
            'applicant' => $loan->member->name,
            'approver_comments' => $comments,
            'approval_level' => 'MANAGER',
            'previous_status' => $loan->status
        ]);

        $loan->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'approver_comments' => $comments
        ]);

        return $loan;
    }

    public function reject(Loan $loan, string $reason)
    {
        // Log the rejection
        AuditLogService::loan('REJECTED', $loan->id, [
            'loan_number' => $loan->loan_number,
            'amount' => $loan->amount,
            'applicant' => $loan->member->name,
            'rejection_reason' => $reason,
            'previous_status' => $loan->status
        ]);

        $loan->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejection_reason' => $reason
        ]);

        return $loan;
    }
}
```

**In Event Listeners:**

```php
<?php

namespace App\Listeners;

use App\Events\TransactionReversed;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogTransactionReversal
{
    public function handle(TransactionReversed $event)
    {
        AuditLogService::transaction('REVERSED', $event->transaction->id, [
            'original_amount' => $event->transaction->amount,
            'original_status' => 'completed',
            'reversal_reason' => $event->reason,
            'reversed_by' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
```

---

### Step 2: View Audit Logs

**Method 1: Direct Log File Inspection**

```bash
# View today's log
tail -f storage/logs/laravel-2025-10-11.log

# View specific date
cat storage/logs/laravel-2025-10-10.log

# Search for specific user actions
grep '"user_id":12' storage/logs/laravel-2025-10-11.log

# Search for DELETED actions
grep "DELETED" storage/logs/laravel-2025-10-11.log

# Count errors
grep "ERROR" storage/logs/laravel-2025-10-11.log | wc -l
```

**Method 2: Use the Analysis Command**

```bash
# Interactive analysis
php artisan logs:analyze

# User activity report
php artisan logs:analyze --user=12 --from=2025-10-01

# Security audit (warnings and errors only)
php artisan logs:analyze --level=WARNING --from=2025-10-01

# Deletions audit
php artisan logs:analyze --search="DELETED" --from=2025-10-01

# Export monthly report
php artisan logs:analyze --from=2025-10-01 --to=2025-10-31 --export=csv
```

**Method 3: Programmatic Access**

```php
// In a controller or service
$logFile = storage_path('logs/laravel-' . now()->format('Y-m-d') . '.log');

if (File::exists($logFile)) {
    $logs = file($logFile);

    // Filter for user actions
    $userLogs = array_filter($logs, function($line) {
        return str_contains($line, '"user_id":12');
    });

    // Process logs...
}
```

---

## Real-World Usage Examples

### Example 1: Track Account Suspensions

```php
public function suspendAccount(Account $account, string $reason)
{
    AuditLogService::account('SUSPENDED', $account->id, [
        'account_number' => $account->account_number,
        'member_name' => $account->member->name,
        'suspension_reason' => $reason,
        'previous_status' => $account->status,
        'balance_at_suspension' => $account->balance
    ]);

    $account->update(['status' => 'suspended']);
}

// Query later
php artisan logs:analyze --search="SUSPENDED" --from=2025-10-01
```

### Example 2: Track Permission Changes

```php
public function updateUserRole(User $user, $newRoleId)
{
    $oldRole = $user->role->name;

    $user->update(['role_id' => $newRoleId]);

    $newRole = $user->fresh()->role->name;

    AuditLogService::roleChanged($user->id, $oldRole, $newRole);
}

// Query later
php artisan logs:analyze --search="ROLE_CHANGED" --level=WARNING
```

### Example 3: Track Large Transactions

```php
public function processTransaction(Transaction $transaction)
{
    if ($transaction->amount > 1000000) {
        AuditLogService::transaction('LARGE_TRANSACTION', $transaction->id, [
            'amount' => $transaction->amount,
            'type' => $transaction->type,
            'from_account' => $transaction->from_account,
            'to_account' => $transaction->to_account,
            'requires_approval' => true
        ]);
    }
}

// Query later
php artisan logs:analyze --search="LARGE_TRANSACTION" --export=csv
```

### Example 4: Track Data Exports

```php
public function exportMembers(Request $request)
{
    $members = Member::where('status', $request->status)->get();

    AuditLogService::dataExport('Members', [
        'count' => $members->count(),
        'format' => 'Excel',
        'filters' => $request->all(),
        'exported_at' => now()->toDateTimeString()
    ]);

    return Excel::download(new MembersExport($members), 'members.xlsx');
}

// Query later
php artisan logs:analyze --search="DATA_EXPORTED" --level=WARNING
```

---

## Audit Reports for Compliance

### Monthly Audit Report

```bash
# Generate monthly report (e.g., October 2025)
php artisan logs:analyze \
    --from=2025-10-01 \
    --to=2025-10-31 \
    --export=csv

# Output: storage/app/exports/audit-logs-2025-10-01-to-2025-10-31.csv
```

### Critical Actions Report

```bash
# All deletions
php artisan logs:analyze --search="DELETED" --from=2025-10-01 --export=csv

# All permission changes
php artisan logs:analyze --search="PERMISSION_CHANGED" --from=2025-10-01 --export=csv

# All errors
php artisan logs:analyze --level=ERROR --from=2025-10-01 --export=csv

# Security events
php artisan logs:analyze --search="LOGIN_FAILED" --from=2025-10-01 --export=csv
```

### User Activity Report

```bash
# Specific user's activity
php artisan logs:analyze --user=12 --from=2025-10-01 --to=2025-10-31 --export=csv

# All users' critical actions
php artisan logs:analyze --level=WARNING --from=2025-10-01 --export=csv
```

---

## Log Retention Policy

### Current Configuration

Logs are retained indefinitely by default. For compliance and disk space management:

```bash
# Clean up logs older than 7 years (BOT requirement)
find storage/logs -name "*.log" -mtime +2555 -delete

# Or keep only last 365 days
find storage/logs -name "laravel-*.log" -mtime +365 -delete
```

### Automated Cleanup (Add to Scheduler)

**File:** `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule)
{
    // Clean logs older than 7 years (2555 days) - BOT compliance
    $schedule->call(function () {
        $logsPath = storage_path('logs');
        $sevenYearsAgo = now()->subYears(7);

        foreach (File::glob("{$logsPath}/*.log") as $logFile) {
            if (File::lastModified($logFile) < $sevenYearsAgo->timestamp) {
                File::delete($logFile);
                Log::info("Deleted old log file", ['file' => basename($logFile)]);
            }
        }
    })->monthly(); // Run monthly
}
```

---

## Advantages of Log-Based Audit Trail

### ✅ Pros:
1. **No Database Overhead**: Logs don't slow down database
2. **Already Implemented**: Laravel does this automatically
3. **Simple**: Just call `AuditLogService::log()`
4. **Fast**: Writing to files is faster than database writes
5. **Searchable**: Use grep, awk, or the analyzer command
6. **Exportable**: CSV, JSON, TXT formats
7. **Rotation**: Automatic daily rotation prevents huge files
8. **Backup-Friendly**: Easy to archive to S3 or external storage

### ⚠️ Cons:
1. **Not Queryable in Real-Time**: Need to parse files
2. **No Foreign Keys**: Can't join with database tables
3. **File Size**: Can grow large on high-traffic systems
4. **Parsing Overhead**: Analysis requires file reading

---

## Best Practices

### ✅ DO:
- Log all critical actions (create, update, delete, approve, reject)
- Include user_id, entity type, entity ID
- Add context (amounts, reasons, approvers)
- Use consistent action names (CREATED, UPDATED, DELETED, APPROVED)
- Export monthly reports for compliance
- Set up log retention policy (7 years for BOT compliance)

### ❌ DON'T:
- Log sensitive data (passwords, PINs, API tokens)
- Log every read operation (too much noise)
- Log temporary/cache data
- Forget to include context
- Delete logs before retention period

---

## What to Log (Critical Actions)

### High Priority:
```php
✓ User login/logout
✓ Permission/role changes
✓ Financial transactions (create, update, delete, reverse)
✓ Loan approvals/rejections
✓ Account status changes (suspend, close, reactivate)
✓ Data exports
✓ System configuration changes
✓ Failed authentication attempts
```

### Medium Priority:
```php
✓ Member record changes
✓ Report generation
✓ Bulk operations
✓ File uploads
✓ Payment processing
```

### Low Priority (Optional):
```php
✓ Page views
✓ Search queries
✓ Filter applications
```

---

## Integration with Existing Code

Your system already logs rich information! For example:

```
[2025-10-11T13:08:14.592797+00:00] production.INFO: Menu item clicked [: in ] {
    "menu_id":16,
    "menu_name":"Reconciliation",
    "user_id":12
}
```

Just add `AuditLogService` calls in critical operations:

```php
// Before
$loan->update(['status' => 'approved']);

// After
AuditLogService::loan('APPROVED', $loan->id, [
    'amount' => $loan->amount,
    'approver' => auth()->id()
]);
$loan->update(['status' => 'approved']);
```

---

## Summary

### Files Created:
1. `app/Services/AuditLogService.php` - Helper service for logging
2. `app/Console/Commands/AnalyzeAuditLogs.php` - Analysis tool
3. `LOG_BASED_AUDIT_TRAIL.md` - This documentation

### Quick Start:
```bash
# 1. Add logging to your code
AuditLogService::created('Member', $member->id);

# 2. Analyze logs
php artisan logs:analyze --from=2025-10-01

# 3. Export report
php artisan logs:analyze --from=2025-10-01 --to=2025-10-31 --export=csv
```

### Benefits:
- ✅ Lightweight (no database overhead)
- ✅ Fast implementation (already have logs!)
- ✅ BOT compliant (7-year retention possible)
- ✅ Easy to search and analyze
- ✅ Exportable for auditors

---

## Next Steps

1. **Start Logging**: Add `AuditLogService` calls to critical operations
2. **Test**: Run `php artisan logs:analyze` to see current logs
3. **Schedule**: Add log cleanup to scheduler
4. **Train**: Show team how to use the analyzer
5. **Compliance**: Export monthly reports for auditors

---

**End of Documentation**
