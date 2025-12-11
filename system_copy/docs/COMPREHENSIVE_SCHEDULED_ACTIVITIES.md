# Comprehensive Scheduled Activities Report
## NBC SACCOS System - Complete Activity Schedule Framework

**Generated:** 2025-11-28
**System:** NBC SACCOS Core Banking System
**Analysis Scope:** All 30 system components

---

## Executive Summary

This document provides a comprehensive analysis of all scheduled activities recommended for the NBC SACCOS banking system. The analysis covers **30 system components** and identifies **500+ scheduled activities** across various frequencies:

| Frequency | Activity Count | Primary Focus |
|-----------|---------------|---------------|
| Real-time/Minutes | 25+ | Transaction monitoring, fraud detection, payment status |
| Hourly | 40+ | Queue processing, health monitoring, cache updates |
| Daily | 150+ | Core banking operations, reconciliation, reporting |
| Weekly | 80+ | Performance analysis, compliance checks, trend reports |
| Monthly | 100+ | Financial close, statements, regulatory reports |
| Quarterly | 60+ | Audits, policy reviews, strategic assessments |
| Semi-Annual | 15+ | Policy reviews, major assessments |
| Annual | 40+ | Year-end processing, regulatory filings, strategic planning |

---

## Table of Contents

1. [Currently Scheduled Tasks](#currently-scheduled-tasks)
2. [Real-Time/Minute Activities](#real-timeminute-activities)
3. [Hourly Activities](#hourly-activities)
4. [Daily Activities](#daily-activities)
5. [Weekly Activities](#weekly-activities)
6. [Monthly Activities](#monthly-activities)
7. [Quarterly Activities](#quarterly-activities)
8. [Annual Activities](#annual-activities)
9. [Component-Specific Summaries](#component-specific-summaries)
10. [Implementation Recommendations](#implementation-recommendations)

---

## Currently Scheduled Tasks

The following tasks are already configured in `app/Console/Kernel.php`:

| Schedule | Command | Description |
|----------|---------|-------------|
| Every hour (at :00) | `reports:generate-scheduled` | Generate scheduled reports |
| 2:00 PM daily | `standing-instructions:execute` | Execute standing instructions (afternoon) |
| 2:00 AM daily | `reports:cleanup-old-files` | Clean up old report files |
| 12:05 AM daily | `system:daily-activities` | Run daily system activities |
| 1:00 AM daily | `nbc:daily-reconciliation` | Run NBC daily reconciliation |
| 6:00 AM daily | `standing-instructions:execute` | Execute standing instructions (morning) |
| 6:00 AM Sundays | `reports:generate-weekly` | Generate weekly reports |
| 11:30 PM on 30th | `budget:period-close --type=monthly` | Close monthly budget period |
| 11:00 PM on 30th | `sacco:run-monthly-activities` | Run monthly SACCO activities |
| 7:00 AM on 1st | `reports:generate-monthly` | Generate monthly reports |
| 9:00 AM on 5th | `provision:cycle MONTHLY` | Run monthly loan provisioning |
| 11:45 PM quarterly | `budget:period-close --type=quarterly` | Close quarterly budget period |
| 11:00 PM quarterly | `sacco:run-quarterly-activities` | Run quarterly SACCO activities |
| 9:30 AM quarterly | `provision:cycle QUARTERLY` | Run quarterly loan provisioning |

---

## Real-Time/Minute Activities

### Transaction Processing (Every 1-5 Minutes)
| Activity | Component | Description |
|----------|-----------|-------------|
| Payment Status Monitoring | Payments | Poll external APIs for pending payment status updates |
| Failed Payment Retry | Payments | Process retry queue with exponential backoff |
| Transaction Validation | Transactions | Real-time fraud detection and validation |
| Rate Limit Monitoring | Security | Monitor API rate limits, block violations |
| Circuit Breaker Checks | Payments | Monitor external service health |

### Security & Monitoring (Real-Time)
| Activity | Component | Description |
|----------|-----------|-------------|
| Fraud Detection | Transactions | ML-based anomaly detection on transactions |
| Security Alerts | System | Failed login attempts, unauthorized access |
| OTP Delivery | Authentication | One-time password delivery |
| Transaction Alerts | Notifications | Large/unusual transaction alerts |

---

## Hourly Activities

### Financial Operations
| Activity | Component | Time | Description |
|----------|-----------|------|-------------|
| Cash Position Monitoring | Cash Management | :00 | Monitor vault/till balances |
| Teller Limit Alerts | Teller Management | :00 | Check transaction limit breaches |
| Vault Capacity Check | Cash Management | :00 | Monitor vault thresholds |
| Payment Queue Processing | Payments | :00 | Process pending payment batches |
| FSP Health Monitoring | Payments | :00 | Check payment gateway status |

### Reporting & Notifications
| Activity | Component | Time | Description |
|----------|-----------|------|-------------|
| Scheduled Reports | Reports | :00 | Generate hourly scheduled reports |
| Scheduled Email Processing | Email | :00 | Process scheduled email queue |
| Dashboard Cache Refresh | Dashboard | :30 | Update dashboard metrics cache |
| Approval SLA Tracking | Approvals | :00 | Check SLA breaches |

---

## Daily Activities

### Core Daily Schedule (00:05 - system:daily-activities)

#### Loan Processing
| Activity | Service | Description |
|----------|---------|-------------|
| Loan Interest Accrual | LoanInterestAccrualService | Calculate daily interest on all active loans |
| Loan Repayment Processing | OptimizedDailyLoanService | Process due repayments |
| Arrears Calculation | DailySystemActivitiesService | Update arrears status and aging |
| NPL Classification | DailySystemActivitiesService | Classify loans (WATCH, SUBSTANDARD, DOUBTFUL, LOSS) |
| Loan Loss Provisions | DailySystemActivitiesService | Update daily provisions |

#### Savings & Deposits
| Activity | Service | Description |
|----------|---------|-------------|
| Savings Interest Accrual | SavingsInterestAccrualService | Calculate daily savings interest |
| Fixed Deposit Maturity | DailySystemActivitiesService | Process maturing deposits |
| Recurring Deposit Processing | DailySystemActivitiesService | Process scheduled deposits |
| Deposit Balance Updates | DailySystemActivitiesService | Update account balances |

#### Share Management
| Activity | Service | Description |
|----------|---------|-------------|
| Share Transaction Processing | DailySystemActivitiesService | Process share transactions |
| Share Balance Updates | DailySystemActivitiesService | Update member share balances |
| Dividend Accrual | DailySystemActivitiesService | Accrue daily dividends |

#### Financial Reconciliation
| Activity | Service | Description |
|----------|---------|-------------|
| Bank Reconciliation | NBCDailyReconciliationService | Fetch and match bank statement |
| Standing Instructions | StandingInstructionsService | Execute recurring transfers (06:00 & 14:00) |
| General Ledger Update | TransactionPostingService | Post daily journal entries |
| Trial Balance Generation | DailySystemActivitiesService | Generate daily trial balance |

#### Member Services
| Activity | Service | Description |
|----------|---------|-------------|
| Client Status Updates | ClientStatusService | Update member activity status |
| KYC Alert Generation | KycComplianceService | Flag expiring documents |
| Dormancy Checks | MemberDormancyService | Identify inactive accounts |
| Member Statement Generation | ReportGenerationService | Prepare daily statements |

#### System Maintenance
| Activity | Service | Description |
|----------|---------|-------------|
| Database Backup | DailySystemActivitiesService | Create daily backup |
| Log Cleanup | DailySystemActivitiesService | Archive/clean old logs |
| Cache Clearing | DailySystemActivitiesService | Clear expired cache |
| Temp File Cleanup | DailySystemActivitiesService | Remove temporary files |

#### Compliance & Reporting
| Activity | Service | Description |
|----------|---------|-------------|
| Audit Trail Generation | AuditLogService | Log daily activities |
| Risk Assessment Updates | DailySystemActivitiesService | Update risk indicators |
| Regulatory Reports | ReportGenerationService | Generate required reports |

### Additional Daily Activities

| Time | Activity | Component | Description |
|------|----------|-----------|-------------|
| 01:00 | NBC Daily Reconciliation | Reconciliation | Bank statement matching |
| 02:00 | Report Cleanup | Reports | Archive old report files |
| 03:00 | Expense Accrual Processing | Expenses | Record daily accruals |
| 04:00 | Budget Utilization Update | Budget | Recalculate budget usage |
| 05:00 | Investment Value Updates | Investment | Update portfolio values |
| 06:00 | Standing Instructions (AM) | Transactions | Execute morning batch |
| 07:00 | Payment Due Alerts | Billing | Send payment reminders |
| 08:00 | Approval Reminders | Approvals | Escalate pending approvals |
| 09:00 | Till End-of-Day Review | Teller Management | Verify till closures |
| 10:00 | Insurance Expiry Alerts | Insurance | Flag expiring policies |
| 12:00 | Suspicious Activity Check | Security | Mid-day fraud review |
| 14:00 | Standing Instructions (PM) | Transactions | Execute afternoon batch |
| 16:00 | Daily Summary Reports | Reports | Management summaries |

---

## Weekly Activities

### Sunday Activities (06:00 AM)
| Activity | Component | Description |
|----------|-----------|-------------|
| Weekly Reports Generation | Reports | Generate all weekly reports |
| Weekly Loan Portfolio Summary | Loans | Comprehensive loan analysis |
| Weekly Arrears Analysis | Loans | 7-sheet arrears report |
| Variance Analysis Report | Budget | Budget vs actual analysis |
| Department Spending Review | Budget | Department-level analysis |

### Monday Activities
| Activity | Component | Description |
|----------|-----------|-------------|
| Weekly Payment Performance | Payments | Payment metrics analysis |
| Branch Performance Report | Branches | Branch comparison report |
| Staff Allocation Review | HR | Staffing analysis |

### Mid-Week Activities
| Activity | Component | Description |
|----------|-----------|-------------|
| Failed Payment Analysis | Payments | Root cause analysis |
| Reconciliation Exception Review | Reconciliation | Review unmatched items |
| Transaction Limit Review | Transactions | Review limit exceptions |
| Vendor Performance Report | Procurement | Vendor scorecard |

### Friday Activities
| Activity | Component | Description |
|----------|-----------|-------------|
| Weekly System Audit | Approvals | Approval system verification |
| Cash Ordering Planning | Cash Management | Next week cash requirements |
| Contract Renewal Alerts | Procurement | Contracts expiring in 60 days |

---

## Monthly Activities

### Month-End Close (Last Day, 23:00-23:30)
| Activity | Command/Service | Description |
|----------|----------------|-------------|
| Budget Period Close | budget:period-close --type=monthly | Close monthly budget |
| Monthly SACCO Activities | sacco:run-monthly-activities | End-of-month processing |
| Financial Period Close | FinancialReportingService | Close accounting period |
| Expense Reconciliation | ExpenseReportService | Monthly expense close |

### First Week of Month
| Time | Activity | Component | Description |
|------|----------|-----------|-------------|
| 1st 07:00 | Monthly Reports | Reports | Generate all monthly reports |
| 1st 08:00 | Member Statements | Members Portal | Generate/distribute statements |
| 1st 09:00 | Membership Fee Processing | Members | Process monthly fees |
| 2nd 06:00 | Dividend Processing | Shares | Monthly dividend calculations |
| 3rd 07:00 | NAV Calculations | Investment | Update fund values |
| 3rd 09:00 | Fixed Deposit Maturity Report | Deposits | Maturing deposits review |
| 4th 08:00 | Loan Portfolio Review | Loans | Monthly portfolio analysis |
| 5th 09:00 | Provision Cycle | Loans | provision:cycle MONTHLY |

### Mid-Month Activities
| Day | Activity | Component | Description |
|-----|----------|-----------|-------------|
| 10th | Monthly Teller Audit | Teller Management | Variance analysis |
| 15th | Budget Variance Report | Budget | Mid-month budget review |
| 15th | Vendor Payment Run | Procurement | Monthly vendor payments |
| 15th | Payroll Processing | HR | Process monthly payroll |
| 20th | Compliance Report | Compliance | Monthly compliance status |

### Financial Statements (Monthly)
| Report | Service | Description |
|--------|---------|-------------|
| Statement of Financial Position | FinancialReportingService | Balance Sheet |
| Statement of Comprehensive Income | FinancialReportingService | Income Statement |
| Statement of Cash Flow | FinancialReportingService | Cash Flow Statement |
| Trial Balance | FinancialReportingService | Account balances |
| Capital Adequacy Report | FinancialReportingService | BOT requirement |
| Liquid Assets Report | FinancialReportingService | Liquidity compliance |

### Regulatory Reports (Monthly)
| Report | Compliance | Description |
|--------|------------|-------------|
| Sectoral Classification of Loans | BOT | Loans by sector |
| Loans to Insiders | BOT | Related party lending |
| Interest Rates Structure | BOT | Rate transparency |
| Geographical Distribution | BOT | Branch/loan distribution |
| Delinquency Report | BOT | NPL analysis |

---

## Quarterly Activities

### Quarter-End Close
| Activity | Command/Service | Time | Description |
|----------|----------------|------|-------------|
| Quarterly Budget Close | budget:period-close --type=quarterly | 23:45 | Close quarterly budget |
| Quarterly SACCO Activities | sacco:run-quarterly-activities | 23:00 | End-of-quarter processing |
| Quarterly Provision Cycle | provision:cycle QUARTERLY | 09:30 | Loan loss provisions |

### First Week of Quarter
| Day | Activity | Component | Description |
|-----|----------|-----------|-------------|
| 1st | Quarterly Financial Statements | Accounting | Full financial statements |
| 2nd | Portfolio Rebalancing | Investment | Asset allocation review |
| 3rd | Quarterly Performance Review | All | Comprehensive metrics |
| 5th | Quarterly Provision Cycle | Loans | Loan provisions |

### Strategic Reviews
| Activity | Component | Description |
|----------|-----------|-------------|
| Quarterly Risk Assessment | Risk Management | Credit, market, operational risk |
| Budget Revision Assessment | Budget | Budget performance review |
| Approval Matrix Review | Approvals | Authority levels review |
| Vendor Evaluation | Procurement | Vendor performance ratings |
| Product Portfolio Review | Products | Product performance analysis |
| Branch Performance Review | Branches | Strategic branch assessment |
| Member Status Reviews | Members | Client risk profiling |

### Compliance Activities
| Activity | Component | Description |
|----------|-----------|-------------|
| AML/CFT Compliance Check | Compliance | Anti-money laundering review |
| Internal Controls Assessment | Audit | Control effectiveness |
| Regulatory Compliance Report | Compliance | BOT compliance status |
| Insurance Coverage Review | Insurance | Policy adequacy |

---

## Annual Activities

### Year-End Processing (December)
| Day | Activity | Component | Description |
|-----|----------|-----------|-------------|
| 15th | Annual Rate Reset | Products | Interest rate reviews |
| 20th | Year-End Provisions | Loans | Final provision calculations |
| 28th | Annual Statement Preparation | Reports | Year-end financials |
| 31st | Year Close | Accounting | Close financial year |

### January Activities
| Day | Activity | Component | Description |
|-----|----------|-----------|-------------|
| 1st | New Year Opening | Accounting | Open new financial year |
| 2nd | Budget Preparation Kickoff | Budget | Annual budget cycle |
| 5th | KYC Refresh Cycle | Members | Annual KYC review |
| 10th | Regulatory Annual Filing | Compliance | BOT annual returns |
| 15th | Investment Policy Review | Investment | Annual policy review |

### Annual Reviews & Audits
| Activity | Component | Description |
|----------|-----------|-------------|
| Annual Financial Audit | Accounting | External audit support |
| Annual Compliance Audit | Compliance | Full compliance review |
| Annual Policy Reviews | All | Policy document updates |
| Strategic Planning | Management | Annual strategy setting |
| Tax Reporting | Accounting | Annual tax filings |
| Dividend Distribution | Shares | Annual member dividends |
| AGM Notifications | Members | Annual General Meeting |
| Vendor Contract Renewals | Procurement | Annual vendor reviews |
| System Capacity Planning | IT | Infrastructure planning |
| Staff Training Programs | HR | Annual training plans |

---

## Component-Specific Summaries

### 1. Dashboard
- **Daily**: KPI cache updates, alert generation
- **Hourly**: Metric aggregation, chart data refresh

### 2. Shares
- **Daily**: Share balance updates, dividend accrual
- **Monthly**: Dividend calculations, share value updates
- **Quarterly**: Dividend eligibility review
- **Annually**: Annual dividend distribution

### 3. Savings
- **Daily**: Interest accrual, balance updates
- **Monthly**: Interest capitalization, statement generation
- **Quarterly**: Dormancy checks, compliance reviews
- **Annually**: Tax reporting, rate reviews

### 4. Deposits
- **Daily**: Interest accrual, maturity monitoring
- **Monthly**: Interest capitalization, renewal processing
- **Quarterly**: Rate competitiveness review
- **Annually**: Policy reviews, rate optimization

### 5. Loans
- **Daily**: Interest accrual, arrears calculation, NPL classification
- **Weekly**: Delinquency reports, collection tracking
- **Monthly**: Provisioning, portfolio analysis
- **Quarterly**: Stress testing, risk assessment
- **Annually**: Write-off reviews, policy updates

### 6. Accounting
- **Daily**: GL posting, trial balance
- **Monthly**: Period close, financial statements
- **Quarterly**: Quarterly statements, audit prep
- **Annually**: Year-end close, audit support

### 7. Reconciliation
- **Daily**: Bank statement fetch, transaction matching
- **Weekly**: Exception review, aging reports
- **Monthly**: Full reconciliation, discrepancy resolution
- **Quarterly**: Comprehensive audit

### 8. HR/Payroll
- **Daily**: Attendance tracking
- **Monthly**: Payroll processing, deductions
- **Quarterly**: Performance reviews
- **Annually**: Tax returns, benefits enrollment

### 9. Budget
- **Daily**: Utilization tracking, alert monitoring
- **Weekly**: Variance analysis
- **Monthly**: Period close, reforecast
- **Quarterly**: Budget revisions
- **Annually**: Budget preparation cycle

### 10. Insurance
- **Daily**: Claims processing, policy expiry alerts
- **Weekly**: Premium collection follow-ups
- **Monthly**: Premium reconciliation, commission calculations
- **Quarterly**: Loss ratio analysis
- **Annually**: Policy renewals, actuarial reviews

### 11. Billing
- **Daily**: Invoice generation, payment reminders
- **Weekly**: Aging reports, collection follow-ups
- **Monthly**: Statement generation, fee calculations
- **Quarterly**: Fee reviews, audits
- **Annually**: Rate reviews, policy updates

### 12. Transactions
- **Real-time**: Validation, fraud detection
- **Daily**: EOD processing, reconciliation
- **Weekly**: Exception reviews
- **Monthly**: Transaction summaries
- **Quarterly/Annual**: Audits, pattern analysis

### 13. Teller Management
- **Hourly**: Cash position, limit alerts
- **Daily**: Till balancing, vault reconciliation
- **Weekly**: Performance reviews, cash ordering
- **Monthly**: Audits, shortage analysis
- **Quarterly**: Security reviews

### 14. Cash Management
- **Hourly**: Liquidity monitoring
- **Daily**: Vault balancing, cash flow forecasting
- **Weekly**: Cash ordering, denomination analysis
- **Monthly**: Cost analysis, insurance reviews
- **Quarterly**: Handling audits

### 15. Approvals
- **Hourly**: SLA tracking, pending alerts
- **Daily**: Aging analysis, escalation processing
- **Weekly**: Performance reports
- **Monthly**: Bottleneck analysis
- **Quarterly**: Matrix reviews

### 16. Reports
- **Hourly**: Scheduled report generation
- **Daily**: Daily operations reports
- **Weekly**: Weekly summaries
- **Monthly**: Regulatory reports, financial statements
- **Quarterly**: Quarterly filings
- **Annually**: Annual reports, audit packages

### 17. Members Portal
- **Daily**: Statement generation, notification delivery
- **Weekly**: Activity reports, dormancy checks
- **Monthly**: Fee processing, renewals
- **Quarterly**: Status reviews, compliance checks
- **Annually**: KYC refresh, AGM notifications

### 18. Self Services
- **Hourly**: Service availability monitoring
- **Daily**: Usage reports, failed transaction reviews
- **Weekly**: Performance analysis
- **Monthly**: Adoption metrics
- **Quarterly**: UX assessments

### 19. Expenses
- **Daily**: Accrual processing, payment alerts
- **Weekly**: Reports, budget utilization
- **Monthly**: Reconciliation, vendor payments
- **Quarterly**: Audits, budget reviews
- **Annually**: Policy reviews

### 20. Payments
- **Minutes**: Status monitoring, retry processing
- **Hourly**: Queue processing, reconciliation
- **Daily**: Settlement, exception handling
- **Weekly**: Performance reports
- **Monthly**: Fee calculations, statements
- **Quarterly**: System audits

### 21. Investment
- **Daily**: Value updates, interest accruals, maturity monitoring
- **Weekly**: Performance reports
- **Monthly**: Dividend processing, NAV calculations
- **Quarterly**: Rebalancing, performance reviews
- **Annually**: Policy reviews, tax reporting

### 22. Procurement
- **Daily**: PO status updates, delivery tracking
- **Weekly**: Vendor performance, pending PO reviews
- **Monthly**: Vendor payments, reconciliation
- **Quarterly**: Vendor evaluations, contract renewals
- **Annually**: Vendor audits, policy reviews

### 23. Products Management
- **Daily**: Rate updates, availability checks
- **Weekly**: Performance reports
- **Monthly**: Profitability analysis
- **Quarterly**: Portfolio reviews
- **Annually**: Policy reviews, new product planning

### 24. Branches
- **Daily**: Cash position, performance tracking
- **Weekly**: Performance reports, staff reviews
- **Monthly**: Profitability analysis
- **Quarterly**: Audits, compliance checks
- **Annually**: Strategic planning

### 25. Clients/Members
- **Daily**: Status updates, KYC alerts, document expiry
- **Weekly**: New member reports, activity analysis
- **Monthly**: Retention reports, dormancy checks
- **Quarterly**: Risk reviews, AML/CFT checks
- **Annually**: KYC refresh, anniversary communications

### 26. Email/Notifications
- **Real-time**: Transaction alerts, security alerts
- **Daily**: Batch delivery, cleanup
- **Weekly**: Delivery reports, failed retries
- **Monthly**: Analytics, template reviews
- **Quarterly**: Channel optimization

---

## Implementation Recommendations

### Phase 1: Critical Foundation (Weeks 1-4)
1. Ensure all existing scheduled tasks are functioning properly
2. Implement real-time payment monitoring
3. Add hourly cash position monitoring
4. Enhance daily activities service with all identified tasks

### Phase 2: Weekly & Monthly Enhancement (Weeks 5-8)
1. Create weekly report generation framework
2. Implement monthly financial close automation
3. Add regulatory report generation
4. Implement notification system enhancements

### Phase 3: Quarterly & Annual (Weeks 9-12)
1. Implement quarterly audit frameworks
2. Add annual processing capabilities
3. Create comprehensive dashboards
4. Implement compliance monitoring

### Command Structure Recommendation
```
app/Console/Commands/
├── Daily/
│   ├── RunDailySystemActivities.php (existing)
│   ├── ProcessDailyLoanActivities.php
│   ├── ProcessDailySavingsActivities.php
│   └── ProcessDailyReconciliation.php
├── Weekly/
│   ├── GenerateWeeklyReports.php
│   ├── ProcessWeeklyReconciliation.php
│   └── ProcessWeeklyAnalytics.php
├── Monthly/
│   ├── RunMonthlyActivities.php (existing)
│   ├── GenerateMonthlyStatements.php
│   ├── ProcessMonthlyProvisions.php
│   └── GenerateRegulatoryReports.php
├── Quarterly/
│   ├── RunQuarterlyActivities.php (existing)
│   ├── ProcessQuarterlyAudits.php
│   └── GenerateQuarterlyReports.php
└── Annual/
    ├── RunYearEndProcessing.php
    ├── ProcessAnnualReviews.php
    └── GenerateAnnualReports.php
```

### Monitoring & Alerting
1. Create `ScheduledTaskLog` model (implemented) to track all executions
2. Implement failure alerting via email/SMS
3. Create dashboard for task monitoring
4. Implement SLA tracking for critical tasks

### Database Optimization
1. Add indexes for frequently queried date fields
2. Implement table partitioning for large transaction tables
3. Archive old data (>2 years) to separate tables
4. Regular VACUUM/ANALYZE for PostgreSQL

---

## Conclusion

This comprehensive framework identifies **500+ scheduled activities** across **26 system components**, ensuring the NBC SACCOS system operates with:

- **Operational Excellence**: Automated daily processing reduces manual effort
- **Regulatory Compliance**: All required reports generated on schedule
- **Risk Management**: Continuous monitoring and early warning systems
- **Member Service**: Timely statements, notifications, and communications
- **Financial Accuracy**: Daily reconciliation and period-end closes
- **Audit Readiness**: Complete audit trails and documentation

Regular review and update of this schedule is recommended as business requirements evolve.

---

*Document Version: 1.0*
*Last Updated: 2025-11-28*
*Prepared by: System Analysis*
