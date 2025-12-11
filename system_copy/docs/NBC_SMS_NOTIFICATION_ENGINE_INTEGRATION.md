# NBC SMS Notification Engine Integration Documentation

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Installation & Configuration](#installation--configuration)
4. [API Integration](#api-integration)
5. [Usage Guide](#usage-guide)
6. [Email Templates](#email-templates)
7. [Console Commands](#console-commands)
8. [Monitoring & Statistics](#monitoring--statistics)
9. [Troubleshooting](#troubleshooting)
10. [API Reference](#api-reference)

## Overview

This documentation covers the integration of the NBC SMS Notification Engine API v2.0.0 with the NBC SACCOS system. The implementation provides a centralized notification service that handles both SMS and email notifications with comprehensive retry logic, logging, and monitoring capabilities.

### Key Features
- **NBC SMS Engine Integration**: Full compliance with NBC SMS Notification Engine API v2.0.0
- **Centralized Notification Service**: Unified interface for SMS and email notifications
- **Retry Logic**: Automatic retry of failed notifications with exponential backoff
- **Bulk Processing**: Support for bulk notifications (up to 250 recipients)
- **Comprehensive Logging**: Detailed logging for all notification operations
- **Statistics & Monitoring**: Real-time notification statistics and monitoring
- **Professional Templates**: NBC-branded email templates

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                    NBC SACCOS System                        │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐    ┌─────────────────┐                │
│  │ Notification    │    │ Mandatory       │                │
│  │ Service         │◄──►│ Savings Service │                │
│  └─────────────────┘    └─────────────────┘                │
│           │                                                    │
│           ▼                                                    │
│  ┌─────────────────┐    ┌─────────────────┐                │
│  │ SMS Service     │    │ Email Service   │                │
│  │ (NBC API)       │    │ (Laravel Mail)  │                │
│  └─────────────────┘    └─────────────────┘                │
│           │                                                    │
│           ▼                                                    │
│  ┌─────────────────┐    ┌─────────────────┐                │
│  │ Notification    │    │ Email Templates │                │
│  │ Log Model       │    │ (Blade)         │                │
│  └─────────────────┘    └─────────────────┘                │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
                    ┌─────────────────┐
                    │ NBC SMS Engine  │
                    │ API v2.0.0      │
                    └─────────────────┘
```

### Data Flow

1. **Notification Trigger**: MandatorySavingsService generates tracking records
2. **Central Service**: NotificationService processes the notification request
3. **Channel Selection**: Service determines SMS and/or email channels
4. **Message Generation**: Creates appropriate messages for each channel
5. **API Integration**: SMS sent via NBC SMS Engine API
6. **Logging**: All operations logged in NotificationLog model
7. **Retry Logic**: Failed notifications automatically retried

## Installation & Configuration

### 1. Environment Variables

Add the following variables to your `.env` file:

```env
# NBC SMS Configuration
NBC_SMS_BASE_URL=https://sms-engine.tz.af.absa.local
NBC_SMS_API_KEY=your_api_key_here
NBC_SMS_CHANNEL_ID=KRWT43976
NBC_SMS_RATE_LIMIT=100
NBC_SMS_MAX_RETRIES=3
NBC_SMS_RETRY_DELAY=60

# Notification Configuration
NOTIFICATION_MAX_RETRIES=3
NOTIFICATION_RETRY_DELAY_1=60
NOTIFICATION_RETRY_DELAY_2=300
NOTIFICATION_RETRY_DELAY_3=900
NOTIFICATION_EMAIL_ENABLED=true
NOTIFICATION_SMS_ENABLED=true
NOTIFICATION_LOGGING_ENABLED=true
NOTIFICATION_CLEANUP_ENABLED=true
NOTIFICATION_RETENTION_DAYS=90
NOTIFICATION_BULK_MAX_RECIPIENTS=250
NOTIFICATION_BULK_BATCH_SIZE=50
```

### 2. Configuration Files

The system uses two main configuration files:

#### `config/services.php`
```php
'nbc_sms' => [
    'base_url' => env('NBC_SMS_BASE_URL', 'https://sms-engine.tz.af.absa.local'),
    'api_key' => env('NBC_SMS_API_KEY'),
    'channel_id' => env('NBC_SMS_CHANNEL_ID', 'KRWT43976'),
    'rate_limit' => env('NBC_SMS_RATE_LIMIT', 100),
    'rate_limit_window' => env('NBC_SMS_RATE_LIMIT_WINDOW', 3600),
    'max_retries' => env('NBC_SMS_MAX_RETRIES', 3),
    'retry_delay' => env('NBC_SMS_RETRY_DELAY', 60),
],
```

#### `config/notifications.php`
```php
'max_retries' => env('NOTIFICATION_MAX_RETRIES', 3),
'retry_delays' => [
    env('NOTIFICATION_RETRY_DELAY_1', 60),   // 1 minute
    env('NOTIFICATION_RETRY_DELAY_2', 300),  // 5 minutes
    env('NOTIFICATION_RETRY_DELAY_3', 900),  // 15 minutes
],
```

### 3. Database Migration

Ensure the `notification_logs` table exists:

```bash
php artisan migrate
```

## API Integration

### NBC SMS Notification Engine API v2.0.0

The system integrates with the NBC SMS Notification Engine using the Direct SMS API.

#### API Endpoint
```
POST /nbc-sms-engine/api/v1/direct-sms
```

#### Headers
```
Content-Type: application/json
Accept: application/json
X-API-Key: your_api_key_here
```

#### Request Payload (Single Recipient)
```json
{
    "notificationRefNo": "UILKS89868766009",
    "recipientPhone": "255653666201",
    "sms": "Dear John Doe, your mandatory savings payment for January 2024 is ready. Control No: ABC123, Amount: TZS 10,000.00, Due: 15/1/2024. Visit any NBC branch or use mobile money. NBC SACCOS",
    "recipientName": "John Doe",
    "language": "English",
    "smsType": "TRANSACTIONAL",
    "serviceName": "SACCOSS",
    "channelId": "KRWT43976"
}
```
```

#### Request Payload (Bulk Recipients)
```json
{
    "notificationRefNo": "UILKS89868766010",
    "recipientPhone": "",
    "sms": "Dear Member, your mandatory savings payment is ready. Please check your account for details. NBC SACCOS",
    "recipientName": "Members",
    "language": "English",
    "smsType": "BULK",
    "serviceName": "SACCOSS",
    "channelId": "KRWT43976",
    "recipientsPhones": [
        "255653123456",
        "255754789012",
        "255765345678"
    ]
}
```

#### Success Response
```json
{
    "status": 200,
    "message": "Your request for sending Direct SMS has been successfully received, its now being queued for processing...",
    "serviceType": "Direct-SMS",
    "smsEngineUuid": "nbc-4e30a2bf-05e6-4d48-b86f-5af96b3ff75e",
    "timestamp": "2024-01-15T11:32:56.895406",
    "body": {
        "notificationRefNo": "NRF1663835571"
    }
}
```

## Usage Guide

### 1. Basic Usage

#### Send Individual Notification
```php
use App\Services\NotificationService;

$notificationService = new NotificationService();

$result = $notificationService->sendMandatorySavingsNotification(
    $member,           // Member object
    $controlNumber,    // Payment control number
    $amount,          // Payment amount
    $dueDate,         // Due date (Carbon instance)
    $year,            // Year (e.g., 2024)
    $month,           // Month (e.g., 'January')
    $accountNumber    // Account number
);

// Result structure
$result = [
    'email_sent' => true,
    'sms_sent' => true,
    'email_error' => null,
    'sms_error' => null
];
```

#### Send Bulk Notifications
```php
$result = $notificationService->sendBulkMandatorySavingsNotifications(
    $members,         // Array of member objects
    $controlNumbers,  // Array of control numbers
    $amounts,         // Array of amounts
    $dueDate,         // Due date (Carbon instance)
    $year,            // Year
    $month,           // Month
    $accountNumber    // Account number
);

// Result structure
$result = [
    'total_members' => 100,
    'email_sent' => 95,
    'sms_sent' => 98,
    'failed' => 2,
    'errors' => ['Member 123: All notifications failed', 'Member 456: SMS failed']
];
```

### 2. Direct SMS Service Usage

#### Send Single SMS
```php
use App\Services\SmsService;

$smsService = new SmsService();

$result = $smsService->send(
    '255653123456',           // Phone number
    'Your payment is ready',  // Message
    $member,                  // Recipient object (optional)
    [                         // Options (optional)
        'smsType' => 'TRANSACTIONAL',
        'serviceName' => 'SACCOSS',
        'language' => 'English'
    ]
);
```

#### Send Bulk SMS
```php
$recipients = [
    ['phone' => '255653123456', 'name' => 'John Doe'],
    ['phone' => '255754789012', 'name' => 'Jane Smith'],
    // ... up to 250 recipients
];

$result = $smsService->sendBulk(
    $recipients,
    'Bulk notification message',
    ['smsType' => 'BULK', 'serviceName' => 'SACCOSS']
);
```

### 3. Integration with MandatorySavingsService

The notification system is automatically integrated with the MandatorySavingsService:

```php
// In MandatorySavingsService::generateTrackingRecords()
$this->sendPaymentNotifications($member, $controlNumber, $amount, $dueDate, $year, $month, $accountNumber);
```

## Email Templates

### 1. Mandatory Savings Payment Template

**File**: `resources/views/emails/mandatory-savings-payment.blade.php`

**Features**:
- NBC-branded design with blue color scheme
- Prominent control number display
- Payment amount and due date
- Multiple payment method instructions
- Contact information
- Mobile-responsive layout

**Template Variables**:
- `$memberName` - Member's full name
- `$controlNumber` - Payment control number
- `$amount` - Payment amount
- `$dueDate` - Due date
- `$month` - Month name
- `$year` - Year
- `$accountNumber` - Account number
- `$paymentInstructions` - Payment method instructions

### 2. Generic Notification Template

**File**: `resources/views/emails/generic-notification.blade.php`

**Features**:
- Clean, professional design
- Generic message support
- Contact information
- Used for retry notifications

**Template Variables**:
- `$memberName` - Member's name
- `$message` - Generic message content

## Console Commands

### 1. Process Failed Notifications

**Command**: `php artisan notifications:process-failed`

**Description**: Retry failed notifications that haven't exceeded the maximum retry attempts.

**Options**:
- `--days=7` - Number of days to look back for failed notifications

**Example**:
```bash
php artisan notifications:process-failed --days=3
```

**Output**:
```
Starting to process failed notifications...
Processed 15 failed notifications
Successfully retried 12 notifications

┌─────────────────┬───────┐
│ Metric          │ Count │
├─────────────────┼───────┤
│ Total Retried   │ 15    │
│ Successfully    │ 12    │
│ Retried         │       │
│ Still Failed    │ 3     │
└─────────────────┴───────┘
```

### 2. Get Notification Statistics

**Command**: `php artisan notifications:stats`

**Description**: Display comprehensive notification statistics.

**Options**:
- `--days=30` - Number of days to get statistics for

**Example**:
```bash
php artisan notifications:stats --days=7
```

**Output**:
```
Getting notification statistics for the last 7 days...

Notification Statistics:

┌─────────────────────┬───────┐
│ Metric              │ Count │
├─────────────────────┼───────┤
│ Total Notifications │ 1250  │
│ Delivered           │ 1180  │
│ Failed              │ 45    │
│ Pending             │ 25    │
└─────────────────────┴───────┘

By Channel:

┌─────────┬───────┬──────────┬────────┐
│ Channel │ Total │ Delivered│ Failed │
├─────────┼───────┼──────────┼────────┤
│ SMS     │ 650   │ 620      │ 20     │
│ Email   │ 600   │ 560      │ 25     │
│         │       │          │        │
└─────────┴───────┴──────────┴────────┘

Success Rates:

┌─────────┬──────────────┐
│ Channel │ Success Rate │
├─────────┼──────────────┤
│ SMS     │ 95.38%       │
│ Email   │ 93.33%       │
│ Overall │ 94.40%       │
└─────────┴──────────────┘
```

## Monitoring & Statistics

### 1. Notification Logs

All notifications are logged in the `notification_logs` table with the following information:

- `process_id` - Unique process identifier
- `recipient_type` - Type of recipient (model class)
- `recipient_id` - Recipient ID
- `notification_type` - Type of notification
- `channel` - Channel used (sms/email)
- `status` - Current status (pending/sent/delivered/failed)
- `error_message` - Error message if failed
- `error_details` - Detailed error information
- `attempts` - Number of attempts made
- `sent_at` - When notification was sent
- `delivered_at` - When notification was delivered
- `failed_at` - When notification failed
- `response_data` - API response data

### 2. Statistics Methods

#### Get Notification Statistics
```php
$notificationService = new NotificationService();
$stats = $notificationService->getNotificationStats(30); // Last 30 days

// Returns comprehensive statistics including:
// - Total notifications
// - Delivered/Failed/Pending counts
// - Channel-wise breakdown
// - Daily statistics
```

#### Clean Up Old Logs
```php
$deleted = $notificationService->cleanupOldLogs(90); // Delete logs older than 90 days
```

### 3. Logging

The system provides comprehensive logging at different levels:

#### Info Level
- Notification sent successfully
- Bulk notification processing
- Statistics generation

#### Warning Level
- Retry attempts
- Rate limit warnings
- Missing phone numbers

#### Error Level
- API failures
- Notification failures
- System errors

## Troubleshooting

### 1. Common Issues

#### SMS Not Sending
**Symptoms**: SMS notifications not being delivered
**Possible Causes**:
- Invalid API key
- Incorrect base URL
- Network connectivity issues
- Rate limiting

**Solutions**:
1. Verify API key in `.env` file
2. Check network connectivity to NBC SMS Engine
3. Review rate limiting settings
4. Check logs for specific error messages

#### Email Not Sending
**Symptoms**: Email notifications not being delivered
**Possible Causes**:
- Mail configuration issues
- Invalid email addresses
- SMTP server problems

**Solutions**:
1. Verify mail configuration in `.env`
2. Check email address format
3. Test SMTP connection
4. Review mail logs

#### High Failure Rate
**Symptoms**: Many notifications failing
**Possible Causes**:
- API service issues
- Invalid phone numbers
- Message format issues

**Solutions**:
1. Check NBC SMS Engine status
2. Validate phone number formats
3. Review message content
4. Check API response codes

### 2. Debugging Commands

#### Check Configuration
```bash
php artisan config:show services.nbc_sms
php artisan config:show notifications
```

#### Test SMS Service
```php
// In tinker or test script
$smsService = new App\Services\SmsService();
$result = $smsService->send('255653123456', 'Test message');
```

#### Check Notification Logs
```sql
-- Check recent notifications
SELECT * FROM notification_logs 
WHERE created_at >= NOW() - INTERVAL 1 DAY 
ORDER BY created_at DESC;

-- Check failed notifications
SELECT * FROM notification_logs 
WHERE status = 'failed' 
AND created_at >= NOW() - INTERVAL 7 DAY;
```

### 3. Error Codes

#### NBC SMS API Error Codes
- `200` - Success
- `400` - Bad Request (client error)
- `401` - Unauthorized (invalid API key)
- `429` - Rate Limited
- `500` - Server Error

#### System Error Codes
- `SMS_RATE_LIMIT_EXCEEDED` - Rate limit exceeded
- `INVALID_PHONE_NUMBER` - Invalid phone number format
- `API_CONNECTION_FAILED` - Network connectivity issue
- `API_RESPONSE_ERROR` - API returned error response

## API Reference

### NotificationService

#### Methods

##### `sendMandatorySavingsNotification($member, $controlNumber, $amount, $dueDate, $year, $month, $accountNumber)`
Send mandatory savings payment notification to a single member.

**Parameters**:
- `$member` - Member object
- `$controlNumber` - Payment control number
- `$amount` - Payment amount
- `$dueDate` - Due date (Carbon instance)
- `$year` - Year
- `$month` - Month name
- `$accountNumber` - Account number

**Returns**: Array with notification results

##### `sendBulkMandatorySavingsNotifications($members, $controlNumbers, $amounts, $dueDate, $year, $month, $accountNumber)`
Send bulk mandatory savings notifications.

**Parameters**:
- `$members` - Array of member objects
- `$controlNumbers` - Array of control numbers
- `$amounts` - Array of amounts
- `$dueDate` - Due date (Carbon instance)
- `$year` - Year
- `$month` - Month name
- `$accountNumber` - Account number

**Returns**: Array with bulk notification results

##### `processFailedNotifications()`
Process and retry failed notifications.

**Returns**: Array with retry results

##### `getNotificationStats($days = 30)`
Get notification statistics.

**Parameters**:
- `$days` - Number of days to get statistics for

**Returns**: Array with comprehensive statistics

##### `cleanupOldLogs($days = 90)`
Clean up old notification logs.

**Parameters**:
- `$days` - Delete logs older than this many days

**Returns**: Number of deleted records

### SmsService

#### Methods

##### `send($phoneNumber, $message, $recipient = null, $options = [])`
Send SMS to single recipient.

**Parameters**:
- `$phoneNumber` - Phone number
- `$message` - SMS message
- `$recipient` - Recipient object (optional)
- `$options` - Additional options (optional)

**Returns**: Array with send results

##### `sendBulk($recipients, $message, $options = [])`
Send bulk SMS to multiple recipients.

**Parameters**:
- `$recipients` - Array of recipient objects
- `$message` - SMS message
- `$options` - Additional options (optional)

**Returns**: Array with bulk send results

### NotificationLog Model

#### Scopes

##### `pending()`
Get pending notifications.

##### `failed()`
Get failed notifications.

##### `delivered()`
Get delivered notifications.

##### `byChannel($channel)`
Get notifications by channel.

##### `byProcess($processId)`
Get notifications by process ID.

##### `recent($days = 7)`
Get recent notifications.

#### Methods

##### `markAsSent()`
Mark notification as sent.

##### `markAsDelivered()`
Mark notification as delivered.

##### `markAsFailed($error, $details = null)`
Mark notification as failed.

##### `logNotification($data)`
Create new notification log entry.

## Support

For technical support or questions regarding this integration:

- **Email**: InhouseDevelopments@nbc.co.tz
- **Documentation**: NBC SMS Notification Engine API Document v2.0.0
- **System**: NBC SACCOS Notification System

---

**Version**: 1.0.0  
**Last Updated**: January 2024  
**Compatibility**: NBC SMS Notification Engine API v2.0.0 