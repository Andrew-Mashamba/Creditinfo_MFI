# NBC SACCOS UAT Test Execution Guide - Part 2

## 3. LOAN MANAGEMENT (Continued)

### LM-11: Loan Officer Portal Access
**Tester**: Loan Officer  
**Expected Result**: Loan officer can log in using correct credentials

**Test Journey:**
1. Open web browser and navigate to **Staff Portal URL**
2. Enter loan officer credentials:
   - Username: Loan officer's username
   - Password: Loan officer's password
3. Click "Login"
4. **Verify**: System redirects to loan officer dashboard
5. **Verify**: Dashboard shows:
   - Pending loan applications
   - Loan portfolio summary
   - Recent activities
6. **Verify**: Navigation menu shows loan officer functions
7. **Verify**: Loan officer can access loan management tools
8. **Verify**: System shows loan officer's name and role

**Test Data**: Use pre-configured loan officer account

---

### LM-12: View Loan Applications from Web Portal
**Tester**: Loan Officer  
**Expected Result**: Loan officer can see all web portal applications

**Test Journey:**
1. Login as loan officer (from LM-11)
2. Navigate to **Loans** → **Applications** → **Web Portal Applications**
3. **Verify**: List of applications from member portal is displayed
4. **Verify**: Each application shows:
   - Member name and number
   - Loan product
   - Amount requested
   - Application date
   - Status
5. **Verify**: Applications are sorted by date (newest first)
6. **Verify**: Loan officer can filter by:
   - Status (Pending, Under Review, Approved, Rejected)
   - Loan product
   - Date range
7. **Verify**: Loan officer can search by member name/number
8. Click on any application to view details
9. **Verify**: Complete application details are displayed

**Test Data**: Use system with existing web portal applications

---

### LM-13: Review Computed DSR and Make Recommendation
**Tester**: Loan Officer  
**Expected Result**: Loan officer can verify loan details and recommend to committee

**Test Journey:**
1. Login as loan officer
2. Navigate to **Loans** → **Applications** → **Pending Review**
3. Select a loan application for review
4. **Verify**: Application details are displayed
5. **Verify**: DSR calculation is shown:
   - Member's income
   - Existing loan obligations
   - New loan installment
   - DSR percentage
6. **Verify**: System shows DSR interpretation:
   - "Good" (DSR < 30%)
   - "Acceptable" (DSR 30-50%)
   - "High Risk" (DSR > 50%)
7. Review member's:
   - Credit history
   - Savings balance
   - Share balance
   - Employment status
8. Add loan officer comments
9. Select recommendation:
   - "Recommend for Approval"
   - "Recommend for Rejection"
   - "Request Additional Information"
10. Click "Submit Recommendation"
11. **Verify**: Recommendation is submitted to committee
12. **Verify**: Application status changes to "Under Committee Review"

**Test Data**: Use loan application with calculated DSR

---

### LM-14: Apply Loan via NBC SACCOS Portal
**Tester**: Loan Officer  
**Expected Result**: Loan officer can apply loan through internal portal

**Test Journey:**
1. Login as loan officer
2. Navigate to **Loans** → **New Application**
3. Search for member by name/number
4. Select member from search results
5. **Verify**: Member details are displayed
6. Select loan product from dropdown
7. Enter loan details:
   - Amount
   - Tenure
   - Purpose
   - Collateral details (if applicable)
8. **Verify**: System calculates DSR automatically
9. **Verify**: System shows loan terms and conditions
10. Upload required documents
11. Add loan officer notes
12. Click "Submit Application"
13. **Verify**: Application is created successfully
14. **Verify**: Application reference number is generated
15. **Verify**: Application appears in pending list

**Test Data**: Use existing member and standard loan product

---

### LM-15: Review and Compute DSR for Internal Applications
**Tester**: Loan Officer  
**Expected Result**: Loan officer can compute DSR and verify loan details

**Test Journey:**
1. Login as loan officer
2. Navigate to **Loans** → **Applications** → **Internal Applications**
3. Select an internal application for review
4. **Verify**: Application details are displayed
5. **Verify**: Member's financial information is shown
6. **Verify**: DSR calculation is displayed:
   - Monthly income
   - Existing obligations
   - New loan installment
   - DSR percentage
7. **Verify**: System allows DSR recalculation if needed
8. **Verify**: Loan officer can adjust parameters and recalculate
9. **Verify**: System shows risk assessment
10. **Verify**: Loan officer can add comments and recommendations
11. **Verify**: All calculations are accurate and properly formatted

