# NBC SACCOS UAT Test Execution Guide

## Overview
This guide provides step-by-step instructions for executing each UAT test case. Each test case includes the complete user journey from login to verification of expected results.

---

## 1. SECURITY AND ACCESS CONTROL

### SEC-01: User Login with Valid Credentials
**Tester**: Manager  
**Expected Result**: Login successful, access granted to appropriate dashboard

**Test Journey:**
1. Open web browser and navigate to the SACCOS system URL
2. Enter valid username in the login field
3. Enter valid password in the password field
4. Click "Login" button
5. **Verify**: System redirects to appropriate dashboard based on user role
6. **Verify**: User profile/name is displayed in the top navigation
7. **Verify**: Dashboard shows relevant widgets and menu options
8. **Verify**: No error messages are displayed

**Test Data**: Use pre-configured manager account credentials

---

### SEC-02: User Login with Invalid Credentials
**Tester**: Manager  
**Expected Result**: Access denied with appropriate error message

**Test Journey:**
1. Open web browser and navigate to the SACCOS system URL
2. Enter valid username in the login field
3. Enter **incorrect** password in the password field
4. Click "Login" button
5. **Verify**: System displays error message "Invalid credentials" or similar
6. **Verify**: User remains on login page
7. **Verify**: Password field is cleared for security
8. Repeat steps 2-7 with **incorrect** username and correct password
9. **Verify**: System displays appropriate error message
10. Repeat steps 2-7 with both **incorrect** username and password
11. **Verify**: System displays appropriate error message

**Test Data**: Use valid username with wrong password, wrong username with valid password, both wrong

---

### SEC-03: Role-Based Access Control Validation
**Tester**: Manager  
**Expected Result**: System denies access to restricted actions based on user role

**Test Journey:**
1. Login with a **Loan Officer** account
2. Navigate to different sections of the system
3. **Verify**: Loan Officer can access loan-related functions
4. **Verify**: Loan Officer **cannot** access Board Chair functions
5. **Verify**: Loan Officer **cannot** access System Administration functions
6. Logout and login with **Accountant** account
7. **Verify**: Accountant can access accounting functions
8. **Verify**: Accountant **cannot** access Board Chair approval functions
9. Logout and login with **Board Chair** account
10. **Verify**: Board Chair can access all approval functions
11. **Verify**: Board Chair can see all loan applications
12. Try to access restricted URLs directly in browser address bar
13. **Verify**: System redirects to appropriate page or shows access denied

**Test Data**: Use accounts with different roles (Loan Officer, Accountant, Board Chair)

---

### SEC-04: Audit Trail Verification
**Tester**: Manager  
**Expected Result**: All transactions and actions are logged correctly with user details

**Test Journey:**
1. Login with any user account
2. Perform several actions (create member, process loan, etc.)
3. Navigate to **System Logs** or **Audit Trail** section
4. **Verify**: All actions performed are logged with:
   - User ID and name
   - Timestamp
   - Action performed
   - IP address
   - User agent
5. **Verify**: Log entries are in chronological order
6. **Verify**: Log entries cannot be modified by regular users
7. Test with different user accounts
8. **Verify**: Each user's actions are properly attributed

**Test Data**: Use multiple user accounts to perform various actions

---

## 2. MEMBER MANAGEMENT

### MM-01: Add New Member with Valid Details
**Tester**: Staff  
**Expected Result**: Member added successfully, unique member ID generated

**Test Journey:**
1. Login with Staff account
2. Navigate to **Member Management** → **Add New Member**
3. Fill in the member registration form:
   - First Name: "John"
   - Middle Name: "Michael"
   - Last Name: "Doe"
   - Date of Birth: "1990-01-15"
   - Gender: "Male"
   - National ID: "1234567890123456"
   - Mobile Phone: "255712345678"
   - Email: "john.doe@email.com"
   - Address: "123 Main Street, Dar es Salaam"
   - Membership Type: "Individual"
4. Upload required documents (ID copy, photo)
5. Click "Save Member"
6. **Verify**: System displays success message
7. **Verify**: Unique member ID is generated (e.g., "MEM001234")
8. **Verify**: Member appears in member list
9. **Verify**: Member status is "Active"
10. **Verify**: All entered information is saved correctly

