# NBC SMS Notification Engine - Quick Reference Guide

## Quick Start

### 1. Environment Setup
```env
# Required Environment Variables
NBC_SMS_BASE_URL=https://sms-engine.tz.af.absa.local
NBC_SMS_API_KEY=your_api_key_here
NBC_SMS_CHANNEL_ID=KRWT43976
NOTIFICATION_EMAIL_ENABLED=true
NOTIFICATION_SMS_ENABLED=true
```

### 2. Basic Usage
```php
// Send single notification
$notificationService = new NotificationService();
$result = $notificationService->sendMandatorySavingsNotification(
    $member, $controlNumber, $amount, $dueDate, $year, $month, $accountNumber
);

// Send bulk notifications
$result = $notificationService->sendBulkMandatorySavingsNotifications(
    $members, $controlNumbers, $amounts, $dueDate, $year, $month, $accountNumber
);
```

### 3. Console Commands
```bash
# Process failed notifications
php artisan notifications:process-failed --days=7

# Get statistics
php artisan notifications:stats --days=30
```

## API Endpoints

### NBC SMS Engine
- **Base URL**: `https://sms-engine.tz.af.absa.local`
- **Endpoint**: `/nbc-sms-engine/api/v1/direct-sms`
- **Method**: `POST`
- **Headers**: 
  - `Content-Type: application/json`
  - `X-API-Key: your_api_key`

### Request Format
```json
{
    "notificationRefNo": "UILKS89868766009",
    "recipientPhone": "255653666201",
    "sms": "Hi testing sms",
    "recipientName": "Customer Name",
    "language": "English",
    "smsType": "TRANSACTIONAL",
    "serviceName": "SACCOSS",
    "channelId": "KRWT43976"
}
```

## Configuration Options

### SMS Configuration
```php
// config/services.php
'nbc_sms' => [
    'base_url' => env('NBC_SMS_BASE_URL'),
    'api_key' => env('NBC_SMS_API_KEY'),
    'channel_id' => env('NBC_SMS_CHANNEL_ID', 'KRWT43976'),
    'rate_limit' => env('NBC_SMS_RATE_LIMIT', 100),
    'max_retries' => env('NBC_SMS_MAX_RETRIES', 3),
    'retry_delay' => env('NBC_SMS_RETRY_DELAY', 60),
],
```

### Notification Configuration
```php
// config/notifications.php
'max_retries' => env('NOTIFICATION_MAX_RETRIES', 3),
'retry_delays' => [60, 300, 900], // 1min, 5min, 15min
'email_enabled' => env('NOTIFICATION_EMAIL_ENABLED', true),
'sms_enabled' => env('NOTIFICATION_SMS_ENABLED', true),
'bulk_max_recipients' => env('NOTIFICATION_BULK_MAX_RECIPIENTS', 250),
```

## Error Handling

### Common Error Codes
- `200` - Success
- `400` - Bad Request
- `401` - Unauthorized (invalid API key)
- `429` - Rate Limited
- `500` - Server Error

### Retry Logic
- **Max Retries**: 3 attempts
- **Retry Delays**: 1min, 5min, 15min (exponential backoff)
- **Rate Limiting**: 100 requests per hour

## Monitoring

### Check Logs
```sql
-- Recent notifications
SELECT * FROM notification_logs 
WHERE created_at >= NOW() - INTERVAL 1 DAY 
ORDER BY created_at DESC;

-- Failed notifications
SELECT * FROM notification_logs 
WHERE status = 'failed' 
AND created_at >= NOW() - INTERVAL 7 DAY;
```

### Get Statistics
```php
$notificationService = new NotificationService();
$stats = $notificationService->getNotificationStats(30); // Last 30 days
```

## Templates

### Email Templates
- **Mandatory Savings**: `resources/views/emails/mandatory-savings-payment.blade.php`
- **Generic**: `resources/views/emails/generic-notification.blade.php`

### SMS Templates
```
Dear {memberName}, your mandatory savings payment for {month} {year} is ready. 
Control No: {controlNumber}, Amount: TZS {amount}, Due: {dueDate}. 
Visit any NBC branch or use mobile money. NBC SACCOS
```

## Troubleshooting

### SMS Not Sending
1. Check API key in `.env`
2. Verify network connectivity
3. Check rate limiting
4. Review logs for errors

### High Failure Rate
1. Validate phone number formats
2. Check NBC SMS Engine status
3. Review message content
4. Monitor API response codes

### Debug Commands
```bash
# Test configuration
php artisan config:show services.nbc_sms

# Test SMS service
php artisan tinker
$smsService = new App\Services\SmsService();
$result = $smsService->send('255653123456', 'Test message');
```

## Support

- **Email**: InhouseDevelopments@nbc.co.tz
- **Documentation**: NBC SMS Notification Engine API Document v2.0.0
- **System**: NBC SACCOS Notification System

---

**Version**: 1.0.0  
**Last Updated**: January 2024 