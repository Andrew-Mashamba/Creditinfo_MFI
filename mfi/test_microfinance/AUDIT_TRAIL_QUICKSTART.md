# Audit Trail - Quick Start Guide

## What is an Audit Trail?

An **Audit Trail** is a security-relevant chronological record that provides documentary evidence of the sequence of activities that have affected a specific operation, procedure, or event.

### In Simple Terms:
Think of it as a **digital CCTV camera** for your database that records:
- **WHO** did something (which user)
- **WHAT** they did (created, updated, deleted)
- **WHEN** they did it (timestamp)
- **WHERE** they did it from (IP address)
- **WHAT CHANGED** (before and after values)

---

## Why Do You Need It?

### 1. Compliance & Regulation
- **Bank of Tanzania (BOT):** Requires 7-year audit trails for financial institutions
- **Data Protection Act:** Mandates tracking of personal data access
- **Internal Audits:** Required for ISO certifications

### 2. Security & Fraud Detection
- Detect unauthorized access
- Track suspicious patterns
- Investigate security breaches
- Monitor privilege escalation

### 3. Operational Benefits
- Debug production issues
- Understand user behavior
- Performance tracking
- Customer dispute resolution

---

## Quick Implementation (3 Steps)

### Step 1: Add Trait to Your Model

**Before:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['name', 'email', 'status'];
}
```

**After:**
```php
<?php

namespace App\Models;

use App\Traits\Auditable;  // ← Add this
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use Auditable;  // ← Add this

    protected $fillable = ['name', 'email', 'status'];
}
```

**That's it!** All changes are now automatically logged.

---

### Step 2: Test It

```php
// Create a member
$member = Member::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'status' => 'active'
]);
// ✓ Audit log created: "Created new Member"

// Update the member
$member->status = 'inactive';
$member->save();
// ✓ Audit log created: "Updated Member (ID: 1)"
//   Old value: status = 'active'
//   New value: status = 'inactive'

// Delete the member
$member->delete();
// ✓ Audit log created: "Deleted Member (ID: 1)"
```

---

### Step 3: View Audit History

```php
// Get complete audit history for a member
$member = Member::find(1);
$history = $member->auditHistory();

foreach ($history as $audit) {
    echo "{$audit->performed_at}: {$audit->action} by {$audit->user->name}\n";
}

// Output:
// 2025-10-11 14:30:00: DELETE by Jane Smith
// 2025-10-11 14:25:00: UPDATE by John Doe
// 2025-10-11 14:20:00: CREATE by John Doe
```

---

## Advanced Usage

### Log Custom Actions

```php
class Loan extends Model
{
    use Auditable;

    public function approve($approverId, $comments)
    {
        // Log custom action with context
        $this->auditAction(
            'LOAN_APPROVED',
            "Loan {$this->loan_number} approved by manager",
            [
                'loan_amount' => $this->amount,
                'approved_by' => $approverId,
                'approval_comments' => $comments,
                'approval_level' => 'MANAGER'
            ]
        );

        $this->update(['status' => 'approved']);
    }
}
```

### Exclude Sensitive Fields

```php
class User extends Model
{
    use Auditable;

    // Don't log these fields
    protected $auditExclude = ['password', 'pin', 'api_token'];
}
```

---

## Viewing Audit Logs

### Method 1: Via Model

```php
// Get audit history for specific record
$member = Member::find(123);
$history = $member->auditHistory();

// Get recent changes (last 7 days)
$recentChanges = $member->recentAudits(7);

// Get audit summary
$summary = $member->auditSummary();
/*
[
    'total_changes' => 15,
    'created_at' => '2025-01-15 10:00:00',
    'last_updated_at' => '2025-10-11 14:30:00',
    'total_updates' => 12,
    'unique_editors' => 3,
    'critical_actions' => 1
]
*/
```

### Method 2: Direct Query

```php
use App\Models\AuditLog;

// All actions by specific user
$userActions = AuditLog::where('user_id', 123)
    ->orderBy('performed_at', 'desc')
    ->get();

