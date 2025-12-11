# SACCOS Core System - System Integration Testing (SIT) Guide

## Table of Contents
1. [Overview](#overview)
2. [SIT Testing Strategy](#sit-testing-strategy)
3. [Test Environment Setup](#test-environment-setup)
4. [API Integration Test Categories](#api-integration-test-categories)
5. [Test Execution Framework](#test-execution-framework)
6. [Test Cases by Service](#test-cases-by-service)
7. [Performance Testing](#performance-testing)
8. [Security Testing](#security-testing)
9. [Error Handling & Recovery](#error-handling--recovery)
10. [Test Data Management](#test-data-management)
11. [Reporting & Monitoring](#reporting--monitoring)
12. [CI/CD Integration](#cicd-integration)
13. [Troubleshooting Guide](#troubleshooting-guide)

---

## Overview

### Purpose
This document provides comprehensive guidance for System Integration Testing (SIT) of the SACCOS Core System, ensuring all external API integrations function correctly, securely, and reliably in production environments.

### Scope
The SIT covers all external API integrations including:
- **Payment Gateways**: NBC Payments, GEPG Gateway, Luku Gateway
- **Financial Services**: Bank Transaction Services, Fund Transfers
- **Communication Services**: NBC SMS, Email Services
- **AI & ML Services**: Groq, OpenAI, Together AI, Claude
- **Internal APIs**: Account Services, Loan Services, Transaction Processing

### Objectives
- ✅ Validate all API integrations function correctly
- ✅ Ensure data integrity across system boundaries
- ✅ Verify security measures and authentication
- ✅ Test error handling and recovery mechanisms
- ✅ Validate performance under load
- ✅ Confirm business logic compliance

---

## SIT Testing Strategy

### Testing Approach
1. **Comprehensive Coverage**: Test all API endpoints and integration points
2. **Realistic Scenarios**: Use production-like data and conditions
3. **Automated Execution**: Minimize manual intervention
4. **Continuous Validation**: Regular testing in CI/CD pipeline
5. **Performance Monitoring**: Track response times and throughput

### Test Types
1. **Functional Tests**: Verify API functionality and business logic
2. **Integration Tests**: Test end-to-end workflows
3. **Performance Tests**: Validate response times and throughput
4. **Security Tests**: Verify authentication and authorization
5. **Error Handling Tests**: Test failure scenarios and recovery
6. **Load Tests**: Validate system behavior under stress

---

## Test Environment Setup

### Prerequisites
```bash
# Required software
- PHP 8.1+
- Composer
- Laravel Framework
- MySQL/PostgreSQL
- Redis (for caching)
- SSL certificates (for secure APIs)

# Required environment variables
- API keys for all external services
- Database credentials
- SSL certificate paths
- Test data sets
```

### Environment Configuration
```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saccos_test
DB_USERNAME=test_user
DB_PASSWORD=test_password

# External API Configuration
NBC_PAYMENTS_BASE_URL=https://api.nbc.co.tz
NBC_PAYMENTS_API_KEY=your_api_key
NBC_PAYMENTS_CLIENT_ID=your_client_id

GEPG_GATEWAY_URL=https://gepg-gateway.example.com
GEPG_CHANNEL_ID=your_channel_id
GEPG_CHANNEL_NAME=your_channel_name

LUKU_GATEWAY_BASE_URL=https://luku-gateway.example.com
LUKU_GATEWAY_API_TOKEN=your_api_token

NBC_SMS_BASE_URL=https://sms-engine.tz.af.absa.local
NBC_SMS_API_KEY=your_sms_api_key

# AI Services Configuration
GROQ_API_KEY=your_groq_key
OPENAI_API_KEY=your_openai_key
TOGETHER_API_KEY=your_together_key
CLAUDE_API_KEY=your_claude_key
```

---

## API Integration Test Categories

### 1. Payment Gateway Integrations

#### NBC Payment Service
**Service Location**: `app/Services/NbcPayments/NbcPaymentService.php`
**Base URL**: `services.nbc_payments.base_url`

**Test Scenarios**:
- ✅ Outgoing payment processing
- ✅ Digital signature generation
- ✅ SSL certificate validation
- ✅ Callback URL handling
- ✅ Error response handling
- ✅ Timeout scenarios

#### GEPG Gateway Service
**Service Location**: `app/Services/NbcPayments/GepgGatewayService.php`
**Base URL**: `gepg.gateway_url`

**Test Scenarios**:
- ✅ Bill verification and inquiry
- ✅ Bill payment processing
- ✅ Payment status checking
- ✅ XML signing and verification
- ✅ Control number validation

#### Luku Gateway Service
**Service Location**: `app/Services/LukuGatewayService.php`

**Test Scenarios**:
- ✅ Meter lookup and validation
- ✅ Token purchase processing
- ✅ Payment verification
- ✅ SSL certificate handling
- ✅ Rate limiting

### 2. Communication Services

#### NBC SMS Service
**Service Location**: `app/Services/SmsService.php`
**API Version**: NBC SMS Notification Engine API v2.0.0

**Test Scenarios**:
- ✅ Single SMS sending
- ✅ Bulk SMS operations
- ✅ Delivery status checking
- ✅ Rate limiting enforcement
- ✅ Invalid phone number handling

### 3. AI & Machine Learning Services

#### AI Provider Service
**Service Location**: `app/Services/AiProviderService.php`

**Test Scenarios**:
- ✅ Groq API integration
- ✅ OpenAI API integration
- ✅ Together AI integration
- ✅ Model availability checking
- ✅ Response validation

### 4. Internal Banking Services

#### Bank Transaction Service
**Service Location**: `app/Http/Services/BankTransactionService.php`

**Test Scenarios**:
- ✅ IFT (Intra-Financial Transaction) processing
- ✅ EFT (Electronic Funds Transfer) processing
- ✅ Mobile money transactions
- ✅ Transaction status checking
- ✅ Error handling

---

## Test Execution Framework

### Test Runner Structure
```php
<?php
// sit-tests/run-all-tests.php
class SITTestRunner
{
    private $tests = [
        'BankTransactionServiceTest',
        'GEPGGatewayTest',
        'LukuGatewayTest',
        'NBCSMSTest',
        'AIServicesTest',
        'PaymentCallbackTest',
        'SecurityTest',
        'PerformanceTest'
    ];
    
    public function runAllTests()
    {
        $results = [];
        foreach ($this->tests as $testClass) {
            $test = new $testClass();
            $results[$testClass] = $test->runAllTests();
        }
        return $this->generateReport($results);
    }
}
```

### Test Execution Commands
```bash
# Run all SIT tests
php sit-tests/run-all-tests.php

# Run specific test category
php sit-tests/run-all-tests.php --category=payment

# Run with detailed logging
php sit-tests/run-all-tests.php --verbose --log-level=debug

# Run performance tests only
php sit-tests/run-all-tests.php --type=performance

# Run security tests only
php sit-tests/run-all-tests.php --type=security
```

---

## Performance Testing

### Load Testing Scenarios
```php
class PerformanceTest
{
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
}
```

### Performance Benchmarks
| Service | Max Response Time | Target Throughput | Success Rate |
|---------|------------------|-------------------|--------------|
| NBC Payments | 5 seconds | 50 RPS | 99.5% |
| GEPG Gateway | 10 seconds | 30 RPS | 99.0% |
| Luku Gateway | 8 seconds | 20 RPS | 98.5% |
| NBC SMS | 5 seconds | 100 RPS | 99.0% |
| AI Services | 30 seconds | 10 RPS | 95.0% |

---

## Security Testing

### Authentication Tests
```php
class SecurityTest
{
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
}
```

### Security Test Cases
1. **API Key Validation**: Verify API keys are properly validated
2. **IP Whitelisting**: Test IP address restrictions
3. **Digital Signatures**: Validate request/response signatures
4. **SSL/TLS**: Verify secure connections
5. **Rate Limiting**: Test rate limiting mechanisms
6. **Input Validation**: Test for SQL injection and XSS
7. **Session Management**: Verify session security

---

## Error Handling & Recovery

### Error Scenarios
```php
class ErrorHandlingTest
{
    public function testConnectionTimeout()
    {
        // Simulate connection timeout
        $this->service->setTimeout(1); // 1 second timeout
        $result = $this->service->processTransaction($this->getTestData());
        
        $this->assertFalse($result['success']);
        $this->assertEquals('timeout', $result['error_type']);
        $this->assertNotEmpty($result['error_message']);
    }
    
    public function testRetryMechanism()
    {
        // Test automatic retry on failure
        $attempts = 0;
        $maxRetries = 3;
        
        do {
            $attempts++;
            $result = $this->service->processTransaction($this->getTestData());
        } while (!$result['success'] && $attempts < $maxRetries);
        
        $this->assertLessThanOrEqual($maxRetries, $attempts);
    }
}
```

### Recovery Mechanisms
1. **Automatic Retries**: Retry failed requests with exponential backoff
2. **Circuit Breaker**: Prevent cascading failures
3. **Fallback Mechanisms**: Use alternative services when primary fails
4. **Graceful Degradation**: Continue operation with reduced functionality
5. **Error Logging**: Comprehensive error tracking and alerting

---

## Test Data Management

### Test Data Requirements
```php
class TestDataManager
{
    private $testAccounts = [
        'TEST001' => ['balance' => 1000000.00, 'status' => 'ACTIVE'],
        'TEST002' => ['balance' => 500000.00, 'status' => 'ACTIVE'],
        'TEST003' => ['balance' => 750000.00, 'status' => 'ACTIVE']
    ];
    
    private $testClients = [
        'CLI001' => ['name' => 'John Doe', 'phone' => '255712345678'],
        'CLI002' => ['name' => 'Jane Smith', 'phone' => '255798765432'],
        'CLI003' => ['name' => 'Bob Johnson', 'phone' => '255711223344']
    ];
    
    public function setupTestData()
    {
        // Create test accounts
        foreach ($this->testAccounts as $accountNumber => $data) {
            $this->createTestAccount($accountNumber, $data);
        }
        
        // Create test clients
        foreach ($this->testClients as $clientNumber => $data) {
            $this->createTestClient($clientNumber, $data);
        }
    }
    
    public function cleanupTestData()
    {
        // Remove test data after tests
        $this->removeTestAccounts();
        $this->removeTestClients();
    }
}
```

### Data Isolation
1. **Separate Test Database**: Use dedicated test database
2. **Transaction Rollback**: Rollback changes after each test
3. **Mock External Services**: Use mocks for external dependencies
4. **Test Data Cleanup**: Clean up test data after tests

---

## Reporting & Monitoring

### Test Report Structure
```php
class TestReport
{
    public function generateReport($testResults)
    {
        $report = [
            'summary' => [
                'total_tests' => count($testResults),
                'passed' => count(array_filter($testResults, fn($r) => $r['status'] === 'PASSED')),
                'failed' => count(array_filter($testResults, fn($r) => $r['status'] === 'FAILED')),
                'errors' => count(array_filter($testResults, fn($r) => $r['status'] === 'ERROR')),
                'execution_time' => $this->calculateExecutionTime($testResults)
            ],
            'performance_metrics' => [
                'average_response_time' => $this->calculateAverageResponseTime($testResults),
                'throughput' => $this->calculateThroughput($testResults),
                'success_rate' => $this->calculateSuccessRate($testResults)
            ],
            'detailed_results' => $testResults,
            'recommendations' => $this->generateRecommendations($testResults)
        ];
        
        return $report;
    }
}
```

### Monitoring Dashboard
```php
class MonitoringDashboard
{
    public function getSystemHealth()
    {
        return [
            'api_status' => $this->checkAPIStatus(),
            'database_status' => $this->checkDatabaseStatus(),
            'queue_status' => $this->checkQueueStatus(),
            'performance_metrics' => $this->getPerformanceMetrics(),
            'error_rates' => $this->getErrorRates()
        ];
    }
    
    public function getAlertThresholds()
    {
        return [
            'response_time_threshold' => 5000, // 5 seconds
            'error_rate_threshold' => 0.05, // 5%
            'throughput_threshold' => 10 // 10 RPS
        ];
    }
}
```

---

## CI/CD Integration

### GitHub Actions Workflow
```yaml
name: SIT Tests
on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  sit-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          
      - name: Install dependencies
        run: composer install
        
      - name: Setup test environment
        run: |
          cp .env.example .env.test
          php artisan key:generate --env=test
          
      - name: Run SIT tests
        run: php sit-tests/run-all-tests.php
        
      - name: Generate test report
        run: php sit-tests/generate-report.php
        
      - name: Upload test results
        uses: actions/upload-artifact@v2
        with:
          name: sit-test-results
          path: sit-tests/reports/
```

---

## Troubleshooting Guide

### Common Issues & Solutions

#### 1. SSL Certificate Errors
```bash
# Issue: SSL certificate validation failures
# Solution: Verify certificate paths and validity
openssl x509 -in /path/to/certificate.crt -text -noout
```

#### 2. API Key Authentication Failures
```bash
# Issue: API key not working
# Solution: Check environment variables and key format
echo $NBC_PAYMENTS_API_KEY
php artisan tinker --execute="echo config('services.nbc_payments.api_key');"
```

#### 3. Database Connection Issues
```bash
# Issue: Database connection failures
# Solution: Verify database configuration
php artisan migrate:status
php artisan db:show
```

#### 4. Rate Limiting Issues
```bash
# Issue: Rate limit exceeded
# Solution: Implement proper rate limiting handling
# Add delays between requests or use bulk operations
```

#### 5. Timeout Issues
```bash
# Issue: Request timeouts
# Solution: Increase timeout values or optimize requests
# Check network connectivity and service availability
```

### Debug Commands
```bash
# Test API connectivity
php sit-tests/test-connectivity.php

# Check service status
php artisan service:status

# View logs
tail -f storage/logs/laravel.log

# Test specific service
php sit-tests/BankTransactionServiceTest.php --verbose
```

---

## Conclusion

This SIT Testing Guide provides a comprehensive framework for testing all API integrations in the SACCOS Core System. By following these guidelines, you can ensure:

- ✅ **Reliability**: All integrations work correctly and consistently
- ✅ **Security**: Proper authentication and authorization
- ✅ **Performance**: Systems meet performance requirements
- ✅ **Maintainability**: Easy to maintain and extend
- ✅ **Monitoring**: Comprehensive monitoring and alerting

### Next Steps
1. Implement the test framework
2. Set up CI/CD integration
3. Configure monitoring and alerting
4. Establish regular testing schedules
5. Document any system-specific requirements

### Maintenance
- Review and update test cases regularly
- Monitor test results and performance metrics
- Update documentation as system evolves
- Conduct regular security audits

---

*Document Version: 1.0*
*Last Updated: January 2025*
*System: SACCOS Core System*
*Author: Development Team*
