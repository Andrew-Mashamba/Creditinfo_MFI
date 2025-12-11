# Firewall Exception Request - Email Services

**Date**: September 2, 2025  
**System**: SACCOS Management System  
**Server**: tzukawrapsmms01.tz.af.absa.local (22.32.221.43)  
**Environment**: UAT  

## Request Summary
Enable email functionality for SACCOS system by allowing standard email protocols.

## Required Firewall Rules

### Outbound (Sending Emails)
```
Source: 22.32.221.43
Destination: ANY
Ports: TCP 25, 465, 587
Direction: OUTBOUND
Purpose: Send emails via SMTP
```

### Inbound (Receiving Emails)
```
Source: ANY
Destination: 22.32.221.43
Ports: TCP 25, 143, 993, 110, 995
Direction: INBOUND
Purpose: Receive emails via SMTP/IMAP/POP3
```

## Business Impact
- Users cannot login (OTP emails blocked)
- No transaction notifications
- No system alerts
- Manual intervention required for every authentication

## Testing
```bash
# Test connectivity after approval
/var/www/html/template/test-smtp-connectivity.sh
```

## Approval Required
- [ ] Network Security Team
- [ ] IT Operations

**Note**: Standard email ports required for dynamic mail server connections as per business requirements.

---
**Priority**: URGENT - Blocking UAT