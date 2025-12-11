# Firewall Exception Request - SMTP Ports for SACCOS System

**Date**: September 2, 2025  
**System**: SACCOS Management System  
**Environment**: UAT  
**Priority**: High

## Executive Summary
The SACCOS Management System requires outbound SMTP connectivity to send critical email notifications including:
- OTP codes for two-factor authentication
- Transaction notifications
- System alerts and reports
- User account notifications

Currently, all SMTP ports are blocked, preventing the system from sending emails, which impacts user authentication and system notifications.

## Requestor Information
- **System**: SACCOS Management System
- **Server Hostname**: tzukawrapsmms01.tz.af.absa.local
- **Server IP**: 22.32.221.43
- **Application URL**: http://saccos-uat.intra.nbc.co.tz
- **Environment**: UAT (User Acceptance Testing)

## Technical Requirements

### Primary SMTP Server (Zima Email Service)
**Server**: server354.web-hosting.com  
**IP Address**: 192.64.117.3  
**Required Ports**:
- **Port 465** (SMTPS/SSL) - PRIMARY
- **Port 587** (SMTP/TLS) - ALTERNATIVE
- **Port 25** (SMTP) - FALLBACK

**Protocol**: TCP Outbound  
**Direction**: From 22.32.221.43 → server354.web-hosting.com

### Alternative: Office 365 SMTP (If NBC uses Microsoft Email)
**Server**: smtp.office365.com  
**Required Ports**:
- **Port 587** (SMTP/TLS)

**Protocol**: TCP Outbound  
**Direction**: From 22.32.221.43 → smtp.office365.com

## Business Justification

### Critical System Functions Affected:
1. **User Authentication**: OTP codes cannot be delivered, blocking user logins
2. **Transaction Confirmations**: Members don't receive transaction notifications
3. **Compliance Reports**: Automated reports cannot be sent to regulators
4. **Security Alerts**: Critical security notifications are not delivered
5. **Account Management**: Password resets and account confirmations fail

### Current Impact:
- Users unable to complete two-factor authentication
- Manual intervention required for all OTP codes
- Delayed notification of critical transactions
- Increased security risk due to lack of email alerts

## Security Considerations

### Authentication:
- SMTP connections use authenticated sessions
- Credentials stored securely in encrypted configuration
- SSL/TLS encryption for all email transmissions

### Rate Limiting:
- Application implements rate limiting (100 emails/hour)
- Queue system prevents email flooding
- Monitoring in place for unusual activity

### Compliance:
- Emails contain no sensitive financial data in plain text
- All communications logged for audit purposes
- Compliant with NBC security policies

## Firewall Rules Requested

### Rule 1: Primary SMTP Server
```
Source IP: 22.32.221.43
Destination: server354.web-hosting.com (192.64.117.3)
Ports: TCP 465, 587, 25
Direction: Outbound
Action: Allow
```

### Rule 2: Office 365 (Optional Backup)
```
Source IP: 22.32.221.43
Destination: smtp.office365.com
Port: TCP 587
Direction: Outbound
Action: Allow
```

## Testing Plan

Once firewall rules are implemented:

1. **Connection Test**:
```bash
telnet server354.web-hosting.com 465
telnet server354.web-hosting.com 587
```

2. **SSL Certificate Verification**:
```bash
openssl s_client -connect server354.web-hosting.com:465
```

3. **Application Test**:
```bash
php artisan tinker
>>> Mail::raw('Firewall test', function($m) { 
>>>     $m->to('test@nbc.co.tz')->subject('SMTP Test'); 
>>> });
```

## Current Workaround
Emails are currently being logged to local files instead of being sent. This is not sustainable for production use and requires manual intervention for every OTP request.

## Rollback Plan
If issues arise after firewall changes:
1. Revert to log driver (current workaround)
2. Document any connection errors
3. Work with network team to troubleshoot

## Contacts

### Application Team:
- System Administrator
- Email: admin@nbc.co.tz

### Network Team:
- To be filled by NBC IT

## Approval Required From:
- [ ] Network Security Team
- [ ] IT Operations Manager
- [ ] Information Security Officer
- [ ] System Owner

## Additional Notes
- This is a standard SMTP configuration used by many enterprise applications
- Similar rules likely exist for other NBC systems
- Can provide packet capture data if needed for troubleshooting
- Alternative email gateway solutions can be discussed if direct SMTP is not permitted

---

**Submitted by**: SACCOS Development Team  
**Date**: September 2, 2025  
**Ticket Reference**: [To be assigned]