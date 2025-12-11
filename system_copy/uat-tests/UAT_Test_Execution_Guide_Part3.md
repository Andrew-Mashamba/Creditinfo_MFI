# NBC SACCOS UAT Test Execution Guide - Part 3

## 3. LOAN MANAGEMENT (Final Test Cases)

### LM-32: Credit Bureau Submission
**Tester**: Manager  
**Expected Result**: Online submission and template functionality

**Test Journey:**
1. Login as manager
2. Navigate to **Loans** → **Credit Bureau** → **Submit to Bureau**
3. **Verify**: Submission interface is available
4. **Verify**: Template selection is provided:
   - Standard template
   - Custom template
5. Select loan applications for submission
6. **Verify**: Selected applications are listed
7. **Verify**: System validates data completeness
8. Click "Submit to Credit Bureau"
9. **Verify**: System connects to credit bureau
10. **Verify**: Submission is successful
11. **Verify**: Confirmation receipt is received
12. **Verify**: Submission is logged in audit trail

**Test Data**: Use approved loan applications ready for bureau submission

---

### LM-33: Loan Approval Procedures
**Tester**: Manager  
**Expected Result**: Loan status changes to "Approved" with proper workflow

**Test Journey:**
1. Login as manager
2. Navigate to **Loans** → **Approvals** → **Pending Approvals**
3. **Verify**: Pending approvals are listed
4. Select a loan application for approval
5. **Verify**: Complete application details are displayed
6. **Verify**: All approvals are in place:
   - Loan officer recommendation
   - Committee approval
   - Board approval (if required)
7. Review all documentation
8. Click "Approve Loan"
9. **Verify**: System requests final confirmation
10. Confirm approval
11. **Verify**: Loan status changes to "Approved"
12. **Verify**: Approval workflow is completed
13. **Verify**: All stakeholders are notified
14. **Verify**: Loan is ready for disbursement

**Test Data**: Use loan application with all required approvals

---

### LM-34: Disburse Approved Loan
**Tester**: Accountant  
**Expected Result**: Loan balance created, disbursement recorded, accounting entries posted

**Test Journey:**
1. Login as accountant
2. Navigate to **Loans** → **Disbursement** → **Approved Loans**
3. **Verify**: Approved loans are listed
4. Select a loan for disbursement
5. **Verify**: Loan details are displayed
6. **Verify**: Disbursement amount is calculated:
   - Principal amount
   - Less: Processing fees
   - Less: Insurance
   - Less: First interest
   - = Net disbursement
7. Select disbursement method:
   - Bank transfer
   - Cash
   - Mobile money
8. Enter disbursement details
9. Click "Process Disbursement"
10. **Verify**: Disbursement is processed successfully
11. **Verify**: Loan account is created with negative balance
12. **Verify**: Accounting entries are posted:
   - Debit: Loan account
   - Credit: Bank/Cash account
13. **Verify**: Member is notified of disbursement
14. **Verify**: Disbursement receipt is generated

**Test Data**: Use approved loan with complete documentation

---

### LM-35: Loan Repayment Processing
**Tester**: Accountant  
**Expected Result**: Loan balance reduced correctly with GL effects

**Test Journey:**
1. Login as accountant
2. Navigate to **Loans** → **Repayments** → **Process Repayment**
3. Search for active loan
4. **Verify**: Loan details are displayed:
   - Outstanding balance
   - Next due date
   - Installment amount
5. Enter repayment details:
   - Amount paid
   - Payment method
   - Payment date
6. **Verify**: System calculates allocation:
   - Principal payment
   - Interest payment
   - Penalty (if applicable)
7. Click "Process Repayment"
8. **Verify**: Repayment is processed successfully
9. **Verify**: Loan balance is reduced
10. **Verify**: Accounting entries are posted:
   - Debit: Bank/Cash account
   - Credit: Loan account
11. **Verify**: Repayment schedule is updated
12. **Verify**: Member receives receipt

**Test Data**: Use active loan with outstanding balance

---

### LM-36: Loan Penalty Application
**Tester**: Accountant  
**Expected Result**: Penalty automatically applied on late repayments

