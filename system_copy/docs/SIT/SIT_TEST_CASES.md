# SACCOS Core System - SIT Test Cases

## Overview
This document provides detailed test cases for all API integrations in the SACCOS Core System, organized by service category.

---

## 1. Payment Gateway Test Cases

### NBC Payment Service Tests

#### Test Case: NBC_001 - Outgoing Transfer Processing
**Objective**: Verify successful outgoing payment processing
**Priority**: High
**Preconditions**: Valid API credentials, sufficient account balance

```php
public function testOutgoingTransfer()
{
    $testData = [
        'account_from' => '1234567890',
        'account_to' => '0987654321',
        'amount' => 10000.00,
        'currency' => 'TZS',
        'description' => 'Test transfer',
        'reference' => 'TXN' . time()
    ];
    
    $result = $this->service->processOutgoingTransfer($testData);
    
    // Assertions
    $this->assertTrue($result['success']);
    $this->assertNotEmpty($result['transaction_id']);
    $this->assertLessThan(5000, $result['response_time']); // 5 seconds max
    $this->assertEquals('SUCCESS', $result['status']);
}
```

**Expected Results**:
- ✅ HTTP Status: 200
- ✅ Response Time: < 5 seconds
- ✅ Transaction ID: Present
- ✅ Status: SUCCESS

#### Test Case: NBC_002 - Digital Signature Generation
**Objective**: Verify digital signature generation and validation
**Priority**: High

```php
public function testDigitalSignature()
{
    $payload = [
        'account_from' => '1234567890',
        'amount' => 10000.00,
        'timestamp' => time()
    ];
    
    $signature = $this->service->generateSignature($payload);
    $isValid = $this->service->verifySignature($payload, $signature);
    
    $this->assertNotEmpty($signature);
    $this->assertTrue($isValid);
}
```

#### Test Case: NBC_003 - Callback URL Processing
**Objective**: Verify callback URL handling for payment confirmations
**Priority**: Medium

```php
public function testCallbackHandling()
{
    $callbackData = [
        'transaction_id' => 'TXN123456',
        'status' => 'SUCCESS',
        'amount' => 10000.00,
        'reference' => 'REF123'
    ];
    
    $result = $this->service->processCallback($callbackData);
    
    $this->assertTrue($result['success']);
    $this->assertEquals('PROCESSED', $result['callback_status']);
}
```

#### Test Case: NBC_004 - Error Handling
**Objective**: Verify proper error handling for invalid requests
**Priority**: Medium

```php
public function testErrorHandling()
{
    $invalidData = [
        'account_from' => 'INVALID',
        'amount' => -1000, // Invalid amount
        'currency' => 'INVALID'
    ];
    
    $result = $this->service->processOutgoingTransfer($invalidData);
    
    $this->assertFalse($result['success']);
    $this->assertEquals(400, $result['status_code']);
    $this->assertNotEmpty($result['error_message']);
}
```

### GEPG Gateway Service Tests

#### Test Case: GEPG_001 - Bill Query
**Objective**: Verify bill inquiry functionality
**Priority**: High

```php
public function testBillQuery()
{
    $controlNumber = 'GEPG' . time();
    
    $result = $this->service->queryBill($controlNumber);
    
    $this->assertEquals('0000', $result['BillStsCode']);
    $this->assertNotEmpty($result['control_number']);
    $this->assertLessThan(10000, $result['response_time']); // 10 seconds max
}
```

#### Test Case: GEPG_002 - Bill Payment Processing
**Objective**: Verify bill payment functionality
**Priority**: High

```php
public function testBillPayment()
{
    $paymentData = [
        'control_number' => 'GEPG' . time(),
        'amount' => 50000.00,
        'currency' => 'TZS',
        'payer_name' => 'Test User',
        'payer_phone' => '255712345678'
    ];
    
    $result = $this->service->processBillPayment($paymentData);
    
    $this->assertEquals('0000', $result['ResultCode']);
    $this->assertNotEmpty($result['payment_reference']);
    $this->assertLessThan(15000, $result['response_time']); // 15 seconds max
}
```

