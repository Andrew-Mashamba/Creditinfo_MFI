# Email System Documentation

## Overview
The Email System is a comprehensive email management module integrated into the SACCOS Laravel application. It provides Gmail-like functionality for internal communication within the organization.

## Features

### Core Features
- **Compose and Send Emails**: Rich text editor with formatting options
- **Folder Management**: Inbox, Sent, Drafts, Spam, and Trash folders
- **Email Actions**: Reply, Reply All, Forward
- **Search Functionality**: Basic and advanced search with filters
- **Spam Detection**: Automatic spam filtering
- **Rate Limiting**: Prevents email flooding
- **Storage Quota**: Track and display email storage usage
- **Email Encryption**: Secure storage of email content

### Advanced Features
- **SMTP Integration**: Real email sending via configured SMTP server
- **IMAP Integration**: Sync emails from external email servers
- **Smart Compose**: AI-powered email composition suggestions
- **Email Snooze**: Temporarily hide emails until a specified time
- **Schedule Send**: Write emails now, send them later automatically
- **Undo Send**: Cancel sent emails within a 30-second window
- **Advanced Search Filters**: Search by sender, recipient, date range, attachments
- **Email Activity Logging**: Track all email actions for audit
- **Automated Backup**: Archive old emails automatically
- **Error Handling**: User-friendly error messages with logging
- **Text Formatting**: Bold, italic, underline, lists, and links

## Installation

### 1. Install Dependencies
```bash
composer require webklex/laravel-imap
composer update
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Configure Email Settings

#### For Zima Email Server
Add to your `.env` file:
```env
# SMTP Settings (for sending)
MAIL_MAILER=smtp
MAIL_HOST=server354.web-hosting.com
MAIL_PORT=465
MAIL_USERNAME=andrew.mashamba@zima.co.tz
MAIL_PASSWORD="your-password-here"
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=andrew.mashamba@zima.co.tz
MAIL_FROM_NAME="${APP_NAME}"

# Zima Email Settings (for IMAP)
ZIMA_EMAIL_USERNAME=andrew.mashamba@zima.co.tz
ZIMA_EMAIL_PASSWORD="your-password-here"
EMAIL_SERVER=zima
EMAIL_SYNC_ENABLED=true
EMAIL_SYNC_INTERVAL=5
EMAIL_SYNC_BATCH_SIZE=50
EMAIL_SYNC_DAYS=30
```

### 4. Seed Sample Data (Optional)
```bash
php artisan db:seed --class=EmailsSeeder
```

### 5. Sync Emails from IMAP Server
```bash
# Sync for all users
php artisan emails:sync --all

# Sync for specific user
php artisan emails:sync --email=andrew.mashamba@zima.co.tz

