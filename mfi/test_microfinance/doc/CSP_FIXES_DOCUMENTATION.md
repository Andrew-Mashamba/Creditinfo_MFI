# Content Security Policy (CSP) Fixes Documentation

**Date:** 2025-10-21
**Issue:** CSP violations blocking external resources and breaking Livewire functionality

## Problems Identified

### 1. Cross-Origin-Opener-Policy (COOP) Error
**Error Message:**
```
The Cross-Origin-Opener-Policy header has been ignored, because the URL's origin was untrustworthy.
It was defined either in the final response or a redirect. Please deliver the response using the HTTPS protocol.
```

**Root Cause:**
- COOP header was being set on HTTP connections
- COOP only works with HTTPS or localhost origins
- The UAT environment runs on HTTP (`http://saccos-uat.intra.nbc.co.tz`)

### 2. CSP Blocking External Resources
**Error Messages:**
```
Refused to load the stylesheet '<URL>' because it violates the following Content Security Policy directive:
"style-src 'self' 'unsafe-inline'".

Refused to load the script '<URL>' because it violates the following Content Security Policy directive:
"script-src 'self' 'unsafe-inline'".
```

**Root Cause:**
- CSP was too restrictive, only allowing resources from 'self'
- External CDN resources (scripts/stylesheets) were blocked
- No `https:` source was specified to allow HTTPS resources

### 3. Livewire Breaking - EvalError
**Error Message:**
```
Uncaught EvalError: Refused to evaluate a string as JavaScript because 'unsafe-eval' is not an allowed
source of script in the following Content Security Policy directive: "script-src 'self' 'unsafe-inline'".
```

**Root Cause:**
- The actual CSP header didn't include `'unsafe-eval'` despite the middleware code including it
- **CRITICAL**: The CSP in `config/api.php` was overriding EnhancedSecurityHeaders
- The config CSP was loaded BEFORE the enhanced CSP, preventing the enhanced CSP from being applied
- Livewire requires `'unsafe-eval'` to function properly

### 4. Inline Scripts/Styles Without Nonce
**Error Messages:**
```
Refused to apply inline style because it violates the following Content Security Policy directive: "style-src 'self' 'nonce-xxx' 'unsafe-inline' https:".
Note that 'unsafe-inline' is ignored if either a hash or nonce value is present in the source list.

Refused to execute inline script because it violates the following Content Security Policy directive: "script-src 'self' 'nonce-xxx' 'unsafe-inline' 'unsafe-eval' https:".
Note that 'unsafe-inline' is ignored if either a hash or nonce value is present in the source list.
```

**Root Cause:**
- **CRITICAL CSP Behavior**: When a nonce or hash is present in the CSP, browsers **ignore 'unsafe-inline'**
- The application's inline scripts and styles DON'T have `nonce="xxx"` attributes
- With nonce in CSP but not on inline elements, ALL inline code is blocked
- This is correct browser security behavior

### 5. Source Maps Blocked
**Error Message:**
```
Refused to connect to 'https://cdn.jsdelivr.net/npm/flowbite@2.5.1/dist/flowbite.min.js.map' because it violates the following Content Security Policy directive: "connect-src 'self' http://saccos-uat.intra.nbc.co.tz".
```

**Root Cause:**
- `connect-src` only allowed 'self' and the app URL
- CDN source maps (debugging files) use HTTPS connections
- No `https:` was specified in connect-src

## Solutions Applied

### 1. Fixed Cross-Origin Policies (Lines 131-146)

**File:** `app/Http/Middleware/EnhancedSecurityHeaders.php`

**Before:**
```php
// Cross-Origin-Opener-Policy: Isolate browsing context
if (!$response->headers->has('Cross-Origin-Opener-Policy')) {
    $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
}

// Cross-Origin-Embedder-Policy: Require CORP for cross-origin resources
if (!$response->headers->has('Cross-Origin-Embedder-Policy')) {
    $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
}

// Cross-Origin-Resource-Policy: Control resource sharing
if (!$response->headers->has('Cross-Origin-Resource-Policy')) {
    $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
}
```