**Test Data**: Complete member information with valid data

---

### MM-02: Add Member with Missing Mandatory Fields
**Tester**: Staff  
**Expected Result**: System displays validation error for missing fields

**Test Journey:**
1. Login with Staff account
2. Navigate to **Member Management** → **Add New Member**
3. Fill in **partial** member information:
   - First Name: "Jane"
   - Last Name: "Smith"
   - **Leave National ID empty**
   - **Leave Mobile Phone empty**
4. Click "Save Member"
5. **Verify**: System displays validation errors for missing fields
6. **Verify**: Error messages are clear and specific
7. **Verify**: Form remains on the same page
8. **Verify**: No member record is created
9. Fill in the missing fields and try again
10. **Verify**: Member is created successfully after validation passes

**Test Data**: Incomplete member information missing NIN and mobile number

---

### MM-03: Search for Existing Member
**Tester**: Staff  
**Expected Result**: Member profile displayed correctly with all details

**Test Journey:**
1. Login with Staff account
2. Navigate to **Member Management** → **Member Search**
3. Search by **Member ID**: Enter "MEM001234"
4. Click "Search"
5. **Verify**: Member profile is displayed
6. **Verify**: All member details are shown correctly
7. Search by **Name**: Enter "John Doe"
8. **Verify**: Member appears in search results
9. Search by **Phone Number**: Enter "255712345678"
10. **Verify**: Member appears in search results
11. Search by **National ID**: Enter "1234567890123456"
12. **Verify**: Member appears in search results
13. Click on member name to view full profile
14. **Verify**: Complete member information is displayed

**Test Data**: Use existing member created in MM-01

---

### MM-04: Update Member Details
**Tester**: Staff  
**Expected Result**: Details updated successfully with audit trail

**Test Journey:**
1. Login with Staff account
2. Navigate to **Member Management** → **Member Search**
3. Search for existing member (from MM-01)
4. Click "Edit Member" button
5. Update member information:
   - Change phone number to "255712345679"
   - Change email to "john.doe.new@email.com"
   - Update address to "456 New Street, Dar es Salaam"
6. Click "Update Member"
7. **Verify**: System displays success message
8. **Verify**: Updated information is saved
9. **Verify**: Member profile shows new information
10. Navigate to **Audit Trail** or **Member History**
11. **Verify**: Update action is logged with:
    - User who made the change
    - Timestamp
    - Old values vs new values
12. **Verify**: Audit trail shows what was changed

**Test Data**: Use existing member and update contact information

---

### MM-05: Deactivate/Exit Member
**Tester**: Staff  
**Expected Result**: Member status updated to "Inactive" with maker/checker controls

**Test Journey:**
1. Login with **Staff** account (Maker)
2. Navigate to **Member Management** → **Member Search**
3. Search for existing member
4. Click "Deactivate Member" button
5. Enter reason for deactivation: "Member requested exit"
6. Click "Submit for Approval"
7. **Verify**: System displays "Submitted for approval" message
8. **Verify**: Member status shows "Pending Deactivation"
9. Logout and login with **Manager** account (Checker)
10. Navigate to **Approvals** → **Pending Approvals**
11. **Verify**: Member deactivation request appears in list
12. Review the request and click "Approve"
13. **Verify**: System displays approval confirmation
14. **Verify**: Member status changes to "Inactive"
15. **Verify**: Member cannot login to portal
16. **Verify**: Audit trail shows maker and checker actions

**Test Data**: Use existing member and follow maker/checker workflow

---

### MM-06: Prevent Duplicate Member Registration
**Tester**: Staff  
**Expected Result**: System rejects with error "Member already exists"

**Test Journey:**
1. Login with Staff account
2. Navigate to **Member Management** → **Add New Member**
3. Fill in member information with **existing National ID**:
   - First Name: "Different"
   - Last Name: "Person"
   - National ID: "1234567890123456" (same as existing member)
   - Mobile Phone: "255712345680"
   - Email: "different@email.com"
4. Click "Save Member"
5. **Verify**: System displays error "Member with this National ID already exists"
6. **Verify**: Form remains on the same page
7. **Verify**: No duplicate member is created
8. Try with **existing mobile phone number**
9. **Verify**: System displays appropriate error message
10. Try with **existing email address**
11. **Verify**: System displays appropriate error message

