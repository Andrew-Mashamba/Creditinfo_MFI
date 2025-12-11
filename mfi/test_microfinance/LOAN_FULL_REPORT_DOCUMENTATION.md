# Loan Full Report Service Documentation

## Overview
The Loan Full Report Service generates comprehensive loan reports with detailed information about all loans in the system, including customer details, payment schedules, arrears, classifications, and financial metrics.

## Features
- Generate comprehensive loan reports in Excel (XLSX) or CSV format
- Filter by branch, status, product type, classification, and date range
- Get report statistics without generating full report
- Stream large reports for better performance
- Command-line interface for automated report generation

## Service Location
- **Service**: `app/Services/LoanFullReportService.php`
- **Controller**: `app/Http/Controllers/LoanFullReportController.php`
- **Command**: `app/Console/Commands/GenerateLoanFullReport.php`

## Report Columns (55 fields)
The report includes the following columns:

### Basic Information
- PRODUCT_TYPE - Loan product type
- LOAN_NUMBER - Unique loan identifier
- CONTROL_NUMBER - Control/account number
- NIDA_NUMBER - National ID number
- PHONE_NUMBER - Customer phone number
- LOAN_ACCOUNT - Loan account number
- BANK_SAVING_ACCOUNT - Bank savings account
- CUSTOMER_ID - Customer identifier
- CUSTOMER_NAME - Full customer name

### Status & Branch
- ACCRUAL_INTEREST_STATUS - Interest accrual status
- BRANCH_CODE - Branch code
- BRANCH_BANK_NAME - Branch name

### Dates & Schedule
- TOTAL_MORATORIUM_DAYS - Moratorium days
- NEXT_DUE_DATE - Next payment due date
- OUTSTANDING_EMI - Outstanding EMI amount
- OVERDUE_EMI - Overdue EMI amount
- OPEN_DATE - Loan opening date
- LIMIT_DISBURSED_DATE - Disbursement date
- LIMIT_MATURITY_DATE - Maturity date
- LAST_PAID_DATE - Last payment date
- LAST_PAID_AMT_TZS - Last payment amount

### Loan Terms
- PAYMENT_FREQUENCY - Payment frequency (MONTHLY, SEMI ANNUAL, etc.)
- LOAN_OD_TENURE - Loan tenure in months
- MONTH_ON_BOOK - Months since disbursement
- DAYS_OVERLINE - Days in arrears

### Classification Tracking
- PAYMENT_CYCLE - Payment cycle
- CYCLE_LASTMONTH_2 - Classification 2 months ago
- CYCLE_LASTMONTH_1 - Classification last month
- DEL_CYCLE - Current delinquency cycle

### Financial Details
- FX_RATE - Foreign exchange rate
- CURRENCY - Currency (TZS)
- LIMIT_DISBURSED_TZS - Disbursed amount
- AMT_PROFIT_SHARE_TZS - Interest amount
- BALANCE_TZS - Total outstanding balance
- INTEREST_IN_SUSPENSE_TZS - Suspended interest
- PRINCIPAL_BALANCE_TZS - Principal outstanding
- CR_TURNOVER_MTD_TZS - Credits month-to-date
- DR_TURNOVER_MTD_TZS - Debits month-to-date
- INSTALMENT_AMOUNT_TZS - Installment amount
- ARREARS_EXCESS_TZS - Arrears amount

### Rates & Classification
- INTEREST_RATE - Interest rate
- PROFIT_RATE - Profit rate
- PREV_SEGMENT_CODE - Previous classification code
- ACCOUNT_SEGMENT_CODE - Current classification code
- SEGMENT_NAMING - Classification name

### Analytics
- END_DATE - Report end date
- NET_EXPOSURE_TZS - Net exposure
- NTD - New to Delinquency indicator
- STRAIGHT_ROLLER - Straight roller indicator
- BUCKET_JUMP - Bucket jump indicator
- MOVEMENT - Movement description
- FLOW - Flow category
- CURRENT_INT_RATE - Current interest rate
- FINAL_INSTALMENT_DATE - Final installment date
- CURRENT_INSTALMENT_TZS - Current installment amount

## Usage

### 1. Using Artisan Command

#### Generate Excel Report
```bash
php artisan loans:generate-full-report
```

#### Generate CSV Report
```bash
php artisan loans:generate-full-report --format=csv
```

#### Filter by Branch
```bash
php artisan loans:generate-full-report --branch=1
```

#### Filter by Status
```bash
php artisan loans:generate-full-report --status=NORMAL
```

#### Filter by Product Type
```bash
php artisan loans:generate-full-report --product=DEVELOPMENT
```

#### Filter by Classification
```bash
php artisan loans:generate-full-report --classification=PERFORMING
```

#### Filter by Date Range
```bash
php artisan loans:generate-full-report --date-from=2024-01-01 --date-to=2024-12-31
```

#### Include All Loans (Not Just Active)
```bash
php artisan loans:generate-full-report --all
```

#### Get Statistics Only
```bash
php artisan loans:generate-full-report --stats
```

#### Combined Filters Example
```bash
php artisan loans:generate-full-report \
  --format=xlsx \
  --branch=1 \
  --classification=PERFORMING \
  --date-from=2024-01-01 \
  --date-to=2024-12-31
```

### 2. Using API Endpoints

Add these routes to your `routes/api.php` or `routes/web.php`:

```php
use App\Http\Controllers\LoanFullReportController;

// Generate and download report
Route::post('reports/loans/full/generate', [LoanFullReportController::class, 'generate'])
    ->name('reports.loans.full.generate');

// Get report statistics
Route::get('reports/loans/full/statistics', [LoanFullReportController::class, 'statistics'])
    ->name('reports.loans.full.statistics');

// Stream download for large reports
Route::get('reports/loans/full/stream', [LoanFullReportController::class, 'stream'])
    ->name('reports.loans.full.stream');
```

#### API Request Examples

**Generate Excel Report**
```bash
curl -X POST http://your-domain/api/reports/loans/full/generate \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "format": "xlsx",
    "branch_id": "1",
    "classification": "PERFORMING"
  }' \
  --output loan_report.xlsx
```

**Generate CSV Report**
```bash
curl -X POST http://your-domain/api/reports/loans/full/generate \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "format": "csv",
    "date_from": "2024-01-01",
    "date_to": "2024-12-31"
  }' \
  --output loan_report.csv
```

**Get Statistics**
```bash
curl -X GET "http://your-domain/api/reports/loans/full/statistics?branch_id=1&classification=PERFORMING" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Response:
```json
{
  "success": true,
  "data": {
    "total_loans": 150,
    "total_disbursed": 500000000.00,
    "total_outstanding": 350000000.00,
    "total_principal_outstanding": 300000000.00,
    "total_arrears": 5000000.00,
    "by_product": {
      "DEVELOPMENT": {
        "count": 50,
        "total_disbursed": 200000000.00,
        "total_outstanding": 150000000.00
      },
      "ONJA": {
        "count": 100,
        "total_disbursed": 300000000.00,
        "total_outstanding": 200000000.00
      }
    },
    "by_classification": {
      "PERFORMING": {
        "count": 120,
        "total_outstanding": 300000000.00
      },
      "WATCH": {
        "count": 30,
        "total_outstanding": 50000000.00
      }
    }
  }
}
```

### 3. Using Service Directly in Code

```php
use App\Services\LoanFullReportService;

// Inject service
$reportService = app(LoanFullReportService::class);

// Generate Excel report
$result = $reportService->generateFullLoanReport([
    'branch_id' => '1',
    'classification' => 'PERFORMING',
    'date_from' => '2024-01-01',
    'date_to' => '2024-12-31'
]);

if ($result['success']) {
    echo "Report generated: " . $result['file_name'];
    echo "File path: " . $result['file_path'];
    echo "Records: " . $result['record_count'];
}

// Generate CSV report
$result = $reportService->exportAsCSV([
    'product_type' => 'DEVELOPMENT'
]);

// Get statistics
$stats = $reportService->getReportStatistics([
    'branch_id' => '1'
]);
```

## Filter Options

### Available Filters
- `branch_id` - Filter by branch ID (string)
- `loan_status` - Filter by loan status (string)
- `product_type` - Filter by product type (string)
- `classification` - Filter by classification (PERFORMING, WATCH, SUBSTANDARD, DOUBTFUL, LOSS)
- `date_from` - Filter loans from date (YYYY-MM-DD)
- `date_to` - Filter loans to date (YYYY-MM-DD)
- `include_all` - Include all loans regardless of status (boolean)

### Default Behavior
By default, the report includes only active loans (status: approved, disbursed, active). Use `include_all: true` to include all loans.

## Output Location
Reports are saved to: `storage/app/reports/`

File naming convention: `loan_full_report_YYYY_MM_DD.xlsx` or `.csv`

## Performance Considerations

### Large Datasets
For large datasets (>10,000 records), consider:
1. Using CSV format instead of Excel
2. Using the stream endpoint for real-time download
3. Applying filters to reduce dataset size
4. Running reports during off-peak hours

### Scheduled Reports
You can schedule automatic report generation using Laravel's task scheduler:

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Generate daily report at 6 AM
    $schedule->command('loans:generate-full-report --format=csv')
        ->dailyAt('06:00')
        ->timezone('Africa/Dar_es_Salaam');

    // Generate monthly report
    $schedule->command('loans:generate-full-report --format=xlsx')
        ->monthlyOn(1, '07:00')
        ->timezone('Africa/Dar_es_Salaam');
}
```

## Error Handling
The service includes comprehensive error handling and logging:
- All errors are logged to `storage/logs/laravel.log`
- API endpoints return appropriate HTTP status codes
- Command-line interface displays clear error messages

## Troubleshooting

### Report Directory Not Found
Create the reports directory:
```bash
mkdir -p storage/app/reports
chmod 755 storage/app/reports
```

### Permission Issues
Ensure proper permissions:
```bash
chown -R apache:apache storage/app/reports
chmod -R 755 storage/app/reports
```

### Memory Limit Exceeded
For very large reports, increase PHP memory limit:
```bash
php -d memory_limit=512M artisan loans:generate-full-report
```

### Database Connection Issues
Verify database credentials in `.env` file

## Customization

### Adding Custom Fields
To add custom fields to the report:

1. Update the query in `LoanFullReportService::getLoanData()`
2. Add the column to `setReportHeaders()` method
3. Add data population in `populateReportData()` method
4. Update documentation

### Changing Date Formats
Date formats are controlled by Carbon formatting in the `populateReportData()` method:
- `n/j/y` - M/D/YY (9/5/24)
- `n/j/Y` - M/D/YYYY (9/5/2024)
- `Y-m-d` - YYYY-MM-DD (2024-09-05)

### Customizing Segment Codes
Segment codes are mapped in the `getSegmentCodeSQL()` method. Modify the CASE statement to change mappings.

## Support
For issues or questions, contact the development team or check the application logs in `storage/logs/laravel.log`.