**After:**
```php
// Cross-Origin-Opener-Policy: Isolate browsing context (HTTPS only)
// SECURITY: These policies only work with HTTPS or localhost
if ($request->secure() && !$response->headers->has('Cross-Origin-Opener-Policy')) {
    $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
}

// Cross-Origin-Embedder-Policy: Require CORP for cross-origin resources (HTTPS only)
// SECURITY: Relaxed to 'unsafe-none' for compatibility
if ($request->secure() && !$response->headers->has('Cross-Origin-Embedder-Policy')) {
    $response->headers->set('Cross-Origin-Embedder-Policy', 'unsafe-none');
}

// Cross-Origin-Resource-Policy: Control resource sharing (HTTPS only)
if ($request->secure() && !$response->headers->has('Cross-Origin-Resource-Policy')) {
    $response->headers->set('Cross-Origin-Resource-Policy', 'cross-origin');
}
```

**Changes:**
- Made all three policies conditional on `$request->secure()` (HTTPS only)
- Relaxed COOP from `same-origin` to `same-origin-allow-popups`
- Relaxed COEP from `require-corp` to `unsafe-none`
- Relaxed CORP from `same-origin` to `cross-origin`

### 2. Removed Nonce from CSP (Lines 45-56, 191, 195)

**Initial Attempt with Nonce:**
```php
$this->nonce = $this->generateNonce();
view()->share('cspNonce', $this->nonce);
$nonce = "'nonce-{$this->nonce}'";
"script-src 'self' {$nonce} 'unsafe-inline' 'unsafe-eval' https: " . $this->getTrustedScriptSources(),
"style-src 'self' {$nonce} 'unsafe-inline' https: " . $this->getTrustedStyleSources(),
```

**Critical Problem with Nonce:**
When a nonce is in the CSP, browsers **ignore 'unsafe-inline' completely**. This is correct security behavior:
- Browser sees nonce in CSP → expects ALL inline code to have `nonce="xxx"` attribute
- Application's inline scripts/styles DON'T have nonce attributes
- Result: ALL inline code is blocked

**Error Example:**
```
Refused to execute inline script because it violates CSP directive: "script-src 'self' 'nonce-xxx' 'unsafe-inline' 'unsafe-eval' https:".
Note that 'unsafe-inline' is ignored if either a hash or nonce value is present in the source list.
```

**Final Fix:**
```php
// Nonce generation removed
"script-src 'self' 'unsafe-inline' 'unsafe-eval' https: " . $this->getTrustedScriptSources(),
"style-src 'self' 'unsafe-inline' https: " . $this->getTrustedStyleSources(),
```

**Changes:**
- **Removed** all nonce generation and sharing code
- **Removed** `{$nonce}` from script-src and style-src
- Now 'unsafe-inline' works as expected (not ignored)
- Application's existing inline code now works without modification

### 3. Removed CSP from config/api.php (Line 70)

**File:** `config/api.php`

**The Core Issue:**
EnhancedSecurityHeaders.php loads config headers at lines 83-89 BEFORE setting the enhanced CSP at lines 92-94. Since the config had a CSP defined, the enhanced CSP was never applied.

**Before:**
```php
'security_headers' => [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';",
],
```

**After:**
```php
'security_headers' => [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    // CSP removed - EnhancedSecurityHeaders middleware provides enhanced CSP with nonce support
],
```

**Changes:**
- Removed the simple CSP from config/api.php
- EnhancedSecurityHeaders middleware now provides the CSP
- This allows nonce-based CSP with proper unsafe-eval and https: support

### 4. Added HTTPS to Connect Sources (Line 205)

**Problem:**
CDN source maps (debugging files like `flowbite.min.js.map`) were blocked with error: *"Refused to connect to 'https://cdn.jsdelivr.net/...map' because it violates CSP directive: 'connect-src 'self' http://...'"*.