**Test Data**: Use internal loan application with member financial data

---

### LM-16: Committee Member Portal Access
**Tester**: Loan Committee  
**Expected Result**: Committee member can log in using correct credentials

**Test Journey:**
1. Open web browser and navigate to **Staff Portal URL**
2. Enter committee member credentials
3. Click "Login"
4. **Verify**: System redirects to committee dashboard
5. **Verify**: Dashboard shows:
   - Applications pending committee review
   - Committee meeting schedule
   - Recent decisions
6. **Verify**: Navigation menu shows committee functions
7. **Verify**: Committee member can access decision-making tools

**Test Data**: Use pre-configured committee member account

---

### LM-17: View All Loan Applications
**Tester**: Loan Committee  
**Expected Result**: Committee member can see all loan applications

**Test Journey:**
1. Login as committee member (from LM-16)
2. Navigate to **Loans** → **Committee Review**
3. **Verify**: All loan applications are displayed
4. **Verify**: Applications are categorized by:
   - Pending Committee Review
   - Approved by Committee
   - Rejected by Committee
5. **Verify**: Each application shows:
   - Member details
   - Loan amount and terms
   - Loan officer recommendation
   - DSR calculation
   - Supporting documents
6. **Verify**: Committee can filter and search applications
7. **Verify**: Committee can view application history

**Test Data**: Use system with various loan applications

---

### LM-18: Review DSR, Charges, and Make Decision
**Tester**: Loan Committee  
**Expected Result**: Committee can verify loan details and make decisions

**Test Journey:**
1. Login as committee member
2. Navigate to **Loans** → **Committee Review** → **Pending Decisions**
3. Select a loan application
4. **Verify**: Complete application details are displayed
5. **Verify**: DSR calculation and interpretation are shown
6. **Verify**: All charges and fees are itemized
7. **Verify**: Supporting documents are accessible
8. **Verify**: Loan officer recommendation is displayed
9. Review all information thoroughly
10. Make committee decision:
    - "Approve"
    - "Reject"
    - "Request More Information"
11. Add committee comments
12. Click "Submit Decision"
13. **Verify**: Decision is recorded with timestamp
14. **Verify**: Application status is updated
15. **Verify**: Member and loan officer are notified

**Test Data**: Use loan application with complete documentation

---

### LM-19: Accountant Portal Access
**Tester**: Accountant  
**Expected Result**: Accountant can log in using correct credentials

**Test Journey:**
1. Open web browser and navigate to **Staff Portal URL**
2. Enter accountant credentials
3. Click "Login"
4. **Verify**: System redirects to accountant dashboard
5. **Verify**: Dashboard shows:
   - Approved loans pending disbursement
   - Accounting transactions
   - Financial reports
6. **Verify**: Navigation menu shows accounting functions
7. **Verify**: Accountant can access financial management tools

**Test Data**: Use pre-configured accountant account

---

### LM-20: View All Loan Applications
**Tester**: Accountant  
**Expected Result**: Accountant can see all loan applications

**Test Journey:**
1. Login as accountant (from LM-19)
2. Navigate to **Loans** → **Accounting Review**
3. **Verify**: All loan applications are displayed
4. **Verify**: Applications are categorized by:
   - Approved - Pending Disbursement
   - Disbursed
   - Active Loans
5. **Verify**: Each application shows:
   - Member details
   - Loan amount and terms
   - Committee decision
   - Accounting entries required
6. **Verify**: Accountant can filter by status and date
7. **Verify**: Accountant can view accounting impact

**Test Data**: Use system with approved loan applications

---

### LM-21: Review DSR, Charges, and Account Entries
**Tester**: Accountant  
**Expected Result**: Accountant can verify and allocate transactional accounts

**Test Journey:**
1. Login as accountant
2. Navigate to **Loans** → **Accounting Review**
3. Select an approved loan application
4. **Verify**: Loan details are displayed
5. **Verify**: DSR and charges are shown
6. **Verify**: Required accounting entries are listed:
   - Loan account (debit)
   - Bank account (credit)
   - Interest account
   - Charges account
7. **Verify**: Accountant can allocate accounts:
   - Select appropriate GL accounts
   - Verify account balances
   - Confirm accounting treatment
8. **Verify**: System shows accounting impact
9. **Verify**: Accountant can add accounting notes
10. **Verify**: All entries are properly balanced