// All deletions in last month
$deletions = AuditLog::where('action', 'DELETE')
    ->where('performed_at', '>=', now()->subMonth())
    ->get();

// All changes to Members table
$memberChanges = AuditLog::where('entity_type', 'App\Models\Member')
    ->orderBy('performed_at', 'desc')
    ->get();
```

### Method 3: Generate Reports

```bash
# Generate audit report for last 7 days
php artisan audit:report

# Report for specific date range
php artisan audit:report --from=2025-10-01 --to=2025-10-11

# Filter by user
php artisan audit:report --user=123

# Filter by action type
php artisan audit:report --action=DELETE

# Show only critical actions
php artisan audit:report --critical

# Export to CSV
php artisan audit:report --export=csv

# Export to JSON
php artisan audit:report --export=json --from=2025-10-01 --to=2025-10-11
```

---

## What Gets Logged?

For each event, the system captures:

| Field | Description | Example |
|-------|-------------|---------|
| `user_id` | Who performed the action | 123 |
| `user_name` | User's full name | "John Doe" |
| `action` | What was done | CREATE, UPDATE, DELETE |
| `entity_type` | Which table/model | App\Models\Member |
| `entity_id` | Which specific record | 456 |
| `old_values` | Data before change | {"status": "active"} |
| `new_values` | Data after change | {"status": "inactive"} |
| `description` | Human-readable summary | "Updated Member (ID: 456)" |
| `ip_address` | Where from | 192.168.1.100 |
| `user_agent` | Browser/device | Mozilla/5.0... |
| `performed_at` | When | 2025-10-11 14:30:00 |
| `branch_id` | Which branch | 5 |
| `severity` | Importance level | low, medium, high, critical |

---

## Real-World Examples

### Example 1: Track Account Status Changes

```php
// In AccountController
$account = Account::find($accountId);
$oldStatus = $account->status;

$account->update(['status' => 'suspended']);

// Audit log automatically created:
// Action: UPDATE
// Description: "Updated Account (ID: 123)"
// Old values: {"status": "active"}
// New values: {"status": "suspended"}
// User: Current logged-in user
// Timestamp: 2025-10-11 14:30:00
```

### Example 2: Track Loan Approvals

```php
class Loan extends Model
{
    use Auditable;

    public function approve($approverComments)
    {
        $this->auditAction(
            'LOAN_APPROVED',
            "Loan {$this->loan_number} approved",
            [
                'loan_amount' => $this->amount,
                'approver_comments' => $approverComments,
                'applicant_name' => $this->member->name
            ]
        );

        $this->update(['status' => 'approved', 'approved_at' => now()]);
    }

    public function reject($reason)
    {
        $this->auditAction(
            'LOAN_REJECTED',
            "Loan {$this->loan_number} rejected",
            ['rejection_reason' => $reason]
        );

        $this->update(['status' => 'rejected']);
    }
}
```

### Example 3: Track Transaction Reversals

```php
class Transaction extends Model
{
    use Auditable;

    public function reverse($reason)
    {
        $this->auditAction(
            'TRANSACTION_REVERSED',
            "Transaction {$this->reference} reversed",
            [
                'original_amount' => $this->amount,
                'reversal_reason' => $reason,
                'original_status' => $this->status
            ]
        );

        $this->update(['status' => 'reversed']);
    }
}
```

---

## Security Features

### 1. Immutable Logs
Audit logs **CANNOT** be deleted or modified once created.

```php
$audit = AuditLog::find(1);
$audit->delete();
// Exception: "Audit logs are immutable and cannot be deleted"
```

### 2. Automatic Context Capture
- User ID (who did it)
- IP address (where from)
- User Agent (which browser/device)
- Timestamp (when exactly)
- Request ID (correlate multiple operations)

### 3. Sensitive Data Exclusion
```php
protected $auditExclude = ['password', 'pin', 'api_token'];
```

---

## Common Queries

### 1. Who changed this record?
```php
$member = Member::find(123);
$createdBy = $member->createdByUser();
$lastModifiedBy = $member->lastModifiedByUser();

