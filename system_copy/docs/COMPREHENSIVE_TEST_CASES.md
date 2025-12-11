# SACCOS Core System - Comprehensive Test Cases

## Overview
This document contains comprehensive test cases for the SACCOS Core System, covering all major modules and functionalities. Each test case includes detailed descriptions, expected results, and status tracking.

## Test Case Structure
- **S/n**: Serial number
- **Module**: System module being tested
- **Test Case ID**: Unique identifier for the test case
- **Test Case Description**: Detailed description of what is being tested
- **Expected Result**: Expected outcome of the test
- **Status**: Test execution status (PASS/FAIL)

---

## 1. Member Management Module

### MM-01: Add New Member
- **Test Case ID**: MM-01
- **Test Case Description**: Add new member with valid details
- **Expected Result**: Member added successfully, unique ID generated
- **Status**: [ ] PENDING

### MM-02: Add Member with Missing Mandatory Fields
- **Test Case ID**: MM-02
- **Test Case Description**: Add member with missing mandatory fields (NIN, mobile no)
- **Expected Result**: System should display validation error
- **Status**: [ ] PENDING

### MM-03: Search for Existing Member
- **Test Case ID**: MM-03
- **Test Case Description**: Search for existing member
- **Expected Result**: Member profile displayed correctly
- **Status**: [ ] PENDING

### MM-04: Update Member Details
- **Test Case ID**: MM-04
- **Test Case Description**: Update member details
- **Expected Result**: Details updated successfully
- **Status**: [ ] PENDING

### MM-05: Deactivate/Exit Member
- **Test Case ID**: MM-05
- **Test Case Description**: Deactivate/Exit member
- **Expected Result**: Member status updated to "Inactive" - confirm maker/checker controls
- **Status**: [ ] PENDING

### MM-06: Prevent Duplicate Member Registration
- **Test Case ID**: MM-06
- **Test Case Description**: Prevent duplicate member registration
- **Expected Result**: System rejects with error "Member already exists"
- **Status**: [ ] PENDING

---

## 2. Savings Management Module

### SV-01: Deposit Savings
- **Test Case ID**: SV-01
- **Test Case Description**: Deposit savings
- **Expected Result**: Balance updated, accounting entry, receipt generated
- **Status**: [ ] PENDING

### SV-02: Withdraw Savings Within Balance
- **Test Case ID**: SV-02
- **Test Case Description**: Withdraw savings within balance
- **Expected Result**: Balance updated correctly, accounting entry, confirm maker/checker controls
- **Status**: [ ] PENDING

### SV-03: Withdraw Savings Exceeding Balance
- **Test Case ID**: SV-03
- **Test Case Description**: Withdraw savings exceeding balance
- **Expected Result**: System prevents transaction with error message (no overdraft)
- **Status**: [ ] PENDING

### SV-04: View Savings Statement
- **Test Case ID**: SV-04
- **Test Case Description**: View savings statement
- **Expected Result**: Accurate savings history displayed, export statements
- **Status**: [ ] PENDING

---

## 3. Share Management Module

### SM-001: Share Purchase
- **Test Case ID**: SM-001
- **Test Case Description**: Share Purchase
- **Expected Result**: Shares balance increases for member, GL updated and confirm share processing workflow
- **Status**: [ ] PENDING

### SM-002: Share Redemption
- **Test Case ID**: SM-002
- **Test Case Description**: Share Redemption
- **Expected Result**: Member share balance decreases, payout recorded, GL reflects correctly
- **Status**: [ ] PENDING

### SM-003: Share Statement
- **Test Case ID**: SM-003
- **Test Case Description**: Share Statement
- **Expected Result**: Report shows opening balance, share purchases, redemptions, and closing balance
- **Status**: [ ] PENDING

---

## 4. Loans Management Module

### LM-01: Apply for Loan with Valid Details
- **Test Case ID**: LM-01
- **Test Case Description**: Apply for loan with valid details
- **Expected Result**: Loan application recorded for approval procedures
- **Status**: [ ] PENDING

### LM-02: Apply for Loan with Missing Requirements
- **Test Case ID**: LM-02
- **Test Case Description**: Apply for loan with missing collateral/guarantor/Requirement
- **Expected Result**: System prompts for missing info
- **Status**: [ ] PENDING

### LM-03: Approval Procedures of Loan
- **Test Case ID**: LM-03
- **Test Case Description**: Approval Procedures of loan
- **Expected Result**: Loan status changes to "Approved", confirm loan processing workflows
- **Status**: [ ] PENDING

