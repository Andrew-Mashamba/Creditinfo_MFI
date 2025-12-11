# Reseller API Logging System

## Overview

The Reseller API logging system provides comprehensive logging for all API interactions, including requests, responses, errors, and domain operations. It uses a dedicated log channel and file to keep API logs separate from the main application logs.

## Features

### 🔐 Security Features
- **Sensitive Data Masking**: Automatically masks API keys, passwords, and other sensitive information
- **Secure Headers**: Masks authorization headers and API keys in request logs
- **Data Protection**: Ensures no sensitive data is exposed in logs

### 📊 Comprehensive Logging
- **Request Logging**: Logs all outgoing API requests with payload and headers
- **Response Logging**: Logs API responses with status codes and response times
- **Error Logging**: Detailed error logging with context information
- **Domain Operations**: Tracks domain-specific operations (check, register, renew)
- **Rate Limiting**: Logs API rate limit information when available

### 📈 Analytics & Monitoring
- **Request Statistics**: Track total requests, success rates, and response times
- **Operation Tracking**: Monitor most common domain operations
- **Performance Metrics**: Response time tracking and analysis
- **Error Analysis**: Detailed error tracking and categorization

## Log Structure

### Log Entry Types

#### 1. REQUEST
```json
{
    "timestamp": "2025-09-10T11:28:18.825849Z",
    "type": "REQUEST",
    "method": "POST",
    "url": "https://api.example.com/test",
    "payload": {
        "action": "checkDomain",
        "domainName": "example.tz",
        "api_key": "secr********2345"
    },
    "headers": {
        "X-API-KEY": "secr********2345",
        "Content-Type": "application/json"
    },
    "request_id": "req_68c160d2c9f3c_1757503698"
}
```

#### 2. RESPONSE
```json
{
    "timestamp": "2025-09-10T11:28:18.972424Z",
    "type": "RESPONSE",
    "request_id": "req_68c160d2c9f3c_1757503698",
    "status_code": 200,
    "response": {
        "status": "success",
        "data": {
            "available": true,
            "domain": "example.tz",
            "price": 50000
        }
    },
    "response_time_ms": 150.5
}
```

#### 3. ERROR
```json
{
    "timestamp": "2025-09-10T11:28:18.989013Z",
    "type": "ERROR",
    "request_id": "req_68c160d2c9f3c_1757503698",
    "error": "API request failed: Invalid domain name",
    "context": {
        "status_code": 400,
        "response_body": "{\"error\": \"Invalid domain\"}"
    }
}
```

#### 4. DOMAIN_OPERATION
```json
{
    "timestamp": "2025-09-10T11:28:19.001911Z",
    "type": "DOMAIN_OPERATION",
    "operation": "register",
    "domain": "example.tz",
    "data": {
        "period": 1,
        "registrant_name": "John Doe"
    },
    "result": {
        "success": true,
        "transaction_id": "TXN123456789"
    }
}
```

#### 5. RATE_LIMIT
```json
{
    "timestamp": "2025-09-10T11:28:19.006441Z",
    "type": "RATE_LIMIT",
    "request_id": "req_68c160d2c9f3c_1757503698",
    "limit": 100,
    "remaining": 95,
    "reset_time": 1757507299
}
```

## Configuration

### Log Channel Configuration

The Reseller API logging system uses a dedicated log channel configured in `config/logging.php`:

```php
'reseller_api' => [
    'driver' => 'daily',
    'path' => storage_path('logs/reseller-api/reseller-api.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => 30,
    'replace_placeholders' => true,
    'permission' => 0664,
    'formatter' => env('LOG_STDERR_FORMATTER'),
    'formatter_with' => [
        'dateFormat' => 'Y-m-d H:i:s',
    ],
],
```

### Log File Location

- **Main Log File**: `storage/logs/reseller-api.log`
- **Daily Logs**: `storage/logs/reseller-api/reseller-api-YYYY-MM-DD.log`
- **Retention**: 30 days (configurable)

## Usage

### Automatic Logging

The logging system is automatically integrated into the `ResellerApiService`. All API calls are logged without any additional configuration:

```php
// This will automatically log the request, response, and any errors
$result = $resellerApiService->checkDomainAvailability('example.tz');
```

### Manual Logging

You can also use the logger directly for custom logging:

```php
use App\Services\ResellerApiLogger;

$logger = new ResellerApiLogger();

// Log a custom domain operation
$logger->logDomainOperation('custom_operation', 'example.tz', [
    'custom_data' => 'value'
], [
    'success' => true,
    'result' => 'Operation completed'
]);
```

## Management Commands

### View Log Statistics

```bash
# Show statistics for the last 7 days
php artisan reseller-api:logs stats

# Show statistics for the last 30 days
php artisan reseller-api:logs stats --days=30
```

