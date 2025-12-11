# Comprehensive Payroll Management System 💼
## Integration: HR Module → Budget Checking → Approval → Expense → Payment

---

## **System Overview**

This is a fully integrated payroll management system following **international best practices** for SACCO payroll processing. The system ensures:

✅ **Separation of Duties** - Different roles for generation, approval, and payment
✅ **Budget Control** - Automatic budget checking before approval
✅ **Multi-Level Approval** - HR → Finance → Management workflow
✅ **Double-Entry Accounting** - Automatic GL postings via centralized services
✅ **Direct Deposit** - Payments to employee SACCO deposit accounts
✅ **Complete Audit Trail** - Comprehensive logging of all actions
✅ **Statutory Compliance** - Tanzania PAYE, NSSF, NHIF calculations

---

## **Workflow Diagram**

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         PAYROLL WORKFLOW                                │
└─────────────────────────────────────────────────────────────────────────┘

1. GENERATE PAYROLL (HR Department)
   ├─ Input: Month, Year
   ├─ Calculate: Basic Salary + Allowances
   ├─ Calculate: PAYE, NSSF, NHIF deductions
   ├─ Create: PayRolls records (status: pending)
   └─ Output: Payroll entries for all active employees

2. CHECK BUDGET (Automatic)
   ├─ Find: Salaries budget allocation for month
   ├─ Calculate: Total payroll amount
   ├─ Compare: Available budget vs. Required amount
   └─ Output: Budget check result (PASS/FAIL)

3. SUBMIT FOR APPROVAL (HR Department)
   ├─ Validate: Budget availability
   ├─ Create: Approval record (process_code: PAYROLL_BATCH)
   ├─ Update: PayRolls status → submitted_for_approval
   └─ Output: Approval ID

4. APPROVE/REJECT (Finance/Management)
   ├─ Review: Payroll details, budget status
   ├─ Decision: Approve or Reject
   ├─ If APPROVED:
   │  ├─ Reserve budget allocation
   │  └─ Update: PayRolls status → approved
   └─ If REJECTED:
      └─ Revert: PayRolls status → pending

5. PROCESS PAYMENT (Finance Department)
   ├─ For Each Approved Payroll:
   │  ├─ Get/Create: Employee deposit account
   │  ├─ Post Transaction: Bank → Employee Account (Net Salary)
   │  ├─ Create Expenses: Salary + PAYE + NSSF + NHIF
   │  ├─ Post to GL: Multi-line journal entry
   │  └─ Update: PayRolls status → paid
   └─ Output: Payment confirmations + Reference numbers
```

---

## **Database Schema**

### **Employees Table**
```sql
employees (
    id BIGINT PRIMARY KEY,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    employee_number VARCHAR(100) UNIQUE,
    basic_salary NUMERIC(10,2),
    client_id INTEGER,  -- Links to SACCO membership
    employee_status VARCHAR(255),  -- 'active', 'inactive'
    branch_id INTEGER,
    -- ... other fields
)
```

### **PayRolls Table**
```sql
pay_rolls (
    id BIGINT PRIMARY KEY,
    employee_id BIGINT REFERENCES employees(id),
    month INTEGER,
    year INTEGER,
    pay_period_start DATE,
    pay_period_end DATE,
    basic_salary NUMERIC(10,2),
    house_allowance NUMERIC(10,2),
    transport_allowance NUMERIC(10,2),
    gross_salary NUMERIC(10,2),
    paye NUMERIC(10,2),  -- PAYE tax
    nssf NUMERIC(10,2),  -- Social Security
    nhif NUMERIC(10,2),  -- Health Insurance
    tax_deductions NUMERIC(10,2),
    social_security NUMERIC(10,2),
    health_insurance NUMERIC(10,2),
    total_deductions NUMERIC(10,2) GENERATED,  -- Auto-calculated
    net_salary NUMERIC(10,2) GENERATED,  -- Auto-calculated
    status VARCHAR(255) DEFAULT 'pending',
        -- Status flow: pending → submitted_for_approval → approved → paid
    payment_date DATE,
    expense_created BOOLEAN DEFAULT false,
    INDEX (month, year),
    INDEX (status),
    INDEX (expense_created)
)
```

### **Expenses Table** (Integration Point)
```sql
expenses (
    id BIGINT PRIMARY KEY,
    account_id BIGINT,  -- Salary Expense Account
    amount NUMERIC(10,2),
    description TEXT,
    status VARCHAR(255),  -- APPROVED, PAID
    payroll_id BIGINT,  -- Links back to payroll
    employee_id BIGINT,  -- Links to employee
    -- ... other fields
)
```

---

## **Services Architecture**

### **1. PayrollBudgetCheckingService** ✅
**File:** `app/Services/PayrollBudgetCheckingService.php`

**Purpose:** Validates budget availability before payroll approval

**Key Methods:**
```php
checkPayrollBudget($month, $year)
// Returns: ['success' => bool, 'total_required', 'available_budget', 'shortfall']

