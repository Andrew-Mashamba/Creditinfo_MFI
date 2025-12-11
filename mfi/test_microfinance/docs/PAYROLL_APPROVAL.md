# Payroll Approval System

## Overview

Simple payroll approval system - when payroll is submitted, a record is created in the `approvals` table with process code `PAYROLL`.

## Workflow

### 1. Submit Payroll for Approval

```php
$approvalService = new PayrollApprovalService();
$result = $approvalService->submitPayrollForApproval(10, 2025);
```

**What happens:**
- Checks budget availability
- Creates record in `approvals` table with code `PAYROLL`
- Updates payroll records status to `submitted_for_approval`

**Approval Record Created:**
```php
[
    'process_code' => 'PAYROLL',
    'process_id' => '10-2025',
    'process_name' => 'Payroll Approval',
    'approval_status' => 'PENDING',
    'process_status' => 'PENDING',
    'additional_data' => [
        'month' => 10,
        'year' => 2025,
        'payroll_count' => 7,
        'total_gross' => 5250000.00,
        'budget_check' => [...]
    ]
]
```

---

### 2. Approve Payroll

```php
$processId = '10-2025';
$result = $approvalService->approvePayroll($processId, 'Approved by Finance');
```

**What happens:**
- Re-checks budget availability
- Updates approval status to `APPROVED`
- Updates process status to `APPROVED`
- Updates payroll records status to `approved`
- Reserves budget

---

### 3. Reject Payroll

```php
$result = $approvalService->rejectPayroll($processId, 'Incorrect deductions');
```

**What happens:**
- Updates approval status to `REJECTED`
- Updates process status to `REJECTED`
- Reverts payroll records status to `pending`
- Records rejection reason

---

### 4. Check Approval Status

```php
$status = $approvalService->getPayrollApprovalStatus(10, 2025);
```

**Returns:**
```php
[
    'status' => 'PENDING',
    'process_status' => 'PENDING',
    'approval_id' => 33,
    'submitted_by' => 12,
    'submitted_at' => '2025-10-12 10:30:00',
    'approved_by' => null,
    'approved_at' => null,
    'comments' => null,
    'payroll_count' => 7,
    'total_amount' => 5250000.00
]
```

---

## Database Structure

### Key Fields in `approvals` Table

| Field | Description |
|-------|-------------|
| `process_code` | Always `PAYROLL` for payroll approvals |
| `process_id` | Format: `month-year` (e.g., `10-2025`) |
| `approval_status` | PENDING / APPROVED / REJECTED |
| `process_status` | PENDING / APPROVED / REJECTED |
| `additional_data` | JSON with payroll details |
| `submitted_at` | When submitted |
| `approved_by` | User ID who approved/rejected |
| `approved_at` | When approved/rejected |
| `approval_notes` | Comments/rejection reason |

---

## Status Flow

```
PENDING → APPROVED (with budget reservation)
   ↓
REJECTED (payroll reverted to pending)
```

---

## Integration with Budget System

1. **Before Submission:** Checks if budget is sufficient
2. **Before Approval:** Re-checks budget (in case it changed)
3. **After Approval:** Reserves budget from allocation

---

## Files

- `/app/Services/PayrollApprovalService.php` - Main service
- `/app/Models/Approval.php` - Approval model
- `/app/Http/Livewire/HR/PayrollManagement.php` - UI component

---

**Process Code:** `PAYROLL`
**Last Updated:** October 12, 2025
