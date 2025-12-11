# Mandatory Savings Tracking System

## Overview

The Mandatory Savings Tracking System is a comprehensive solution designed to track, manage, and enforce mandatory monthly savings contributions from SACCO members. The system automatically tracks payments, calculates arrears, and sends notifications to ensure compliance.

## System Architecture

### Database Tables

1. **`mandatory_savings_tracking`** - Main tracking table
   - Tracks monthly payment requirements for each member
   - Records actual payments made
   - Calculates outstanding balances and arrears
   - Manages payment status (PAID, PARTIAL, UNPAID, OVERDUE)

2. **`mandatory_savings_notifications`** - Notification management
   - Stores scheduled notifications for payment reminders
   - Tracks notification delivery status
   - Supports multiple notification types (SMS, Email, System)

3. **`mandatory_savings_settings`** - System configuration
   - Configures monthly amounts, due dates, grace periods
   - Manages notification schedules and templates
   - Controls system behavior and automation

### Key Components

#### 1. Models
- **`MandatorySavingsTracking`** - Core tracking functionality
- **`MandatorySavingsNotification`** - Notification management
- **`MandatorySavingsSettings`** - System configuration

#### 2. Service Layer
- **`MandatorySavingsService`** - Business logic and operations
- Handles tracking record generation
- Processes payments from general ledger
- Manages notifications and arrears calculation

#### 3. Livewire Components
- **`MandatorySavingsManagement`** - Web interface for management
- Comprehensive dashboard with filtering and reporting
- Settings management and configuration
- Real-time tracking and monitoring

#### 4. Console Commands
- **`ProcessMandatorySavings`** - Automated processing
- Can be scheduled via cron jobs
- Handles all automated operations

## How It Works

### 1. Configuration Setup

The system automatically retrieves the mandatory savings amount from:
1. **Institution table** (ID = 1) → `mandatory_savings_account` field
2. **sub_products table** → `min_balance` where `product_account` matches the institution's mandatory savings account

### 2. Monthly Tracking Process

#### Step 1: Generate Tracking Records
```bash
php artisan mandatory-savings:process --action=generate --month=12 --year=2024
```
- Creates tracking records for all active members
- Sets required amount, due date, and initial status
- Handles existing records updates

#### Step 2: Update from Payments
```bash
php artisan mandatory-savings:process --action=update --month=12 --year=2024
```
- Scans general ledger for payments to mandatory savings account
- Updates tracking records with actual payments
- Recalculates balances and status

#### Step 3: Generate Notifications
```bash
php artisan mandatory-savings:process --action=notify --month=12 --year=2024
```
- Creates scheduled notifications for unpaid members
- Three reminder levels: First (7 days), Second (3 days), Final (1 day)
- Supports SMS and email notifications

#### Step 4: Process Overdue Records
```bash
php artisan mandatory-savings:process --action=overdue
```
- Updates status of overdue payments
- Calculates days overdue and total arrears

### 3. Notification System

#### Notification Types
1. **FIRST_REMINDER** - 7 days before due date
2. **SECOND_REMINDER** - 3 days before due date  
3. **FINAL_REMINDER** - 1 day before due date
4. **OVERDUE_NOTICE** - After grace period expires

#### Notification Methods
- **SMS** - Text message notifications
- **Email** - Email notifications
- **SYSTEM** - In-system notifications

#### Templates
- Configurable SMS and email templates
- Dynamic placeholders: `{member_name}`, `{amount}`, `{period}`, `{due_date}`, `{account_number}`, `{institution_name}`

### 4. Arrears Management

#### Arrears Calculation
- Tracks months in arrears for each member
- Calculates total outstanding amount
- Provides detailed arrears reporting

#### Arrears Features
- Automatic arrears calculation
- Historical arrears tracking
- Member-specific arrears reports
- Bulk arrears management

## Usage Guide

### Web Interface

Access the management interface at: `/mandatory-savings`

#### Features Available:
1. **Dashboard Overview**
   - Summary statistics
   - Compliance rates
   - Payment status breakdown

2. **Tracking Records Management**
   - View all tracking records
   - Filter by year, month, status
   - Search by member name or number
   - Export data

3. **Settings Configuration**
   - Monthly amount configuration
   - Due date and grace period settings
   - Notification preferences
   - Template customization

4. **Operations**
   - Generate tracking records
   - Update from payments
   - Generate notifications
   - Process overdue records

