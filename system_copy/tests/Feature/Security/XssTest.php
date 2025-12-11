<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Member;
use App\Models\Comment;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

/**
 * XSS (Cross-Site Scripting) Security Tests
 *
 * Tests for XSS vulnerabilities:
 * - Reflected XSS
 * - Stored XSS
 * - DOM-based XSS
 * - JSON endpoint XSS
 * - Template injection
 *
 * @package Tests\Feature\Security
 * @author NBC SACCOS Security Team
 * @date 2025-10-16
 */
class XssTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * XSS payloads for testing
     *
     * @return array
     */
    protected function getXssPayloads(): array
    {
        return [
            // Basic XSS
            '<script>alert("XSS")</script>',
            '<script>alert(document.cookie)</script>',
            '<script>alert(String.fromCharCode(88,83,83))</script>',

            // IMG tag XSS
            '<img src=x onerror=alert("XSS")>',
            '<img src="javascript:alert(\'XSS\')">',
            '<img src=x onload=alert("XSS")>',
            '<img/src="x"/onerror=alert("XSS")>',

            // Event handler XSS
            '<body onload=alert("XSS")>',
            '<div onmouseover=alert("XSS")>',
            '<input onfocus=alert("XSS") autofocus>',
            '<select onfocus=alert("XSS") autofocus>',
            '<textarea onfocus=alert("XSS") autofocus>',

            // SVG XSS
            '<svg onload=alert("XSS")>',
            '<svg><script>alert("XSS")</script></svg>',
            '<svg><animate onbegin=alert("XSS") attributeName=x>',

            // iFrame XSS
            '<iframe src="javascript:alert(\'XSS\')">',
            '<iframe srcdoc="<script>alert(\'XSS\')</script>">',

            // Link XSS
            '<a href="javascript:alert(\'XSS\')">Click</a>',
            '<a href="data:text/html,<script>alert(\'XSS\')</script>">Click</a>',

            // Object/Embed XSS
            '<object data="javascript:alert(\'XSS\')">',
            '<embed src="javascript:alert(\'XSS\')">',

            // Form XSS
            '<form action="javascript:alert(\'XSS\')"><input type="submit"></form>',

            // Meta tag XSS
            '<meta http-equiv="refresh" content="0;url=javascript:alert(\'XSS\')">',

            // Base tag XSS
            '<base href="javascript:alert(\'XSS\')//">',

            // Obfuscated XSS
            '<scr<script>ipt>alert("XSS")</scr</script>ipt>',
            '<<SCRIPT>alert("XSS");//<</SCRIPT>',
            '<SCRIPT SRC=http://evil.com/xss.js></SCRIPT>',

            // Unicode/Encoding XSS
            '<script>alert&#40"XSS"&#41</script>',
            '<script>alert&#x28"XSS"&#x29</script>',
            '<IMG SRC=&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#88;&#83;&#83;&#39;&#41;>',

            // HTML5 XSS
            '<input type="image" src=x onerror=alert("XSS")>',
            '<video src=x onerror=alert("XSS")>',
            '<audio src=x onerror=alert("XSS")>',
            '<details open ontoggle=alert("XSS")>',

            // Style XSS
            '<style>body{background:url("javascript:alert(\'XSS\')")}</style>',
            '<div style="background-image:url(javascript:alert(\'XSS\'))">',

            // JavaScript protocol
            'javascript:alert("XSS")',
            'javascript&#58;alert("XSS")',

            // Data URI XSS
            'data:text/html,<script>alert("XSS")</script>',
            'data:text/html;base64,PHNjcmlwdD5hbGVydCgnWFNTJyk8L3NjcmlwdD4=',

            // Template injection
            '{{7*7}}',
            '${7*7}',
            '<%= 7*7 %>',
            '{{constructor.constructor("alert(1)")()}}',
        ];
    }

    /**
     * Test reflected XSS in GET parameters
     *
     * @test
     */
    public function it_prevents_reflected_xss_in_get_parameters()
    {
        $this->actingAs($this->user);

        $payloads = $this->getXssPayloads();

        foreach ($payloads as $payload) {
            $response = $this->get('/search?q=' . urlencode($payload));

            $this->assertFalse(
                $this->responseContainsUnescapedXss($response, $payload),
                "Reflected XSS vulnerability with payload: {$payload}"
            );
        }
    }

    /**
     * Test reflected XSS in POST data
     *
     * @test
     */
    public function it_prevents_reflected_xss_in_post_data()
    {
        $this->actingAs($this->user);

        $payloads = $this->getXssPayloads();

        foreach ($payloads as $payload) {
            $response = $this->post('/members/search', [
                'search' => $payload,
            ]);

            $this->assertFalse(
                $this->responseContainsUnescapedXss($response, $payload),
                "Reflected XSS in POST data: {$payload}"
            );
        }
    }

    /**
     * Test stored XSS in user input
     *
     * @test
     */
    public function it_prevents_stored_xss_in_user_profiles()
    {
        $this->actingAs($this->user);

        $payloads = $this->getXssPayloads();

        foreach ($payloads as $payload) {
            // Store XSS payload
            $member = Member::create([
                'first_name' => $payload,
                'last_name' => 'Test',
                'email' => 'xss@example.com',
                'phone' => '0123456789',
            ]);

            // Retrieve and display
            $response = $this->get('/members/' . $member->id);

            $this->assertFalse(
                $this->responseContainsUnescapedXss($response, $payload),
                "Stored XSS in member profile: {$payload}"
            );

            $member->delete();
        }
    }

    /**
     * Test stored XSS in comments/messages
     *
     * @test
     */
    public function it_prevents_stored_xss_in_comments()
    {
        $this->actingAs($this->user);

        $payloads = $this->getXssPayloads();

        foreach ($payloads as $payload) {
            // Create comment with XSS payload
            $response = $this->post('/comments', [
                'content' => $payload,
                'entity_type' => 'loan',
                'entity_id' => 1,
            ]);

            // View comment
            $viewResponse = $this->get('/comments');

            $this->assertFalse(
                $this->responseContainsUnescapedXss($viewResponse, $payload),
                "Stored XSS in comments: {$payload}"
            );
        }
    }

    /**
     * Test XSS in JSON responses
     *
     * @test
     */
    public function it_prevents_xss_in_json_responses()
    {
        $payloads = $this->getXssPayloads();

        foreach ($payloads as $payload) {
            $response = $this->json('GET', '/api/search', [
                'q' => $payload,
            ], [
                'Accept' => 'application/json',
            ]);

            $content = $response->getContent();
            $decoded = json_decode($content, true);

            // JSON should be properly encoded
            $this->assertNotNull($decoded, 'Response should be valid JSON');

            // Check if Content-Type is correct
            $this->assertEquals('application/json', $response->headers->get('Content-Type'));

            // XSS payload should be encoded in JSON
            if (strpos($content, $payload) !== false) {
                // Payload appears in JSON, check it's properly escaped
                $this->assertTrue(
                    strpos($content, json_encode($payload)) !== false,
                    "XSS payload not properly encoded in JSON: {$payload}"
                );
            }
        }
    }

    /**
     * Test XSS in error messages
     *
     * @test
     */
    public function it_prevents_xss_in_error_messages()
    {
        $payloads = $this->getXssPayloads();

        foreach ($payloads as $payload) {
            // Trigger validation error with XSS payload
            $response = $this->post('/members', [
                'first_name' => $payload,
                'last_name' => '', // Missing required field
            ]);

            $this->assertFalse(
                $this->responseContainsUnescapedXss($response, $payload),
                "XSS in error messages: {$payload}"
            );
        }
    }

    /**
     * Test XSS in URL redirects
     *
     * @test
     */
    public function it_prevents_xss_in_redirects()
    {
        $payloads = [
            'javascript:alert("XSS")',
            'data:text/html,<script>alert("XSS")</script>',
            '/search?q=<script>alert("XSS")</script>',
        ];

        foreach ($payloads as $payload) {
            $response = $this->get('/redirect?url=' . urlencode($payload));

            // Should not redirect to javascript: or data: URLs
            $location = $response->headers->get('Location');

            if ($location) {
                $this->assertStringNotStartsWith('javascript:', $location);
                $this->assertStringNotStartsWith('data:', $location);
            }
        }
    }

    /**
     * Test DOM-based XSS protection
     *
     * @test
     */
    public function it_prevents_dom_based_xss()
    {
        $this->actingAs($this->user);

        $response = $this->get('/dashboard');

        $content = $response->getContent();

        // Check that dangerous DOM manipulation is not present
        $dangerousPatterns = [
            '/document\.write\s*\(/i',
            '/\.innerHTML\s*=/i',
            '/\.outerHTML\s*=/i',
            '/eval\s*\(/i',
            '/setTimeout\s*\(\s*[\'"`]/',
            '/setInterval\s*\(\s*[\'"`]/',
        ];

        foreach ($dangerousPatterns as $pattern) {
            $this->assertNotRegExp(
                $pattern,
                $content,
                "Dangerous DOM manipulation pattern found: {$pattern}"
            );
        }
    }

    /**
     * Test template injection
     *
     * @test
     */
    public function it_prevents_template_injection()
    {
        $this->actingAs($this->user);

        $templatePayloads = [
            '{{7*7}}',
            '${7*7}',
            '<%= 7*7 %>',
            '{{constructor.constructor("alert(1)")()}}',
            '{{this}}',
            '{{process}}',
            '${7*7}#{7*7}',
        ];

        foreach ($templatePayloads as $payload) {
            $response = $this->post('/members', [
                'first_name' => $payload,
                'last_name' => 'Test',
                'email' => 'test@example.com',
                'phone' => '0123456789',
            ]);

            $content = $response->getContent();

            // Template should not be executed (should not see "49")
            $this->assertStringNotContainsString('49', $content);

            // Original payload should be escaped or not present
            if (strpos($content, $payload) !== false) {
                $this->assertTrue(
                    $this->isProperlyEscaped($content, $payload),
                    "Template injection vulnerability: {$payload}"
                );
            }
        }
    }

    /**
     * Test XSS in headers
     *
     * @test
     */
    public function it_prevents_xss_in_headers()
    {
        $payloads = $this->getXssPayloads();

        foreach ($payloads as $payload) {
            $response = $this->get('/profile', [
                'X-Custom-Header' => $payload,
                'Referer' => $payload,
            ]);

            // Headers should not be reflected without escaping
            $responseHeaders = $response->headers->all();

            foreach ($responseHeaders as $name => $values) {
                foreach ($values as $value) {
                    $this->assertFalse(
                        $this->containsUnescapedXss($value),
                        "XSS in response header {$name}: {$payload}"
                    );
                }
            }
        }
    }

    /**
     * Test XSS with Content-Type spoofing
     *
     * @test
     */
    public function it_prevents_content_type_xss()
    {
        $payloads = $this->getXssPayloads();

        foreach ($payloads as $payload) {
            $response = $this->json('POST', '/api/data', [
                'content' => $payload,
            ], [
                'Content-Type' => 'text/html', // Spoofed content type
            ]);

            // Response should still be JSON, not HTML
            $contentType = $response->headers->get('Content-Type');
            $this->assertStringContainsString('application/json', $contentType);

            // Content should be properly escaped
            $this->assertFalse(
                $this->responseContainsUnescapedXss($response, $payload),
                "Content-Type XSS: {$payload}"
            );
        }
    }

    /**
     * Test that CSP headers are present
     *
     * @test
     */
    public function it_has_csp_headers_to_prevent_xss()
    {
        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp, 'CSP header should be present');

        // CSP should restrict script sources
        $this->assertStringContainsString("script-src", $csp);

        // Should use nonce or strict-dynamic
        $hasNonce = strpos($csp, 'nonce-') !== false;
        $hasStrictDynamic = strpos($csp, 'strict-dynamic') !== false;
        $hasUnsafeInline = strpos($csp, 'unsafe-inline') !== false;

        // Should use nonce/strict-dynamic and not unsafe-inline
        $this->assertTrue(
            ($hasNonce || $hasStrictDynamic) && !$hasUnsafeInline,
            'CSP should use nonce or strict-dynamic, not unsafe-inline'
        );
    }

    /**
     * Test that X-XSS-Protection header is present
     *
     * @test
     */
    public function it_has_xss_protection_header()
    {
        $response = $this->get('/');

        $xssProtection = $response->headers->get('X-XSS-Protection');

        $this->assertNotNull($xssProtection, 'X-XSS-Protection header should be present');
        $this->assertEquals('1; mode=block', $xssProtection);
    }

    /**
     * Check if response contains unescaped XSS
     *
     * @param  \Illuminate\Testing\TestResponse  $response
     * @param  string  $payload
     * @return bool
     */
    protected function responseContainsUnescapedXss($response, string $payload): bool
    {
        $content = $response->getContent();

        // Check if payload appears unescaped
        if (strpos($content, $payload) !== false) {
            Log::warning('Potential XSS vulnerability detected', [
                'payload' => $payload,
                'status_code' => $response->getStatusCode(),
            ]);
            return true;
        }

        // Check for common XSS indicators
        $xssIndicators = [
            '<script>',
            'onerror=',
            'onload=',
            'javascript:',
            'onclick=',
            '<iframe',
        ];

        foreach ($xssIndicators as $indicator) {
            if (stripos($payload, $indicator) !== false && stripos($content, $indicator) !== false) {
                // Check if it's properly escaped
                $escapedIndicator = htmlspecialchars($indicator, ENT_QUOTES, 'UTF-8');
                if (strpos($content, $escapedIndicator) === false) {
                    Log::warning('XSS indicator not properly escaped', [
                        'indicator' => $indicator,
                        'payload' => $payload,
                    ]);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if string contains unescaped XSS
     *
     * @param  string  $content
     * @return bool
     */
    protected function containsUnescapedXss(string $content): bool
    {
        $xssPatterns = [
            '/<script[^>]*>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>/i',
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if content is properly escaped
     *
     * @param  string  $content
     * @param  string  $original
     * @return bool
     */
    protected function isProperlyEscaped(string $content, string $original): bool
    {
        $escaped = htmlspecialchars($original, ENT_QUOTES, 'UTF-8');
        return strpos($content, $escaped) !== false;
    }
}
