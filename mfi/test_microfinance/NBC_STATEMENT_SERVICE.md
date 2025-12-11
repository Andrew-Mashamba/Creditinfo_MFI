# NBC Statement Service - Implementation Documentation

**Date:** October 11, 2025
**Service Version:** v1.2
**API Provider:** NBC Development Team
**Status:** ✅ Implementation Complete - Awaiting NBC Credentials

---

## Overview

The NBC Statement Service integrates with NBC's **PVAS (Partners Values Added Services)** API to fetch account statements, balances, and transaction summaries. This service is used by the Daily Reconciliation Job to automatically pull NBC bank statements.

**API Features:**
- JWT/OAuth2 Bearer Authentication
- Digital Signature Verification (SHA256withRSA)
- Account Statement Retrieval (SC990003)
- Account Balance Retrieval (SC990001)
- Transaction Summary Retrieval (SC990002)

---

## Service Architecture

### Component Files

1. **`/app/Services/NBCStatementService.php`** - Main service class
2. **`/config/services.php`** - Configuration
3. **`.env`** - Environment variables (credentials)
4. **Storage Keys:**
   - `/storage/keys/nbc_statement_private.pem` - Our private key for signing
   - `/storage/keys/nbc_statement_public.pem` - NBC's public key for verification

---

## API Integration Details

### Base Endpoints

**UAT:** TBD (to be provided by NBC)
**PROD:** TBD (after UAT sign-off)

### Authentication Method

**Type:** Bearer Authentication (JWT/OAuth2)

**Login Endpoint:** `POST /api/auth/login`

**Request:**
```json
{
  "username": "<CHANNEL-ID>",
  "password": "<CHANNEL-SECRET>"
}
```

**Response:**
```json
{
  "token": "<JWT_TOKEN_REDACTED>",
  "expiry": 86400000
}
```

**Token Caching:**
- JWT token is cached in Laravel cache with key `nbc_statement_jwt_token`
- Auto-expires 5 minutes before actual token expiry
- Automatically refreshes on next request

---

## Available Service Methods

### 1. Fetch Account Statement

**Method:** `fetchAccountStatement(string $accountNumber, $statementDate, ?string $partnerRef = null): array`

**Purpose:** Fetch account transactions for a specific date

**NBC API Endpoint:** `POST /api/v1/casa/statement`

**Service Code:** `SC990003`

**Request Format:**
```json
{
  "timestamp": "2025-03-04T12:17:12.249Z",
  "serviceCode": "SC990003",
  "partnerRef": "CB2501201264890",
  "accountNumber": "012102000376",
  "statementDate": "2025-03-03"
}
```

**NBC Response Format:**
```json
{
  "statusCode": 600,
  "message": "Successful",
  "serviceCode": "SC990003",
  "partnerRef": "CB2501201264890",
  "bankRef": "PVS25030415565672233",
  "timestamp": "2025-03-04T12:56:58.122+00:00",
  "data": {
    "transactions": [
      {
        "transactionDate": "2025-02-10T00:00:00",
        "postingDate": "2025-02-10T00:00:00",
        "valueDate": "2023-01-02T00:00:00",
        "currency": "TZS",
        "amount": 910,
        "balance": 4170415.84,
        "reference": "99520102000100948495",
        "description": "SC|250210141523713748|20250210|102|NULL|",
        "debitCredit": "D",
        "debitAmount": 910,
        "creditAmount": 0
      }
    ]
  }
}
```

**Mapped Output:**
```php
[
    'transaction_date' => '2025-02-10',
    'value_date' => '2023-01-02',
    'reference_number' => '99520102000100948495',
    'narration' => 'SC|250210141523713748|20250210|102|NULL|',
    'withdrawal_amount' => 910,
    'deposit_amount' => 0,
    'balance' => 4170415.84,
    'branch' => null,
    'currency' => 'TZS',
    'raw_data' => [...] // Original NBC response
]
```

**Usage Example:**
```php
use App\Services\NBCStatementService;

$service = new NBCStatementService();

// Fetch yesterday's statement
$transactions = $service->fetchAccountStatement(
    '015103001490',
    now()->subDay()
);

foreach ($transactions as $txn) {
    echo "Reference: {$txn['reference']} - Amount: {$txn['amount']}\n";
}
```

