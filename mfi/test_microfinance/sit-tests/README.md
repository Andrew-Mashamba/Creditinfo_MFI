# System Integration Tests (SIT)

This directory contains System Integration Tests for all external API integrations in the SACCOS Core System.

## Overview

The SIT suite tests the following external API integrations:

1. **Bank Transaction Service** - IFT, EFT, and Mobile transactions
2. **GEPG Gateway** - Government Electronic Payment Gateway integration
3. **Luku Gateway** - Electricity token purchase system
4. **NBC SMS** - SMS notification service
5. **AI Services** - Integration with Groq, OpenAI, Together AI, and Claude

## Running Tests

### Run All Tests
```bash
php sit-tests/run-all-tests.php
```

### Run Specific Test
```bash
php sit-tests/run-all-tests.php --test=BankTransactionServiceTest
```

### List Available Tests
```bash
php sit-tests/run-all-tests.php --list
```

### Run Individual Test Files
```bash
php sit-tests/BankTransactionServiceTest.php
php sit-tests/GEPGGatewayTest.php
php sit-tests/LukuGatewayTest.php
php sit-tests/NBCSMSTest.php
php sit-tests/AIServicesTest.php
```

## Test Coverage

### Bank Transaction Service
- IFT (Intra-Financial Transaction) processing
- EFT (Electronic Funds Transfer) processing
- Mobile money transactions
- Invalid transaction type handling
- Connection failure scenarios

### GEPG Gateway
- Bill query (control number verification)
- Bill payment processing
- Prepaid payment (quote generation)
- Transaction status checking
- XML signing and verification
- Error handling for various scenarios

### Luku Gateway
- Token query operations
- Token purchase transactions
- Meter number validation
- Transaction status checking
- SSL configuration verification
- Error handling and rate limiting

### NBC SMS
- Single SMS sending
- Bulk SMS operations
- SMS delivery status checking
- Rate limiting enforcement
- Invalid phone number handling
- Long message (multipart SMS) handling

### AI Services
- Groq API integration
- OpenAI API integration
- Together AI API integration
- Claude API integration
- Model availability checking
- Response validation

## Configuration

The tests use configuration from your Laravel `.env` file and `config/services.php`. Ensure the following environment variables are set:

### GEPG Gateway
```
GEPG_GATEWAY_URL=https://gepg-gateway.example.com
GEPG_CHANNEL_ID=YOUR_CHANNEL_ID
GEPG_CHANNEL_NAME=YOUR_CHANNEL_NAME
GEPG_AUTHORIZATION=YOUR_AUTH_TOKEN
```

### Luku Gateway
```
LUKU_GATEWAY_BASE_URL=https://luku-gateway.example.com
LUKU_GATEWAY_CHANNEL_ID=YOUR_CHANNEL_ID
LUKU_GATEWAY_CHANNEL_NAME=YOUR_CHANNEL_NAME
LUKU_GATEWAY_API_TOKEN=YOUR_API_TOKEN
```

### NBC SMS
```
NBC_SMS_BASE_URL=https://sms-engine.tz.af.absa.local
NBC_SMS_API_KEY=YOUR_API_KEY
NBC_SMS_CHANNEL_ID=KRWT43976
```

### AI Services
```
GROQ_API_KEY=your_groq_api_key
OPENAI_API_KEY=your_openai_api_key
TOGETHER_API_KEY=your_together_api_key
CLAUDE_API_KEY=your_claude_api_key
```

## Test Results

Tests will output detailed results for each API integration, including:
- Individual test case results (PASSED/FAILED/ERROR)
- Response times and performance metrics
- Error messages and debugging information
- Overall summary with pass/fail counts

## Mocking

The tests use HTTP mocking to simulate API responses without making actual external calls. This ensures:
- Tests can run without external dependencies
- Consistent and predictable test results
- No usage of API quotas or credits
- Fast test execution

To test against real APIs, you would need to modify the test files to remove the `Http::fake()` calls.

## Troubleshooting

1. **Class not found errors**: Ensure you run `composer dump-autoload` if you get autoloading errors
2. **Configuration errors**: Check that your `.env` file has the necessary API credentials
3. **SSL certificate errors**: For Luku Gateway, ensure SSL certificates are properly configured
4. **Rate limiting**: Some tests simulate rate limiting - this is expected behavior

## Adding New Tests

To add tests for a new API integration:

1. Create a new test file in the `sit-tests` directory
2. Follow the naming convention: `ServiceNameTest.php`
3. Implement the test class with a `runAllTests()` method
4. Add the test to the `$tests` array in `run-all-tests.php`
5. Document the new test in this README

## CI/CD Integration

These tests can be integrated into your CI/CD pipeline:

```yaml
# Example GitHub Actions workflow
- name: Run SIT Tests
  run: php sit-tests/run-all-tests.php
```

The test runner exits with code 0 on success and 1 on failure, making it suitable for CI/CD pipelines.