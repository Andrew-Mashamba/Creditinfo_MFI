# API Integrations Inventory - SACCOS Core System

## Overview
This document provides a comprehensive inventory of all API integrations in the SACCOS Core System, including both outgoing requests to external systems and incoming requests from external systems.

---

## OUTGOING API REQUESTS
*These are API calls made from our system to external services*

### 1. Payment Gateways & Financial Services

#### NBC Payment Service
- **Location**: `app/Services/NbcPayments/NbcPaymentService.php`
- **Base URL**: Configured in `services.nbc_payments.base_url`
- **Endpoints**:
  - `POST /domestix/api/v2/outgoing-transfers` - Process outgoing payments and transfers
- **Authentication**: API Key, Client ID, Digital Signature
- **Features**:
  - Outgoing payment processing
  - Digital signature generation
  - SSL verification (can be disabled)
  - Callback URL configuration

#### GEPG Gateway Service
- **Location**: `app/Services/NbcPayments/GepgGatewayService.php`
- **Base URL**: Configured in `gepg.gateway_url`
- **Endpoints**:
  - `POST /api/nbc-sg/v2/billquery` - Bill verification and inquiry
  - `POST /api/nbc-sg/v2/bill-pay` - Process bill payments
  - `POST /api/nbc-sg/v2/status-check` - Check payment status
- **Authentication**: Channel ID, Channel Name, RSA Keys
- **Protocol**: XML/JSON
- **Features**:
  - Control number verification
  - Bill payment processing
  - Payment status tracking

#### Luku Gateway Service
- **Location**: `app/Services/LukuGatewayService.php`
- **Purpose**: Electricity token purchases and meter management
- **Authentication**: API Token (Base64 encoded)
- **Protocol**: XML
- **Security**:
  - SSL certificate validation
  - Public/Private key authentication
  - CA certificate verification
- **Features**:
  - Meter lookup
  - Token purchase
  - Payment processing

#### NBC SMS Service
- **Location**: `app/Services/SmsService.php`
- **API Version**: NBC SMS Notification Engine API v2.0.0
- **Base URL**: Configured in `services.nbc_sms.base_url`
- **Features**:
  - SMS notification sending
  - Phone number validation
  - Rate limiting (100 requests per hour by default)
  - Retry logic (max 3 retries)
  - Notification logging

### 2. AI & Machine Learning Services

#### AI Provider Service
- **Location**: `app/Services/AiProviderService.php`
- **Integrated Providers**:

##### Groq API
- **URL**: `https://api.groq.com/openai/v1/chat/completions`
- **Models**:
  - meta-llama/llama-4-scout-17b-16e-instruct (default)
  - llama3-8b-8192
  - llama3-70b-8192
  - mixtral-8x7b-32768
- **Rate Limit**: 1000 requests/minute
- **Timeout**: 30 seconds

##### OpenAI API
- **URL**: `https://api.openai.com/v1/chat/completions`
- **Models**:
  - gpt-3.5-turbo (default)
  - gpt-4
  - gpt-4-turbo
- **Rate Limit**: 3000 requests/minute
- **Timeout**: 60 seconds

##### Together AI
- **URL**: `https://api.together.xyz/v1/chat/completions`
- **Models**:
  - meta-llama/Llama-2-70b-chat-hf (default)
  - meta-llama/Llama-2-13b-chat-hf
  - microsoft/DialoGPT-medium
- **Rate Limit**: 500 requests/minute
- **Timeout**: 45 seconds

#### AI Agent Service
- **Location**: `app/Services/AiAgentService.php`
- **Purpose**: AI-powered customer service and analysis
- **Features**:
  - Natural language processing
  - Customer query handling
  - Automated responses

#### Credit Assessment Service
- **Location**: `app/Services/CreditAssessmentService.php`
- **Purpose**: Credit scoring and risk assessment
- **Features**:
  - Credit score calculation
  - Risk analysis
  - Loan eligibility assessment

### 3. Internal Banking Services

#### Bank Transaction Service
- **Location**: `app/Http/Services/BankTransactionService.php`
- **Purpose**: Internal bank transaction processing

