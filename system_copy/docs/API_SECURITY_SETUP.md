# API Security Setup Guide

This document provides comprehensive instructions for setting up and configuring the secure API endpoints for the Transaction Processing System.

## Overview

The API security system includes:
- **API Key Authentication**: Secure key-based authentication
- **IP Whitelisting**: Restrict access to specific IP addresses
- **Rate Limiting**: Prevent abuse with configurable limits
- **Security Headers**: Protect against common web vulnerabilities
- **Comprehensive Logging**: Monitor and audit API usage

## Environment Configuration

Add the following variables to your `.env` file:

```env
# API Security Configuration
API_ALLOWED_IPS=192.168.1.0/24,10.0.0.1,172.16.0.0/16
API_RATE_LIMITING_ENABLED=true
API_RATE_LIMIT_PER_MINUTE=60
API_RATE_LIMIT_PER_HOUR=1000
API_RATE_LIMIT_PER_DAY=10000

# API Key Settings
API_KEY_PREFIX=sk_
API_KEY_LENGTH=32
API_DEFAULT_RATE_LIMIT=1000
API_DEFAULT_EXPIRY_DAYS=365

# Security Headers
API_CORS_ENABLED=false
API_CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://api.yourdomain.com

# Logging Configuration
API_LOGGING_ENABLED=true
API_LOG_FAILED_ATTEMPTS=true
API_LOG_SUCCESSFUL_REQUESTS=false
API_LOG_SENSITIVE_DATA=false

# Transaction Processing Limits
API_MAX_TRANSACTION_AMOUNT=1000000
API_MIN_TRANSACTION_AMOUNT=1
API_REQUIRE_IDEMPOTENCY=true
API_IDEMPOTENCY_WINDOW=24

# Monitoring and Alerts
API_ALERT_ON_RATE_LIMIT=true
API_ALERT_ON_AUTH_FAILURE=true
API_ALERT_ON_SUSPICIOUS_ACTIVITY=true
API_SUSPICIOUS_ACTIVITY_THRESHOLD=10
```

## Database Setup

Run the migration to create the API keys table:

```bash
php artisan migrate
```

## Creating API Keys

### Using the Admin Interface

1. Navigate to `/api/admin/api-keys` (requires authentication)
2. Create a new API key with appropriate permissions
3. Copy the generated key securely

### Using Artisan Command

```bash
php artisan tinker
```

```php
use App\Models\ApiKey;

$apiKey = ApiKey::create([
    'client_name' => 'Your Client Name',
    'description' => 'API key for transaction processing',
    'rate_limit' => 1000,
    'allowed_ips' => ['192.168.1.100', '10.0.0.50'],
    'permissions' => ['transactions.write', 'transactions.read'],
    'expires_at' => now()->addYear(),
    'created_by' => 1, // Admin user ID
]);

echo "API Key: " . $apiKey->key;
```

## API Usage

### Authentication

Include your API key in the request headers:

```bash
curl -X POST https://yourdomain.com/api/secure/transactions/process \
  -H "X-API-Key: sk_your_api_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "service_type": "cash",
    "amount": 1000,
    "from_account": "1234567890",
    "to_account": "0987654321",
    "member_id": 1,
    "metadata": {
      "description": "Test transaction"
    }
  }'
```

### Alternative Authentication Methods

You can also use the `Authorization` header:

```bash
curl -X POST https://yourdomain.com/api/secure/transactions/process \
  -H "Authorization: Bearer sk_your_api_key_here" \
  -H "Content-Type: application/json" \
  -d '{...}'
```

## IP Whitelisting

### Configuration

Set allowed IPs in your `.env` file:

```env
# Single IPs
API_ALLOWED_IPS=192.168.1.100,10.0.0.50

# CIDR ranges
API_ALLOWED_IPS=192.168.1.0/24,10.0.0.0/16

# Mixed configuration
API_ALLOWED_IPS=192.168.1.100,192.168.1.0/24,10.0.0.50
```

### Per-Key IP Restrictions

You can also set IP restrictions per API key:

```php
$apiKey = ApiKey::create([
    'client_name' => 'Restricted Client',
    'allowed_ips' => ['192.168.1.100', '10.0.0.50'],
    // ... other fields
]);
```

## Rate Limiting

### Global Rate Limits

Configured in `.env`:

```env
API_RATE_LIMIT_PER_MINUTE=60
API_RATE_LIMIT_PER_HOUR=1000
API_RATE_LIMIT_PER_DAY=10000
```

### Per-Key Rate Limits

Set when creating API keys:

```php
$apiKey = ApiKey::create([
    'client_name' => 'High Volume Client',
    'rate_limit' => 5000, // 5000 requests per hour
    // ... other fields
]);
```

## Security Headers

The API automatically adds security headers to all responses:

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Strict-Transport-Security: max-age=31536000; includeSubDomains`
- `Content-Security-Policy: default-src 'self'`

## Monitoring and Logging

### Log Locations

- **Authentication failures**: `storage/logs/laravel.log`
- **Rate limit violations**: `storage/logs/laravel.log`
- **Suspicious activity**: `storage/logs/laravel.log`

### Monitoring Commands

```bash
# Check failed transactions
php artisan transactions:failed --retry

# Monitor API usage
php artisan transactions:failed --report

# Check system health
php artisan transactions:failed --health
```

## Error Responses

### Authentication Errors

```json
{
  "success": false,
  "message": "API key is required",
  "error_code": "MISSING_API_KEY"
}
```

```json
{
  "success": false,
  "message": "Invalid API key",
  "error_code": "INVALID_API_KEY"
}
```

### Rate Limiting Errors

```json
{
  "success": false,
  "message": "Rate limit exceeded",
  "error_code": "RATE_LIMIT_EXCEEDED"
}
```

### IP Whitelist Errors

```json
{
  "success": false,
  "message": "Access denied: IP not in whitelist",
  "error_code": "IP_NOT_WHITELISTED"
}
```

## Best Practices

### 1. Key Management

- Generate unique keys for each client
- Set appropriate expiration dates
- Use least-privilege permissions
- Rotate keys regularly
- Never expose keys in logs or responses

### 2. IP Security

- Use specific IPs rather than broad ranges
- Regularly review and update whitelists
- Monitor for unauthorized access attempts
- Use VPNs for secure connections

### 3. Rate Limiting

- Set appropriate limits based on client needs
- Monitor usage patterns
- Adjust limits as needed
- Implement progressive rate limiting

### 4. Monitoring

- Set up alerts for security events
- Monitor failed authentication attempts
- Track rate limit violations
- Review logs regularly

## Troubleshooting

### Common Issues

1. **"API key is required"**
   - Check that the `X-API-Key` header is included
   - Verify the key format (should start with `sk_`)

2. **"Invalid API key"**
   - Verify the key exists in the database
   - Check if the key is active
   - Ensure the key hasn't expired

3. **"IP not in whitelist"**
   - Check your client's IP address
   - Verify IP is in the allowed list
   - Check CIDR notation if using ranges

4. **"Rate limit exceeded"**
   - Check current usage vs. limits
   - Consider increasing limits if needed
   - Implement request batching

### Debug Mode

Enable debug mode in `.env` for detailed error messages:

```env
APP_DEBUG=true
```

**Note**: Disable debug mode in production for security.

## Security Checklist

- [ ] API keys are securely generated and stored
- [ ] IP whitelisting is configured
- [ ] Rate limits are appropriate for your use case
- [ ] Security headers are enabled
- [ ] Logging is configured and monitored
- [ ] Keys are rotated regularly
- [ ] Access is audited regularly
- [ ] Error messages don't expose sensitive information
- [ ] HTTPS is enforced in production
- [ ] API versioning is implemented

## Support

For security-related issues or questions:

1. Check the logs for detailed error information
2. Review the configuration settings
3. Test with a simple API call
4. Contact the development team with specific error codes and logs

## API Endpoints

### Secure Endpoints

- `POST /api/secure/transactions/process` - Process transactions
- `GET /api/secure/transactions/{reference}/status` - Get transaction status
- `GET /api/secure/transactions` - Get transaction history

### Admin Endpoints (requires web authentication)

- `GET /api/admin/api-keys` - List API keys
- `POST /api/admin/api-keys` - Create API key
- `GET /api/admin/api-keys/{id}` - Get API key details
- `PUT /api/admin/api-keys/{id}` - Update API key
- `DELETE /api/admin/api-keys/{id}` - Delete API key
- `POST /api/admin/api-keys/{id}/regenerate` - Regenerate API key
- `GET /api/admin/api-keys/{id}/stats` - Get API key statistics 