---

### 2. Fetch Account Balance

**Method:** `fetchAccountBalance(string $accountNumber, $statementDate, ?string $partnerRef = null): array`

**Purpose:** Get account balance and transaction summary for a date

**NBC API Endpoint:** `POST /api/v1/casa/balance`

**Service Code:** `SC990001`

**Response Data:**
```json
{
  "currency": "TZS",
  "openingBalance": 252493350003.31,
  "closingBalance": 252491972089.07,
  "totalTransactionsCount": 116,
  "totalDebitAmount": 1451245.74,
  "totalDebitCount": 108,
  "totalCreditAmount": 73331.50,
  "totalCreditCount": 8
}
```

**Usage Example:**
```php
$balance = $service->fetchAccountBalance(
    '015103001490',
    '2025-03-03'
);

echo "Opening: {$balance['openingBalance']}\n";
echo "Closing: {$balance['closingBalance']}\n";
echo "Transactions: {$balance['totalTransactionsCount']}\n";
```

---

### 3. Test Connectivity

**Method:** `testConnectivity(): array`

**Purpose:** Test NBC API connectivity and authentication

**Returns:**
```php
[
    'timestamp' => '2025-10-11 12:00:00',
    'tests' => [
        'authentication' => [
            'status' => 'success',
            'time_ms' => 345.67,
            'token_length' => 187
        ],
        'signature_generation' => [
            'status' => 'success',
            'signature_length' => 344
        ]
    ],
    'overall_status' => 'success'
]
```

**Usage:**
```php
$result = $service->testConnectivity();

if ($result['overall_status'] === 'success') {
    echo "NBC API is accessible and working!\n";
}
```

---

## Digital Signature Implementation

### How It Works

All requests to NBC PVAS API must be digitally signed using **SHA256withRSA** algorithm.

**Process:**
1. Prepare request payload as JSON
2. Sign JSON string with our private key
3. Base64 encode the signature
4. Send in `X-Signature` header

**Code Implementation:**
```php
protected function generateSignature(array $payload): string
{
    // Convert to JSON
    $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);

    // Load private key
    $privateKey = openssl_pkey_get_private(
        file_get_contents($this->privateKeyPath)
    );

    // Sign with SHA256withRSA
    openssl_sign($jsonPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256);

    // Base64 encode
    return base64_encode($signature);
}
```

**Headers Sent:**
```
POST /api/v1/casa/statement
X-Signature: eyJhbGciOiJIUzI1NiJ9...
Authorization: Bearer eyJhbGciOiJIUzI1NiJ9...
Content-Type: application/json
Accept: application/json
Connection: Keep-Alive
Accept-Encoding: br,deflate,gzip,x-gzip
```

---

## Configuration Setup

### 1. Add to `.env`

```bash
# NBC PVAS Statement API
NBC_STATEMENT_BASE_URL=https://api-uat.nbc.co.tz
NBC_STATEMENT_USERNAME=SACCOSNBC
NBC_STATEMENT_PASSWORD=<provided-by-nbc>
NBC_STATEMENT_ACCOUNT_NUMBER=015103001490
NBC_STATEMENT_PRIVATE_KEY_PATH=/var/www/html/INSTANCES/nbc_saccos/core/storage/keys/nbc_statement_private.pem
NBC_STATEMENT_PUBLIC_KEY_PATH=/var/www/html/INSTANCES/nbc_saccos/core/storage/keys/nbc_statement_public.pem
NBC_STATEMENT_TIMEOUT=30
NBC_STATEMENT_VERIFY_SSL=true
```

### 2. Configuration Already Added

Located in `/config/services.php`:

```php
'nbc_statement' => [
    'base_url' => env('NBC_STATEMENT_BASE_URL'),
    'username' => env('NBC_STATEMENT_USERNAME'),
    'password' => env('NBC_STATEMENT_PASSWORD'),
    'private_key_path' => env('NBC_STATEMENT_PRIVATE_KEY_PATH',
        storage_path('keys/nbc_statement_private.pem')),
    'public_key_path' => env('NBC_STATEMENT_PUBLIC_KEY_PATH',
        storage_path('keys/nbc_statement_public.pem')),
    'account_number' => env('NBC_STATEMENT_ACCOUNT_NUMBER', '015103001490'),
    'timeout' => env('NBC_STATEMENT_TIMEOUT', 30),
    'verify_ssl' => env('NBC_STATEMENT_VERIFY_SSL', true),
],
```

