# Laravel Penetration Testing Preparation Checklist

This document provides a comprehensive checklist to prepare the MFI Management System Laravel application for an intensive penetration test.

---

## 1. Planning & Scope

- [ ] Define exact scope for pentesters (hosts, subdomains, APIs, mobile apps, third-party services, test data allowed)
- [ ] Create delegate contacts, escalation path and emergency rollback procedures
- [ ] Provide test accounts with different roles (admin, staff, normal user, unauthenticated) and clear credentials for each
- [ ] Prepare a "blast radius" plan (what they may safely break) and backup/restore plan
- [ ] Conduct threat modeling & data classification: list sensitive data (PII, credentials, financial data) and where stored/transmitted

---

## 2. Environment Setup (Test on Staging, NOT Production)

- [ ] Create a staging environment that mirrors production (config, services, network) but contains scrubbed test data
- [ ] Ensure backups and snapshot capability enabled so you can revert quickly
- [ ] Ensure monitoring + alerting are active for staging (so you notice destructive tests)
- [ ] Disable or restrict integrations that might trigger real-world costs or notifications (payment gateways, SMS/email to customers) or use sandbox/test keys

---

## 3. Code & Framework Hardening (Laravel-Specific)

- [ ] Upgrade Laravel to latest supported version and apply security patches
- [ ] Upgrade PHP to a supported, patched version
- [ ] Remove dev code & debug: `APP_DEBUG=false` in production/staging
- [ ] Ensure `APP_ENV` is set correctly and not leaking dev config
- [ ] Ensure `config:cache`, `route:cache`, `view:cache` in production for stability
- [ ] Ensure `.env` is NOT committed to git and not readable from webroot
- [ ] Publish `composer.lock` and run `composer update` only after vetting; use `composer install --no-dev` in production
- [ ] Audit third-party packages: remove unused packages; update to patched versions
- [ ] Run `composer audit` / SensioLabs Security Checker / OSV/Dependabot
- [ ] Remove debug and dev endpoints (Telescope, Ignition, debugbar) from non-dev environments

---

## 4. Secrets & Source Control

- [ ] Scan repo for secrets: use truffleHog, git-secrets, gitleaks
- [ ] Detect any credentials and rotate them immediately
- [ ] Remove `.env` and other secrets from history (`git filter-branch`/BFG) and rotate keys
- [ ] Ensure CI/CD secrets are in a secret manager (Vault, AWS Secrets Manager, GitHub Actions secrets)
- [ ] Enforce branch protections and required PR reviews
- [ ] Ensure no sensitive files (private keys, certs) stored in repo

---

## 5. Authentication & Session Management

