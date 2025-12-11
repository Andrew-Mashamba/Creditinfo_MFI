# SIT Test Pass/Fail Criteria

## Overview
This document defines the criteria used to determine whether each System Integration Test (SIT) passes or fails.

## General Pass Criteria

A test is considered **PASSED** when ALL of the following conditions are met:

### 1. Connectivity & Response
- ✅ **Response Received**: The API must return a response (not timeout or connection refused)
- ✅ **Valid HTTP Status Code**: Response must have a valid HTTP status code
- ✅ **Response Time**: Response received within maximum allowed time (default: 30 seconds)

### 2. Response Validation
- ✅ **Expected Status Code**: HTTP status matches expected (e.g., 200 for success)
- ✅ **Response Format**: Response is in expected format (JSON/XML)
- ✅ **Required Fields**: All mandatory fields present in response
- ✅ **Success Indicator**: Response contains success indicator field with correct value

### 3. Data Integrity
- ✅ **Transaction ID**: Valid transaction/reference ID returned (for transactional APIs)
- ✅ **Data Types**: Response fields have correct data types
- ✅ **Business Rules**: Response adheres to business logic rules

## Test Categories

### Positive Tests
Tests that verify normal, expected behavior:
- Must receive a successful response
- Status code should be 2xx (success)
- Response must contain valid data
- All required fields must be present

### Negative Tests
Tests that verify error handling:
- Must receive an error response
- Status code should match expected error (4xx or 5xx)
- Error message must be meaningful
- Should not crash or timeout

## API-Specific Criteria

### Bank Transaction Service

| Test | Expected Status | Success Criteria | Max Response Time |
|------|----------------|------------------|-------------------|
| IFT Transaction | 200 | status="success", has transaction_id | 5 seconds |
| EFT Transaction | 200 | status="success", has transaction_id | 10 seconds |
| Mobile Transaction | 200 | status="success", has mobile_network | 8 seconds |
| Invalid Transaction Type | 400 | status="error", correct error message | 5 seconds |
| Connection Failure | 500 | Handles timeout gracefully | 60 seconds |

### GEPG Gateway

| Test | Expected Status | Success Criteria | Max Response Time |
|------|----------------|------------------|-------------------|
| Bill Query | 200 | BillStsCode="0000", has control number | 10 seconds |
| Bill Payment | 200 | ResultCode="0000", has payment confirmation | 15 seconds |
| Prepaid Payment | 200 | ResultCode="0000", has token | 15 seconds |
| Status Check | 200 | TrxStatus="SUCCESS" | 5 seconds |
| XML Signing | 200 | Valid signature present | 3 seconds |

### Luku Gateway

| Test | Expected Status | Success Criteria | Max Response Time |
|------|----------------|------------------|-------------------|
| Token Query | 200 | status="success", has meter details | 8 seconds |
| Token Purchase | 200 | status="success", has token & units | 20 seconds |
| Meter Validation | 200/400 | Correctly identifies valid/invalid meters | 3 seconds |
| Transaction Status | 200 | Has transaction status | 5 seconds |

### NBC SMS

| Test | Expected Status | Success Criteria | Max Response Time |
|------|----------------|------------------|-------------------|
| Single SMS | 200 | status="success", has message_id | 5 seconds |
| Bulk SMS | 200 | status="success", has batch_id | 10 seconds |
| SMS Status | 200 | Has delivery_status (DELIVERED/PENDING/FAILED) | 3 seconds |
| Rate Limiting | 429 | Returns rate limit error with retry_after | 1 second |
| Invalid Phone | 400 | Returns validation error | 2 seconds |

### AI Services

| Test | Expected Status | Success Criteria | Max Response Time |
|------|----------------|------------------|-------------------|
| Groq API | 200 | Has choices array with content | 30 seconds |
| OpenAI API | 200 | Has choices array with content | 60 seconds |
| Claude API | 200 | Has content array with text | 45 seconds |
| Together API | 200 | Has choices array with content | 45 seconds |

## Failure Conditions

A test is considered **FAILED** when ANY of the following occur:

### Critical Failures
- ❌ No response received (timeout/connection refused)
- ❌ Unexpected HTTP status code
- ❌ Missing required fields in response
- ❌ Invalid response format (not JSON/XML when expected)
- ❌ Success indicator shows failure when success expected

### Performance Failures
- ❌ Response time exceeds maximum allowed
- ❌ API returns timeout error
- ❌ Connection dropped during request

### Data Failures
- ❌ Missing or invalid transaction ID
- ❌ Incorrect data types in response
- ❌ Business logic violations
- ❌ Calculation errors

## Test Result Classification

### PASSED ✅
- All criteria met
- Response received within time limit
- Expected data returned
- No errors encountered

### FAILED ❌
- One or more criteria not met
- Unexpected response or error
- Performance requirements not met
- Data validation failed

### ERROR 🔴
- Exception thrown during test
- Connection failure
- Timeout occurred
- Unexpected system error

### WARNING ⚠️
- Test passed but with concerns
- Performance near threshold
- Deprecated API usage detected
- Non-critical validation issues

## Logging Requirements

For each test, the following MUST be logged:

### Request Details
```json
{
  "timestamp": "2024-01-15 10:30:45",
  "test_name": "IFT Transaction",
  "method": "POST",
  "url": "https://api.bank.com/ift-transaction",
  "headers": {...},
  "body": {...}
}
```

### Response Details
```json
{
  "response_received": true,
  "status_code": 200,
  "response_time_ms": 1234,
  "headers": {...},
  "body": {...},
  "error": null
}
```

### Test Result
```json
{
  "test_passed": true,
  "criteria_checked": ["Response Received", "Status Code", "Required Fields"],
  "failures": [],
  "warnings": []
}
```

## Usage Example

```php
// Evaluate test results
$testResult = [
    'response_received' => true,
    'status_code' => 200,
    'response_time_ms' => 1500,
    'response_body' => [
        'status' => 'success',
        'transaction_id' => 'TXN123456'
    ]
];

$evaluation = TestCriteria::evaluate(
    'BankTransactionService',
    'IFT Transaction',
    $testResult
);

if ($evaluation['passed']) {
    echo "✅ Test PASSED\n";
} else {
    echo "❌ Test FAILED\n";
    foreach ($evaluation['failures'] as $failure) {
        echo "  - $failure\n";
    }
}
```

## Reporting

Each test run generates:
1. **Detailed Logs**: Complete request/response data
2. **Summary Report**: Pass/fail statistics
3. **Performance Metrics**: Response times and throughput
4. **Error Analysis**: Detailed failure reasons

## Continuous Improvement

Test criteria should be reviewed and updated:
- When API specifications change
- When performance requirements change
- When new validation rules are added
- Based on production incident analysis