#### Internal Fund Transfer Service
- **Location**: `app/Services/NbcPayments/InternalFundTransferService.php`
- **Purpose**: Process internal fund transfers between accounts

#### FSP Details Service
- **Location**: `app/Services/NbcPayments/FspDetailsService.php`
- **Purpose**: Financial Service Provider details management

#### NBC Lookup Service
- **Location**: `app/Services/NbcPayments/NbcLookupService.php`
- **Purpose**: NBC account and entity lookups

---

## INCOMING API REQUESTS
*These are API endpoints exposed by our system for external consumers*

### 1. Public API Endpoints

#### Test & Monitoring
- `POST /api/testApi` - Test API endpoint for connectivity checks
- `GET /api/test` - Simple test endpoint

#### Institution Services
- `POST /api/institution-product-info` - Get institution and product information
- `POST /api/bank_funds_transfer_request` - Process internal bank transfer requests

#### Loan Services
- `POST /api/loan-decision` - Process loan decision requests

### 2. Callback/Webhook Endpoints

#### Payment Callbacks
- `POST /api/luku/callback` - Luku payment callback handler
- `POST /api/v1/nbc-payments/callback` - NBC payment callback handler
- `POST /api/luku-gateway/callback` - Luku gateway payment callback
- `POST /api/gepg-callback` - GEPG payment callback handler

### 3. Billing API Services

#### Billing Operations
- `POST /api/billing/inquiry` - Bill inquiry service
- `POST /api/billing/payment-notify` - Payment notification receiver
- `POST /api/billing/status-check` - Payment status verification

### 4. Luku Gateway API

#### Meter Management
- `POST /api/luku-gateway/meter/lookup` - Lookup meter information
- `POST /api/luku-gateway/payment` - Process Luku payments
- `POST /api/luku-gateway/token/status` - Check token status

### 5. Account Services API (v1)

#### Account Operations
- `POST /api/v1/account-details` - Retrieve account details
- `GET /api/v1/account-details/test` - Test API connectivity
- `GET /api/v1/account-details/stats` - Get account statistics
- `POST /api/accounts/setup` - Setup new accounts

### 6. Secured Transaction Processing API
*Requires API key, IP whitelisting, and security headers*

#### Transaction Management
- `POST /api/secure/transactions/process` - Process secure transactions
- `GET /api/secure/transactions/{reference}/status` - Get transaction status
- `GET /api/secure/transactions` - Retrieve transaction history

### 7. Loan Disbursement API (v1)
*Requires API key, IP whitelisting, and security headers*

#### Disbursement Operations
- `POST /api/v1/loans/auto-disburse` - Automatic loan creation and disbursement (requires only client_number and amount)
- `POST /api/v1/loans/disburse` - Single loan disbursement
- `POST /api/v1/loans/bulk-disburse` - Bulk loan disbursement
- `GET /api/v1/loans/disbursement/{transactionId}/status` - Check disbursement status

### 8. AI Agent API
*Requires Sanctum authentication*

#### AI Services
- `POST /api/ai-agent/ask` - Submit queries to AI agent

### 9. Admin API
*Requires Sanctum authentication*

#### API Key Management
- Full CRUD operations on `/api/admin/api-keys`
- `POST /api/admin/api-keys/{id}/regenerate` - Regenerate API key
- `GET /api/admin/api-keys/{id}/stats` - Get API key usage statistics

---

## Security Measures

### Authentication Methods
1. **API Key Authentication** (`api.key` middleware)
   - Used for secure transaction processing
   - Required for loan disbursement endpoints

2. **IP Whitelisting** (`ip.whitelist` middleware)
   - Restricts access to specific IP addresses
   - Applied to sensitive endpoints

3. **Security Headers** (`security.headers` middleware)
   - Adds security headers to responses
   - Prevents common web vulnerabilities

4. **Sanctum Authentication**
   - Used for admin and AI agent endpoints
   - Session-based authentication

5. **Digital Signatures**
   - Used in NBC Payment Service
   - RSA key-based signatures for request validation

### SSL/TLS Configuration
- SSL certificate validation for external connections
- Optional SSL verification for development environments
- Certificate-based authentication for Luku Gateway