- [ ] Enforce strong password policies and hashing: Argon2id or bcrypt with appropriate cost
- [ ] Implement account lockout/throttling after N failed attempts and exponential backoff
- [ ] Ensure email/password reset flows are secure (single-use tokens, short expiry)
- [ ] Use multi-factor authentication (MFA) for admin users and provide test accounts with MFA enabled
- [ ] Protect against session fixation: regenerate session ID on login (`session()->regenerate()`)
- [ ] Use secure session store (Redis/DB) and set `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, and `SESSION_SAME_SITE=strict` (or Lax where required)
- [ ] Use HTTPS-only cookies and set appropriate `SameSite` values
- [ ] Ensure logout invalidates session server-side
- [ ] If using JWTs: short expiry, refresh token rotation, store refresh tokens securely and support revocation
- [ ] Check "remember me" implementation: minimal lifetime and revocation

---

## 6. Authorization & Access Control

- [ ] Implement RBAC / ACL; map roles to permissions explicitly
- [ ] Perform an access matrix audit — verify every endpoint enforces auth and checks authorization
- [ ] Test for IDOR: ensure object access is always validated against the user (no sequential numeric IDs without checks)
- [ ] Principle of Least Privilege: ensure users and service accounts have minimal permissions

---

## 7. Input Validation & Injection Prevention

- [ ] Use Eloquent/Query Builder or parameterized queries everywhere. Avoid raw queries; if used, ensure bindings
- [ ] Validate all inputs server-side with Laravel validation rules; whitelist expected values
- [ ] Sanitize outputs to prevent reflected/stored XSS (escape in Blade `{{ }}` and use `@verbatim`/`{!! !!}` only when safe)
- [ ] Protect against SQL injection, command injection, LDAP injection — validate & bind parameters
- [ ] Limit uploaded file types and use MIME & content checks, not just extensions

---

## 8. XSS / CSP / UI Protections

- [ ] Ensure all user-supplied content is escaped when shown
- [ ] Implement Content Security Policy (CSP) with strict `script-src`, `object-src`, etc.
- [ ] Add nonce support for inline scripts if needed
- [ ] Add `X-XSS-Protection` (legacy), `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Expect-CT` where appropriate
- [ ] Use proper `utf-8` meta and avoid dangerous `innerHTML` without sanitization
- [ ] Sanitize HTML inputs with an allowlist sanitizer (e.g., HTMLPurifier) if rich text is permitted

---

## 9. CSRF Protection

- [ ] Ensure Laravel's CSRF middleware is enabled for stateful endpoints (`VerifyCsrfToken`)
- [ ] Ensure APIs using stateless tokens (JWT/OAuth) are not depending on CSRF tokens; use correct auth model
- [ ] Verify CSRF tokens are validated on all form POST/PUT/DELETE actions

---

## 10. File Upload Handling

- [ ] Store uploads outside webroot or use signed URLs (S3)
- [ ] Don't execute uploaded files. Ensure webserver does not treat upload dirs as executable
- [ ] Check file size limits and filename sanitization
- [ ] Scan uploaded files for malware if necessary
- [ ] Serve files through controlled handlers that check authorization

---

## 11. Error Handling & Information Disclosure

- [ ] Generic error pages — do not leak stack traces, paths, DB errors, or config values
- [ ] Ensure logging does not contain secrets, tokens, or full credit card numbers
- [ ] Use centralized logging with controlled access (ELK/Graylog/Splunk) and retention policies

---

## 12. TLS / Transport Security

- [ ] Enforce HTTPS everywhere (HSTS) with long `max-age`, includeSubDomains, and preload if appropriate
- [ ] Use modern TLS (1.2+ / 1.3), strong ciphers, disable old protocols (SSLv3/TLS1.0/1.1)
- [ ] Use valid certificates (no self-signed in production). Enable OCSP stapling
- [ ] Use automated renewal (Let's Encrypt certbot or ACME)
- [ ] Disable weak ciphers and enable Perfect Forward Secrecy (PFS)
- [ ] Check for mixed content and remove insecure resources

---

## 13. HTTP Security Headers

- [ ] `Content-Security-Policy` (CSP)
- [ ] `Strict-Transport-Security` (HSTS)
- [ ] `X-Frame-Options: DENY` (or frame-ancestors CSP)
- [ ] `X-Content-Type-Options: nosniff`
- [ ] `Referrer-Policy: no-referrer-when-downgrade` (or stricter)
- [ ] `Permissions-Policy` to restrict features (geolocation, camera, microphone)

---

## 14. Rate Limiting & Brute Force Protections

- [ ] Use Laravel Rate Limiter to throttle login, password reset, API endpoints
- [ ] Apply IP-based and user-based throttles
- [ ] Consider CAPTCHA for high-risk flows
- [ ] Block or rate limit suspicious IPs / geolocations if appropriate

---

## 15. API Security

- [ ] Use OAuth2 / API tokens with granular scopes rather than passing credentials in requests
- [ ] Validate `Content-Type` and `Accept` headers
- [ ] Use strong token storage and rotation; never allow long-lived tokens unnecessarily
- [ ] Use per-client rate limits, and require authentication for all non-public endpoints
- [ ] For GraphQL, enforce depth/complexity limits and query whitelisting

---

## 16. Logging, Monitoring & Detection

- [ ] Ensure audit logging for security-relevant events: logins, failed logins, password resets, privilege changes, API key creation, critical operations
- [ ] Logs include timestamps, actor, IP, user-agent, but scrub sensitive content
- [ ] Integrate with SIEM or at least alerting on anomalous events
- [ ] Enable integrity monitoring for critical files (Tripwire-like)
- [ ] Ensure logs are tamper-evident (write-once storage / remote logging)

---

## 17. Backups & Recovery

- [ ] Ensure regular encrypted backups, test restores, and document RTO/RPO
- [ ] Ensure backup credentials are protected and not accessible from the web server

---

## 18. Database Security

- [ ] Use least-privilege DB users; separate users for app and admin
- [ ] Disable superuser access from app servers
- [ ] Enforce parameterized queries; avoid `EXEC`/`CALL` with concatenated strings
- [ ] Encrypt sensitive fields at rest (column-level encryption) where required
- [ ] Enforce DB connection over TLS
- [ ] Apply DB auditing if available

---

## 19. Infrastructure & OS Hardening

- [ ] Harden servers (CIS benchmarks): disable unnecessary services, close unused ports, apply latest OS patches
- [ ] Set strict file permissions for webroot, config files (no `777`)
- [ ] Remove default accounts and change default SSH port or use key-based access only
- [ ] Use SSH keys, disable password login for SSH
- [ ] Use firewall (ufw/iptables/security groups) to restrict access to needed ports only
- [ ] Disable directory listing in webserver
- [ ] Ensure `php.ini` hardened: disable `allow_url_fopen` if not needed, disable `exec`/`shell_exec` if possible, `expose_php = Off`
- [ ] Apply kernel hardening (sysctl) and enable AppArmor/SELinux where feasible

---

## 20. Webserver & Reverse Proxy Configuration

- [ ] Ensure correct DocumentRoot and no symlinks to sensitive directories
- [ ] Protect `.env`, `.git`, `storage` and `vendor` from web access (deny via webserver)
- [ ] Use a reverse proxy / CDN / WAF (Cloudflare, AWS WAF) to mitigate obvious attacks
- [ ] Enable request/response body size limits and request timeouts

---

## 21. Container / Orchestration Security (If Using)

- [ ] Use minimal base images, scan images for vulnerabilities
- [ ] Don't run containers as root
- [ ] Use image signing and a private registry
- [ ] Scan images with Clair/Trivy
- [ ] Use Kubernetes network policies and RBAC
- [ ] Secrets via k8s secrets + external secret manager, not env vars in manifests

---

## 22. CI/CD & Build Pipeline Security

- [ ] Scan dependencies during CI (SCA)
- [ ] Run linters, static analysis (PHPStan, Psalm) and security checks automatically
- [ ] Ensure CI runs tests and doesn't leak secrets in logs
- [ ] Limit who can approve deployments; protect production branches

---

## 23. Dependency & Supply Chain Security

- [ ] Lock dependencies via composer.lock and verify signatures where possible
- [ ] Monitor for new vulnerabilities (Dependabot, Snyk)
- [ ] Avoid installing packages with low maintenance or unknown provenance
- [ ] Consider reproducible builds

---

## 24. Automated Scanning & Tests (Must Run Before Pentest)

- [ ] SAST: Run Psalm, PHPStan with security rules
- [ ] DAST: Run OWASP ZAP and/or Burp Scanner against staging
- [ ] Dynamic analysis for OWASP Top10 and business logic checks
- [ ] Run `npm audit` and `composer audit`
- [ ] Use dependency vulnerability scanners (Snyk, Dependabot)
- [ ] Run interactive application security testing (IAST) if available
- [ ] Run nmap/nikto for basic network/webserver exposure scanning
- [ ] Run token/secret scanners (gitleaks/truffleHog)

---

## 25. Manual Security Review

- [ ] Manual code review of auth, crypto, input validation, file handling, and business logic
- [ ] Verify every endpoint requires expected authorization
- [ ] Test for business logic flaws (e.g., bypassing approvals, manipulating balances)
- [ ] Test IDORs, forced browsing, account takeover scenarios, privilege escalation

---

## 26. Common Attack Surfaces & Tests to Run

- [ ] SQL injection: test all inputs, header fields, and API params
- [ ] XSS: reflected, stored, DOM XSS in JSON flows, template injections
- [ ] CSRF: forms and state-changing GETs
- [ ] IDOR: object IDs, file access, downloads
- [ ] File upload: upload .php, mixed content, large files, path traversal
- [ ] Authentication bypass: reset flows, magic link, forgot password
- [ ] Session management: fixation, cookie stealing, logout behavior, concurrent sessions
- [ ] Business logic abuse: discount manipulation, negative amounts, sequence manipulation
- [ ] Race conditions: concurrent requests to change balances or state
- [ ] SSRF: image fetch, URL fetch endpoints, server-side URL validators
- [ ] Command injection: any system calls or shell exec
- [ ] Open redirect: URL params that redirect
- [ ] LDAP/XXE if XML endpoints exist
- [ ] GraphQL specific checks (if used): introspection, deep queries, injections
- [ ] API fuzzing for boundary testing

---

## 27. Privacy & Compliance

- [ ] Ensure data minimization and retention policy adhered to
- [ ] PII encryption and access control
- [ ] Prepare data processing and breach response plans
- [ ] Mask or tokenize logs where PII would otherwise appear

---

## 28. Operational & Staff Readiness

- [ ] Train ops/dev on incident response and have runbooks ready
- [ ] Ensure point persons available during pentest window to triage findings
- [ ] Prepare a vulnerability tracking workflow (Jira/GitHub issues) with severity and owner assignment

---

## 29. Penetration Test Logistics & Acceptance

- [ ] Provide pentesters with requisite test accounts and any disclaimers
- [ ] Decide on disclosure timeline and remediation expectations
- [ ] Ensure you have capacity to triage and patch high-impact findings quickly

---

## 30. Post-Test Actions

- [ ] Triage findings by severity, fix, and re-test
- [ ] Ensure changelogs for security fixes
- [ ] Re-run automated scans and regression tests
- [ ] Perform root cause analysis and preventive measures

---

## Quick Practical Checklist (High-Impact Actions)

### Immediate Priority (Do These First)

1. [ ] `APP_DEBUG=false` and remove debug consoles (Telescope, Ignition, debugbar)
2. [ ] Update Laravel/PHP and composer deps; run `composer audit`
3. [ ] Scan repo for secrets; rotate anything found
4. [ ] Enforce HTTPS + HSTS + modern TLS
5. [ ] Ensure CSRF + XSS protections and CSP in place
6. [ ] Protect `.env`, `.git`, `storage` from web access
7. [ ] Implement rate limiting on auth endpoints
8. [ ] Harden server (no world write, latest patches, firewall)
9. [ ] Run OWASP ZAP and Burp scans against staging
10. [ ] Provide test accounts and document scope

---

## Recommended Tools & Commands

### Static Analysis
```bash
# Run PHPStan
./vendor/bin/phpstan analyse