**Test Data**: Use National ID, phone, and email from existing member

---

### MM-07: Edit Member Details
**Tester**: Staff  
**Expected Result**: System saves updated details with proper validation

**Test Journey:**
1. Login with Staff account
2. Navigate to **Member Management** → **Member Search**
3. Search for existing member
4. Click "Edit Member" button
5. Update **phone number** to "255712345681"
6. Update **email** to "updated.email@email.com"
7. Update **account number** if applicable
8. Update **personal information** (address, occupation)
9. Click "Update Member"
10. **Verify**: System validates new phone number format
11. **Verify**: System validates new email format
12. **Verify**: System displays success message
13. **Verify**: All updated information is saved correctly
14. **Verify**: Member profile reflects all changes
15. **Verify**: Validation prevents invalid data entry

**Test Data**: Use existing member and update various fields

---

## 3. LOAN MANAGEMENT

### LM-01: Member Access to NBC SACCOS Portal
**Tester**: Member  
**Expected Result**: Member can log in using correct credentials

**Test Journey:**
1. Open web browser and navigate to **Member Portal URL**
2. Enter member credentials:
   - Username: Member number or email
   - Password: Member's portal password
3. Click "Login"
4. **Verify**: System redirects to member dashboard
5. **Verify**: Member's name is displayed
6. **Verify**: Dashboard shows member's account summary
7. **Verify**: Navigation menu shows available options
8. **Verify**: Member can see their loan applications
9. **Verify**: Member can see their savings balance
10. **Verify**: Member can see their share balance

**Test Data**: Use member portal credentials for existing member

---

### LM-02: Login with Correct Username and Wrong Password
**Tester**: Member  
**Expected Result**: Access denied with "incorrect password" error

**Test Journey:**
1. Open web browser and navigate to **Member Portal URL**
2. Enter **correct** username (member number/email)
3. Enter **incorrect** password
4. Click "Login"
5. **Verify**: System displays "Incorrect password" error message
6. **Verify**: User remains on login page
7. **Verify**: Password field is cleared
8. **Verify**: Username field retains the entered value
9. **Verify**: System does not reveal if username exists
10. Try logging in again with correct password
11. **Verify**: Login is successful

**Test Data**: Use correct member credentials but wrong password

---

### LM-03: Login with Wrong Username and Correct Password
**Tester**: Member  
**Expected Result**: Access denied with "incorrect username" error

**Test Journey:**
1. Open web browser and navigate to **Member Portal URL**
2. Enter **incorrect** username (non-existent member number/email)
3. Enter **correct** password
4. Click "Login"
5. **Verify**: System displays "Incorrect username" error message
6. **Verify**: User remains on login page
7. **Verify**: Both fields are cleared
8. **Verify**: System does not reveal if password is correct
9. Try with correct username and password
10. **Verify**: Login is successful

**Test Data**: Use wrong member credentials but correct password format

---

### LM-04: Login with Wrong Username and Wrong Password
**Tester**: Member  
**Expected Result**: Access denied with "incorrect username and password" error

**Test Journey:**
1. Open web browser and navigate to **Member Portal URL**
2. Enter **incorrect** username
3. Enter **incorrect** password
4. Click "Login"
5. **Verify**: System displays "Incorrect username and password" error message
6. **Verify**: User remains on login page
7. **Verify**: Both fields are cleared
8. **Verify**: System provides generic error message
9. Try with correct credentials
10. **Verify**: Login is successful

**Test Data**: Use completely wrong credentials

---

### LM-05: View Loan Dashboard and Products
**Tester**: Member  
**Expected Result**: Member can see loan catalogue and available products

**Test Journey:**
1. Login to member portal (from LM-01)
2. Navigate to **Loans** section
3. **Verify**: Loan dashboard is displayed
4. **Verify**: Available loan products are shown:
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
5. **Verify**: Each product shows:
   - Interest rate
   - Maximum amount
   - Maximum tenure
   - Requirements
6. **Verify**: Member can click on any product for details
7. **Verify**: Product details are displayed clearly

**Test Data**: Use member with access to loan products

---

### LM-06: Select Required Loan Product
**Tester**: Member  
**Expected Result**: Product selected successfully, displays existing loans and applied loans

