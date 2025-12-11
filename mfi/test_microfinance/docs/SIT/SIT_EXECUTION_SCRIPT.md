# SACCOS Core System - SIT Execution Script

## Overview
This document provides step-by-step instructions for executing System Integration Tests (SIT) for the SACCOS Core System.

---

## Prerequisites

### System Requirements
```bash
# Required software versions
PHP >= 8.1
Composer >= 2.0
Laravel >= 10.0
MySQL >= 8.0 or PostgreSQL >= 13
Redis >= 6.0
```

### Environment Setup
```bash
# 1. Clone the repository
git clone <repository-url>
cd SACCOS_CORE_SYSTEM

# 2. Install dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Configure database
php artisan migrate

# 6. Seed test data
php artisan db:seed --class=TestDataSeeder
```

### Environment Variables
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
NBC_PAYMENTS_DIGITAL_SIGNATURE_KEY=your_signature_key

GEPG_GATEWAY_URL=https://gepg-gateway.example.com
GEPG_CHANNEL_ID=your_channel_id
GEPG_CHANNEL_NAME=your_channel_name
GEPG_AUTHORIZATION=your_auth_token

LUKU_GATEWAY_BASE_URL=https://luku-gateway.example.com
LUKU_GATEWAY_CHANNEL_ID=your_channel_id
LUKU_GATEWAY_CHANNEL_NAME=your_channel_name
LUKU_GATEWAY_API_TOKEN=your_api_token

NBC_SMS_BASE_URL=https://sms-engine.tz.af.absa.local
NBC_SMS_API_KEY=your_sms_api_key
NBC_SMS_CHANNEL_ID=KRWT43976

# AI Services Configuration
GROQ_API_KEY=your_groq_key
OPENAI_API_KEY=your_openai_key
TOGETHER_API_KEY=your_together_key
CLAUDE_API_KEY=your_claude_key

# SSL Certificate Paths
LUKU_SSL_CERT_PATH=/path/to/luku/certificate.crt
LUKU_SSL_KEY_PATH=/path/to/luku/private.key
LUKU_CA_CERT_PATH=/path/to/ca/certificate.crt
```

---

## Test Execution Scripts

### 1. Complete SIT Test Suite

```bash
#!/bin/bash
# sit-tests/run-complete-sit.sh

echo "=========================================="
echo "SACCOS Core System - Complete SIT Suite"
echo "=========================================="
echo "Starting comprehensive system integration testing..."
echo ""

# Set environment
export TESTING=true
export SIT_MODE=true

# Create test directories
mkdir -p sit-tests/reports
mkdir -p sit-tests/logs

# Start timestamp
START_TIME=$(date +%s)
echo "Test execution started at: $(date)"
echo ""

# 1. Pre-flight checks
echo "1. Running pre-flight checks..."
php sit-tests/pre-flight-checks.php
if [ $? -ne 0 ]; then
    echo "❌ Pre-flight checks failed. Aborting tests."
    exit 1
fi
echo "✅ Pre-flight checks passed"
echo ""

# 2. Database setup
echo "2. Setting up test database..."
php sit-tests/setup-test-database.php
if [ $? -ne 0 ]; then
    echo "❌ Database setup failed. Aborting tests."
    exit 1
fi
echo "✅ Database setup completed"
echo ""

# 3. Run all test categories
echo "3. Running test categories..."

# Payment Gateway Tests
echo "   - Payment Gateway Tests..."
php sit-tests/run-all-tests.php --category=payment --output=json > sit-tests/reports/payment-tests.json
PAYMENT_RESULT=$?

# Communication Tests
echo "   - Communication Tests..."
php sit-tests/run-all-tests.php --category=communication --output=json > sit-tests/reports/communication-tests.json
COMMUNICATION_RESULT=$?

# AI Services Tests
echo "   - AI Services Tests..."
php sit-tests/run-all-tests.php --category=ai --output=json > sit-tests/reports/ai-tests.json
AI_RESULT=$?

# Banking Services Tests
echo "   - Banking Services Tests..."
php sit-tests/run-all-tests.php --category=banking --output=json > sit-tests/reports/banking-tests.json
BANKING_RESULT=$?

# Security Tests
echo "   - Security Tests..."
php sit-tests/run-all-tests.php --category=security --output=json > sit-tests/reports/security-tests.json
SECURITY_RESULT=$?