# Run Psalm
./vendor/bin/psalm
```

### Secret Scanning
```bash
# Install and run gitleaks
gitleaks detect --source . --verbose

# Install and run truffleHog
trufflehog git file://. --only-verified
```

### Dependency Scanning
```bash
# Composer audit
composer audit

# NPM audit
npm audit
```

### DAST Tools
- OWASP ZAP
- Burp Suite Professional
- Nikto

### Network Scanning
```bash
# Nmap scan
nmap -sV -sC <staging-host>

# Nikto scan
nikto -h <staging-url>
```

### Container Image Scanning
```bash
# Trivy scan
trivy image <image-name>
```

---

## Immediate Action Plan (Start Here)

### Phase 1: Snapshot & Baseline (Day 1)
1. [ ] Snapshot your staging environment (so you can revert)
2. [ ] Run `composer audit`
3. [ ] Check PHP version: `php -v`
4. [ ] Run PHPStan/Psalm and document top issues

### Phase 2: Quick Wins (Days 2-3)
1. [ ] Run a quick OWASP ZAP scan against staging
2. [ ] Fix highest severity items first (auth & public endpoints)
3. [ ] Scan for and rotate any credentials found in repo/history
4. [ ] Ensure `APP_DEBUG=false` on staging

### Phase 3: Systematic Review (Week 1)
1. [ ] Work through sections 1-10 of main checklist
2. [ ] Document findings and fixes
3. [ ] Re-run automated scans

### Phase 4: Advanced Hardening (Week 2)
1. [ ] Complete sections 11-20 of main checklist
2. [ ] Perform manual security review
3. [ ] Test common attack surfaces

### Phase 5: Final Preparation (Week 3)
1. [ ] Complete all remaining checklist items
2. [ ] Run full automated scan suite
3. [ ] Prepare test accounts and documentation for pentesters
4. [ ] Brief team on procedures during test

---

## Notes

- **Always test on staging, never on production**
- **Document all findings and remediations**
- **Maintain a severity-prioritized list of issues**
- **Have rollback procedures ready**
- **Ensure team availability during pentest window**

---

## Appendix A: OWASP Top 10 (2021) - Detailed Overview

The OWASP Top 10 represents the broad consensus about the most critical security risks to web applications. Use this as a guide for your security testing priorities.

### A01:2021 - Broken Access Control
**Risk**: 94% of applications tested had some form of broken access control vulnerability.

**Description**: Failures in access control allow unauthorized users to access data or functionality they shouldn't have access to.

**Common Vulnerabilities**:
- Bypassing access control checks by modifying URLs, internal application state, or HTML pages
- Allowing primary keys to be changed to access another user's record (IDOR)
- Elevation of privilege (acting as user without being logged in, or acting as admin when logged in as user)
- Metadata manipulation (replaying or tampering with JWT tokens, cookies, or hidden fields)
- CORS misconfiguration allowing unauthorized API access

**Prevention**:
- Deny by default; implement access control on trusted server-side code
- Use Laravel Gates and Policies for authorization checks
- Disable web server directory listing
- Log access control failures and alert admins on repeated failures
- Rate limit API and controller access

### A02:2021 - Cryptographic Failures
**Risk**: Previously known as "Sensitive Data Exposure"; focuses on failures related to cryptography.

**Description**: Failures that lead to exposure of sensitive data such as passwords, credit card numbers, health records, personal information, and business secrets.

**Common Vulnerabilities**:
- Transmitting data in clear text (HTTP, FTP, SMTP)
- Using old or weak cryptographic algorithms
- Using default crypto keys or weak/reused crypto keys
- Not enforcing encryption (missing HTTP security headers)
- Improper certificate validation

**Prevention**:
- Classify data and apply controls per classification
- Encrypt all sensitive data at rest and in transit
- Use TLS 1.2+ with strong cipher suites
- Use Laravel's encryption features for sensitive fields
- Disable caching for responses containing sensitive data
- Use proper key management (rotate keys, use HSM where appropriate)

### A03:2021 - Injection
**Risk**: 94% of applications were tested for some form of injection vulnerability.

**Description**: Application vulnerable to injection when user-supplied data is not validated, filtered, or sanitized. Includes SQL, NoSQL, OS command, ORM, LDAP, and Expression Language (EL) injection.

**Common Vulnerabilities**:
- SQL injection via user inputs
- Command injection via system calls
- LDAP injection
- Template injection
- XSS (now grouped under injection)

**Prevention**:
- Use Laravel Eloquent ORM or Query Builder with parameter binding
- Use parameterized queries everywhere
- Validate all inputs using Laravel validation rules
- Escape special characters using the appropriate escape syntax
- Use LIMIT and other SQL controls to prevent mass disclosure in SQL injection

### A04:2021 - Insecure Design (NEW)
**Risk**: New category focusing on risks related to design and architectural flaws.

**Description**: Represents different weaknesses expressed as "missing or ineffective control design." Focuses on threats and requires secure design patterns.

**Common Vulnerabilities**:
- Lack of threat modeling during design phase
- Missing security controls for business logic
- Insecure design patterns
- Lack of principle of least privilege in design

**Prevention**:
- Establish secure development lifecycle with security professionals
- Establish and use threat modeling for critical flows
- Write unit and integration tests to validate critical flows
- Use established secure design patterns and reference architectures
- Segregate tier layers and implement defense in depth

### A05:2021 - Security Misconfiguration
**Risk**: 90% of applications tested had some form of misconfiguration.

**Description**: Application might be vulnerable if the application has unnecessary features enabled, default accounts with unchanged passwords, overly informative error messages, or security settings not set to secure values.

**Common Vulnerabilities**:
- Missing security hardening or improperly configured permissions
- Unnecessary features enabled (debug mode, Telescope in production)
- Default accounts and passwords still enabled
- Error handling reveals stack traces or overly informative messages
- Security settings in frameworks not set to secure values
- Server missing security patches or outdated

**Prevention**:
- Remove unused features, components, frameworks, and documentation
- Ensure `APP_DEBUG=false` in production
- Review and update configurations as part of patch management
- Use automated verification of configuration effectiveness
- Implement segmented application architecture with proper separation

### A06:2021 - Vulnerable and Outdated Components
**Risk**: Using components with known vulnerabilities.

**Description**: Components run with the same privileges as the application. If a vulnerable component is exploited, such an attack can facilitate data loss or server takeover.

**Common Vulnerabilities**:
- Not knowing versions of all components used (client and server-side)
- Software is vulnerable, unsupported, or out of date
- Not scanning for vulnerabilities regularly
- Not fixing or upgrading the underlying platform, frameworks, and dependencies

**Prevention**:
- Remove unused dependencies, features, components, files, and documentation
- Continuously inventory versions of components using tools like `composer audit`, Snyk, or Dependabot
- Only obtain components from official sources over secure links
- Monitor for unmaintained libraries and components
- Have an ongoing plan for monitoring, triaging, and applying updates

### A07:2021 - Identification and Authentication Failures
**Risk**: Previously "Broken Authentication"; confirmation of user identity is critical.

**Description**: Failures that allow attackers to compromise passwords, keys, session tokens, or exploit implementation flaws to assume other users' identities.

**Common Vulnerabilities**:
- Permits automated attacks such as credential stuffing
- Permits brute force or other automated attacks
- Permits default, weak, or well-known passwords
- Uses weak or ineffective credential recovery processes
- Uses plain text, encrypted, or weakly hashed passwords
- Missing or ineffective multi-factor authentication
- Exposes session IDs in the URL
- Doesn't properly invalidate sessions after logout

**Prevention**:
- Implement multi-factor authentication
- Do not ship or deploy with default credentials
- Implement weak password checks (test against top 10,000 worst passwords)
- Use Laravel's built-in authentication with Argon2id or bcrypt
- Limit or increasingly delay failed login attempts using Laravel throttling
- Use server-side, secure, built-in session manager
- Regenerate session IDs after login using `session()->regenerate()`

### A08:2021 - Software and Data Integrity Failures (NEW)
**Risk**: New category focuses on making assumptions related to software updates, critical data, and CI/CD pipelines without verifying integrity.

**Description**: Code and infrastructure that does not protect against integrity violations. An example is where objects or data are encoded or serialized into a structure that an attacker can see and modify.

**Common Vulnerabilities**:
- Application relies on plugins, libraries, or modules from untrusted sources
- Insecure CI/CD pipeline allowing unauthorized access
- Auto-update functionality without sufficient integrity verification
- Insecure deserialization

**Prevention**:
- Use digital signatures to verify software comes from expected source
- Ensure libraries and dependencies use trusted repositories
- Use software supply chain security tools (OWASP Dependency Check, Snyk)
- Ensure your CI/CD pipeline has proper segregation, configuration, and access control
- Ensure unsigned or unencrypted serialized data is not sent to untrusted clients

### A09:2021 - Security Logging and Monitoring Failures
**Risk**: Previously "Insufficient Logging & Monitoring"; expanded scope.

**Description**: Without logging and monitoring, breaches cannot be detected. Insufficient logging, detection, monitoring, and active response occurs any time.

**Common Vulnerabilities**:
- Auditable events not logged (logins, failed logins, high-value transactions)
- Warnings and errors generate no, inadequate, or unclear log messages
- Logs not monitored for suspicious activity
- Logs only stored locally
- Alerting thresholds and response escalation processes not in place
- Penetration testing and scans do not trigger alerts

**Prevention**:
- Ensure all login, access control, and server-side input validation failures can be logged with sufficient user context
- Ensure logs are generated in a format that log management solutions can consume
- Ensure log data is encoded correctly to prevent injections or attacks
- Ensure high-value transactions have an audit trail with integrity controls
- Establish effective monitoring and alerting (suspicious activities detected and responded to quickly)
- Establish incident response and recovery plan (NIST 800-61 or similar)

### A10:2021 - Server-Side Request Forgery (SSRF) (NEW)
**Risk**: New addition from community survey; increasingly common.

**Description**: SSRF flaws occur when a web application fetches a remote resource without validating the user-supplied URL. Allows attacker to coerce the application to send a crafted request to an unexpected destination.

**Common Vulnerabilities**:
- Application fetches data from user-supplied URL without validation
- Application allows importing data from URLs
- Webhooks, file fetching from URLs, or document processors that fetch external resources

**Prevention**:
- Sanitize and validate all client-supplied input data
- Enforce URL schema, port, and destination with a positive allow list
- Do not send raw responses to clients
- Disable HTTP redirections
- Segment remote resource access functionality in separate networks
- Be aware of URL consistency to avoid attacks such as DNS rebinding

---

## Appendix B: Laravel Security Features - Implementation Guide

Laravel provides robust built-in security features. Ensure these are properly configured and utilized.

### 1. Authentication
**Built-in Features**:
- Laravel Breeze (simple authentication scaffolding)
- Laravel Fortify (backend authentication)
- Laravel Sanctum (API token authentication)
- Laravel Passport (full OAuth2 server)

**Implementation Checklist**:
- [ ] Use Laravel's authentication scaffolding (don't roll your own)
- [ ] Implement password hashing with Argon2id (preferred) or bcrypt
- [ ] Configure password requirements in validation rules
- [ ] Implement "remember me" securely using `remember_token`
- [ ] Use Laravel Sanctum for SPA/mobile API authentication
- [ ] Implement email verification for new accounts

**Configuration** (`config/auth.php`):
```php
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60, // Short expiry
        'throttle' => 60, // Rate limiting
    ],
],
```

### 2. Authorization (Gates & Policies)
**Built-in Features**:
- Gates: Simple closure-based authorization
- Policies: Class-based authorization for models

**Implementation Checklist**:
- [ ] Define Gates for simple authorization checks
- [ ] Create Policies for model-level authorization
- [ ] Use `@can` directive in Blade templates
- [ ] Use `authorize()` method in controllers
- [ ] Implement `authorizeResource()` for resource controllers
- [ ] Check authorization in API controllers

**Example Policy**:
```php
public function update(User $user, Post $post)
{
    return $user->id === $post->user_id;
}
```

### 3. CSRF Protection
**Built-in Features**:
- Automatic CSRF token generation
- `VerifyCsrfToken` middleware
- Blade `@csrf` directive

**Implementation Checklist**:
- [ ] Ensure `VerifyCsrfToken` middleware is active
- [ ] Include `@csrf` in all forms
- [ ] Configure CSRF token refresh for SPAs
- [ ] Exclude API routes from CSRF (use token authentication instead)
- [ ] Set proper `SameSite` cookie attribute

**Configuration** (`config/session.php`):
```php
'same_site' => 'strict', // or 'lax' if needed
```

### 4. XSS Prevention
**Built-in Features**:
- Automatic escaping with `{{ }}` in Blade
- `@verbatim` directive for Vue/React code

**Implementation Checklist**:
- [ ] Use `{{ $variable }}` for all user input display
- [ ] Only use `{!! $variable !!}` for trusted, sanitized HTML
- [ ] Implement Content Security Policy headers
- [ ] Sanitize HTML input with HTMLPurifier if rich text needed
- [ ] Validate and escape JSON responses

### 5. SQL Injection Prevention
**Built-in Features**:
- Eloquent ORM with automatic parameter binding
- Query Builder with parameter binding
- PDO prepared statements

**Implementation Checklist**:
- [ ] Use Eloquent ORM for all database operations
- [ ] Use Query Builder with parameter binding
- [ ] Never concatenate user input into SQL queries
- [ ] If raw queries needed, use bindings: `DB::raw('... WHERE id = ?', [$id])`
- [ ] Validate all inputs before database operations

### 6. Encryption & Hashing
**Built-in Features**:
- Application encryption key (`APP_KEY`)
- `Crypt` facade for encryption/decryption
- `Hash` facade for password hashing
- `Encryptable` cast for model attributes

**Implementation Checklist**:
- [ ] Generate strong `APP_KEY` using `php artisan key:generate`
- [ ] Use `Hash::make()` for passwords (never store plain text)
- [ ] Use `Hash::check()` for password verification
- [ ] Use `Crypt::encrypt()` for sensitive data
- [ ] Use `encrypted` cast for sensitive model fields
- [ ] Rotate encryption keys periodically

**Example**:
```php
use Illuminate\Database\Eloquent\Casts\Attribute;

