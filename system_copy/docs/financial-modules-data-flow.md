# Financial Modules Data Flow Architecture

## Overview
This document outlines the complete data flow from business modules to financial statements through the accounts table.

## Architecture Pattern
```
Business Modules (Detailed Data) → Integration Service → Accounts Table (GL) → Financial Statements
```

## Business Modules and Their Tables

### 1. Trade Receivables Module
- **Table**: `trade_receivables`
- **Purpose**: Track customer invoices, aging, and provisions
- **Key Fields**: invoice_number, customer_name, amount, balance, aging_days, provision_amount
- **Account Integration**: Creates/updates receivable accounts in accounts table
- **GL Posting**: Debit AR account, Credit Revenue account
- **Component**: `app/Http/Livewire/Accounting/TradeAndOtherReceivables.php` ✅

### 2. Investments Module  
- **Table**: `investments_list`, `investment_transactions`
- **Purpose**: Manage various investment types (shares, bonds, FDRs, etc.)
- **Key Fields**: investment_type, principal_amount, maturity_date, interest_rate
- **Account Integration**: Creates investment asset accounts
- **GL Posting**: Debit Investment account, Credit Cash account
- **Component**: `app/Http/Livewire/Accounting/Investiments.php` ✅

### 3. PPE Management Module
- **Table**: `ppe_assets`, `ppe_transactions`
- **Purpose**: Track property, plant & equipment lifecycle
- **Key Fields**: asset_code, cost, accumulated_depreciation, net_book_value
- **Account Integration**: Creates fixed asset accounts
- **GL Posting**: 
  - Purchase: Debit PPE account, Credit Cash/Payable
  - Depreciation: Debit Depreciation Expense, Credit Accumulated Depreciation
- **Component**: `app/Http/Livewire/Accounting/PpeManagement.php` ✅

### 4. Prepaid Expenses Module
- **Table**: `prepaid_expenses`
- **Purpose**: Manage expenses paid in advance
- **Key Fields**: expense_type, total_amount, monthly_expense, remaining_balance
- **Account Integration**: Creates prepaid asset accounts
- **GL Posting**: 
  - Initial: Debit Prepaid account, Credit Cash
  - Monthly: Debit Expense account, Credit Prepaid account
- **Component**: To be created/enhanced

### 5. Trade Payables Module
- **Table**: `trade_payables`, `payable_payments`
- **Purpose**: Track vendor bills and payments
- **Key Fields**: bill_number, vendor_name, amount, balance, due_date
- **Account Integration**: Creates payable liability accounts
- **GL Posting**: Debit Expense/Asset, Credit Payables account
- **Component**: `app/Http/Livewire/Accounting/TradeAndOtherPayables.php` ✅

### 6. Creditors Module
- **Table**: `creditors`, `borrowing_transactions`
- **Purpose**: Manage loans, leases, and other credit facilities
- **Key Fields**: creditor_type, principal_amount, interest_rate, outstanding_amount
- **Account Integration**: Creates liability accounts
- **GL Posting**: 
  - Borrowing: Debit Cash, Credit Loan Payable
  - Repayment: Debit Loan Payable, Credit Cash
- **Component**: `app/Http/Livewire/Accounting/Creditors.php` ✅

### 7. Other Income Module
- **Table**: `other_income`
- **Purpose**: Record non-operating income
- **Key Fields**: income_category, amount, tax_amount, net_amount
- **Account Integration**: Updates income accounts
- **GL Posting**: Debit Cash/Receivable, Credit Income account
- **Component**: `app/Http/Livewire/Accounting/OtherIncome.php` ✅

### 8. Financial Insurance Module
- **Table**: `financial_insurance`, `insurance_claims`
- **Purpose**: Manage insurance policies and claims
- **Key Fields**: policy_number, coverage_amount, premium_amount, claim_status
- **Account Integration**: Creates prepaid/expense accounts
- **GL Posting**: 
  - Premium: Debit Insurance Expense, Credit Cash
  - Claim: Debit Cash/Receivable, Credit Insurance Recovery
- **Component**: `app/Http/Livewire/Accounting/FinancialInsurance.php` ✅

### 9. Interest Payable Module
- **Table**: `interest_payables`
- **Purpose**: Track accrued interest obligations
- **Key Fields**: amount, interest_rate, payment_frequency
- **Account Integration**: Creates liability accounts
- **GL Posting**: Debit Interest Expense, Credit Interest Payable
- **Component**: `app/Http/Livewire/Accounting/InterestPayable.php` ✅

### 10. Unearned Revenue Module
- **Table**: `unearned_deferred_revenue`
- **Purpose**: Track revenue received but not yet earned
- **Key Fields**: amount, description, is_recognized
- **Account Integration**: Creates liability accounts
- **GL Posting**: 
  - Receipt: Debit Cash, Credit Unearned Revenue
  - Recognition: Debit Unearned Revenue, Credit Revenue
- **Component**: `app/Http/Livewire/Accounting/Unearned.php` ✅

## Integration Service

### BalanceSheetItemIntegrationService
**Location**: `app/Services/BalanceSheetItemIntegrationService.php`