**Test Data**: Use approved loan application with accounting requirements

---

### LM-22: Board Chairman Portal Access
**Tester**: Board Chair  
**Expected Result**: Board Chairman can log in using correct credentials

**Test Journey:**
1. Open web browser and navigate to **Staff Portal URL**
2. Enter board chairman credentials
3. Click "Login"
4. **Verify**: System redirects to board chairman dashboard
5. **Verify**: Dashboard shows:
   - High-value loans pending approval
   - Committee decisions for review
   - Board meeting schedule
6. **Verify**: Navigation menu shows board functions
7. **Verify**: Board chairman has highest level access

**Test Data**: Use pre-configured board chairman account

---

### LM-23: View Loan Applications
**Tester**: Board Chair  
**Expected Result**: Board Chairman can see all loan applications

**Test Journey:**
1. Login as board chairman (from LM-22)
2. Navigate to **Loans** → **Board Review**
3. **Verify**: All loan applications are displayed
4. **Verify**: Applications are categorized by:
   - High-value loans (> threshold)
   - Committee approved - pending board approval
   - Board approved
   - Board rejected
5. **Verify**: Each application shows:
   - Complete loan details
   - Committee decision and rationale
   - Risk assessment
   - Financial impact
6. **Verify**: Board chairman can access all application details

**Test Data**: Use system with various loan applications

---

### LM-24: Review Lending Parameters and Make Final Decision
**Tester**: Board Chair  
**Expected Result**: Board Chairman can verify parameters and approve/reject loans

**Test Journey:**
1. Login as board chairman
2. Navigate to **Loans** → **Board Review** → **Pending Board Approval**
3. Select a loan application
4. **Verify**: Complete application details are displayed
5. **Verify**: All lending parameters are shown:
   - DSR calculation
   - Collateral value
   - Member's financial position
   - Risk assessment
6. **Verify**: Committee decision and rationale are displayed
7. **Verify**: Board chairman can review all supporting documents
8. Review lending parameters against board policies
9. Make final decision:
   - "Approve"
   - "Reject"
   - "Request Additional Information"
10. Add board chairman comments
11. Click "Submit Final Decision"
12. **Verify**: Decision is recorded with timestamp
13. **Verify**: Application status is updated to "Board Approved/Rejected"
14. **Verify**: All stakeholders are notified

**Test Data**: Use loan application approved by committee

---

### LM-25: Configure Loan Products and Charges
**Tester**: Loan Officer  
**Expected Result**: Proper product and fees configuration for all products

**Test Journey:**
1. Login as loan officer with admin privileges
2. Navigate to **Products** → **Loan Products** → **Manage Products**
3. **Verify**: All 10 loan products are listed:
   - Onja
   - ChapChap
   - Dharura
   - Maendeleo Mkubwa
   - Maendeleo Mdogo
   - Business Loan
   - NBC SACCOS Butua
   - Wastaafu loan
   - Wastaafu loan (dharura)
   - Sikuku
4. Select "ChapChap" product for configuration
5. **Verify**: Product details are displayed:
   - Interest rate
   - Maximum amount
   - Maximum tenure
   - Minimum amount
   - Processing fees
6. Update product configuration:
   - Interest rate: "15%"
   - Maximum amount: "2,000,000"
   - Processing fee: "2%"
7. Click "Update Product"
8. **Verify**: Changes are saved successfully
9. **Verify**: Updated configuration is applied to new applications
10. Test with other products

**Test Data**: Use existing loan products with current configurations

---

### LM-26: Loan Liquidation Process
**Tester**: Loan Officer  
**Expected Result**: System can liquidate liabilities correctly

**Test Journey:**
1. Login as loan officer
2. Navigate to **Loans** → **Loan Management** → **Liquidation**
3. Search for a loan to liquidate
4. **Verify**: Loan details are displayed
5. **Verify**: Outstanding balance is shown
6. **Verify**: Liquidation options are available:
   - Full liquidation
   - Partial liquidation
   - Liquidation with penalty
7. Select liquidation type
8. Enter liquidation amount
9. **Verify**: System calculates:
   - Principal amount
   - Interest amount
   - Penalty (if applicable)
   - Total liquidation amount
10. Add liquidation reason
11. Click "Process Liquidation"
12. **Verify**: Liquidation is processed successfully
13. **Verify**: Loan status changes to "Liquidated"
14. **Verify**: Accounting entries are created
15. **Verify**: Member is notified

