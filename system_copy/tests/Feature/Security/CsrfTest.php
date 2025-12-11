<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * CSRF (Cross-Site Request Forgery) Security Tests
 *
 * Tests for CSRF protection in:
 * - Forms
 * - State-changing GET requests
 * - API endpoints
 * - AJAX requests
 *
 * @package Tests\Feature\Security
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class CsrfTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * Test CSRF token is required for POST requests
     *
     * @test
     */
    public function it_requires_csrf_token_for_post_requests()
    {
        $this->actingAs($this->user);

        // Attempt POST without CSRF token
        $response = $this->post('/members', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        // Should fail with 419 (CSRF token mismatch)
        $response->assertStatus(419);
    }

    /**
     * Test CSRF token is required for PUT requests
     *
     * @test
     */
    public function it_requires_csrf_token_for_put_requests()
    {
        $this->actingAs($this->user);

        $response = $this->put('/members/1', [
            'first_name' => 'Jane',
        ]);

        $response->assertStatus(419);
    }

    /**
     * Test CSRF token is required for DELETE requests
     *
     * @test
     */
    public function it_requires_csrf_token_for_delete_requests()
    {
        $this->actingAs($this->user);

        $response = $this->delete('/members/1');

        $response->assertStatus(419);
    }

    /**
     * Test CSRF token is required for PATCH requests
     *
     * @test
     */
    public function it_requires_csrf_token_for_patch_requests()
    {
        $this->actingAs($this->user);

        $response = $this->patch('/members/1', [
            'status' => 'inactive',
        ]);

        $response->assertStatus(419);
    }

    /**
     * Test valid CSRF token allows requests
     *
     * @test
     */
    public function it_allows_requests_with_valid_csrf_token()
    {
        $this->actingAs($this->user);

        $response = $this->withSession(['_token' => 'test-token'])
            ->post('/members', [
                '_token' => 'test-token',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'phone' => '0123456789',
            ]);

        // Should not return 419
        $this->assertNotEquals(419, $response->getStatusCode());
    }

    /**
     * Test GET requests don't change state
     *
     * @test
     */
    public function it_prevents_state_changes_via_get_requests()
    {
        $this->actingAs($this->user);

        // Attempt to delete via GET
        $response = $this->get('/members/1/delete');

        // Should not be allowed or should require confirmation
        if ($response->isSuccessful()) {
            // If GET is allowed, it should only show confirmation, not perform deletion
            $content = $response->getContent();
            $this->assertStringContainsString('confirm', strtolower($content));
        } else {
            // Or it should return 405 Method Not Allowed
            $response->assertStatus(405);
        }
    }

    /**
     * Test CSRF protection in AJAX requests
     *
     * @test
     */
    public function it_protects_ajax_requests_with_csrf_token()
    {
        $this->actingAs($this->user);

        // AJAX POST without CSRF token
        $response = $this->json('POST', '/api/members', [
            'first_name' => 'John',
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        // Should require authentication or CSRF token
        $this->assertTrue(
            in_array($response->getStatusCode(), [401, 419]),
            'AJAX request should require authentication or CSRF token'
        );
    }

    /**
     * Test CSRF protection with SameSite cookies
     *
     * @test
     */
    public function it_uses_samesite_cookie_attribute()
    {
        $response = $this->get('/');

        $cookies = $response->headers->getCookies();

        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'XSRF-TOKEN' || $cookie->getName() === session()->getName()) {
                $this->assertNotNull($cookie->getSameSite());
                $this->assertContains(
                    $cookie->getSameSite(),
                    ['Lax', 'Strict'],
                    'Session cookies should have SameSite attribute'
                );
            }
        }
    }

    /**
     * Test CSRF token rotation after login
     *
     * @test
     */
    public function it_rotates_csrf_token_after_login()
    {
        // Get initial CSRF token
        $response = $this->get('/login');
        $initialToken = $response->session()->token();

        // Login
        $this->post('/login', [
            '_token' => $initialToken,
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        // Get new CSRF token
        $response = $this->get('/dashboard');
        $newToken = $response->session()->token();

        // Token should have rotated
        $this->assertNotEquals($initialToken, $newToken);
    }

    /**
     * Test CSRF protection doesn't break legitimate requests
     *
     * @test
     */
    public function it_allows_legitimate_form_submissions()
    {
        $this->actingAs($this->user);

        // Get form page
        $response = $this->get('/members/create');
        $token = $response->session()->token();

        // Submit form with token
        $response = $this->post('/members', [
            '_token' => $token,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '0123456789',
        ]);

        // Should succeed (not 419)
        $this->assertNotEquals(419, $response->getStatusCode());
    }

    /**
     * Test API endpoints use token authentication, not CSRF
     *
     * @test
     */
    public function api_endpoints_use_token_auth_not_csrf()
    {
        // API requests should use Bearer token, not CSRF
        $response = $this->json('POST', '/api/members', [
            'first_name' => 'John',
        ]);

        // Should return 401 (Unauthorized), not 419 (CSRF)
        $response->assertStatus(401);
    }

    /**
     * Test double-submit cookie pattern
     *
     * @test
     */
    public function it_implements_double_submit_cookie_pattern()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        // Check for XSRF-TOKEN cookie
        $cookies = $response->headers->getCookies();
        $hasXsrfCookie = false;

        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'XSRF-TOKEN') {
                $hasXsrfCookie = true;
                $this->assertNotEmpty($cookie->getValue());
            }
        }

        $this->assertTrue($hasXsrfCookie, 'XSRF-TOKEN cookie should be set');
    }
}