reservePayrollBudget($month, $year)
// Updates budget allocation with utilized amount

getPayrollBudgetBreakdown($month, $year)
// Returns detailed breakdown by employee/department
```

**Budget Checking Logic:**
1. Calculate total gross salaries for all pending payroll
2. Find "Salaries" budget item
3. Get budget allocation for specific month
4. Compare: Available Budget >= Total Payroll
5. Calculate utilization percentage

**Budget Item Requirements:**
- Budget item name must contain: "SALARIES", "WAGES", or "PAYROLL"
- Must have monthly allocations
- Status must be ACTIVE

---

### **2. PayrollApprovalService** ✅
**File:** `app/Services/PayrollApprovalService.php`

**Purpose:** Manages multi-level approval workflow

**Key Methods:**
```php
submitPayrollForApproval($month, $year)
// Creates approval record, validates budget

approvePayroll($processId, $comments)
// Approves batch, reserves budget

rejectPayroll($processId, $reason)
// Rejects batch, reverts to pending

getPayrollApprovalStatus($month, $year)
// Returns current approval status
```

**Approval Flow:**
```
1. HR submits → Creates Approval (process_code: PAYROLL_BATCH)
2. Finance reviews → Checks budget again
3. Management approves → Reserves budget, updates status
4. OR Rejects → Reverts status, adds rejection note
```

**Approval Record:**
```php
approvals (
    process_code = 'PAYROLL_BATCH',
    process_id = 'month-year',  // e.g., "10-2025"
    approval_status = 'PENDING|APPROVED|REJECTED',
    additional_data = JSON {
        month, year, payroll_count,
        total_gross, total_net, budget_check
    }
)
```

---

### **3. PayrollPaymentService** ✅
**File:** `app/Services/PayrollPaymentService.php`

**Purpose:** Processes payments to employee deposit accounts

**Key Methods:**
```php
processMonthlyPayroll($month, $year)
// Processes all approved payrolls for the month

processPayrollPayment($payrollId)
// Processes single payroll payment
```

**Payment Process:**
1. **Get/Create Employee Deposit Account**
   - Checks if employee has active deposit account (client_number)
   - Creates account if doesn't exist
   - Account format: 0101[branch][client_id][sequence]

2. **Post Transaction via TransactionPostingService**
   ```
   Debit:  Bank Account (Payment Source)
   Credit: Employee Deposit Account (Net Salary)
   ```

3. **Create Expense Entries** (via PayrollExpenseIntegrationService)
   - Creates 4 types of expenses:
     - Salary Expense (Net amount)
     - PAYE Expense
     - NSSF Expense
     - NHIF Expense

4. **Post to General Ledger** (via JournalEntryService)
   ```
   Debit:  Salary Expense          (Gross Salary)
   Credit: Bank Account             (Net Salary)
   Credit: PAYE Payable             (Tax)
   Credit: NSSF Payable             (Social Security)
   Credit: NHIF Payable             (Health Insurance)
   ```

5. **Update PayRolls Status** → 'paid'

---

### **4. PayrollExpenseIntegrationService** ✅
**File:** `app/Services/PayrollExpenseIntegrationService.php`

**Purpose:** Converts approved payroll to expense entries for accounting

**Key Methods:**
```php
createExpenseFromPayroll($payrollId)
// Creates expense entries and posts to GL

batchCreateExpensesFromPayroll($month, $year)
// Batch processing for multiple payrolls
```

**Expense Creation:**
- **Salary Expense**: Net salary amount
- **PAYE Expense**: Tax deduction
- **NSSF Expense**: Social security contribution
- **NHIF Expense**: Health insurance contribution

All expenses are auto-approved (status: 'APPROVED')

---

## **Livewire Component**

### **PayrollManagement** (Enhanced)
**File:** `app/Http/Livewire/HR/PayrollManagement.php`

**New Features Added:**
```php
// Budget checking
$this->budgetCheckResult  // Real-time budget status

