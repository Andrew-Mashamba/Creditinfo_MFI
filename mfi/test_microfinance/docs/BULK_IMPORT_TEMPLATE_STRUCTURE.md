# Bulk Import Excel Template Structure

## Template Overview

The bulk import template is an Excel file with 26 columns (A-Z) that contains all the necessary fields for member registration. The template includes headers, sample data, and validation rules.

## Column Structure

### Column A: Membership Type*
- **Required**: Yes
- **Values**: Individual, Group, Business
- **Description**: Determines which additional fields are required
- **Sample**: "Individual"

### Column B: Branch ID*
- **Required**: Yes
- **Type**: Integer
- **Description**: ID of the branch where member will be registered
- **Sample**: "1"

### Column C: Phone Number*
- **Required**: Yes
- **Format**: 0XXXXXXXXX (Tanzanian format)
- **Description**: Primary contact number
- **Sample**: "0755123456"

### Column D: Address*
- **Required**: Yes
- **Max Length**: 255 characters
- **Description**: Physical address
- **Sample**: "123 Main Street, Dar es Salaam"

### Column E: Nationality*
- **Required**: Yes
- **Description**: Member's nationality
- **Sample**: "Tanzanian"

### Column F: Citizenship*
- **Required**: Yes
- **Description**: Member's citizenship
- **Sample**: "Tanzanian"

### Column G: Income Available*
- **Required**: Yes
- **Type**: Numeric
- **Min**: 0
- **Description**: Monthly available income in TZS
- **Sample**: "500000"

### Column H: Income Source*
- **Required**: Yes
- **Description**: Source of income
- **Sample**: "Salary"

### Column I: Hisa Amount*
- **Required**: Yes
- **Type**: Numeric
- **Min**: 1000 TZS
- **Description**: Shares contribution amount
- **Sample**: "50000"

### Column J: Akiba Amount*
- **Required**: Yes
- **Type**: Numeric
- **Min**: 1000 TZS
- **Description**: Savings contribution amount
- **Sample**: "100000"

### Column K: Amana Amount*
- **Required**: Yes
- **Type**: Numeric
- **Min**: 1000 TZS
- **Description**: Deposits contribution amount
- **Sample**: "50000"

### Column L: Guarantor Member Number*
- **Required**: Yes
- **Type**: String
- **Description**: Existing active member number who will guarantee
- **Sample**: "00001"

### Column M: Guarantor Relationship*
- **Required**: Yes
- **Description**: Relationship with guarantor
- **Sample**: "Family"

### Column N: Email
- **Required**: No
- **Type**: Email
- **Description**: Email address
- **Sample**: "john.doe@email.com"

### Column O: First Name*
- **Required**: Yes (Individual only)
- **Description**: Member's first name
- **Sample**: "John"

### Column P: Last Name*
- **Required**: Yes (Individual only)
- **Description**: Member's last name
- **Sample**: "Doe"

### Column Q: Gender*
- **Required**: Yes (Individual only)
- **Values**: Male, Female
- **Description**: Member's gender
- **Sample**: "Male"

### Column R: Date of Birth*
- **Required**: Yes (Individual only)
- **Format**: YYYY-MM-DD
- **Description**: Birth date (must be 18+ years)
- **Sample**: "1990-01-01"

### Column S: Marital Status*
- **Required**: Yes (Individual only)
- **Values**: Single, Married, Divorced, Widowed
- **Description**: Marital status
- **Sample**: "Married"

### Column T: Next of Kin Name*
- **Required**: Yes (Individual only)
- **Description**: Emergency contact name
- **Sample**: "Jane Doe"

### Column U: Next of Kin Phone*
- **Required**: Yes (Individual only)
- **Format**: 0XXXXXXXXX
- **Description**: Emergency contact phone
- **Sample**: "0755123457"

### Column V: Business/Group Name*
- **Required**: Yes (Business/Group only)
- **Description**: Organization name
- **Sample**: "ABC Company Ltd"

### Column W: Incorporation Number*
- **Required**: Yes (Business/Group only)
- **Description**: Business registration number
- **Sample**: "123456789"

### Column X: NBC Account Number
- **Required**: No
- **Type**: Numeric
- **Description**: Existing NBC bank account number
- **Sample**: "1234567890"

### Column Y: TIN Number
- **Required**: No
- **Description**: Tax identification number
- **Sample**: "123456789"

### Column Z: Middle Name
- **Required**: No
- **Description**: Middle name (Individual only)
- **Sample**: "Michael"

## Sample Data Row

```
Individual | 1 | 0755123456 | 123 Main Street, Dar es Salaam | Tanzanian | Tanzanian | 500000 | Salary | 50000 | 100000 | 50000 | 00001 | Family | john.doe@email.com | John | Doe | Male | 1990-01-01 | Married | Jane Doe | 0755123457 | | | | | Michael
```

## Business/Group Sample Data Row

```
Business | 1 | 0755123456 | 456 Business Ave, Dar es Salaam | Tanzanian | Tanzanian | 1000000 | Business | 100000 | 200000 | 100000 | 00001 | Business Partner | info@abc.com | | | | | | | | ABC Company Ltd | 123456789 | 9876543210 | 987654321 | |
```

## Validation Rules Summary

### Required for All Members
- Membership Type (A)
- Branch ID (B)
- Phone Number (C)
- Address (D)
- Nationality (E)
- Citizenship (F)
- Income Available (G)
- Income Source (H)
- Hisa Amount (I)
- Akiba Amount (J)
- Amana Amount (K)
- Guarantor Member Number (L)
- Guarantor Relationship (M)

### Required for Individual Members Only
- First Name (O)
- Last Name (P)
- Gender (Q)
- Date of Birth (R)
- Marital Status (S)
- Next of Kin Name (T)
- Next of Kin Phone (U)

### Required for Business/Group Members Only
- Business/Group Name (V)
- Incorporation Number (W)

### Optional Fields
- Email (N)
- NBC Account Number (X)
- TIN Number (Y)
- Middle Name (Z)

## Formatting Guidelines

### Date Format
- Use YYYY-MM-DD format
- Example: 1990-01-01 (January 1, 1990)

### Phone Number Format
- Must start with 0
- Followed by 9-10 digits
- Example: 0755123456

### Numeric Fields
- No currency symbols
- No commas or spaces
- Example: 500000 (not 500,000 or TZS 500,000)

### Text Fields
- No special formatting required
- Case doesn't matter (will be converted to uppercase)

## Common Errors to Avoid

1. **Phone Number Format**
   - ❌ 0755-123-456
   - ❌ +255755123456
   - ✅ 0755123456

2. **Date Format**
   - ❌ 01/01/1990
   - ❌ 1990/01/01
   - ✅ 1990-01-01

3. **Numeric Values**
   - ❌ 50,000
   - ❌ TZS 50000
   - ✅ 50000

4. **Membership Type**
   - ❌ individual
   - ❌ INDIVIDUAL
   - ✅ Individual

5. **Gender**
   - ❌ M
   - ❌ male
   - ✅ Male

## Template Download

The template can be downloaded from the bulk import interface and includes:
- All column headers
- Sample data row
- Instructions sheet
- Validation rules reference
- Error handling guide 