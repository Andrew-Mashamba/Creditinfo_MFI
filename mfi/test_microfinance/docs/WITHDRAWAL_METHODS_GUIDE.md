# Savings Withdrawal Methods Documentation

## Overview

The SACCO system supports four different methods for withdrawing funds from member savings accounts. Each method is designed for specific use cases and involves different account combinations and processing workflows.

## Table of Contents

1. [Cash Withdrawal (Cash in Safe)](#1-cash-withdrawal-cash-in-safe)
2. [Internal Funds Transfer (NBC to NBC)](#2-internal-funds-transfer-nbc-to-nbc)
3. [TIPS Transfer to MNO Wallet](#3-tips-transfer-to-mno-wallet)
4. [TIPS Transfer to Other Bank](#4-tips-transfer-to-other-bank)
5. [Common Features](#common-features)
6. [Troubleshooting](#troubleshooting)

---

## 1. Cash Withdrawal (Cash in Safe)

### Description
Direct cash withdrawal where funds are transferred from the member's savings account to the cash in safe account, allowing physical cash to be dispensed to the member.

### Accounts Involved
- **Debit Account**: Member's savings account
- **Credit Account**: Cash in safe account

### Use Cases
- Immediate cash needs
- Small to medium withdrawal amounts
- When member prefers physical cash
- Emergency withdrawals

### How to Use

#### Step 1: Access Withdrawal Feature
1. Navigate to **Savings** → **Quick Actions**
2. Click **"Withdraw Savings"** button

#### Step 2: Verify Membership
1. Enter the **Membership Number**
2. Click **"Verify Membership"**
3. Ensure member details are displayed correctly

#### Step 3: Select Account and Amount
1. Choose the **savings account** from the dropdown
2. Verify the **account balance** displayed
3. Enter the **withdrawal amount**
4. Ensure sufficient balance is available

#### Step 4: Choose Withdrawal Method
1. Select **"Cash (Cash in Safe)"** radio button
2. The system will automatically:
   - Generate a reference number (format: `CASH-{UNIQUE_ID}`)
   - Set current date and time

#### Step 5: Complete Transaction
1. Enter **Name of Withdrawer**
2. Add **Narration** (optional but recommended)
3. Click **"Process Withdrawal"**

### Required Fields
- ✅ Membership Number
- ✅ Selected Account
- ✅ Withdrawal Amount
- ✅ Withdrawer Name
- ✅ Narration

### System Behavior
- Automatically generates reference number
- Validates account balance
- Posts transaction to general ledger
- Updates account balances in real-time

---

## 2. Internal Funds Transfer (NBC to NBC)

### Description
Transfer funds from the member's savings account to their NBC bank account through the SACCO's NBC account. This method uses NBC's Internal Fund Transfer API.

### Accounts Involved
- **Debit Account**: Member's savings account
- **Intermediate Account**: SACCO's cash at NBC account
- **Credit Account**: Member's NBC account

### Use Cases
- Large withdrawal amounts
- When member has an NBC account
- Secure electronic transfers
- Business transactions

### How to Use

#### Step 1: Access Withdrawal Feature
1. Navigate to **Savings** → **Quick Actions**
2. Click **"Withdraw Savings"** button

#### Step 2: Verify Membership
1. Enter the **Membership Number**
2. Click **"Verify Membership"**
3. Ensure member details are displayed correctly

#### Step 3: Select Account and Amount
1. Choose the **savings account** from the dropdown
2. Verify the **account balance** displayed
3. Enter the **withdrawal amount**
4. Ensure sufficient balance is available

#### Step 4: Choose Withdrawal Method
1. Select **"Internal Funds Transfer (NBC to NBC)"** radio button
2. The system will automatically:
   - Generate a reference number (format: `IFT-{UNIQUE_ID}`)
   - Set current date and time

#### Step 5: Enter NBC Account Details
1. **NBC Account Number**: Enter the member's NBC account number
2. **Account Holder Name**: Enter the name as it appears on the NBC account

#### Step 6: Complete Transaction
1. Enter **Name of Withdrawer**
2. Add **Narration** (optional but recommended)
3. Click **"Process Withdrawal"**

### Required Fields
- ✅ Membership Number
- ✅ Selected Account
- ✅ Withdrawal Amount
- ✅ NBC Account Number
- ✅ Account Holder Name
- ✅ Reference Number
- ✅ Withdrawal Date
- ✅ Withdrawal Time
- ✅ Withdrawer Name
- ✅ Narration

### System Behavior
- Validates NBC account through NBC API
- Processes internal fund transfer via NBC
- Posts transaction to general ledger
- Provides NBC transaction reference
- Updates account balances in real-time

### Important Notes
- NBC account must be active and valid
- Account holder name must match NBC records
- Transfer may take 1-2 business days to reflect in member's NBC account

---

## 3. TIPS Transfer to MNO Wallet

### Description
Transfer funds from the member's savings account to their mobile money wallet (M-PESA, Airtel Money, Tigo Pesa, etc.) using the TIPS (Tanzania Instant Payment System) network.

### Accounts Involved
- **Debit Account**: Member's savings account
- **Intermediate Account**: SACCO's cash at NBC account
- **Credit Account**: Member's mobile money wallet

### Use Cases
- Quick mobile money transfers
- When member prefers mobile money
- Small to medium amounts
- Instant transfers

### How to Use

#### Step 1: Access Withdrawal Feature
1. Navigate to **Savings** → **Quick Actions**
2. Click **"Withdraw Savings"** button

#### Step 2: Verify Membership
1. Enter the **Membership Number**
2. Click **"Verify Membership"**
3. Ensure member details are displayed correctly

#### Step 3: Select Account and Amount
1. Choose the **savings account** from the dropdown
2. Verify the **account balance** displayed
3. Enter the **withdrawal amount**
4. Ensure sufficient balance is available

#### Step 4: Choose Withdrawal Method
1. Select **"TIPS Transfer to MNO Wallet"** radio button
2. The system will automatically:
   - Generate a reference number (format: `TIPS-MNO-{UNIQUE_ID}`)
   - Set current date and time

#### Step 5: Enter Mobile Money Details
1. **Mobile Money Provider**: Select from dropdown:
   - M-PESA (VMCASHIN)
   - AIRTEL-MONEY (AMCASHIN)
   - TIGO-PESA (TPCASHIN)
   - HALLOTEL (HPCASHIN)
   - AZAMPESA (APCASHIN)
   - EZYPESA (ZPCASHIN)
2. **Phone Number**: Enter the mobile number (e.g., 0786123456)
3. **Wallet Holder Name**: Enter the name as registered with the MNO

#### Step 6: Complete Transaction
1. Enter **Name of Withdrawer**
2. Add **Narration** (optional but recommended)
3. Click **"Process Withdrawal"**

### Required Fields
- ✅ Membership Number
- ✅ Selected Account
- ✅ Withdrawal Amount
- ✅ Mobile Money Provider
- ✅ Phone Number
- ✅ Wallet Holder Name
- ✅ Reference Number
- ✅ Withdrawal Date
- ✅ Withdrawal Time
- ✅ Withdrawer Name
- ✅ Narration

### System Behavior
- Performs TIPS lookup to validate wallet details
- Processes TIPS B2W transfer
- Posts transaction to general ledger
- Provides TIPS transaction reference
- Updates account balances in real-time

### Important Notes
- Phone number must be registered with the selected MNO
- Wallet holder name must match MNO records
- Transfer is typically instant
- Maximum transfer limits may apply based on MNO policies

---

## 4. TIPS Transfer to Other Bank

### Description
Transfer funds from the member's savings account to their account at another bank using the TIPS network for interbank transfers.

### Accounts Involved
- **Debit Account**: Member's savings account
- **Intermediate Account**: SACCO's cash at NBC account
- **Credit Account**: Member's account at other bank

### Use Cases
- Large transfers to other banks
- When member has accounts at multiple banks
- Business transactions
- Secure interbank transfers

### How to Use

#### Step 1: Access Withdrawal Feature
1. Navigate to **Savings** → **Quick Actions**
2. Click **"Withdraw Savings"** button

#### Step 2: Verify Membership
1. Enter the **Membership Number**
2. Click **"Verify Membership"**
3. Ensure member details are displayed correctly

#### Step 3: Select Account and Amount
1. Choose the **savings account** from the dropdown
2. Verify the **account balance** displayed
3. Enter the **withdrawal amount**
4. Ensure sufficient balance is available

#### Step 4: Choose Withdrawal Method
1. Select **"TIPS Transfer to Other Bank"** radio button
2. The system will automatically:
   - Generate a reference number (format: `TIPS-BANK-{UNIQUE_ID}`)
   - Set current date and time

#### Step 5: Enter Bank Account Details
1. **Bank Code**: Enter the destination bank's code (e.g., CORUTZTZ for CRDB Bank)
2. **Bank Account Number**: Enter the member's account number at the destination bank
3. **Bank Account Holder Name**: Enter the name as it appears on the bank account

#### Step 6: Complete Transaction
1. Enter **Name of Withdrawer**
2. Add **Narration** (optional but recommended)
3. Click **"Process Withdrawal"**

### Required Fields
- ✅ Membership Number
- ✅ Selected Account
- ✅ Withdrawal Amount
- ✅ Bank Code
- ✅ Bank Account Number
- ✅ Bank Account Holder Name
- ✅ Reference Number
- ✅ Withdrawal Date
- ✅ Withdrawal Time
- ✅ Withdrawer Name
- ✅ Narration

### System Behavior
- Performs TIPS lookup to validate bank account details
- Processes TIPS B2B transfer
- Posts transaction to general ledger
- Provides TIPS transaction reference
- Updates account balances in real-time

### Important Notes
- Bank account must be active and valid
- Account holder name must match bank records
- Transfer may take 1-2 business days to reflect in member's bank account
- Bank codes must be accurate (contact administrator for correct codes)

---

## Common Features

### Reference Number Generation
All withdrawal methods automatically generate unique reference numbers:
- **Cash**: `CASH-{UNIQUE_ID}`
- **Internal Transfer**: `IFT-{UNIQUE_ID}`
- **TIPS MNO**: `TIPS-MNO-{UNIQUE_ID}`
- **TIPS Bank**: `TIPS-BANK-{UNIQUE_ID}`

### Balance Validation
- System checks available balance before processing
- Prevents overdraft situations
- Shows clear error messages for insufficient funds

### Transaction Logging
- All transactions are logged in the general ledger
- Detailed audit trail maintained
- Transaction references stored for reconciliation

### Error Handling
- Comprehensive validation for all fields
- Clear error messages for failed transactions
- Automatic rollback on system errors

### Security Features
- Membership verification required
- Account ownership validation
- Transaction limits and controls
- Audit trail for all operations

---

## Troubleshooting

### Common Issues and Solutions

#### 1. "Insufficient Balance" Error
**Problem**: Cannot process withdrawal due to insufficient funds
**Solution**: 
- Verify the account balance
- Check for pending transactions
- Ensure the correct account is selected

#### 2. "Account Not Found" Error
**Problem**: System cannot find the specified account
**Solution**:
- Verify the account number is correct
- Ensure the account is active
- Check for typos in account numbers

#### 3. "NBC Account Validation Failed" Error
**Problem**: Internal transfer fails due to invalid NBC account
**Solution**:
- Verify the NBC account number
- Ensure account holder name matches NBC records
- Check if the NBC account is active

#### 4. "TIPS Lookup Failed" Error
**Problem**: TIPS transfer fails during beneficiary validation
**Solution**:
- Verify phone number is registered with the MNO
- Ensure wallet holder name matches MNO records
- Check if the bank account is valid for bank transfers

#### 5. "Transaction Posting Failed" Error
**Problem**: Internal transaction posting fails
**Solution**:
- Contact system administrator
- Check system logs for detailed error
- Verify account configurations

### Contact Information

For technical support or questions about withdrawal methods:

- **System Administrator**: admin@nbcsaccos.co.tz
- **Technical Support**: support@nbcsaccos.co.tz
- **Phone**: +255 22 219 7000
- **WhatsApp**: +255 755 123 456

### System Requirements

- Modern web browser (Chrome, Firefox, Safari, Edge)
- Stable internet connection
- Valid user credentials with appropriate permissions
- Active membership and savings account

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2024-01-XX | Initial documentation for four withdrawal methods |
| 1.1 | 2024-01-XX | Added troubleshooting section and common issues |

---

**Note**: This documentation is subject to updates as the system evolves. Always refer to the latest version for the most current information. 