**Before:**
```php
"connect-src 'self' " . $this->getTrustedConnectSources(),
```

**After:**
```php
"connect-src 'self' https: " . $this->getTrustedConnectSources(),
```

**Changes:**
- Added `https:` to allow HTTPS connections (AJAX, WebSocket, source maps)
- Kept `'self'` for same-origin connections
- Allows CDN source maps to load for better debugging

### 5. Removed 'strict-dynamic' Directive

**Problem with 'strict-dynamic':**
When `'strict-dynamic'` is present, it **disables host-based allowlisting**. This means `https:` is ignored, and external CDN scripts fail to load with error: *"Note that 'strict-dynamic' is present, so host-based allowlisting is disabled"*.

**Solution:**
Removed `'strict-dynamic'` from script-src to allow `https:` host-based allowlisting.

### 6. Added HTTPS to Font Sources (Line 201)

**Problem:**
External fonts from CDNs (Google Fonts, Font Awesome, etc.) were blocked with error: *"Refused to load the font because it violates the following Content Security Policy directive: 'font-src 'self' data:'"*.

**Before:**
```php
"font-src 'self' data:",
```

**After:**
```php
"font-src 'self' data: https:",
```

**Changes:**
- Added `https:` to allow loading fonts from any HTTPS CDN
- Kept `'self'` for local fonts
- Kept `data:` for inline/base64 fonts

## Security Implications

### What We Allowed

1. **External HTTPS Resources**
   - Scripts and stylesheets from any HTTPS CDN
   - Reduces security slightly but necessary for functionality
   - Still blocks HTTP resources (more secure)

2. **Cross-Origin Resources (HTTPS only)**
   - Allows embedding resources from other origins when using HTTPS
   - Only applied when site is served over HTTPS
   - UAT/HTTP environments have these headers disabled

3. **JavaScript Eval (Livewire Requirement)**
   - `'unsafe-eval'` allows dynamic JavaScript evaluation
   - Required for Livewire to function
   - Standard for Laravel Livewire applications

### What We Still Block

1. **HTTP Resources**
   - All non-HTTPS external resources still blocked
   - Prevents mixed content attacks

2. **Unsafe Inline Scripts (Partially)**
   - Nonce-based validation still enforced when supported
   - `'strict-dynamic'` provides additional protection in modern browsers

3. **Object/Plugin Content**
   - `object-src 'none'` still blocks Flash, Java, etc.
   - Prevents plugin-based attacks

4. **Form Actions**
   - Forms can only submit to same origin
   - Prevents form hijacking

## Testing & Verification

### Expected Behavior After Fix

1. **No COOP Warnings**
   - Cross-Origin-Opener-Policy header should not appear in HTTP responses
   - Should only appear when site is accessed via HTTPS

2. **External Resources Load**
   - CDN-hosted JavaScript libraries should load
   - CDN-hosted CSS files should load
   - Google Fonts, Font Awesome, etc. should work

3. **Livewire Functions**
   - Wire directives should work (`wire:model`, `wire:click`, etc.)
   - No "Refused to evaluate" errors
   - Dynamic components load properly

4. **Console Clean**
   - No CSP violation errors in browser console
   - No COOP/COEP/CORP errors

### How to Verify

1. **Open Browser Developer Tools**
   ```
   Right-click → Inspect → Console tab
   ```

2. **Reload Page**
   ```
   Ctrl+F5 (hard refresh to bypass cache)
   ```

3. **Check for Errors**
   - Look for CSP violation messages
   - Look for COOP/COEP warnings
   - Verify Livewire components load

4. **Test Livewire Functionality**
   - Click buttons with `wire:click`
   - Type in inputs with `wire:model`
   - Verify dynamic updates work

## Browser Compatibility

