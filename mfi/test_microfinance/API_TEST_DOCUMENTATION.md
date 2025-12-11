# API Service Testing Documentation

## Overview
This document provides comprehensive information about the API service testing infrastructure, including the recent field mapping fixes and how to properly test all 12 service endpoints.

## Recent Field Mapping Fixes

### Issue Discovered
The ServiceTestController was passing field names that didn't match what the underlying service classes expected, which would cause API calls to fail. The following mismatches were identified and fixed:

### 1. Internal Funds Transfer
**Controller was passing:**
- `sourceAccount` → Service expects: `from_account`
- `destinationAccount` → Service expects: `to_account`
- `description` → Service expects: `narration`
- `beneficiaryName` → Service expects: `beneficiary_name`

**Fix Applied:** Updated controller to map fields correctly in `app/Http/Controllers/Api/ServiceTestController.php:100-109`

### 2. External Funds Transfer
**Controller was passing:**
- `sourceAccount` → Service expects: `from_account`
- `destinationAccount` → Service expects: `to_account`
- `destinationBank` → Service expects: `bank_code`
- `description` → Service expects: `narration`
- `beneficiaryName` → Service expects: `beneficiary_name`

**Fix Applied:** Updated controller to map fields correctly in `app/Http/Controllers/Api/ServiceTestController.php:247-257`

### 3. Utilities Payment
**Controller was passing:**
- `utilityType`, `accountNumber`, `meterNumber` → Service expects: `from_account`, `bill_reference`, `meter_number`
- Missing required fields for service: `payer_name`, `sp_code`

**Fix Applied:** Updated controller to map fields correctly and determine bill type in `app/Http/Controllers/Api/ServiceTestController.php:680-710`

## Available Test Scripts

### 1. test_services_real.py (Recommended)
Python script with actual request structures based on controller validations.

**Features:**
- Real request body structures from controller validations
- Expected response format validation
- Beautiful tabulated output
- Response time tracking
- Comprehensive error reporting

**Usage:**
```bash
# Test all services
python3 test_services_real.py

# Test specific service
python3 test_services_real.py --service internal_funds_transfer

# Test against staging environment
python3 test_services_real.py --env staging
```

### 2. test_services.sh
Bash wrapper script with dependency checking and user-friendly interface.

**Features:**
- Dependency validation (PHP, curl, Laravel server)
- Color-coded output
- Result archiving
- Log file generation

**Usage:**
```bash
# Test all services
./test_services.sh

# List available services
./test_services.sh list

# Test specific service
./test_services.sh internal-funds-transfer

# Verbose mode with log watching
./test_services.sh -v -w sms
```

### 3. test_all_services.php
PHP script that directly calls the API endpoints.

**Usage:**
```bash
# Test all services
php test_all_services.php

# Test specific service
php test_all_services.php local internal_funds_transfer
```

## API Endpoints

Base URL: `http://127.0.0.1:8000/api/test-services`

| Service | Endpoint | Method |
|---------|----------|---------|
| List Services | `/` | GET |
| Internal Funds Transfer | `/internal-funds-transfer` | POST |
| External Funds Transfer | `/external-funds-transfer` | POST |
| Mini Statement | `/mini-statement` | POST |
| Full Statement | `/full-statement` | POST |
| Account Lookup | `/account-lookup` | POST |
| Utilities Payment | `/utilities` | POST |
| SMS Service | `/sms` | POST |
| Direct Debit | `/direct-debit` | POST |
| Control Number Payments | `/control-number-payments` | POST |
| LUKU | `/luku` | POST |
| GEPG | `/gepg` | POST |
| Pay By Link | `/pay-by-link` | POST |

## Request Structures

### Internal Funds Transfer
```json
{
  "source_account": "06012040022",        // Required
  "destination_account": "06012040023",   // Required
  "amount": 10000.00,                     // Required, min: 0.01
  "currency": "TZS",                      // Optional, default: TZS
  "description": "Transfer description",  // Optional, max: 255 chars
  "reference": "TEST_INT_001",           // Optional
  "beneficiary_name": "John Doe",        // Optional
  "sender_name": "Jane Smith"            // Optional (added for service)
}
```

### External Funds Transfer
```json
{
  "source_account": "06012040022",        // Required
  "destination_account": "1234567890",    // Required
  "destination_bank": "CRDB",             // Required
  "swift_code": "CORUTZTZ",               // Optional
  "amount": 50000.00,                     // Required, min: 0.01
  "currency": "TZS",                      // Optional
  "beneficiary_name": "Jane Smith",       // Required
  "description": "External transfer",     // Optional
  "reference": "TEST_EXT_001",           // Optional
  "sender_name": "John Sender"           // Optional (added for service)
}
```