**Test Journey:**
1. Login as accountant
2. Navigate to **Loans** → **Penalties** → **Calculate Penalties**
3. **Verify**: System identifies overdue loans
4. **Verify**: Penalty calculation is shown:
   - Days overdue
   - Penalty rate
   - Penalty amount
5. **Verify**: System applies penalties automatically
6. **Verify**: Penalty is added to loan balance
7. **Verify**: Accounting entries are created:
   - Debit: Penalty receivable
   - Credit: Penalty income
8. **Verify**: Member is notified of penalty
9. **Verify**: Penalty is included in next payment
10. **Verify**: Penalty history is maintained

**Test Data**: Use loans with overdue payments

---

### LM-37: Overdue Loans Aging Analysis
**Tester**: Accountant  
**Expected Result**: Aging analysis report generated correctly

**Test Journey:**
1. Login as accountant
2. Navigate to **Reports** → **Loan Reports** → **Aging Analysis**
3. **Verify**: Aging analysis report is generated
4. **Verify**: Loans are categorized by age:
   - Current (0-30 days)
   - 31-60 days overdue
   - 61-90 days overdue
   - 91-180 days overdue
   - Over 180 days overdue
5. **Verify**: Each category shows:
   - Number of loans
   - Total amount
   - Percentage of portfolio
6. **Verify**: Report can be filtered by:
   - Date range
   - Loan product
   - Branch
7. **Verify**: Report can be exported to Excel/PDF
8. **Verify**: Report includes recommendations

**Test Data**: Use system with loans in various aging categories

---

## 4. SAVINGS MANAGEMENT

### SV-01: Open Savings Account
**Tester**: Accountant  
**Expected Result**: Savings account created successfully

**Test Journey:**
1. Login as accountant
2. Navigate to **Savings** → **New Account** → **Open Savings Account**
3. Search for member by name/number
4. **Verify**: Member details are displayed
5. Select savings product:
   - Regular savings
   - Fixed deposit
   - Emergency fund
6. **Verify**: Product details are shown:
   - Interest rate
   - Minimum balance
   - Terms and conditions
7. Enter account details:
   - Initial deposit amount
   - Account name
   - Beneficiary details
8. **Verify**: System validates minimum deposit
9. Click "Create Account"
10. **Verify**: Savings account is created
11. **Verify**: Account number is generated
12. **Verify**: Account appears in member's account list
13. **Verify**: Initial deposit is recorded

**Test Data**: Use existing member and standard savings product

---

### SV-02: Deposit Savings
**Tester**: Accountant  
**Expected Result**: Balance updated, accounting entry recorded, receipt generated

**Test Journey:**
1. Login as accountant
2. Navigate to **Savings** → **Transactions** → **Deposit**
3. Search for savings account
4. **Verify**: Account details are displayed:
   - Current balance
   - Account holder
   - Account type
5. Enter deposit details:
   - Amount
   - Payment method
   - Reference number
   - Narration
6. **Verify**: System validates deposit amount
7. Click "Process Deposit"
8. **Verify**: Deposit is processed successfully
9. **Verify**: Account balance is updated
10. **Verify**: Accounting entries are posted:
   - Debit: Bank/Cash account
   - Credit: Savings account
11. **Verify**: Receipt is generated
12. **Verify**: Member is notified (if configured)
13. **Verify**: Transaction appears in account history

**Test Data**: Use existing savings account

---

### SV-03: Withdraw Savings Within Balance
**Tester**: Accountant  
**Expected Result**: Balance updated correctly with maker/checker controls

**Test Journey:**
1. Login as accountant (Maker)
2. Navigate to **Savings** → **Transactions** → **Withdrawal**
3. Search for savings account
4. **Verify**: Account details and balance are displayed
5. Enter withdrawal details:
   - Amount (within available balance)
   - Withdrawal reason
   - Payment method
6. **Verify**: System validates withdrawal amount
7. **Verify**: System checks minimum balance requirements
8. Click "Submit for Approval"
9. **Verify**: Withdrawal request is submitted
10. **Verify**: Status shows "Pending Approval"
11. Logout and login as manager (Checker)
12. Navigate to **Approvals** → **Savings Withdrawals**
13. **Verify**: Withdrawal request appears in list
14. Review request and click "Approve"
15. **Verify**: Withdrawal is processed
16. **Verify**: Account balance is reduced
17. **Verify**: Accounting entries are posted
18. **Verify**: Maker/checker actions are logged

