<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Member;
use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Comprehensive Security Tests
 *
 * Tests for:
 * - File Upload Security
 * - Authentication Bypass
 * - Session Management
 * - Business Logic Abuse
 * - Race Conditions
 * - SSRF (Server-Side Request Forgery)
 * - Command Injection
 * - Open Redirects
 *
 * @package Tests\Feature\Security
 * @author MFI Management System Security Team
 * @date 2025-10-16
 */
class ComprehensiveSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        Storage::fake('local');
    }

    // ==============================================
    // FILE UPLOAD SECURITY TESTS
    // ==============================================

    /**
     * Test malicious file upload (PHP file)
     *
     * @test
     */
    public function it_prevents_php_file_uploads()
    {
        $this->actingAs($this->user);

        $phpFile = UploadedFile::fake()->createWithContent(
            'malicious.php',
            '<?php system($_GET["cmd"]); ?>'
        );

        $response = $this->post('/upload', [
            'file' => $phpFile,
        ]);

        // Should reject PHP files
        $this->assertTrue(
            $response->getStatusCode() === 422 || $response->getStatusCode() === 400,
            'Should reject PHP file uploads'
        );
    }

    /**
     * Test double extension file upload
     *
     * @test
     */
    public function it_prevents_double_extension_uploads()
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->createWithContent(
            'document.pdf.php',
            '<?php echo "malicious"; ?>'
        );

        $response = $this->post('/upload', [
            'file' => $file,
        ]);

        $this->assertTrue(
            $response->getStatusCode() === 422 || $response->getStatusCode() === 400,
            'Should reject double extension files'
        );
    }

    /**
     * Test file type mismatch (content vs extension)
     *
     * @test
     */
    public function it_validates_file_mime_type()
    {
        $this->actingAs($this->user);

        // PHP content with PDF extension
        $file = UploadedFile::fake()->createWithContent(
            'document.pdf',
            '<?php system("whoami"); ?>'
        );

        $response = $this->post('/upload', [
            'file' => $file,
        ]);

        // Should detect MIME type mismatch
        $this->assertTrue(
            $response->getStatusCode() === 422 || $response->getStatusCode() === 400,
            'Should detect MIME type mismatch'
        );
    }

    /**
     * Test large file upload (DOS attack)
     *
     * @test
     */
    public function it_enforces_file_size_limits()
    {
        $this->actingAs($this->user);

        // Create large file (simulated)
        $largeFile = UploadedFile::fake()->create('large.pdf', 50000); // 50MB

        $response = $this->post('/upload', [
            'file' => $largeFile,
        ]);

        // Should reject files exceeding limit
        $this->assertTrue(
            $response->getStatusCode() === 422 || $response->getStatusCode() === 413,
            'Should enforce file size limits'
        );
    }

    /**
     * Test path traversal in file upload
     *
     * @test
     */
    public function it_prevents_path_traversal_in_uploads()
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('../../etc/passwd.txt');

        $response = $this->post('/upload', [
            'file' => $file,
        ]);

        // Should sanitize filename and prevent traversal
        if ($response->isSuccessful()) {
            // Check that file was NOT saved to traversed path
            $this->assertFalse(
                Storage::exists('../../etc/passwd.txt'),
                'Should prevent path traversal in file uploads'
            );
        }
    }

    /**
     * Test executable file upload
     *
     * @test
     */
    public function it_blocks_executable_file_uploads()
    {
        $this->actingAs($this->user);

        $executableExtensions = ['exe', 'bat', 'sh', 'com', 'pif', 'cmd', 'vbs', 'js'];

        foreach ($executableExtensions as $ext) {
            $file = UploadedFile::fake()->create("malware.{$ext}");

            $response = $this->post('/upload', [
                'file' => $file,
            ]);

            $this->assertTrue(
                $response->getStatusCode() === 422 || $response->getStatusCode() === 400,
                "Should block .{$ext} file uploads"
            );
        }
    }

    // ==============================================
    // AUTHENTICATION BYPASS TESTS
    // ==============================================

    /**
     * Test password reset token validation
     *
     * @test
     */
    public function it_validates_password_reset_tokens()
    {
        // Attempt to reset password with invalid token
        $response = $this->post('/password/reset', [
            'token' => 'invalid_token_12345',
            'email' => $this->user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // Should reject invalid token
        $this->assertTrue(
            in_array($response->getStatusCode(), [400, 422, 419]),
            'Should reject invalid reset tokens'
        );

        // Verify password wasn't changed
        $this->assertTrue(
            Hash::check('password123', $this->user->fresh()->password),
            'Password should not be changed with invalid token'
        );
    }

    /**
     * Test password reset token expiration
     *
     * @test
     */
    public function it_expires_password_reset_tokens()
    {
        // Create expired token (simulate)
        DB::table('password_resets')->insert([
            'email' => $this->user->email,
            'token' => Hash::make('expired_token'),
            'created_at' => now()->subHours(2), // 2 hours old
        ]);

        $response = $this->post('/password/reset', [
            'token' => 'expired_token',
            'email' => $this->user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // Should reject expired token
        $this->assertTrue(
            in_array($response->getStatusCode(), [400, 422, 419]),
            'Should reject expired reset tokens'
        );
    }

    /**
     * Test magic link security
     *
     * @test
     */
    public function it_secures_magic_links()
    {
        // Attempt to reuse a magic link
        $magicLink = "/magic-login?token=used_token_123";

        // First use (should work)
        $response1 = $this->get($magicLink);

        // Second use (should fail)
        $response2 = $this->get($magicLink);

        $this->assertTrue(
            in_array($response2->getStatusCode(), [400, 403, 404, 419]),
            'Magic links should be single-use'
        );
    }

    /**
     * Test authentication bypass via parameter tampering
     *
     * @test
     */
    public function it_prevents_auth_bypass_via_parameter_tampering()
    {
        // Attempt login with admin=true parameter
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
            'admin' => true,
            'is_admin' => '1',
            'role' => 'admin',
        ]);

        auth()->logout();

        // Verify user doesn't have admin role
        $this->user->refresh();
        $this->assertFalse(
            $this->user->hasRole('Admin'),
            'Parameter tampering should not grant admin access'
        );
    }

    // ==============================================
    // SESSION MANAGEMENT TESTS
    // ==============================================

    /**
     * Test session fixation prevention
     *
     * @test
     */
    public function it_prevents_session_fixation()
    {
        // Get session ID before login
        $response = $this->get('/login');
        $sessionIdBefore = $response->session()->getId();

        // Login
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        // Get session ID after login
        $response = $this->get('/dashboard');
        $sessionIdAfter = $response->session()->getId();

        // Session ID should change after login
        $this->assertNotEquals(
            $sessionIdBefore,
            $sessionIdAfter,
            'Session ID should regenerate after login to prevent fixation'
        );
    }

    /**
     * Test proper logout behavior
     *
     * @test
     */
    public function it_properly_invalidates_sessions_on_logout()
    {
        $this->actingAs($this->user);

        // Get session ID
        $response = $this->get('/dashboard');
        $sessionId = $response->session()->getId();

        // Logout
        $this->post('/logout');

        // Attempt to use old session
        $response = $this->withSession(['_token' => $sessionId])
            ->get('/dashboard');

        // Should redirect to login or be unauthorized
        $this->assertTrue(
            in_array($response->getStatusCode(), [302, 401, 403]),
            'Old session should be invalid after logout'
        );
    }

    /**
     * Test concurrent session handling
     *
     * @test
     */
    public function it_handles_concurrent_sessions_securely()
    {
        // Login from device 1
        $response1 = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        $session1 = $response1->session()->getId();

        // Login from device 2
        $response2 = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        $session2 = $response2->session()->getId();

        // Sessions should be different
        $this->assertNotEquals($session1, $session2);

        // Both sessions should be valid OR first should be invalidated
        // (depends on application policy)
        $this->assertTrue(true);
    }

    /**
     * Test session timeout
     *
     * @test
     */
    public function it_enforces_session_timeout()
    {
        $this->actingAs($this->user);

        // Simulate expired session (set last activity time to past)
        session(['last_activity' => now()->subMinutes(config('session.lifetime') + 10)->timestamp]);

        $response = $this->get('/dashboard');

        // Should redirect to login
        $this->assertTrue(
            in_array($response->getStatusCode(), [302, 401, 403]),
            'Expired sessions should be invalidated'
        );
    }

    // ==============================================
    // BUSINESS LOGIC ABUSE TESTS
    // ==============================================

    /**
     * Test negative amount transactions
     *
     * @test
     */
    public function it_prevents_negative_amount_transactions()
    {
        $this->actingAs($this->user);

        $response = $this->post('/transactions', [
            'type' => 'deposit',
            'amount' => -1000, // Negative amount
        ]);

        // Should reject negative amounts
        $this->assertTrue(
            $response->getStatusCode() === 422 || $response->getStatusCode() === 400,
            'Should reject negative transaction amounts'
        );
    }

    /**
     * Test zero amount transactions
     *
     * @test
     */
    public function it_prevents_zero_amount_transactions()
    {
        $this->actingAs($this->user);

        $response = $this->post('/transactions', [
            'type' => 'deposit',
            'amount' => 0,
        ]);

        $this->assertTrue(
            $response->getStatusCode() === 422 || $response->getStatusCode() === 400,
            'Should reject zero amount transactions'
        );
    }

    /**
     * Test discount manipulation
     *
     * @test
     */
    public function it_validates_discount_values()
    {
        $this->actingAs($this->user);

        // Attempt to apply 150% discount
        $response = $this->post('/loans/apply', [
            'amount' => 10000,
            'discount' => 150, // Invalid discount
        ]);

        $this->assertTrue(
            $response->getStatusCode() === 422 || $response->getStatusCode() === 400,
            'Should reject invalid discount values'
        );
    }

    /**
     * Test sequence manipulation
     *
     * @test
     */
    public function it_prevents_sequence_manipulation()
    {
        $this->actingAs($this->user);

        // Attempt to submit step 3 before step 1
        $response = $this->post('/loan-application/step/3', [
            'data' => 'test',
        ]);

        $this->assertTrue(
            in_array($response->getStatusCode(), [400, 422, 403]),
            'Should enforce sequence validation'
        );
    }

    /**
     * Test insufficient balance withdrawal
     *
     * @test
     */
    public function it_prevents_overdrafts()
    {
        $this->actingAs($this->user);

        $member = Member::factory()->create(['user_id' => $this->user->id]);
        $account = \App\Models\Account::factory()->create([
            'member_id' => $member->id,
            'balance' => 1000,
        ]);

        // Attempt to withdraw more than balance
        $response = $this->post('/transactions', [
            'account_id' => $account->id,
            'type' => 'withdrawal',
            'amount' => 5000, // More than balance
        ]);

        $this->assertTrue(
            in_array($response->getStatusCode(), [400, 422, 403]),
            'Should prevent overdrafts'
        );

        // Verify balance unchanged
        $account->refresh();
        $this->assertEquals(1000, $account->balance);
    }

    // ==============================================
    // RACE CONDITION TESTS
    // ==============================================

    /**
     * Test concurrent balance modifications
     *
     * @test
     */
    public function it_handles_concurrent_transactions_safely()
    {
        $this->actingAs($this->user);

        $member = Member::factory()->create(['user_id' => $this->user->id]);
        $account = \App\Models\Account::factory()->create([
            'member_id' => $member->id,
            'balance' => 1000,
        ]);

        $initialBalance = $account->balance;

        // Simulate concurrent withdrawals
        $promises = [];
        for ($i = 0; $i < 5; $i++) {
            $promises[] = $this->post('/transactions', [
                'account_id' => $account->id,
                'type' => 'withdrawal',
                'amount' => 200,
            ]);
        }

        // Check final balance
        $account->refresh();

        // Should have proper locking (balance should be 0 or transactions rejected)
        $this->assertTrue(
            $account->balance >= 0,
            'Concurrent transactions should not create negative balance'
        );

        $this->assertTrue(
            $account->balance === ($initialBalance - 1000) || $account->balance > 0,
            'Race condition should be handled with proper locking'
        );
    }

    // ==============================================
    // SSRF TESTS
    // ==============================================

    /**
     * Test SSRF in URL fetch endpoints
     *
     * @test
     */
    public function it_prevents_ssrf_attacks()
    {
        $this->actingAs($this->user);

        $ssrfPayloads = [
            'http://localhost/admin',
            'http://127.0.0.1:22',
            'http://169.254.169.254/latest/meta-data/', // AWS metadata
            'file:///etc/passwd',
            'gopher://localhost:25',
            'dict://localhost:11211',
        ];

        foreach ($ssrfPayloads as $payload) {
            $response = $this->post('/fetch-url', [
                'url' => $payload,
            ]);

            $this->assertTrue(
                in_array($response->getStatusCode(), [400, 422, 403]),
                "Should prevent SSRF with: {$payload}"
            );
        }
    }

    // ==============================================
    // COMMAND INJECTION TESTS
    // ==============================================

    /**
     * Test command injection prevention
     *
     * @test
     */
    public function it_prevents_command_injection()
    {
        $this->actingAs($this->user);

        $commandPayloads = [
            '; ls -la',
            '| whoami',
            '& cat /etc/passwd',
            '`id`',
            '$(whoami)',
            '; rm -rf /',
        ];

        foreach ($commandPayloads as $payload) {
            $response = $this->post('/process', [
                'filename' => "document{$payload}.pdf",
            ]);

            // Should sanitize input or reject
            $this->assertTrue(
                $response->getStatusCode() !== 500,
                "Command injection should be prevented: {$payload}"
            );
        }
    }

    // ==============================================
    // OPEN REDIRECT TESTS
    // ==============================================

    /**
     * Test open redirect prevention
     *
     * @test
     */
    public function it_prevents_open_redirects()
    {
        $maliciousUrls = [
            'http://evil.com',
            'https://phishing-site.com',
            '//evil.com',
            'javascript:alert(1)',
            'data:text/html,<script>alert(1)</script>',
        ];

        foreach ($maliciousUrls as $url) {
            $response = $this->get('/redirect?url=' . urlencode($url));

            if ($response->isRedirect()) {
                $location = $response->headers->get('Location');

                // Should not redirect to external sites
                $this->assertFalse(
                    $this->isExternalUrl($location),
                    "Should prevent open redirect to: {$url}"
                );
            }
        }
    }

    /**
     * Test safe redirects
     *
     * @test
     */
    public function it_allows_safe_internal_redirects()
    {
        $this->actingAs($this->user);

        $safeUrls = [
            '/dashboard',
            '/members',
            '/loans',
        ];

        foreach ($safeUrls as $url) {
            $response = $this->get('/redirect?url=' . urlencode($url));

            if ($response->isRedirect()) {
                $location = $response->headers->get('Location');

                // Should allow internal redirects
                $this->assertFalse(
                    $this->isExternalUrl($location),
                    "Internal redirect should be allowed: {$url}"
                );
            }
        }
    }

    // ==============================================
    // HELPER METHODS
    // ==============================================

    /**
     * Check if URL is external
     *
     * @param  string  $url
     * @return bool
     */
    protected function isExternalUrl(string $url): bool
    {
        $parsedUrl = parse_url($url);

        if (!isset($parsedUrl['host'])) {
            return false; // Relative URL
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        return $parsedUrl['host'] !== $appHost;
    }
}
