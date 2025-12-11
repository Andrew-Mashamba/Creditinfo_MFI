# Account Details Service Documentation

## Overview

The Account Details Service is a Laravel service that consumes an external API to retrieve account details. It provides a secure, well-logged interface for fetching account information from external banking systems with **always fresh balance data**.

## Features

- **External API Integration**: Consumes external account details API
- **RSA Signature Authentication**: Implements SHA256withRSA signature generation
- **Comprehensive Logging**: Detailed request/response logging with unique request IDs
- **Fresh Data Guarantee**: **No caching** - balance results are always fresh from external API
- **Error Handling**: Robust error handling with proper status code mapping
- **Configuration Management**: Environment-based configuration
- **Request Validation**: Input validation and sanitization
- **Connectivity Testing**: Built-in connectivity testing capabilities

## API Endpoints

### 1. Get Account Details
```
POST /api/v1/account-details
```

**Request Body:**
```json
{
    "accountNumber": "011101018916"
}
```

**Response:**
```json
{
    "statusCode": 600,
    "message": "SUCCESS",
    "body": {
        "currencyShortName": "TZS",
        "availableBalance": "740916137.85",
        "blocked": false,
        "replyCode": "0",
        "accountTitle": "VIAZI",
        "branchCode": "12",
        "branchShortName": "SAMORA",
        "customerShortName": "JUMA J MWANGU",
        "restrictedAccount": false,
        "casaAccountStatus": "ACCOUNT OPEN REGULAR",
        "casaAccountStatusCode": "8",
        "customerId": "724930"
    }
}
```

### 2. Test Connectivity
```
GET /api/v1/account-details/test
```

**Response:**
```json
{
    "success": true,
    "message": "External API is accessible",
    "data": {
        "success": true,
        "status_code": 200,
        "response_time_ms": 150.25
    }
}
```

### 3. Get Service Statistics
```
GET /api/v1/account-details/stats
```

**Response:**
```json
{
    "success": true,
    "data": {
        "base_url": "https://api.example.com",
        "channel_name": "NBC_SACCOS",
        "channel_code": "NBC001",
        "timeout": 30,
        "fresh_data_enabled": true,
        "total_requests": 150,
        "errors": 2
    }
}
```

## Configuration

### Environment Variables

Add the following variables to your `.env` file:

```env
# External API Configuration
ACCOUNT_DETAILS_BASE_URL=https://api.example.com
ACCOUNT_DETAILS_API_KEY=your-api-key-here
ACCOUNT_DETAILS_PRIVATE_KEY_PATH=/path/to/private.pem
ACCOUNT_DETAILS_CHANNEL_NAME=NBC_SACCOS
ACCOUNT_DETAILS_CHANNEL_CODE=NBC001
ACCOUNT_DETAILS_TIMEOUT=30
```

### Configuration File

The service configuration is defined in `config/services.php`:

```php
'account_details' => [
    'base_url' => env('ACCOUNT_DETAILS_BASE_URL', 'https://api.example.com'),
    'api_key' => env('ACCOUNT_DETAILS_API_KEY'),
    'private_key_path' => env('ACCOUNT_DETAILS_PRIVATE_KEY_PATH', storage_path('keys/private.pem')),
    'channel_name' => env('ACCOUNT_DETAILS_CHANNEL_NAME', 'NBC_SACCOS'),
    'channel_code' => env('ACCOUNT_DETAILS_CHANNEL_CODE', 'NBC001'),
    'timeout' => env('ACCOUNT_DETAILS_TIMEOUT', 30),
],
```

## Security

### RSA Signature Generation

The service generates RSA signatures for authentication:

1. **Private Key**: Must be stored securely and accessible to the application
2. **Signature Algorithm**: SHA256withRSA
3. **Encoding**: Base64 encoded signature
4. **Request Body**: JSON-encoded request body is signed

### Headers Required

The external API requires the following headers:

- `X-API-Key`: Static API key for authentication
- `Signature`: Base64-encoded SHA256withRSA signature
- `X-Timestamp`: ISO8601 UTC timestamp
- `X-Channel-Name`: Name of the calling system
- `X-Channel-Code`: Code of the calling system

## Logging

### Log Channel

The service uses a dedicated log channel: `account_details_external`

### Log Configuration

Add to `config/logging.php`:

