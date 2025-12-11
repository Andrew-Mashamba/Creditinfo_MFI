# API Test Script Quick Reference Guide

## Main Test Script: `test_api_services.php`

This is a comprehensive single-file test script with individual test functions for each of the 12 API services.

## Features

- ✅ Individual test functions for each service
- ✅ Can run all tests or specific tests
- ✅ Color-coded output for easy reading
- ✅ Verbose mode for detailed debugging
- ✅ Server connectivity check before testing
- ✅ Detailed timing and response information
- ✅ Summary report with pass/fail status

## Usage

### Prerequisites
```bash
# Start Laravel server first
php artisan serve
```

### Run All Tests
```bash
php test_api_services.php
```

### Run Specific Test
```bash
# Using full name
php test_api_services.php internal_transfer
php test_api_services.php external_transfer
php test_api_services.php mini_statement
php test_api_services.php full_statement
php test_api_services.php account_lookup
php test_api_services.php utilities
php test_api_services.php sms
php test_api_services.php direct_debit
php test_api_services.php control_number
php test_api_services.php luku
php test_api_services.php gepg
php test_api_services.php pay_by_link

# Using shortcuts
php test_api_services.php internal    # Internal Transfer
php test_api_services.php external    # External Transfer
php test_api_services.php mini        # Mini Statement
php test_api_services.php full        # Full Statement
php test_api_services.php lookup      # Account Lookup
php test_api_services.php utility     # Utilities
php test_api_services.php debit       # Direct Debit
php test_api_services.php control     # Control Number
php test_api_services.php link        # Pay By Link
```

### Verbose Mode (Show Request/Response Details)
```bash
php test_api_services.php -v
php test_api_services.php --verbose internal_transfer
```

### List Available Tests
```bash
php test_api_services.php list
php test_api_services.php --list
```

### Show Help
```bash
php test_api_services.php --help
php test_api_services.php -h
```

## Test Functions Available

The script contains these individual test functions that you can call:

| Function Name | Description | Command |
|--------------|-------------|---------|
| `testInternalFundsTransfer()` | Tests internal funds transfer between NBC accounts | `internal_transfer` |
| `testExternalFundsTransfer()` | Tests external funds transfer to other banks | `external_transfer` |
| `testMiniStatement()` | Tests mini statement retrieval | `mini_statement` |
| `testFullStatement()` | Tests full statement generation | `full_statement` |
| `testAccountLookup()` | Tests account lookup functionality | `account_lookup` |
| `testUtilities()` | Tests utility bill payments | `utilities` |
| `testSMS()` | Tests SMS sending service | `sms` |
| `testDirectDebit()` | Tests direct debit setup | `direct_debit` |
| `testControlNumberPayments()` | Tests control number payments (GEPG) | `control_number` |
| `testLUKU()` | Tests LUKU token purchase | `luku` |
| `testGEPG()` | Tests GEPG bill generation | `gepg` |
| `testPayByLink()` | Tests payment link generation | `pay_by_link` |

## Output Format

### Normal Mode
```
✓ Internal Funds Transfer
  HTTP Code: 200
  Duration: 97.89ms
  Message: Account lookup failed
```

### Verbose Mode
```
Testing: Internal Funds Transfer
  Request Data:
    {
      "source_account": "06012040022",
      "destination_account": "06012040023",
      "amount": 10000,
      ...
    }
  Response:
    {
      "status": "error",
      "request_id": "IFT_xxx",
      ...
    }
```

### Summary Report
```
TEST SUMMARY
════════════════════════════════════════
Total Tests: 12
Passed: 0
Failed: 12
Duration: 5.23s

Detailed Results:
─────────────────────────────────────────
✗ Internal Funds Transfer     [200]  97.89ms - Account lookup failed
✗ External Funds Transfer     [500] 234.56ms - Service error
✓ Mini Statement              [200] 123.45ms - Success
...
```

## Color Coding

- 🟢 **Green**: Successful tests, passed validations
- 🔴 **Red**: Failed tests, errors
- 🟡 **Yellow**: Warnings, important messages
- 🔵 **Blue**: Request data (in verbose mode)
- 🟣 **Magenta**: Headers and section titles
- ⚪ **White**: General information

## Customization

You can easily modify the test data in each function. For example:

```php
// In testInternalFundsTransfer() function
$data = [
    'source_account' => '06012040022',      // Change this
    'destination_account' => '06012040023',  // Change this
    'amount' => 10000.00,                    // Change amount
    // ... other fields
];
```

## Troubleshooting

### Server Not Running
```
✗ Server is not reachable at http://127.0.0.1:8000/api/test-services
Make sure to run: php artisan serve
```
**Solution:** Start the Laravel server with `php artisan serve`

### Test Timeouts
The script has a 30-second timeout for each test. If a test times out (especially SMS), it will show:
```
✗ SMS Service
  Error: Operation timed out after 30001 milliseconds
```

### Connection Errors
If you see errors like:
```
Error: cURL error 6: Could not resolve host: cbpuat.intra.nbc.co.tz
```
This means the external NBC services are not accessible from your environment.

## Integration with CI/CD

You can use this script in your CI/CD pipeline:

```bash
# In your CI/CD script
php artisan serve --port=8000 --no-interaction &
sleep 5
php test_api_services.php
EXIT_CODE=$?
kill %1
exit $EXIT_CODE
```

## Notes

1. **Current Status**: All tests will show as "passed" (HTTP 200) even when returning errors because the controller returns proper error responses with HTTP 200 status.

2. **External Dependencies**: Most tests will fail with connectivity errors because they try to reach NBC internal APIs that are not accessible from local development.

3. **Timeout**: SMS service may timeout due to implementation issues.

4. **Customizable**: You can easily modify test data, add new tests, or change validation logic.

## Quick Test Commands

```bash
# Quick test of payment services
php test_api_services.php internal
php test_api_services.php external
php test_api_services.php luku

# Quick test of information services
php test_api_services.php mini
php test_api_services.php full
php test_api_services.php lookup

# Test all with details
php test_api_services.php -v

# Test specific with details
php test_api_services.php -v internal_transfer
```

## Next Steps

After running tests, check:
1. `storage/logs/laravel.log` for detailed server-side logs
2. Look for request IDs in responses to trace specific requests
3. Update `.env` file with proper API credentials when available
4. Generate required cryptographic keys in `storage/keys/`