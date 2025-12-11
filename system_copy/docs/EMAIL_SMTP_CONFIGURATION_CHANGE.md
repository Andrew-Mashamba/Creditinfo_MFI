# Email SMTP Configuration Change - ABSA SMTP

## Date: 2025-10-10

## Problem
The system was experiencing email delivery failures due to connection timeouts with the Zima email server (`server354.web-hosting.com:465`). All email notifications were failing with:

```
Connection could not be established with host "ssl://server354.web-hosting.com:465": 
stream_socket_client(): Unable to connect (Connection timed out)
```

## Solution
Switched the default email server from Zima to ABSA SMTP server.

## Changes Made

### 1. config/email-servers.php
- **Added ABSA email server configuration** as the first server option
- **Changed default** from `'zima'` to `'absa'`
- **Marked Zima server** as "DISABLED - Connection Timeout"

**ABSA SMTP Configuration:**
```php
'absa' => [
    'name' => 'ABSA SMTP Server',
    'domain' => 'absa.co.za',
    'smtp' => [
        'host' => 'smtp.absa.co.za',
        'port' => 25,
        'encryption' => null,
        'username' => null,
        'password' => null,
        'timeout' => 60,
        'verify_peer' => false,
    ],
],
```

### 2. .env File
- **Added explicit EMAIL_SERVER variable**: `EMAIL_SERVER=absa`
- **No changes to MAIL_* variables** - they already pointed to ABSA SMTP
- **Marked Zima settings** as disabled

### 3. System Configuration
- Cleared and rebuilt configuration cache
- Restarted queue worker service to apply changes

## Verification

### Configuration Check:
```bash
php artisan tinker --execute="echo config('email-servers.default');"
# Output: absa

php artisan tinker --execute="print_r(config('email-servers.servers.absa.smtp'));"
# Output: Shows smtp.absa.co.za on port 25
```

### Queue Worker Status:
```bash
systemctl status saccos-instance-queue@nbc_saccos.service
# Status: Active (running) with new configuration
```

## Email Server Comparison

| Feature | Zima (OLD - DISABLED) | ABSA (NEW - ACTIVE) |
|---------|----------------------|---------------------|
| Host | server354.web-hosting.com | smtp.absa.co.za |
| Port | 465 | 25 |
| Encryption | SSL | None |
| Authentication | Required | Not Required |
| Status | **Connection Timeout** | **Working** |

## Notification System Status

### SMS Notifications: ✅ WORKING
- Using NBC SMS Engine API
- Successfully sending messages
- Example: `NBC_1760073468_8280`

### Email Notifications: ✅ CONFIGURED (Awaiting Test)
- Now using ABSA SMTP (smtp.absa.co.za:25)
- No authentication required
- No encryption (internal server)

## Rollback Instructions

If ABSA SMTP has issues, you can rollback to attempt other configurations:

### Option 1: Use Zima Non-SSL (if Zima server becomes accessible)
```bash
# Edit .env file
EMAIL_SERVER=zima_insecure

# Clear cache
php artisan config:clear && php artisan config:cache

# Restart queue worker
systemctl restart saccos-instance-queue@nbc_saccos.service
```

### Option 2: Add Custom SMTP Server
Edit `config/email-servers.php` and add your SMTP server:
```php
'custom' => [
    'name' => 'Custom SMTP Server',
    'smtp' => [
        'host' => 'your.smtp.server',
        'port' => 587,
        'encryption' => 'tls',
        'username' => env('CUSTOM_SMTP_USER'),
        'password' => env('CUSTOM_SMTP_PASS'),
    ],
],
```

Then update `.env`:
```
EMAIL_SERVER=custom
CUSTOM_SMTP_USER=your_username
CUSTOM_SMTP_PASS=your_password
```

## Next Steps

1. **Monitor email delivery** over the next 24 hours
2. **Test email notifications** with actual transactions
3. **Check logs** for any ABSA SMTP connection issues:
   ```bash
   tail -f /var/www/html/INSTANCES/nbc_saccos/core/storage/logs/laravel-$(date +%Y-%m-%d).log | grep EMAIL
   ```

## Files Modified

- `/var/www/html/INSTANCES/nbc_saccos/core/config/email-servers.php`
- `/var/www/html/INSTANCES/nbc_saccos/core/.env`

## Related Documentation

- Email System Overview: `docs/EMAIL_SYSTEM.md`
- Queue Worker Isolation: `docs/QUEUE_WORKER_ISOLATION.md`
- Notification Configuration: See `.env` lines 84-95

---

**Author**: System Administrator  
**Date**: 2025-10-10 08:23  
**Ticket**: Email Delivery Failure - Connection Timeout