# Sync for specific user ID
php artisan emails:sync --user=1
```

## Usage

### Accessing the Email System
Navigate to the email module in your application. The interface provides:

1. **Sidebar Navigation**
   - Compose button
   - Search bar with advanced filters
   - Folder navigation
   - Storage quota display

2. **Email List View**
   - Paginated email list
   - Unread indicators
   - Quick actions (delete, mark read/unread, spam)
   - Email preview

3. **Email Detail View**
   - Full email content
   - Reply/Forward options
   - Delete and spam actions

### Composing Emails
1. Click the "Compose" button
2. Fill in recipient (To, Cc, Bcc)
3. Add subject
4. Write email body with formatting options
5. Send or save as draft

### Advanced Search
1. Click "Advanced Search" below the search bar
2. Use filters:
   - From: Sender email or name
   - To: Recipient email
   - Date range
   - Has attachments

## Configuration

### Email Server Settings
The system is configured to use Zima email server by default. Server configurations are stored in `config/email-servers.php`:

- **SMTP Server**: server354.web-hosting.com (Port 465, SSL)
- **IMAP Server**: zima.co.tz (Port 993, SSL)
- **POP3 Server**: zima.co.tz (Port 995, SSL)
- **CalDAV**: https://zima.co.tz:2080
- **CardDAV**: https://zima.co.tz:2080

### Rate Limiting
Edit `app/Services/EmailService.php`:
```php
protected $rateLimitPerMinute = 5;
protected $rateLimitPerHour = 50;
```

### Storage Quota
Edit `app/Http/Livewire/Email/Email.php`:
```php
public $storageLimit = 15728640; // 15MB in bytes
```

### Spam Keywords
Edit `app/Services/EmailService.php` in the `detectSpam()` method to add/remove spam keywords.

### Email Sync Settings
Configure in `.env`:
```env
EMAIL_SYNC_ENABLED=true      # Enable/disable automatic sync
EMAIL_SYNC_INTERVAL=5        # Sync interval in minutes
EMAIL_SYNC_BATCH_SIZE=50     # Number of emails per sync
EMAIL_SYNC_DAYS=30          # Days of emails to sync
```

## API Reference

### EmailService Methods

#### sendEmail($data)
Send an email via SMTP and save to database.
```php
$emailService->sendEmail([
    'to' => 'recipient@example.com',
    'cc' => 'cc@example.com',
    'bcc' => 'bcc@example.com',
    'subject' => 'Email Subject',
    'body' => 'Email body content'
]);
```

#### getEmailStats($userId)
Get email statistics for a user.
```php
$stats = $emailService->getEmailStats($userId);
// Returns: total_sent, total_received, unread_count, storage_used, emails_today, spam_count
```

#### detectSpam($email)
Check if an email is spam.
```php
$isSpam = $emailService->detectSpam($emailData);
```

#### backupOldEmails($daysOld = 90)
Archive emails older than specified days.
```php
$archivedCount = $emailService->backupOldEmails(90);
```

## Security

### Encryption
- Email bodies are encrypted using Laravel's encryption
- Encryption key must be set in `.env` (APP_KEY)

### CSRF Protection
- All forms include CSRF tokens
- Livewire handles CSRF automatically

### Rate Limiting
- Prevents email flooding
- Configurable per-minute and per-hour limits

### Logging
- All email activities are logged
- Separate log channel for email-related events
- Logs stored in `storage/logs/email.log`

## Testing

Run the email system tests:
```bash
php artisan test --filter EmailSystemTest
```

Test coverage includes:
- Email sending
- Rate limiting
- Spam detection
- Search functionality
- Email actions (read/unread, delete, spam)
- Draft management
- Statistics calculation
- Email archiving

## Troubleshooting

### Common Issues

1. **SMTP Connection Failed**
   - Check SMTP credentials in `.env`
   - Verify firewall allows outbound SMTP
   - For Gmail, use app-specific password

2. **Rate Limit Exceeded**
   - Wait before sending more emails
   - Adjust rate limits if needed

3. **Storage Quota Exceeded**
   - Archive old emails
   - Increase storage limit
   - Delete unnecessary emails

4. **Emails Not Sending**
   - Check Laravel mail configuration
   - Verify SMTP settings
   - Check email logs for errors

### Debug Mode
Enable debug logging in `.env`:
```env
LOG_LEVEL=debug
```

Check logs:
```bash
tail -f storage/logs/email.log
```

## Maintenance

### Regular Tasks
1. **Archive Old Emails** (Monthly)
   ```bash
   php artisan tinker
   >>> $emailService = new \App\Services\EmailService();
   >>> $emailService->backupOldEmails(90);
   ```

2. **Clear Email Logs** (Monthly)
   ```bash
   rm storage/logs/email-*.log
   ```

3. **Monitor Storage Usage**
   - Check user storage quotas
   - Notify users approaching limits

## Advanced Features Usage

### Smart Compose
Smart Compose provides AI-powered suggestions as you type:
- Automatically activated when composing emails
- Press Tab to accept suggestions
- Use arrow keys to navigate between multiple suggestions
- Based on your writing style and email history

### Email Snooze
Temporarily hide emails and have them reappear later:
1. Click the snooze button (clock icon) on any email
2. Choose from preset times or set a custom date/time
3. Email moves to the Snoozed folder
4. Automatically returns to inbox at the specified time

### Schedule Send
Send emails at the optimal time:
1. Compose your email normally
2. Click "Schedule" instead of "Send Now"
3. Choose from suggested times or pick custom date/time
4. Email will be sent automatically at the scheduled time
5. View/cancel scheduled emails in the Scheduled folder

### Undo Send
Cancel emails within 30 seconds of sending:
- After clicking send, a notification appears with countdown
- Click "Undo" to move the email back to drafts
- Automatic sending after the undo window expires

## Scheduled Tasks
The system runs these automated tasks:
```bash
# Process snoozed emails (every 5 minutes)
php artisan emails:process-snoozes

# Send scheduled emails (every minute)
php artisan emails:send-scheduled

# Process undo queue (every minute)
php artisan emails:process-undo-queue

# Sync emails from IMAP (configurable interval)
php artisan emails:sync --all

# Archive old emails (daily at 2 AM)
# Handled automatically by the scheduler
```

## Future Enhancements
- File attachments support
- Email templates system
- Email signatures management
- Contact management
- Calendar integration
- Mobile app support
- Real-time notifications
- Email threading/conversations
- Multiple email accounts
- Email tracking (read receipts)
- Follow-up reminders
- Labels and categories