protected function socialSecurity(): Attribute
{
    return Attribute::make(
        get: fn ($value) => decrypt($value),
        set: fn ($value) => encrypt($value),
    );
}
```

### 7. Rate Limiting
**Built-in Features**:
- `RateLimiter` facade
- `throttle` middleware
- Route-level and global rate limiting

**Implementation Checklist**:
- [ ] Configure rate limits in `RouteServiceProvider`
- [ ] Apply `throttle:60,1` middleware to API routes
- [ ] Implement stricter limits for authentication endpoints
- [ ] Use named rate limiters for different contexts
- [ ] Return proper 429 status codes
- [ ] Log rate limit violations

**Configuration** (`app/Providers/RouteServiceProvider.php`):
```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->email.$request->ip());
});
```

### 8. Session Security
**Built-in Features**:
- Secure session configuration
- Session regeneration
- Multiple session drivers (file, database, redis)

**Implementation Checklist**:
- [ ] Use database or Redis for session storage (not file in production)
- [ ] Set `SESSION_SECURE_COOKIE=true` in production
- [ ] Set `SESSION_HTTP_ONLY=true` (prevents JavaScript access)
- [ ] Set `SESSION_SAME_SITE=strict` or `lax`
- [ ] Call `session()->regenerate()` after login
- [ ] Invalidate session on logout properly
- [ ] Set appropriate session lifetime

**Configuration** (`.env`):
```env
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
```

### 9. Mass Assignment Protection
**Built-in Features**:
- `$fillable` property
- `$guarded` property
- Force mass assignment

**Implementation Checklist**:
- [ ] Define `$fillable` on all models (whitelist approach)
- [ ] Or use `$guarded` to blacklist specific fields
- [ ] Never use `$guarded = []` (disables protection)
- [ ] Be cautious with `forceFill()`
- [ ] Validate inputs before mass assignment

**Example**:
```php
protected $fillable = ['name', 'email', 'phone'];
// Or
protected $guarded = ['id', 'password', 'is_admin'];
```

### 10. File Upload Security
**Built-in Features**:
- Validated file uploads
- Secure file storage
- Signed URLs for private files

**Implementation Checklist**:
- [ ] Validate file uploads with rules: `mimes`, `max`, etc.
- [ ] Store uploads outside webroot or use S3
- [ ] Use `store()` or `storeAs()` methods
- [ ] Generate signed URLs for private files
- [ ] Scan uploaded files if necessary
- [ ] Randomize filenames to prevent overwrites

**Example**:
```php
$request->validate([
    'document' => 'required|file|mimes:pdf,doc,docx|max:10240',
]);

