# Loan Disbursement Process Documentation

## Overview

The loan disbursement process handles three main types of loans with different transaction flows and accounting treatments. This document explains how each loan type is processed during disbursement.

## Loan Types

### 1. "New" - Standard New Loans

**Purpose**: Standard new loan disbursements for first-time or additional loans to members.

**Process Flow**:
1. **Account Creation**: Creates loan-specific accounts (loan, interest, charges, insurance)
2. **Deduction Processing**: Processes all applicable deductions as separate transactions
3. **External Service Processing**: For non-cash payments, processes external services first with retry logic
4. **Main Disbursement**: Single transaction for net disbursement amount based on payment method
5. **Repayment Schedule**: Generates repayment schedule
6. **Control Numbers**: Creates billing control numbers
7. **Notifications**: Sends email and SMS notifications

**Transaction Sequence**:
1. **Deductions** (separate transactions for each):
   - Charges: Credit bank account, debit charges account
   - Insurance: Credit bank account, debit insurance account  
   - First Interest: Credit bank account, debit interest account
   - Outside Settlements: Credit bank account, debit liability account

2. **External Service Processing** (for non-cash payments):
   - **internal_transfer**: NBC Internal Fund Transfer Service
   - **tips_mno**: TIPS Bank-to-Wallet transfer via NBC Payment Service
   - **tips_bank**: TIPS Bank-to-Bank transfer via NBC Payment Service
   - **Retry Logic**: Up to 3 attempts with exponential backoff
   - **Failure Handling**: Comprehensive error handling and recovery strategies

3. **Main Disbursement Transaction**:
   - **For CASH payments**: Debit loan account, credit member's selected deposit account
   - **For all other payment methods**: Credit bank account, debit loan account

**External Services Integration**:
- **NBC Internal Fund Transfer**: Uses `InternalFundTransferService` for internal NBC account transfers
- **TIPS MNO**: Uses `NbcPaymentService` for mobile money transfers (M-PESA, Airtel Money, etc.)
- **TIPS Bank**: Uses `NbcPaymentService` for interbank transfers via TIPS system
- **Service Validation**: All external services must return successful responses before main transaction posting
- **Error Handling**: Comprehensive error handling and logging for all external service calls

**Error Handling & Recovery Strategy**:

**When Step 3 (External Service) Fails**:

1. **Retry Mechanism**:
   - Up to 3 retry attempts with exponential backoff (2s, 4s, 8s)
   - Detailed logging of each attempt
   - Automatic retry for transient failures

2. **Failure Handling by Payment Method**:

   **For `internal_transfer` failures**:
   - Check if member has deposit accounts for cash fallback
   - If deposit accounts exist: Offer cash disbursement option
   - If no deposit accounts: Require manual intervention
   - Update loan status to `EXTERNAL_SERVICE_FAILED`

   **For `tips_mno` failures**:
   - Log detailed failure information
   - Update loan status to `EXTERNAL_SERVICE_FAILED`
   - Require manual verification of phone number and MNO provider
   - Send notifications to member and staff

   **For `tips_bank` failures**:
   - Log detailed failure information
   - Update loan status to `EXTERNAL_SERVICE_FAILED`
   - Require manual verification of bank account and bank code
   - Send notifications to member and staff

3. **Notification System**:
   - **Member Notifications**: Email and SMS about failure and next steps
   - **Internal Notifications**: Admin/staff alerts for manual review
   - **Detailed Logging**: Comprehensive audit trail for troubleshooting

4. **Loan Status Management**:
   - Status updated to `EXTERNAL_SERVICE_FAILED`
   - Failure reason, date, and payment method recorded
   - Maintains loan in system for manual resolution

5. **Manual Review Process**:
   - Failed loans appear in admin dashboard for review
   - Staff can manually process or change payment method
   - Clear error messages guide resolution process

**Key Features**:
- Net disbursement amount = Approved loan amount - Total deductions
- User selects deposit account for cash disbursements
- All deductions processed as separate transactions for proper accounting
- External services processed first with success validation
- Comprehensive retry logic with exponential backoff
- Robust error handling with fallback options
- Complete notification system for failures
- Secure data masking for sensitive information in logs
- Manual review workflow for failed disbursements

---

### 2. "TopUp" - Top-up Loans

**Purpose**: Additional funds added to an existing loan, with closure of the old loan.

**Process Flow**:
1. **Account Creation**: Creates loan-specific accounts (loan, interest, charges, insurance)
2. **Deduction Processing**: Processes all applicable deductions
3. **Net Disbursement Calculation**: Calculates net disbursement as (approved loan amount minus all deductions)
4. **Top-up Processing**: Closes old loan and processes top-up amount
5. **Repayment Schedule**: Generates new repayment schedule
6. **Control Numbers**: Creates billing control numbers
7. **Notifications**: Sends email and SMS notifications

