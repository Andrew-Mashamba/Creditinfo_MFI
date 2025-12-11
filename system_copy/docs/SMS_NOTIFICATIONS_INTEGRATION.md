# SMS Notifications Integration

## Overview

The NBC SACCOS system now includes comprehensive SMS notifications for loan disbursement and other member services. This integration uses the NBC SMS Notification Engine API v2.0.0 to send professional SMS messages to both members and guarantors.

## Features

### ✅ SMS Notifications Included

1. **Loan Disbursement SMS**
   - Member notification with loan details and payment instructions
   - Guarantor notification with loan amount and monitoring instructions

2. **Member Registration SMS**
   - Welcome message with account creation confirmation
   - Payment control number and amount details

3. **Guarantor Notification SMS**
   - Regular guarantor assignment notification
   - Loan-specific guarantor responsibilities

4. **Payment Reminders SMS**
   - Payment due date reminders
   - Control number and amount information

5. **Payment Confirmations SMS**
   - Payment receipt confirmations
   - Transaction reference numbers

6. **Loan Status Updates SMS**
   - Loan approval notifications
   - Loan rejection notifications with reasons

## SMS Templates

### Loan Disbursement - Member
```
Dear [Member Name], your loan of TZS [Amount] has been disbursed successfully. 
Monthly payment: TZS [Monthly Installment]. 
Control No: [Control Number]. 
Pay via NBC Kiganjani, Wakala or branches. 
Or pay online: [Payment Link]. 
Contact: +255 22 219 7000. NBC SACCOS
```

### Loan Disbursement - Guarantor
```
Dear [Guarantor Name], you are guarantor for [Member Name]'s loan of TZS [Amount]. 
Loan has been disbursed. Please monitor payments. 
Contact: +255 22 219 7000. NBC SACCOS
```

### Member Registration
```
Dear [Member Name], welcome to NBC SACCOS! 
Your account has been created successfully. 
Control No: [Control Number], Amount: TZS [Amount]. 
Pay via NBC Kiganjani, Wakala or branches. 
Contact: +255 22 219 7000. NBC SACCOS
```

### Payment Reminder
```
Dear [Member Name], payment reminder. 
Control No: [Control Number], Amount: TZS [Amount]. 
Due: [Due Date]. 
Pay via NBC Kiganjani, Wakala or branches. NBC SACCOS
```

## Technical Implementation

### Services Used

1. **SmsService** (`app/Services/SmsService.php`)
   - Handles NBC SMS API integration
   - Manages retry logic and error handling
   - Supports both single and bulk SMS sending

2. **SmsTemplateService** (`app/Services/SmsTemplateService.php`)
   - Generates professional SMS messages
   - Handles message length validation
   - Provides template-based message generation

3. **ProcessMemberNotifications** (`app/Jobs/ProcessMemberNotifications.php`)
   - Orchestrates email and SMS notifications
   - Determines notification type (loan disbursement vs regular)
   - Handles both member and guarantor notifications

### Configuration

#### Environment Variables
```env
# NBC SMS Configuration
NBC_SMS_BASE_URL=https://sms.nbc.co.tz
NBC_SMS_API_KEY=your_api_key_here
NBC_SMS_CHANNEL_ID=101_SYSTEM
NBC_SMS_RATE_LIMIT=100
NBC_SMS_MAX_RETRIES=3
NBC_SMS_RETRY_DELAY=60
```

#### Services Configuration (`config/services.php`)
```php
'nbc_sms' => [
    'base_url' => env('NBC_SMS_BASE_URL', 'https://sms.nbc.co.tz'),
    'api_key' => env('NBC_SMS_API_KEY'),
    'channel_id' => env('NBC_SMS_CHANNEL_ID', '101_SYSTEM'),
    'rate_limit' => env('NBC_SMS_RATE_LIMIT', 100),
    'rate_limit_window' => env('NBC_SMS_RATE_LIMIT_WINDOW', 3600),
    'max_retries' => env('NBC_SMS_MAX_RETRIES', 3),
    'retry_delay' => env('NBC_SMS_RETRY_DELAY', 60),
],
```

## Usage Examples

### Sending Loan Disbursement Notifications

```php
use App\Jobs\ProcessMemberNotifications;

// Dispatch notification job
ProcessMemberNotifications::dispatch(
    $member,           // Member object
    $guarantor,        // Guarantor object (optional)
    $controlNumbers,   // Array of control numbers
    $paymentLink       // Payment link (optional)
);
```

### Direct SMS Service Usage

```php
use App\Services\SmsService;
use App\Services\SmsTemplateService;

$smsService = new SmsService();
$templateService = new SmsTemplateService();

// Generate loan disbursement SMS
$message = $templateService->generateLoanDisbursementMemberSMS(
    'John Doe',
    5000000,  // Loan amount
    500000,   // Monthly installment
    'CN123456789',  // Control number
    'https://pay.nbcsaccos.co.tz/loan/123'  // Payment link
);

// Send SMS
$result = $smsService->send(
    '255653123456',  // Phone number
    $message,        // SMS message
    $member,         // Recipient object
    [
        'smsType' => 'LOAN_DISBURSEMENT',
        'serviceName' => 'LOAN_REPAYMENT',
        'language' => 'English'
    ]
);
```

