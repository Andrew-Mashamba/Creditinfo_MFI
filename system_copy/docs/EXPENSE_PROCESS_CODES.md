# Expense Process Codes Documentation

## Overview
Process codes have been added to support the expense management and budget validation workflow in the SACCOS Core System.

## Process Codes Added

### 1. EXPENSE_REG (ID: 47)
- **Name**: Expense Registration
- **Description**: Process for registering and approving expenses with budget validation
- **Configuration**:
  - Requires First Checker: Yes (Roles: Admin, Manager, Supervisor)
  - Requires Second Checker: Yes (Roles: Admin, Manager)
  - Requires Approver: Yes (Roles: Admin)
  - Min Amount: 0
  - Max Amount: No limit
  - Status: Active

### 2. EXPENSE_PAYMENT (ID: 48)
- **Name**: Expense Payment
- **Description**: Process for paying approved expenses
- **Configuration**:
  - Requires First Checker: Yes (Roles: Admin, Manager, Accountant)
  - Requires Second Checker: No
  - Requires Approver: Yes (Roles: Admin, Manager)
  - Min Amount: 0
  - Max Amount: No limit
  - Status: Active

### 3. EXPENSE_REIMBURSE (ID: 49)
- **Name**: Expense Reimbursement
- **Description**: Process for reimbursing employee expenses
- **Configuration**:
  - Requires First Checker: Yes (Roles: Admin, Manager, Supervisor, Accountant)
  - Requires Second Checker: Yes (Roles: Admin, Manager)
  - Requires Approver: Yes (Roles: Admin)
  - Min Amount: 0
  - Max Amount: No limit
  - Status: Active

### 4. BUDGET_OVERRIDE (ID: 50)
- **Name**: Budget Override
- **Description**: Process for approving expenses that exceed budget
- **Configuration**:
  - Requires First Checker: Yes (Roles: Admin, Manager)
  - Requires Second Checker: Yes (Roles: Admin)
  - Requires Approver: Yes (Roles: Admin only)
  - Min Amount: 0
  - Max Amount: No limit
  - Status: Active

## Existing Related Process Codes

### EXP_PETTY (ID: 32)
- **Name**: Petty Cash
- **Description**: Process for petty cash expenses

### EXP_OPERATING (ID: 33)
- **Name**: Operating Expense
- **Description**: Process for operating expenses

### EXP_CAPITAL (ID: 34)
- **Name**: Capital Expense
- **Description**: Process for capital expenses

## Database Tables

### 1. process_code_configs
Stores the configuration for all process codes including approval requirements, role assignments, and amount limits.

**Key Fields**:
- `process_code`: Unique identifier for the process
- `process_name`: Human-readable name
- `description`: Detailed description
- `requires_first_checker`: Boolean flag
- `requires_second_checker`: Boolean flag
- `requires_approver`: Boolean flag
- `first_checker_roles`: JSON array of role IDs
- `second_checker_roles`: JSON array of role IDs
- `approver_roles`: JSON array of role IDs
- `min_amount`: Minimum amount threshold
- `max_amount`: Maximum amount threshold
- `is_active`: Active status flag

### 2. approvals
Stores approval requests for various processes including expenses.

**Key Fields**:
- `process_code`: Links to process_code_configs
- `process_id`: ID of the related entity (e.g., expense ID)
- `process_name`: Name of the process
- `process_description`: Description of the specific request
- `approval_status`: Current status (PENDING, APPROVED, REJECTED)
- `first_checker_status`: Status of first checker approval
- `second_checker_status`: Status of second checker approval

## Usage in Code

### Creating an Expense Approval Request

```php
use App\Models\Approvals;

$approval = Approvals::create([
    'process_name' => 'new_expense_request',
    'process_description' => Auth::user()->name . ' has registered an expense: ' . $description,
    'approval_process_description' => 'Expense approval required',
    'process_code' => 'EXPENSE_REG',  // The new process code
    'process_id' => $expense->id,
    'process_status' => 'PENDING',
    'user_id' => Auth::id(),
    'approval_status' => 'PENDING'
]);
```

### Checking Process Configuration

```php
use App\Models\ProcessCodeConfig;

$config = ProcessCodeConfig::where('process_code', 'EXPENSE_REG')
    ->where('is_active', true)
    ->first();

if ($config) {
    // Check requirements
    if ($config->requires_first_checker) {
        // Assign first checker based on roles
        $firstCheckerRoles = $config->first_checker_roles;
    }
    
    // Check if amount requires second checker
    if ($config->requiresSecondChecker($amount)) {
        // Assign second checker
    }
}
```

## Migration Files

### Created Migration
- **File**: `database/migrations/2025_09_05_add_expense_reg_process_code.php`
- **Purpose**: Adds EXPENSE_REG and related process codes to the database

## Integration Points

### 1. Expense Registration (`NewExpense.php`)
- Uses `EXPENSE_REG` process code when creating approval requests
- Integrates with budget checking service
- Creates approval workflow based on process configuration

### 2. Budget Override
- Uses `BUDGET_OVERRIDE` when expense exceeds budget
- Requires higher-level approval (Admin only)
- Triggered when budget resolution is needed

### 3. Expense Payment
- Uses `EXPENSE_PAYMENT` after expense approval
- Simplified workflow (no second checker required)
- Handles actual disbursement of funds

## Role Mapping

Common role IDs used in the configuration:
- 1: Admin
- 2: Manager
- 3: Supervisor
- 4: Accountant

*Note: Adjust role IDs based on your actual roles table*

## Testing

To verify process codes are working:

```bash
# Check database
php artisan tinker
>>> App\Models\ProcessCodeConfig::where('process_code', 'EXPENSE_REG')->first();

# Test expense submission
# 1. Create an expense through the UI
# 2. Check approvals table for process_code = 'EXPENSE_REG'
# 3. Verify approval workflow follows configuration
```

## Troubleshooting

### Common Issues

1. **Process code not found**
   - Check if process code exists in process_code_configs table
   - Verify is_active = true

2. **Approval workflow not triggered**
   - Check role assignments in process configuration
   - Verify user has appropriate roles

3. **Sequence ID conflicts**
   - Reset PostgreSQL sequence: `ALTER SEQUENCE process_code_configs_id_seq RESTART WITH [next_id]`

---
*Last Updated: 2025-09-05*
*Version: 1.0*