$path = $request->file('document')->store('documents', 's3');
```

---

## Appendix C: CIS Benchmarks - Implementation Guide

CIS (Center for Internet Security) Benchmarks provide prescriptive guidance for securing systems. These are consensus-based configurations developed by cybersecurity experts globally.

### What Are CIS Benchmarks?

CIS Benchmarks are configuration baselines and best practices for securely configuring systems, software, and networks. They help organizations:
- Reduce vulnerabilities
- Establish security baselines
- Meet compliance requirements
- Implement defense-in-depth strategies

### Relevant CIS Benchmarks for Laravel Applications

#### 1. CIS Linux Benchmarks
**Coverage**:
- User account management
- File system permissions
- Network configuration
- Service hardening
- Logging and auditing
- System maintenance

**Key Recommendations**:
- [ ] Apply latest security patches
- [ ] Disable unused services
- [ ] Configure firewall (iptables/firewalld)
- [ ] Enable SELinux or AppArmor
- [ ] Configure secure SSH (disable root login, use keys)
- [ ] Set proper file permissions (no 777)
- [ ] Enable system auditing (auditd)
- [ ] Configure NTP for time synchronization
- [ ] Harden kernel parameters (sysctl)

#### 2. CIS Apache/NGINX Benchmarks
**Coverage**:
- Server configuration
- Access controls
- SSL/TLS configuration
- Logging and monitoring
- DoS protection

**Key Recommendations**:
- [ ] Disable directory listing
- [ ] Hide server version information
- [ ] Configure secure SSL/TLS (disable weak ciphers)
- [ ] Implement access logging
- [ ] Set resource limits
- [ ] Protect sensitive directories (.env, .git)
- [ ] Configure security headers
- [ ] Implement rate limiting
- [ ] Use minimal modules/disable unused

#### 3. CIS PHP Benchmarks
**Coverage**:
- php.ini hardening
- Dangerous function controls
- Error handling
- Session security
- File upload restrictions

**Key Recommendations**:
- [ ] Set `expose_php = Off`
- [ ] Disable `allow_url_fopen` and `allow_url_include`
- [ ] Disable dangerous functions: `exec`, `shell_exec`, `system`, `passthru`
- [ ] Set `display_errors = Off` in production
- [ ] Configure `open_basedir` restrictions
- [ ] Set proper `upload_max_filesize` and `post_max_size`
- [ ] Enable `log_errors` and configure `error_log`
- [ ] Set `session.cookie_httponly = 1`
- [ ] Set `session.cookie_secure = 1`
- [ ] Configure `disable_functions`

**Example php.ini hardening**:
```ini
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
allow_url_fopen = Off
allow_url_include = Off
disable_functions = exec,passthru,shell_exec,system,proc_open,popen
open_basedir = /var/www/html:/tmp
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

