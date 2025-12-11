# HTTP Security Headers Implementation

## Overview

This document provides comprehensive information about the HTTP security headers implementation in the MFI Management System application. Security headers are HTTP response headers that instruct browsers to enable additional security protections, preventing various attack vectors including XSS, clickjacking, MIME-type sniffing, and information leakage.

**Implementation Status:** ✅ **COMPLETE**

**Last Updated:** 2025-10-16

---

## Table of Contents

1. [Security Headers Implemented](#security-headers-implemented)
2. [Architecture](#architecture)
3. [Configuration](#configuration)
4. [Usage Guide](#usage-guide)
5. [Testing & Auditing](#testing--auditing)
6. [Security Headers Reference](#security-headers-reference)
7. [Browser Compatibility](#browser-compatibility)
8. [Troubleshooting](#troubleshooting)
9. [Best Practices](#best-practices)
10. [References](#references)

---

## Security Headers Implemented

### Critical Security Headers (Implemented)

| Header | Status | Severity | Protection Against |
|--------|--------|----------|---------------------|
| Content-Security-Policy | ✅ Implemented | CRITICAL | XSS, injection attacks, unauthorized script execution |
| Strict-Transport-Security | ✅ Implemented | CRITICAL | Man-in-the-middle attacks, protocol downgrade attacks |
| X-Frame-Options | ✅ Implemented | HIGH | Clickjacking, UI redressing attacks |
| X-Content-Type-Options | ✅ Implemented | HIGH | MIME-type sniffing attacks |
| Referrer-Policy | ✅ Implemented | MEDIUM | Information leakage via Referer header |
| Permissions-Policy | ✅ Implemented | MEDIUM | Unauthorized browser feature access |

### Additional Security Headers (Implemented)

| Header | Value | Purpose |
|--------|-------|---------|
| Cross-Origin-Opener-Policy | same-origin | Isolates browsing context |
| Cross-Origin-Embedder-Policy | require-corp | Prevents unauthorized resource embedding |
| Cross-Origin-Resource-Policy | same-origin | Restricts cross-origin resource loading |
| X-XSS-Protection | 1; mode=block | Legacy XSS protection for older browsers |
| X-Permitted-Cross-Domain-Policies | none | Restricts Flash/PDF cross-domain access |
| Expect-CT | max-age=86400, enforce | Certificate Transparency enforcement |

---

## Architecture

### Component Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                      HTTP Request Flow                           │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                   EnhancedSecurityHeaders                        │
│                        Middleware                                │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  1. Generate CSP Nonce                                    │  │
│  │  2. Build Security Headers                                │  │
│  │  3. Apply to Response                                     │  │
│  │  4. Share Nonce with Views                                │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Response with Headers                         │
│  • Content-Security-Policy: default-src 'self'; script-src...   │
│  • Strict-Transport-Security: max-age=31536000; ...             │
│  • X-Frame-Options: SAMEORIGIN                                  │
│  • X-Content-Type-Options: nosniff                              │
│  • Referrer-Policy: strict-origin-when-cross-origin             │
│  • Permissions-Policy: camera=(), microphone=(), ...            │
│  • Additional headers...                                        │
└─────────────────────────────────────────────────────────────────┘
```

### File Structure

```
app/
├── Http/
│   ├── Middleware/
│   │   └── EnhancedSecurityHeaders.php      # Main implementation
│   └── Kernel.php                            # Middleware registration
├── Console/
│   └── Commands/
│       └── AuditSecurityHeaders.php          # Audit command
└── Helpers/
    └── SecurityHelpers.php                   # csp_nonce() helper

config/
└── security-headers.php                      # Configuration

resources/views/
└── *.blade.php                               # Use nonce="{{ csp_nonce() }}"

doc/
└── HTTP_SECURITY_HEADERS.md                  # This document
```

---

## Configuration

### Configuration File

All security headers are configured in `config/security-headers.php`:

```php
<?php

return [
    // Content Security Policy
    'csp' => [
        'enabled' => env('CSP_ENABLED', true),
        'script_src' => [],  // Add trusted CDNs
        'style_src' => [],   // Add trusted CSS sources
        'connect_src' => [], // Add trusted API endpoints
        'allow_unsafe_eval' => env('CSP_ALLOW_UNSAFE_EVAL', true),
        'allow_unsafe_inline_styles' => env('CSP_ALLOW_UNSAFE_INLINE_STYLES', true),
        'upgrade_insecure_requests' => env('CSP_UPGRADE_INSECURE', true),
        'block_mixed_content' => env('CSP_BLOCK_MIXED_CONTENT', true),
        'report_only' => env('CSP_REPORT_ONLY', false),
        'report_uri' => env('CSP_REPORT_URI', null),
    ],

    // Strict Transport Security
    'hsts' => [
        'enabled' => env('HSTS_ENABLED', true),
        'max_age' => env('HSTS_MAX_AGE', 31536000),  // 1 year
        'include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => env('HSTS_PRELOAD', true),
    ],

    // X-Frame-Options
    'x_frame_options' => [
        'enabled' => env('X_FRAME_OPTIONS_ENABLED', true),
        'value' => env('X_FRAME_OPTIONS', 'SAMEORIGIN'),
    ],

    // X-Content-Type-Options
    'x_content_type_options' => [
        'enabled' => env('X_CONTENT_TYPE_OPTIONS_ENABLED', true),
        'value' => 'nosniff',
    ],

    // Referrer-Policy
    'referrer_policy' => [
        'enabled' => env('REFERRER_POLICY_ENABLED', true),
        'value' => env('REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    ],

    // Permissions-Policy
    'permissions_policy' => [
        'enabled' => env('PERMISSIONS_POLICY_ENABLED', true),
        'features' => [
            'accelerometer' => '()',
            'camera' => '()',
            'geolocation' => '()',
            'microphone' => '()',
            'payment' => '()',
            'fullscreen' => '(self)',
            // ... more features
        ],
    ],

    // Cross-Origin Policies
    'cross_origin' => [
        'opener_policy' => env('COOP', 'same-origin'),
        'embedder_policy' => env('COEP', 'require-corp'),
        'resource_policy' => env('CORP', 'same-origin'),
    ],

    // Development Overrides
    'development' => [
        'disable_hsts_local' => env('DISABLE_HSTS_LOCAL', true),
        'relaxed_csp_local' => env('RELAXED_CSP_LOCAL', false),
    ],
];
```

### Environment Variables

Add these to your `.env` file:

```bash
# Content Security Policy
CSP_ENABLED=true
CSP_ALLOW_UNSAFE_EVAL=true
CSP_ALLOW_UNSAFE_INLINE_STYLES=true
CSP_UPGRADE_INSECURE=true
CSP_BLOCK_MIXED_CONTENT=true
CSP_REPORT_ONLY=false
CSP_REPORT_URI=

# Strict Transport Security (HSTS)
HSTS_ENABLED=true
HSTS_MAX_AGE=31536000
HSTS_INCLUDE_SUBDOMAINS=true
HSTS_PRELOAD=true

# X-Frame-Options
X_FRAME_OPTIONS_ENABLED=true
X_FRAME_OPTIONS=SAMEORIGIN

# X-Content-Type-Options
X_CONTENT_TYPE_OPTIONS_ENABLED=true

# Referrer Policy
REFERRER_POLICY_ENABLED=true
REFERRER_POLICY=strict-origin-when-cross-origin

# Permissions Policy
PERMISSIONS_POLICY_ENABLED=true

# Cross-Origin Policies
COOP=same-origin
COEP=require-corp
CORP=same-origin

# Development Settings
DISABLE_HSTS_LOCAL=true
RELAXED_CSP_LOCAL=false
```

### Middleware Registration

The middleware is registered in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \App\Http\Middleware\EnhancedSecurityHeaders::class,
    ],
];

protected $routeMiddleware = [
    // ... other middleware
    'security.headers' => \App\Http\Middleware\EnhancedSecurityHeaders::class,
];
```

---

## Usage Guide

### Basic Usage

The middleware is automatically applied to all web routes. No additional configuration is needed for basic usage.

### Using CSP Nonce in Blade Templates

For inline scripts and styles to work with Content Security Policy, use the `csp_nonce()` helper:

#### Inline Scripts

```blade
{{-- Before (will be blocked by CSP) --}}
<script>
    console.log('Hello World');
</script>

{{-- After (CSP-compliant) --}}
<script nonce="{{ csp_nonce() }}">
    console.log('Hello World');
</script>
```

#### Inline Styles

```blade
{{-- Before (will be blocked by strict CSP) --}}
<style>
    .custom-class { color: red; }
</style>

{{-- After (CSP-compliant) --}}
<style nonce="{{ csp_nonce() }}">
    .custom-class { color: red; }
</style>
```

#### External Scripts with Integrity

```blade
<script
    src="https://cdn.example.com/library.js"
    integrity="sha384-hash..."
    crossorigin="anonymous"
    nonce="{{ csp_nonce() }}"
></script>
```

### Adding Trusted Sources to CSP

If you need to load resources from external domains:

1. **Add to configuration:**

```php
// config/security-headers.php
'csp' => [
    'script_src' => [
        'https://cdn.jsdelivr.net',
        'https://www.google-analytics.com',
    ],
    'style_src' => [
        'https://fonts.googleapis.com',
        'https://cdnjs.cloudflare.com',
    ],
    'connect_src' => [
        'https://api.external-service.com',
    ],
],
```

2. **Or add via environment:**

```bash
CSP_SCRIPT_SRC="https://cdn.jsdelivr.net,https://www.google-analytics.com"
```

### Customizing Security Headers per Route

You can customize headers for specific routes:

```php
Route::middleware(['web', 'security.headers:relaxed'])->group(function () {
    // Routes with relaxed CSP for admin panel
});
```

### Disabling Security Headers for Specific Routes

```php
Route::withoutMiddleware(EnhancedSecurityHeaders::class)->group(function () {
    // Routes without security headers (not recommended)
});
```

---

## Testing & Auditing

### Audit Command

Use the built-in audit command to test security headers:

```bash
# Test default APP_URL
php artisan security:audit-headers

# Test specific URL
php artisan security:audit-headers https://example.com

# Show detailed analysis
php artisan security:audit-headers --detailed

# Output as JSON
php artisan security:audit-headers --json
```

#### Example Output

```
🔍 Auditing Security Headers
URL: https://saccos.nbc.co.tz

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
SECURITY HEADERS AUDIT RESULTS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Grade: A (95/100)

✅ Headers Found: 10
   Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-...'
   Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
   X-Frame-Options: SAMEORIGIN
   X-Content-Type-Options: nosniff
   Referrer-Policy: strict-origin-when-cross-origin
   Permissions-Policy: camera=(), microphone=(), geolocation=(), ...
   Cross-Origin-Opener-Policy: same-origin
   Cross-Origin-Embedder-Policy: require-corp
   Cross-Origin-Resource-Policy: same-origin
   X-XSS-Protection: 1; mode=block

✅ Security headers are properly configured!

Quick Test:
curl -I https://saccos.nbc.co.tz | grep -i 'content-security-policy\|strict-transport\|x-frame'
```

### Manual Testing with cURL

```bash
# Test all security headers
curl -I https://your-domain.com

# Test specific headers
curl -I https://your-domain.com | grep -i 'content-security-policy'
curl -I https://your-domain.com | grep -i 'strict-transport-security'
curl -I https://your-domain.com | grep -i 'x-frame-options'
```

### Online Security Header Scanners

Use these online tools to verify your security headers:

1. **Mozilla Observatory**
   - URL: https://observatory.mozilla.org/
   - Provides comprehensive security analysis

2. **Security Headers**
   - URL: https://securityheaders.com/
   - Quick header check with grade

3. **SSL Labs**
   - URL: https://www.ssllabs.com/ssltest/
   - Tests SSL/TLS and HSTS configuration

4. **CSP Evaluator**
   - URL: https://csp-evaluator.withgoogle.com/
   - Validates Content Security Policy

### Browser DevTools Testing

1. **Open DevTools** (F12)
2. **Go to Network tab**
3. **Reload page**
4. **Click on the main document request**
5. **View Response Headers**

Look for all security headers in the response.

### Integration Testing

Add to your test suite:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    /** @test */
    public function it_includes_content_security_policy()
    {
        $response = $this->get('/');

        $response->assertHeader('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'",
            $response->headers->get('Content-Security-Policy'));
    }

    /** @test */
    public function it_includes_strict_transport_security_on_https()
    {
        $response = $this->get('https://example.com');

        $response->assertHeader('Strict-Transport-Security');
        $this->assertStringContainsString('max-age=31536000',
            $response->headers->get('Strict-Transport-Security'));
    }

    /** @test */
    public function it_includes_x_frame_options()
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    /** @test */
    public function it_includes_x_content_type_options()
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    /** @test */
    public function it_includes_referrer_policy()
    {
        $response = $this->get('/');

        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /** @test */
    public function it_includes_permissions_policy()
    {
        $response = $this->get('/');

        $response->assertHeader('Permissions-Policy');
        $this->assertStringContainsString('camera=()',
            $response->headers->get('Permissions-Policy'));
    }
}
```

---

## Security Headers Reference

### 1. Content-Security-Policy (CSP)

**Purpose:** Prevents XSS attacks by controlling which resources can be loaded and executed.

**Implementation:**
```
Content-Security-Policy: default-src 'self';
                         script-src 'self' 'nonce-...' 'strict-dynamic';
                         style-src 'self' 'nonce-...' 'unsafe-inline';
                         img-src 'self' data: blob: https:;
                         font-src 'self' data:;
                         connect-src 'self';
                         object-src 'none';
                         frame-ancestors 'self';
                         base-uri 'self';
                         upgrade-insecure-requests;
                         block-all-mixed-content
```

**Key Directives:**

| Directive | Value | Purpose |
|-----------|-------|---------|
| default-src | 'self' | Default policy for all resource types |
| script-src | 'self' 'nonce-...' 'strict-dynamic' | Only allow scripts from same origin or with valid nonce |
| style-src | 'self' 'nonce-...' 'unsafe-inline' | Allow styles from same origin, with nonce, or inline |
| img-src | 'self' data: blob: https: | Allow images from same origin, data URIs, blobs, or HTTPS |
| object-src | 'none' | Disable plugins like Flash |
| frame-ancestors | 'self' | Prevent embedding in other sites (clickjacking protection) |
| base-uri | 'self' | Restrict `<base>` tag URLs |
| upgrade-insecure-requests | | Automatically upgrade HTTP to HTTPS |
| block-all-mixed-content | | Block mixed content (HTTP resources on HTTPS page) |

**Using Nonces:**

Nonces are random values generated per request that allow specific inline scripts/styles:

```blade
<script nonce="{{ csp_nonce() }}">
    // This script is allowed by CSP
    console.log('Inline script with nonce');
</script>
```

**CSP Violations:**

To monitor CSP violations, configure a report URI:

```php
'csp' => [
    'report_uri' => 'https://your-domain.com/api/csp-report',
],
```

**Report-Only Mode:**

For testing CSP without blocking resources:

```bash
CSP_REPORT_ONLY=true
```

This sends the header as `Content-Security-Policy-Report-Only` instead.

---

### 2. Strict-Transport-Security (HSTS)

**Purpose:** Forces browsers to only connect via HTTPS, preventing protocol downgrade attacks.

**Implementation:**
```
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
```

**Parameters:**

| Parameter | Value | Purpose |
|-----------|-------|---------|
| max-age | 31536000 | Cache duration in seconds (1 year) |
| includeSubDomains | | Apply to all subdomains |
| preload | | Eligible for browser preload list |

**Important Notes:**

1. **HTTPS Required:** HSTS only works on HTTPS connections
2. **Preload List:** Submit your domain to https://hstspreload.org/ for maximum security
3. **Subdomain Consideration:** Ensure all subdomains support HTTPS before enabling includeSubDomains
4. **Testing:** Start with shorter max-age during testing

**Configuration:**

```php
'hsts' => [
    'enabled' => true,
    'max_age' => 31536000,  // 1 year
    'include_subdomains' => true,
    'preload' => true,
],
```

**Disabling HSTS for Local Development:**

```bash
DISABLE_HSTS_LOCAL=true
```

---

### 3. X-Frame-Options

**Purpose:** Prevents clickjacking by controlling whether the page can be embedded in frames.

**Implementation:**
```
X-Frame-Options: SAMEORIGIN
```

**Options:**

| Value | Behavior |
|-------|----------|
| DENY | Prevents all framing |
| SAMEORIGIN | Allows framing only from same origin |
| ALLOW-FROM https://example.com | Allows framing from specific domain (deprecated) |

**Note:** The `frame-ancestors` CSP directive is more flexible and should be preferred, but X-Frame-Options provides better backward compatibility.

**Configuration:**

```php
'x_frame_options' => [
    'enabled' => true,
    'value' => 'SAMEORIGIN',  // or 'DENY'
],
```

---

### 4. X-Content-Type-Options

**Purpose:** Prevents MIME-type sniffing attacks by forcing browsers to respect declared content types.

**Implementation:**
```
X-Content-Type-Options: nosniff
```

**Why It Matters:**

Without this header, browsers might try to "sniff" the content type of a file and execute it as a script, even if the server declares it as a different type. This header forces browsers to trust the `Content-Type` header.

**Example Attack Prevented:**
- Attacker uploads image file containing JavaScript
- Without `nosniff`, browser might execute it as script
- With `nosniff`, browser treats it strictly as image

**Configuration:**

```php
'x_content_type_options' => [
    'enabled' => true,
    'value' => 'nosniff',
],
```

---

### 5. Referrer-Policy

**Purpose:** Controls how much referrer information is sent with requests.

**Implementation:**
```
Referrer-Policy: strict-origin-when-cross-origin
```

**Policy Options:**

| Policy | Behavior |
|--------|----------|
| no-referrer | Never send referrer |
| no-referrer-when-downgrade | Send referrer except HTTPS → HTTP |
| origin | Send only origin (no path) |
| origin-when-cross-origin | Full URL for same-origin, origin only for cross-origin |
| same-origin | Send referrer only for same-origin requests |
| strict-origin | Send origin except HTTPS → HTTP |
| strict-origin-when-cross-origin | Full for same-origin, origin for cross-origin HTTPS |
| unsafe-url | Always send full URL (not recommended) |

**Recommendation:** `strict-origin-when-cross-origin` balances privacy and functionality.

**Configuration:**

```php
'referrer_policy' => [
    'enabled' => true,
    'value' => 'strict-origin-when-cross-origin',
],
```

---

### 6. Permissions-Policy

**Purpose:** Controls which browser features and APIs can be used.

**Implementation:**
```
Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=(), ...
```

**Feature Options:**

| Feature | Our Setting | Purpose |
|---------|-------------|---------|
| accelerometer | () | Disabled |
| ambient-light-sensor | () | Disabled |
| autoplay | () | Disabled |
| camera | () | Disabled |
| display-capture | () | Disabled |
| document-domain | () | Disabled |
| encrypted-media | () | Disabled |
| fullscreen | (self) | Same-origin only |
| geolocation | () | Disabled |
| gyroscope | () | Disabled |
| magnetometer | () | Disabled |
| microphone | () | Disabled |
| midi | () | Disabled |
| payment | () | Disabled |
| picture-in-picture | () | Disabled |
| usb | () | Disabled |
| web-share | () | Disabled |

**Syntax:**

- `()` = Disabled for all origins
- `(self)` = Allowed for same origin only
- `(*)` = Allowed for all origins
- `(self "https://example.com")` = Allowed for self and specific origin

**Configuration:**

```php
'permissions_policy' => [
    'enabled' => true,
    'features' => [
        'camera' => '()',
        'microphone' => '()',
        'geolocation' => '()',
        'fullscreen' => '(self)',
        // ... more features
    ],
],
```

**Enabling Specific Features:**

If you need to enable a feature (e.g., geolocation for maps):

```php
'features' => [
    'geolocation' => '(self)',  // Allow for same origin
],
```

---

### 7. Cross-Origin Policies

#### Cross-Origin-Opener-Policy (COOP)

**Purpose:** Isolates browsing context from cross-origin windows.

**Implementation:**
```
Cross-Origin-Opener-Policy: same-origin
```

**Options:**
- `unsafe-none`: No isolation (default)
- `same-origin-allow-popups`: Isolate except for same-origin popups
- `same-origin`: Full isolation

#### Cross-Origin-Embedder-Policy (COEP)

**Purpose:** Prevents loading cross-origin resources without explicit permission.

**Implementation:**
```
Cross-Origin-Embedder-Policy: require-corp
```

**Options:**
- `unsafe-none`: No restrictions (default)
- `require-corp`: Require Cross-Origin-Resource-Policy header
- `credentialless`: Load without credentials

#### Cross-Origin-Resource-Policy (CORP)

**Purpose:** Controls which origins can load the resource.

**Implementation:**
```
Cross-Origin-Resource-Policy: same-origin
```

**Options:**
- `same-site`: Allow same-site requests
- `same-origin`: Allow same-origin only
- `cross-origin`: Allow all origins

**Configuration:**

```php
'cross_origin' => [
    'opener_policy' => 'same-origin',
    'embedder_policy' => 'require-corp',
    'resource_policy' => 'same-origin',
],
```

---

### 8. Additional Security Headers

#### X-XSS-Protection

**Purpose:** Legacy XSS protection for older browsers (IE, older Chrome/Safari).

**Implementation:**
```
X-XSS-Protection: 1; mode=block
```

**Note:** Modern browsers rely on CSP instead. This header is included for backward compatibility.

#### X-Permitted-Cross-Domain-Policies

**Purpose:** Restricts Flash and PDF cross-domain policies.

**Implementation:**
```
X-Permitted-Cross-Domain-Policies: none
```

#### Expect-CT

**Purpose:** Certificate Transparency enforcement.

**Implementation:**
```
Expect-CT: max-age=86400, enforce
```

**Note:** This header is being deprecated as Certificate Transparency becomes mandatory.

---

## Browser Compatibility

### Modern Browser Support

| Header | Chrome | Firefox | Safari | Edge |
|--------|--------|---------|--------|------|
| Content-Security-Policy | ✅ 25+ | ✅ 23+ | ✅ 7+ | ✅ 12+ |
| Strict-Transport-Security | ✅ 4+ | ✅ 4+ | ✅ 7+ | ✅ 12+ |
| X-Frame-Options | ✅ All | ✅ All | ✅ All | ✅ All |
| X-Content-Type-Options | ✅ All | ✅ All | ✅ All | ✅ All |
| Referrer-Policy | ✅ 56+ | ✅ 50+ | ✅ 11.1+ | ✅ 79+ |
| Permissions-Policy | ✅ 88+ | ✅ 74+ | ✅ 11.1+ | ✅ 88+ |
| Cross-Origin Policies | ✅ 83+ | ✅ 79+ | ✅ 15.2+ | ✅ 83+ |

### Legacy Browser Support

For older browsers that don't support modern headers:
- X-XSS-Protection provides basic XSS protection
- X-Frame-Options provides clickjacking protection
- Other protections degrade gracefully

---

## Troubleshooting

### Common Issues

#### 1. CSP Blocking Inline Scripts

**Symptom:** Console errors like "Refused to execute inline script because it violates CSP"

**Solution:**
```blade
{{-- Add nonce to inline scripts --}}
<script nonce="{{ csp_nonce() }}">
    // Your script
</script>
```

#### 2. CSP Blocking External Resources

**Symptom:** Console errors like "Refused to load script from 'https://cdn.example.com'"

**Solution:**
```php
// config/security-headers.php
'csp' => [
    'script_src' => [
        'https://cdn.example.com',
    ],
],
```

#### 3. HSTS Not Working

**Symptom:** HSTS header not appearing in response

**Possible Causes:**
- Not using HTTPS connection
- HSTS disabled for local development
- Middleware not registered

**Solution:**
```bash
# Ensure HTTPS
APP_URL=https://your-domain.com

# Enable HSTS
HSTS_ENABLED=true
DISABLE_HSTS_LOCAL=false  # Only in production
```

#### 4. Mixed Content Warnings

**Symptom:** Browser warnings about mixed content (HTTP resources on HTTPS page)

**Solution:**
```php
// Enable automatic upgrades in CSP
'csp' => [
    'upgrade_insecure_requests' => true,
    'block_mixed_content' => true,
],
```

#### 5. Permissions Policy Blocking Required Features

**Symptom:** Features like geolocation not working

**Solution:**
```php
// config/security-headers.php
'permissions_policy' => [
    'features' => [
        'geolocation' => '(self)',  // Enable for same origin
    ],
],
```

#### 6. Frame Embedding Issues

**Symptom:** Page can't be embedded in iframe when needed

**Solution:**
```php
// Change X-Frame-Options
'x_frame_options' => [
    'value' => 'SAMEORIGIN',  // or disable for specific routes
],

// Adjust CSP frame-ancestors
'csp' => [
    // Add to custom CSP configuration
],
```

### Debugging Steps

1. **Check Headers in Browser:**
   - Open DevTools (F12)
   - Network tab → Select document → Response Headers
   - Verify all security headers are present

2. **Use Audit Command:**
   ```bash
   php artisan security:audit-headers --detailed
   ```

3. **Check CSP Violations:**
   - Open DevTools Console
   - Look for CSP violation messages
   - Add nonces or whitelist sources as needed

4. **Verify Middleware Registration:**
   ```bash
   php artisan route:list --middleware=security.headers
   ```

5. **Test with Online Tools:**
   - https://securityheaders.com/
   - https://observatory.mozilla.org/
   - https://csp-evaluator.withgoogle.com/

---

## Best Practices

### 1. Start with Report-Only Mode

When deploying CSP, start in report-only mode:

```bash
CSP_REPORT_ONLY=true
```

Monitor violations for a period before enforcing.

### 2. Use Nonces for Inline Scripts

Prefer nonces over 'unsafe-inline':

```blade
{{-- Good --}}
<script nonce="{{ csp_nonce() }}">
    console.log('Safe');
</script>

{{-- Bad --}}
<script>
    console.log('Blocked by CSP');
</script>
```

### 3. Minimize Inline Styles

Move styles to external CSS files when possible:

```blade
{{-- Good --}}
<link rel="stylesheet" href="/css/app.css">

{{-- Less ideal --}}
<style nonce="{{ csp_nonce() }}">
    .custom { color: red; }
</style>
```

### 4. Regular Security Audits

Run audits regularly:

```bash
# Add to CI/CD pipeline
php artisan security:audit-headers --json > security-audit.json
```

### 5. Keep Headers Updated

Review and update security headers quarterly:
- Check for new browser features to restrict
- Review CSP violations and adjust policies
- Update HSTS max-age incrementally
- Monitor security best practices

### 6. Document Custom Configurations

Document any deviations from default configuration:

```php
// config/security-headers.php
'csp' => [
    // Custom configuration for payment gateway integration
    'script_src' => [
        'https://payment-gateway.com',  // Required for checkout
    ],
],
```

### 7. Test Across Browsers

Test security headers in multiple browsers:
- Chrome/Edge (Chromium)
- Firefox
- Safari
- Mobile browsers

### 8. Monitor Production

Set up monitoring for:
- CSP violations (via report-uri)
- HSTS compliance
- Mixed content warnings
- Security header presence

### 9. Progressive Enhancement

Implement headers progressively:
1. Start with basic headers (X-Frame-Options, X-Content-Type-Options)
2. Add HSTS with short max-age
3. Implement CSP in report-only mode
4. Enforce CSP after monitoring
5. Add advanced policies (Permissions-Policy, Cross-Origin policies)
6. Increase HSTS max-age gradually

### 10. Development vs Production

Use different configurations for environments:

```php
'development' => [
    'disable_hsts_local' => true,
    'relaxed_csp_local' => false,
],
```

---

## Security Compliance

### OWASP Top 10 Compliance

Our security headers implementation addresses:

| OWASP Risk | Headers Used | Protection Level |
|------------|--------------|------------------|
| A01:2021 - Broken Access Control | CSP, CORP, COEP, COOP | HIGH |
| A02:2021 - Cryptographic Failures | HSTS, Expect-CT | HIGH |
| A03:2021 - Injection | CSP, X-Content-Type-Options | HIGH |
| A05:2021 - Security Misconfiguration | All headers | HIGH |
| A07:2021 - XSS | CSP, X-XSS-Protection | HIGH |
| A08:2021 - Software Integrity Failures | CSP (SRI), Expect-CT | MEDIUM |

### CIS Benchmarks

Compliant with CIS Apache HTTP Server Benchmark:
- 3.5: Configure HTTP Security Headers
- 3.6: Configure Content Security Policy
- 3.7: Configure HSTS

### PCI DSS Requirements

Supports PCI DSS compliance:
- Requirement 6.5.7: Cross-site scripting (XSS) - via CSP
- Requirement 6.5.9: Improper error handling - via security headers
- Requirement 6.6: Web application firewall - complements WAF

---

## Performance Considerations

### Header Size

Security headers add approximately 500-800 bytes to each response:
- Content-Security-Policy: ~300 bytes
- Permissions-Policy: ~200 bytes
- Other headers: ~200 bytes

**Optimization:**
- Use compression (gzip/brotli)
- Cache headers at CDN level
- Consider header size when adding many CSP sources

### Caching

Security headers are generated per-request but should be consistent:
- CSP nonces change per request (required for security)
- Other headers can be cached
- CDN can cache headers for static resources

### Server Load

Minimal impact:
- Header generation: <1ms per request
- Nonce generation: Cryptographically secure, minimal overhead
- No database queries required

---

## Migration Guide

### From No Security Headers

1. **Deploy middleware** (already done)
2. **Test in development**
   ```bash
   php artisan security:audit-headers http://localhost
   ```
3. **Enable report-only CSP in production**
   ```bash
   CSP_REPORT_ONLY=true
   ```
4. **Monitor for 1-2 weeks**
5. **Fix violations by adding nonces**
6. **Enable enforcement**
   ```bash
   CSP_REPORT_ONLY=false
   ```
7. **Gradually increase HSTS max-age**
   - Week 1: `HSTS_MAX_AGE=86400` (1 day)
   - Week 2: `HSTS_MAX_AGE=604800` (1 week)
   - Week 4: `HSTS_MAX_AGE=2592000` (1 month)
   - Month 3: `HSTS_MAX_AGE=31536000` (1 year)

### From Basic Headers

If you already have basic X-Frame-Options and X-Content-Type-Options:

1. **Verify compatibility with EnhancedSecurityHeaders middleware**
2. **Remove old headers configuration**
3. **Deploy new middleware**
4. **Add CSP gradually** (start with report-only)
5. **Add HSTS** (start with short max-age)
6. **Add advanced policies** (Permissions-Policy, Cross-Origin)

---

## Monitoring and Logging

### CSP Violation Reporting

Set up CSP violation endpoint:

```php
// routes/api.php
Route::post('/csp-report', [SecurityController::class, 'reportCspViolation'])
    ->withoutMiddleware(VerifyCsrfToken::class);

// app/Http/Controllers/SecurityController.php
public function reportCspViolation(Request $request)
{
    $report = $request->input('csp-report');

    Log::warning('CSP Violation', [
        'document-uri' => $report['document-uri'] ?? null,
        'violated-directive' => $report['violated-directive'] ?? null,
        'blocked-uri' => $report['blocked-uri'] ?? null,
        'source-file' => $report['source-file'] ?? null,
    ]);

    return response()->json(['received' => true]);
}
```

Enable in configuration:

```php
'csp' => [
    'report_uri' => env('CSP_REPORT_URI', config('app.url') . '/api/csp-report'),
],
```

### Security Header Monitoring

Create monitoring dashboard:

```bash
# Daily audit
php artisan security:audit-headers --json | \
    jq '.score' | \
    monitoring-system --metric security.headers.score

# Alert on grade drop
php artisan security:audit-headers --json | \
    jq -r '.grade' | \
    grep -v '[AB]' && send-alert "Security headers degraded"
```

### Access Logs

Monitor security header effectiveness:
- Track CSP violations
- Monitor HSTS deployment
- Audit header presence in logs

---

## Rollback Procedures

### Emergency Rollback

If security headers cause production issues:

1. **Disable specific header:**
   ```bash
   CSP_ENABLED=false
   php artisan config:cache
   ```

2. **Disable all enhanced headers:**
   ```bash
   # Comment out middleware in Kernel.php
   // \App\Http\Middleware\EnhancedSecurityHeaders::class,
   ```

3. **Switch CSP to report-only:**
   ```bash
   CSP_REPORT_ONLY=true
   php artisan config:cache
   ```

### Gradual Rollback

1. **Relax strict policies:**
   ```bash
   CSP_ALLOW_UNSAFE_EVAL=true
   CSP_ALLOW_UNSAFE_INLINE_STYLES=true
   ```

2. **Reduce HSTS max-age:**
   ```bash
   HSTS_MAX_AGE=0  # Browser will clear HSTS
   ```

3. **Disable restrictive features:**
   ```bash
   PERMISSIONS_POLICY_ENABLED=false
   ```

---

## References

### Official Specifications

1. **Content Security Policy**
   - W3C CSP Level 3: https://www.w3.org/TR/CSP3/
   - MDN CSP Guide: https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP

2. **Strict-Transport-Security**
   - RFC 6797: https://tools.ietf.org/html/rfc6797
   - HSTS Preload: https://hstspreload.org/

3. **Permissions Policy**
   - W3C Specification: https://www.w3.org/TR/permissions-policy-1/
   - MDN Guide: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Permissions-Policy

4. **Cross-Origin Policies**
   - COOP: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cross-Origin-Opener-Policy
   - COEP: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cross-Origin-Embedder-Policy
   - CORP: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cross-Origin-Resource-Policy

### Security Resources

1. **OWASP Secure Headers Project**
   - https://owasp.org/www-project-secure-headers/

2. **Mozilla Web Security Guidelines**
   - https://infosec.mozilla.org/guidelines/web_security

3. **Google Web Fundamentals**
   - https://developers.google.com/web/fundamentals/security

4. **Security Headers Best Practices**
   - https://securityheaders.com/
   - https://observatory.mozilla.org/

### Testing Tools

1. **securityheaders.com** - Quick header scan
2. **observatory.mozilla.org** - Comprehensive security analysis
3. **csp-evaluator.withgoogle.com** - CSP validation
4. **ssllabs.com** - SSL/TLS and HSTS testing

---

## Changelog

### 2025-10-16 - Initial Implementation

- ✅ Implemented all 6 critical security headers
- ✅ Created EnhancedSecurityHeaders middleware with CSP nonce support
- ✅ Created configuration file (config/security-headers.php)
- ✅ Created audit command (php artisan security:audit-headers)
- ✅ Added 8 additional security headers (COOP, COEP, CORP, etc.)
- ✅ Implemented environment-based configuration
- ✅ Added development/production overrides
- ✅ Created comprehensive documentation

---

## Support

### Getting Help

1. **Review this documentation**
2. **Run audit command:**
   ```bash
   php artisan security:audit-headers --detailed
   ```
3. **Check browser console for CSP violations**
4. **Test with online tools**
5. **Contact MFI Management System Security Team**

### Reporting Security Issues

Report security concerns to: security@nbc.co.tz

---

## Conclusion

HTTP security headers are a critical defense layer that protects against various web vulnerabilities including XSS, clickjacking, MIME-sniffing, and information leakage. This implementation provides:

✅ **Comprehensive Protection** - All major security headers implemented
✅ **Easy Configuration** - Environment-based settings
✅ **Testing Tools** - Built-in audit command
✅ **Developer-Friendly** - CSP nonce helper for inline scripts
✅ **Production-Ready** - Tested and documented
✅ **Compliance** - Meets OWASP, CIS, and PCI DSS requirements

**Next Steps:**

1. Review configuration in `config/security-headers.php`
2. Test with `php artisan security:audit-headers`
3. Add nonces to any inline scripts/styles
4. Configure CSP sources for external resources
5. Monitor CSP violations in production
6. Gradually increase HSTS max-age
7. Run regular security audits

**Remember:** Security headers are one layer of defense. Combine with other security measures:
- Input validation and sanitization
- CSRF protection
- File upload security
- Authentication and authorization
- Database security
- Regular security audits

---

**Document Version:** 1.0
**Last Updated:** 2025-10-16
**Author:** MFI Management System Security Team
**Status:** Production Ready
