# Account Details Service Environment Configuration

> ## ⚠️ SECURITY WARNING - EXAMPLE VALUES ONLY
>
> **All API keys, tokens, private keys, and credentials shown in this document are EXAMPLES ONLY.**
>
> - **NEVER** use these example values in production environments
> - **NEVER** commit real credentials to version control
> - **NEVER** share real credentials in documentation or chat
> - All private key examples use `...` to indicate redacted/placeholder content
> - Always generate unique credentials for each environment
> - Rotate credentials regularly according to your security policy
>
> See the **Security Best Practices** section at the end of this document.

## Required Environment Variables

Add the following variables to your `.env` file:

```env
# Account Details External API Configuration
ACCOUNT_DETAILS_BASE_URL=https://api.example.com
ACCOUNT_DETAILS_API_KEY=your-api-key-here
ACCOUNT_DETAILS_PRIVATE_KEY_PATH=/path/to/private.pem
ACCOUNT_DETAILS_CHANNEL_NAME=NBC_SACCOS
ACCOUNT_DETAILS_CHANNEL_CODE=NBC001
ACCOUNT_DETAILS_TIMEOUT=30
```

## Configuration Details

### ACCOUNT_DETAILS_BASE_URL
- **Type**: String (URL)
- **Required**: Yes
- **Description**: Base URL of the external account details API
- **Example**: `https://api.example.com`

### ACCOUNT_DETAILS_API_KEY
- **Type**: String
- **Required**: Yes
- **Description**: API key for authentication with the external service
- **Example**: `abc123def456ghi789`

### ACCOUNT_DETAILS_PRIVATE_KEY_PATH
- **Type**: String (File Path)
- **Required**: Yes
- **Description**: Path to the private key file for RSA signature generation
- **Example**: `/var/www/storage/keys/private.pem`
- **Default**: `storage_path('keys/private.pem')`

### ACCOUNT_DETAILS_CHANNEL_NAME
- **Type**: String
- **Required**: Yes
- **Description**: Name of the calling system or channel
- **Example**: `NBC_SACCOS`
- **Default**: `NBC_SACCOS`

### ACCOUNT_DETAILS_CHANNEL_CODE
- **Type**: String
- **Required**: Yes
- **Description**: Code of the calling system or channel
- **Example**: `NBC001`
- **Default**: `NBC001`

### ACCOUNT_DETAILS_TIMEOUT
- **Type**: Integer (seconds)
- **Required**: No
- **Description**: HTTP request timeout in seconds
- **Example**: `30`
- **Default**: `30`

## Private Key Setup

### Generate Private Key

```bash
# Generate private key
openssl genrsa -out private.pem 2048

# Set proper permissions
chmod 600 private.pem

# Move to secure location
mv private.pem /var/www/storage/keys/
```

### Private Key Format

The private key must be in PEM format:

```
[PRIVATE_KEY_PLACEHOLDER - Generate using: openssl genrsa -out private.pem 2048]
```

## Security Considerations

1. **API Key Security**
   - Store API keys securely
   - Rotate keys regularly
   - Never commit keys to version control

2. **Private Key Security**
   - Use appropriate file permissions (600)
   - Store in secure location
   - Backup securely
   - Consider using environment variables for key content

3. **Network Security**
   - Use HTTPS for all API communications
   - Implement proper firewall rules
   - Monitor network access

## Validation

After setting up the environment variables, you can validate the configuration:

```bash
# Test connectivity
curl -X GET http://your-app.com/api/v1/account-details/test

# Check configuration
php artisan tinker
>>> config('services.account_details')
```

## Troubleshooting

### Common Issues

1. **Private Key Not Found**
   ```
   Error: Missing required configuration: private_key_path
   ```
   - Verify the file path is correct
   - Check file permissions
   - Ensure the file exists

2. **API Key Invalid**
   ```
   Error: Invalid API key
   ```
   - Verify the API key is correct
   - Check with the external API provider
   - Ensure no extra spaces or characters

3. **Network Connectivity**
   ```
   Error: External API call failed
   ```
   - Check network connectivity
   - Verify the base URL is correct
   - Check firewall settings

### Debug Mode

Enable debug logging by setting:

```env
LOG_LEVEL=debug
```

This will provide detailed logs in `storage/logs/account-details-external.log`.

# Environment Variables Example

This file contains example environment variables for the NBC SACCOs system.

## NBC Payment Services

### NBC Payments (TIPS)
```env
# NBC Payments Base Configuration
NBC_PAYMENTS_BASE_URL=https://api.nbc.co.tz
NBC_PAYMENTS_API_KEY=your_api_key_here
NBC_PAYMENTS_CLIENT_ID=your_client_id_here
NBC_PAYMENTS_PRIVATE_KEY="[PATH_TO_YOUR_PRIVATE_KEY_FILE]"
NBC_PAYMENTS_CALLBACK_URL=https://your-domain.com/api/v1/nbc-payments/callback
```

