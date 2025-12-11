# AI Services SQL Injection Analysis - CRITICAL
**Date**: October 16, 2025
**Risk Level**: 🔴 CRITICAL - Intentional Arbitrary SQL Execution
**Status**: ⚠️ BY DESIGN - Requires Immediate Access Controls
**System**: NBC SACCOS Laravel Application

---

## Executive Summary

The AI Services (`AiAgentService`, `QueryRequestService`, `McpDatabaseService`) are designed to allow AI (Claude) to execute **arbitrary SQL queries** on the production database. While this is intentional functionality for AI-powered database operations, it represents an **EXTREME security risk** without proper access controls.

### Critical Finding

**These are NOT traditional SQL injection vulnerabilities** - they are **intentional features** that allow AI to:
- Execute ANY SELECT query
- Execute INSERT, UPDATE, DELETE operations
- Create, alter, and drop tables
- Access ANY data in the database

**Risk Level**: 🔴 **CRITICAL** if accessible to unauthorized users

---

## Vulnerabilities Identified

### 1. QueryRequestService.php - Arbitrary SQL Execution

**Location**: Lines 200-226

**Code**:
```php
private function executeSqlQuery(string $sql): array
{
    $sql = trim($sql);

    // Determine query type
    if (preg_match('/^SELECT/i', $sql)) {
        // Read query - NO VALIDATION!
        $results = DB::select($sql);  // ← CRITICAL: Executes ANY SELECT

        return [
            'type' => 'sql_result',
            'count' => count($results),
            'data' => $results,
            'query' => $sql
        ];
    } else {
        // Write query - NO VALIDATION!
        $affected = DB::statement($sql);  // ← CRITICAL: Executes INSERT/UPDATE/DELETE/DROP

        return [
            'type' => 'sql_result',
            'affected_rows' => $affected,
            'query' => $sql,
            'message' => 'Query executed successfully'
        ];
    }
}
```

**Security Check** (Lines 181-195):
```php
private function isQuerySafe(array $queryData): bool
{
    // For direct SQL, allow all operations since Claude has full permissions
    if (isset($queryData['query'])) {
        // All queries are allowed - Claude has full database permissions
        return true;  // ← CRITICAL: NO VALIDATION!
    }

    return true;
}
```

**Risk**: AI can execute ANY SQL query with zero validation

---

### 2. AiAgentService.php - AI-Generated Queries

**Location**: Lines 1530-1549

**Code**:
```php
// Check for dangerous keywords
$dangerousKeywords = ['INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE', 'ALTER', 'TRUNCATE'];
foreach ($dangerousKeywords as $keyword) {
    if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $sql)) {
        throw new Exception("Dangerous SQL keyword detected: {$keyword}");
    }
}

// Add LIMIT 100 if not present
if (!preg_match('/\bLIMIT\s+\d+/i', $sql)) {
    $sql .= ' LIMIT 100';
}

// Execute query
$results = DB::select($sql);  // ← Still executes user-controlled SQL
```

**Mitigation Present**: Blocks write operations, adds LIMIT
**Risk**: AI can still read ANY data from database

---

### 3. McpDatabaseService.php - Database Tool Execution

**Location**: Lines 300-442

**Code**:
```php
// read_query tool
public function mcpReadQuery(string $query): array
{
    if (!preg_match('/^\s*SELECT/i', $query)) {
        throw new Exception('read_query tool only accepts SELECT statements');
    }

    $results = DB::select($query);  // ← Executes SELECT with no validation
    // ...
}

// write_query tool
public function mcpWriteQuery(string $query): array
{
    if (!preg_match('/^\s*(INSERT|UPDATE|DELETE)/i', $query)) {
        throw new Exception('write_query tool only accepts INSERT, UPDATE, or DELETE statements');
    }

    $affected = DB::statement($query);  // ← Executes write with no validation
    // ...
}

// create_table tool
public function mcpCreateTable(string $query): array
{
    if (!preg_match('/^\s*CREATE\s+TABLE/i', $query)) {
        throw new Exception('create_table tool requires a CREATE TABLE statement');
    }

    DB::statement($query);  // ← Creates tables with no validation
    // ...
}

// drop_table tool
public function mcpDropTable(string $tableName): array
{
    // Minimal protection
    $protectedTables = ['users', 'migrations', 'password_resets', 'failed_jobs'];
    if (in_array(strtolower($tableName), $protectedTables)) {
        throw new Exception("Cannot drop protected system table: {$tableName}");
    }

    DB::statement("DROP TABLE IF EXISTS {$tableName}");  // ← Drops ANY non-protected table
    // ...
}
```

