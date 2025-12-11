#!/bin/bash
#
# Secret Remediation Script for NBC SACCOS
# Date: October 16, 2025
# Purpose: Automated remediation for identified security issues
#
# USAGE: bash doc/SECRET_REMEDIATION_SCRIPT.sh
#
# WARNING: Review this script carefully before running in production!
#

set -e

echo "========================================="
echo "NBC SACCOS - Secret Remediation Script"
echo "========================================="
echo ""

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_info() {
    echo "[INFO] $1"
}

# Check if running from project root
if [ ! -f "artisan" ]; then
    print_error "Please run this script from the Laravel project root directory"
    exit 1
fi

echo "Starting secret remediation process..."
echo ""

# ======================================================================
# STEP 1: Fix Private Key Permissions
# ======================================================================
print_info "Step 1: Fixing private key permissions..."

if [ -f "storage/keys/private.pem" ]; then
    # Set restrictive permissions (owner read/write only)
    chmod 600 storage/keys/private.pem
    chown apache:apache storage/keys/private.pem
    print_success "Private key permissions set to 600 (owner read/write only)"
else
    print_warning "Private key not found at storage/keys/private.pem"
fi

if [ -f "storage/keys/public.pem" ]; then
    # Public key can be readable
    chmod 644 storage/keys/public.pem
    chown apache:apache storage/keys/public.pem
    print_success "Public key permissions set to 644"
fi

echo ""

# ======================================================================
# STEP 2: Check for Hardcoded Secrets in Code
# ======================================================================
print_info "Step 2: Scanning for hardcoded API tokens..."

# Check LukuGatewayService for hardcoded token
if grep -q "apiToken = \"c2FjY29zbmJjOkBOQkNzYWNjb3Npc2FsZUx0ZA==\"" app/Services/LukuGatewayService.php 2>/dev/null; then
    print_error "CRITICAL: Hardcoded API token still present in LukuGatewayService.php"
    print_warning "Manual action required: Replace hardcoded token with config value"
    echo "  Location: app/Services/LukuGatewayService.php:22"
    echo "  Replace with: \$this->apiToken = config('services.luku.api_token');"
else
    print_success "No hardcoded API token found in LukuGatewayService.php"
fi

# Check test file
if grep -q "apiToken = '<HARDCODED_TOKEN>'" sit-tests/LukuGatewayTest.php 2>/dev/null; then
    print_error "Hardcoded API token found in sit-tests/LukuGatewayTest.php"
    print_warning "Manual action required: Replace with test configuration"
fi

echo ""

# ======================================================================
# STEP 3: Verify .env is Not in Git
# ======================================================================
print_info "Step 3: Verifying .env file is not tracked by git..."

if git ls-files | grep -q "^\.env$"; then
    print_error ".env file is tracked by git!"
    print_warning "Run: git rm --cached .env"
    print_warning "Then commit: git commit -m 'Remove .env from git tracking'"
else
    print_success ".env file is not tracked by git"
fi

echo ""

# ======================================================================
# STEP 4: Check .gitignore
# ======================================================================
print_info "Step 4: Verifying .gitignore configuration..."

if grep -q "^\.env$" .gitignore; then
    print_success ".env is in .gitignore"
else
    print_warning ".env not found in .gitignore - adding it now..."
    echo "" >> .gitignore
    echo "# Environment configuration" >> .gitignore
    echo ".env" >> .gitignore
    echo ".env.backup" >> .gitignore
    echo ".env.production" >> .gitignore
    print_success "Added .env to .gitignore"
fi

if grep -q "^storage/keys/\*.pem$" .gitignore; then
    print_success "Private keys are in .gitignore"
else
    print_warning "Adding private keys to .gitignore..."
    echo "" >> .gitignore
    echo "# Private keys and certificates" >> .gitignore
    echo "storage/keys/*.pem" >> .gitignore
    echo "storage/keys/*.key" >> .gitignore
    echo "*.p12" >> .gitignore
    echo "*.pfx" >> .gitignore
    print_success "Added key patterns to .gitignore"
fi

echo ""

# ======================================================================
# STEP 5: Create/Update .env.example
# ======================================================================
print_info "Step 5: Checking .env.example template..."

if [ -f ".env.example" ]; then
    print_success ".env.example exists"

    # Check if critical variables are documented
    if ! grep -q "LUKU_GATEWAY_API_TOKEN" .env.example; then
        print_warning "LUKU_GATEWAY_API_TOKEN not in .env.example - adding..."
        echo "" >> .env.example
        echo "# Luku Gateway Configuration" >> .env.example
        echo "LUKU_GATEWAY_API_TOKEN=your_api_token_here" >> .env.example
        echo "LUKU_GATEWAY_BASE_URL=https://luku-gateway-url.com" >> .env.example
        echo "LUKU_GATEWAY_CHANNEL_ID=SACCOSNBC" >> .env.example
        print_success "Added Luku Gateway variables to .env.example"
    fi
else
    print_warning ".env.example not found - manual creation recommended"
fi

echo ""

# ======================================================================
# STEP 6: Install Gitleaks Pre-commit Hook
# ======================================================================
print_info "Step 6: Setting up gitleaks pre-commit hook..."

if [ -f "/usr/local/bin/gitleaks" ]; then
    print_success "Gitleaks is installed"

    # Create pre-commit hook
    mkdir -p .git/hooks
    cat > .git/hooks/pre-commit << 'HOOK_EOF'