# Performance Tests
echo "   - Performance Tests..."
php sit-tests/run-all-tests.php --category=performance --output=json > sit-tests/reports/performance-tests.json
PERFORMANCE_RESULT=$?

echo "✅ All test categories completed"
echo ""

# 4. Generate comprehensive report
echo "4. Generating comprehensive report..."
php sit-tests/generate-comprehensive-report.php \
    --payment-report=sit-tests/reports/payment-tests.json \
    --communication-report=sit-tests/reports/communication-tests.json \
    --ai-report=sit-tests/reports/ai-tests.json \
    --banking-report=sit-tests/reports/banking-tests.json \
    --security-report=sit-tests/reports/security-tests.json \
    --performance-report=sit-tests/reports/performance-tests.json \
    --output=sit-tests/reports/comprehensive-report.html

echo "✅ Comprehensive report generated"
echo ""

# 5. Calculate overall results
echo "5. Calculating overall results..."
TOTAL_RESULT=$((PAYMENT_RESULT + COMMUNICATION_RESULT + AI_RESULT + BANKING_RESULT + SECURITY_RESULT + PERFORMANCE_RESULT))

# End timestamp
END_TIME=$(date +%s)
DURATION=$((END_TIME - START_TIME))

echo ""
echo "=========================================="
echo "SIT Test Execution Summary"
echo "=========================================="
echo "Duration: ${DURATION} seconds"
echo ""

if [ $TOTAL_RESULT -eq 0 ]; then
    echo "🎉 ALL TESTS PASSED!"
    echo "✅ Payment Gateway Tests: PASSED"
    echo "✅ Communication Tests: PASSED"
    echo "✅ AI Services Tests: PASSED"
    echo "✅ Banking Services Tests: PASSED"
    echo "✅ Security Tests: PASSED"
    echo "✅ Performance Tests: PASSED"
    echo ""
    echo "📊 Report available at: sit-tests/reports/comprehensive-report.html"
    exit 0
else
    echo "❌ SOME TESTS FAILED!"
    [ $PAYMENT_RESULT -ne 0 ] && echo "❌ Payment Gateway Tests: FAILED"
    [ $COMMUNICATION_RESULT -ne 0 ] && echo "❌ Communication Tests: FAILED"
    [ $AI_RESULT -ne 0 ] && echo "❌ AI Services Tests: FAILED"
    [ $BANKING_RESULT -ne 0 ] && echo "❌ Banking Services Tests: FAILED"
    [ $SECURITY_RESULT -ne 0 ] && echo "❌ Security Tests: FAILED"
    [ $PERFORMANCE_RESULT -ne 0 ] && echo "❌ Performance Tests: FAILED"
    echo ""
    echo "📊 Detailed report available at: sit-tests/reports/comprehensive-report.html"
    echo "📋 Check individual test logs in: sit-tests/logs/"
    exit 1
fi
```

### 2. Individual Test Category Scripts

#### Payment Gateway Tests
```bash
#!/bin/bash
# sit-tests/run-payment-tests.sh

echo "Running Payment Gateway SIT Tests..."
echo "====================================="

# NBC Payment Service Tests
echo "1. Testing NBC Payment Service..."
php sit-tests/BankTransactionServiceTest.php --service=nbc --verbose

# GEPG Gateway Tests
echo "2. Testing GEPG Gateway Service..."
php sit-tests/GEPGGatewayTest.php --verbose

# Luku Gateway Tests
echo "3. Testing Luku Gateway Service..."
php sit-tests/LukuGatewayTest.php --verbose

echo "Payment Gateway tests completed!"
```

#### Communication Tests
```bash
#!/bin/bash
# sit-tests/run-communication-tests.sh

echo "Running Communication SIT Tests..."
echo "=================================="

# NBC SMS Service Tests
echo "1. Testing NBC SMS Service..."
php sit-tests/NBCSMSTest.php --verbose

# Email Service Tests
echo "2. Testing Email Service..."
php sit-tests/EmailServiceTest.php --verbose

echo "Communication tests completed!"
```

#### AI Services Tests
```bash
#!/bin/bash
# sit-tests/run-ai-tests.sh

echo "Running AI Services SIT Tests..."
echo "================================"

# AI Provider Service Tests
echo "1. Testing AI Provider Service..."
php sit-tests/AIServicesTest.php --verbose