---

## Key Generation & Exchange

### Generate Our Keys (Already Done if using existing keys)

```bash
# If generating new keys
cd /var/www/html/INSTANCES/nbc_saccos/core/storage/keys

# Generate private key
openssl genrsa -out nbc_statement_private.pem 2048

# Generate public key from private
openssl rsa -in nbc_statement_private.pem -pubout -out nbc_statement_public.pem

# Set permissions
chmod 600 nbc_statement_private.pem
chmod 644 nbc_statement_public.pem
```

### Exchange with NBC

**What to Send NBC:**
- Our public key: `/storage/keys/nbc_statement_public.pem`
- Should be PEM base64 encoded

**What NBC Will Provide:**
- Their public key (for verifying responses)
- Channel ID (username)
- Channel Secret (password)
- UAT Base URL
- PROD Base URL (after sign-off)

---

## Response Status Codes

| Code | Description |
|------|-------------|
| 600 | Success |
| 601 | Failed |
| 602 | Digital Signature Verification Failure |
| 615 | Authentication Failed |
| 613 | Unauthorized service access request |
| 699 | Exception caught |

**Get Description:**
```php
$description = NBCStatementService::getStatusCodeDescription(600);
// Returns: "Success"
```

---

## Integration with Daily Reconciliation

The NBCStatementService is automatically used by the NBCDailyReconciliationService:

```php
// In NBCDailyReconciliationService.php
protected function fetchNBCBankStatement(): array
{
    $statementDate = now()->subDay();

    // Fetch from NBC API
    $nbcTransactions = $this->nbcStatementService->fetchAccountStatement(
        $this->nbcAccountNumber,
        $statementDate
    );

    // Map to our format
    $mappedTransactions = array_map(
        [$this->nbcStatementService, 'mapNBCTransaction'],
        $nbcTransactions
    );

    return $mappedTransactions;
}
```

**Flow:**
1. Daily reconciliation job runs at 01:00 AM
2. Calls `fetchNBCBankStatement()`
3. NBCStatementService authenticates with NBC
4. Signs request with digital signature
5. Fetches yesterday's transactions
6. Maps NBC format to bank_transactions format
7. Returns to reconciliation service
8. Transactions inserted into database
9. Reconciliation performed

---

## Testing & Debugging

### Test Authentication

```bash
php artisan tinker

use App\Services\NBCStatementService;
$service = new NBCStatementService();

// Test connectivity
$result = $service->testConnectivity();
print_r($result);
```

### Test Statement Fetching

```php
// In tinker
$transactions = $service->fetchAccountStatement(
    '015103001490',
    '2025-10-10'
);

echo "Fetched " . count($transactions) . " transactions\n";
print_r($transactions[0]); // View first transaction
```

### Clear Auth Cache

```php
$service->clearAuthCache();
echo "Authentication cache cleared\n";
```

### View Logs

```bash
# NBC Statement API logs
tail -f storage/logs/laravel.log | grep "NBC Statement API"

# Daily reconciliation logs
tail -f storage/logs/nbc-daily-reconciliation.log
```

---

## Error Handling

### Common Errors & Solutions

#### 1. Authentication Failed (615)

**Error:** `Authentication failed: HTTP 401`

**Causes:**
- Incorrect username/password
- Credentials not yet configured by NBC

**Solution:**
```bash
# Check credentials in .env
cat .env | grep NBC_STATEMENT

# Verify with NBC that credentials are active
```

#### 2. Signature Verification Failure (602)

**Error:** `Digital Signature Verification Failure`

**Causes:**
- Wrong private key being used
- NBC doesn't have our correct public key

**Solution:**
```bash
# Verify private key exists and is readable
ls -l storage/keys/nbc_statement_private.pem

# Resend our public key to NBC
cat storage/keys/nbc_statement_public.pem
```

#### 3. Connection Timeout

