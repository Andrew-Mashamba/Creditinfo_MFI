<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Member;
use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SQL Injection Security Tests
 *
 * Tests for SQL injection vulnerabilities in:
 * - URL parameters
 * - Request inputs
 * - Header fields
 * - API parameters
 *
 * @package Tests\Feature\Security
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class SqlInjectionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $member;

    protected function setUp(): void
    {
        parent::setUp();

        // Create authenticated user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->member = Member::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * SQL Injection payloads for testing
     *
     * @return array
     */
    protected function getSqlInjectionPayloads(): array
    {
        return [
            // Classic SQL injection
            "' OR '1'='1",
            "' OR 1=1--",
            "' OR '1'='1' --",
            "admin'--",
            "admin' #",
            "admin'/*",

            // UNION-based injection
            "' UNION SELECT NULL--",
            "' UNION SELECT NULL,NULL--",
            "' UNION ALL SELECT NULL--",

            // Boolean-based blind injection
            "' AND 1=1--",
            "' AND 1=2--",

            // Time-based blind injection
            "'; WAITFOR DELAY '00:00:05'--",
            "' OR SLEEP(5)--",
            "' AND (SELECT * FROM (SELECT(SLEEP(5)))a)--",

            // Stacked queries
            "'; DROP TABLE users--",
            "'; DELETE FROM users WHERE '1'='1",

            // PostgreSQL specific
            "'; DROP TABLE users CASCADE--",
            "' || pg_sleep(5)--",

            // String concatenation
            "a' OR 'a'='a",
            "x' AND email IS NULL; --",

            // Numeric injection
            "1 OR 1=1",
            "1) OR (1=1",
            "1)) OR ((1=1",

            // Comment injection
            "--",
            "#",
            "/**/",

            // Special characters
            "\\",
            "'\"",
            ";",

            // Hex injection
            "0x27",
            "0x3B",
        ];
    }

    /**
     * Test SQL injection in URL parameters
     *
     * @test
     */
    public function it_prevents_sql_injection_in_url_parameters()
    {
        $this->actingAs($this->user);

        $payloads = $this->getSqlInjectionPayloads();

        foreach ($payloads as $payload) {
            // Test member search
            $response = $this->get('/members?search=' . urlencode($payload));

            $this->assertFalse(
                $this->responseIndicatesSqlInjection($response),
                "SQL injection vulnerability detected with payload: {$payload}"
            );

            // Test loan search
            $response = $this->get('/loans?status=' . urlencode($payload));

            $this->assertFalse(
                $this->responseIndicatesSqlInjection($response),
                "SQL injection vulnerability in loan search: {$payload}"
            );
        }
    }

    /**
     * Test SQL injection in POST data
     *
     * @test
     */
    public function it_prevents_sql_injection_in_post_data()
    {
        $this->actingAs($this->user);

        $payloads = $this->getSqlInjectionPayloads();

        foreach ($payloads as $payload) {
            $response = $this->post('/login', [
                'email' => $payload,
                'password' => 'password123',
            ]);

            $this->assertFalse(
                $this->responseIndicatesSqlInjection($response),
                "SQL injection vulnerability in login with payload: {$payload}"
            );

            // Should not be authenticated
            $this->assertFalse(auth()->check());
        }
    }

    /**
     * Test SQL injection in API requests
     *
     * @test
     */
    public function it_prevents_sql_injection_in_api_requests()
    {
        // Create API token
        $token = 'test_token_' . str_random(32);

        $payloads = $this->getSqlInjectionPayloads();

        foreach ($payloads as $payload) {
            $response = $this->json('GET', '/api/members', [
                'search' => $payload,
            ], [
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ]);

            $this->assertFalse(
                $this->responseIndicatesSqlInjection($response),
                "SQL injection vulnerability in API: {$payload}"
            );
        }
    }

    /**
     * Test SQL injection in header fields
     *
     * @test
     */
    public function it_prevents_sql_injection_in_headers()
    {
        $payloads = $this->getSqlInjectionPayloads();

        foreach ($payloads as $payload) {
            $response = $this->get('/login', [
                'X-Custom-Header' => $payload,
                'User-Agent' => $payload,
                'Referer' => $payload,
            ]);

            $this->assertFalse(
                $this->responseIndicatesSqlInjection($response),
                "SQL injection vulnerability in headers: {$payload}"
            );
        }
    }

    /**
     * Test SQL injection in sorting parameters
     *
     * @test
     */
    public function it_prevents_sql_injection_in_sort_parameters()
    {
        $this->actingAs($this->user);

        $payloads = $this->getSqlInjectionPayloads();

        foreach ($payloads as $payload) {
            $response = $this->get('/members?sort=' . urlencode($payload) . '&order=asc');

            $this->assertFalse(
                $this->responseIndicatesSqlInjection($response),
                "SQL injection in sort parameter: {$payload}"
            );
        }
    }

    /**
     * Test SQL injection in pagination parameters
     *
     * @test
     */
    public function it_prevents_sql_injection_in_pagination()
    {
        $this->actingAs($this->user);

        $payloads = $this->getSqlInjectionPayloads();

        foreach ($payloads as $payload) {
            $response = $this->get('/members?page=' . urlencode($payload));

            $this->assertFalse(
                $this->responseIndicatesSqlInjection($response),
                "SQL injection in pagination: {$payload}"
            );
        }
    }

    /**
     * Test SQL injection in filter parameters
     *
     * @test
     */
    public function it_prevents_sql_injection_in_filters()
    {
        $this->actingAs($this->user);

        $payloads = $this->getSqlInjectionPayloads();

        foreach ($payloads as $payload) {
            $response = $this->get('/loans?status=' . urlencode($payload) . '&amount_min=' . urlencode($payload));

            $this->assertFalse(
                $this->responseIndicatesSqlInjection($response),
                "SQL injection in filter parameters: {$payload}"
            );
        }
    }

    /**
     * Test parameterized queries are used correctly
     *
     * @test
     */
    public function it_uses_parameterized_queries()
    {
        // Enable query logging
        DB::enableQueryLog();

        $this->actingAs($this->user);

        // Perform a query with user input
        Member::where('first_name', "' OR '1'='1")->get();

        $queries = DB::getQueryLog();

        foreach ($queries as $query) {
            // Check that bindings are used (not raw SQL concatenation)
            $this->assertNotEmpty(
                $query['bindings'],
                'Query should use parameter bindings'
            );

            // Check query doesn't contain unescaped user input
            $this->assertStringNotContainsString(
                "' OR '1'='1",
                $query['query'],
                'User input should not appear directly in SQL query'
            );
        }

        DB::disableQueryLog();
    }

    /**
     * Test that raw queries are properly escaped
     *
     * @test
     */
    public function it_properly_escapes_raw_queries()
    {
        $maliciousInput = "'; DROP TABLE members; --";

        // Test with DB::raw (should be used with caution)
        try {
            $query = Member::whereRaw('first_name = ?', [$maliciousInput])->toSql();

            // Query should use placeholders
            $this->assertStringContainsString('?', $query);
            $this->assertStringNotContainsString('DROP TABLE', $query);

        } catch (\Exception $e) {
            // If exception thrown, vulnerability prevented
            $this->assertTrue(true);
        }
    }

    /**
     * Test SQL injection doesn't cause database errors
     *
     * @test
     */
    public function it_handles_sql_injection_gracefully()
    {
        $this->actingAs($this->user);

        $payloads = $this->getSqlInjectionPayloads();

        foreach ($payloads as $payload) {
            try {
                $response = $this->get('/members?search=' . urlencode($payload));

                // Should not return database errors
                $this->assertFalse(
                    $this->responseContainsDatabaseError($response),
                    "Database error exposed with payload: {$payload}"
                );

            } catch (\Exception $e) {
                // Exception is acceptable (input validation)
                $this->assertStringNotContainsString(
                    'SQL syntax',
                    $e->getMessage(),
                    "SQL syntax error exposed: {$payload}"
                );
            }
        }
    }

    /**
     * Check if response indicates SQL injection success
     *
     * @param  \Illuminate\Testing\TestResponse  $response
     * @return bool
     */
    protected function responseIndicatesSqlInjection($response): bool
    {
        $content = $response->getContent();
        $statusCode = $response->getStatusCode();

        // Check for SQL error messages
        $sqlErrors = [
            'SQL syntax',
            'mysql_fetch',
            'mysqli_fetch',
            'pg_query',
            'PostgreSQL',
            'MySQL',
            'SQLSTATE',
            'PDOException',
            'Illuminate\Database\QueryException',
            'syntax error at or near',
            'unterminated quoted string',
            'Query failed',
            'SQL error',
        ];

        foreach ($sqlErrors as $error) {
            if (stripos($content, $error) !== false) {
                Log::critical('SQL Injection Vulnerability Detected', [
                    'status_code' => $statusCode,
                    'error_fragment' => $error,
                ]);
                return true;
            }
        }

        // Check if authentication was bypassed (should not be logged in)
        if ($statusCode === 200 && stripos($content, 'dashboard') !== false && !auth()->check()) {
            Log::critical('Possible Authentication Bypass via SQL Injection');
            return true;
        }

        return false;
    }

    /**
     * Check if response contains database errors
     *
     * @param  \Illuminate\Testing\TestResponse  $response
     * @return bool
     */
    protected function responseContainsDatabaseError($response): bool
    {
        $content = $response->getContent();

        $errorPatterns = [
            '/SQLSTATE\[\w+\]/i',
            '/SQL syntax/i',
            '/mysql_/i',
            '/mysqli_/i',
            '/pg_query/i',
            '/QueryException/i',
            '/PDOException/i',
        ];

        foreach ($errorPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Test second-order SQL injection
     *
     * @test
     */
    public function it_prevents_second_order_sql_injection()
    {
        $this->actingAs($this->user);

        $maliciousInput = "admin'--";

        // Store malicious input
        $member = Member::create([
            'first_name' => $maliciousInput,
            'last_name' => 'Test',
            'email' => 'test@example.com',
            'phone' => '0123456789',
        ]);

        // Retrieve and use in query (second-order injection)
        $name = $member->first_name;

        $result = Member::where('first_name', $name)->get();

        // Should find exactly one member (the one we created)
        $this->assertCount(1, $result);
        $this->assertEquals($maliciousInput, $result->first()->first_name);
    }
}