**Test Data**: Use savings account with sufficient balance

---

### SV-04: Withdraw Savings Exceeding Balance
**Tester**: Accountant  
**Expected Result**: System prevents transaction with "no overdraft" error

**Test Journey:**
1. Login as accountant
2. Navigate to **Savings** → **Transactions** → **Withdrawal**
3. Search for savings account
4. **Verify**: Account balance is displayed
5. Enter withdrawal amount **greater than** available balance
6. Click "Submit for Approval"
7. **Verify**: System displays error message:
   - "Insufficient balance"
   - "No overdraft allowed"
8. **Verify**: Withdrawal request is rejected
9. **Verify**: No transaction is created
10. **Verify**: Account balance remains unchanged
11. Try with amount equal to available balance
12. **Verify**: System allows withdrawal of full balance
13. **Verify**: Account balance becomes zero

**Test Data**: Use savings account with limited balance

---

### SV-05: View Savings Statement
**Tester**: Accountant  
**Expected Result**: Accurate savings history displayed with export capability

**Test Journey:**
1. Login as accountant
2. Navigate to **Savings** → **Reports** → **Account Statement**
3. Search for savings account
4. Select date range for statement
5. **Verify**: Statement is generated with:
   - Account holder details
   - Account information
   - Transaction history
   - Running balance
6. **Verify**: All transactions are included:
   - Deposits
   - Withdrawals
   - Interest credits
   - Fees (if any)
7. **Verify**: Statement shows:
   - Opening balance
   - Closing balance
   - Total deposits
   - Total withdrawals
8. **Verify**: Statement can be exported to:
   - PDF format
   - Excel format
9. **Verify**: Statement can be printed
10. **Verify**: Statement includes bank details and contact info

**Test Data**: Use savings account with transaction history

---

### SV-06: Savings Transfers Between Accounts
**Tester**: Accountant  
**Expected Result**: Transfer processed with proper GL effects

**Test Journey:**
1. Login as accountant
2. Navigate to **Savings** → **Transactions** → **Transfer**
3. Select source account (from account)
4. Select destination account (to account)
5. **Verify**: Both account details are displayed
6. Enter transfer details:
   - Amount
   - Transfer reason
   - Reference number
7. **Verify**: System validates:
   - Source account has sufficient balance
   - Both accounts are active
   - Transfer amount is valid
8. Click "Process Transfer"
9. **Verify**: Transfer is processed successfully
10. **Verify**: Source account balance is reduced
11. **Verify**: Destination account balance is increased
12. **Verify**: Accounting entries are posted:
   - Debit: Source account
   - Credit: Destination account
13. **Verify**: Transfer receipt is generated
14. **Verify**: Both account holders are notified

**Test Data**: Use two different savings accounts

---

### SV-07: Bulk Savings Import
**Tester**: Accountant  
**Expected Result**: System allows bulk savings imports

**Test Journey:**
1. Login as accountant
2. Navigate to **Savings** → **Bulk Operations** → **Import Transactions**
3. **Verify**: Import interface is available
4. **Verify**: Template download is provided
5. Download import template
6. Prepare import file with:
   - Account numbers
   - Transaction amounts
   - Transaction types
   - Dates
   - References
7. Upload import file
8. **Verify**: System validates file format
9. **Verify**: System shows preview of transactions
10. **Verify**: System validates each transaction:
   - Account exists
   - Amount is valid
   - Date is valid
11. Click "Process Import"
12. **Verify**: Bulk transactions are processed
13. **Verify**: Import report is generated
14. **Verify**: Failed transactions are reported
15. **Verify**: Successful transactions are posted

**Test Data**: Use properly formatted import file with valid transactions

---

### SV-08: Interest Calculation on Savings
**Tester**: Accountant  
**Expected Result**: Interest calculated monthly with GL effects to payables

**Test Journey:**
1. Login as accountant
2. Navigate to **Savings** → **Interest** → **Calculate Interest**
3. **Verify**: Interest calculation interface is available
4. Select calculation period (monthly)
5. **Verify**: System identifies all savings accounts
6. **Verify**: System calculates interest for each account:
   - Daily balance method
   - Interest rate per product
   - Minimum balance requirements
