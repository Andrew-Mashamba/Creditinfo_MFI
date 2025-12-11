# Loan Processing Optimization Guide

## Overview
The SACCOS Core System includes an optimized loan processing engine designed to handle 5000+ loans efficiently through chunking, batch operations, and parallel processing.

## Performance Metrics Tracked

The system automatically tracks the following metrics during loan processing:

- **Total loans processed** - Number of loans evaluated during the daily run
- **Repayments processed** - Count of automatic repayments executed
- **Amount collected** - Total monetary value of collections
- **Schedules updated** - Number of loan schedule records modified
- **Notifications queued** - Email and SMS notifications sent to members
- **Processing duration** - Time taken to complete the entire process
- **Error count** - Number of errors encountered during processing

## Commands

### Run Daily Activities with Optimization
```bash
php artisan system:daily-activities
```
This command executes the complete daily loan processing routine including:
- Automatic loan repayments from member deposit accounts
- Arrears calculation and classification updates
- Report generation and distribution
- Member notifications

### Monitor Processing Statistics
```bash
php artisan loans:monitor
```
Displays a comprehensive dashboard showing:
- Portfolio overview (active loans, outstanding amounts, PAR %)
- Classification distribution (PERFORMING, WATCH, SUBSTANDARD, DOUBTFUL, LOSS)
- Today's collection activity
- Processing performance from the last run
- Queue status

### Live Monitoring
```bash
php artisan loans:monitor --live
```
Provides real-time monitoring with updates every 5 seconds:
- Current processing status
- Live collection statistics
- Active queue jobs
- Memory usage metrics

## Architecture Components

### 1. OptimizedDailyLoanService
Core service that handles loan processing with:
- Chunking (500 records at a time)
- Batch database operations (100 records per batch)
- Efficient SQL queries using CASE statements
- Memory optimization for large datasets

### 2. Queue Jobs
Parallel processing through Laravel queue jobs:
- **SendRepaymentEmail** - HTML email notifications to members
- **SendRepaymentSMS** - SMS notifications for repayments
- **GenerateDailyLoanReports** - Excel report generation with streaming

### 3. Monitoring System
- Real-time statistics cached for 24 hours
- Performance metrics tracking
- Queue status monitoring
- Failed job tracking

## Performance Optimizations

1. **Batch Updates**: Loan schedules are updated in batches using efficient SQL statements
2. **Chunked Processing**: Large datasets are processed in manageable chunks of 500 records
3. **Queue Distribution**: Notifications and reports are queued for asynchronous processing
4. **Memory Management**: Reports use streaming and chunking to avoid memory exhaustion
5. **Cached Statistics**: Processing statistics are cached to reduce database queries

## Troubleshooting

### Check Failed Jobs
```bash
php artisan queue:failed
```

### Retry Failed Jobs
```bash
php artisan queue:retry all
```

### Clear Processing Cache
```bash
php artisan cache:forget daily_loan_processing_stats
```

### View Queue Worker Status
```bash
php artisan queue:work --queue=default,notifications --tries=3 --timeout=60
```

### View Loan Loss Provisions
```bash
# View current provision status
php artisan provisions:view

# View with trends and details
php artisan provisions:view --trends --details
```

## Configuration

The system uses the following key configurations:
- **Chunk Size**: 500 records per chunk
- **Batch Size**: 100 records per batch insert
- **Cache Duration**: 24 hours for statistics
- **Queue Timeout**: 60 seconds for job processing
- **Retry Attempts**: 3 attempts for failed jobs

## Daily Processing Flow

1. **Update Arrears**: Calculate days and amounts in arrears for all schedules
2. **Process Repayments**: Automatically deduct from member deposit accounts (product_number='3000')
3. **Update Classifications**: Categorize loans based on arrears (PERFORMING, WATCH, etc.)
4. **Calculate Loan Loss Provisions**: Set aside reserves for potential loan losses
5. **Queue Notifications**: Send email and SMS notifications to affected members
6. **Generate Reports**: Create Excel reports with arrears and portfolio summaries
7. **Cache Statistics**: Store processing metrics for monitoring

## Loan Loss Provisions

The system automatically calculates and maintains loan loss provisions based on loan classifications:

### Provision Rates
- **PERFORMING**: 1% (General provision for healthy loans)
- **WATCH**: 5% (Special mention - early signs of weakness)
- **SUBSTANDARD**: 25% (Loans with defined weaknesses)
- **DOUBTFUL**: 50% (Full repayment questionable)
- **LOSS**: 100% (Considered uncollectible)

### Key Features
- **Daily Calculation**: Provisions are recalculated daily based on current loan status
- **Automatic GL Posting**: Provisions are posted to the general ledger automatically
- **NPL Monitoring**: Non-Performing Loans ratio tracked with alerts above 5%
- **Coverage Ratio**: Ensures adequate provisions to cover potential losses
- **Trend Analysis**: 30-day trends to monitor portfolio health

### Risk Alerts
- **NPL Alert**: Sent to management when NPL ratio exceeds 5%
- **Coverage Alert**: Warning when provision coverage falls below 100%
- **Email Notifications**: Automated alerts to credit managers and administrators

### Provision Management
- **Release**: Provisions automatically released when loans are closed
- **Write-offs**: Provisions utilized when loans are written off
- **Adjustments**: Daily adjustments posted to reflect changes in risk profile

## Report Generation

The system generates two comprehensive Excel reports daily:

### Arrears Report
- Summary sheet with portfolio overview
- PAR (Portfolio at Risk) analysis
- Detailed list of loans in arrears
- Classification-specific sheets (WATCH, SUBSTANDARD, DOUBTFUL, LOSS)

### Loan Summary Report
- Portfolio summary with key metrics
- Product performance analysis
- Collections summary by payment method
- Daily disbursement tracking

Both reports are automatically distributed to all system users via email.

## System Requirements

- **PHP**: 8.0 or higher
- **PostgreSQL**: 12 or higher
- **Laravel**: 9.x or higher
- **Memory**: Minimum 512MB for processing 5000+ loans
- **Queue Worker**: Must be running for notifications and reports

## Best Practices

1. **Run during off-peak hours** to minimize system load
2. **Monitor queue workers** to ensure notifications are sent
3. **Check failed jobs daily** and retry if necessary
4. **Review processing statistics** to identify bottlenecks
5. **Maintain adequate server resources** for large portfolios
6. **Regular database maintenance** for optimal query performance

## Support

For issues or questions regarding the loan processing system:
1. Check the Laravel logs at `storage/logs/laravel.log`
2. Review failed jobs in the queue
3. Monitor system resources during processing
4. Verify database indexes are properly maintained