**Test Journey:**
1. Login to member portal
2. Navigate to **Loans** section
3. Click on **"ChapChap"** loan product
4. **Verify**: Product details page is displayed
5. **Verify**: Product information is shown:
   - Interest rate
   - Maximum amount
   - Maximum tenure
   - Eligibility criteria
6. Click **"Apply for this Loan"** button
7. **Verify**: System shows member's existing loans (if any)
8. **Verify**: System shows previously applied loans (if any)
9. **Verify**: Member can see their loan history
10. **Verify**: System displays eligibility status
11. **Verify**: Member can proceed with new application

**Test Data**: Use member with existing loan history

---

### LM-07: Use Loan Calculator for Eligibility
**Tester**: Member  
**Expected Result**: Member can compute DSR and get decision

**Test Journey:**
1. Login to member portal
2. Navigate to **Loans** → **Loan Calculator**
3. Select loan product: **"ChapChap"**
4. Enter loan details:
   - Amount: "500,000"
   - Tenure: "12 months"
   - Purpose: "Business"
5. Click **"Calculate"** button
6. **Verify**: System displays:
   - Monthly installment amount
   - Total interest
   - Total amount to be repaid
   - DSR (Debt Service Ratio)
7. **Verify**: System shows eligibility decision:
   - "Eligible" or "Not Eligible"
   - Reason if not eligible
8. **Verify**: System shows required documents
9. **Verify**: Member can adjust amount/tenure and recalculate
10. **Verify**: DSR calculation is accurate

**Test Data**: Use realistic loan amounts and tenures

---

### LM-08: Application with Breaches Moves to Deviation Queue
**Tester**: Member  
**Expected Result**: System shows breaches and allows document attachment

**Test Journey:**
1. Login to member portal
2. Navigate to **Loans** → **Apply for Loan**
3. Select loan product and enter details that may cause breaches:
   - Amount: Very high amount
   - Tenure: Very long tenure
   - Or other parameters that exceed limits
4. Submit application
5. **Verify**: System identifies breaches/limits exceeded
6. **Verify**: System displays breach details:
   - Which limits are exceeded
   - By how much
   - Required actions
7. **Verify**: System allows document attachment:
   - Additional income proof
   - Business registration
   - Bank statements
   - Other supporting documents
8. **Verify**: Application moves to "Deviation Queue"
9. **Verify**: Member can track application status
10. **Verify**: System notifies loan officer of deviation

**Test Data**: Use loan parameters that exceed normal limits

---

### LM-09: Accept Terms and Conditions
**Tester**: Member  
**Expected Result**: Member can accept terms for loan product, amount, and tenure

**Test Journey:**
1. Login to member portal
2. Navigate to **Loans** → **Apply for Loan**
3. Select loan product and enter details
4. **Verify**: Terms and conditions are displayed
5. **Verify**: Terms include:
   - Interest rate and calculation method
   - Repayment schedule
   - Penalty for late payment
   - Default consequences
   - Member obligations
6. Read through the terms
7. Check the **"I accept the terms and conditions"** checkbox
8. **Verify**: Checkbox is required to proceed
9. **Verify**: Member cannot proceed without accepting
10. Click **"Accept and Continue"**
11. **Verify**: System proceeds to next step
12. **Verify**: Acceptance is recorded with timestamp

**Test Data**: Use any loan product with standard terms

---

### LM-10: Receive OTP and Submit Application
**Tester**: Member  
**Expected Result**: Member receives OTP via email/SMS and submits application request

**Test Journey:**
1. Complete loan application up to terms acceptance (from LM-09)
2. Click **"Submit Application"**
3. **Verify**: System requests OTP verification
4. **Verify**: OTP is sent to member's registered email/SMS
5. Check email/SMS for OTP code
6. Enter OTP in the verification field
7. Click **"Verify and Submit"**
8. **Verify**: System validates OTP
9. **Verify**: Application is submitted successfully
10. **Verify**: System displays confirmation message
11. **Verify**: Application reference number is generated
12. **Verify**: Member receives confirmation email/SMS
13. **Verify**: Application appears in "Pending" status
14. **Verify**: Member can track application status

**Test Data**: Use member with valid email and phone number

---

*[This is the first part of the comprehensive test execution guide. The document continues with the remaining test cases for all modules.]*