// Approval workflow
$this->approvalStatus  // Current approval status
submitForApproval()  // Submit batch for approval
approvePayrollBatch()  // Approve entire month
rejectPayrollBatch()  // Reject with reason

// Payment processing
processMonthlyPayments()  // Process all approved
processPayment($id)  // Process single payroll
viewBudgetDetails()  // Show detailed budget breakdown
```

**UI Enhancements Needed:**
1. Budget status indicator (green/red/yellow)
2. Approval workflow buttons (Submit/Approve/Reject)
3. Payment processing button (disabled until approved)
4. Budget breakdown modal
5. Approval history timeline

---

## **Statutory Calculations** (Tanzania)

### **PAYE Tax Calculation**
```php
Annual Income        Tax Rate    Tax Amount
─────────────────────────────────────────────
0 - 270,000          0%          0
270,001 - 520,000    8%          Excess * 0.08
520,001 - 760,000    20%         20,000 + (Excess * 0.20)
760,001 - 1,000,000  25%         68,000 + (Excess * 0.25)
Above 1,000,000      30%         128,000 + (Excess * 0.30)
```

### **NSSF Contribution**
```php
Rate: 10% of gross salary
Split: 5% employee + 5% employer (combined in payroll)
```

### **NHIF Contribution**
```php
Salary Range         NHIF Amount
─────────────────────────────────
0 - 5,999            150
6,000 - 7,999        300
8,000 - 11,999       400
12,000 - 14,999      500
15,000 - 19,999      600
20,000 - 24,999      750
25,000 - 29,999      850
30,000 - 34,999      900
35,000 - 39,999      950
40,000 - 44,999      1,000
45,000 - 49,999      1,100
50,000 - 59,999      1,200
60,000 - 69,999      1,300
70,000 - 79,999      1,400
80,000 - 89,999      1,500
90,000 - 99,999      1,600
100,000+             1,700
```

---

## **Sample Data**

### **Employees** ✅
```
ID  Name           Employee#  Basic Salary  Status   Client ID
──────────────────────────────────────────────────────────────
1   Sample         EMP001     50,000        active   1
2   Sample         EMP002     50,000        active   2
3   John Mwangi    EMP003     800,000       active   3
4   Mary Komba     EMP004     650,000       active   4
5   Peter Ndege    EMP005     900,000       active   5
6   Grace Lema     EMP006     550,000       active   6
7   James Mboya    EMP007     1,200,000     active   7
```

**Total Active Employees:** 7
**Total Monthly Basic Salaries:** TZS 4,200,000

---

## **Usage Guide**

### **Step 1: Generate Payroll** (HR Department)
```
1. Navigate to: HR Management → Payroll Management
2. Select: Month = October, Year = 2025
3. Click: "Generate Payroll"
4. System creates payroll entries for all 7 active employees
5. Status: All records set to 'pending'
```

### **Step 2: Review Budget** (HR/Finance)
```
Budget Check displays:
✅ Total Required: TZS 5,460,000 (gross)
✅ Available Budget: TZS 6,000,000
✅ Surplus: TZS 540,000
✅ Utilization: 91%
```

### **Step 3: Submit for Approval** (HR Department)
```
1. Review payroll entries
2. Click: "Submit for Approval"
3. System validates budget
4. Creates approval record
5. Status: All records → 'submitted_for_approval'
```

### **Step 4: Approve Payroll** (Finance/Management)
```
1. Review payroll summary
2. Check budget availability
3. Click: "Approve Payroll"
4. System reserves budget
5. Status: All records → 'approved'
```

**OR Reject:**
```
1. Enter rejection reason
2. Click: "Reject Payroll"
3. Status: All records → 'pending'
4. HR can revise and resubmit
```

### **Step 5: Process Payment** (Finance Department)
```
1. Click: "Process Monthly Payments"
2. System processes each approved payroll:
   - Creates/finds employee deposit accounts
   - Posts transactions to accounts
   - Creates expense entries
   - Posts to general ledger
3. Status: All records → 'paid'
4. Displays: Payment summary with reference numbers
```

---

## **General Ledger Posting Example**

For Employee: John Mwangi (Gross: 1,040,000, Net: 700,000)

```
Date: 2025-10-25
Reference: PAYROLL-2025-10-3