### Mini Statement
```json
{
  "account_number": "06012040022",  // Required
  "limit": 5,                        // Optional, min: 1, max: 10
  "channel_code": "SACCOSNBC"       // Optional
}
```

### Full Statement
```json
{
  "account_number": "06012040022",          // Required
  "from_date": "2024-12-01",                // Required
  "to_date": "2024-12-31",                  // Required
  "format": "json",                         // Optional: json/pdf/excel
  "channel_code": "SACCOSNBC"               // Optional
}
```

### Account Lookup
```json
{
  "account_number": "06012040022",  // One of these required
  "phone_number": "255700000000",   // Optional
  "id_number": "ID123456",          // Optional
  "email": "test@example.com",      // Optional
  "channel_code": "SACCOSNBC"       // Optional
}
```

### Utilities Payment
```json
{
  "utility_type": "electricity",     // Required: water/electricity/gas/internet/tv
  "account_number": "06012040022",   // Required
  "meter_number": "LUKU123456",      // Optional
  "amount": 20000.00,                // Required, min: 0.01
  "customer_name": "Test Customer",  // Optional
  "phone_number": "255700000000",    // Optional
  "provider_code": "TANESCO"         // Required
}
```

### SMS Service
```json
{
  "phone_numbers": ["255700000001", "255700000002"],  // Required array
  "message": "Test SMS message",                       // Required, max: 160
  "sender_id": "SACCOS",                              // Optional, max: 11
  "schedule_time": null,                              // Optional
  "sms_type": "TRANSACTIONAL"                         // Optional: TRANSACTIONAL/PROMOTIONAL
}
```

### Direct Debit
```json
{
  "account_number": "06012040022",         // Required
  "amount": 100000.00,                     // Required, min: 0.01
  "mandate_reference": "DD_TEST_001",      // Required
  "beneficiary_account": "06012040023",    // Required
  "beneficiary_name": "Test Beneficiary",  // Required
  "frequency": "monthly",                  // Optional: once/daily/weekly/monthly/yearly
  "start_date": "2025-01-01",             // Optional
  "end_date": "2025-12-31",               // Optional
  "bank_code": "NBC"                      // Optional
}
```

### Control Number Payments
```json
{
  "control_number": "991234567890",         // Required
  "payer_name": "Test Payer",               // Required
  "payer_phone": "255700000000",            // Optional
  "amount": 75000.00,                       // Required, min: 0.01
  "payment_description": "Payment desc",    // Optional
  "currency": "TZS",                        // Optional
  "account_number": "06012040022"           // Required
}
```

### LUKU
```json
{
  "meter_number": "04123456789",      // Required
  "amount": 10000.00,                 // Required, min: 1000
  "customer_phone": "255700000000",   // Required
  "customer_name": "Test Customer",   // Optional
  "account_number": "28012040011"     // Optional (default: 28012040011)
}
```

### GEPG
```json
{
  "bill_id": "BILL_12345",           // Required
  "payer_name": "Test Payer",        // Required
  "payer_phone": "255700000000",     // Required
  "payer_email": "test@example.com", // Optional
  "amount": 150000.00,               // Required, min: 0.01
  "payment_option": "exact",         // Required: exact/partial/full
  "currency": "TZS",                 // Optional
  "account_number": "06012040022"    // Required
}
```

### Pay By Link
```json
{
  "amount": 25000.00,                      // Required, min: 0.01
  "description": "Payment for services",   // Required
  "customer_email": "customer@test.com",   // Optional
  "customer_phone": "255700000000",        // Optional
  "customer_name": "Test Customer",        // Optional
  "expiry_hours": 24,                      // Optional, min: 1, max: 720
  "callback_url": "https://callback.url",  // Optional
  "currency": "TZS"                        // Optional
}
```

## Expected Response Structures

### Success Response
```json
{
  "status": "success",
  "request_id": "IFT_uuid-here",
  "message": "Operation successful",
  "data": {
    "reference": "transaction_reference",
    "status": "SUCCESS",
    "amount": 10000.00,
    "currency": "TZS",
    "timestamp": "2025-01-24T10:30:00"
  },
  "transaction_id": "transaction_reference",
  "timestamp": "2025-01-24 10:30:00",
  "processing_time_ms": 1250.5
}
```

### Error Response
```json
{
  "status": "error",
  "request_id": "IFT_uuid-here",
  "message": "Error description",
  "details": {
    "error_code": "ERR001",
    "error_description": "Detailed error message"
  },
  "timestamp": "2025-01-24 10:30:00"
}
```

## Logging

All API requests generate detailed logs with unique request IDs:

- **Request ID Format:** `SERVICE_UUID` (e.g., `IFT_123e4567-e89b-12d3-a456-426614174000`)
- **Log Location:** `storage/logs/laravel.log`
- **Log Levels:** INFO for normal operations, WARNING for failures, ERROR for exceptions

### Log Entry Example
```
[2025-01-24 10:30:00] local.INFO: === INTERNAL FUNDS TRANSFER REQUEST STARTED === {
  "request_id": "IFT_123e4567-e89b-12d3-a456-426614174000",
  "timestamp": "2025-01-24T10:30:00.000000Z",
  "ip_address": "127.0.0.1",
  "endpoint": "http://127.0.0.1:8000/api/test-services/internal-funds-transfer",
  "raw_input": {...}
}
```

## Troubleshooting

### Common Issues

1. **Field Mapping Errors**
   - Symptom: API returns error about missing fields
   - Solution: Check that controller properly maps fields to service expectations

2. **External Service Connection Failures**
   - Symptom: Timeout or connection refused errors
   - Solution: Verify external service configurations in `.env` file

3. **Validation Errors**
   - Symptom: 422 Unprocessable Entity responses
   - Solution: Check request body against validation rules in controller

4. **Authentication Issues**
   - Symptom: 401 Unauthorized responses
   - Solution: Verify API key configuration in test scripts

### Debug Mode

Enable detailed debugging by setting in `.env`:
```
APP_DEBUG=true
LOG_LEVEL=debug
```

## Testing Workflow

1. **Start Laravel Server**
   ```bash
   php artisan serve
   ```

2. **Run Dependency Check**
   ```bash
   ./test_services.sh -h
   ```

3. **Test Individual Service**
   ```bash
   python3 test_services_real.py --service internal_funds_transfer
   ```

4. **Review Logs**
   ```bash
   tail -f storage/logs/laravel.log | grep "REQUEST_ID"
   ```

5. **Test All Services**
   ```bash
   ./test_services.sh all -v
   ```

## Performance Benchmarks

Expected response times for each service:

| Service | Expected Time | Timeout |
|---------|---------------|---------|
| Internal Funds Transfer | 500-1500ms | 10s |
| External Funds Transfer | 1000-3000ms | 15s |
| Mini Statement | 300-800ms | 5s |
| Full Statement | 1000-5000ms | 20s |
| Account Lookup | 200-500ms | 5s |
| Utilities Payment | 800-2000ms | 10s |
| SMS Service | 100-500ms | 5s |
| Direct Debit | 500-1500ms | 10s |
| Control Number Payments | 1500-3500ms | 15s |
| LUKU | 2000-5000ms | 20s |
| GEPG | 2000-5000ms | 20s |
| Pay By Link | 300-800ms | 5s |

## Configuration

### Environment Variables
Key environment variables for service configuration:

```env
# NBC API Configuration
NBC_API_URL=https://api.nbc.co.tz
NBC_API_KEY=your_api_key
NBC_API_SECRET=your_api_secret

# SMS Gateway
SMS_GATEWAY_URL=https://sms.provider.com
SMS_API_KEY=your_sms_key

# GEPG Configuration
GEPG_API_URL=https://gepg.go.tz
GEPG_SP_CODE=your_sp_code
GEPG_SUB_SP_CODE=your_sub_sp_code

# LUKU Configuration
LUKU_API_URL=https://luku.tanesco.co.tz
LUKU_API_KEY=your_luku_key
```

## Monitoring

### Health Check Endpoint
```bash
curl http://127.0.0.1:8000/api/test-services/
```

### Service Status Check
Monitor service availability and response times using the test scripts in watch mode:

```bash
watch -n 60 'python3 test_services_real.py --service internal_funds_transfer'
```

## Support

For issues or questions:
1. Check the detailed logs in `storage/logs/laravel.log`
2. Review the test results in `test_results/` directory
3. Ensure all environment variables are properly configured
4. Verify external service endpoints are accessible

## Actual Test Results (2025-09-24)

### Testing Environment
- **Server:** Laravel development server on port 8000
- **Environment:** Local development
- **External Services:** NBC UAT endpoints (not accessible from local environment)

### Test Execution Summary

| Service | Status | Response Time | Issue |
|---------|--------|---------------|-------|
| Internal Funds Transfer | ❌ Failed | 1.66ms | NBC API unreachable (cbpuat.intra.nbc.co.tz) |
| External Funds Transfer | ❌ Failed | N/A | NBC API unreachable |
| Mini Statement | ❌ Failed | N/A | Private key missing (storage/keys/private.pem) |
| Full Statement | ❌ Failed | N/A | Private key missing |
| Account Lookup | ❌ Failed | N/A | Private key missing |
| Utilities | Not tested | - | - |
| SMS | ❌ Timeout | 30s | Service timeout |
| Direct Debit | Not tested | - | - |
| Control Number Payments | Not tested | - | - |
| LUKU | ❌ Failed | N/A | NBC Gateway unreachable (nbc-gateway-uat.intra.nbc.co.tz) |
| GEPG | Not tested | - | - |
| Pay By Link | ❌ Failed | N/A | Missing 'target' field in service |

