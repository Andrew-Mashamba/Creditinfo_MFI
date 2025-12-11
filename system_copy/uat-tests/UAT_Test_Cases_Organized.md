# NBC SACCOS UAT TEST CASES 2025

## Test Execution Information
- **Project**: NBC SACCOS Core System
- **Version**: 2025
- **Test Type**: User Acceptance Testing (UAT)
- **Environment**: Production-like UAT Environment
- **Test Period**: [To be filled during execution]

---

## 1. SECURITY AND ACCESS CONTROL

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| SEC-01 | Manager | User login with valid credentials | Login successful, access granted to appropriate dashboard | | | |
| SEC-02 | Manager | User login with invalid credentials | Access denied with appropriate error message | | | |
| SEC-03 | Manager | Role-based access control validation | System denies access to restricted actions based on user role | | | |
| SEC-04 | Manager | Audit trail verification | All transactions and actions are logged correctly with user details | | | |

---

## 2. MEMBER MANAGEMENT

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| MM-01 | Staff | Add new member with valid details | Member added successfully, unique member ID generated | | | |
| MM-02 | Staff | Add member with missing mandatory fields (NIN, mobile no) | System displays validation error for missing fields | | | |
| MM-03 | Staff | Search for existing member | Member profile displayed correctly with all details | | | |
| MM-04 | Staff | Update member details | Details updated successfully with audit trail | | | |
| MM-05 | Staff | Deactivate/Exit member | Member status updated to "Inactive" with maker/checker controls | | | |
| MM-06 | Staff | Prevent duplicate member registration | System rejects with error "Member already exists" | | | |
| MM-07 | Staff | Edit member details (phone/email/account no/personal info) | System saves updated details with proper validation | | | |

---

## 3. LOAN MANAGEMENT

### 3.1 Member Portal Loan Application

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| LM-01 | Member | Member access to NBC SACCOS Portal | Member can log in using correct credentials | | | |
| LM-02 | Member | Login with correct username and wrong password | Access denied with "incorrect password" error | | | |
| LM-03 | Member | Login with wrong username and correct password | Access denied with "incorrect username" error | | | |
| LM-04 | Member | Login with wrong username and wrong password | Access denied with "incorrect username and password" error | | | |
| LM-05 | Member | View loan dashboard and products | Member can see loan catalogue and available products | | | |
| LM-06 | Member | Select required loan product | Product selected successfully, displays existing loans and applied loans | | | |
| LM-07 | Member | Use loan calculator for eligibility | Member can compute DSR and get decision | | | |
| LM-08 | Member | Application with breaches moves to deviation queue | System shows breaches and allows document attachment | | | |
| LM-09 | Member | Accept terms and conditions | Member can accept terms for loan product, amount, and tenure | | | |
| LM-10 | Member | Receive OTP and submit application | Member receives OTP via email/SMS and submits application | | | |

### 3.2 Loan Officer Operations

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| LM-11 | Loan Officer | Loan officer portal access | Loan officer can log in using correct credentials | | | |
| LM-12 | Loan Officer | View loan applications from web portal | Loan officer can see all web portal applications | | | |
| LM-13 | Loan Officer | Review computed DSR and make recommendation | Loan officer can verify loan details and recommend to committee | | | |
| LM-14 | Loan Officer | Apply loan via NBC SACCOS Portal | Loan officer can apply loan through internal portal | | | |
| LM-15 | Loan Officer | Review and compute DSR for internal applications | Loan officer can compute DSR and verify loan details | | | |

### 3.3 Loan Committee Operations

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| LM-16 | Loan Committee | Committee member portal access | Committee member can log in using correct credentials | | | |
| LM-17 | Loan Committee | View all loan applications | Committee member can see all loan applications | | | |
| LM-18 | Loan Committee | Review DSR, charges, and make decision | Committee can verify loan details and make decisions | | | |

### 3.4 Accountant Operations

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| LM-19 | Accountant | Accountant portal access | Accountant can log in using correct credentials | | | |
| LM-20 | Accountant | View all loan applications | Accountant can see all loan applications | | | |
| LM-21 | Accountant | Review DSR, charges, and account entries | Accountant can verify and allocate transactional accounts | | | |

### 3.5 Board Chair Operations

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| LM-22 | Board Chair | Board Chairman portal access | Board Chairman can log in using correct credentials | | | |
| LM-23 | Board Chair | View loan applications | Board Chairman can see all loan applications | | | |
| LM-24 | Board Chair | Review lending parameters and make final decision | Board Chairman can verify parameters and approve/reject loans | | | |

