# Approvals Table - Comprehensive Analysis

## Overview

The `approvals` table is a **centralized approval management system** that handles all approval workflows across the entire application. It supports multi-level approval chains with configurable checker levels.

---

## Table Structure

### Core Identity Fields

| Field | Type | Purpose |
|-------|------|---------|
| `id` | BIGINT | Primary key |
| `process_code` | VARCHAR(255) | Identifies the type of process (e.g., LOAN_APP, EXPENSE_REG, MEMBER_REG) |
| `process_id` | VARCHAR(255) | Identifies the specific record being approved (can be numeric ID or composite key) |
| `process_name` | VARCHAR(255) | Human-readable process name |
| `process_description` | VARCHAR(255) | Description of what's being approved |

### Status Tracking Fields

| Field | Type | Purpose |
|-------|------|---------|
| `status` | VARCHAR(255) | Legacy status field (default: 'pending') |
| `approval_status` | VARCHAR(255) | Current approval status (PENDING, APPROVED, REJECTED, PARTIALLY_APPROVED) |
| `process_status` | VARCHAR(255) | Overall process status (PENDING, IN_PROGRESS, APPROVED, REJECTED) |

### Multi-Level Approval Fields

| Field | Type | Purpose |
|-------|------|---------|
| `checker_level` | INTEGER | Current approval level (1 = first checker, 2 = second checker, 3 = approver) |
| `first_checker_id` | BIGINT FK(users) | User ID of first checker |
| `first_checker_status` | VARCHAR(255) | First checker's decision (APPROVED/REJECTED) |
| `first_checked_at` | TIMESTAMP | When first checker acted |
| `first_checker_rejection_reason` | VARCHAR(255) | Rejection reason from first checker |
| `second_checker_id` | BIGINT FK(users) | User ID of second checker |
| `second_checker_status` | VARCHAR(255) | Second checker's decision |
| `second_checked_at` | TIMESTAMP | When second checker acted |
| `second_checker_rejection_reason` | VARCHAR(255) | Rejection reason from second checker |
| `approver_id` | BIGINT FK(users) | User ID of final approver |
| `approver_rejection_reason` | VARCHAR(255) | Rejection reason from approver |

### Timestamp Fields

| Field | Type | Purpose |
|-------|------|---------|
| `submitted_at` | TIMESTAMP | When approval was requested |
| `approved_at` | TIMESTAMP | When finally approved |
| `rejected_at` | TIMESTAMP | When rejected |
| `created_at` | TIMESTAMP | Record creation |
| `updated_at` | TIMESTAMP | Last update |
| `deleted_at` | TIMESTAMP | Soft delete |

### User Tracking

| Field | Type | Purpose |
|-------|------|---------|
| `user_id` | BIGINT FK(users) | User who initiated the approval request |
| `approved_by` | BIGINT FK(users) | User who made final decision (approve/reject) |
| `last_action_by` | BIGINT FK(users) | User who last acted on this approval |

### Additional Data

| Field | Type | Purpose |
|-------|------|---------|
| `additional_data` | JSONB | Flexible storage for process-specific data |
| `edit_package` | JSON | Package of changes for edit approvals |
| `comments` | TEXT | General comments |
| `approval_notes` | TEXT | Approval/rejection notes |
| `approval_category` | VARCHAR(255) | Category grouping (e.g., PAYROLL, EXPENSE, LOAN) |
| `approval_process_description` | VARCHAR(255) | Description of approval process |

### Specialized Fields

| Field | Type | Purpose |
|-------|------|---------|
| `team_id` | VARCHAR(255) | Team context |
| `branch_id` | BIGINT FK(branches) | Branch context |
| `next_role_name` | VARCHAR(255) | Next role in approval chain |
| `committee_minutes_path` | VARCHAR(255) | Path to committee meeting minutes |
| `policy_adherence_confirmed` | BOOLEAN | Policy compliance confirmation |
| `rejection_reason` | VARCHAR(255) | Legacy rejection reason field |

---

## Approval Workflow Design

### Three-Level Approval Chain

The system supports a **configurable 3-level approval process**:

```
Level 1: First Checker → Level 2: Second Checker → Level 3: Approver
```

Each level is optional and configured per process code in the `process_code_configs` table.

### Configuration via ProcessCodeConfig

Each process code has a configuration that defines:

```sql
process_code_configs:
- requires_first_checker (BOOLEAN)
- requires_second_checker (BOOLEAN)
- requires_approver (BOOLEAN)
- first_checker_roles (JSON array)
- second_checker_roles (JSON array)
- approver_roles (JSON array)
- min_amount / max_amount (for amount-based routing)
```