**Output Example:**
```
Reseller API Log Statistics (Last 7 days)

+-----------------------+-------+
| Metric                | Value |
+-----------------------+-------+
| Total Requests        | 150   |
| Successful Requests   | 145   |
| Failed Requests       | 5     |
| Success Rate          | 96.67%|
| Average Response Time | 245.5 ms |
+-----------------------+-------+

Most Common Operations:
+------------------+-------+
| Operation        | Count |
+------------------+-------+
| check_availability| 120   |
| register         | 25    |
| renew            | 5     |
+------------------+-------+
```

### View Recent Log Entries

```bash
# View the last 10 log entries
php artisan reseller-api:logs view --limit=10

# View the last 50 log entries
php artisan reseller-api:logs view --limit=50
```

**Output Example:**
```
Recent Reseller API Log Entries (Last 5 entries)

REQUEST - 2025-09-10 11:28:18
Request ID: req_68c160d2c9f3c_1757503698
Domain: example.tz

RESPONSE - 2025-09-10 11:28:18
Request ID: req_68c160d2c9f3c_1757503698
Status: 200
Response Time: 150.5 ms

ERROR - 2025-09-10 11:28:18
Request ID: req_68c160d2c9f3c_1757503698
Error: API request failed: Invalid domain name
```

### Clean Old Logs

```bash
# Clean logs older than 30 days
php artisan reseller-api:logs clean --days=30

# Clean logs older than 7 days
php artisan reseller-api:logs clean --days=7
```

### Test Logging System

```bash
# Test the logging system with sample data
php artisan reseller-api:test-logging
```

## Security Considerations

### Sensitive Data Masking

The logging system automatically masks sensitive information:

- **API Keys**: `secret_key_12345` → `secr********2345`
- **Passwords**: `mypassword123` → `mypa********123`
- **Authorization Headers**: Automatically masked
- **Custom Sensitive Fields**: Configurable in the logger

### Log File Permissions

- **File Permissions**: 0664 (readable by owner/group, writable by owner/group)
- **Directory Permissions**: 0755 (standard directory permissions)
- **Access Control**: Logs should be accessible only to authorized personnel

## Monitoring & Alerting

### Key Metrics to Monitor

1. **Success Rate**: Should be > 95%
2. **Response Time**: Should be < 500ms average
3. **Error Rate**: Should be < 5%
4. **Rate Limit Hits**: Monitor for API quota issues

### Recommended Alerts

- High error rate (> 10%)
- Slow response times (> 1000ms average)
- Rate limit exceeded
- API key authentication failures

## Troubleshooting

### Common Issues

#### 1. Logs Not Being Written
- Check file permissions on `storage/logs/reseller-api/`
- Verify the log channel configuration
- Check disk space availability

#### 2. Sensitive Data in Logs
- Ensure the masking function is working correctly
- Check for new sensitive fields that need masking
- Review log entries for any exposed data

#### 3. Performance Impact
- Monitor log file sizes
- Use log rotation for large files
- Consider log level adjustments in production

### Debug Commands

```bash
# Check log file permissions
ls -la storage/logs/reseller-api/

# View log file size
du -h storage/logs/reseller-api.log

# Test logging system
php artisan reseller-api:test-logging

# View recent entries
php artisan reseller-api:logs view --limit=5
```

## Best Practices

### 1. Log Rotation
- Use daily log rotation for large volumes
- Implement log cleanup policies
- Monitor disk usage

### 2. Security
- Regularly review logs for sensitive data
- Implement access controls for log files
- Use secure log transmission for remote monitoring

### 3. Performance
- Monitor log file sizes
- Use appropriate log levels in production
- Consider async logging for high-volume applications

### 4. Monitoring
- Set up automated log analysis
- Implement alerting for critical errors
- Regular log review and analysis

## Integration with External Tools

### Log Aggregation
The structured JSON format makes it easy to integrate with log aggregation tools:

- **ELK Stack** (Elasticsearch, Logstash, Kibana)
- **Splunk**
- **Datadog**
- **New Relic**

### Example Logstash Configuration

```ruby
filter {
  if [type] == "reseller-api" {
    json {
      source => "message"
    }
    
    date {
      match => [ "timestamp", "ISO8601" ]
    }
  }
}
```

## API Reference

### ResellerApiLogger Class

#### Methods

- `logRequest($method, $url, $payload, $headers = [])` - Log API request
- `logResponse($requestId, $statusCode, $response, $responseTime = null)` - Log API response
- `logError($requestId, $error, $context = [])` - Log API error
- `logDomainOperation($operation, $domain, $data = [], $result = null)` - Log domain operation
- `logRateLimit($requestId, $limit, $remaining, $resetTime)` - Log rate limit info
- `getLogStats($days = 7)` - Get log statistics
- `cleanOldLogs($days = 30)` - Clean old log entries

#### Properties

- `$logChannel` - Laravel log channel name
- `$logFile` - Log file name
- `$sensitiveKeys` - Array of sensitive field names to mask

## Conclusion

The Reseller API logging system provides comprehensive, secure, and efficient logging for all API interactions. It ensures data security through automatic masking, provides detailed analytics, and offers easy management through command-line tools. The system is designed to scale with your application and integrate seamlessly with external monitoring and analysis tools.
