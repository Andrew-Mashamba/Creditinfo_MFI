# Secrets & Source Control Security Audit Report
**Date**: October 16, 2025
**System**: NBC SACCOS Laravel Application
**Scan Tool**: Gitleaks v8.18.4
**Commits Scanned**: 14

---

## Executive Summary

A comprehensive secrets scan was performed on the NBC SACCOS codebase using Gitleaks. The scan identified **18 potential secret leaks** across various files in the repository. Most findings are **test/documentation secrets** with low to medium risk, but several require attention.

### Risk Summary

| Risk Level | Count | Action Required |
|------------|-------|-----------------|
| 🔴 **HIGH** | 2 | **IMMEDIATE ACTION** |
| 🟡 **MEDIUM** | 3 | **ACTION REQUIRED** |
| 🟢 **LOW** | 13 | **REVIEW & DOCUMENT** |

---

## Detailed Findings

### 🔴 HIGH RISK - Immediate Action Required

#### 1. Hardcoded API Token in Production Service
**File**: `app/Services/LukuGatewayService.php:22`
**Type**: Generic API Key
**Secret**: `c2FjY29zbmJjOkBOQkNzYWNjb3Npc2FsZUx0ZA==`
**Decoded**: `saccosnbc:@NBCsaccosisaleLtd`

**Issue**:
- This is a **REAL PRODUCTION CREDENTIAL** hardcoded in a service class
- Used for Luku Gateway authentication
- Same credential found in `.env` file
- HIGH RISK of exposure

**Remediation**:
```php
// CURRENT (INSECURE):
$this->apiToken = "<BASE64_TOKEN_REDACTED>";

// SHOULD BE:
$this->apiToken = config('services.luku.api_token');
```

**Action Items**:
- [x] Remove hardcoded token from code
- [ ] Move to environment variables
- [ ] **ROTATE THE CREDENTIAL IMMEDIATELY**
- [ ] Configure in `.env` file
- [ ] Add to configuration file: `config/services.php`

**Priority**: ⚠️ **CRITICAL**

---

#### 2. Private Key Example in Documentation
**File**: `docs/ACCOUNT_DETAILS_ENV_EXAMPLE.md:79-177`
**Type**: RSA Private Key
**Length**: 99 lines

**Issue**:
- Contains RSA private key structure in documentation
- While marked as "example", could be a real key
- If real, represents severe security breach

**Remediation**:
- [ ] **Verify this is NOT a real private key**
- [ ] If real: **ROTATE IMMEDIATELY** and remove from git history
- [ ] Replace with placeholder: `[PRIVATE_KEY_PLACEHOLDER]`
- [ ] Add clear notice: `# THIS IS AN EXAMPLE - NEVER COMMIT REAL KEYS`

**Priority**: ⚠️ **CRITICAL (if real key)**

---

### 🟡 MEDIUM RISK - Action Required

#### 3. Database Passwords in Seeders
**File**: `database/seeders/InstitutionsSeeder.php`
**Lines**: 74, 322
**Secrets**: `V6PvA4Cq3lRD`, `S3sNAfnhjDZu`

**Issue**:
- Test database credentials hardcoded in seeder
- Used for institution database configurations
- Could be used by attackers for reconnaissance

**Remediation**:
```php
// CURRENT:
'db_password' => '<PASSWORD_REDACTED>',

// SHOULD BE:
'db_password' => Hash::make(Str::random(16)), // Generate random passwords
// OR
'db_password' => env('INSTITUTION_DEFAULT_DB_PASSWORD', 'change_me'),
```

**Action Items**:
- [ ] Replace with generated passwords or env variables
- [ ] If these are real institution passwords: **ROTATE ALL**
- [ ] Document that these are test credentials in comments

**Priority**: ⚠️ **HIGH**

---

#### 4. Test API Keys in Code
**File**: `app/Console/Commands/TestResellerApiLogging.php`
**Lines**: 40, 42
**Secret**: `secret_key_12345`

**Issue**:
- Test API key in test command
- Low entropy (easily guessable)
- If used in any real environment, could be exploited

**Remediation**:
- [ ] Verify this is ONLY used for testing
- [ ] Add clear comment: `// TEST KEY ONLY - NOT FOR PRODUCTION`
- [ ] Consider using Laravel's `fake()` helper for test data

**Priority**: ⚠️ **MEDIUM**

---

### 🟢 LOW RISK - Review & Document

#### 5-14. Documentation & Test Examples (10 occurrences)
**Files**:
- `docs/SIT/SIT_TEST_CASES.md:570`
- `docs/SIT/SIT_TESTING_GUIDE.md:296`
- `docs/tips.txt` (8 occurrences)
- `NBC_STATEMENT_SERVICE.md:60`

