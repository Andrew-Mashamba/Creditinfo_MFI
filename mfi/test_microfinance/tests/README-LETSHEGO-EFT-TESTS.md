# 🚀 Letshego EFT Service Test Suite

Comprehensive testing suite for the Letshego External Funds Transfer (EFT) Service integration.

## 📋 Test Coverage

This test suite covers all major functionalities:

- ✅ **Account Enquiry** - Verify external bank accounts
- ✅ **Outward Transfers** - Send money to other banks  
- ✅ **Transaction Status** - Check transfer status
- ✅ **Institution List** - Get supported banks
- ✅ **Transaction Reversal** - Cancel/reverse transfers
- ✅ **Comprehensive Logging** - Detailed transaction tracking
- ✅ **Error Handling** - Robust error management

## 🧪 Test Methods

### 1. PHPUnit Tests (Recommended for CI/CD)

**Location:** `tests/Feature/Services/LetshEgoEftServiceTest.php`

```bash
# Run all EFT service tests
./vendor/bin/phpunit tests/Feature/Services/LetshEgoEftServiceTest.php --verbose

# Run specific test
./vendor/bin/phpunit --filter test_can_get_institution_list tests/Feature/Services/LetshEgoEftServiceTest.php
```

**Features:**
- 10 comprehensive test methods
- HTTP mocking for safe testing
- Detailed logging and assertions
- Internal method testing via reflection
- Error scenario validation

### 2. Artisan Command (Interactive Testing)

**Location:** `app/Console/Commands/TestLetshEgoEftCommand.php`

```bash
# Run all tests
php artisan test:letshego-eft all

# Test specific operations
php artisan test:letshego-eft institutions
php artisan test:letshego-eft lookup --account=0752518001 --bank=016
php artisan test:letshego-eft transfer --account=0752518001 --bank=016 --amount=50000
php artisan test:letshego-eft status --ref=044-WK175371850125227076
php artisan test:letshego-eft reverse --ref=044-20220616000009

# Interactive mode (prompts for input)
php artisan test:letshego-eft lookup
php artisan test:letshego-eft transfer
```

**Features:**
- Interactive prompts for test data
- Real-time API testing
- Colored output and progress indicators
- Detailed response analysis
- Safety confirmations for transfers/reversals

### 3. HTTP Client Tests (Direct API Testing)

**Location:** `tests/http/letshego-eft-test.http`

Use with VS Code REST Client extension or similar HTTP client tools.

**Features:**
- 12 different HTTP test scenarios
- Direct API endpoint testing
- Variable substitution
- Rate limiting tests
- Error scenario validation

### 4. Bash Script (Automated Testing)

**Location:** `tests/scripts/test-letshego-eft.sh`

```bash
# Make executable (one time)
chmod +x tests/scripts/test-letshego-eft.sh

# Run all tests
./tests/scripts/test-letshego-eft.sh

# Run specific tests
./tests/scripts/test-letshego-eft.sh institutions
./tests/scripts/test-letshego-eft.sh lookup
./tests/scripts/test-letshego-eft.sh transfer    # Interactive confirmation
./tests/scripts/test-letshego-eft.sh status
./tests/scripts/test-letshego-eft.sh reversal   # Interactive confirmation
./tests/scripts/test-letshego-eft.sh laravel    # Run PHPUnit tests

# Show help
./tests/scripts/test-letshego-eft.sh help
```

**Features:**
- Colorized output
- Response time measurement
- JSON formatting
- Interactive confirmations for destructive operations
- Comprehensive error reporting

## 🔧 Configuration

### Environment Variables

The service uses these configuration keys (set in your `.env` or config files):

```env
# Letshego API Configuration
LETSHEGO_BASE_URL="https://api-staging-cb.letshego.com/letshego-api-gw/tz-eftpay-tips/v2"
LETSHEGO_CONSUMER_ID="12123"
LETSHEGO_API_KEY="your-jwt-token-here"
LETSHEGO_CALLBACK_URL="http://localhost:8005/api/letshego-callback"
```