echo "Created by: {$createdBy->name}";
echo "Last modified by: {$lastModifiedBy->name}";
```

### 2. What changed in the last week?
```php
$member = Member::find(123);
$recentChanges = $member->recentAudits(7);

foreach ($recentChanges as $change) {
    echo "{$change->performed_at}: {$change->description}\n";
}
```

### 3. Find all deletions
```php
$deletions = AuditLog::where('action', 'DELETE')
    ->where('performed_at', '>=', now()->subDays(30))
    ->with('user')
    ->get();

foreach ($deletions as $deletion) {
    echo "{$deletion->user->name} deleted {$deletion->entity_type} ID {$deletion->entity_id}\n";
}
```

### 4. Suspicious activity detection
```php
// User with >1000 actions today
$suspiciousUsers = AuditLog::selectRaw('user_id, COUNT(*) as action_count')
    ->whereDate('performed_at', today())
    ->groupBy('user_id')
    ->having('action_count', '>', 1000)
    ->get();

// Multiple failed logins
$failedLogins = SecurityAuditLog::where('action', 'LOGIN_FAILED')
    ->where('ip_address', $ip)
    ->where('created_at', '>=', now()->subHour())
    ->count();

if ($failedLogins > 5) {
    // Block IP or trigger alert
}
```

---

## Best Practices

### ✅ DO:
- Add `Auditable` trait to all critical models (Member, Transaction, Loan, Account)
- Log custom actions for business-critical operations (approvals, reversals)
- Exclude sensitive fields (passwords, tokens)
- Generate monthly audit reports for compliance
- Monitor critical actions in real-time

### ❌ DON'T:
- Log read operations (too much noise)
- Log temporary/cache data
- Delete audit logs (they're immutable by design)
- Store passwords in audit logs
- Skip logging because "it's just a small change"

---

## Models to Add Auditing (Priority)

### Critical (Add Now):
```php
// Financial
✓ Transaction
✓ Loan
✓ Account
✓ LoanRepayment
✓ BankTransfer

// User Management
✓ User
✓ Role
✓ Permission
✓ SubRole

// Member Management
✓ Member
✓ MemberAccount
```

### High (Add Soon):
```php
✓ Bill
✓ Payment
✓ BudgetAllocation
✓ FixedAsset
✓ JournalEntry
```

### Medium (Add Later):
```php
✓ Branch
✓ Department
✓ Settings
✓ Configuration
```

---

## Compliance Checklist

### Bank of Tanzania (BOT):
- ✅ Maintain audit logs for 7 years
- ✅ Track all financial transactions
- ✅ Record user authentication events
- ✅ Log system configuration changes
- ✅ Enable export for regulatory review

### Data Protection Act:
- ✅ Log access to personal data
- ✅ Track consent changes
- ✅ Record data deletions
- ✅ Audit data exports

---

## Summary

### Implementation Effort:
- **Time:** 5 minutes per model
- **Code Change:** Add 1 line (`use Auditable;`)
- **Testing:** Automatic

### Benefits:
- ✅ Full compliance with BOT regulations
- ✅ Forensic investigation capability
- ✅ Fraud detection
- ✅ User accountability
- ✅ Dispute resolution
- ✅ Performance tracking
- ✅ Security monitoring

---

## Next Steps

1. **Read:** Full documentation in `AUDIT_TRAIL_IMPLEMENTATION.md`
2. **Add:** `use Auditable;` to critical models
3. **Test:** Create, update, delete records
4. **Verify:** Run `php artisan audit:report`
5. **Monitor:** Setup alerts for critical actions

---

## Support

- **Documentation:** `/AUDIT_TRAIL_IMPLEMENTATION.md`
- **Trait:** `/app/Traits/Auditable.php`
- **Model:** `/app/Models/AuditLog.php`
- **Command:** `/app/Console/Commands/GenerateAuditReport.php`

---

**Start logging today!** Add `use Auditable;` to your first model and watch the audit trail work automatically.
