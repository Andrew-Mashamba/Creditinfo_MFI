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