**Error:** `Connection timeout after 30 seconds`

**Causes:**
- NBC API is down
- Network/firewall issues
- IP not whitelisted

**Solution:**
```bash
# Test network connectivity
ping api-uat.nbc.co.tz

# Check if IP whitelisted with NBC
curl -I https://api-uat.nbc.co.tz
```

#### 4. Missing Private Key

**Error:** `NBC Statement Service: Private key file not found`

**Solution:**
```bash
# Check path in .env
echo $NBC_STATEMENT_PRIVATE_KEY_PATH

# Ensure file exists
ls -l /var/www/html/INSTANCES/nbc_saccos/core/storage/keys/nbc_statement_private.pem

# Fix permissions if needed
chmod 600 storage/keys/nbc_statement_private.pem
```

---

## Security Considerations

### ✅ Implemented

- Digital signature on all requests (SHA256withRSA)
- JWT token authentication
- Private key file permissions (600)
- Token caching with auto-expiry
- HTTPS enforcement
- Request timeout (30 seconds)
- Comprehensive error logging (without exposing credentials)

### 🔒 Recommended

1. **IP Whitelisting:** Ensure NBC whitelists our IP
2. **Key Rotation:** Rotate keys every 6-12 months
3. **Credential Rotation:** Change passwords quarterly
4. **SSL Verification:** Keep `NBC_STATEMENT_VERIFY_SSL=true` in production
5. **Monitoring:** Set up alerts for authentication failures
6. **Audit:** Review logs weekly for suspicious activity

---

## Checklist for Production Deployment

### Before UAT Testing

- [ ] Generate new key pair or use existing
- [ ] Send public key to NBC (PEM base64 encoded)
- [ ] Receive credentials from NBC:
  - [ ] Channel ID (username)
  - [ ] Channel Secret (password)
  - [ ] UAT Base URL
  - [ ] NBC Public Key
- [ ] Configure `.env` with NBC credentials
- [ ] Save NBC public key to `storage/keys/nbc_statement_public.pem`
- [ ] Test authentication
- [ ] Test statement fetching
- [ ] Test digital signature

### UAT Phase

- [ ] Run daily reconciliation manually
- [ ] Verify transactions are fetched correctly
- [ ] Verify transactions are mapped correctly
- [ ] Check reconciliation matching works
- [ ] Review all logs for errors
- [ ] Test error scenarios
- [ ] Document any issues

### Before Production

- [ ] Complete UAT sign-off with NBC
- [ ] Receive PROD credentials from NBC
- [ ] Update `.env` with PROD credentials
- [ ] Test PROD connectivity (if allowed)
- [ ] Schedule reconciliation job for production time
- [ ] Set up monitoring/alerting
- [ ] Document runbook for operations team

---

## API Reference

### NBC PVAS API Endpoints

| Endpoint | Method | Service Code | Purpose |
|----------|--------|--------------|---------|
| `/api/auth/login` | POST | N/A | Authentication |
| `/api/v1/casa/statement` | POST | SC990003 | Get account statement |
| `/api/v1/casa/balance` | POST | SC990001 | Get account balance |
| `/api/v1/casa/summary` | POST | SC990002 | Get transaction summary |

### Service Methods

| Method | Returns | Purpose |
|--------|---------|---------|
| `fetchAccountStatement()` | array | Fetch statement transactions |
| `fetchAccountBalance()` | array | Fetch balance info |
| `testConnectivity()` | array | Test API connection |
| `mapNBCTransaction()` | array | Map NBC format to our format |
| `clearAuthCache()` | void | Clear cached JWT token |
| `getStatusCodeDescription()` | string | Get status code meaning |

---

## Support & Contact

**NBC Development Team:**
Email: InhouseDevelopments@nbc.co.tz

**Documentation:**
- This file: `/NBC_STATEMENT_SERVICE.md`
- Daily Reconciliation: `/NBC_DAILY_RECONCILIATION.md`
- Bank Recon Improvements: `/BANK_RECON_IMPROVEMENTS.md`

**Service Files:**
- Service: `/app/Services/NBCStatementService.php`
- Daily Recon: `/app/Services/NBCDailyReconciliationService.php`
- Config: `/config/services.php`

---

**End of Documentation**