**Test Data**: Use existing loan with outstanding balance

---

### LM-27: Credit Bureau Integration
**Tester**: Loan Officer  
**Expected Result**: System integrated with credit bureau

**Test Journey:**
1. Login as loan officer
2. Navigate to **Loans** → **Credit Bureau** → **Member Credit Check**
3. Enter member number or National ID
4. Click "Check Credit"
5. **Verify**: System connects to credit bureau
6. **Verify**: Credit report is retrieved and displayed:
   - Credit score
   - Credit history
   - Existing loans
   - Payment history
7. **Verify**: System interprets credit score:
   - Excellent (800+)
   - Good (700-799)
   - Fair (600-699)
   - Poor (<600)
8. **Verify**: Credit report influences loan decision
9. **Verify**: Credit check is logged in audit trail

**Test Data**: Use member with credit bureau record

---

### LM-28: Document Attachment Functionality
**Tester**: Loan Officer  
**Expected Result**: System allows single and multiple attachments

**Test Journey:**
1. Login as loan officer
2. Navigate to **Loans** → **Applications** → **New Application**
3. Create new loan application
4. **Verify**: Document upload section is available
5. **Verify**: Required documents are listed:
   - National ID copy
   - Payslip/Income proof
   - Bank statements
   - Business registration (if applicable)
6. Upload single document:
   - Click "Choose File"
   - Select document
   - Click "Upload"
7. **Verify**: Document is uploaded successfully
8. **Verify**: Document appears in attachments list
9. Upload multiple documents:
   - Select multiple files
   - Click "Upload All"
10. **Verify**: All documents are uploaded
11. **Verify**: Documents can be viewed and downloaded
12. **Verify**: Document types are validated

**Test Data**: Use various document types (PDF, JPG, PNG)

---

### LM-29: Exception/Waiver Loan Queue
**Tester**: Loan Officer  
**Expected Result**: System provides queue for exception loans

**Test Journey:**
1. Login as loan officer
2. Navigate to **Loans** → **Exception Queue**
3. **Verify**: Exception loans are displayed
4. **Verify**: Each exception shows:
   - Member details
   - Loan amount
   - Exception reason
   - DSR breach details
   - Supporting documents
5. **Verify**: Exception types are categorized:
   - DSR exceedance
   - Amount exceedance
   - Tenure exceedance
   - Other exceptions
6. Select an exception loan
7. **Verify**: Exception details are displayed
8. **Verify**: Loan officer can:
   - Approve exception
   - Reject exception
   - Request additional information
9. **Verify**: Exception decisions are tracked
10. **Verify**: Approved exceptions proceed to committee

**Test Data**: Use loans that exceed normal parameters

---

### LM-30: Loan Application Form and Terms
**Tester**: Loan Officer  
**Expected Result**: System accommodates terms and conditions acceptance

**Test Journey:**
1. Login as loan officer
2. Navigate to **Loans** → **Applications** → **New Application**
3. Create new loan application
4. **Verify**: Terms and conditions section is displayed
5. **Verify**: Terms include:
   - Interest rate and calculation
   - Repayment terms
   - Penalty clauses
   - Default consequences
   - Member obligations
6. **Verify**: Terms are specific to selected loan product
7. **Verify**: Member must accept terms to proceed
8. **Verify**: Terms acceptance is recorded with timestamp
9. **Verify**: Terms can be updated and versioned
10. **Verify**: Updated terms apply to new applications

**Test Data**: Use different loan products with varying terms

---

### LM-31: NBC SACCOS Butua Rules
**Tester**: Loan Officer  
**Expected Result**: Proper rules configuration with savings limits and arrears checks

**Test Journey:**
1. Login as loan officer
2. Navigate to **Products** → **Loan Products** → **NBC SACCOS Butua**
3. **Verify**: Butua rules are configured:
   - Maximum amount = Member's savings balance
   - No arrears allowed
   - Monthly limit restrictions
4. Test Butua application:
   - Select member with savings
   - Apply for Butua loan
   - Enter amount within savings balance
5. **Verify**: System validates against savings balance
6. **Verify**: System checks for existing arrears
7. **Verify**: System enforces monthly limits
8. Test with member having arrears
9. **Verify**: System rejects application due to arrears
10. **Verify**: System provides clear rejection reasons

**Test Data**: Use members with various savings and arrears status

---

*[This continues with the remaining loan management test cases and other modules...]*