---

## Controllers Handling External Requests

### Payment Controllers
- `BillingController` - Handles billing inquiries and payment processing
- `LukuGatewayController` - Manages Luku electricity services
- `LukuCallbackController` - Processes Luku payment callbacks
- `GepgCallbackController` - Handles GEPG payment callbacks
- `NbcPaymentCallbackController` - NBC payment callback processing

### Transaction Controllers
- `TransactionProcessingController` - Secure transaction processing
- `AccountDetailsController` - Account information services
- `AccountSetupController` - Account creation and setup

### Loan Controllers
- `LoanDisbursementController` - Loan disbursement services
- `LoanDecisionController` - Loan decision processing

### AI Controllers
- `AiAgentController` - AI agent interactions and queries

### Administrative Controllers
- `ApiKeyController` - API key management
- `InstitutionInformationApi` - Institution information services

---

## Frontend API Calls

### JavaScript/Axios Implementations
- Located in `resources/js/` directory
- Dashboard components making API calls:
  - `dashboard-card-01.js` through `dashboard-card-11.js`
- Bootstrap configuration in `resources/js/bootstrap.js`

### Blade Template Integrations
- NBC payment interface: `resources/views/nbc/payment.blade.php`
- AI agent interfaces:
  - `resources/views/ai-agent/dashboard.blade.php`
  - `resources/views/ai-agent/conversation.blade.php`
  - `resources/views/ai-agent/chat.blade.php`

---

## Testing Infrastructure

### System Integration Tests (SIT)
- `sit-tests/incoming-api-tests/IncomingApiTestBase.php` - Base test class
- `sit-tests/BankTransactionServiceTest.php` - Bank transaction tests
- `sit-tests/AIServicesTest.php` - AI service integration tests
- `sit-tests/NBCSMSTest.php` - SMS service tests
- `sit-tests/LukuGatewayTest.php` - Luku gateway tests
- `sit-tests/GEPGGatewayTest.php` - GEPG gateway tests

### Feature Tests
- `tests/Feature/ExternalAccountsBalanceTest.php`
- `tests/Feature/AccountDetailsApiTest.php`

---

## Configuration Files

### Service Configurations
- NBC Payments: `config/services.php` → `nbc_payments`
- Luku Gateway: `config/services.php` → `luku_gateway`
- NBC SMS: `config/services.php` → `nbc_sms`
- GEPG: `config/gepg.php`
- AI Providers: Environment variables for API keys

### Routing Configuration
- Main API routes: `routes/api.php`
- Web routes with API endpoints: `routes/web.php`
- Payment link routes: `routes/api_payment_links.php`
- Monitoring routes: `routes/api-monitor.php`

---

## Jobs & Background Processing

### Asynchronous Processing
- `app/Jobs/FundsTransfer.php` - Background fund transfer processing
- `app/Jobs/ProcessCallback.php` - Callback processing queue
- `app/Jobs/StandingOrder/SendNewStandingOrder.php` - Standing order processing

---

## Monitoring & Logging

### Dedicated Log Channels
- Luku Gateway: `luku` channel
- GEPG Gateway: Custom logger service
- NBC Payments: Transaction logging
- SMS Service: Notification logs with process IDs

### Health Checks
- AI Provider health checks (5-minute intervals)
- API connectivity test endpoints
- Status check endpoints for payment gateways

---

## Rate Limiting

### Service-Specific Limits
- NBC SMS: 100 requests per hour
- Groq AI: 1000 requests per minute
- OpenAI: 3000 requests per minute
- Together AI: 500 requests per minute

---

## Notes & Recommendations

1. **Security Audit**: Regular security audits should be performed on all API endpoints
2. **API Documentation**: Consider implementing OpenAPI/Swagger documentation
3. **Rate Limiting**: Implement rate limiting on all public endpoints
4. **Monitoring**: Set up comprehensive API monitoring and alerting
5. **Versioning**: Maintain API versioning strategy (currently using v1 prefix)
6. **Deprecation**: Legacy endpoints should be properly deprecated and migrated

---

*Document Generated: August 2025*
*System: SACCOS Core System*
*Version: As per current codebase*