**Transaction Sequence**:
```
1. Charges Transaction (if applicable)
   - Debit: Loan charges account from loan sub-product
   - Credit: Bank account
   - Amount: Charges amount

2. Insurance Transaction (if applicable)
   - Debit: Loan insurance account from loan sub-product
   - Credit: Bank account
   - Amount: Insurance amount

3. First Interest Transaction (if applicable)
   - Debit: Loan interest account from loan sub-product
   - Credit: Bank account
   - Amount: First interest amount

4. Outside Settlements Transaction (if applicable)
   - Debit: Liability account from institutions table
   - Credit: Bank account
   - Amount: Outside settlements amount

5. TOP-UP LOAN PROCESSING
   a. Calculate outstanding amount on old loan
   b. Close old loan (status = 'CLOSED')
   c. Update old loan repayments
   d. Process top-up transaction:
      - Debit: Old loan account
      - Credit: Bank account
      - Amount: Outstanding amount
   e. Process remainder transaction:
      - Debit: New loan account
      - Credit: Bank account
      - Amount: Net disbursement (approved amount - all deductions - outstanding amount)
```

**Key Features**:
- Closes the existing loan being topped up
- Calculates outstanding balance on old loan
- Calculates net disbursement as approved amount minus all deductions
- Updates repayment schedules
- Processes two transactions: old loan closure + net disbursement to member
- Full notification system (email + SMS)

---

### 3. "Restructuring" - Restructuring Loans

**Purpose**: Modifies existing loan terms without additional disbursement.

**Process Flow**:
1. **Account Creation**: Creates loan-specific accounts (loan, interest, charges, insurance)
2. **Deduction Processing**: Processes all applicable deductions
3. **Restructuring Processing**: Updates loan statuses and generates new schedule
4. **Repayment Schedule**: Generates new repayment schedule
5. **Control Numbers**: Creates billing control numbers
6. **Notifications**: Sends email and SMS notifications

**Transaction Sequence**:
```
1. Charges Transaction (if applicable)
   - Debit: Loan charges account from loan sub-product
   - Credit: Bank account
   - Amount: Charges amount

2. Insurance Transaction (if applicable)
   - Debit: Loan insurance account from loan sub-product
   - Credit: Bank account
   - Amount: Insurance amount

3. First Interest Transaction (if applicable)
   - Debit: Loan interest account from loan sub-product
   - Credit: Bank account
   - Amount: First interest amount

4. Outside Settlements Transaction (if applicable)
   - Debit: Liability account from institutions table
   - Credit: Bank account
   - Amount: Outside settlements amount

5. RESTRUCTURING PROCESSING
   a. Update original loan status to 'RESTRUCTURED'
   b. Update new loan status to 'ACTIVE'
   c. Generate new repayment schedule based on new terms
   d. NO FUNDS TRANSFERRED - only schedule changes
```

**Key Features**:
- No actual funds are transferred
- Only repayment schedule is modified
- Original loan is marked as 'RESTRUCTURED'
- New loan is marked as 'ACTIVE'
- Full notification system (email + SMS)

---

## Common Elements Across All Loan Types

### Account Creation
All loan types create the following accounts:
- **Loan Account**: Member's specific loan account
- **Interest Account**: For interest tracking
- **Charges Account**: For fees and charges
- **Insurance Account**: For insurance premiums

### Deduction Processing
All loan types process these deductions (if applicable):
- **Charges**: Processing fees, legal fees, etc.
- **Insurance**: Loan insurance premiums
- **First Interest**: Upfront interest collection
- **Outside Settlements**: External loan settlements

### Notification System
All loan types send:
- **Email Notifications**: Detailed loan information with payment instructions
- **SMS Notifications**: Concise payment reminders with control numbers

### Control Numbers
All loan types generate billing control numbers for:
- Loan repayment tracking
- Payment processing
- Reconciliation purposes

---

## Technical Implementation

### Key Methods

1. **`disburseLoan($payMethod, $loanType, $productCode)`**
   - Main entry point for all loan disbursements
   - Determines loan type and routes to appropriate processing

2. **`processAllLoanTransactions()`**
   - Handles deduction processing for all loan types
   - Routes to loan-type-specific processing based on `$loanType`

3. **`processMainLoanDisbursementTransaction()`**
   - Handles main disbursement for "New" and "Auto" loans
   - Credits bank account, debits loan account

4. **`processTopUpLoanTransaction()`**
   - Handles top-up loan processing
   - Closes old loan and processes top-up amount

5. **`processRestructuringTransaction()`**
   - Handles restructuring processing
   - Updates loan statuses and generates new schedule

### Database Updates

All loan types update the loans table with:
- Status changes
- Account numbers
- Disbursement details
- Net disbursement amounts
- Monthly installments

### Error Handling

- Comprehensive logging at each step
- Transaction rollback on failures
- Detailed error messages for debugging
- Validation at each processing stage

---

## Summary

| Loan Type | Funds Transferred | Old Loan Status | New Loan Status | Main Transaction |
|-----------|-------------------|-----------------|-----------------|------------------|
| **New** | Yes | N/A | ACTIVE | Single disbursement |
| **TopUp** | Yes | CLOSED | ACTIVE | Old loan closure + new disbursement |
| **Restructuring** | No | RESTRUCTURED | ACTIVE | Schedule changes only |

Each loan type follows the same initial process (account creation, deductions) but differs in the final disbursement/processing step based on the specific requirements of that loan type. 