### Approval Flow Example

**Example: EXPENSE_REG (Expense Registration)**

```
Config:
- requires_first_checker: true
- requires_second_checker: true
- requires_approver: true

Flow:
1. User submits expense
   → approval record created with checker_level = 1

2. First Checker reviews
   → If approved: checker_level = 2, first_checker_status = 'APPROVED'
   → If rejected: approval_status = 'REJECTED', process_status = 'REJECTED'

3. Second Checker reviews (if level 2 reached)
   → If approved: checker_level = 3, second_checker_status = 'APPROVED'
   → If rejected: approval_status = 'REJECTED'

4. Approver reviews (if level 3 reached)
   → If approved: approval_status = 'APPROVED', process_status = 'APPROVED'
   → If rejected: approval_status = 'REJECTED'
```

---

## Process Codes

### Currently Configured (74 process codes)

Common categories:

**Financial:**
- `EXPENSE_REG` - Expense Registration (3 levels)
- `EXPENSE_PAYMENT` - Expense Payment (2 levels)
- `BUDGET_CREATE` - Create Budget (3 levels)
- `PAY_SALARY` - Salary Payment (3 levels)
- `PAY_VENDOR` - Vendor Payment (3 levels)

**Loans:**
- `LOAN_APP` - Loan Application (3 levels)
- `LOAN_DISB` - Loan Disbursement (3 levels)
- `LOAN_WOFF` - Loan Write-off (3 levels)

**Accounts:**
- `SAV_OPEN` - Open Savings Account (2 levels)
- `SAV_LARGE_WD` - Large Savings Withdrawal (3 levels)
- `ACC_CREATE` - Account Creation (3 levels)

**Members:**
- `MEMBER_REG` - Member Registration (3 levels)
- `CLIENT_REG` - Client Registration (2 levels)

**HR:**
- `HR_HIRE` - Staff Hiring (3 levels)
- `HR_SALARY` - Salary Change (3 levels)
- `HR_TERMINATE` - Staff Termination (3 levels)

**Note:** There is **NO** `PAYROLL` or `PAYROLL_BATCH` process code configured yet.

---

## Usage Patterns

### Pattern 1: Using HasApprovals Trait

Models that use the `HasApprovals` trait:

```php
use App\Traits\HasApprovals;

class Loan extends Model {
    use HasApprovals;
}

// Initiate approval
$loan->initiateApproval(
    'Loan Application',
    'Loan for ' . $client->name,
    'LOAN_APP',
    $loanAmount
);
```

### Pattern 2: Direct Approval Creation

```php
$approval = Approval::create([
    'process_code' => 'MEMBER_REG',
    'process_id' => $client->id,
    'process_name' => 'new_member_registration',
    'process_description' => 'Register ' . $client->name,
    'user_id' => auth()->id(),
    'approval_status' => 'PENDING',
    'process_status' => 'PENDING',
    'submitted_at' => now()
]);
```

### Pattern 3: Custom Approval Service (like Payroll)

```php
class PayrollApprovalService {
    public function submitPayrollForApproval($month, $year) {
        $approval = Approval::create([
            'process_code' => 'PAYROLL',
            'process_id' => $month . '-' . $year,
            'approval_status' => 'PENDING',
            'process_status' => 'PENDING',
            'additional_data' => [
                'month' => $month,
                'year' => $year,
                'payroll_count' => 7,
                'total_gross' => 5250000.00
            ]
        ]);
    }
}
```

---

## Related Tables

### 1. process_code_configs
Defines approval requirements and role permissions for each process code.

### 2. approval_comments
Allows multiple comments to be added to an approval:
```sql
approval_comments:
- id
- approval_id (FK to approvals)
- comment (TEXT)
- branch_id
```

### 3. Referenced By
The approvals table is referenced by:
- `budget_managements.approval_request_id`
- `expenses.approval_id`

---

## Migration History

### Base Migration (2023_01_04_000008)
Created the `approvals` table with:
- Basic approval fields
- Multi-level checker support
- Status tracking
- Foreign keys to users

**Note:** The migration defines `process_id` as `bigInteger`, but in the actual database it's `VARCHAR(255)`. This was likely altered manually to support composite process IDs like "10-2025".

### Enhancement Migrations

**2025_08_18:** Added committee approval support
- `next_role_name`
- `committee_minutes_path`
- `policy_adherence_confirmed`