```php
'account_details_external' => [
    'driver' => 'daily',
    'path' => storage_path('logs/account-details-external.log'),
    'level' => 'debug',
    'days' => 30,
    'replace_placeholders' => true,
    'permission' => 0664,
],
```

### Logged Information

- Request initiation with unique request ID
- Request validation results
- External API call details
- Response processing
- Error details with stack traces
- Performance metrics (execution time)
- **Fresh data indicator** in success logs

## Fresh Data Guarantee

### No Caching Policy

The service **does not cache** any responses to ensure:

- **Always Fresh Balances**: Every request fetches the latest balance from the external API
- **Real-time Accuracy**: Balance information is always current
- **No Stale Data**: Eliminates risk of using outdated balance information
- **Immediate Updates**: Changes in external system are immediately reflected

### Performance Considerations

- **Direct API Calls**: Every request goes directly to the external API
- **Response Time**: May be slightly slower due to no caching
- **API Rate Limits**: Consider external API rate limits
- **Network Dependency**: Requires stable network connectivity

## Error Handling

### Status Code Mapping

| Internal Code | HTTP Status | Description |
|---------------|-------------|-------------|
| 600 | 200 | Success |
| 605 | 200 | Account not found |
| 400 | 400 | Bad request |
| 401 | 401 | Unauthorized |
| 700 | 500 | Internal error |

### Error Response Format

```json
{
    "statusCode": 605,
    "message": "Account not found",
    "body": {}
}
```

## Usage Examples

### Basic Usage

```php
use App\Services\AccountDetailsService;

$service = new AccountDetailsService();
$result = $service->getAccountDetails('011101018916');
```

### Controller Usage

```php
use App\Http\Controllers\Api\V1\AccountDetailsController;

$controller = new AccountDetailsController($service);
$response = $controller->getAccountDetails($request);
```

### Testing

```php
// Test connectivity
$result = $service->testConnectivity();

// Get statistics
$stats = $service->getServiceStatistics();
```

## Testing

### Running Tests

```bash
php artisan test tests/Feature/AccountDetailsApiTest.php
```

### Test Coverage

- Successful account details retrieval
- Account not found scenarios
- Input validation
- External API error handling
- **Fresh data verification**
- Connectivity testing
- Configuration validation

## Monitoring

### Key Metrics

- Request count
- **Fresh data requests** (100% of requests)
- Error rate
- Response time
- External API availability

### Health Checks

- Connectivity test endpoint
- Configuration validation
- Private key accessibility
- External API response time

## Troubleshooting

### Common Issues

1. **Configuration Errors**
   - Check all required environment variables
   - Verify private key file exists and is readable
   - Ensure API key is valid

2. **Signature Generation Failures**
   - Verify private key format (PEM)
   - Check file permissions
   - Ensure OpenSSL extension is enabled

3. **External API Errors**
   - Check network connectivity
   - Verify API endpoint URL
   - Review external API logs

4. **Performance Issues**
   - Monitor external API response times
   - Check network latency
   - Verify timeout settings

### Debug Mode

Enable debug logging by setting log level to 'debug' in the logging configuration.

## Security Considerations

1. **Private Key Security**
   - Store private key securely
   - Use appropriate file permissions
   - Consider using environment variables for key content

2. **API Key Management**
   - Rotate API keys regularly
   - Use environment variables
   - Monitor API key usage

3. **Request Validation**
   - Validate all inputs
   - Sanitize account numbers
   - Implement rate limiting

4. **Log Security**
   - Avoid logging sensitive data
   - Implement log rotation
   - Monitor log access

## Performance Optimization

1. **Network Optimization**
   - Configure HTTP client connection pooling
   - Optimize timeout settings
   - Implement retry mechanisms

2. **Monitoring**
   - Track response times
   - Monitor external API performance
   - Set up alerts for failures

3. **Rate Limiting**
   - Implement request throttling
   - Monitor API usage
   - Respect external API limits

## Dependencies

- Laravel Framework
- Guzzle HTTP Client
- OpenSSL PHP Extension
- Monolog (for logging)

## Version History

- **v1.1.0**: Removed caching for always fresh data
- **v1.0.0**: Initial release with caching functionality
- External API integration
- RSA signature authentication
- Caching implementation (removed)
- Comprehensive logging
- Error handling
- Testing suite 