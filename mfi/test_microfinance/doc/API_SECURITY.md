# MFI Management System - API Security Implementation

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Token-Based Authentication](#token-based-authentication)
4. [Token Lifecycle Management](#token-lifecycle-management)
5. [API Headers Validation](#api-headers-validation)
6. [Scope-Based Authorization](#scope-based-authorization)
7. [Per-Client Rate Limiting](#per-client-rate-limiting)
8. [CLI Token Management](#cli-token-management)
9. [Usage Examples](#usage-examples)
10. [Best Practices](#best-practices)
11. [Testing Procedures](#testing-procedures)
12. [Security Considerations](#security-considerations)

---

## Overview

This document describes the comprehensive API security implementation for MFI Management System, focusing on:

- **Token-Based Authentication**: OAuth2-style API tokens instead of credential passing
- **Granular Scopes**: Fine-grained permission control with wildcard support
- **Token Lifecycle**: Automatic generation, rotation, and expiration management
- **Header Validation**: Strict Content-Type and Accept header enforcement
- **Per-Client Rate Limiting**: Individual rate limits for each API client
- **Secure Storage**: Hashed token storage with one-time plain text exposure

### Key Benefits

✅ **No Credential Exposure**: API tokens eliminate the need to pass user credentials
✅ **Granular Permissions**: Each token has specific scopes defining allowed operations
✅ **Automatic Rotation**: Tokens near expiration can be automatically rotated
✅ **Audit Trail**: Complete history of token generation, usage, and revocation
✅ **Rate Limiting**: Per-client limits prevent abuse and ensure fair usage
✅ **Easy Revocation**: Tokens can be instantly revoked without affecting users

---

## Architecture

### Components

```
┌─────────────────────────────────────────────────────────────────┐
│                        API Request Flow                          │
└─────────────────────────────────────────────────────────────────┘

   Client Request
        │
        ├─► ValidateApiHeaders (Middleware)
        │   └─► Validates Content-Type & Accept headers
        │
        ├─► ApiKeyAuthentication (Middleware)
        │   ├─► Validates API token
        │   ├─► Checks token expiration
        │   ├─► Checks per-client rate limit
        │   └─► Attaches API key to request
        │
        ├─► ValidateApiScopes (Middleware)
        │   └─► Checks required scopes/permissions
        │
        └─► Controller Action
            └─► Process request
```

### Files Structure

```
app/
├── Http/Middleware/
│   ├── ApiKeyAuthentication.php       # Token authentication & rate limiting
│   ├── ValidateApiHeaders.php         # Content-Type/Accept validation
│   └── ValidateApiScopes.php          # Scope-based authorization
│
├── Services/
│   └── ApiTokenService.php            # Token lifecycle management
│
├── Console/Commands/
│   └── ManageApiTokens.php            # CLI token management
│
└── Models/
    └── ApiKey.php                     # API token model

database/migrations/
└── [timestamp]_create_api_keys_table.php

config/
└── auth.php                           # Authentication configuration
```

---

## Token-Based Authentication

### Overview

API tokens replace traditional username/password authentication for API requests. Each token:
- Is cryptographically secure (64 random characters)
- Has a unique identifier prefix: `nbc_saccos_v1_`
- Is hashed before storage (never stored in plain text)
- Has an optional expiration date
- Can be instantly revoked

### Token Format

```
<EXAMPLE_TOKEN>  (total 86 characters)
│           │  └──────────────────► 64 random hex characters
│           └──────────────────────► Version identifier
└──────────────────────────────────► Namespace prefix
```

### ApiKeyAuthentication Middleware

**File**: `app/Http/Middleware/ApiKeyAuthentication.php`

**Purpose**: Authenticates API requests using tokens and enforces per-client rate limits.

#### Features

1. **Token Extraction**: Supports both `X-API-Key` and `Authorization: Bearer` headers
2. **Token Validation**: Verifies token exists, is active, and not expired
3. **Rate Limiting**: Enforces per-client rate limits (configurable per token)
4. **Caching**: Uses cache for performance (5-minute TTL)
5. **Audit Logging**: Logs all authentication attempts

#### Implementation

```php
class ApiKeyAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        // Extract token from headers
        $apiKey = $request->header('X-API-Key')
               ?? $request->header('Authorization');

        if ($apiKey && str_starts_with($apiKey, 'Bearer ')) {
            $apiKey = substr($apiKey, 7);
        }

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required',
                'error_code' => 'MISSING_API_KEY'
            ], 401);
        }

        // Validate API key
        $validKey = $this->validateApiKey($apiKey);

        if (!$validKey || !$validKey->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive API key',
                'error_code' => 'INVALID_API_KEY'
            ], 401);
        }

        // Check rate limiting
        if ($this->isRateLimited($validKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded',
                'error_code' => 'RATE_LIMIT_EXCEEDED'
            ], 429);
        }

        // Add API key to request
        $request->merge(['api_key' => $validKey]);

        return $next($request);
    }
}
```

#### Usage in Routes

**Apply to individual routes:**
```php
Route::get('/api/accounts', [AccountController::class, 'index'])
    ->middleware('api.key');
```

**Apply to route groups:**
```php
Route::middleware(['api.key'])->prefix('api')->group(function () {
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
});
```

---

## Token Lifecycle Management

### ApiTokenService

**File**: `app/Services/ApiTokenService.php`

**Purpose**: Manages complete token lifecycle including generation, rotation, expiration, and revocation.

### Token Generation

#### Method: `generateToken()`

Generates a new cryptographically secure API token.

**Parameters:**
- `$clientName` (string): Descriptive name for the client/application
- `$scopes` (array): Array of permission scopes (e.g., `['transactions.read', 'users.write']`)
- `$expiresInDays` (int|null): Days until token expires (default: 30)
- `$metadata` (array): Additional metadata (rate limits, contact info, etc.)

**Returns:**
```php
[
    'token' => 'nbc_saccos_v1_...',  // Plain token (only shown once)
    'api_key' => ApiKey               // Database record
]
```

**Example:**
```php
use App\Services\ApiTokenService;

$tokenService = new ApiTokenService();

$result = $tokenService->generateToken(
    clientName: 'Mobile Banking App',
    scopes: ['transactions.read', 'accounts.read', 'transfers.write'],
    expiresInDays: 90,
    metadata: [
        'rate_limit' => 5000,
        'contact_email' => 'dev@example.com',
        'environment' => 'production'
    ]
);

// IMPORTANT: Store this token securely - it won't be shown again
$plainToken = $result['token'];
$apiKey = $result['api_key'];
```

### Token Rotation

#### Method: `rotateToken()`

Generates a new token with the same scopes and revokes the old token.

**When to Rotate:**
- Token approaching expiration
- Security incident or suspected compromise
- Regular rotation policy (e.g., every 90 days)
- Client requests new token

**Example:**
```php
$apiKey = ApiKey::find($tokenId);

$newToken = $tokenService->rotateToken(
    apiKey: $apiKey,
    expiresInDays: 90
);

// Old token is automatically revoked
// Notify client to use new token
```

### Automatic Token Rotation

#### Method: `autoRotateExpiringTokens()`

Automatically rotates tokens expiring within a specified timeframe.

**Usage (in scheduled command):**
```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Auto-rotate tokens expiring in 7 days
    $schedule->call(function () {
        $tokenService = app(ApiTokenService::class);
        $rotatedCount = $tokenService->autoRotateExpiringTokens(
            daysBeforeExpiration: 7
        );

        Log::info("Auto-rotated {$rotatedCount} expiring tokens");
    })->daily();
}
```

**Behavior:**
- Finds tokens expiring within specified days
- Skips tokens with `metadata.auto_rotate = false`
- Generates new tokens with same scopes
- Revokes old tokens
- Logs all rotations

### Token Revocation

#### Method: `revokeToken()`

Immediately revokes a token, preventing all future use.

**Common Reasons:**
- Security incident
- Client decommissioned
- Suspected compromise
- Policy violation
- Client request

**Example:**
```php
$apiKey = ApiKey::find($tokenId);

$tokenService->revokeToken(
    apiKey: $apiKey,
    reason: 'Security incident - suspected compromise'
);

// Token immediately invalid
// Cache cleared
// Audit log created
```

### Token Expiration

#### Method: `isTokenExpired()`

Checks if a token has passed its expiration date.

**Example:**
```php
$apiKey = ApiKey::find($tokenId);

if ($tokenService->isTokenExpired($apiKey)) {
    echo "Token expired on: " . $apiKey->expires_at;
}
```

### Token Expiration Periods

```php
const SHORT_LIVED_TOKEN_DAYS = 30;    // 1 month (recommended)
const MEDIUM_LIVED_TOKEN_DAYS = 90;   // 3 months
const LONG_LIVED_TOKEN_DAYS = 365;    // 1 year (not recommended)
```

**Best Practice:** Use 30-day tokens with automatic rotation for production APIs.

### Token Usage Tracking

#### Method: `recordTokenUsage()`

Records when and how often a token is used.

**Automatic Tracking:**
The ApiKeyAuthentication middleware automatically tracks:
- Last usage timestamp (`last_used_at`)
- Total usage count (`usage_count`)
- Daily usage statistics (cached for 30 days)

**Manual Recording:**
```php
$tokenService->recordTokenUsage($apiKey, [
    'endpoint' => '/api/transactions',
    'method' => 'POST',
    'ip' => '10.0.0.1'
]);
```

#### Method: `getTokenUsageStats()`

Retrieves usage statistics for a token.

**Example:**
```php
$stats = $tokenService->getTokenUsageStats($apiKey, days: 30);

/*
Returns:
[
    'key_id' => 123,
    'client_name' => 'Mobile Banking App',
    'total_usage' => 15420,
    'last_used_at' => Carbon instance,
    'daily_usage' => [
        '2025-10-15' => 523,
        '2025-10-14' => 489,
        ...
    ]
]
*/
```

### Token Cleanup

#### Method: `cleanupOldTokens()`

Removes old revoked and expired tokens from the database.

**Usage (in scheduled command):**
```php
protected function schedule(Schedule $schedule)
{
    // Clean up tokens revoked/expired > 90 days ago
    $schedule->call(function () {
        $tokenService = app(ApiTokenService::class);
        $deletedCount = $tokenService->cleanupOldTokens(daysOld: 90);

        Log::info("Cleaned up {$deletedCount} old API tokens");
    })->weekly();
}
```

**Safety:**
- Only deletes inactive (revoked/expired) tokens
- Active tokens are never deleted
- Audit logs should be backed up before cleanup

---

## API Headers Validation

### ValidateApiHeaders Middleware

**File**: `app/Http/Middleware/ValidateApiHeaders.php`

**Purpose**: Validates Content-Type and Accept headers for API requests to prevent content-type confusion attacks.

### Why Validate Headers?

**Security Risks of Invalid Headers:**
1. **Content-Type Confusion**: Attacker sends HTML as JSON to exploit XSS
2. **Parser Confusion**: Different parsers interpret ambiguous content differently
3. **API Misuse**: Non-JSON clients may not handle responses correctly

### Validation Rules

#### Content-Type (for POST/PUT/PATCH)

**Allowed:**
- `application/json`
- `application/json; charset=utf-8`
- `application/json; charset=UTF-8`

**Rejected:**
- `text/html` (HTML responses in API)
- `text/javascript` (JavaScript responses)
- `application/x-www-form-urlencoded` (should use JSON)

#### Accept Header

**Allowed (when strict):**
- `application/json`
- `*/*`
- `application/*`

**Flexible (when not strict):**
- Missing Accept header allowed
- Multiple Accept types with quality parameters

### Configuration

The middleware accepts two boolean parameters:

```php
ValidateApiHeaders::handle($request, $next, $strictContentType, $strictAccept)
```

**Parameters:**
- `$strictContentType` (default: `true`): Enforce Content-Type validation
- `$strictAccept` (default: `false`): Enforce Accept header validation

### Usage

**Strict validation (recommended for production APIs):**
```php
Route::post('/api/transactions', [TransactionController::class, 'store'])
    ->middleware('api.headers:true,true');
```

**Lenient validation (for legacy clients):**
```php
Route::post('/api/legacy/endpoint', [LegacyController::class, 'store'])
    ->middleware('api.headers:false,false');
```

**Default validation (strict Content-Type, lenient Accept):**
```php
Route::post('/api/accounts', [AccountController::class, 'store'])
    ->middleware('api.headers');
```

### Error Responses

**Missing Content-Type:**
```json
{
    "success": false,
    "message": "Invalid API headers",
    "errors": ["Content-Type header is required"],
    "error_code": "INVALID_HEADERS"
}
```

**Invalid Content-Type:**
```json
{
    "success": false,
    "message": "Invalid API headers",
    "errors": ["Invalid Content-Type header. Expected application/json, got text/html"],
    "error_code": "INVALID_HEADERS"
}
```

### Security Features

1. **Dangerous Content-Type Detection**: Logs warnings for potentially dangerous types
2. **Automatic Response Headers**: Sets correct `Content-Type: application/json` on responses
3. **Audit Logging**: All header validation failures are logged with IP and endpoint

---

## Scope-Based Authorization

### ValidateApiScopes Middleware

**File**: `app/Http/Middleware/ValidateApiScopes.php`

**Purpose**: Implements fine-grained permission control based on token scopes.

### Scope Syntax

Scopes follow a hierarchical dot notation:

```
resource.action
```

**Examples:**
- `transactions.read` - Read transaction data
- `transactions.write` - Create/update transactions
- `accounts.read` - Read account information
- `users.*.read` - Read any user resource
- `reports.export` - Export reports
- `admin` - Full administrative access
- `*` - All permissions (wildcard)

### Wildcard Matching

The middleware supports wildcard scopes for flexible permissions:

**Pattern Matching:**
- `transactions.*` grants `transactions.read`, `transactions.write`, `transactions.delete`
- `users.*.read` grants `users.1.read`, `users.2.read`, etc.
- `*.read` grants all read operations
- `*` or `admin` grants all permissions

**Implementation:**
```php
protected function scopeMatchesWildcard(string $requiredScope, string $grantedScope): bool
{
    if (!str_contains($grantedScope, '*')) {
        return false;
    }

    // Convert wildcard to regex: "transactions.*" -> "^transactions\..*$"
    $pattern = '/^' . str_replace('\*', '.*', preg_quote($grantedScope, '/')) . '$/';

    return preg_match($pattern, $requiredScope) === 1;
}
```

### Usage in Routes

**Single scope requirement:**
```php
Route::get('/api/transactions', [TransactionController::class, 'index'])
    ->middleware('api.scopes:transactions.read');
```

**Multiple scope requirements (all required):**
```php
Route::post('/api/transfers', [TransferController::class, 'store'])
    ->middleware('api.scopes:transactions.write,accounts.debit');
```

**Combining with authentication:**
```php
Route::middleware(['api.key', 'api.scopes:users.read'])->group(function () {
    Route::get('/api/users', [UserController::class, 'index']);
    Route::get('/api/users/{id}', [UserController::class, 'show']);
});
```

### Common Scope Patterns

**Read-Only API Client:**
```php
$scopes = [
    'accounts.read',
    'transactions.read',
    'reports.read'
];
```

**Mobile Banking App:**
```php
$scopes = [
    'accounts.read',
    'transactions.read',
    'transactions.write',
    'transfers.write',
    'users.profile.read',
    'users.profile.update'
];
```

**Reporting System:**
```php
$scopes = [
    '*.read',           // Read all resources
    'reports.export'    // Export capability
];
```

**Admin Panel:**
```php
$scopes = ['admin'];  // or ['*']
```

### Error Response

**Insufficient Permissions:**
```json
{
    "success": false,
    "message": "Insufficient permissions",
    "error_code": "INSUFFICIENT_PERMISSIONS",
    "required_scopes": ["transactions.write", "accounts.debit"]
}
```

### Best Practices

1. **Principle of Least Privilege**: Grant only necessary scopes
2. **Specific Over Wildcard**: Use `transactions.read` instead of `*`
3. **Separate Read/Write**: Don't grant write access when read is sufficient
4. **Regular Audits**: Review and update scopes as requirements change
5. **Scope Documentation**: Maintain clear documentation of all available scopes

---

## Per-Client Rate Limiting

### Overview

Each API token has its own rate limit, preventing individual clients from overloading the system.

### Rate Limit Storage

Rate limits are stored in the `api_keys` table:

```php
$apiKey->rate_limit = 5000;  // Requests per hour
```

### Implementation

**In ApiKeyAuthentication Middleware:**

```php
protected function isRateLimited($apiKey)
{
    $rateLimitKey = "rate_limit:api_key:{$apiKey->id}";
    $maxRequests = $apiKey->rate_limit ?? 1000;  // Default: 1000/hour
    $window = 3600;  // 1 hour

    $currentRequests = Cache::get($rateLimitKey, 0);

    if ($currentRequests >= $maxRequests) {
        return true;
    }

    Cache::put($rateLimitKey, $currentRequests + 1, $window);
    return false;
}
```

### Rate Limit Configuration

**Default Limits:**
```php
// app/Services/ApiTokenService.php
$metadata = [
    'rate_limit' => 1000  // Default: 1000 requests/hour
];
```

**Custom Limits by Client Type:**

```php
// High-volume client (e.g., mobile app)
$metadata = ['rate_limit' => 10000];

// Standard integration
$metadata = ['rate_limit' => 5000];

// Trial/testing client
$metadata = ['rate_limit' => 100];

// Internal system (no limit)
$metadata = ['rate_limit' => 999999];
```

### Rate Limit Headers

The middleware automatically adds rate limit information to responses:

```http
X-RateLimit-Limit: 5000
X-RateLimit-Remaining: 4823
X-RateLimit-Reset: 1634567890
```

**Implementation:**
```php
$response->headers->set('X-RateLimit-Limit', $apiKey->rate_limit);
$response->headers->set('X-RateLimit-Remaining', $remaining);
$response->headers->set('X-RateLimit-Reset', $resetTime);
```

### Rate Limit Response

**When limit exceeded:**
```json
{
    "success": false,
    "message": "Rate limit exceeded",
    "error_code": "RATE_LIMIT_EXCEEDED"
}
```

**HTTP Status:** 429 Too Many Requests

### Monitoring Rate Limits

**Check current usage:**
```php
$rateLimitKey = "rate_limit:api_key:{$apiKey->id}";
$currentUsage = Cache::get($rateLimitKey, 0);
$percentUsed = ($currentUsage / $apiKey->rate_limit) * 100;

if ($percentUsed > 80) {
    // Alert client approaching limit
}
```

### Sliding Window vs Fixed Window

**Current Implementation:** Fixed window (resets every hour)

**Upgrading to Sliding Window:**
```php
// Store per-minute buckets for 60 minutes
for ($i = 0; $i < 60; $i++) {
    $minute = now()->subMinutes($i)->format('YmdHi');
    $key = "rate_limit:api_key:{$apiKey->id}:minute:{$minute}";
    $count = Cache::get($key, 0);
    $totalRequests += $count;
}
```

---

## CLI Token Management

### ManageApiTokens Command

**File**: `app/Console/Commands/ManageApiTokens.php`

**Purpose**: Command-line interface for managing API tokens without database access.

### Available Actions

```bash
php artisan api:token {action}
```

**Actions:**
- `generate` - Create a new API token
- `rotate` - Rotate an existing token
- `revoke` - Revoke a token
- `list` - List all tokens
- `stats` - Show token usage statistics

### Generate Token

**Basic Usage:**
```bash
php artisan api:token generate \
    --client="Mobile Banking App" \
    --scopes=transactions.read,transactions.write,accounts.read \
    --expires=90 \
    --rate-limit=5000
```

**Interactive Mode:**
```bash
php artisan api:token generate
```

**Output:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
API TOKEN GENERATED
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Client Name: Mobile Banking App
Token ID: 42
Scopes: transactions.read, transactions.write, accounts.read
Rate Limit: 5000 requests/hour
Expires: 2026-01-14 10:30:00

⚠️  IMPORTANT: Store this token securely. It will NOT be shown again!

Token: <EXAMPLE_TOKEN>

Use in API requests:
  Authorization: Bearer <EXAMPLE_TOKEN>
  or
  X-API-Key: <EXAMPLE_TOKEN>
```

### Rotate Token

**Usage:**
```bash
php artisan api:token rotate --id=42 --expires=90
```

**Interactive Mode:**
```bash
php artisan api:token rotate
```

**Output:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
TOKEN ROTATED
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Old Token ID: 42 (revoked)
New Token ID: 78
Expires: 2026-01-14 11:45:00

⚠️  The old token has been revoked and is no longer valid.
⚠️  Update your application with the new token:

New Token: <NEW_EXAMPLE_TOKEN>
```

### Revoke Token

**Usage:**
```bash
php artisan api:token revoke \
    --id=42 \
    --reason="Security incident - suspected compromise"
```

**Interactive Mode:**
```bash
php artisan api:token revoke
```

**Output:**
```
✓ Token revoked successfully
Client: Mobile Banking App
Reason: Security incident - suspected compromise
```

### List Tokens

**Usage:**
```bash
php artisan api:token list
```

**Output:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
API TOKENS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

┌────┬──────────────────────┬──────────┬────────────────────────────────┬────────────┬────────────────┐
│ ID │ Client               │ Status   │ Scopes                         │ Expires    │ Last Used      │
├────┼──────────────────────┼──────────┼────────────────────────────────┼────────────┼────────────────┤
│ 78 │ Mobile Banking App   │ ✓ Active │ transactions.*, accounts.read  │ 2026-01-14 │ 2 hours ago    │
│ 65 │ Web Dashboard        │ ✓ Active │ admin                          │ 2025-12-31 │ 5 minutes ago  │
│ 52 │ Reporting System     │ ✓ Active │ *.read, reports.export         │ 2025-11-30 │ 1 day ago      │
│ 42 │ Mobile Banking App   │ ✗ Revoked│ transactions.*, accounts.read  │ 2025-10-20 │ 3 days ago     │
│ 38 │ Legacy Integration   │ ⚠ Expired│ transactions.read              │ 2025-09-15 │ 1 month ago    │
└────┴──────────────────────┴──────────┴────────────────────────────────┴────────────┴────────────────┘
```

### Token Statistics

**Usage:**
```bash
php artisan api:token stats --id=78
```

**Output:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
TOKEN USAGE STATISTICS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Client: Mobile Banking App
Token ID: 78
Total Usage: 15,420 requests
Last Used: 2 hours ago

Daily Usage (Last 30 Days):

┌────────────┬──────────┐
│ Date       │ Requests │
├────────────┼──────────┤
│ 2025-10-16 │ 523      │
│ 2025-10-15 │ 489      │
│ 2025-10-14 │ 512      │
│ 2025-10-13 │ 456      │
│ 2025-10-12 │ 501      │
│ ...        │ ...      │
└────────────┴──────────┘
```

---

## Usage Examples

### Complete API Request Flow

#### 1. Generate API Token (Admin)

```bash
php artisan api:token generate \
    --client="Mobile App v2.5" \
    --scopes=transactions.read,transactions.write,accounts.read,transfers.write \
    --expires=90 \
    --rate-limit=10000
```

**Store the token securely** (e.g., in environment variables or secrets manager).

#### 2. Configure API Client

**Environment Configuration:**
```env
API_BASE_URL=https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api
API_TOKEN=<EXAMPLE_TOKEN>
```

**Client Code (JavaScript):**
```javascript
const apiConfig = {
    baseURL: process.env.API_BASE_URL,
    headers: {
        'Authorization': `Bearer ${process.env.API_TOKEN}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
};

const api = axios.create(apiConfig);
```

#### 3. Make API Request

**GET Request (Read Transactions):**
```javascript
const response = await api.get('/transactions', {
    params: {
        account_id: 12345,
        start_date: '2025-10-01',
        end_date: '2025-10-16'
    }
});

console.log(response.data);
```

**POST Request (Create Transaction):**
```javascript
const transaction = {
    from_account: '011191000035',
    to_account: '011191000042',
    amount: 50000.00,
    currency: 'TZS',
    description: 'Transfer to savings'
};

const response = await api.post('/transactions', transaction);

console.log(response.data);
```

#### 4. Handle Rate Limiting

```javascript
try {
    const response = await api.get('/transactions');

    // Check rate limit headers
    const limit = response.headers['x-ratelimit-limit'];
    const remaining = response.headers['x-ratelimit-remaining'];
    const reset = response.headers['x-ratelimit-reset'];

    console.log(`Rate Limit: ${remaining}/${limit} remaining`);
    console.log(`Resets at: ${new Date(reset * 1000)}`);

} catch (error) {
    if (error.response?.status === 429) {
        console.error('Rate limit exceeded. Please retry later.');

        // Implement exponential backoff
        const retryAfter = error.response.headers['retry-after'];
        setTimeout(() => {
            // Retry request
        }, retryAfter * 1000);
    }
}
```

#### 5. Handle Scope Errors

```javascript
try {
    const response = await api.delete('/users/123');
} catch (error) {
    if (error.response?.status === 403) {
        console.error('Insufficient permissions');
        console.log('Required scopes:', error.response.data.required_scopes);

        // Token needs users.delete scope
        // Request new token with appropriate scopes
    }
}
```

### Route Protection Examples

#### Protected API Endpoints

**routes/api.php:**

```php
<?php

use App\Http\Controllers\Api\{
    AccountController,
    TransactionController,
    TransferController,
    ReportController,
    UserController
};

// Public endpoints (no authentication)
Route::get('/health', fn() => ['status' => 'healthy']);
Route::get('/version', fn() => ['version' => '2.5.0']);

// Authenticated endpoints
Route::middleware(['api.key', 'api.headers'])->group(function () {

    // Read-only endpoints
    Route::middleware('api.scopes:accounts.read')->group(function () {
        Route::get('/accounts', [AccountController::class, 'index']);
        Route::get('/accounts/{id}', [AccountController::class, 'show']);
    });

    Route::middleware('api.scopes:transactions.read')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    });

    // Write endpoints (multiple scopes required)
    Route::post('/transactions', [TransactionController::class, 'store'])
        ->middleware('api.scopes:transactions.write,accounts.debit');

    Route::post('/transfers', [TransferController::class, 'store'])
        ->middleware('api.scopes:transfers.write,accounts.debit,accounts.credit');

    // Report endpoints
    Route::middleware('api.scopes:reports.read')->group(function () {
        Route::get('/reports/daily', [ReportController::class, 'daily']);
        Route::get('/reports/monthly', [ReportController::class, 'monthly']);
    });

    Route::get('/reports/export', [ReportController::class, 'export'])
        ->middleware('api.scopes:reports.export');

    // Admin endpoints
    Route::middleware('api.scopes:admin')->prefix('admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{id}/reset-password', [UserController::class, 'resetPassword']);
    });
});
```

### Token Rotation Workflow

#### Automatic Rotation (Scheduled)

**app/Console/Kernel.php:**

```php
protected function schedule(Schedule $schedule)
{
    // Auto-rotate tokens expiring in 7 days
    $schedule->call(function () {
        $tokenService = app(ApiTokenService::class);
        $rotatedCount = $tokenService->autoRotateExpiringTokens(
            daysBeforeExpiration: 7
        );

        if ($rotatedCount > 0) {
            Log::info("Auto-rotated {$rotatedCount} expiring API tokens");

            // TODO: Notify clients about token rotation
            // - Send email with new token
            // - Log notification in audit trail
        }
    })->daily()->at('02:00');

    // Clean up old revoked tokens (90+ days)
    $schedule->call(function () {
        $tokenService = app(ApiTokenService::class);
        $deletedCount = $tokenService->cleanupOldTokens(daysOld: 90);

        Log::info("Cleaned up {$deletedCount} old API tokens");
    })->weekly()->sundays()->at('03:00');
}
```

#### Manual Rotation (Security Incident)

```bash
# 1. List tokens to find compromised token
php artisan api:token list

# 2. Rotate compromised token
php artisan api:token rotate \
    --id=78 \
    --expires=30 \
    --reason="Security incident"

# 3. Notify client of new token via secure channel
#    (email, secure portal, encrypted message)

# 4. Monitor old token for usage attempts
tail -f storage/logs/laravel.log | grep "INVALID_API_KEY"
```

---

## Best Practices

### Token Management

1. **Short-Lived Tokens**
   - Use 30-day expiration for production tokens
   - Use 7-day expiration for testing tokens
   - Avoid tokens without expiration

2. **Secure Token Storage**
   - Store tokens in environment variables or secrets manager
   - Never commit tokens to version control
   - Use `.gitignore` to exclude token files
   - Rotate tokens immediately if exposed

3. **Regular Rotation**
   - Implement automatic token rotation (7 days before expiration)
   - Notify clients in advance of rotation
   - Provide grace period for token transition
   - Log all rotation events

4. **Principle of Least Privilege**
   - Grant only necessary scopes
   - Use specific scopes over wildcards
   - Separate read/write permissions
   - Review and reduce scopes periodically

5. **Token Monitoring**
   - Monitor usage patterns for anomalies
   - Alert on rate limit violations
   - Track failed authentication attempts
   - Review inactive tokens monthly

### Scope Design

1. **Hierarchical Structure**
   ```
   resource.subresource.action

   Examples:
   - users.profile.read
   - users.profile.write
   - users.admin.read
   - users.admin.write
   ```

2. **Standard Actions**
   - `read` / `list` - Read access
   - `write` / `create` - Create access
   - `update` - Update access
   - `delete` - Delete access
   - `admin` - Full administrative access

3. **Scope Documentation**
   - Maintain scope registry
   - Document each scope's purpose
   - Provide examples of permitted operations
   - Update documentation when scopes change

### Rate Limiting

1. **Appropriate Limits**
   ```php
   // High-volume production apps
   $rateLimit = 10000;  // 10k/hour

   // Standard integrations
   $rateLimit = 5000;   // 5k/hour

   // Development/testing
   $rateLimit = 1000;   // 1k/hour

   // Trial accounts
   $rateLimit = 100;    // 100/hour
   ```

2. **Rate Limit Headers**
   - Always include rate limit headers in responses
   - Help clients manage their usage
   - Prevent unexpected 429 errors

3. **Gradual Reduction**
   - Implement exponential backoff for 429 responses
   - Don't immediately retry failed requests
   - Use jitter to prevent thundering herd

### Security

1. **HTTPS Only**
   - All API requests must use HTTPS
   - Reject HTTP requests
   - Use HSTS headers

2. **Request Validation**
   - Validate Content-Type headers
   - Validate Accept headers
   - Reject unexpected content types
   - Sanitize all inputs

3. **Audit Logging**
   - Log all authentication attempts
   - Log scope validation failures
   - Log rate limit violations
   - Log token rotations and revocations

4. **Incident Response**
   - Have token revocation procedures
   - Know how to emergency-rotate all tokens
   - Monitor for compromised tokens
   - Practice incident response drills

### API Client Guidelines

1. **Token Storage**
   ```javascript
   // ✅ Good: Environment variable
   const token = process.env.API_TOKEN;

   // ✅ Good: Secrets manager
   const token = await secretsManager.getSecret('api_token');

   // ❌ Bad: Hardcoded
   const token = 'nbc_saccos_v1_...';

   // ❌ Bad: In code repository
   const token = require('./token.json').apiToken;
   ```

2. **Error Handling**
   ```javascript
   async function makeApiRequest(url, options) {
       try {
           const response = await api.get(url, options);
           return response.data;
       } catch (error) {
           if (error.response?.status === 401) {
               // Token invalid or expired
               // Notify admin to rotate token
               throw new Error('API token invalid or expired');
           } else if (error.response?.status === 403) {
               // Insufficient permissions
               console.error('Missing scopes:', error.response.data.required_scopes);
               throw new Error('Insufficient API permissions');
           } else if (error.response?.status === 429) {
               // Rate limit exceeded
               const retryAfter = error.response.headers['retry-after'];
               await sleep(retryAfter * 1000);
               return makeApiRequest(url, options); // Retry
           }
           throw error;
       }
   }
   ```

3. **Rate Limit Awareness**
   ```javascript
   // Track rate limit status
   let rateLimitRemaining = Infinity;
   let rateLimitReset = Date.now();

   api.interceptors.response.use(response => {
       rateLimitRemaining = response.headers['x-ratelimit-remaining'];
       rateLimitReset = response.headers['x-ratelimit-reset'] * 1000;

       // Warn when approaching limit
       if (rateLimitRemaining < 100) {
           console.warn(`Rate limit low: ${rateLimitRemaining} remaining`);
       }

       return response;
   });
   ```

---

## Testing Procedures

### 1. Token Authentication Testing

#### Test Valid Token

```bash
# Generate test token
php artisan api:token generate \
    --client="Test Client" \
    --scopes=transactions.read \
    --expires=30

# Test authentication
curl -X GET "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
    -H "Authorization: Bearer nbc_saccos_v1_..." \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"

# Expected: 200 OK with transaction data
```

#### Test Invalid Token

```bash
curl -X GET "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
    -H "Authorization: Bearer invalid_token_12345" \
    -H "Content-Type: application/json"

# Expected: 401 Unauthorized
# {"success":false,"message":"Invalid API key","error_code":"INVALID_API_KEY"}
```

#### Test Missing Token

```bash
curl -X GET "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
    -H "Content-Type: application/json"

# Expected: 401 Unauthorized
# {"success":false,"message":"API key is required","error_code":"MISSING_API_KEY"}
```

#### Test Revoked Token

```bash
# Revoke token
php artisan api:token revoke --id=123 --reason="Testing"

# Attempt to use revoked token
curl -X GET "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
    -H "Authorization: Bearer nbc_saccos_v1_..." \
    -H "Content-Type: application/json"

# Expected: 401 Unauthorized
# {"success":false,"message":"Invalid or inactive API key","error_code":"INVALID_API_KEY"}
```

### 2. Header Validation Testing

#### Test Valid Headers

```bash
curl -X POST "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
    -H "Authorization: Bearer nbc_saccos_v1_..." \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"amount": 10000}'

# Expected: 200 OK or 201 Created
```

#### Test Invalid Content-Type

```bash
curl -X POST "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
    -H "Authorization: Bearer nbc_saccos_v1_..." \
    -H "Content-Type: text/html" \
    -d '{"amount": 10000}'

# Expected: 400 Bad Request
# {"success":false,"message":"Invalid API headers","errors":["Invalid Content-Type header. Expected application/json, got text/html"],"error_code":"INVALID_HEADERS"}
```

#### Test Missing Content-Type

```bash
curl -X POST "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
    -H "Authorization: Bearer nbc_saccos_v1_..." \
    -d '{"amount": 10000}'

# Expected: 400 Bad Request (if strict validation enabled)
# {"success":false,"message":"Invalid API headers","errors":["Content-Type header is required"],"error_code":"INVALID_HEADERS"}
```

### 3. Scope Authorization Testing

#### Test Sufficient Scopes

```bash
# Generate token with transactions.read scope
php artisan api:token generate \
    --client="Test" \
    --scopes=transactions.read

# Access endpoint requiring transactions.read
curl -X GET "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
    -H "Authorization: Bearer nbc_saccos_v1_..." \
    -H "Content-Type: application/json"

# Expected: 200 OK
```

#### Test Insufficient Scopes

```bash
# Use token with only transactions.read scope
curl -X POST "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
    -H "Authorization: Bearer nbc_saccos_v1_..." \
    -H "Content-Type: application/json" \
    -d '{"amount": 10000}'

# Expected: 403 Forbidden
# {"success":false,"message":"Insufficient permissions","error_code":"INSUFFICIENT_PERMISSIONS","required_scopes":["transactions.write"]}
```

#### Test Wildcard Scopes

```bash
# Generate token with wildcard scope
php artisan api:token generate \
    --client="Test" \
    --scopes=transactions.*

# Test read access
curl -X GET "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
    -H "Authorization: Bearer nbc_saccos_v1_..." \
    -H "Content-Type: application/json"

# Expected: 200 OK (wildcard grants transactions.read)

# Test write access
curl -X POST "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
    -H "Authorization: Bearer nbc_saccos_v1_..." \
    -H "Content-Type: application/json" \
    -d '{"amount": 10000}'

# Expected: 200 OK or 201 Created (wildcard grants transactions.write)
```

### 4. Rate Limiting Testing

#### Test Normal Usage

```bash
# Make requests within rate limit
for i in {1..10}; do
    curl -X GET "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
        -H "Authorization: Bearer nbc_saccos_v1_..." \
        -H "Content-Type: application/json" \
        -I | grep "X-RateLimit"
    sleep 1
done

# Expected: 200 OK responses with rate limit headers
# X-RateLimit-Limit: 1000
# X-RateLimit-Remaining: 990, 989, 988, ...
```

#### Test Rate Limit Exceeded

```bash
# Generate token with low rate limit for testing
php artisan api:token generate \
    --client="Rate Limit Test" \
    --scopes=transactions.read \
    --rate-limit=10

# Make 15 rapid requests
for i in {1..15}; do
    curl -X GET "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
        -H "Authorization: Bearer nbc_saccos_v1_..." \
        -H "Content-Type: application/json"
done

# Expected: First 10 requests succeed (200 OK)
#           Requests 11-15 fail (429 Too Many Requests)
# {"success":false,"message":"Rate limit exceeded","error_code":"RATE_LIMIT_EXCEEDED"}
```

### 5. Token Rotation Testing

#### Test Token Rotation

```bash
# Generate initial token
php artisan api:token generate \
    --client="Rotation Test" \
    --scopes=transactions.read

# Note the token ID (e.g., 123)
# Use token successfully
curl -X GET "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
    -H "Authorization: Bearer [OLD_TOKEN]" \
    -H "Content-Type: application/json"

# Expected: 200 OK

# Rotate token
php artisan api:token rotate --id=123

# Try old token (should fail)
curl -X GET "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
    -H "Authorization: Bearer [OLD_TOKEN]" \
    -H "Content-Type: application/json"

# Expected: 401 Unauthorized

# Try new token (should succeed)
curl -X GET "https://saccos-uat.intra.nbc.co.tz/nbc_saccos/core/api/transactions" \
    -H "Authorization: Bearer [NEW_TOKEN]" \
    -H "Content-Type: application/json"

# Expected: 200 OK
```

### 6. Automated Test Suite

**tests/Feature/ApiSecurityTest.php:**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ApiKey;
use App\Services\ApiTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $tokenService;
    protected $validToken;
    protected $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenService = new ApiTokenService();

        $result = $this->tokenService->generateToken(
            'Test Client',
            ['transactions.read', 'transactions.write'],
            30,
            ['rate_limit' => 1000]
        );

        $this->validToken = $result['token'];
        $this->apiKey = $result['api_key'];
    }

    /** @test */
    public function it_authenticates_with_valid_token()
    {
        $response = $this->getJson('/api/transactions', [
            'Authorization' => "Bearer {$this->validToken}"
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_rejects_invalid_token()
    {
        $response = $this->getJson('/api/transactions', [
            'Authorization' => 'Bearer invalid_token_12345'
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'error_code' => 'INVALID_API_KEY'
                 ]);
    }

    /** @test */
    public function it_rejects_missing_token()
    {
        $response = $this->getJson('/api/transactions');

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'error_code' => 'MISSING_API_KEY'
                 ]);
    }

    /** @test */
    public function it_validates_content_type_header()
    {
        $response = $this->postJson('/api/transactions',
            ['amount' => 10000],
            ['Authorization' => "Bearer {$this->validToken}",
             'Content-Type' => 'text/html']
        );

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'error_code' => 'INVALID_HEADERS'
                 ]);
    }

    /** @test */
    public function it_enforces_scope_permissions()
    {
        // Create token without write scope
        $result = $this->tokenService->generateToken(
            'Read-Only Client',
            ['transactions.read'],
            30
        );

        $response = $this->postJson('/api/transactions',
            ['amount' => 10000],
            ['Authorization' => "Bearer {$result['token']}",
             'Content-Type' => 'application/json']
        );

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'error_code' => 'INSUFFICIENT_PERMISSIONS'
                 ]);
    }

    /** @test */
    public function it_supports_wildcard_scopes()
    {
        $result = $this->tokenService->generateToken(
            'Wildcard Client',
            ['transactions.*'],
            30
        );

        // Should grant both read and write
        $readResponse = $this->getJson('/api/transactions', [
            'Authorization' => "Bearer {$result['token']}"
        ]);

        $writeResponse = $this->postJson('/api/transactions',
            ['amount' => 10000],
            ['Authorization' => "Bearer {$result['token']}",
             'Content-Type' => 'application/json']
        );

        $readResponse->assertStatus(200);
        $writeResponse->assertSuccessful();
    }

    /** @test */
    public function it_enforces_rate_limits()
    {
        // Create token with rate limit of 5
        $result = $this->tokenService->generateToken(
            'Limited Client',
            ['transactions.read'],
            30,
            ['rate_limit' => 5]
        );

        // Make 6 requests
        for ($i = 0; $i < 6; $i++) {
            $response = $this->getJson('/api/transactions', [
                'Authorization' => "Bearer {$result['token']}"
            ]);

            if ($i < 5) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429)
                         ->assertJson([
                             'success' => false,
                             'error_code' => 'RATE_LIMIT_EXCEEDED'
                         ]);
            }
        }
    }

    /** @test */
    public function it_rotates_tokens()
    {
        $oldToken = $this->validToken;

        $result = $this->tokenService->rotateToken($this->apiKey, 30);
        $newToken = $result['token'];

        // Old token should fail
        $oldResponse = $this->getJson('/api/transactions', [
            'Authorization' => "Bearer {$oldToken}"
        ]);

        // New token should succeed
        $newResponse = $this->getJson('/api/transactions', [
            'Authorization' => "Bearer {$newToken}"
        ]);

        $oldResponse->assertStatus(401);
        $newResponse->assertStatus(200);
    }

    /** @test */
    public function it_revokes_tokens()
    {
        $this->tokenService->revokeToken($this->apiKey, 'Testing revocation');

        $response = $this->getJson('/api/transactions', [
            'Authorization' => "Bearer {$this->validToken}"
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'error_code' => 'INVALID_API_KEY'
                 ]);
    }
}
```

**Run tests:**
```bash
php artisan test --filter ApiSecurityTest
```

---

## Security Considerations

### 1. Token Storage Security

**Server-Side (Database):**
- ✅ Store hashed tokens using bcrypt/argon2
- ✅ Never store plain tokens in database
- ✅ Use database encryption for sensitive fields
- ❌ Never log plain tokens
- ❌ Never transmit plain tokens except during generation

**Client-Side:**
- ✅ Store in environment variables or secrets manager
- ✅ Use encrypted storage for mobile apps
- ✅ Restrict file permissions for config files
- ❌ Never hardcode in source code
- ❌ Never commit to version control
- ❌ Never expose in client-side JavaScript

### 2. Token Transmission Security

**HTTPS Only:**
```php
// Force HTTPS for all API routes
Route::middleware(['https.enforce'])->prefix('api')->group(function () {
    // API routes
});
```

**HSTS Headers:**
```php
// Enforce HTTPS with HSTS
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
```

**Token Headers:**
```http
✅ Authorization: Bearer nbc_saccos_v1_...
✅ X-API-Key: nbc_saccos_v1_...

❌ ?api_key=nbc_saccos_v1_...  (Never in URL/query string)
❌ Cookie: api_token=...        (Avoid cookies for API tokens)
```

### 3. Scope Security

**Least Privilege:**
- Grant minimum necessary scopes
- Separate read/write permissions
- Use specific scopes over wildcards
- Review and reduce scopes regularly

**Dangerous Scopes:**
```php
// ❌ Avoid granting these unless absolutely necessary
'*'                    // All permissions
'admin'                // Full administrative access
'users.*.delete'       // Delete any user
'accounts.*.write'     // Modify any account
```

**Safe Scopes:**
```php
// ✅ Prefer specific, limited scopes
'accounts.read'           // Read own account
'transactions.read'       // Read transactions
'transfers.write'         // Create transfers (with validation)
'reports.export'          // Export reports (read-only)
```

### 4. Rate Limiting Security

**DDoS Protection:**
- Implement per-client rate limits
- Add IP-based rate limits for authentication endpoints
- Use exponential backoff for retries
- Consider CAPTCHA for repeated failures

**Resource Protection:**
- Limit expensive operations more strictly
- Implement separate limits for read/write
- Monitor and adjust limits based on usage patterns

**Example Rate Limit Strategy:**
```php
// Authentication endpoint: Very strict
'auth' => 5,  // 5 requests per minute

// Read endpoints: Moderate
'api.read' => 1000,  // 1000 requests per hour

// Write endpoints: Strict
'api.write' => 500,  // 500 requests per hour

// Export/report endpoints: Very strict
'api.export' => 10,  // 10 requests per hour
```

### 5. Audit Logging

**Log Everything:**
```php
// Successful authentication
Log::info('API Authentication Success', [
    'key_id' => $apiKey->id,
    'client_name' => $apiKey->client_name,
    'endpoint' => $request->path(),
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent()
]);

// Failed authentication
Log::warning('API Authentication Failed', [
    'provided_key' => substr($apiKey, 0, 8) . '...',
    'endpoint' => $request->path(),
    'ip' => $request->ip(),
    'reason' => 'Invalid API key'
]);

// Scope violations
Log::warning('API Scope Violation', [
    'key_id' => $apiKey->id,
    'required_scopes' => $requiredScopes,
    'granted_scopes' => $apiKey->scopes,
    'endpoint' => $request->path()
]);

// Rate limit violations
Log::warning('API Rate Limit Exceeded', [
    'key_id' => $apiKey->id,
    'endpoint' => $request->path(),
    'current_usage' => $currentRequests,
    'limit' => $apiKey->rate_limit
]);

// Token rotation
Log::info('API Token Rotated', [
    'old_key_id' => $oldKey->id,
    'new_key_id' => $newKey->id,
    'client_name' => $oldKey->client_name,
    'initiated_by' => auth()->user()->id ?? 'CLI'
]);
```

**Sensitive Data Handling:**
- ❌ Never log full API tokens
- ✅ Log partial tokens (first 8 chars)
- ✅ Log token IDs instead of tokens
- ✅ Use structured logging (JSON)
- ✅ Implement log retention policies

### 6. Incident Response

**Token Compromise Procedures:**

1. **Immediate Actions:**
```bash
# Revoke compromised token
php artisan api:token revoke --id=<TOKEN_ID> --reason="Security incident"

# Check for unauthorized usage
grep "key_id:<TOKEN_ID>" storage/logs/*.log

# Rotate all tokens for affected client
php artisan api:token list | grep "<CLIENT_NAME>"
# Rotate each token manually
```

2. **Investigation:**
```bash
# Review access logs
grep "key_id:<TOKEN_ID>" storage/logs/*.log | tail -n 100

# Check rate limit violations
grep "RATE_LIMIT_EXCEEDED.*key_id:<TOKEN_ID>" storage/logs/*.log

# Check scope violations
grep "INSUFFICIENT_PERMISSIONS.*key_id:<TOKEN_ID>" storage/logs/*.log
```

3. **Remediation:**
- Generate new token with updated scopes
- Notify client via secure channel
- Document incident in security log
- Review and update security procedures

**Emergency Token Revocation:**
```bash
# Revoke all tokens for a client
php artisan tinker

>>> $client = 'Compromised Client';
>>> $tokens = App\Models\ApiKey::where('client_name', $client)->get();
>>> foreach ($tokens as $token) {
...     app(App\Services\ApiTokenService::class)->revokeToken($token, 'Emergency revocation');
... }
```

### 7. Monitoring and Alerting

**Key Metrics to Monitor:**

1. **Authentication Failures**
```php
// Alert when failure rate exceeds threshold
$failureRate = cache('api_auth_failures_last_hour', 0);

if ($failureRate > 100) {
    // Send alert: Possible brute force attack
}
```

2. **Rate Limit Violations**
```php
// Alert when client consistently hits limits
$violations = cache("rate_limit_violations:key:{$keyId}", 0);

if ($violations > 10) {
    // Send alert: Client may need higher limits or is misbehaving
}
```

3. **Scope Violations**
```php
// Alert on repeated scope violations (possible attack)
$scopeViolations = cache("scope_violations:key:{$keyId}", 0);

if ($scopeViolations > 5) {
    // Send alert: Client attempting unauthorized operations
}
```

4. **Token Usage Anomalies**
```php
// Alert on unusual usage patterns
$avgDailyUsage = $apiKey->getAverageDailyUsage();
$todayUsage = $apiKey->getTodayUsage();

if ($todayUsage > $avgDailyUsage * 3) {
    // Send alert: Unusual spike in API usage
}
```

**Alerting Channels:**
- Email notifications for security events
- Slack/Teams webhooks for real-time alerts
- SMS for critical incidents
- Dashboard for metrics visualization

---

## GraphQL Security (Not Applicable)

**Status:** ✅ Not Used in Application

The MFI Management System application does not currently use GraphQL. If GraphQL is added in the future, implement these security measures:

### Required Security Controls

1. **Depth Limiting**
```php
use GraphQL\Validator\Rules\QueryDepth;

$maxDepth = 7;
$queryDepth = new QueryDepth($maxDepth);
```

2. **Complexity Limiting**
```php
use GraphQL\Validator\Rules\QueryComplexity;

$maxComplexity = 1000;
$queryComplexity = new QueryComplexity($maxComplexity);
```

3. **Query Whitelisting**
```php
// Only allow pre-approved queries
$allowedQueries = [
    'getAccount',
    'listTransactions',
    'createTransfer'
];

if (!in_array($queryName, $allowedQueries)) {
    throw new Exception('Query not allowed');
}
```

4. **Rate Limiting**
```php
// Apply same rate limiting as REST API
// Use per-client limits based on API token
```

---

## Conclusion

This API security implementation provides comprehensive protection for MFI Management System APIs through:

✅ **Token-Based Authentication** - Secure, revocable API tokens
✅ **Granular Scopes** - Fine-grained permission control
✅ **Token Lifecycle Management** - Automatic rotation and expiration
✅ **Header Validation** - Prevention of content-type attacks
✅ **Per-Client Rate Limiting** - Fair usage and DDoS protection
✅ **Audit Logging** - Complete security audit trail
✅ **CLI Management** - Easy token administration

### Key Security Benefits

- No user credentials exposed in API requests
- Instant token revocation without user impact
- Automatic token rotation prevents long-term compromise
- Scope-based permissions follow least privilege principle
- Per-client rate limits ensure fair resource usage
- Comprehensive audit logging enables security monitoring

### Next Steps

1. **Deploy to Production**
   - Generate production API tokens
   - Configure appropriate rate limits
   - Enable automatic token rotation
   - Set up monitoring and alerting

2. **Document API for Clients**
   - Provide API token request process
   - Document available scopes
   - Share rate limiting policies
   - Provide code examples

3. **Regular Security Reviews**
   - Monthly token audit
   - Quarterly scope review
   - Annual penetration testing
   - Continuous monitoring

---

**Document Version:** 1.0
**Last Updated:** 2025-10-16
**Author:** MFI Management System Security Team
**Classification:** Internal Use Only