### LM-04: Disburse Approved Loan
- **Test Case ID**: LM-04
- **Test Case Description**: Disburse approved loan
- **Expected Result**: Loan balance created and disbursed amount deducted, accounting entry recorded
- **Status**: [ ] PENDING

### LM-05: Repay Loan (Valid Installment)
- **Test Case ID**: LM-05
- **Test Case Description**: Repay loan (valid installment)
- **Expected Result**: Loan balance reduced correctly, GL Effect
- **Status**: [ ] PENDING

---

## 5. Accounting & Finance Module

### AC-01: Double Entry Validation
- **Test Case ID**: AC-01
- **Test Case Description**: Double Entry Validation
- **Expected Result**: Ensure each transaction follows double-entry principles
- **Status**: [ ] PENDING

### AC-02: Journal Entry Recording
- **Test Case ID**: AC-02
- **Test Case Description**: Journal Entry Recording
- **Expected Result**: Manual journal entries, balanced and posted correctly
- **Status**: [ ] PENDING

### AC-03: General Ledger Accounts
- **Test Case ID**: AC-03
- **Test Case Description**: General Ledger accounts
- **Expected Result**: List of all individual ledger balances
- **Status**: [ ] PENDING

### AC-04: Trial Balance Generation
- **Test Case ID**: AC-04
- **Test Case Description**: Trial Balance Generation
- **Expected Result**: All accounts balance (debits = credits)
- **Status**: [ ] PENDING

### AC-05: Generate Financial Reports
- **Test Case ID**: AC-05
- **Test Case Description**: Generate Financial Reports
- **Expected Result**: Correct financial report displayed
- **Status**: [ ] PENDING

### AC-06: Chart of Accounts
- **Test Case ID**: AC-06
- **Test Case Description**: Chart of Accounts
- **Expected Result**: Add, activate, reject duplicate
- **Status**: [ ] PENDING

### AC-07: Reconciliations
- **Test Case ID**: AC-07
- **Test Case Description**: Reconciliations
- **Expected Result**: Match records vs. bank statement (all reconciled correctly)
- **Status**: [ ] PENDING

### AC-08: Audit Trail
- **Test Case ID**: AC-08
- **Test Case Description**: Audit Trail
- **Expected Result**: Ensure all changes and transactions are logged
- **Status**: [ ] PENDING

---

## 6. Reports Management Module

### RM-01: Generate Member Reports
- **Test Case ID**: RM-01
- **Test Case Description**: Generate member reports
- **Expected Result**: Report shows all active members, inactive clients
- **Status**: [ ] PENDING

### RM-02: Generate Loan Reports
- **Test Case ID**: RM-02
- **Test Case Description**: Generate loan reports
- **Expected Result**: Report on loan portfolio, aging analysis, disbursal, collection, overdue
- **Status**: [ ] PENDING

### RM-03: Generate Saving Reports
- **Test Case ID**: RM-03
- **Test Case Description**: Generate Saving Reports
- **Expected Result**: Reports on deposits, withdraw, balances
- **Status**: [ ] PENDING

### RM-04: Accounting Reports
- **Test Case ID**: RM-04
- **Test Case Description**: Accounting Reports
- **Expected Result**: Trial balance, note to accounts, income statement, balance sheet, changes in equity Cash flow, cash book report
- **Status**: [ ] PENDING

### RM-05: Share Reports
- **Test Case ID**: RM-05
- **Test Case Description**: Share reports
- **Expected Result**: List of shareholdings
- **Status**: [ ] PENDING

### RM-06: TCDC, BoT Compliance Reports
- **Test Case ID**: RM-06
- **Test Case Description**: TCDC, BoT compliance reports etc
- **Expected Result**: [To be defined]
- **Status**: [ ] PENDING

---

## Test Execution Summary

### Module Coverage
- [x] Member Management (6 test cases)
- [x] Savings Management (4 test cases)
- [x] Share Management (3 test cases)
- [x] Loans Management (5 test cases)
- [x] Accounting & Finance (8 test cases)
- [x] Reports Management (6 test cases)

### Total Test Cases: 32

### Status Legend
- [ ] PENDING - Test not yet executed
- [x] PASS - Test passed successfully
- [ ] FAIL - Test failed (requires investigation)

---

## Notes
- All test cases should be executed in a controlled test environment
- Test data should be prepared before execution
- Results should be documented with screenshots and detailed logs
- Failed tests should be investigated and retested after fixes
- Regular regression testing should be performed after system updates

---

*Document created: $(date)*
*Last updated: $(date)*
*Version: 1.0*