#### 4. CIS Database Benchmarks (PostgreSQL/MySQL)
**Coverage**:
- Installation and configuration
- Authentication and authorization
- Network configuration
- Auditing and logging
- Data encryption

**Key Recommendations**:
- [ ] Use least-privilege database accounts
- [ ] Disable remote root access
- [ ] Enforce strong passwords
- [ ] Enable SSL/TLS for connections
- [ ] Configure audit logging
- [ ] Encrypt sensitive data at rest
- [ ] Regular backup and tested recovery
- [ ] Remove test databases and accounts
- [ ] Configure proper file permissions for data files

#### 5. CIS Docker Benchmarks (If Using Containers)
**Coverage**:
- Host configuration
- Docker daemon configuration
- Container images
- Container runtime
- Security operations

**Key Recommendations**:
- [ ] Use minimal base images
- [ ] Don't run containers as root
- [ ] Scan images for vulnerabilities
- [ ] Use read-only file systems where possible
- [ ] Limit container resources
- [ ] Use secrets management
- [ ] Enable Docker Content Trust
- [ ] Configure logging drivers
- [ ] Use network segmentation

### CIS Implementation Levels

CIS Benchmarks define two implementation levels:

**Level 1**: Basic security requirements that can be configured on any system with minimal impact to functionality.
- Recommended for all organizations
- Focus on foundational security practices
- Minimal service disruption