# AI Agent Service Tests
echo "2. Testing AI Agent Service..."
php sit-tests/AiAgentServiceTest.php --verbose

echo "AI Services tests completed!"
```

### 3. Quick Health Check Script

```bash
#!/bin/bash
# sit-tests/quick-health-check.sh

echo "SACCOS Core System - Quick Health Check"
echo "======================================="

# Check system connectivity
echo "1. Checking system connectivity..."
php sit-tests/check-connectivity.php

# Check API endpoints
echo "2. Checking API endpoints..."
php sit-tests/check-api-endpoints.php

# Check database connection
echo "3. Checking database connection..."
php sit-tests/check-database.php

# Check external services
echo "4. Checking external services..."
php sit-tests/check-external-services.php

echo "Quick health check completed!"
```

---

## Test Execution Commands

### Basic Commands
```bash
# Run complete SIT suite
./sit-tests/run-complete-sit.sh

# Run specific test category
./sit-tests/run-payment-tests.sh
./sit-tests/run-communication-tests.sh
./sit-tests/run-ai-tests.sh

# Quick health check
./sit-tests/quick-health-check.sh
```

### Advanced Commands
```bash
# Run with specific options
php sit-tests/run-all-tests.php --category=payment --priority=critical --verbose
php sit-tests/run-all-tests.php --service=nbc --timeout=30 --retries=3
php sit-tests/run-all-tests.php --parallel --max-workers=4

# Run with custom configuration
php sit-tests/run-all-tests.php --config=sit-tests/config/production.json
php sit-tests/run-all-tests.php --env=staging --log-level=debug

# Generate specific reports
php sit-tests/generate-report.php --type=performance --format=html
php sit-tests/generate-report.php --type=security --format=json
```

### CI/CD Integration
```bash
# For GitHub Actions
- name: Run SIT Tests
  run: |
    chmod +x sit-tests/run-complete-sit.sh
    ./sit-tests/run-complete-sit.sh

# For Jenkins
stage('SIT Tests') {
    steps {
        sh 'chmod +x sit-tests/run-complete-sit.sh'
        sh './sit-tests/run-complete-sit.sh'
    }
}
```

---

## Test Configuration Files

### Main Configuration
```json
{
  "sit_config": {
    "test_environment": "staging",
    "timeout": 30,
    "retries": 3,
    "parallel_execution": true,
    "max_workers": 4,
    "log_level": "info",
    "report_format": "html",
    "cleanup_after_tests": true
  },
  "services": {
    "nbc_payments": {
      "enabled": true,
      "timeout": 10,
      "retries": 2
    },
    "gepg_gateway": {
      "enabled": true,
      "timeout": 15,
      "retries": 2
    },
    "luku_gateway": {
      "enabled": true,
      "timeout": 20,
      "retries": 3
    },
    "nbc_sms": {
      "enabled": true,
      "timeout": 5,
      "retries": 1
    },
    "ai_services": {
      "enabled": true,
      "timeout": 60,
      "retries": 1
    }
  },
  "performance": {
    "concurrent_requests": 10,
    "test_duration": 60,
    "success_rate_threshold": 0.95,
    "response_time_threshold": 5000
  }
}
```

### Environment-Specific Configurations

#### Development Environment
```json
{
  "sit_config": {
    "test_environment": "development",
    "timeout": 60,
    "retries": 5,
    "parallel_execution": false,
    "log_level": "debug"
  }
}
```

#### Staging Environment
```json
{
  "sit_config": {
    "test_environment": "staging",
    "timeout": 30,
    "retries": 3,
    "parallel_execution": true,
    "max_workers": 2,
    "log_level": "info"
  }
}
```

#### Production Environment
```json
{
  "sit_config": {
    "test_environment": "production",
    "timeout": 15,
    "retries": 2,
    "parallel_execution": true,
    "max_workers": 4,
    "log_level": "warn"
  }
}
```

---

## Monitoring and Alerting

### Test Monitoring Script
```bash
#!/bin/bash
# sit-tests/monitor-tests.sh

# Monitor test execution
while true; do
    echo "Checking test status..."
    
    # Check if tests are running
    if pgrep -f "run-all-tests.php" > /dev/null; then
        echo "Tests are running..."
    else
        echo "No tests running"
    fi
    
    # Check test results
    if [ -f "sit-tests/reports/latest-results.json" ]; then
        FAILED_TESTS=$(jq '.failed_count' sit-tests/reports/latest-results.json)
        if [ "$FAILED_TESTS" -gt 0 ]; then
            echo "ALERT: $FAILED_TESTS tests failed!"
            # Send alert notification
            ./sit-tests/send-alert.sh "SIT Tests Failed: $FAILED_TESTS tests failed"
        fi
    fi
    
    sleep 300 # Check every 5 minutes
