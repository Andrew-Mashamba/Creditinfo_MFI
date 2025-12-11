# Loan Writeoff System Documentation

## Overview
The Loan Writeoff System is a comprehensive solution for managing bad loans in SACCOS, implementing IFRS 9 standards and regulatory compliance requirements.

## System Architecture

### Database Tables
1. **loan_write_offs** - Main writeoff records
2. **loan_writeoff_recoveries** - Post-writeoff recovery tracking
3. **loan_collection_efforts** - Collection attempts documentation
4. **writeoff_approval_workflow** - Multi-level approval system
5. **writeoff_analytics** - Analytics and reporting data
6. **writeoff_member_communications** - Member notification tracking

### Configuration
All writeoff thresholds are stored in the `institutions` table:
- `writeoff_board_approval_threshold` - Default: TZS 1,000,000
- `writeoff_manager_approval_threshold` - Default: TZS 500,000
- `writeoff_minimum_collection_efforts` - Default: 3 attempts
- `writeoff_recovery_tracking_period` - Default: 36 months

## Features

### 1. Loan Classification
Loans are classified based on days in arrears:
- **PERFORMING**: 0 days (1% provision)
- **WATCH**: 1-30 days (5% provision)
- **SUBSTANDARD**: 31-90 days (25% provision)
- **DOUBTFUL**: 91-180 days (50% provision)
- **LOSS**: 181+ days (100% provision)

### 2. Eligibility Criteria
Loans eligible for writeoff must meet:
- Classification: LOSS
- Days in arrears: > 180 days
- Minimum collection efforts documented
- Proper approval obtained

### 3. Approval Workflow

#### Approval Levels
1. **Manager** - For amounts below manager threshold
2. **Director** - For amounts above manager threshold
3. **CEO** - For significant amounts
4. **Board** - For amounts above board threshold
5. **External Auditor** - For special cases

#### Approval Process
```php
// Initialize workflow
WriteoffApprovalWorkflow::initializeWorkflow($writeOff);

// Approve at current level
$workflow->approve($comments, $conditions);

// Reject writeoff
$workflow->reject($reason);

// Escalate to higher level
$workflow->escalate($reason, $nextLevel);
```

### 4. Collection Efforts Documentation

#### Effort Types
- `call` - Phone calls
- `sms` - SMS messages
- `email` - Email communications
- `visit` - Physical visits
- `letter` - Written letters
- `legal_notice` - Legal notices
- `court_summons` - Court proceedings

#### Outcome Types
- `promise_to_pay` - Client promises payment
- `payment_made` - Payment received
- `dispute` - Client disputes debt
- `no_response` - No client response
- `unreachable` - Cannot contact client
- `partial_payment` - Partial payment made
- `request_extension` - Extension requested

### 5. Recovery Tracking

#### Recovery Methods
- `cash` - Direct cash payment
- `collateral_sale` - Sale of collateral
- `legal_settlement` - Legal settlement
- `insurance_claim` - Insurance recovery
- `debt_forgiveness` - Partial forgiveness
- `other` - Other methods

#### Recovery Sources
- `client` - Direct from borrower
- `guarantor` - From loan guarantors
- `collateral` - From asset sale
- `legal` - Through legal process

### 6. Member Communication

#### Communication Channels
- SMS notifications
- Email notifications
- Formal letters (PDF generation)
- Phone call logging
- Meeting documentation

#### Communication Stages
1. **Initiated** - Writeoff initiated notification
2. **Approved** - Writeoff approved notification
3. **Recovery** - Recovery efforts notification

### 7. Analytics & Reporting

#### Available Reports
- Summary statistics
- Trend analysis (weekly/monthly/quarterly)
- Recovery analysis
- Collection efficiency
- Portfolio impact
- Regulatory compliance metrics
- Automated recommendations

#### Key Metrics
- Total writeoffs amount
- Recovery rate percentage
- NPL (Non-Performing Loans) ratio
- Provision coverage ratio
- Collection success rate
- Board approval compliance

## Usage

### Writing Off a Loan

```php
// 1. Document collection efforts
$effort = LoanCollectionEffort::create([
    'loan_id' => $loanId,
    'effort_date' => now(),
    'effort_type' => 'call',
    'outcome' => 'no_response',
    'staff_id' => auth()->id()
]);

// 2. Create writeoff
$writeOff = LoanWriteOff::create([
    'loan_id' => $loanId,
    'write_off_date' => now(),
    'total_amount' => $amount,
    'reason' => 'Prolonged default',
    'initiated_by' => auth()->id()
]);

// 3. Initialize approval workflow
WriteoffApprovalWorkflow::initializeWorkflow($writeOff);

// 4. Send member notification
$communicationService->sendWriteoffNotification($writeOff, 'initiated');
```