### 3.6 Loan Product Configuration

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| LM-25 | Loan Officer | Configure loan products and charges | Proper product and fees configuration for all products | | | |
| LM-26 | Loan Officer | Loan liquidation process | System can liquidate liabilities correctly | | | |
| LM-27 | Loan Officer | Credit bureau integration | System integrated with credit bureau | | | |
| LM-28 | Loan Officer | Document attachment functionality | System allows single and multiple attachments | | | |
| LM-29 | Loan Officer | Exception/waiver loan queue | System provides queue for exception loans | | | |
| LM-30 | Loan Officer | Loan application form and terms | System accommodates terms and conditions acceptance | | | |
| LM-31 | Loan Officer | NBC SACCOS Butua rules | Proper rules configuration with savings limits and arrears checks | | | |

### 3.7 Loan Processing and Management

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| LM-32 | Manager | Credit bureau submission | Online submission and template functionality | | | |
| LM-33 | Manager | Loan approval procedures | Loan status changes to "Approved" with proper workflow | | | |
| LM-34 | Accountant | Disburse approved loan | Loan balance created, disbursement recorded, accounting entries posted | | | |
| LM-35 | Accountant | Loan repayment processing | Loan balance reduced correctly with GL effects | | | |
| LM-36 | Accountant | Loan penalty application | Penalty automatically applied on late repayments | | | |
| LM-37 | Accountant | Overdue loans aging analysis | Aging analysis report generated correctly | | | |

---

## 4. SAVINGS MANAGEMENT

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| SV-01 | Accountant | Open savings account | Savings account created successfully | | | |
| SV-02 | Accountant | Deposit savings | Balance updated, accounting entry recorded, receipt generated | | | |
| SV-03 | Accountant | Withdraw savings within balance | Balance updated correctly with maker/checker controls | | | |
| SV-04 | Accountant | Withdraw savings exceeding balance | System prevents transaction with "no overdraft" error | | | |
| SV-05 | Accountant | View savings statement | Accurate savings history displayed with export capability | | | |
| SV-06 | Accountant | Savings transfers between accounts | Transfer processed with proper GL effects | | | |
| SV-07 | Accountant | Bulk savings import | System allows bulk savings imports | | | |
| SV-08 | Accountant | Interest calculation on savings | Interest calculated monthly with GL effects to payables | | | |

---

## 5. SHARE MANAGEMENT

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| SM-01 | Accountant | Share purchase | Share balance increases, GL updated with proper workflow | | | |
| SM-02 | Accountant | Share redemption | Share balance decreases, payout recorded, GL reflects correctly | | | |
| SM-03 | Accountant | Share statement generation | Report shows opening balance, purchases, redemptions, closing balance | | | |
| SM-04 | Accountant | Share certificate generation | Certificate generated successfully | | | |

---

## 6. ACCOUNTING & FINANCE

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| AC-01 | Accountant | Double entry validation | Each transaction follows double-entry principles | | | |
| AC-02 | Accountant | Manual journal entry recording | Journal entries balanced and posted correctly | | | |
| AC-03 | Accountant | General ledger accounts listing | List of all individual ledger balances displayed | | | |
| AC-04 | Accountant | Trial balance generation | All accounts balance (debits = credits) | | | |
| AC-05 | Accountant | Financial reports generation | Correct financial reports displayed | | | |
| AC-06 | Accountant | Chart of accounts management | Add, activate, reject duplicate accounts | | | |
| AC-07 | Accountant | Bank reconciliation | Records match bank statement (all reconciled correctly) | | | |
| AC-08 | Accountant | Audit trail verification | All changes and transactions are logged | | | |

---

## 7. INCOME MANAGEMENT

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| INC-01 | Accountant | Record new income transaction | Transaction recorded and updated to respective income ledger | | | |
| INC-02 | Accountant | Automatic income recognition | Interest income posted automatically (accrual concept) | | | |
| INC-03 | Accountant | Edit/reverse income transaction | System allows correction with audit trail | | | |
| INC-04 | Accountant | Generate income report | Report shows correct income breakdown | | | |
| INC-05 | Accountant | Accrued income recognition | All incomes recognized on accrual basis with ledger effects | | | |
| INC-06 | Accountant | Income audit trail | Each income shows date, user, and references | | | |

---

