# Bulk Member Import Feature

## Overview

The Bulk Member Import feature allows administrators to register multiple members simultaneously by uploading an Excel or CSV file. This feature handles all the registration fields and processes that are available in the individual member registration form, except for the approval package creation.

## Features

### 1. **Step-by-Step Process**
- **Step 1**: Upload file with template download
- **Step 2**: Preview and validate data
- **Step 3**: Process bulk import with progress tracking
- **Step 4**: Display results with detailed error reporting

### 2. **Comprehensive Validation**
- Real-time validation of all required fields
- Membership type-specific validation
- Data format validation (phone numbers, dates, etc.)
- Business rule validation (guarantor verification, minimum amounts)

### 3. **Complete Registration Process**
- Member number generation
- Account creation (shares, savings, deposits)
- Control number generation
- Bill creation
- Guarantor assignment
- Approval request creation
- Notification dispatch

## File Format

### Required Columns (in order):

| Column | Field Name | Required | Description | Validation |
|--------|------------|----------|-------------|------------|
| A | Membership Type* | Yes | Individual, Group, or Business | Must be one of the three values |
| B | Branch ID* | Yes | Branch identifier | Must exist in branches table |
| C | Phone Number* | Yes | Contact phone | Format: 0XXXXXXXXX |
| D | Address* | Yes | Physical address | Max 255 characters |
| E | Nationality* | Yes | Member nationality | Required |
| F | Citizenship* | Yes | Member citizenship | Required |
| G | Income Available* | Yes | Monthly income | Positive number |
| H | Income Source* | Yes | Source of income | Required |
| I | Hisa Amount* | Yes | Shares contribution | Min 1,000 TZS |
| J | Akiba Amount* | Yes | Savings contribution | Min 1,000 TZS |
| K | Amana Amount* | Yes | Deposits contribution | Min 1,000 TZS |
| L | Guarantor Member Number* | Yes | Existing member number | Must be active member |
| M | Guarantor Relationship* | Yes | Relationship description | Required |
| N | Email | No | Email address | Valid email format |
| O | First Name* | Yes (Individual) | Member first name | Required for individuals |
| P | Last Name* | Yes (Individual) | Member last name | Required for individuals |
| Q | Gender* | Yes (Individual) | Male or Female | Required for individuals |
| R | Date of Birth* | Yes (Individual) | Birth date | Format: YYYY-MM-DD, 18+ years |
| S | Marital Status* | Yes (Individual) | Marital status | Single/Married/Divorced/Widowed |
| T | Next of Kin Name* | Yes (Individual) | Emergency contact | Required for individuals |
| U | Next of Kin Phone* | Yes (Individual) | Emergency contact phone | Format: 0XXXXXXXXX |
| V | Business/Group Name* | Yes (Business/Group) | Organization name | Required for business/group |
| W | Incorporation Number* | Yes (Business/Group) | Registration number | Required for business/group |
| X | NBC Account Number | No | Existing bank account | Optional |
| Y | TIN Number | No | Tax identification | Optional |
| Z | Middle Name | No | Middle name | Optional for individuals |

### Template Download

Users can download a template file that includes:
- All required column headers
- Sample data row
- Instructions and field descriptions
- Validation rules

## Validation Rules

### General Validation
- All required fields must be filled
- Phone numbers must follow Tanzanian format (0XXXXXXXXX)
- Email addresses must be valid format
- Numeric fields must be positive numbers
- Minimum amounts for contributions (1,000 TZS each)

### Individual Member Validation
- First Name, Last Name required
- Gender must be Male or Female
- Date of Birth must be valid and member must be 18+
- Marital Status must be valid option
- Next of Kin information required

### Business/Group Validation
- Business/Group Name required
- Incorporation Number required

### Guarantor Validation
- Guarantor member number must exist
- Guarantor must have ACTIVE status
- Relationship description required

## Processing Flow

### 1. **File Upload & Preview**
```php
// Read file and show first 10 rows
$import = new BulkMembersImport();
$previewData = Excel::toArray($import, $uploadFile)[0];
```

### 2. **Validation**
```php
// Validate each row
foreach ($previewData as $row) {
    $errors = $this->validateRow($row, $rowNumber);
    if (!empty($errors)) {
        $this->validationErrors[$rowNumber] = $errors;
    }
}
```