**Level 2**: Defense-in-depth measures for environments requiring higher security.
- Intended for high-security environments
- May reduce functionality or require additional expertise
- Suitable for handling sensitive data

### Tools for CIS Compliance

**Assessment Tools**:
- **CIS-CAT Pro** (official CIS assessment tool)
- **OpenSCAP** (open-source security compliance)
- **Lynis** (Linux system auditing)
- **Docker Bench for Security** (container security)

**Automation Tools**:
- Ansible CIS playbooks
- Chef/Puppet CIS cookbooks
- Terraform CIS modules

---

## Appendix D: NIST Cybersecurity Framework (CSF 2.0)

The NIST Cybersecurity Framework provides a policy framework of computer security guidance for organizations to assess and improve their ability to prevent, detect, and respond to cyber attacks.

### Framework Structure

The NIST CSF 2.0 is organized around **six core functions**:

### 1. GOVERN (GV)
**Purpose**: Establish and monitor cybersecurity risk management strategy, expectations, and policy.

**Key Categories**:
- Organizational Context
- Risk Management Strategy
- Roles, Responsibilities, and Authorities
- Policy
- Oversight
- Cybersecurity Supply Chain Risk Management

**Implementation for Laravel App**:
- [ ] Define security governance structure
- [ ] Establish security policies and procedures
- [ ] Define roles and responsibilities for security
- [ ] Create security awareness training program
- [ ] Establish third-party vendor security requirements
- [ ] Define acceptable use policies
- [ ] Create incident response governance

### 2. IDENTIFY (ID)
**Purpose**: Develop organizational understanding to manage cybersecurity risk to systems, people, assets, data, and capabilities.

**Key Categories**:
- Asset Management
- Risk Assessment
- Improvement

**Implementation for Laravel App**:
- [ ] Inventory all assets (servers, databases, applications, APIs)
- [ ] Classify data by sensitivity (PII, financial, public)
- [ ] Map data flows and trust boundaries
- [ ] Identify business-critical systems
- [ ] Document external information systems
- [ ] Conduct regular vulnerability assessments
- [ ] Perform threat modeling on critical flows
- [ ] Assess supply chain risks

### 3. PROTECT (PR)
**Purpose**: Develop and implement appropriate safeguards to ensure delivery of critical services.

**Key Categories**:
- Identity Management, Authentication and Access Control
- Awareness and Training
- Data Security
- Platform Security
- Technology Infrastructure Resilience