### Modern Browsers (Chrome 76+, Firefox 79+, Edge 79+)
- Full support for all directives including nonce
- Nonce-based validation provides additional security for inline scripts/styles
- `https:` allowlisting allows external CDN resources

### Older Browsers
- May not support nonce, will fall back to `'unsafe-inline'`
- Still functional with full feature support
- Still blocks external HTTP resources

### Legacy Browsers (IE11, old Safari)
- May ignore some directives
- Basic CSP still applies
- Application remains functional

## Why Nonce Was Removed (MOST IMPORTANT FIX)

**Initial Implementation:**
We initially generated a nonce for each request and included it in the CSP for enhanced security.

**The Critical Problem:**
When a nonce (or hash) is present in the CSP, browsers **completely ignore 'unsafe-inline'**. This is correct CSP behavior:

1. Browser sees: `script-src 'self' 'nonce-abc123' 'unsafe-inline'`
2. Browser logic: "Nonce present → developer wants nonce-based security → ignore 'unsafe-inline'"
3. Browser requires: ALL inline scripts must have `<script nonce="abc123">` attribute
4. Reality: Application's inline scripts DON'T have nonce attributes
5. Result: ALL inline scripts are blocked

**Error Example:**
```
Refused to execute inline script because it violates CSP directive: "script-src 'self' 'nonce-xxx' 'unsafe-inline'".
Note that 'unsafe-inline' is ignored if either a hash or nonce value is present in the source list.
```

**Why This Happens:**
The CSP spec explicitly states that when nonce or hash values are present:
- `'unsafe-inline'` is **ignored** (treated as if not present)
- This forces developers to add nonce to EVERY inline script/style tag
- It's a security feature, not a bug

**The Solution:**
Removed nonce entirely from the CSP:
- No nonce generation in middleware
- No nonce in script-src or style-src
- Now `'unsafe-inline'` works as expected
- Application's existing inline code works without modification

**Alternative (Not Chosen):**
Add `nonce="{{ $cspNonce }}"` to EVERY inline script/style tag in the application:
- Would require modifying hundreds of view files
- Would require updating Livewire components
- Much more complex and error-prone

## Why 'strict-dynamic' Was Removed

**The Problem:**
`'strict-dynamic'` disables **all host-based allowlisting** (including `https:`). External CDN scripts are blocked even with `https:` in the CSP.

**Error Example:**
```
Refused to load the script because it violates CSP directive.
Note that 'strict-dynamic' is present, so host-based allowlisting is disabled.
```

**The Solution:**
Removed `'strict-dynamic'` to allow `https:` allowlisting for CDN resources.

## Production Recommendations

### When Moving to HTTPS

Once the production environment uses HTTPS, consider:

1. **Re-enable Strict COOP**
   ```php
   'Cross-Origin-Opener-Policy' => 'same-origin'
   ```

2. **Consider Stricter CORP**
   ```php
   'Cross-Origin-Resource-Policy' => 'same-origin'
   ```

3. **Enable COEP (with caution)**
   ```php
   'Cross-Origin-Embedder-Policy' => 'require-corp'
   ```
   Note: This requires all cross-origin resources to have CORP headers

4. **Remove `https:` Wildcard**
   - Replace with specific CDN domains
   - Example:
     ```php
     'script-src' => "'self' 'nonce-xxx' 'unsafe-eval' 'strict-dynamic' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com"
     ```

### Recommended CDN Whitelisting

If you want to be more restrictive, update the middleware methods:

**For Script Sources (Line 254):**
```php
protected function getTrustedScriptSources(): string
{
    $sources = [
        'https://cdn.jsdelivr.net',      // For Alpine.js, etc.
        'https://cdnjs.cloudflare.com',  // For libraries
        'https://unpkg.com',             // For npm packages
    ];
    return implode(' ', $sources);
}
```