### Bulk SMS Sending

```php
$recipients = [
    ['phone' => '255653123456', 'name' => 'John Doe'],
    ['phone' => '255754789012', 'name' => 'Jane Smith'],
];

$message = "Dear members, your loan disbursements are ready. Please check your accounts.";

$result = $smsService->sendBulk(
    $recipients,
    $message,
    [
        'smsType' => 'BULK',
        'serviceName' => 'LOAN_REPAYMENT'
    ]
);
```

## Message Length Management

### Character Limits
- **Single SMS**: 160 characters
- **Multi-part SMS**: Automatically split by NBC SMS Engine
- **Recommended**: Keep messages under 160 characters for cost efficiency

### Template Validation
```php
$templateService = new SmsTemplateService();

$message = "Your long message here...";
$validation = $templateService->validateMessageLength($message);

// Result:
// [
//     'valid' => false,
//     'length' => 180,
//     'segments' => 2,
//     'max_length' => 160
// ]
```

## Error Handling

### SMS Failure Handling
- SMS failures don't stop email notifications
- Failed SMS are logged with detailed error information
- Retry logic is built into the SmsService
- Rate limiting prevents API abuse

### Logging
All SMS operations are logged with:
- Process ID for tracking
- Phone numbers (masked for privacy)
- Success/failure status
- Error messages and stack traces
- NBC API response data

## Monitoring and Analytics

### Notification Logs
```php
use App\Models\NotificationLog;

// Get SMS statistics
$smsStats = NotificationLog::byChannel('sms')
    ->where('created_at', '>=', now()->subDays(30))
    ->selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->get();
```

### Success Rates
- Track delivery success rates
- Monitor failed notifications
- Analyze SMS vs Email performance
- Identify common failure patterns

## Security and Privacy

### Phone Number Formatting
- Automatic country code formatting (255 for Tanzania)
- Validation of phone number format
- Rate limiting per phone number

### Data Protection
- Phone numbers logged in masked format
- SMS content logged for debugging only
- No sensitive data in SMS messages

## Best Practices

### Message Content
1. **Keep it concise**: Under 160 characters when possible
2. **Include essential info**: Control numbers, amounts, due dates
3. **Clear call-to-action**: Payment instructions
4. **Contact information**: Always include support contact
5. **Professional tone**: Use formal but friendly language

### Timing
1. **Business hours**: Send during business hours for better response
2. **Avoid weekends**: Unless urgent notifications
3. **Rate limiting**: Respect NBC SMS API limits
4. **Bulk sending**: Use bulk API for multiple recipients

### Testing
1. **Test templates**: Validate message length and content
2. **Test phone numbers**: Ensure proper formatting
3. **Test API integration**: Verify NBC SMS API connectivity
4. **Monitor logs**: Check for delivery confirmations

## Troubleshooting

### Common Issues

1. **Invalid Phone Numbers**
   - Check phone number format
   - Ensure country code is included
   - Validate phone number before sending

2. **API Errors**
   - Check NBC SMS API credentials
   - Verify API endpoint connectivity
   - Review rate limiting settings

3. **Message Delivery Failures**
   - Check SMS logs for error details
   - Verify recipient phone numbers
   - Review message content for issues

4. **Rate Limiting**
   - Monitor SMS sending frequency
   - Implement proper delays between sends
   - Use bulk SMS for multiple recipients

### Debug Commands
```bash
# Check SMS configuration
php artisan tinker
>>> config('services.nbc_sms')

# Test SMS service
php artisan tinker
>>> $sms = new App\Services\SmsService();
>>> $result = $sms->send('255653123456', 'Test message');
```

## Future Enhancements

### Planned Features
1. **SMS Templates Management**: Admin interface for template editing
2. **SMS Scheduling**: Send SMS at specific times
3. **SMS Analytics**: Detailed delivery and engagement metrics
4. **Multi-language Support**: Swahili and English templates
5. **SMS Opt-out**: Allow recipients to opt out of SMS
6. **SMS Verification**: OTP verification via SMS

### Integration Opportunities
1. **WhatsApp Business API**: Alternative messaging channel
2. **Push Notifications**: Mobile app notifications
3. **Voice Calls**: Automated voice notifications
4. **Email-SMS Fallback**: Automatic fallback between channels

## Support

For technical support with SMS notifications:
- **Email**: support@nbcsaccos.co.tz
- **Phone**: +255 22 219 7000
- **Documentation**: Check this file for updates
- **Logs**: Review `storage/logs/laravel.log` for detailed error information 