#### Test Case: GEPG_003 - XML Signing
**Objective**: Verify XML signing and verification
**Priority**: Medium

```php
public function testXMLSigning()
{
    $xmlData = '<BillQuery><ControlNumber>GEPG123</ControlNumber></BillQuery>';
    
    $signedXml = $this->service->signXML($xmlData);
    $isValid = $this->service->verifyXMLSignature($signedXml);
    
    $this->assertContains('Signature', $signedXml);
    $this->assertTrue($isValid);
}
```

### Luku Gateway Service Tests

#### Test Case: LUKU_001 - Meter Lookup
**Objective**: Verify meter number validation and lookup
**Priority**: High

```php
public function testMeterLookup()
{
    $meterNumber = '1234567890123456';
    
    $result = $this->service->lookupMeter($meterNumber);
    
    $this->assertTrue($result['success']);
    $this->assertNotEmpty($result['meter_details']);
    $this->assertLessThan(8000, $result['response_time']); // 8 seconds max
}
```

#### Test Case: LUKU_002 - Token Purchase
**Objective**: Verify electricity token purchase
**Priority**: High

```php
public function testTokenPurchase()
{
    $purchaseData = [
        'meter_number' => '1234567890123456',
        'amount' => 10000.00,
        'units' => 10,
        'customer_name' => 'Test Customer',
        'customer_phone' => '255712345678'
    ];
    
    $result = $this->service->purchaseToken($purchaseData);
    
    $this->assertTrue($result['success']);
    $this->assertNotEmpty($result['token']);
    $this->assertNotEmpty($result['units']);
    $this->assertLessThan(20000, $result['response_time']); // 20 seconds max
}
```

#### Test Case: LUKU_003 - SSL Certificate Validation
**Objective**: Verify SSL certificate handling
**Priority**: Medium

```php
public function testSSLCertificate()
{
    $result = $this->service->validateSSLCertificate();
    
    $this->assertTrue($result['valid']);
    $this->assertNotEmpty($result['certificate_info']);
}
```

---

## 2. Communication Service Test Cases

### NBC SMS Service Tests

#### Test Case: SMS_001 - Single SMS Sending
**Objective**: Verify single SMS delivery
**Priority**: High

```php
public function testSingleSMS()
{
    $smsData = [
        'phone_number' => '255712345678',
        'message' => 'Test SMS message from SACCOS',
        'sender_id' => 'SACCOS'
    ];
    
    $result = $this->service->sendSMS($smsData);
    
    $this->assertTrue($result['success']);
    $this->assertNotEmpty($result['message_id']);
    $this->assertLessThan(5000, $result['response_time']); // 5 seconds max
}
```

#### Test Case: SMS_002 - Bulk SMS Sending
**Objective**: Verify bulk SMS delivery
**Priority**: Medium

```php
public function testBulkSMS()
{
    $bulkData = [
        'phone_numbers' => ['255712345678', '255798765432', '255711223344'],
        'message' => 'Bulk test message from SACCOS',
        'sender_id' => 'SACCOS'
    ];
    
    $result = $this->service->sendBulkSMS($bulkData);
    
    $this->assertTrue($result['success']);
    $this->assertNotEmpty($result['batch_id']);
    $this->assertLessThan(10000, $result['response_time']); // 10 seconds max
}
```

#### Test Case: SMS_003 - SMS Delivery Status
**Objective**: Verify SMS delivery status checking
**Priority**: Medium

```php
public function testSMSDeliveryStatus()
{
    $messageId = 'MSG' . time();
    
    $result = $this->service->checkDeliveryStatus($messageId);
    
    $this->assertContains($result['delivery_status'], ['DELIVERED', 'PENDING', 'FAILED']);
    $this->assertLessThan(3000, $result['response_time']); // 3 seconds max
}
```