### Recording Recovery

```php
$recovery = LoanWriteoffRecovery::create([
    'writeoff_id' => $writeOffId,
    'recovery_date' => now(),
    'recovery_amount' => $amount,
    'recovery_method' => 'cash',
    'recovery_source' => 'client',
    'recorded_by' => auth()->id()
]);

// Approve recovery
$recovery->approve();

// Update writeoff recovery status
$writeOff->updateRecoveryStatus();
```

### Generating Analytics

```php
$analyticsService = new WriteoffAnalyticsService();
$report = $analyticsService->generateReport(
    $dateFrom,
    $dateTo,
    'monthly' // or 'weekly', 'quarterly'
);

// Access report sections
$summary = $report['summary'];
$trends = $report['trends'];
$recovery = $report['recovery_analysis'];
$recommendations = $report['recommendations'];
```

## API Endpoints

### Livewire Components
- `/active-loans/write-offs` - Main writeoff management interface

### Key Methods
```php
// Writeoff management
$component->initiateWriteOff($loanId);
$component->processWriteOff();
$component->approveWriteOff($writeOffId);

// Recovery management
$component->initiateRecovery($writeOffId);
$component->processRecovery();
$component->approveRecovery($recoveryId);

// Collection efforts
$component->addCollectionEffort($loanId);
$component->processCollectionEffort();

// Analytics
$component->showAnalytics();
$component->getWriteoffAnalytics();
$component->exportWriteOffs();
```

## Security & Compliance

### Audit Trail
Every action is logged with:
- User identification
- Timestamp
- IP address
- User agent
- Action details
- Before/after states

### Regulatory Compliance
- IFRS 9 compliance for credit losses
- SASRA requirements for SACCOs
- Board approval for large writeoffs
- Minimum documentation requirements
- Member notification requirements

### Access Control
- Role-based permissions
- Approval level restrictions
- Audit trail access controls
- Report generation permissions

## Maintenance

### Regular Tasks
1. **Daily**: Process pending approvals
2. **Weekly**: Review collection efforts
3. **Monthly**: Generate analytics reports
4. **Quarterly**: Board reporting
5. **Annually**: Policy review

### Database Optimization
```sql
-- Index optimization
CREATE INDEX idx_writeoffs_date_status ON loan_write_offs(write_off_date, status);
CREATE INDEX idx_recoveries_date ON loan_writeoff_recoveries(recovery_date);
CREATE INDEX idx_efforts_loan ON loan_collection_efforts(loan_id, effort_date);
```

### Monitoring
```php
// Check pending approvals
$pending = WriteoffApprovalWorkflow::getPendingApprovals();

// Monitor recovery rates
$recoveryRate = LoanWriteOff::getRecoveryRate($from, $to);

// Collection efficiency
$efficiency = LoanCollectionEffort::getEffortivenessByStaff($from, $to);
```

## Troubleshooting

### Common Issues

1. **Missing collection efforts**
   - Ensure minimum efforts are documented before writeoff
   - Check `writeoff_minimum_collection_efforts` setting

2. **Approval workflow stuck**
   - Check pending approvals in workflow table
   - Verify approver assignments
   - Check escalation rules

3. **Communication failures**
   - Verify SMS/Email service configuration
   - Check delivery status in communications table
   - Review error logs

### Error Codes
- `WO001` - Insufficient collection efforts
- `WO002` - Approval workflow error
- `WO003` - Communication delivery failure
- `WO004` - Recovery validation error
- `WO005` - Analytics generation error

## Best Practices

1. **Documentation**: Always document all collection efforts before writeoff
2. **Approval**: Ensure proper approval levels based on amount
3. **Communication**: Send timely notifications to members
4. **Recovery**: Continue recovery efforts post-writeoff
5. **Reporting**: Generate regular analytics for management
6. **Compliance**: Maintain audit trail for regulatory reviews

## Support

For issues or questions:
1. Check error logs in `storage/logs/`
2. Review audit trail in database
3. Contact system administrator
4. Refer to IFRS 9 guidelines

---
*Last Updated: January 2025*
*Version: 1.0.0*