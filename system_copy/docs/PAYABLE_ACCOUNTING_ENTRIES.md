# How Accounts Are Affected When Recording a Payable

## Overview
When a new payable (vendor bill) is recorded in the system, it creates double-entry bookkeeping transactions that affect multiple accounts in the General Ledger.

## The Double-Entry Process

### 1. Basic Payable Entry (Without VAT)

When recording a payable for **TZS 100,000** for office supplies:

```
DEBIT:  Office Supplies Expense    100,000
CREDIT: Accounts Payable                   100,000
```

**Account Effects:**
- **Expense Account (Debit)**: Increases by the bill amount
- **Accounts Payable (Credit)**: Increases liability by the bill amount

### 2. Payable Entry With VAT

When recording a payable with VAT (18%):
- Subtotal: TZS 100,000
- VAT: TZS 18,000
- Total: TZS 118,000

```
DEBIT:  Office Supplies Expense    100,000
DEBIT:  Input VAT (Asset)           18,000
CREDIT: Accounts Payable                   118,000
```

**Account Effects:**
- **Expense Account**: Increases by base amount only
- **Input VAT Account**: Increases (recoverable VAT asset)
- **Accounts Payable**: Increases by total including VAT

## Code Implementation

### From `TradeAndOtherPayables.php`

```php
private function createGLEntries($payableId, $data)
{
    $reference = 'PAY-' . $data['invoice_number'];
    $description = 'Bill ' . $data['invoice_number'] . ' - ' . $data['vendor_name'];
    
    // 1. DEBIT EXPENSE ACCOUNT
    general_ledger::create([
        'reference_number' => $reference,
        'transaction_type' => 'PAYABLE',
        'transaction_date' => $data['invoice_date'],
        'account_id' => $data['expense_account_id'],
        'debit_amount' => $data['amount'],      // Base amount without VAT
        'credit_amount' => 0,
        'description' => $description,
        'status' => 'POSTED',
        'source_id' => $payableId,
        'source_type' => 'payables'
    ]);
    
    // 2. DEBIT VAT (if applicable)
    if ($data['vat_amount'] > 0) {
        general_ledger::create([
            'account_id' => $vatAccount->id,
            'debit_amount' => $data['vat_amount'],
            'credit_amount' => 0,
            'description' => 'Input VAT on ' . $description,
            // ... other fields
        ]);
    }
    
    // 3. CREDIT ACCOUNTS PAYABLE
    general_ledger::create([
        'account_id' => $data['payable_account_id'],
        'debit_amount' => 0,
        'credit_amount' => $data['total_amount'],  // Total including VAT
        'description' => $description,
        // ... other fields
    ]);
}
```

## Real Example

Let's trace a real payable from the database:

### Example: Office Equipment Supplier Bill

**Input Data:**
- Vendor: Office Equipment Supplier
- Bill Number: BILL-2025-010
- Expense Category: Office Equipment (Account: 050200001000)
- Amount: TZS 600,000
- VAT (18%): TZS 108,000
- Total: TZS 708,000
- Due Date: 2025-10-15

### General Ledger Entries Created:

1. **Debit Office Equipment Expense**
   ```sql
   INSERT INTO general_ledger (
       reference_number: 'PAY-BILL-2025-010',
       transaction_type: 'PAYABLE',
       account_id: [Office Equipment Account ID],
       debit_amount: 600000.00,
       credit_amount: 0,
       description: 'Bill BILL-2025-010 - Office Equipment Supplier'
   )
   ```

2. **Debit Input VAT**
   ```sql
   INSERT INTO general_ledger (
       reference_number: 'PAY-BILL-2025-010',
       transaction_type: 'PAYABLE',
       account_id: [Input VAT Account ID],
       debit_amount: 108000.00,
       credit_amount: 0,
       description: 'Input VAT on Bill BILL-2025-010'
   )
   ```

3. **Credit Accounts Payable**
   ```sql
   INSERT INTO general_ledger (
       reference_number: 'PAY-BILL-2025-010',
       transaction_type: 'PAYABLE',
       account_id: [Accounts Payable Account ID],
       debit_amount: 0,
       credit_amount: 708000.00,
       description: 'Bill BILL-2025-010 - Office Equipment Supplier'
   )
   ```

## Account Balances Impact

### Before Recording the Payable:
- Office Equipment Expense: TZS 0
- Input VAT: TZS 0
- Accounts Payable: TZS 0

### After Recording the Payable:
- Office Equipment Expense: TZS 600,000 (Increased)
- Input VAT: TZS 108,000 (Increased)
- Accounts Payable: TZS 708,000 (Increased)

## When Payment is Made

When the payable is paid, another set of entries occurs:

```
DEBIT:  Accounts Payable           708,000
CREDIT: Bank Account                       708,000
```

This:
- Reduces the Accounts Payable liability to zero
- Reduces the Bank Account balance

## Financial Statement Impact

### Balance Sheet
- **Current Liabilities**: Increases by total payable amount
- **Current Assets**: Input VAT increases (recoverable)

### Income Statement
- **Operating Expenses**: Increases by base amount (excluding VAT)

### Cash Flow Statement (when paid)
- **Operating Activities**: Cash outflow when payment is made

## Account Hierarchy Used

The system uses these account types from the Chart of Accounts:

1. **Expense Accounts** (Level 3 or 4)
   - Example: 050200001000 - Office Equipment Expense
   - Parent: 050200000000 - Operating Expenses

2. **Accounts Payable** (Liability)
   - Example: 010110002000 - Trade and Other Payables
   - Configured in institution settings

3. **VAT Accounts** (Asset/Liability)
   - Input VAT (Asset): For VAT paid on purchases
   - Output VAT (Liability): For VAT collected on sales

## Validation & Controls

The system ensures:
1. **Account Existence**: Creates missing accounts automatically
2. **Double-Entry Balance**: Total debits = Total credits
3. **Audit Trail**: All entries linked to source payable ID
4. **Status Tracking**: Entries marked as 'POSTED'
5. **User Tracking**: Records who created the entries

## Integration with Other Modules

The payable entries integrate with:
- **General Ledger**: All entries posted immediately
- **Accounts Module**: Updates account balances
- **Reports**: Reflected in Trial Balance, Balance Sheet, Income Statement
- **Cash Management**: When payments are processed
- **VAT Reports**: Input VAT for tax returns

---

This documentation shows the complete flow of how recording a payable affects the accounting system through proper double-entry bookkeeping.