#### Test Case: SMS_004 - Rate Limiting
**Objective**: Verify rate limiting enforcement
**Priority**: Low

```php
public function testRateLimiting()
{
    $rateLimitExceeded = false;
    
    for ($i = 0; $i < 10; $i++) {
        $result = $this->service->sendSMS($this->getTestSMSData());
        
        if ($result['status_code'] === 429) {
            $rateLimitExceeded = true;
            break;
        }
        
        usleep(100000); // 0.1 second delay
    }
    
    $this->assertTrue($rateLimitExceeded, 'Rate limiting should be enforced');
}
```

#### Test Case: SMS_005 - Invalid Phone Number
**Objective**: Verify handling of invalid phone numbers
**Priority**: Medium

```php
public function testInvalidPhoneNumber()
{
    $smsData = [
        'phone_number' => 'INVALID_PHONE',
        'message' => 'Test message',
        'sender_id' => 'SACCOS'
    ];
    
    $result = $this->service->sendSMS($smsData);
    
    $this->assertFalse($result['success']);
    $this->assertEquals(400, $result['status_code']);
    $this->assertContains('phone', strtolower($result['error_message']));
}
```

---

## 3. AI & Machine Learning Service Test Cases

### AI Provider Service Tests

#### Test Case: AI_001 - Groq API Integration
**Objective**: Verify Groq API functionality
**Priority**: High

```php
public function testGroqAPI()
{
    $data = [
        'model' => 'meta-llama/llama-4-scout-17b-16e-instruct',
        'messages' => [
            ['role' => 'user', 'content' => 'Hello, how are you?']
        ],
        'max_tokens' => 100
    ];
    
    $result = $this->service->callGroqAPI($data);
    
    $this->assertTrue($result['success']);
    $this->assertNotEmpty($result['choices']);
    $this->assertLessThan(30000, $result['response_time']); // 30 seconds max
}
```

#### Test Case: AI_002 - OpenAI API Integration
**Objective**: Verify OpenAI API functionality
**Priority**: High

```php
public function testOpenAIAPI()
{
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'user', 'content' => 'Explain loan eligibility criteria']
        ],
        'max_tokens' => 200
    ];
    
    $result = $this->service->callOpenAIAPI($data);
    
    $this->assertTrue($result['success']);
    $this->assertNotEmpty($result['choices']);
    $this->assertLessThan(60000, $result['response_time']); // 60 seconds max
}
```

#### Test Case: AI_003 - Together AI Integration
**Objective**: Verify Together AI API functionality
**Priority**: Medium

```php
public function testTogetherAI()
{
    $data = [
        'model' => 'meta-llama/Llama-2-70b-chat-hf',
        'messages' => [
            ['role' => 'user', 'content' => 'What is a SACCOS?']
        ],
        'max_tokens' => 150
    ];
    
    $result = $this->service->callTogetherAI($data);
    
    $this->assertTrue($result['success']);
    $this->assertNotEmpty($result['choices']);
    $this->assertLessThan(45000, $result['response_time']); // 45 seconds max
}
```

#### Test Case: AI_004 - Model Availability
**Objective**: Verify model availability checking
**Priority**: Medium

```php
public function testModelAvailability()
{
    $models = $this->service->getAvailableModels();
    
    $this->assertContains('gpt-3.5-turbo', $models);
    $this->assertContains('meta-llama/llama-4-scout-17b-16e-instruct', $models);
    $this->assertContains('meta-llama/Llama-2-70b-chat-hf', $models);
}
```

#### Test Case: AI_005 - Error Handling
**Objective**: Verify error handling for AI service failures
**Priority**: Medium

```php
public function testAIErrorHandling()
{
    $invalidData = [
        'model' => 'invalid-model',
        'messages' => []
    ];
    
    $result = $this->service->callGroqAPI($invalidData);
    
    $this->assertFalse($result['success']);
    $this->assertNotEmpty($result['error_message']);
}
```

---

## 4. Internal Banking Service Test Cases

### Bank Transaction Service Tests