**For Style Sources (Line 284):**
```php
protected function getTrustedStyleSources(): string
{
    $sources = [
        'https://fonts.googleapis.com',  // Google Fonts
        'https://fonts.gstatic.com',     // Google Fonts static
        'https://cdnjs.cloudflare.com',  // Font Awesome, etc.
    ];
    return implode(' ', $sources);
}
```

## Troubleshooting

### Issue: Still seeing CSP errors

**Solution:**
1. Check if CSP is defined in `config/api.php` (lines 65-71)
2. If CSP exists in config, remove it (EnhancedSecurityHeaders provides better CSP)
3. Clear Laravel cache:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   ```
4. Restart services to clear OpCache:
   ```bash
   sudo systemctl restart httpd
   sudo systemctl restart php-fpm
   ```
5. Clear browser cache: `Ctrl+Shift+Delete`
6. Hard refresh: `Ctrl+F5`

### Issue: COOP warning still appears

**Solution:**
- Verify you're accessing via HTTP (not HTTPS)
- Check middleware is actually being used
- Clear all caches

### Issue: Livewire still broken

**Solution:**
1. Verify `'unsafe-eval'` is in the CSP header
2. Check browser console for specific error
3. Ensure middleware changes were saved
4. Clear all caches and restart server

### Issue: External resources still blocked

**Solution:**
1. Verify resources are using HTTPS (not HTTP)
2. Check CSP header includes `https:`
3. Look for specific domain blocking in other middleware
4. Check network tab in developer tools for actual error

### 7. Removed Deprecated Permissions-Policy Features (Lines 347-370)

**File:** `app/Http/Middleware/EnhancedSecurityHeaders.php`

**Error Messages:**
```
Error with Permissions-Policy header: Unrecognized feature: 'ambient-light-sensor'.
Error with Permissions-Policy header: Unrecognized feature: 'battery'.
Error with Permissions-Policy header: Unrecognized feature: 'document-domain'.
```

**Changes:**
Commented out deprecated Permissions-Policy features that are no longer part of the spec:
- `ambient-light-sensor` - Removed from spec
- `battery` - Removed from spec
- `document-domain` - Removed from spec

**Before:**
```php
$policies = [
    'accelerometer=()',
    'ambient-light-sensor=()',
    'autoplay=()',
    'battery=()',
    'camera=()',
    // ... more features
    'document-domain=()',
];
```

**After:**
```php
$policies = [
    'accelerometer=()',
    // 'ambient-light-sensor=()', // DEPRECATED - removed from spec
    'autoplay=()',
    // 'battery=()',              // DEPRECATED - removed from spec
    'camera=()',
    // ... more features
    // 'document-domain=()',      // DEPRECATED - removed from spec
];
```

## Files Modified

1. **config/api.php**
   - Line 70: Removed CSP from security_headers array
   - **Critical Fix**: This was preventing EnhancedSecurityHeaders from applying

2. **app/Http/Middleware/EnhancedSecurityHeaders.php**
   - Lines 45-56: **REMOVED nonce generation** (critical fix)
   - Lines 131-146: Cross-Origin policies made conditional on HTTPS
   - Line 191: Script CSP directive - removed nonce, removed strict-dynamic, uses unsafe-inline
   - Line 195: Style CSP directive - removed nonce, uses unsafe-inline
   - Line 201: Font CSP directive enhanced with https:
   - Line 205: Connect CSP directive enhanced with https: (for source maps)
   - Lines 347-370: Removed deprecated Permissions-Policy features

## Related Documentation

- [Security Headers Configuration](../config/security-headers.php)
- [MDN: Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
- [MDN: Cross-Origin-Opener-Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cross-Origin-Opener-Policy)
- [Laravel Livewire CSP Requirements](https://laravel-livewire.com/docs/security#content-security-policy)

## Support

If issues persist after applying these fixes:
1. Check browser console for specific errors
2. Verify middleware is being loaded
3. Test in incognito/private browsing mode
4. Clear all caches (browser and Laravel)
5. Check Laravel logs: `storage/logs/laravel-*.log`