## 8. EXPENSE MANAGEMENT

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| EXP-01 | Accountant | Record expense payment | Expense recorded, cash/bank reduced, expense increased | | | |
| EXP-02 | Accountant | Expense approval process | Expense posts only after approval | | | |
| EXP-03 | Accountant | Expense categorization | System classifies into correct GL expense code | | | |
| EXP-04 | Accountant | Generate expense report | Report displays correct totals | | | |
| EXP-05 | Accountant | Journal expense posting | All accounts affected correctly | | | |
| EXP-06 | Accountant | Payroll management | System manages staff payroll | | | |
| EXP-07 | Accountant | Budget control | Expenses within budget limits | | | |
| EXP-08 | Accountant | Accrued expenses | Expense recognized, liability created under Accounts Payable | | | |

---

## 9. ASSET MANAGEMENT

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| AST-01 | Accountant | PPE management | Acquisition, disposal, revaluation management of assets | | | |
| AST-02 | Accountant | Interest receivable management | Proper management of accrued interest on loans | | | |
| AST-03 | Accountant | Debtors/Account receivable | Proper reporting on account receivables over time | | | |

---

## 10. EQUITY AND LIABILITIES

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| EL-01 | Accountant | Accounts payable listing | Proper listing of payables over time | | | |
| EL-02 | Accountant | Settlement of accounts payable | Accounts Payable reduced, cash/bank reduced | | | |

---

## 11. REPORTS MANAGEMENT

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| RM-01 | Accountant | Generate member reports | Report shows all active and inactive members | | | |
| RM-02 | Accountant | Generate loan reports | Report on loan portfolio, aging analysis, disbursal, collection, overdue | | | |
| RM-03 | Accountant | Generate savings reports | Reports on deposits, withdrawals, balances | | | |
| RM-04 | Accountant | Generate accounting reports | Trial balance, notes to accounts, income statement, balance sheet, cash flow | | | |
| RM-05 | Accountant | Generate share reports | List of shareholdings | | | |
| RM-06 | Accountant | Regulatory provision reports (BoT classification) | Reports generated according to BoT requirements | | | |
| RM-07 | Accountant | Distribution reports (BoT classification) | Reports generated according to BoT requirements | | | |
| RM-08 | Accountant | TCDC, BoT compliance reports | All compliance reports generated automatically | | | |
| RM-09 | Accountant | System backup | Backup completed successfully | | | |
| RM-10 | Accountant | Audit trail reports | Audit trail available and accessible | | | |

---

## 12. SYSTEM INTEGRATION AND OPERATIONS

| Test Case ID | Tester | Test Case Description | Expected Result | Actual Results | Status | Comments |
|--------------|--------|----------------------|-----------------|----------------|---------|----------|
| SY-01 | Accountant | System restore from backup | System restored correctly | | | |
| SY-02 | Accountant | MNO payment integration | Members can make payments via MNOs and Banks | | | |
| SY-03 | Accountant | Bank integration | Members can transfer and make payments to control numbers | | | |
| SY-04 | Accountant | Offline operations | System works flexibly offline when required | | | |
| SY-05 | Accountant | High availability deployment | System provides room for changes and deployments | | | |
| SY-06 | Accountant | Multiple interfaces (Web, App, USSD) | System has web, App, and USSD interfaces | | | |
| SY-07 | Accountant | API integrations (MNOs, banks) | Proper integration between MNOs, control numbers, and bank accounts | | | |
| SY-08 | Accountant | Support matrix | System has proper support matrix | | | |
| SY-09 | Accountant | System licenses | All licenses are in place | | | |

---

## Test Execution Summary

### Test Statistics
- **Total Test Cases**: 89
- **Passed**: ___ / 89
- **Failed**: ___ / 89
- **Blocked**: ___ / 89
- **Not Executed**: ___ / 89

### Test Coverage by Module
- Security and Access Control: 4 test cases
- Member Management: 7 test cases
- Loan Management: 25 test cases
- Savings Management: 8 test cases
- Share Management: 4 test cases
- Accounting & Finance: 8 test cases
- Income Management: 6 test cases
- Expense Management: 8 test cases
- Asset Management: 3 test cases
- Equity and Liabilities: 2 test cases
- Reports Management: 10 test cases
- System Integration: 9 test cases

### Sign-off
- **Test Manager**: _________________ Date: _________
- **Business Analyst**: _________________ Date: _________
- **System Administrator**: _________________ Date: _________
- **End User Representative**: _________________ Date: _________

---

## Notes
- All test cases should be executed in a production-like environment
- Test data should be prepared before execution
- Any defects found should be logged in the defect tracking system
- Test results should be documented with screenshots where applicable
- Status column should be filled with: Pass, Fail, Blocked, or Not Executed