### NBC Internal Fund Transfer
```env
# NBC Internal Fund Transfer Configuration
NBC_INTERNAL_FUND_TRANSFER_BASE_URL=https://api.nbc.co.tz
NBC_INTERNAL_FUND_TRANSFER_API_KEY=your_api_key_here
NBC_INTERNAL_FUND_TRANSFER_USERNAME=your_username
NBC_INTERNAL_FUND_TRANSFER_PASSWORD=your_password
NBC_INTERNAL_FUND_TRANSFER_PRIVATE_KEY="[PATH_TO_YOUR_PRIVATE_KEY_FILE]"
NBC_INTERNAL_FUND_TRANSFER_SERVICE_NAME=internal-fund-transfer
NBC_INTERNAL_FUND_TRANSFER_CHANNEL_ID=your_channel_id
NBC_INTERNAL_FUND_TRANSFER_VERIFY_SSL=true
NBC_INTERNAL_FUND_TRANSFER_TIMEOUT=30
```

## Account Details Service

### Base Configuration
```env
# Account Details Service Configuration
ACCOUNT_DETAILS_BASE_URL=https://api.example.com
ACCOUNT_DETAILS_API_KEY=your_api_key_here
ACCOUNT_DETAILS_PRIVATE_KEY_PATH=/path/to/private.pem
ACCOUNT_DETAILS_CHANNEL_NAME=NBC_SACCOS
ACCOUNT_DETAILS_CHANNEL_CODE=NBC001
ACCOUNT_DETAILS_TIMEOUT=30
```

### SSL Certificate Configuration
```env
# SSL Certificate Paths (if using custom certificates)
ACCOUNT_DETAILS_SSL_CERT_PATH=/path/to/cert.pem
ACCOUNT_DETAILS_SSL_KEY_PATH=/path/to/key.pem
ACCOUNT_DETAILS_SSL_CA_PATH=/path/to/ca.pem
```

## SMS Service

### NBC SMS Service
```env
# NBC SMS Service Configuration
NBC_SMS_BASE_URL=https://sms-engine.tz.af.absa.local
NBC_SMS_API_KEY=your_sms_api_key
NBC_SMS_CHANNEL_ID=KRWT43976
NBC_SMS_RATE_LIMIT=100
NBC_SMS_RATE_LIMIT_WINDOW=3600
NBC_SMS_MAX_RETRIES=3
NBC_SMS_RETRY_DELAY=60
```

### General SMS Service
```env
# General SMS Service Configuration
SMS_API_URL=https://sms-provider.com/api
SMS_API_KEY=your_sms_api_key
```

## Other Services

### Billing Service
```env
BILLING_SERVICE_URL=http://billing-service.test
```

### Luku Gateway
```env
# Luku Gateway Configuration
LUKU_GATEWAY_BASE_URL=https://luku-gateway.com
LUKU_GATEWAY_CHANNEL_ID=your_channel_id
LUKU_GATEWAY_CHANNEL_NAME=your_channel_name
LUKU_GATEWAY_API_TOKEN=your_api_token
LUKU_GATEWAY_STATUS_CHECK_URL=https://luku-gateway.com/status
LUKU_GATEWAY_VERIFY_SSL=true
```

### GEPG (Government Electronic Payment Gateway)
```env
# GEPG Configuration
GEPG_BASE_URL=https://gepg.gov.tz
GEPG_AUTHORIZATION=your_authorization_token
```

## Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nbc_saccos
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

## Mail Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@nbc.co.tz"
MAIL_FROM_NAME="${APP_NAME}"
```

## Application Configuration
```env
APP_NAME="NBC SACCOs"
APP_ENV=local
APP_KEY=your_app_key_here
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
```

## Cache and Session
```env
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

## Security
```env
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1,127.0.0.1:3000,::1
SESSION_DOMAIN=null
```

## Notes

1. **Private Keys**: Ensure private keys are properly formatted with newlines (`\n`) in the environment variable.
2. **SSL Verification**: Set to `false` only for development/testing environments.
3. **API Keys**: Keep these secure and never commit them to version control.
4. **Base URLs**: Use appropriate URLs for different environments (dev, staging, production).
5. **Timeouts**: Adjust timeout values based on your network conditions and requirements.

## Security Best Practices

1. Use strong, unique API keys for each service
2. Store sensitive data in environment variables, never in code
3. Use HTTPS for all production API endpoints
4. Regularly rotate API keys and credentials
5. Monitor API usage and set up alerts for unusual activity
6. Use proper SSL certificate validation in production
7. Implement rate limiting and request validation
8. Log all API interactions for audit purposes 