### Test Data

Default test accounts used in the tests:

```php
// Letshego internal accounts
'21710176786' => 'Letshego Main Account'
'21710176787' => 'Letshego Test Account'
'21710176788' => 'Letshego Business Account'
'21710176789' => 'Letshego Default Account'

// External test accounts
'0752518001' => 'Test Account (Bank 016)'
'3401000000065' => 'Test Account (Bank 035)'
```

## 🚦 Test Scenarios

### 1. Institution List Test
```bash
GET /{consumer_id}/outwards/institution
```
- Tests API connectivity
- Validates authentication
- Returns list of supported banks

### 2. Account Enquiry Tests
```bash
GET /{consumer_id}/outwards/accountenquiry/{account}
```
- Valid account lookup
- Invalid account handling
- Missing headers validation
- Different bank codes

### 3. Transfer Tests
```bash
POST /{consumer_id}/outwards/transfers
```
- Small amount transfers (TIPS routing)
- Large amount transfers (TISS routing)
- Invalid transfer data
- Missing required fields

### 4. Transaction Status Tests
```bash
GET /{consumer_id}/outwards/status/{ref}
```
- Valid transaction status
- Invalid reference handling
- Non-existent transactions

### 5. Reversal Tests
```bash
POST /{consumer_id}/outwards/transfers/reversal
```
- Valid reversal requests
- Invalid reference handling
- Reversal reason validation

### 6. Error Handling Tests
- Invalid API keys
- Network timeouts
- Malformed requests
- Rate limiting scenarios

## 🔍 Debugging & Logging

All tests include comprehensive logging:

```bash
# View EFT service logs
tail -f storage/logs/payments.log

# View Laravel logs during tests
tail -f storage/logs/laravel.log
```

Log levels:
- **INFO**: Successful operations and major milestones
- **ERROR**: Failed operations and exceptions
- **DEBUG**: Detailed request/response data (enabled in debug mode)

## 📊 Expected Response Formats

### Successful Account Lookup
```json
{
  "success": true,
  "account_number": "0752518001",
  "account_name": "HAJI NYEMBO",
  "bank_code": "016",
  "can_receive": true,
  "response_time": 245.67
}
```

### Successful Transfer
```json
{
  "success": true,
  "reference": "EFT20241210123456ABC123",
  "routing_system": "TIPS",
  "letshego_reference": "044-WK1733820123",
  "message": "Transfer completed successfully via Letshego TIPS",
  "amount": 50000,
  "timestamp": "2024-12-10T14:30:00+03:00"
}
```

### Error Response
```json
{
  "success": false,
  "error": "Account not found",
  "account_number": "1234567890",
  "bank_code": "999"
}
```

## 🚨 Safety Notes

⚠️ **Important Safety Considerations:**

1. **Staging Environment**: All tests use Letshego's staging environment
2. **Test Accounts**: Only use designated test accounts
3. **Small Amounts**: Transfer tests use small amounts (50,000 TZS)
4. **Confirmations**: Real transfer/reversal tests require confirmation
5. **Rate Limits**: Respect API rate limits during testing

## 🔄 Continuous Integration

For CI/CD pipelines, use the PHPUnit tests:

```yaml
# .github/workflows/test.yml
- name: Run EFT Service Tests
  run: |
    ./vendor/bin/phpunit tests/Feature/Services/LetshEgoEftServiceTest.php
```

## 📞 Support

If tests fail or you need assistance:

1. Check the service logs for detailed error information
2. Verify API credentials and network connectivity
3. Ensure test accounts are still valid
4. Contact Letshego support for API-related issues

## 🔗 Related Files

- **Service**: `app/Services/Payments/ExternalFundsTransferService.php`
- **Config**: `config/services.php` (letshego_payments section)
- **Routes**: Routes for callback handling
- **Documentation**: Postman collections in `doc/` folder