**Fields Added Outside Migrations:**
These fields exist in the database but not in migrations:
- `additional_data` (JSONB)
- `approval_category` (VARCHAR)
- `submitted_at` (TIMESTAMP)
- `approved_by` (BIGINT)
- `approval_notes` (TEXT)
- `process_id` changed from BIGINT to VARCHAR(255)

---

## Key Design Insights

### 1. Centralized Approval System
- **One table** handles all approval workflows
- Reduces code duplication
- Consistent approval patterns across modules

### 2. Flexible Process Identification
- `process_code` categorizes the type of approval
- `process_id` identifies the specific record (can be numeric or composite)
- Example: `process_code='PAYROLL'`, `process_id='10-2025'`

### 3. Multi-Level Support
- Built-in support for 3 approval levels
- Each level tracks its own checker, status, timestamp, and rejection reason
- Levels can be skipped based on configuration

### 4. Status Duality
- `approval_status` - Current approval state
- `process_status` - Overall process state
- Both used for consistency with other systems

### 5. Extensible Data Storage
- `additional_data` (JSONB) - Store process-specific data
- `edit_package` (JSON) - Store changes for edit approvals
- Flexible enough for any approval type

### 6. Audit Trail
- Complete history of who did what when
- Rejection reasons tracked per level
- Soft delete support

---

## Best Practices

### 1. Always Set Both Status Fields
```php
'approval_status' => 'PENDING',
'process_status' => 'PENDING'
```

### 2. Use process_code for Filtering
```php
Approval::where('process_code', 'PAYROLL')
    ->where('process_id', $processId)
    ->first();
```

### 3. Store Rich Data in additional_data
```php
'additional_data' => [
    'month' => 10,
    'year' => 2025,
    'payroll_count' => 7,
    'total_gross' => 5250000.00,
    'budget_check' => [...]
]
```

### 4. Track All User Actions
```php
'user_id' => Auth::id(),           // Who submitted
'approved_by' => Auth::id(),       // Who approved/rejected
'last_action_by' => Auth::id()     // Last action
```

---

## Recommendations for Payroll

### Option 1: Simple Single-Level (Current Implementation)
```php
process_code: 'PAYROLL'
checker_level: Not used
approval_status: PENDING → APPROVED/REJECTED
```

**Pros:**
- Simple and fast
- Consistent with current implementation
- No need for ProcessCodeConfig

**Cons:**
- No separation of duties
- No multi-level approval support

### Option 2: Add to ProcessCodeConfig
```sql
INSERT INTO process_code_configs (
    process_code,
    process_name,
    description,
    requires_first_checker,
    requires_second_checker,
    requires_approver,
    first_checker_roles,
    second_checker_roles,
    approver_roles,
    is_active
) VALUES (
    'PAYROLL',
    'Payroll Approval',
    'Monthly payroll batch approval',
    false,  -- Skip first checker
    false,  -- Skip second checker
    true,   -- Only require final approver
    null,
    null,
    '["Finance Manager", "Finance Director"]',
    true
);
```

**Pros:**
- Follows system conventions
- Can be upgraded to multi-level later
- Role-based permissions

**Cons:**
- More complex setup
- Requires understanding of ProcessCodeConfig

---

## Database Indexes

Current indexes:
- `approvals_pkey` - Primary key on id
- `approvals_branch_id_index` - Branch filtering

**Recommended Additional Indexes:**
```sql
CREATE INDEX idx_approvals_process_code ON approvals(process_code);
CREATE INDEX idx_approvals_process_id ON approvals(process_id);
CREATE INDEX idx_approvals_approval_status ON approvals(approval_status);
CREATE INDEX idx_approvals_process_code_id ON approvals(process_code, process_id);
```

---

## Summary

The `approvals` table is a **robust, centralized approval management system** that:

1. ✅ Supports **3-level approval chains** (configurable)
2. ✅ Handles **74 different process types** (extensible)
3. ✅ Provides **complete audit trails**
4. ✅ Stores **flexible metadata** via JSONB
5. ✅ Integrates with **role-based permissions**
6. ✅ Supports **both single and multi-level workflows**

For **payroll approvals**, the current simple implementation using `process_code='PAYROLL'` is appropriate for a streamlined workflow. If multi-level approval is needed in the future, the table already supports it.

---

**Last Analyzed:** October 12, 2025
**Database Version:** PostgreSQL 15.14
**Table Version:** Base + 2 enhancements