7. **Verify**: Interest calculations are displayed
8. **Verify**: Total interest amount is shown
9. Click "Post Interest"
10. **Verify**: Interest is posted to accounts
11. **Verify**: Accounting entries are created:
   - Debit: Interest expense
   - Credit: Interest payable
12. **Verify**: Member accounts are credited
13. **Verify**: Interest statements are generated
14. **Verify**: Interest posting is logged

**Test Data**: Use system with savings accounts earning interest

---

## 5. SHARE MANAGEMENT

### SM-01: Share Purchase
**Tester**: Accountant  
**Expected Result**: Share balance increases, GL updated with proper workflow

**Test Journey:**
1. Login as accountant
2. Navigate to **Shares** → **Transactions** → **Share Purchase**
3. Search for member
4. **Verify**: Member details are displayed
5. **Verify**: Current share balance is shown
6. Enter share purchase details:
   - Number of shares
   - Price per share
   - Payment method
   - Source account
7. **Verify**: System calculates total amount
8. **Verify**: System validates:
   - Member eligibility
   - Sufficient funds
   - Share availability
9. Click "Process Purchase"
10. **Verify**: Share purchase is processed
11. **Verify**: Member's share balance increases
12. **Verify**: Accounting entries are posted:
   - Debit: Bank/Cash account
   - Credit: Share capital account
13. **Verify**: Share certificate is generated
14. **Verify**: Purchase is recorded in share register

**Test Data**: Use member with available funds for share purchase

---

### SM-02: Share Redemption
**Tester**: Accountant  
**Expected Result**: Share balance decreases, payout recorded, GL reflects correctly

**Test Journey:**
1. Login as accountant
2. Navigate to **Shares** → **Transactions** → **Share Redemption**
3. Search for member with shares
4. **Verify**: Member's share balance is displayed
5. Enter redemption details:
   - Number of shares to redeem
   - Redemption reason
   - Payment method
6. **Verify**: System validates:
   - Sufficient shares available
   - Redemption eligibility
   - Minimum shareholding requirements
7. **Verify**: System calculates redemption value
8. Click "Process Redemption"
9. **Verify**: Share redemption is processed
10. **Verify**: Member's share balance decreases
11. **Verify**: Redemption payment is made
12. **Verify**: Accounting entries are posted:
   - Debit: Share capital account
   - Credit: Bank/Cash account
13. **Verify**: Redemption is recorded
14. **Verify**: Member is notified

**Test Data**: Use member with sufficient shareholding

---

### SM-03: Share Statement Generation
**Tester**: Accountant  
**Expected Result**: Report shows opening balance, purchases, redemptions, closing balance

**Test Journey:**
1. Login as accountant
2. Navigate to **Shares** → **Reports** → **Share Statement**
3. Search for member
4. Select date range for statement
5. **Verify**: Share statement is generated with:
   - Member details
   - Opening share balance
   - Share purchases during period
   - Share redemptions during period
   - Closing share balance
6. **Verify**: Statement shows:
   - Transaction dates
   - Transaction types
   - Number of shares
   - Price per share
   - Total amounts
7. **Verify**: Statement can be exported to PDF/Excel
8. **Verify**: Statement includes share certificate information
9. **Verify**: Statement shows current share value

**Test Data**: Use member with share transaction history

---

### SM-04: Share Certificate Generation
**Tester**: Accountant  
**Expected Result**: Certificate generated successfully

**Test Journey:**
1. Login as accountant
2. Navigate to **Shares** → **Certificates** → **Generate Certificate**
3. Search for member
4. **Verify**: Member's shareholding is displayed
5. **Verify**: Certificate details are shown:
   - Member name
   - Number of shares
   - Share value
   - Certificate number
6. Click "Generate Certificate"
7. **Verify**: Certificate is generated
8. **Verify**: Certificate includes:
   - SACCO details
   - Member information
   - Share details
   - Issue date
   - Signature and seal
9. **Verify**: Certificate can be printed
10. **Verify**: Certificate can be saved as PDF
11. **Verify**: Certificate is logged in system

**Test Data**: Use member with shareholding

---

*[This completes the detailed test execution guide for all major modules. The guide provides step-by-step instructions for each of the 89 test cases, ensuring testers have clear guidance on how to execute each test scenario.]*
