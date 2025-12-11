# API Security Implementation Guide

## Overview

This document provides comprehensive documentation for the secure API implementation in the Transaction Processing System (TPS). The system implements enterprise-grade security with multiple layers of protection.

## Security Features

### 1. API Key Authentication
- Secure key-based authentication with `sk_` prefix
- Support for both `X-API-Key` and `Authorization: Bearer` headers
- Rate limiting per API key
- Key expiration and status validation
- Comprehensive logging

### 2. IP Whitelisting
- Configurable IP restrictions via environment variables
- Support for single IPs and CIDR ranges
- Per-key IP restrictions
- Flexible configuration for development/production

### 3. Security Headers
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY
- X-XSS-Protection: 1; mode=block
- Strict-Transport-Security
- Content-Security-Policy

### 4. Rate Limiting
- Global system-wide rate limits
- Individual rate limits per API key
- Minute, hour, and day limits
- Configurable based on client needs

## Installation & Setup

### 1. Database Migration
```bash
php artisan migrate
```

### 2. Environment Configuration
Add to your `.env` file:

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
API_LOGGING_ENABLED=true
API_LOG_FAILED_ATTEMPTS=true
API_LOG_SUCCESSFUL_REQUESTS=false
```

## API Key Management

### Generate API Key
```bash
# Basic API key
php artisan api:generate-key "Client Name"

# With specific options
php artisan api:generate-key "High Volume Client" \
    --description "API key for high-volume transactions" \
    --rate-limit 5000 \
    --permissions "transactions.write,transactions.read" \
    --allowed-ips "192.168.1.100,10.0.0.50" \
    --expires-in 365
```

### Programmatic Management
```php
use App\Models\ApiKey;

$apiKey = ApiKey::create([
    'client_name' => 'Your Client Name',
    'description' => 'API key for transaction processing',
    'rate_limit' => 1000,
    'allowed_ips' => ['192.168.1.100', '10.0.0.50'],
    'permissions' => ['transactions.write', 'transactions.read'],
    'expires_at' => now()->addYear(),
    'created_by' => 1,
]);

echo "API Key: " . $apiKey->key;
```

## Usage Examples

### Basic Authentication
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

### PHP Client Example
```php
class TpsApiClient
{
    private $apiKey;
    private $baseUrl;
    
    public function __construct($apiKey, $baseUrl = 'https://yourdomain.com')
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }
    
    public function processTransaction($data)
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/api/secure/transactions/process');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
}

// Usage
$client = new TpsApiClient('sk_your_api_key_here');
$result = $client->processTransaction([
    'service_type' => 'cash',
    'amount' => 1000,
    'from_account' => '1234567890',
    'to_account' => '0987654321',
    'member_id' => 1
]);
```

### JavaScript Client Example
```javascript
class TpsApiClient {
    constructor(apiKey, baseUrl = 'https://yourdomain.com') {
        this.apiKey = apiKey;
        this.baseUrl = baseUrl;
    }
    
    async processTransaction(data) {
        const response = await fetch(`${this.baseUrl}/api/secure/transactions/process`, {
            method: 'POST',
            headers: {
                'X-API-Key': this.apiKey,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        return {
            status: response.status,
            data: await response.json()
        };
    }
}

// Usage
const client = new TpsApiClient('sk_your_api_key_here');
const result = await client.processTransaction({
    service_type: 'cash',
    amount: 1000,
    from_account: '1234567890',
    to_account: '0987654321',
    member_id: 1
});
```

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

## Security Best Practices

### 1. API Key Management
- Generate unique keys for each client
- Set appropriate expiration dates
- Use least-privilege permissions
- Rotate keys regularly (every 90-365 days)
- Never expose keys in logs or responses
- Use different keys for different environments

### 2. IP Security
- Use specific IPs rather than broad ranges
- Regularly review and update whitelists
- Monitor for unauthorized access attempts
- Use VPNs for secure connections

### 3. Rate Limiting
- Set appropriate limits based on client needs
- Monitor usage patterns
- Adjust limits as needed
- Set up alerts for limit violations

### 4. Monitoring
- Set up alerts for security events
- Monitor failed authentication attempts
- Track rate limit violations
- Review logs regularly

## Troubleshooting

### Common Issues

#### 1. "API key is required" (401)
**Solution**: Include `X-API-Key` or `Authorization: Bearer` header

#### 2. "Invalid API key" (401)
**Solution**: Verify key exists and is active
```bash
php artisan tinker --execute="App\Models\ApiKey::where('key', 'sk_your_key_here')->first()"
```

#### 3. "IP not in whitelist" (403)
**Solution**: Add IP to whitelist
```bash
echo "API_ALLOWED_IPS=your_ip_here" >> .env
```

#### 4. "Rate limit exceeded" (429)
**Solution**: Check usage and increase limits if needed
```bash
php artisan tinker --execute="Cache::get('rate_limit:api_key:1')"
```

## Monitoring & Logging

### Log Locations
- Authentication failures: `storage/logs/laravel.log`
- Rate limit violations: `storage/logs/laravel.log`
- Suspicious activity: `storage/logs/laravel.log`

### Monitoring Commands
```bash
# Check failed transactions
php artisan transactions:failed --retry

# Monitor API usage
php artisan transactions:failed --report

# Check system health
php artisan transactions:failed --health
```

## Security Checklist

### Implementation Checklist
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

### Operational Checklist
- [ ] Monitor failed authentication attempts
- [ ] Track rate limit violations
- [ ] Review access logs regularly
- [ ] Update IP whitelists as needed
- [ ] Rotate API keys on schedule
- [ ] Test security measures regularly
- [ ] Keep documentation updated
- [ ] Train team on security procedures
- [ ] Have incident response plan
- [ ] Regular security audits

## Files Created

### Middleware
- `app/Http/Middleware/ApiKeyAuthentication.php`
- `app/Http/Middleware/IpWhitelist.php`
- `app/Http/Middleware/SecurityHeaders.php`

### Models & Controllers
- `app/Models/ApiKey.php`
- `app/Http/Controllers/Api/ApiKeyController.php`

### Commands
- `app/Console/Commands/GenerateApiKey.php`

### Database
- `database/migrations/2024_01_15_000000_create_api_keys_table.php`

### Configuration
- `config/api.php`

### Documentation
- `docs/API_SECURITY_SETUP.md`
- `docs/API_SECURITY_GUIDE.md`

## Support

For security-related issues:
1. Check the logs for detailed error information
2. Review the configuration settings
3. Test with a simple API call
4. Contact the development team with specific error codes and logs

---

*This documentation is maintained by the development team. Last updated: January 15, 2024* 