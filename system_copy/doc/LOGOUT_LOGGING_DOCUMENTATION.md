# Logout Event Logging Documentation

## Overview

The NBC SACCOS system now includes comprehensive logout event logging that captures detailed information about who logged out, when, why, and from where.

## Features

### 1. Dual Logging Channels
- **Main Application Log**: All logout events are logged to the daily Laravel log
- **Dedicated Logout Log**: Separate daily log file for logout events (`storage/logs/logout-YYYY-MM-DD.log`)

### 2. Comprehensive Data Capture

Each logout event captures:

#### User Information
- User ID
- User email
- User name
- Employee number (if applicable)

#### Session Information
- Session ID
- Session lifetime/max age
- Session data

#### Request Information
- IP address
- User agent (browser/device)
- HTTP referer
- Request method
- Route name

#### Logout Context
- **Logout Reason**: Why the logout occurred
- **Logout Source**: Where the logout was initiated from
- **Initiated By**: User or system
- **Timestamp**: Precise date/time of logout

## Logout Routes

The system provides two logout routes:

1. **`POST /logout`** - Standard logout route (for Jetstream/Fortify compatibility)
2. **`POST /sso-logout`** - SSO-specific logout route

Both routes are rate-limited to 10 requests per minute for security.

## Logout Reasons

You can pass additional context to the logout endpoint:

### Example: Manual Logout
```html
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <input type="hidden" name="reason" value="user_initiated">
    <input type="hidden" name="source" value="manual">
    <button type="submit">Logout</button>
</form>
```

### Example: Session Timeout
```html
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <input type="hidden" name="reason" value="session_timeout">
    <input type="hidden" name="source" value="automatic">
    <button type="submit">Logout</button>
</form>
```

### Example: Security Logout
```html
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <input type="hidden" name="reason" value="security_violation">
    <input type="hidden" name="source" value="system_forced">
    <button type="submit">Logout</button>
</form>
```

## Common Logout Reasons

| Reason | Description |
|--------|-------------|
| `user_initiated` | User clicked logout button |
| `session_timeout` | Session expired due to inactivity |
| `concurrent_session` | User logged in from another location |
| `security_violation` | Security policy violation detected |
| `admin_forced` | Administrator forced logout |
| `password_changed` | User changed password |
| `account_disabled` | Account was disabled |
| `system_maintenance` | System maintenance required |

## Common Logout Sources

| Source | Description |
|--------|-------------|
| `manual` | User clicked logout button |
| `automatic` | System-initiated logout |
| `system_forced` | Administrator or system forced logout |
| `mobile_app` | Logout from mobile application |
| `api` | Logout via API call |

## Log Format

### Sample Log Entry

```json
{
    "message": "===== USER LOGOUT EVENT =====",
    "context": {
        "user_id": 123,
        "user_email": "john.doe@nbc.co.tz",
        "user_name": "John Doe",
        "employee_number": "EMP001",
        "session_id": "abc123def456",
        "session_lifetime": 7200,
        "ip_address": "192.168.1.100",
        "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)...",
        "referer": "http://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/system",
        "logout_reason": "user_initiated",
        "logout_source": "manual",
        "logout_initiated_by": "user",
        "route_name": "logout",
        "request_method": "POST",
        "logout_timestamp": "2025-10-21 14:30:45",
        "logout_unix_timestamp": 1729518645
    }
}
```

## Viewing Logout Logs

### View Today's Logout Events
```bash
cat storage/logs/logout-$(date +%Y-%m-%d).log
```

### View Specific Date
```bash
cat storage/logs/logout-2025-10-21.log
```

### Search for Specific User
```bash
grep "john.doe@nbc.co.tz" storage/logs/logout-*.log
```

### Count Logouts by Reason
```bash
grep "logout_reason" storage/logs/logout-*.log | sort | uniq -c
```

### View Recent Logouts (Last 10)
```bash
tail -n 10 storage/logs/logout-$(date +%Y-%m-%d).log
```

## Security Monitoring

### Detect Suspicious Logout Patterns

#### Multiple Logouts from Different IPs
```bash
grep "user_email.*john.doe@nbc.co.tz" storage/logs/logout-*.log | \
  grep "ip_address" | sort | uniq
```

#### Forced Logouts
```bash
grep "logout_initiated_by.*system" storage/logs/logout-*.log
```

#### Session Timeouts
```bash
grep "session_timeout" storage/logs/logout-*.log
```

## Integration with Audit System

Logout events are also logged to the main application log, making them available through:

1. **Laravel Log Viewer** (if installed)
2. **System Logs API** at `/system/logs`
3. **Database Audit Trail** (for critical events)

## Programmatic Access

### In Controllers
```php
use Illuminate\Support\Facades\Log;

// Log custom logout reason
public function forceLogout(User $user, $reason)
{
    // Create logout request with reason
    $request = request();
    $request->merge([
        'reason' => $reason,
        'source' => 'admin_forced'
    ]);

    // Call logout
    Auth::logout();

    // Additional logging
    Log::warning('Admin forced user logout', [
        'admin_id' => auth()->id(),
        'target_user_id' => $user->id,
        'reason' => $reason
    ]);
}
```

### In Middleware
```php
public function handle($request, Closure $next)
{
    if ($this->sessionExpired($request)) {
        $request->merge([
            'reason' => 'session_timeout',
            'source' => 'automatic'
        ]);

        return redirect()->route('logout');
    }

    return $next($request);
}
```

## Log Retention

- Logout logs are stored daily
- Retention period: 90 days (configurable)
- Old logs are automatically cleaned by the system cleanup job

## Privacy & Compliance

- Logs contain personally identifiable information (PII)
- Access should be restricted to authorized personnel
- Logs are stored securely on the application server
- IP addresses and user agents are logged for security auditing

## Troubleshooting

### Route Not Found Error

If you see "Route [logout] not defined":

1. Clear route cache:
   ```bash
   php artisan route:clear
   php artisan route:cache
   ```

2. Verify route exists:
   ```bash
   php artisan route:list | grep logout
   ```

### Logs Not Being Created

1. Check storage permissions:
   ```bash
   chmod -R 775 storage/logs
   chown -R apache:apache storage/logs
   ```

2. Check Laravel logging configuration in `config/logging.php`

3. Verify disk space:
   ```bash
   df -h
   ```

## Best Practices

1. **Always provide context**: Include `reason` and `source` parameters when calling logout
2. **Monitor forced logouts**: Set up alerts for unusual patterns
3. **Review logs regularly**: Check for security anomalies
4. **Document custom reasons**: Maintain a list of application-specific logout reasons
5. **Archive old logs**: Implement log archival for long-term storage

## Related Documentation

- [Audit Trail Implementation](AUDIT_TRAIL_IMPLEMENTATION.md)
- [Security Configuration](../config/security.php)
- [Session Management](../config/session.php)