**Key Methods**:
- `createTradeReceivableAccount()` - For trade receivables
- `createPPEAccount()` - For PPE assets
- `createInvestmentAccount()` - For investments
- `createTradePayableAccount()` - For trade payables
- `createCreditorAccount()` - For creditors
- `createInterestPayableAccount()` - For interest payable
- `createUnearnedRevenueAccount()` - For unearned revenue
- `createFinancialInsuranceAccount()` - For insurance

**Process Flow**:
1. Receives data from business module
2. Creates/updates account in accounts table
3. Posts transaction to general_ledger
4. Returns success/failure status

## Accounts Table Structure

The `accounts` table serves as the single source of truth for all financial data:

**Key Fields**:
- `account_number` - Unique account identifier
- `account_name` - Descriptive name
- `account_type` - ASSET, LIABILITY, EQUITY, REVENUE, EXPENSE
- `balance` - Current balance
- `major_category_code` - Top-level categorization
- `category_code` - Mid-level categorization  
- `sub_category_code` - Detailed categorization

## Financial Statements Data Source

### Statement of Financial Position
- **Component**: `StatementOfFinancialPosition.php`
- **Data Source**: Reads from `accounts` table
- **Categories**:
  - Assets: Codes 1000-1999
  - Liabilities: Codes 2000-2999
  - Equity: Codes 3000-3999

### Statement of Comprehensive Income
- **Component**: `StatementOfComprehensiveIncome.php`
- **Data Source**: Reads from `accounts` table and `general_ledger`
- **Categories**:
  - Revenue: Codes 4000-4999
  - Expenses: Codes 5000-5999

### Statement of Cash Flows
- **Component**: `StatementOfCashFlows.php`
- **Data Source**: Analyzes transactions in `general_ledger`
- **Categories**:
  - Operating Activities
  - Investing Activities
  - Financing Activities

### Statement of Changes in Equity
- **Component**: `StatementOfChangesInEquity.php`
- **Data Source**: Reads from `accounts` table (equity accounts)
- **Tracks**: Share capital, retained earnings, reserves

## Transaction Flow Examples

### Example 1: Recording a Trade Receivable
```
1. User creates invoice in TradeAndOtherReceivables component
2. Data saved to trade_receivables table
3. Integration service called:
   - Creates/updates AR account in accounts table
   - Posts to general_ledger:
     - Dr: Accounts Receivable (1500)
     - Cr: Revenue Account (4000)
4. Statement of Financial Position shows updated AR balance
```

### Example 2: PPE Purchase and Depreciation
```
1. User records PPE purchase in PpeManagement component
2. Data saved to ppe_assets table
3. Integration service:
   - Creates PPE account in accounts table
   - Posts purchase to GL:
     - Dr: PPE Account (1600)
     - Cr: Cash/Payable (1000/2000)
4. Monthly depreciation:
   - Updates accumulated_depreciation in ppe_assets
   - Posts to GL:
     - Dr: Depreciation Expense (5200)
     - Cr: Accumulated Depreciation (1650)
5. Statement shows net book value (Cost - Accumulated Dep)
```

### Example 3: Investment Income Recognition
```
1. Interest accrued on investment
2. Investment component calculates interest
3. Integration service:
   - Updates investment income account
   - Posts to GL:
     - Dr: Interest Receivable (1550)
     - Cr: Interest Income (4100)
4. Income statement shows interest income
```

## Data Validation Rules

1. **Account Number Generation**: Automated using branch + category + sequence
2. **Balance Validation**: Debits must equal credits for each transaction
3. **Period Closure**: No backdated entries after period close
4. **Approval Workflow**: Transactions above threshold require approval
5. **Audit Trail**: All changes logged in audit tables

## Reporting Capabilities

Each business module provides:
1. **Detailed Reports**: Transaction-level details
2. **Summary Reports**: Aggregated balances
3. **Aging Analysis**: For receivables/payables
4. **Movement Reports**: Changes over period
5. **Reconciliation**: Module balance vs GL balance

## Implementation Status

✅ **Completed**:
- All business module tables created
- Integration service implemented
- Components integrated with accounts table
- GL posting mechanisms in place

🔄 **In Progress**:
- Enhanced CRUD interfaces for new tables
- Additional validation rules
- Advanced reporting features

## Best Practices

1. **Always use Integration Service** for account updates
2. **Never directly modify** accounts table balances
3. **Maintain transaction atomicity** - use database transactions
4. **Log all integration attempts** for debugging
5. **Reconcile regularly** between modules and GL
6. **Archive old data** to maintain performance

## Troubleshooting

### Common Issues:
1. **Balance Mismatch**: Check GL entries for incomplete transactions
2. **Missing Accounts**: Verify integration service was called
3. **Duplicate Entries**: Check for retry logic in failed transactions
4. **Performance**: Index key fields, archive old data

### Debug Tools:
- Check `storage/logs/laravel.log` for integration errors
- Use `php artisan balance-sheet:sync` to reconcile
- Query `financial_statement_audit_logs` for changes

## Future Enhancements

1. **Real-time Synchronization**: Webhooks for instant updates
2. **Multi-currency Support**: Foreign exchange handling
3. **Consolidated Reporting**: Multi-branch aggregation
4. **AI-powered Analytics**: Predictive insights
5. **Blockchain Audit Trail**: Immutable transaction log