# External Accounts Integration with Account Details Service

## Overview

The External Accounts module has been integrated with the Account Details Service to automatically fetch **fresh account balances** from external banking APIs when creating or updating bank accounts. **No caching is used** to ensure balance data is always current.

## Features

- **Fresh Balance Fetching**: When creating a new bank account, the system automatically fetches the **current balance** from the external API
- **Real-time Balance Refresh**: Existing bank accounts can have their balances refreshed from the external API
- **No Caching**: **Always fresh data** - no cached balances are used
- **Error Handling**: Graceful handling of API failures with fallback to zero balance
- **Comprehensive Logging**: All balance fetch operations are logged for audit purposes
- **Real-time Updates**: Balance updates happen in real-time during account creation/update

## Integration Points

### 1. Bank Account Creation

When creating a new bank account, the system:

1. Validates the form data
2. **Calls the external API to fetch the current account balance** (fresh data)
3. Creates the bank account record with the fetched balance
4. Sets both `opening_balance` and `current_balance` to the external balance
5. Provides user feedback about the balance fetch result

**Code Location**: `app/Http/Livewire/Accounting/ExternalAccounts.php::createBankAccount()`

### 2. Balance Refresh

For existing bank accounts, the system provides a method to refresh balances:

**Code Location**: `app/Http/Livewire/Accounting/ExternalAccounts.php::refreshAccountBalance()`

### 3. Account Updates

When updating a bank account, if the account number changes, the system automatically refreshes the balance from the external API.

**Code Location**: `app/Http/Livewire/Accounting/ExternalAccounts.php::updateBankAccount()`

## API Integration

### Account Details Service Usage

The integration uses the `AccountDetailsService` to fetch **fresh account details**:

```php
$accountDetailsService = new AccountDetailsService();
$result = $accountDetailsService->getAccountDetails($accountNumber);

if ($result['statusCode'] === 600 && isset($result['body']['availableBalance'])) {
    return (float) $result['body']['availableBalance'];
}
```

### Response Handling

The system handles various response scenarios:

- **Success (600)**: Uses the `availableBalance` from the response
- **Account Not Found (605)**: Logs warning and returns 0.0
- **API Errors**: Logs error and returns 0.0
- **Network Issues**: Logs error and returns 0.0

## Fresh Data Guarantee

### No Caching Policy

The integration ensures **always fresh balance data**:

- **Direct API Calls**: Every balance request goes directly to the external API
- **No Cache**: No cached balances are used
- **Real-time Accuracy**: Balance information is always current
- **Immediate Updates**: Changes in external system are immediately reflected

### Performance Considerations

- **Response Time**: May be slightly slower due to no caching
- **API Rate Limits**: Consider external API rate limits
- **Network Dependency**: Requires stable network connectivity
- **Fresh Data Priority**: Accuracy over speed

## Logging

### Log Entries

The integration creates detailed log entries for:

1. **Fresh Balance Fetch Attempts**:
   ```
   Account balance fetched from external API
   ```

2. **Successful Balance Updates**:
   ```
   Account balance updated successfully
   ```

3. **Failed Balance Fetches**:
   ```
   Failed to get account balance from external API
   ```

4. **Error Scenarios**:
   ```
   Error fetching account balance from external API
   ```

### Log Data

Each log entry includes:
- Account number
- Status code from external API
- Balance amount
- User ID (for audit purposes)
- Error details (when applicable)
- **Fresh data indicator**

## User Experience

### Success Scenarios

When the external API successfully returns a **fresh balance**:

```
Bank account created successfully with balance: 1,500,000.50 TZS
```

### Fallback Scenarios

When the external API fails or returns no balance:

```
Bank account created successfully. (Balance could not be retrieved from external API)
```

### Error Scenarios

When there's a system error:

```
Failed to create bank account: [Error message]
```

## Configuration Requirements

### Environment Variables

Ensure the following environment variables are configured:

```env
ACCOUNT_DETAILS_BASE_URL=https://api.example.com
ACCOUNT_DETAILS_API_KEY=your-api-key-here
ACCOUNT_DETAILS_PRIVATE_KEY_PATH=/path/to/private.pem
ACCOUNT_DETAILS_CHANNEL_NAME=NBC_SACCOS
ACCOUNT_DETAILS_CHANNEL_CODE=NBC001
ACCOUNT_DETAILS_TIMEOUT=30
```

### Private Key Setup

The integration requires a valid RSA private key for signature generation:

```bash
# Generate private key
openssl genrsa -out private.pem 2048

# Set proper permissions
chmod 600 private.pem

# Move to secure location
mv private.pem storage/keys/
```

## Testing

### Test Coverage

The integration includes comprehensive tests covering:

- Successful **fresh balance** fetching
- API failure scenarios
- Account number changes
- Error handling
- Empty account numbers

### Running Tests

```bash
php artisan test tests/Feature/ExternalAccountsBalanceTest.php
```

## Monitoring

### Key Metrics

Monitor the following metrics:

1. **Fresh Balance Fetch Success Rate**: Percentage of successful balance fetches
2. **API Response Time**: Time taken to fetch fresh balances
3. **Error Rate**: Frequency of API failures
4. **Fresh Data Requests**: 100% of requests fetch fresh data

### Health Checks

Regular health checks should include:

1. External API connectivity
2. Private key accessibility
3. Configuration validation
4. Log file monitoring
5. **Fresh data verification**

## Troubleshooting

### Common Issues

1. **Configuration Errors**
   - Verify all environment variables are set
   - Check private key file exists and is readable
   - Ensure API key is valid

2. **Network Issues**
   - Check connectivity to external API
   - Verify firewall settings
   - Monitor timeout configurations

3. **Authentication Failures**
   - Verify API key is correct
   - Check private key format and permissions
   - Ensure signature generation is working

4. **Balance Fetch Failures**
   - Check account number format
   - Verify account exists in external system
   - Review external API logs

5. **Performance Issues**
   - Monitor external API response times
   - Check network latency
   - Verify timeout settings

### Debug Mode

Enable debug logging to troubleshoot issues:

```env
LOG_LEVEL=debug
```

Check logs in:
- `storage/logs/account-details-external.log`
- `storage/logs/laravel.log`

## Security Considerations

1. **API Key Security**
   - Store API keys securely
   - Rotate keys regularly
   - Monitor API key usage

2. **Private Key Security**
   - Use appropriate file permissions (600)
   - Store in secure location
   - Backup securely

3. **Data Privacy**
   - Logs don't contain sensitive balance information
   - Account numbers are logged for audit purposes
   - Error messages are sanitized

4. **Fresh Data Security**
   - No cached data to compromise
   - Always fetches from authoritative source
   - Real-time security validation

## Performance Considerations

1. **Network Optimization**
   - Configure HTTP client connection pooling
   - Optimize timeout settings
   - Implement retry mechanisms

2. **API Rate Limiting**
   - Monitor external API usage
   - Implement request throttling if needed
   - Respect external API limits

3. **Error Recovery**
   - System continues to work even if external API is down
   - Zero balance fallback ensures business continuity
   - Detailed logging for troubleshooting

## Future Enhancements

1. **Batch Balance Updates**
   - Refresh multiple account balances simultaneously
   - Scheduled balance updates
   - Background job processing

2. **Advanced Monitoring**
   - Real-time balance fetch statistics
   - API health monitoring
   - Performance metrics visualization

3. **Retry Mechanisms**
   - Automatic retry on API failures
   - Exponential backoff
   - Circuit breaker pattern

4. **Fresh Data Verification**
   - Balance change detection
   - Anomaly detection
   - Data consistency checks 