5. **Reports**
   - Arrears report
   - Compliance report
   - Payment history
   - Member details

### Command Line Operations

#### Full Processing (Recommended for automation)
```bash
php artisan mandatory-savings:process --action=all
```

#### Individual Operations
```bash
# Generate tracking records for current month
php artisan mandatory-savings:process --action=generate

# Update from payments for specific month
php artisan mandatory-savings:process --action=update --month=12 --year=2024

# Generate notifications
php artisan mandatory-savings:process --action=notify

# Process overdue records
php artisan mandatory-savings:process --action=overdue
```

### Automated Scheduling

Add to your crontab for daily processing:
```bash
# Daily at 2 AM
0 2 * * * cd /path/to/your/project && php artisan mandatory-savings:process --action=all
```

## Configuration

### Initial Setup

1. **Run Migrations**
```bash
php artisan migrate
```

2. **Seed Settings**
```bash
php artisan db:seed --class=MandatorySavingsSettingsSeeder
```

3. **Configure Institution Settings**
   - Ensure institution (ID = 1) has `mandatory_savings_account` set
   - Verify corresponding sub_product exists with correct `min_balance`

### Settings Configuration

#### Basic Settings
- **Monthly Amount**: Required monthly contribution
- **Due Day**: Day of month when payment is due (1-31)
- **Grace Period**: Days after due date before considered overdue

#### Notification Settings
- **Enable Notifications**: Master switch for all notifications
- **First Reminder**: Days before due date for first reminder
- **Second Reminder**: Days before due date for second reminder
- **Final Reminder**: Days before due date for final reminder
- **SMS Notifications**: Enable SMS delivery
- **Email Notifications**: Enable email delivery

#### Templates
- **SMS Template**: Customizable SMS message template
- **Email Template**: Customizable email message template

## Data Flow

### 1. Payment Processing
```
Member Payment → General Ledger → MandatorySavingsService → Update Tracking Records
```

### 2. Notification Flow
```
Tracking Record → Notification Generation → Scheduled Delivery → Status Update
```

### 3. Arrears Calculation
```
Unpaid Records → Arrears Calculation → Member Arrears Summary → Reporting
```

## Monitoring and Reporting

### Key Metrics
- **Compliance Rate**: Percentage of members who paid on time
- **Total Arrears**: Total outstanding amount across all members
- **Average Arrears**: Average arrears per member
- **Payment Trends**: Monthly payment patterns

### Reports Available
1. **Monthly Compliance Report**
2. **Arrears Summary Report**
3. **Member Payment History**
4. **Notification Delivery Report**

## Troubleshooting

### Common Issues

1. **No Tracking Records Generated**
   - Check if institution has mandatory_savings_account configured
   - Verify corresponding sub_product exists
   - Ensure active members exist in system

2. **Payments Not Updating**
   - Verify general ledger transactions exist
   - Check account number matching
   - Ensure transaction dates fall within target month

3. **Notifications Not Sending**
   - Check notification settings are enabled
   - Verify notification templates are configured
   - Check scheduled_at dates are in the past

4. **Arrears Calculation Issues**
   - Verify tracking records exist for all months
   - Check payment amounts are correctly recorded
   - Ensure due dates are properly set

### Debug Commands
```bash
# Check system configuration
php artisan tinker
>>> app(App\Services\MandatorySavingsService::class)->getMandatorySavingsAmount()

# Check tracking records
php artisan tinker
>>> App\Models\MandatorySavingsTracking::count()

# Check notifications
php artisan tinker
>>> App\Models\MandatorySavingsNotification::pending()->count()
```

## Security Considerations

1. **Data Access Control**
   - Implement role-based access to mandatory savings data
   - Audit logging for all operations
   - Secure API endpoints

2. **Notification Security**
   - Validate member contact information
   - Rate limiting for notifications
   - Secure delivery channels

3. **Data Integrity**
   - Transaction-based operations
   - Validation of all inputs
   - Backup and recovery procedures

## Future Enhancements

1. **Advanced Analytics**
   - Predictive payment modeling
   - Risk assessment algorithms
   - Automated intervention strategies

2. **Integration Features**
   - Mobile app notifications
   - WhatsApp integration
   - Bank API integration for payment verification

3. **Compliance Features**
   - Regulatory reporting
   - Audit trail enhancements
   - Compliance dashboard

## Support

For technical support or questions about the Mandatory Savings Tracking System, please refer to the system documentation or contact the development team. 