done
```

### Alert Configuration
```json
{
  "alerts": {
    "email": {
      "enabled": true,
      "recipients": ["dev-team@saccos.com", "qa-team@saccos.com"],
      "smtp_server": "smtp.saccos.com",
      "smtp_port": 587
    },
    "slack": {
      "enabled": true,
      "webhook_url": "https://hooks.slack.com/services/YOUR/WEBHOOK/URL",
      "channel": "#sit-alerts"
    },
    "sms": {
      "enabled": true,
      "recipients": ["255712345678", "255798765432"]
    }
  },
  "thresholds": {
    "critical_failures": 5,
    "performance_degradation": 0.8,
    "response_time_increase": 2.0
  }
}
```

---

## Troubleshooting

### Common Issues and Solutions

#### 1. SSL Certificate Issues
```bash
# Issue: SSL certificate validation failures
# Solution: Verify and update certificates
openssl x509 -in /path/to/certificate.crt -text -noout
php sit-tests/fix-ssl-certificates.php
```

#### 2. Database Connection Issues
```bash
# Issue: Database connection failures
# Solution: Check database configuration
php artisan migrate:status
php sit-tests/check-database-connection.php
```

#### 3. API Key Authentication Issues
```bash
# Issue: API key not working
# Solution: Verify API keys
php sit-tests/verify-api-keys.php
php artisan tinker --execute="echo config('services.nbc_payments.api_key');"
```

#### 4. Timeout Issues
```bash
# Issue: Request timeouts
# Solution: Increase timeout values
php sit-tests/run-all-tests.php --timeout=60 --retries=5
```

#### 5. Memory Issues
```bash
# Issue: Memory exhaustion
# Solution: Increase PHP memory limit
php -d memory_limit=2G sit-tests/run-all-tests.php
```

### Debug Commands
```bash
# Enable debug mode
export SIT_DEBUG=true
php sit-tests/run-all-tests.php --verbose --log-level=debug

# Check system resources
php sit-tests/check-system-resources.php

# Validate test data
php sit-tests/validate-test-data.php

# Test specific service
php sit-tests/test-specific-service.php --service=nbc --method=transfer
```

---

## Report Generation

### HTML Report Generation
```bash
# Generate comprehensive HTML report
php sit-tests/generate-report.php --format=html --output=sit-tests/reports/sit-report.html

# Generate category-specific reports
php sit-tests/generate-report.php --category=payment --format=html --output=sit-tests/reports/payment-report.html
php sit-tests/generate-report.php --category=security --format=html --output=sit-tests/reports/security-report.html
```

### JSON Report Generation
```bash
# Generate JSON report for API consumption
php sit-tests/generate-report.php --format=json --output=sit-tests/reports/sit-report.json

# Generate machine-readable test results
php sit-tests/generate-report.php --format=json --machine-readable --output=sit-tests/reports/machine-readable.json
```

### Email Report
```bash
# Send email report
php sit-tests/send-email-report.php --recipients="dev-team@saccos.com" --subject="SIT Test Results"
```

---

## Best Practices

### Test Execution
1. **Run tests in order of priority**: Critical → High → Medium → Low
2. **Use appropriate timeouts**: Different services have different response times
3. **Implement retry logic**: Handle transient failures gracefully
4. **Monitor system resources**: Ensure sufficient memory and CPU
5. **Clean up test data**: Remove test data after execution

### Configuration Management
1. **Use environment-specific configs**: Different settings for dev/staging/prod
2. **Secure sensitive data**: Never commit API keys to version control
3. **Version control configs**: Track configuration changes
4. **Document configuration**: Maintain clear documentation

### Monitoring and Alerting
1. **Set appropriate thresholds**: Balance between sensitivity and noise
2. **Use multiple notification channels**: Email, Slack, SMS
3. **Implement escalation procedures**: For critical failures
4. **Monitor trends**: Track performance over time

---

*Document Version: 1.0*
*Last Updated: January 2025*
*System: SACCOS Core System*
*Author: Development Team*