Account                          Debit       Credit
─────────────────────────────────────────────────────
Salary Expense                   1,040,000
  Bank Account                               700,000
  PAYE Payable                               150,000
  NSSF Payable                               104,000
  NHIF Payable                                86,000
─────────────────────────────────────────────────────
Total                            1,040,000   1,040,000
```

**Account Balances Updated:**
- Salary Expense: +1,040,000
- Bank Account: -700,000
- PAYE Payable: +150,000
- NSSF Payable: +104,000
- NHIF Payable: +86,000
- Employee Deposit (John): +700,000

**Parent Accounts:** Also updated recursively

---

## **Integration Points**

### **1. Budget Management Module**
- Checks budget allocations before approval
- Reserves budget when approved
- Updates utilized amounts after payment

### **2. Expenses Module**
- Creates expense entries from payroll
- Links expenses to payroll_id and employee_id
- Uses existing approval and payment workflows

### **3. Accounting Module**
- Posts to general ledger via JournalEntryService
- Updates account balances via TransactionPostingService
- Maintains double-entry bookkeeping

### **4. Member Accounts**
- Employees are SACCO members (client_id)
- Payments go to member deposit accounts
- Accounts auto-created if don't exist

---

## **Security & Audit**

### **Separation of Duties**
```
Role            Can Generate    Can Approve    Can Pay
─────────────────────────────────────────────────────
HR Staff        ✓               ✗              ✗
Finance Officer ✗               ✓              ✓
Management      ✗               ✓              ✗
```

### **Audit Trail**
All actions logged to `budget_management` channel:
```
📋 SUBMITTING PAYROLL FOR APPROVAL
✅ PAYROLL SUBMITTED FOR APPROVAL
✔️ APPROVING PAYROLL
✅ PAYROLL APPROVED
💳 PROCESSING PAYROLL PAYMENT
✅ PAYROLL PAYMENT PROCESSED
📒 POSTING TO GENERAL LEDGER
```

---

## **Error Handling**

### **Common Errors & Solutions**

**1. "Insufficient budget for payroll"**
- Solution: Increase budget allocation or use budget transfers

**2. "No bank account found for payroll payment"**
- Solution: Create a bank account with major_category_code = 1000

**3. "Employee has no deposit account"**
- Solution: System auto-creates account (ensure client_id is set)

**4. "Payroll must be approved before payment"**
- Solution: Follow approval workflow first

---

## **Best Practices Implemented**

✅ **Budget Control:** Pre-check before approval, reserve on approval
✅ **Multi-Level Approval:** Separation between submission and approval
✅ **Audit Trail:** Comprehensive logging with emoji markers
✅ **Data Integrity:** Database transactions with rollback on error
✅ **Double-Entry Bookkeeping:** All postings via centralized services
✅ **Direct Deposit:** Payments to employee accounts, not external
✅ **Statutory Compliance:** Tanzania tax rates for PAYE, NSSF, NHIF
✅ **Error Prevention:** Validates status before each action
✅ **Idempotency:** expense_created flag prevents duplicate processing

---

## **Next Steps**

### **Recommended Enhancements:**

1. **Email Notifications**
   - Notify employees of payslip availability
   - Notify approvers when submitted
   - Notify HR when approved/rejected

2. **Payslip Generation**
   - PDF generation for each employee
   - Download individual or batch payslips

3. **Payroll Reports**
   - Monthly payroll summary
   - Statutory deductions report
   - Year-end reports for TRA

4. **Overtime & Bonuses**
   - Add overtime hours calculation
   - Performance bonuses
   - One-time payments

5. **Deduction Management**
   - Loan repayments deduction
   - Savings deductions
   - Other custom deductions

---

## **Support & Maintenance**

### **Database Maintenance**
```sql
-- Monthly cleanup of old payroll records (optional)
DELETE FROM pay_rolls WHERE year < 2023;

-- Reindex for performance
REINDEX TABLE pay_rolls;
REINDEX TABLE employees;
```

### **Performance Monitoring**
```sql
-- Check payroll processing times
SELECT month, year, status, COUNT(*), SUM(gross_salary)
FROM pay_rolls
GROUP BY month, year, status
ORDER BY year DESC, month DESC;
```

---

**System Version:** 1.0
**Last Updated:** October 12, 2025
**Generated with:** 🤖 Claude Code