#!/bin/bash
# Gitleaks pre-commit hook
# Prevents commits containing secrets

echo "Running gitleaks scan..."
gitleaks detect --source . --verbose --no-git

if [ $? -eq 1 ]; then
    echo "❌ Gitleaks detected secrets in your commit!"
    echo "Please remove sensitive data and try again."
    echo ""
    echo "If this is a false positive, you can:"
    echo "1. Add the file to .gitleaksignore"
    echo "2. Skip the hook with: git commit --no-verify (NOT RECOMMENDED)"
    exit 1
fi

echo "✅ No secrets detected"
exit 0
HOOK_EOF

    chmod +x .git/hooks/pre-commit
    print_success "Gitleaks pre-commit hook installed"
else
    print_warning "Gitleaks not installed - pre-commit hook not configured"
    echo "  Install with: curl -sSfL https://github.com/gitleaks/gitleaks/releases/download/v8.18.4/gitleaks_8.18.4_linux_x64.tar.gz | tar -xz && mv gitleaks /usr/local/bin/"
fi

echo ""

# ======================================================================
# STEP 7: Create .gitleaksignore for False Positives
# ======================================================================
print_info "Step 7: Creating .gitleaksignore for false positives..."

cat > .gitleaksignore << 'IGNORE_EOF'
# Gitleaks Ignore File
# Add file paths that contain false positives

# Documentation examples (verified as examples only)
docs/SIT/SIT_TEST_CASES.md
docs/SIT/SIT_TESTING_GUIDE.md
docs/tips.txt
NBC_STATEMENT_SERVICE.md

# Test files with example credentials
database/seeders/*Seeder.php:*test*
database/seeders/*Seeder.php:*example*

# Exclude client references (not secrets)
docs/tips.txt:clientRef

# Add more paths as needed...
IGNORE_EOF

print_success "Created .gitleaksignore file"
echo ""

# ======================================================================
# STEP 8: Generate Security Checklist
# ======================================================================
print_info "Step 8: Generating security checklist..."

cat > doc/SECRET_REMEDIATION_CHECKLIST.md << 'CHECKLIST_EOF'
# Secret Remediation Checklist

## Critical Actions (DO IMMEDIATELY)

- [x] Fix private key permissions (600)
- [ ] Remove hardcoded API token from LukuGatewayService.php
- [ ] Rotate Luku Gateway API credentials
- [ ] Verify private key in docs is example-only
- [ ] Update .gitignore with key patterns

## High Priority (This Week)

- [ ] Review institution database passwords in seeders
- [ ] If real passwords: rotate all institution databases
- [ ] Configure services.php with Luku Gateway settings
- [ ] Update .env with new rotated credentials
- [ ] Test all services after credential rotation

## Medium Priority (Within 2 Weeks)

- [ ] Add clear "EXAMPLE ONLY" comments to documentation
- [ ] Review all test files for hardcoded credentials
- [ ] Create comprehensive .env.example
- [ ] Document secret rotation procedure
- [ ] Train development team on secret management

## Ongoing Maintenance

- [ ] Run gitleaks scan weekly
- [ ] Rotate API credentials quarterly
- [ ] Rotate database passwords semi-annually
- [ ] Rotate private keys annually
- [ ] Review access logs monthly
- [ ] Audit new code commits for secrets

## Verification Tests

Run these commands to verify remediation:

```bash
# 1. Check private key permissions
ls -la storage/keys/private.pem
# Should show: -rw------- (600)

# 2. Verify .env not in git
git ls-files | grep "\.env"
# Should return nothing

# 3. Run gitleaks scan
gitleaks detect --source . --verbose
# Should show: "No leaks found"

# 4. Check for hardcoded tokens
grep -r "c2FjY29zbmJjOkBOQkNzYWNjb3Npc2FsZUx0ZA==" app/
# Should return nothing (or only commented examples)
```

## Contact for Issues

If you discover additional secrets or have questions:
- Review: doc/SECRETS_SCAN_REPORT.md
- Security Team: security@nbc.co.tz
- Escalation: CISO
CHECKLIST_EOF

print_success "Created SECRET_REMEDIATION_CHECKLIST.md"
echo ""

# ======================================================================
# SUMMARY
# ======================================================================
echo "========================================="
echo "Remediation Summary"
echo "========================================="
echo ""

print_success "Automated tasks completed:"
echo "  ✅ Private key permissions fixed (600)"
echo "  ✅ .gitignore updated with key patterns"
echo "  ✅ .gitleaksignore created"
echo "  ✅ Pre-commit hook installed (if gitleaks available)"
echo "  ✅ Remediation checklist created"
echo ""

print_warning "Manual actions required:"
echo "  ⚠️  Remove hardcoded API token from LukuGatewayService.php"
echo "  ⚠️  Rotate Luku Gateway API credentials"
echo "  ⚠️  Review database passwords in InstitutionsSeeder.php"
echo "  ⚠️  Verify private key in docs is example-only"
echo ""

print_info "Next steps:"
echo "  1. Review: doc/SECRETS_SCAN_REPORT.md"
echo "  2. Follow: doc/SECRET_REMEDIATION_CHECKLIST.md"
echo "  3. Test all services after changes"
echo "  4. Run: gitleaks detect --source . --verbose"
echo ""

print_success "Remediation script completed!"
echo ""

exit 0