### Detailed Test Results

#### 1. Internal Funds Transfer
**Request:**
```json
{
  "source_account": "06012040022",
  "destination_account": "06012040023",
  "amount": 10000.00,
  "currency": "TZS",
  "description": "Test internal transfer",
  "reference": "TEST_INT_001",
  "beneficiary_name": "John Doe",
  "sender_name": "Jane Smith"
}
```

**Response (HTTP 200):**
```json
{
  "status": "error",
  "request_id": "IFT_91799811-1b8d-45d0-9bef-d981e2c0e389",
  "message": "Account lookup failed",
  "details": {
    "success": false,
    "error": "cURL error 6: Could not resolve host: cbpuat.intra.nbc.co.tz",
    "account_number": "06012040023"
  },
  "timestamp": "2025-09-24 17:43:43",
  "processing_time_ms": 1.66
}
```

#### 2. Mini Statement
**Response (HTTP 500):**
```json
{
  "status": "error",
  "request_id": "MINI_632b1d05-d29f-4020-95d7-05dbd669cc37",
  "message": "Private key file not found: storage/keys/private.pem"
}
```

#### 3. Account Lookup
**Response (HTTP 500):**
```json
{
  "status": "error",
  "request_id": "LOOKUP_c91ba74e-1887-4d34-9ee4-e6eb925a15dc",
  "message": "Private key file not found: storage/keys/private.pem"
}
```

#### 4. SMS Service
**Response:** Timeout after 30 seconds
```
Maximum execution time of 30 seconds exceeded in SmsService.php on line 189
```

#### 5. LUKU
**Response (HTTP 400):**
```json
{
  "status": "error",
  "request_id": "LUKU_2588b883-b42b-41b3-90d0-fff5461e4fa1",
  "message": "Meter lookup failed",
  "details": {
    "error": "Failed to perform lookup: cURL error 6: Could not resolve host: nbc-gateway-uat.intra.nbc.co.tz"
  }
}
```

#### 6. Pay By Link
**Response (HTTP 500):**
```json
{
  "status": "error",
  "request_id": "LINK_f3e3e0b5-8558-4bbd-8a4a-919e4d97e4b9",
  "message": "Required field 'target' is missing"
}
```

### Issues Identified

1. **NBC API Connectivity:**
   - The NBC UAT endpoints (`cbpuat.intra.nbc.co.tz`, `nbc-gateway-uat.intra.nbc.co.tz`) are internal NBC URLs
   - These are not accessible from local development environments
   - Need VPN access or proper network configuration to reach NBC internal services

2. **Missing Cryptographic Keys:**
   - Services requiring encryption (Mini Statement, Full Statement, Account Lookup) fail due to missing private key
   - Private key expected at: `storage/keys/private.pem`
   - Need to generate or obtain proper RSA key pair for encryption/decryption

3. **Service Implementation Issues:**
   - SMS Service: Infinite loop or blocking operation causing timeout
   - Pay By Link: Missing required 'target' field in service implementation
   - Field mapping has been fixed but services still need proper external connectivity

4. **Configuration Requirements:**
   - Missing `.env` configurations for external services
   - Missing API keys and secrets for NBC services
   - Missing SSL certificates for secure communication

### Recommendations

1. **For Local Testing:**
   - Create mock responses when external services are unreachable
   - Add a test mode flag to bypass external API calls
   - Implement proper timeout handling for all external service calls

2. **For Staging/Production:**
   - Ensure proper network connectivity to NBC internal services
   - Configure VPN or whitelist IP addresses
   - Generate and install required cryptographic keys
   - Set proper environment variables for all external services

3. **Code Improvements:**
   - Add timeout configuration for all HTTP clients (currently SMS times out)
   - Implement circuit breaker pattern for external services
   - Add health check endpoints for each external service dependency
   - Improve error messages to be more descriptive

4. **Documentation Needs:**
   - Document all required environment variables
   - Provide setup instructions for cryptographic keys
   - Document network requirements for NBC API access
   - Create troubleshooting guide for common connectivity issues

## Version History

- **v1.0** - Initial implementation with mock services
- **v2.0** - Integration with real service classes
- **v3.0** - Field mapping fixes and comprehensive logging
- **v3.1** - Added real request/response structures and test documentation
- **v3.2** - Added actual test results showing external service connectivity issues