#### Test Case: BANK_001 - IFT Transaction Processing
**Objective**: Verify Intra-Financial Transaction processing
**Priority**: High

```php
public function testIFTTransaction()
{
    $iftData = [
        'account_from' => 'TEST001',
        'account_to' => 'TEST002',
        'amount' => 50000.00,
        'description' => 'IFT test transaction',
        'reference' => 'IFT' . time()
    ];
    
    $result = $this->service->processIFT($iftData);
    
    $this->assertTrue($result['success']);
    $this->assertNotEmpty($result['transaction_id']);
    $this->assertLessThan(5000, $result['response_time']); // 5 seconds max
}
```

#### Test Case: BANK_002 - EFT Transaction Processing
**Objective**: Verify Electronic Funds Transfer processing
**Priority**: High

```php
public function testEFTTransaction()
{
    $eftData = [
        'account_from' => 'TEST001',
        'bank_code' => 'NBC',
        'account_to' => '1234567890',
        'amount' => 100000.00,
        'description' => 'EFT test transaction',
        'reference' => 'EFT' . time()
    ];
    
    $result = $this->service->processEFT($eftData);
    
    $this->assertTrue($result['success']);
    $this->assertNotEmpty($result['transaction_id']);
    $this->assertLessThan(10000, $result['response_time']); // 10 seconds max
}
```

#### Test Case: BANK_003 - Mobile Money Transaction
**Objective**: Verify mobile money transaction processing
**Priority**: Medium

```php
public function testMobileTransaction()
{
    $mobileData = [
        'account_from' => 'TEST001',
        'mobile_network' => 'MPESA',
        'mobile_number' => '255712345678',
        'amount' => 25000.00,
        'description' => 'Mobile money test',
        'reference' => 'MOB' . time()
    ];
    
    $result = $this->service->processMobileTransaction($mobileData);
    
    $this->assertTrue($result['success']);
    $this->assertNotEmpty($result['transaction_id']);
    $this->assertLessThan(8000, $result['response_time']); // 8 seconds max
}
```

#### Test Case: BANK_004 - Transaction Status Check
**Objective**: Verify transaction status checking
**Priority**: Medium

```php
public function testTransactionStatus()
{
    $transactionId = 'TXN' . time();
    
    $result = $this->service->checkTransactionStatus($transactionId);
    
    $this->assertContains($result['status'], ['PENDING', 'SUCCESS', 'FAILED']);
    $this->assertLessThan(3000, $result['response_time']); // 3 seconds max
}
```

#### Test Case: BANK_005 - Invalid Transaction Type
**Objective**: Verify handling of invalid transaction types
**Priority**: Low

```php
public function testInvalidTransactionType()
{
    $invalidData = [
        'transaction_type' => 'INVALID_TYPE',
        'account_from' => 'TEST001',
        'amount' => 10000.00
    ];
    
    $result = $this->service->processTransaction($invalidData);
    
    $this->assertFalse($result['success']);
    $this->assertEquals(400, $result['status_code']);
    $this->assertContains('transaction type', strtolower($result['error_message']));
}
```

---

## 5. Security Test Cases

### Authentication Tests

#### Test Case: SEC_001 - API Key Authentication
**Objective**: Verify API key validation
**Priority**: High

```php
public function testAPIKeyAuthentication()
{
    // Test valid API key
    $validKey = config('testing.api_key');
    $result = $this->service->authenticate($validKey);
    $this->assertTrue($result['authenticated']);
    
    // Test invalid API key
    $invalidKey = 'invalid_key';
    $result = $this->service->authenticate($invalidKey);
    $this->assertFalse($result['authenticated']);
    $this->assertEquals(401, $result['status_code']);
}
```

#### Test Case: SEC_002 - IP Whitelisting
**Objective**: Verify IP address restrictions
**Priority**: High