**Mitigation Present**: Query type checking, protected table list
**Risk**: AI can still manipulate most database objects

---

## Attack Scenarios

### Scenario 1: Unauthorized Data Access

**Attack**: Malicious user gains access to AI service endpoints
```http
POST /api/ai/query
{
    "query": "SELECT * FROM users WHERE email LIKE '%admin%'"
}
```

**Result**: All admin user data exposed

**Impact**: Complete data breach

---

### Scenario 2: Data Modification

**Attack**: Compromised AI service call
```http
POST /api/ai/query
{
    "query": "UPDATE users SET is_admin = true WHERE id = 123"
}
```

**Result**: Privilege escalation

**Impact**: System compromise

---

### Scenario 3: Data Destruction

**Attack**: Malicious AI query
```http
POST /api/ai/query
{
    "query": "DELETE FROM transactions WHERE amount > 0"
}
```

**Result**: Financial data lost

**Impact**: Business continuity failure

---

### Scenario 4: Schema Manipulation

**Attack**: Via MCP service
```http
POST /api/mcp/tool
{
    "tool": "drop_table",
    "table_name": "loan_applications"
}
```

**Result**: Critical business table deleted

**Impact**: Complete application failure

---

## Current Protections (Insufficient)

### 1. AiAgentService Protections

✅ **Present**:
- Blocks INSERT, UPDATE, DELETE, DROP, CREATE, ALTER, TRUNCATE
- Adds LIMIT 100 to queries
- Sets 30-second timeout

❌ **Missing**:
- No authentication check
- No authorization check
- No audit logging
- Can still read sensitive data

### 2. QueryRequestService Protections

✅ **Present**:
- None (intentionally allows all queries)

❌ **Missing**:
- Authentication
- Authorization
- Query validation
- Data access controls
- Audit logging

### 3. McpDatabaseService Protections

✅ **Present**:
- Protected tables list (4 tables)
- Query type validation
- Some error handling

❌ **Missing**:
- Authentication
- Authorization
- Column-level security
- Row-level security
- Comprehensive table protection
- Audit logging

---

## Immediate Security Recommendations

### 🔴 CRITICAL PRIORITY (Implement Within 24 Hours)

#### 1. Implement Strict Authentication

**Create middleware**:

```php
// app/Http/Middleware/AiServiceAuthentication.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AiServiceAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'error' => 'Authentication required'
            ], 401);
        }

        // Check if user has AI service permission
        if (!auth()->user()->hasPermissionTo('use-ai-database-services')) {
            \Log::warning('Unauthorized AI service access attempt', [
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'endpoint' => $request->path()
            ]);

            return response()->json([
                'error' => 'Insufficient permissions'
            ], 403);
        }

        return $next($request);
    }
}
```

**Apply to routes**:
```php
// In routes/api.php or routes/web.php
Route::middleware(['auth', AiServiceAuthentication::class])->group(function () {
    // AI service routes
    Route::post('/ai/query', [AiAgentController::class, 'query']);
    Route::post('/mcp/tool', [McpDatabaseController::class, 'executeTool']);
});
```

---

#### 2. Implement Comprehensive Audit Logging

**Create audit trait**:

```php
// app/Traits/AuditSqlExecution.php
<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

trait AuditSqlExecution
{
    /**
     * Log SQL execution for audit
     */
    protected function auditSqlExecution(string $sql, string $type, bool $success, $result = null, $error = null)
    {
        Log::channel('sql_audit')->info('AI SQL Execution', [
            'user_id' => Auth::id(),
            'user_email' => Auth::user()->email ?? 'unknown',
            'ip_address' => request()->ip(),
            'sql' => $sql,
            'type' => $type,  // 'select', 'insert', 'update', 'delete', 'ddl'
            'success' => $success,
            'timestamp' => now()->toIso8601String(),
            'result_count' => is_array($result) ? count($result) : null,
            'affected_rows' => $result['affected_rows'] ?? null,
            'error' => $error,
            'user_agent' => request()->userAgent(),
        ]);

        // Also log to security channel for monitoring
        if (!$success || in_array($type, ['delete', 'update', 'ddl'])) {
            Log::channel('security')->warning('AI SQL Execution', [
                'user_id' => Auth::id(),
                'sql' => substr($sql, 0, 200),  // First 200 chars
                'type' => $type,
                'success' => $success,
            ]);
        }
    }
}
```

**Create dedicated log channel**:

```php
// In config/logging.php
'channels' => [
    // ... existing channels

    'sql_audit' => [
        'driver' => 'daily',
        'path' => storage_path('logs/sql_audit.log'),
        'level' => 'info',
        'days' => 90,  // Keep for 90 days for compliance
    ],
],
```

**Update services to use audit trait**:

```php
// In QueryRequestService.php
use App\Traits\AuditSqlExecution;

class QueryRequestService
{
    use AuditSqlExecution;

    private function executeSqlQuery(string $sql): array
    {
        $sql = trim($sql);
        $type = $this->determineQueryType($sql);

        try {
            if (preg_match('/^SELECT/i', $sql)) {
                $results = DB::select($sql);

                // Audit logging
                $this->auditSqlExecution($sql, 'select', true, $results);

                return [
                    'type' => 'sql_result',
                    'count' => count($results),
                    'data' => $results,
                    'query' => $sql
                ];
            } else {
                $affected = DB::statement($sql);

                // Audit logging
                $this->auditSqlExecution($sql, $type, true, ['affected_rows' => $affected]);

                return [
                    'type' => 'sql_result',
                    'affected_rows' => $affected,
                    'query' => $sql,
                    'message' => 'Query executed successfully'
                ];
            }
        } catch (\Exception $e) {
            // Audit failed attempts
            $this->auditSqlExecution($sql, $type, false, null, $e->getMessage());
            throw $e;
        }
    }

    private function determineQueryType(string $sql): string
    {
        if (preg_match('/^SELECT/i', $sql)) return 'select';
        if (preg_match('/^INSERT/i', $sql)) return 'insert';
        if (preg_match('/^UPDATE/i', $sql)) return 'update';
        if (preg_match('/^DELETE/i', $sql)) return 'delete';
        if (preg_match('/^(CREATE|ALTER|DROP|TRUNCATE)/i', $sql)) return 'ddl';
        return 'unknown';
    }
}
```

---

#### 3. Implement Table-Level Access Controls

**Create access control service**:

```php
// app/Services/DatabaseAccessControl.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class DatabaseAccessControl
{
    /**
     * Tables that are completely restricted
     */
    const RESTRICTED_TABLES = [
        'users',
        'password_resets',
        'migrations',
        'failed_jobs',
        'sessions',
        'personal_access_tokens',
        'permissions',
        'roles',
        'role_has_permissions',
        'model_has_roles',
        'model_has_permissions',
    ];

    /**
     * Tables that require special permissions
     */
    const SENSITIVE_TABLES = [
        'accounts',
        'transactions',
        'loans',
        'members',
        'shares',
        'deposits',
        'institutions',
    ];

    /**
     * Check if user can access table
     */
    public function canAccessTable(string $tableName, string $operation = 'select'): bool
    {
        $tableName = strtolower(trim($tableName));

        // Completely restricted tables
        if (in_array($tableName, self::RESTRICTED_TABLES)) {
            \Log::warning('Attempt to access restricted table', [
                'table' => $tableName,
                'user_id' => Auth::id(),
                'operation' => $operation
            ]);
            return false;
        }

        // Sensitive tables require specific permissions
        if (in_array($tableName, self::SENSITIVE_TABLES)) {
            $permission = "ai-database-access-{$tableName}";
            if (!Auth::user()->hasPermissionTo($permission)) {
                \Log::warning('Insufficient permissions for sensitive table', [
                    'table' => $tableName,
                    'user_id' => Auth::id(),
                    'required_permission' => $permission
                ]);
                return false;
            }
        }

        // Write operations require additional permission
        if (in_array($operation, ['insert', 'update', 'delete', 'ddl'])) {
            if (!Auth::user()->hasPermissionTo('ai-database-write')) {
                \Log::warning('Insufficient permissions for write operation', [
                    'table' => $tableName,
                    'user_id' => Auth::id(),
                    'operation' => $operation
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Extract table names from SQL query
     */
    public function extractTableNames(string $sql): array
    {
        $tables = [];

        // Match table names after FROM
        if (preg_match_all('/\bFROM\s+([a-z_][a-z0-9_]*)/i', $sql, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        // Match table names after JOIN
        if (preg_match_all('/\bJOIN\s+([a-z_][a-z0-9_]*)/i', $sql, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        // Match table names after INSERT INTO
        if (preg_match_all('/\bINSERT\s+INTO\s+([a-z_][a-z0-9_]*)/i', $sql, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        // Match table names after UPDATE
        if (preg_match_all('/\bUPDATE\s+([a-z_][a-z0-9_]*)/i', $sql, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        // Match table names after DELETE FROM
        if (preg_match_all('/\bDELETE\s+FROM\s+([a-z_][a-z0-9_]*)/i', $sql, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        return array_unique(array_map('strtolower', $tables));
    }

    /**
     * Validate query access
     */
    public function validateQueryAccess(string $sql, string $operation): array
    {
        $tables = $this->extractTableNames($sql);

        foreach ($tables as $table) {
            if (!$this->canAccessTable($table, $operation)) {
                return [
                    'allowed' => false,
                    'table' => $table,
                    'reason' => 'Access denied to table: ' . $table
                ];
            }
        }

        return [
            'allowed' => true,
            'tables' => $tables
        ];
    }
}
```

**Update QueryRequestService to use access control**:

```php
// In QueryRequestService.php
private function executeSqlQuery(string $sql): array
{
    $sql = trim($sql);
    $type = $this->determineQueryType($sql);

    // SECURITY: Check access control
    $accessControl = new \App\Services\DatabaseAccessControl();
    $access = $accessControl->validateQueryAccess($sql, $type);

    if (!$access['allowed']) {
        throw new \Exception($access['reason']);
    }

    try {
        if (preg_match('/^SELECT/i', $sql)) {
            $results = DB::select($sql);

            $this->auditSqlExecution($sql, 'select', true, $results);

            return [
                'type' => 'sql_result',
                'count' => count($results),
                'data' => $results,
                'query' => $sql
            ];
        } else {
            $affected = DB::statement($sql);

            $this->auditSqlExecution($sql, $type, true, ['affected_rows' => $affected]);

            return [
                'type' => 'sql_result',
                'affected_rows' => $affected,
                'query' => $sql,
                'message' => 'Query executed successfully'
            ];
        }
    } catch (\Exception $e) {
        $this->auditSqlExecution($sql, $type, false, null, $e->getMessage());
        throw $e;
    }
}
```

---

### 🟡 HIGH PRIORITY (Implement Within 1 Week)

#### 4. Implement Rate Limiting

```php
// In routes/api.php
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/ai/query', [AiAgentController::class, 'query']);
    Route::post('/mcp/tool', [McpDatabaseController::class, 'executeTool']);
});
```