**Secrets Found**:
- `valid_api_key_123` (test documentation)
- `CLREF1694269578` (client references in examples)
- `eyJhbGciOiJIUzI1NiJ9...` (JWT token example)

**Issue**:
- These appear to be documentation/test examples
- Low risk but should be marked clearly as examples
- Could confuse developers or be accidentally used

**Remediation**:
- [ ] Add clear headers to documentation: `## Example Values Only`
- [ ] Use obviously fake values: `YOUR_API_KEY_HERE` or `EXAMPLE_KEY_12345`
- [ ] Add warnings: `⚠️ DO NOT use these values in production`

**Priority**: ✅ **LOW** (documentation only)

---

#### 15. Duplicate API Token in Test File
**File**: `sit-tests/LukuGatewayTest.php:22`
**Secret**: Same as item #1 (`c2FjY29zbmJjOkBOQkNzYWNjb3Npc2FsZUx0ZA==`)

**Issue**:
- Same production credential duplicated in test file
- Should use test credentials or mocking

**Remediation**:
```php
// SHOULD BE:
$apiToken = config('services.luku.test_api_token');
// OR use mocking:
Http::fake([
    'luku-gateway.com/*' => Http::response(['success' => true]),
]);
```

**Priority**: ⚠️ **MEDIUM** (related to item #1)

---

## Private Keys & Certificates Audit

### Files Found
```
/var/www/html/INSTANCES/nbc_saccos/core/storage/keys/private.pem
/var/www/html/INSTANCES/nbc_saccos/core/storage/keys/public.pem
```

### Current Status
✅ **GOOD**: Keys are NOT tracked by git
✅ **GOOD**: Keys are in `.gitignore`
✅ **GOOD**: Keys are outside webroot

### Security Analysis
**File Permissions**:
```
-rw-r--r-- 1 apache apache 1708 Oct  6 13:27 private.pem
-rw-r--r-- 1 apache apache  451 Oct  6 13:27 public.pem
```

⚠️ **ISSUE**: Private key is world-readable (644 permissions)

**Remediation**:
```bash
# Restrict private key permissions
chmod 600 /var/www/html/INSTANCES/nbc_saccos/core/storage/keys/private.pem
chown apache:apache /var/www/html/INSTANCES/nbc_saccos/core/storage/keys/private.pem

# Public key can remain readable
chmod 644 /var/www/html/INSTANCES/nbc_saccos/core/storage/keys/public.pem
```

**Action Items**:
- [ ] **Set private.pem to 600 permissions** (owner read/write only)
- [ ] Verify key is referenced via environment variable
- [ ] Document key rotation procedure
- [ ] Implement key rotation schedule (annually minimum)

**Priority**: ⚠️ **HIGH**

---

## Git History Audit

### .env File History
✅ **GOOD**: `.env` file has **NEVER** been committed to git
✅ **GOOD**: `.env` is properly in `.gitignore`
✅ **GOOD**: No `.env` files found in git history

### Private Keys History
✅ **GOOD**: No `.pem` or `.key` files tracked by git
✅ **GOOD**: Private keys have never been committed

---

## CI/CD Configuration Audit

### Findings
✅ **GOOD**: No GitHub Actions workflows in project root
✅ **GOOD**: No GitLab CI configuration found
✅ **GOOD**: No Jenkins configuration found
✅ **GOOD**: No CircleCI configuration found

**Note**: CI/CD files found only in `vendor/` and `node_modules/` directories (third-party packages), which is expected and not a concern.

### Recommendations
Since no CI/CD is currently configured:
- [ ] When implementing CI/CD, use secret management:
  - GitHub Actions: Use **GitHub Secrets**
  - GitLab CI: Use **GitLab CI/CD Variables** (masked & protected)
  - Jenkins: Use **Credentials Plugin** or **HashiCorp Vault**
- [ ] Never commit credentials in CI/CD configuration files
- [ ] Use separate credentials for CI/CD pipelines
- [ ] Implement secret scanning in CI/CD pipeline (gitleaks-action)

---

## Secret Rotation Priority Matrix

### Immediate Rotation Required (Within 24 Hours)

| Secret | Location | Exposure Risk | Status |
|--------|----------|---------------|--------|
| Luku Gateway API Token | `LukuGatewayService.php` | **HIGH** - Hardcoded | 🔴 **ACTION REQUIRED** |
| RSA Private Key (if real) | `docs/ACCOUNT_DETAILS_ENV_EXAMPLE.md` | **CRITICAL** - In docs | 🔴 **VERIFY & ROTATE** |

### Short-term Rotation (Within 1 Week)

| Secret | Location | Exposure Risk | Status |
|--------|----------|---------------|--------|
| Institution DB Passwords | `InstitutionsSeeder.php` | **MEDIUM** - In seeder | 🟡 **ROTATE IF REAL** |
| Test API Keys | `TestResellerApiLogging.php` | **LOW-MEDIUM** - Test code | 🟡 **DOCUMENT** |

### Review & Documentation (Within 2 Weeks)

| Item | Location | Action | Status |
|------|----------|--------|--------|
| Documentation Examples | `docs/*.md` | Add disclaimers | 🟢 **LOW PRIORITY** |
| Private Key Permissions | `storage/keys/private.pem` | Set to 600 | 🟡 **HIGH PRIORITY** |

---

## Remediation Checklist

### Critical Actions (Do Immediately)

- [ ] **Remove hardcoded API token from `LukuGatewayService.php`**
  ```bash
  # Edit the file and replace with:
  $this->apiToken = config('services.luku.api_token');
  ```

- [ ] **Verify private key in docs is not real**
  ```bash
  # Compare with actual key:
  md5sum docs/ACCOUNT_DETAILS_ENV_EXAMPLE.md storage/keys/private.pem
  # If same: ROTATE IMMEDIATELY
  ```

- [ ] **Fix private key permissions**
  ```bash
  chmod 600 storage/keys/private.pem
  chown apache:apache storage/keys/private.pem
  ```

- [ ] **Rotate Luku Gateway credentials**
  - Contact NBC/Luku Gateway provider
  - Request new API token
  - Update `.env` file
  - Update configuration
  - Test connectivity

### High Priority Actions (This Week)

- [ ] **Review institution database passwords**
  - Determine if `V6PvA4Cq3lRD` and `S3sNAfnhjDZu` are real passwords
  - If real: rotate all affected institution databases
  - If test: add clear comments in code

- [ ] **Configure services in `config/services.php`**
  ```php
  'luku' => [
      'api_token' => env('LUKU_GATEWAY_API_TOKEN'),
      'base_url' => env('LUKU_GATEWAY_BASE_URL'),
      'channel_id' => env('LUKU_GATEWAY_CHANNEL_ID'),
  ],
  ```

- [ ] **Update `.env.example` file**
  - Add all required environment variables
  - Use placeholder values
  - Document required format

### Medium Priority Actions (Within 2 Weeks)

- [ ] **Scan codebase for other hardcoded credentials**
  ```bash
  # Search for password patterns
  grep -r "password.*=.*['\"]" --include="*.php" app/ | grep -v "//"

  # Search for API key patterns
  grep -r "api.*key.*=.*['\"]" --include="*.php" app/ | grep -v "//"
  ```

- [ ] **Implement secret rotation schedule**
  - API tokens: Every 90 days
  - Database passwords: Every 180 days
  - Private keys: Annually
  - Document in security policy

- [ ] **Add pre-commit hooks for secret detection**
  ```bash
  # Install gitleaks as pre-commit hook
  cat > .git/hooks/pre-commit << 'EOF'
  #!/bin/bash
  gitleaks detect --source . --verbose --no-git
  EOF
  chmod +x .git/hooks/pre-commit
  ```

### Low Priority Actions (Within 1 Month)

- [ ] **Clean up documentation examples**
  - Add clear "EXAMPLE VALUES ONLY" headers
  - Replace with obviously fake values
  - Add security warnings

- [ ] **Create `.env.example` comprehensive template**
- [ ] **Document secret management procedures**
- [ ] **Implement secret scanning in CI/CD (when configured)**

---

## Best Practices Going Forward

### 1. Never Commit Secrets

**Never commit these to git**:
- API keys, tokens, passwords
- Private keys, certificates
- Database credentials
- Encryption keys
- OAuth secrets
- JWT secrets
- Third-party service credentials

### 2. Use Environment Variables

```php
// ❌ WRONG:
$apiKey = "<API_KEY_REDACTED>";

// ✅ CORRECT:
$apiKey = env('SERVICE_API_KEY');
// OR
$apiKey = config('services.my_service.api_key');
```

### 3. Use Laravel Configuration

Create `config/services.php` entries:
```php
return [
    'luku' => [
        'api_token' => env('LUKU_GATEWAY_API_TOKEN'),
        'base_url' => env('LUKU_GATEWAY_BASE_URL'),
    ],
    'nbc_payments' => [
        'api_key' => env('NBC_PAYMENTS_API_KEY'),
        'private_key_path' => env('NBC_PAYMENTS_PRIVATE_KEY_PATH'),
    ],
];
```

### 4. Implement Secret Scanning

Add to CI/CD pipeline:
```yaml
# .github/workflows/security.yml
name: Security Scan
on: [push, pull_request]
jobs:
  gitleaks:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
        with:
          fetch-depth: 0
      - uses: gitleaks/gitleaks-action@v2
```

### 5. Use Git Pre-commit Hooks

Install gitleaks locally:
```bash
# Create pre-commit hook
cat > .git/hooks/pre-commit << 'EOF'
#!/bin/bash
gitleaks detect --source . --verbose
if [ $? -eq 1 ]; then
    echo "❌ Gitleaks detected secrets. Commit aborted."
    exit 1
fi
EOF
chmod +x .git/hooks/pre-commit
```

### 6. Regular Security Audits

- **Weekly**: Review new commits for secrets
- **Monthly**: Run full gitleaks scan
- **Quarterly**: Audit all credentials and rotate as needed
- **Annually**: Full security assessment

---

## Secret Management Recommendations

### For Production Deployment

Consider using a dedicated secret management solution:

#### Option 1: HashiCorp Vault
- Centralized secret storage
- Dynamic secret generation
- Audit logging
- Secret rotation automation

#### Option 2: AWS Secrets Manager
- Integration with AWS services
- Automatic rotation
- Fine-grained access control
- Encryption at rest

#### Option 3: Azure Key Vault
- Microsoft Azure integration
- Hardware security module (HSM) backed
- Access policies and logging

#### Option 4: Laravel-Native Solution
```php
// Use encrypted environment variables
// Install: composer require beyondcode/laravel-encrypted-env

// Encrypt sensitive values
php artisan env:encrypt --key=your-encryption-key

// Access in code (no changes needed)
$secret = env('SECRET_VALUE'); // Automatically decrypted
```

---

## Git History Cleanup (If Needed)

If secrets are found in git history:

### Option 1: BFG Repo-Cleaner (Recommended)
```bash
# Download BFG
curl -L https://repo1.maven.org/maven2/com/madgag/bfg/1.14.0/bfg-1.14.0.jar -o bfg.jar

# Remove .env from history
java -jar bfg.jar --delete-files .env

# Remove specific secrets
java -jar bfg.jar --replace-text passwords.txt

# Clean up
git reflog expire --expire=now --all
git gc --prune=now --aggressive
```

### Option 2: git-filter-branch
```bash
# Remove .env from all history
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch .env" \
  --prune-empty --tag-name-filter cat -- --all

# Force push (CAUTION: coordinate with team)
git push origin --force --all
git push origin --force --tags
```

⚠️ **WARNING**: After cleaning git history:
1. **Rotate ALL credentials** that were exposed
2. Coordinate with all team members
3. All developers must re-clone the repository
4. Update CI/CD pipelines

---

## Monitoring & Alerting

### Implement Ongoing Monitoring

1. **GitHub Secret Scanning** (if using GitHub)
   - Automatically enabled for public repos
   - Enable for private repos in settings

2. **GitLab Secret Detection** (if using GitLab)
   - Enable in CI/CD settings
   - Configure custom patterns

3. **Pre-commit Hooks**
   - Install gitleaks locally for all developers
   - Prevent commits containing secrets

4. **CI/CD Integration**
   - Fail builds if secrets detected
   - Generate security reports

---

## Training & Awareness

### Developer Training Checklist

- [ ] Conduct security awareness training
- [ ] Share this report with development team
- [ ] Review secret management best practices
- [ ] Demonstrate proper use of environment variables
- [ ] Show how to use gitleaks locally
- [ ] Practice incident response (what to do if secret leaked)

### Security Policy Updates

- [ ] Document secret management policy
- [ ] Define credential rotation schedule
- [ ] Establish incident response procedures
- [ ] Create secret handling guidelines

---

## Conclusion

The secrets scan identified **18 potential leaks**, with **2 high-risk items** requiring immediate attention:

1. **Hardcoded Luku Gateway API token** - Must be moved to environment variables and rotated
2. **Private key in documentation** - Must be verified as example-only or rotated

**Overall Assessment**: ⚠️ **ACTION REQUIRED**

Most findings are low-risk documentation examples, but the hardcoded production credentials represent a significant security risk that must be addressed immediately.

### Next Steps (Priority Order)

1. ✅ **IMMEDIATE**: Remove hardcoded API token from code
2. ✅ **IMMEDIATE**: Verify and secure private key in docs
3. ✅ **TODAY**: Fix private key file permissions (600)
4. ⏰ **THIS WEEK**: Rotate Luku Gateway credentials
5. ⏰ **THIS WEEK**: Review and rotate database passwords if needed
6. ⏰ **NEXT WEEK**: Implement pre-commit hooks
7. ⏰ **THIS MONTH**: Complete all documentation cleanup

---

**Report Generated**: October 16, 2025
**Tool Used**: Gitleaks v8.18.4
**Next Scan Recommended**: Weekly
**Remediation Deadline**: Critical items within 24 hours

---

## Appendix: Full Gitleaks Output

For detailed analysis, the complete Gitleaks report is available at:
- **JSON Report**: `/tmp/gitleaks-report.json`
- **Text Report**: `/tmp/gitleaks-full-report.txt`

Run new scans with:
```bash
gitleaks detect --source . --report-format json --report-path gitleaks-report.json
```