```php
public function testIPWhitelisting()
{
    // Test whitelisted IP
    $whitelistedIP = '192.168.1.100';
    $result = $this->service->checkIPAccess($whitelistedIP);
    $this->assertTrue($result['allowed']);
    
    // Test non-whitelisted IP
    $nonWhitelistedIP = '192.168.1.200';
    $result = $this->service->checkIPAccess($nonWhitelistedIP);
    $this->assertFalse($result['allowed']);
    $this->assertEquals(403, $result['status_code']);
}
```

#### Test Case: SEC_003 - Digital Signature Validation
**Objective**: Verify digital signature validation
**Priority**: High

```php
public function testDigitalSignatureValidation()
{
    $payload = ['test' => 'data'];
    $signature = $this->service->generateSignature($payload);
    
    // Test valid signature
    $result = $this->service->verifySignature($payload, $signature);
    $this->assertTrue($result);
    
    // Test tampered payload
    $tamperedPayload = ['test' => 'tampered_data'];
    $result = $this->service->verifySignature($tamperedPayload, $signature);
    $this->assertFalse($result);
}
```

---

## 6. Performance Test Cases

### Load Testing

#### Test Case: PERF_001 - Concurrent Requests
**Objective**: Verify system behavior under concurrent load
**Priority**: Medium

```php
public function testConcurrentRequests()
{
    $concurrentRequests = 10;
    $results = [];
    
    // Simulate concurrent API calls
    for ($i = 0; $i < $concurrentRequests; $i++) {
        $startTime = microtime(true);
        $result = $this->service->processTransaction($this->getTestData());
        $endTime = microtime(true);
        
        $results[] = [
            'request_id' => $i,
            'response_time' => ($endTime - $startTime) * 1000,
            'success' => $result['success']
        ];
    }
    
    // Calculate performance metrics
    $avgResponseTime = array_sum(array_column($results, 'response_time')) / count($results);
    $successRate = count(array_filter($results, fn($r) => $r['success'])) / count($results);
    
    $this->assertLessThan(5000, $avgResponseTime); // 5 seconds average
    $this->assertGreaterThan(0.95, $successRate); // 95% success rate
}
```

#### Test Case: PERF_002 - Throughput Testing
**Objective**: Verify system throughput under sustained load
**Priority**: Medium

```php
public function testThroughput()
{
    $duration = 60; // 1 minute
    $startTime = time();
    $requestCount = 0;
    
    while (time() - $startTime < $duration) {
        $this->service->processTransaction($this->getTestData());
        $requestCount++;
    }
    
    $throughput = $requestCount / $duration; // requests per second
    $this->assertGreaterThan(10, $throughput); // Minimum 10 RPS
}
```

---

## Test Execution Summary

### Test Categories Summary
| Category | Total Tests | Critical | High | Medium | Low |
|----------|-------------|----------|------|--------|-----|
| Payment Gateway | 12 | 8 | 3 | 1 | 0 |
| Communication | 5 | 2 | 2 | 1 | 0 |
| AI Services | 5 | 2 | 2 | 1 | 0 |
| Banking Services | 5 | 3 | 1 | 1 | 0 |
| Security | 3 | 3 | 0 | 0 | 0 |
| Performance | 2 | 0 | 0 | 2 | 0 |
| **Total** | **32** | **18** | **8** | **6** | **0** |

### Test Execution Priority
1. **Critical Tests** (18): Must pass for system to be considered functional
2. **High Priority Tests** (8): Important for production readiness
3. **Medium Priority Tests** (6): Good to have for comprehensive testing
4. **Low Priority Tests** (0): Optional tests for edge cases

### Test Execution Commands
```bash
# Run all critical tests
php sit-tests/run-all-tests.php --priority=critical

# Run all high priority tests
php sit-tests/run-all-tests.php --priority=high

# Run specific category
php sit-tests/run-all-tests.php --category=payment

# Run with detailed logging
php sit-tests/run-all-tests.php --verbose --log-level=debug
```

---

*Document Version: 1.0*
*Last Updated: January 2025*
*System: SACCOS Core System*
*Author: Development Team*