### 3. **Member Registration**
```php
// Generate member number
$memberNumberGenerator = new MemberNumberGeneratorService();
$clientNumber = $memberNumberGenerator->generate();

// Create client record
$client = ClientsModel::create($clientData);

// Create accounts
$sharesAccount = $accountService->createAccount([...]);
$savingsAccount = $accountService->createAccount([...]);
$depositsAccount = $accountService->createAccount([...]);

// Generate control numbers and bills
foreach (['REG', 'SHC'] as $serviceCode) {
    $controlNumber = $billingService->generateControlNumber(...);
    $bill = $billingService->createBill(...);
}
```

### 4. **Post-Processing**
- Create guarantor relationship
- Create approval request
- Dispatch notifications
- Log all activities

## Error Handling

### Validation Errors
- Displayed in tabular format
- Row-by-row error listing
- Specific error messages for each field
- Prevents processing if errors exist

### Processing Errors
- Individual row processing with try-catch
- Database transaction rollback on errors
- Detailed error logging
- Continues processing other rows

### Results Display
- Success/Error count summary
- Detailed results table
- Member numbers for successful registrations
- Error messages for failed registrations

## Security Features

### File Validation
- File type validation (.xlsx, .xls, .csv)
- File size limit (10MB)
- Malicious file detection

### Data Validation
- Input sanitization
- SQL injection prevention
- XSS protection

### Access Control
- Authentication required
- Authorization checks
- Audit logging

## Performance Considerations

### Batch Processing
- Process rows individually to isolate errors
- Database transactions per member
- Progress tracking for large files

### Memory Management
- Preview limited to 10 rows
- Process file in chunks if needed
- Clean up temporary data

### Error Recovery
- Continue processing on individual failures
- Detailed error reporting
- Partial success handling

## Usage Instructions

### 1. **Access the Feature**
- Navigate to Clients Management
- Click on "Bulk Import" tab
- Follow the step-by-step process

### 2. **Prepare Your File**
- Download the template
- Fill in required fields
- Ensure data format compliance
- Verify guarantor member numbers

### 3. **Upload and Validate**
- Upload your Excel/CSV file
- Review preview data
- Fix any validation errors
- Proceed with import

### 4. **Monitor Progress**
- Watch processing progress
- Do not close browser window
- Wait for completion

### 5. **Review Results**
- Check success/error counts
- Note generated member numbers
- Address any failed registrations
- Download results if needed

## Technical Implementation

### Livewire Component
```php
class BulkImport extends Component
{
    use WithFileUploads;
    
    // Properties for file handling
    public $uploadFile;
    public $currentStep = 1;
    public $validationErrors = [];
    public $processingResults = [];
    
    // Methods for processing
    public function processFilePreview() { ... }
    public function validateRow($row, $rowNumber) { ... }
    public function processMemberRegistration($row, $rowNumber) { ... }
}
```

### Import Class
```php
class BulkMembersImport implements ToArray
{
    public function array(array $array)
    {
        return $array;
    }
}
```

### Database Operations
- Member creation with all fields
- Account creation for three mandatory accounts
- Guarantor relationship creation
- Approval request creation
- Bill creation with control numbers

## Troubleshooting

### Common Issues

1. **File Upload Errors**
   - Check file format (.xlsx, .xls, .csv)
   - Ensure file size < 10MB
   - Verify file is not corrupted

2. **Validation Errors**
   - Check required fields are filled
   - Verify phone number format
   - Ensure guarantor exists and is active
   - Check date formats (YYYY-MM-DD)

3. **Processing Errors**
   - Verify branch IDs exist
   - Check minimum contribution amounts
   - Ensure unique member numbers
   - Verify account creation permissions

### Error Messages

- **"Member not found"**: Guarantor member number doesn't exist
- **"Invalid phone number format"**: Must be 0XXXXXXXXX
- **"Amount must be at least 1,000"**: Minimum contribution required
- **"Branch not found"**: Invalid branch ID
- **"Member must be at least 18 years old"**: Date of birth validation

## Best Practices

### Data Preparation
- Use the provided template
- Validate data before upload
- Test with small files first
- Backup existing data

### File Management
- Use descriptive file names
- Keep file sizes reasonable
- Archive processed files
- Maintain audit trail

### Error Handling
- Review all validation errors
- Fix data issues before reprocessing
- Document error patterns
- Update templates as needed

## Future Enhancements

### Planned Features
- Batch size configuration
- Scheduled imports
- Email notifications for completion
- Import history tracking
- Advanced validation rules
- Custom field mapping
- Duplicate detection
- Auto-correction suggestions

### Performance Improvements
- Parallel processing
- Database optimization
- Caching strategies
- Background job processing
- Progress persistence 