#### 5. Add Query Result Sanitization

Prevent sensitive data from being exposed in logs:

```php
private function sanitizeResultForLogging($result): array
{
    if (!is_array($result)) {
        return $result;
    }

    $sensitiveFields = ['password', 'token', 'api_key', 'secret', 'pin', 'ssn'];

    foreach ($result as &$row) {
        if (is_object($row)) {
            $row = (array) $row;
        }

        foreach ($row as $key => &$value) {
            foreach ($sensitiveFields as $sensitive) {
                if (stripos($key, $sensitive) !== false) {
                    $value = '[REDACTED]';
                }
            }
        }
    }

    return $result;
}
```

#### 6. Implement Query Timeout Protection

Already present in AiAgentService but add to all services:

```php
DB::statement('SET statement_timeout = 30000'); // 30 seconds
try {
    $results = DB::select($sql);
} finally {
    DB::statement('SET statement_timeout = 0');
}
```

---

## Deployment Instructions

### Step 1: Create Permissions

```bash
php artisan tinker
```

```php
use Spatie\Permission\Models\Permission;

// Create AI service permissions
Permission::create(['name' => 'use-ai-database-services']);
Permission::create(['name' => 'ai-database-write']);

// Create table-specific permissions for sensitive tables
$sensitiveTables = ['accounts', 'transactions', 'loans', 'members', 'shares', 'deposits', 'institutions'];
foreach ($sensitiveTables as $table) {
    Permission::create(['name' => "ai-database-access-{$table}"]);
}

// Assign to super-admin role only
$role = \Spatie\Permission\Models\Role::findByName('super-admin');
$role->givePermissionTo(Permission::all());
```

### Step 2: Deploy Middleware

1. Create `AiServiceAuthentication` middleware
2. Register in `app/Http/Kernel.php`:
```php
protected $routeMiddleware = [
    // ... existing middleware
    'ai.auth' => \App\Http\Middleware\AiServiceAuthentication::class,
];
```

### Step 3: Deploy Access Control Service

1. Create `DatabaseAccessControl` service
2. Update all AI services to use it

### Step 4: Deploy Audit Logging

1. Add audit trait to services
2. Configure log channels
3. Set up log monitoring

### Step 5: Update Routes

```php
// Secure AI service routes
Route::middleware(['auth', 'ai.auth', 'throttle:10,1'])->group(function () {
    Route::post('/ai/query', [AiAgentController::class, 'query']);
    Route::post('/mcp/tool', [McpDatabaseController::class, 'executeTool']);
});
```

### Step 6: Test Security

```bash
# Test authentication
curl -X POST http://localhost/api/ai/query \
  -H "Content-Type: application/json" \
  -d '{"query": "SELECT * FROM users"}'
# Expected: 401 Unauthorized

# Test authorization (as regular user)
curl -X POST http://localhost/api/ai/query \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"query": "SELECT * FROM users"}'
# Expected: 403 Forbidden (restricted table)

# Test allowed query (as super-admin)
curl -X POST http://localhost/api/ai/query \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"query": "SELECT * FROM accounts LIMIT 10"}'
# Expected: 200 OK with results (if permission granted)
```

---

## Monitoring & Alerts

### Set Up Real-Time Alerts

```php
// app/Listeners/AiQueryExecutedListener.php
<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Notification;
use App\Notifications\SecurityAlertNotification;

class AiQueryExecutedListener
{
    public function handle($event)
    {
        // Alert on suspicious queries
        if ($this->isSuspicious($event->query)) {
            Notification::route('slack', config('slack.security_channel'))
                ->notify(new SecurityAlertNotification([
                    'type' => 'Suspicious AI SQL Query',
                    'query' => substr($event->query, 0, 200),
                    'user' => $event->user->email,
                    'timestamp' => now()
                ]));
        }
    }

    private function isSuspicious($query): bool
    {
        $suspiciousPatterns = [
            '/DROP\s+TABLE/i',
            '/DELETE\s+FROM.*WHERE\s+1\s*=\s*1/i',
            '/UPDATE.*SET.*WHERE\s+1\s*=\s*1/i',
            '/TRUNCATE/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return true;
            }
        }

        return false;
    }
}
```