**Implementation for Laravel App**:
- [ ] Implement authentication and authorization controls
- [ ] Enforce principle of least privilege
- [ ] Encrypt data at rest and in transit
- [ ] Implement secure configuration management
- [ ] Deploy protective technology (WAF, IDS/IPS)
- [ ] Maintain and test backups
- [ ] Implement change control processes
- [ ] Conduct security awareness training
- [ ] Protect audit logs from tampering

### 4. DETECT (DE)
**Purpose**: Develop and implement appropriate activities to identify occurrence of cybersecurity events.

**Key Categories**:
- Continuous Monitoring
- Adverse Event Analysis

**Implementation for Laravel App**:
- [ ] Implement continuous security monitoring
- [ ] Enable logging for security events
- [ ] Deploy intrusion detection systems
- [ ] Monitor for unauthorized access attempts
- [ ] Implement anomaly detection
- [ ] Monitor system performance baselines
- [ ] Scan for malware and vulnerabilities
- [ ] Review logs regularly
- [ ] Establish security alerting thresholds

### 5. RESPOND (RS)
**Purpose**: Develop and implement appropriate activities to take action regarding a detected cybersecurity incident.

**Key Categories**:
- Incident Management
- Incident Analysis
- Incident Response Reporting and Communication
- Incident Mitigation

**Implementation for Laravel App**:
- [ ] Create incident response plan
- [ ] Establish incident response team
- [ ] Define incident classification and prioritization
- [ ] Document escalation procedures
- [ ] Implement incident tracking system
- [ ] Conduct incident response drills
- [ ] Establish communication protocols
- [ ] Define containment strategies
- [ ] Plan for evidence preservation and forensics

### 6. RECOVER (RC)
**Purpose**: Develop and implement appropriate activities to maintain plans for resilience and restore capabilities or services impaired due to a cybersecurity incident.

**Key Categories**:
- Incident Recovery Plan Execution
- Incident Recovery Communication

**Implementation for Laravel App**:
- [ ] Develop recovery procedures
- [ ] Test backup restoration regularly
- [ ] Document recovery time objectives (RTO)
- [ ] Document recovery point objectives (RPO)
- [ ] Create business continuity plans
- [ ] Establish recovery priorities
- [ ] Coordinate with external stakeholders
- [ ] Conduct post-incident analysis
- [ ] Update security controls based on lessons learned

### NIST CSF Implementation Tiers

The framework defines four tiers representing the degree of cybersecurity risk management sophistication:

**Tier 1: Partial**
- Ad hoc risk management
- Limited awareness
- Risk management integrated on case-by-case basis

**Tier 2: Risk Informed**
- Risk management practices approved but not policy
- Awareness but organization-wide approach not established
- Some integration with enterprise risk management

**Tier 3: Repeatable**
- Formal policies establish risk management practices
- Consistent methods across organization
- Regular updates based on changing risk

**Tier 4: Adaptive**
- Adaptive approach to changing cybersecurity landscape
- Advanced threat intelligence
- Continuous improvement culture

### NIST CSF Profiles

Profiles represent cybersecurity outcomes based on business needs. Organizations can:
- Create current state profile (as-is)
- Define target profile (to-be)
- Conduct gap analysis
- Prioritize actions to close gaps

### Mapping to Other Standards

NIST CSF 2.0 maps to other frameworks including:
- ISO/IEC 27001:2022
- NIST SP 800-53 Rev. 5
- NIST SP 800-171 Rev. 2
- CIS Controls v8
- COBIT 2019
- PCI DSS v4.0

---

## References & Additional Resources

### Primary References
- [OWASP Top 10 (2021)](https://owasp.org/www-project-top-ten/) - Most critical web application security risks
- [Laravel 11.x Documentation](https://laravel.com/docs/11.x) - Official Laravel security features
- [CIS Benchmarks](https://www.cisecurity.org/cis-benchmarks/) - Configuration best practices
- [NIST Cybersecurity Framework (CSF 2.0)](https://www.nist.gov/cyberframework) - Risk management framework

### Additional Security Resources
- [OWASP Web Security Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)
- [OWASP API Security Top 10](https://owasp.org/www-project-api-security/)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [Laravel Security Documentation](https://laravel.com/docs/11.x/authentication)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [NIST SP 800-53](https://csrc.nist.gov/publications/detail/sp/800-53/rev-5/final) - Security Controls
- [NIST SP 800-61](https://csrc.nist.gov/publications/detail/sp/800-61/rev-2/final) - Incident Response
- [CWE Top 25 Most Dangerous Software Weaknesses](https://cwe.mitre.org/top25/)
- [SANS Top 25 Programming Errors](https://www.sans.org/top25-software-errors/)

### Security Testing Tools
- [OWASP ZAP](https://www.zaproxy.org/) - Web application security scanner
- [Burp Suite](https://portswigger.net/burp) - Web vulnerability scanner
- [SQLMap](https://sqlmap.org/) - SQL injection testing tool
- [Nikto](https://cirt.net/Nikto2) - Web server scanner
- [Nmap](https://nmap.org/) - Network security scanner
- [Metasploit](https://www.metasploit.com/) - Penetration testing framework

### Compliance & Standards
- [PCI DSS](https://www.pcisecuritystandards.org/) - Payment card industry security
- [GDPR](https://gdpr.eu/) - General Data Protection Regulation
- [ISO/IEC 27001](https://www.iso.org/isoiec-27001-information-security.html) - Information security management
- [SOC 2](https://www.aicpa.org/interestareas/frc/assuranceadvisoryservices/aicpasoc2report.html) - Security compliance
