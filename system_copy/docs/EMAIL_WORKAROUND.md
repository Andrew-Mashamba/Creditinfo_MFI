# Email Configuration Workaround

## Issue
The external SMTP server (server354.web-hosting.com) is not accessible from this server due to firewall restrictions blocking outbound SMTP connections on ports 25, 465, and 587.

## Current Solution
The mail driver has been changed to `log` which writes all emails to the Laravel log file instead of sending them. This allows the application to continue functioning while email connectivity issues are resolved.

## Configuration Changes
In `.env` file:
```
MAIL_MAILER=log
```

## Accessing Email Content

### 1. View OTP Codes
When a user requests an OTP, you can retrieve it using:

```bash
# Show OTP for specific user
php artisan otp:show user@example.com

# Show all active OTPs
php artisan otp:show
```

### 2. View Email Logs
A helper script has been created to view email content:

```bash
/var/www/html/template/show-email-logs.sh
```

### 3. Manual Log Check
Emails are logged in:
```
/var/www/html/template/storage/logs/laravel-YYYY-MM-DD.log
```

Look for entries starting with `local.DEBUG` containing email content.

## OTP Codes Location
Failed OTP deliveries (with the actual codes) are logged in:
```
/var/www/html/template/storage/logs/otp-YYYY-MM-DD.log
```

## Testing Email Functionality
To test if emails are being logged correctly:

```bash
php artisan tinker
>>> Mail::raw('Test email content', function($m) { 
>>>     $m->to('test@example.com')->subject('Test'); 
>>> });
```

Then check the log file or run the helper script.

## Permanent Solution Options

### Option 1: Internal Mail Server
Install and configure a local mail server (Postfix) that can relay through NBC's internal mail infrastructure.

### Option 2: Firewall Exception
Request firewall rules to allow SMTP connections to:
- server354.web-hosting.com (ports 465, 587, 25)
- Or Office 365 SMTP endpoints if NBC uses Microsoft email

### Option 3: API-based Email Service
Use an HTTP-based email API service that doesn't require SMTP ports:
- SendGrid API
- Mailgun API
- AWS SES API

These services use HTTPS (port 443) which is already open.

### Option 4: Internal SMTP Relay
If NBC has an internal SMTP relay server, update the configuration to use it:
```env
MAIL_HOST=internal-smtp.nbc.co.tz
MAIL_PORT=25
MAIL_ENCRYPTION=null
```

## Monitoring
The queue workers and scheduler continue to run normally. Failed email jobs will be logged but won't block other operations.

## Important Notes
1. This is a temporary workaround for development/testing
2. Production environment should have proper email configuration
3. Users won't receive actual emails - admin must manually provide OTP codes
4. All email content is preserved in logs for debugging

---
**Created**: September 2, 2025  
**Status**: Temporary Workaround Active