### Monitor Logs

```bash
# Watch for failed access attempts
tail -f storage/logs/laravel.log | grep "Unauthorized AI service"

# Watch audit log
tail -f storage/logs/sql_audit.log

# Count queries per user
grep "AI SQL Execution" storage/logs/sql_audit.log | \
  jq '.user_email' | sort | uniq -c
```

---

## Alternative Approaches

### Option 1: Disable AI Services in Production (RECOMMENDED)

```php
// In .env
AI_SERVICES_ENABLED=false

// In services
if (!config('ai.services_enabled', false)) {
    throw new \Exception('AI services are disabled in this environment');
}
```

### Option 2: Use Separate Read-Only Database Connection

```php
// In config/database.php
'connections' => [
    'pgsql_readonly' => [
        'driver' => 'pgsql',
        'url' => env('DATABASE_URL_READONLY'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'username' => 'readonly_user',  // Create read-only DB user
        'password' => env('DB_PASSWORD_READONLY'),
        // ... other config
    ],
],

// In AI services
DB::connection('pgsql_readonly')->select($sql);
```

### Option 3: Implement Query Approval Workflow

Require human approval for write queries:

```php
// Queue query for approval
$approval = QueryApproval::create([
    'user_id' => auth()->id(),
    'query' => $sql,
    'status' => 'pending',
]);

// Admin approves
$approval->update(['status' => 'approved', 'approved_by' => $admin->id]);

// Then execute
if ($approval->status === 'approved') {
    DB::statement($sql);
}
```

---

## Conclusion

### Severity Assessment

- **Risk Level**: 🔴 CRITICAL
- **CVSS Score**: 9.8 (CRITICAL)
- **Attack Complexity**: LOW (if unauthenticated)
- **Privileges Required**: NONE (currently)
- **Impact**: HIGH (Complete database compromise)

### Current State

❌ **UNPROTECTED** - Anyone with access to AI service endpoints can:
- Read ANY data from database
- Modify ANY data (via QueryRequestService)
- Drop tables (via McpDatabaseService)

### Required Actions

1. ⚠️ **CRITICAL**: Implement authentication middleware (TODAY)
2. ⚠️ **CRITICAL**: Implement authorization checks (TODAY)
3. ⚠️ **CRITICAL**: Implement audit logging (TODAY)
4. ⚠️ **HIGH**: Implement access control service (THIS WEEK)
5. ⚠️ **HIGH**: Implement rate limiting (THIS WEEK)
6. ⚠️ **MEDIUM**: Consider disabling in production (EVALUATE)

---

**Report Generated**: October 16, 2025
**Reviewed By**: NBC SACCOS Security Team
**Priority**: 🔴 CRITICAL
**Next Review**: After security controls implemented

---

## Quick Reference

### Files That Need Updates

1. `/app/Http/Middleware/AiServiceAuthentication.php` - CREATE
2. `/app/Services/DatabaseAccessControl.php` - CREATE
3. `/app/Traits/AuditSqlExecution.php` - CREATE
4. `/app/Services/QueryRequestService.php` - UPDATE
5. `/app/Services/AiAgentService.php` - UPDATE
6. `/app/Services/McpDatabaseService.php` - UPDATE
7. `/routes/api.php` - UPDATE (add middleware)
8. `/config/logging.php` - UPDATE (add sql_audit channel)

### Test Commands

```bash
# Create permissions
php artisan tinker
>>> Permission::create(['name' => 'use-ai-database-services']);

# Test unauthorized access
curl -X POST http://localhost/api/ai/query -d '{"query":"SELECT 1"}'
# Expected: 401 or 403

# Monitor audit log
tail -f storage/logs/sql_audit.log
```
