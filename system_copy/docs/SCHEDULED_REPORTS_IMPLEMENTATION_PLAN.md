# Scheduled Reports Implementation Plan

## NBC SACCOS Automated Report Generation & Distribution System

**Document Version:** 1.0
**Date:** November 29, 2025
**Author:** System Implementation

---

## 1. Executive Summary

This document outlines the comprehensive automated report generation and distribution system for NBC SACCOS. The system generates 50+ reports across 10 categories and distributes them to appropriate staff based on their roles.

### Key Features
- Automated report generation at scheduled intervals
- Role-based report distribution
- Support for Daily, Weekly, Monthly, Quarterly, and Annual reports
- Regulatory compliance reports for BOT and TCDC
- Multi-format support (PDF, Excel, HTML email)

---

## 2. Report Categories & Distribution Matrix

### 2.1 Daily Reports (10 Reports)

| Report | Time | Recipients (Roles) |
|--------|------|-------------------|
| Daily Operations Report | 06:00 | CEO, Deputy CEO, COO, Finance Manager |
| Daily Arrears Report | 07:00 | Credit Manager, Senior Credit Officer, Recovery Officer, Collections Officer |
| Daily Cash Position | 06:30 | CEO, CFO, Finance Manager, Chief Accountant |
| Daily Loan Disbursements | 17:00 | Credit Manager, CFO, CEO |
| Daily Collections | 17:30 | Credit Manager, Recovery Officer, Collections Officer, Finance Manager |
| Daily Member Activity | 08:00 | Member Services Manager, Membership Officer, Member Relations Officer |
| Daily Loan Officer Portfolio | 07:00 | Credit Officer, Senior Credit Officer, Credit Analyst |
| Daily Recovery Action List | 07:00 | Recovery Officer, Collections Officer, Credit Manager |
| Daily GL Summary | 18:00 | Chief Accountant, Senior Accountant, Accountant, CFO |
| Daily Large Transactions | 17:00 | Compliance Manager, Compliance Officer, CFO, CEO |

### 2.2 Weekly Reports (8 Reports)

| Report | Day/Time | Recipients (Roles) |
|--------|----------|-------------------|
| Weekly Executive Summary | Mon 08:00 | CEO, Deputy CEO, CFO, COO, Board Member |
| Weekly Arrears Analysis | Mon 07:30 | Credit Manager, Senior Credit Officer, Recovery Officer, Collections Officer |
| Weekly Credit Committee | Tue 08:00 | Credit Manager, Senior Credit Officer, Credit Analyst, CEO, CFO |
| Weekly Collections Performance | Fri 16:00 | Credit Manager, Recovery Officer, Collections Officer |
| Weekly Risk Alerts | Fri 14:00 | Risk Manager, Risk Officer, Risk Analyst, CEO, Chief Internal Auditor |
| Weekly Savings Mobilization | Mon 09:00 | Savings Manager, Savings Mobilization Officer, Member Services Manager |
| Weekly Loan Officer Targets | Mon 06:30 | Credit Officer, Senior Credit Officer, Credit Manager |
| Weekly Suspense Account | Fri 16:00 | Chief Accountant, Senior Accountant, CFO, Chief Internal Auditor |

### 2.3 Monthly Reports - Credit (6 Reports)

| Report | Day/Time | Recipients (Roles) |
|--------|----------|-------------------|
| Monthly Loan Portfolio | 1st 08:00 | CEO, Deputy CEO, CFO, Credit Manager, Risk Manager, Board Member |
| Monthly Delinquency | 1st 08:30 | Credit Manager, Risk Manager, Recovery Officer, CEO, Board Member |
| Monthly NPL Classification | 5th 08:00 | Credit Manager, Risk Manager, CFO, Compliance Manager |
| Monthly Loan Loss Provision | 5th 09:00 | CFO, Finance Manager, Chief Accountant, Risk Manager, Credit Manager |
| Monthly Loan Officer Performance | 3rd 08:00 | Credit Manager, Senior Credit Officer, HR Manager, CEO |
| Monthly Loan Product Analysis | 3rd 09:00 | Credit Manager, Business Development Officer, CEO |

### 2.4 Monthly Reports - Finance (8 Reports)

| Report | Day/Time | Recipients (Roles) |
|--------|----------|-------------------|
| Monthly Trial Balance | 1st 09:00 | CFO, Finance Manager, Chief Accountant, Senior Accountant, Accountant |
| Monthly Income Statement | 5th 09:00 | CEO, Deputy CEO, CFO, Finance Manager, Board Member, Board Treasurer |
| Monthly Balance Sheet | 5th 09:30 | CEO, Deputy CEO, CFO, Finance Manager, Board Member, Board Treasurer |
| Monthly Cash Flow Statement | 5th 10:00 | CEO, CFO, Finance Manager, Chief Accountant |
| Monthly Budget Variance | 7th 08:00 | CEO, CFO, Finance Manager, Chief Accountant, Board Treasurer |
| Monthly Bank Reconciliation | 3rd 10:00 | CFO, Finance Manager, Chief Accountant, Chief Internal Auditor |
| Monthly Interest Accrual | 1st 10:00 | Chief Accountant, Finance Manager, CFO |
| Monthly Liquidity Report | 5th 11:00 | CFO, Risk Manager, CEO, Compliance Manager |

### 2.5 Monthly Reports - Membership & Savings (4 Reports)

| Report | Day/Time | Recipients (Roles) |
|--------|----------|-------------------|
| Monthly Membership Report | 2nd 08:00 | Member Services Manager, Membership Officer, CEO, Board Member |
| Monthly Savings & Deposits | 2nd 09:00 | Savings Manager, CFO, Finance Manager, Member Services Manager |
| Monthly Share Capital | 2nd 10:00 | CFO, Finance Manager, Member Services Manager, Board Treasurer |
| Monthly Mandatory Savings | 25th 08:00 | Member Services Manager, Credit Manager, CEO, Collections Officer |

### 2.6 Monthly Reports - Risk & Compliance (4 Reports)

| Report | Day/Time | Recipients (Roles) |
|--------|----------|-------------------|
| Monthly Risk Assessment | 10th 08:00 | Risk Manager, Risk Officer, Risk Analyst, CEO, Board Member |
| Monthly Concentration Risk | 10th 09:00 | Risk Manager, Credit Manager, CEO, Compliance Manager |
| Monthly AML Compliance | 5th 08:00 | Compliance Manager, Compliance Officer, CEO, Chief Internal Auditor |
| Monthly Write-off Report | 10th 08:00 | Credit Manager, CFO, CEO, Board Member |

### 2.7 Quarterly Reports - Regulatory (7 Reports)

| Report | Schedule | Recipients (Roles) |
|--------|----------|-------------------|
| Quarterly BOT Regulatory Report | Jan/Apr/Jul/Oct 15th | CEO, CFO, Compliance Manager, Compliance Officer, Board Chairperson |
| Quarterly Capital Adequacy | Jan/Apr/Jul/Oct 10th | CEO, CFO, Compliance Manager, Risk Manager, Board Treasurer |
| Quarterly Liquid Assets Return | Jan/Apr/Jul/Oct 10th | CFO, Compliance Manager, Risk Manager |
| Quarterly Large Exposures | Jan/Apr/Jul/Oct 10th | Credit Manager, Risk Manager, Compliance Manager, CEO |
| Quarterly NPL Return (BOT) | Jan/Apr/Jul/Oct 15th | Credit Manager, CFO, Compliance Manager, Risk Manager |
| Quarterly TCDC Supervision | Jan/Apr/Jul/Oct 20th | CEO, CFO, Compliance Manager, Board Chairperson, Board Secretary |
| Quarterly TCDC Membership | Jan/Apr/Jul/Oct 20th | CEO, Member Services Manager, Compliance Manager |

### 2.8 Quarterly Reports - Governance (5 Reports)

| Report | Schedule | Recipients (Roles) |
|--------|----------|-------------------|
| Quarterly Board Pack | Jan/Apr/Jul/Oct 5th | Board Chairperson, Vice Chairperson, Board Member, Board Secretary, Board Treasurer, CEO |
| Quarterly Supervisory Report | Jan/Apr/Jul/Oct 5th | Supervisory Chairperson, Vice Chair, Secretary, Member, CEO |
| Quarterly Audit Report | Jan/Apr/Jul/Oct 8th | Chief Internal Auditor, Senior Auditor, Internal Auditor, Board Chairperson, CEO |
| Quarterly Risk Report | Jan/Apr/Jul/Oct 7th | Risk Manager, Risk Officer, CEO, Board Member, Chief Internal Auditor |
| Quarterly Financial Statements | Jan/Apr/Jul/Oct 12th | CEO, Deputy CEO, CFO, Finance Manager, Board Member, Board Treasurer |

### 2.9 Annual Reports (4 Reports)

| Report | Schedule | Recipients (Roles) |
|--------|----------|-------------------|
| Annual Financial Statements | Jan 31st | CEO, Deputy CEO, CFO, Board Chairperson, Board Member, Board Treasurer |
| Annual AGM Pack | Feb 15th | Board Chairperson, Vice Chairperson, Board Secretary, Board Member, CEO, CFO |
| Annual BOT Statutory Return | Jan 31st | CEO, CFO, Compliance Manager, Compliance Officer |
| Annual TCDC Cooperative Return | Feb 28th | CEO, CFO, Compliance Manager, Board Secretary |

---

## 3. Technical Implementation

### 3.1 System Architecture

```
+-------------------+     +----------------------+     +------------------+
|   Laravel         |     |   Report Generator   |     |   Email Service  |
|   Scheduler       | --> |   (Artisan Command)  | --> |   (SMTP/NBC)     |
|   (Hourly Check)  |     |                      |     |                  |
+-------------------+     +----------------------+     +------------------+
        |                          |
        v                          v
+-------------------+     +----------------------+
|   Kernel.php      |     |   GenerateScheduled  |
|   Schedule        |     |   Reports.php        |
+-------------------+     +----------------------+
                                   |
                    +-------------+-------------+
                    |             |             |
                    v             v             v
            +----------+   +----------+   +----------+
            |  Daily   |   | Weekly   |   | Monthly  |
            | Reports  |   | Reports  |   | Reports  |
            +----------+   +----------+   +----------+
```

### 3.2 Key Files

| File | Purpose |
|------|---------|
| `app/Console/Kernel.php` | Laravel scheduler configuration |
| `app/Console/Commands/GenerateScheduledReports.php` | Main report generation command |
| `app/Services/DailyLoanReportsService.php` | Loan report generation service |
| `app/Models/ScheduledReport.php` | User-scheduled reports model |
| `app/Mail/ScheduledReportMail.php` | Email template for reports |
| `app/Mail/SystemReportMail.php` | System report email template |

### 3.3 Command Usage

```bash
# Generate all due reports
php artisan reports:scheduled-generation

# Dry run (test without sending)
php artisan reports:scheduled-generation --dry-run

# Force generate specific report
php artisan reports:scheduled-generation --report=daily_arrears --force

# Generate only system reports
php artisan reports:scheduled-generation --type=system

# Generate only user-scheduled reports
php artisan reports:scheduled-generation --type=user
```

### 3.4 Schedule Configuration

The scheduler runs hourly and checks which reports are due based on:
- **Daily reports:** Run at their specified time each day
- **Weekly reports:** Run on specified day and time
- **Monthly reports:** Run on specified day of month
- **Quarterly reports:** Run in specified months on specified day
- **Annual reports:** Run on specified month and day

---

## 4. Report Data Sources

### 4.1 Database Tables Used

| Category | Primary Tables |
|----------|---------------|
| Loans | `loans`, `loans_schedules`, `loan_payments`, `loan_products` |
| Members | `clients`, `accounts`, `account_types` |
| Finance | `general_ledger`, `journal_entries`, `transactions`, `budgets` |
| Savings | `accounts` (type: SAV, DEP), `sub_products` |
| Shares | `accounts` (type: SHA), `dividend_accruals` |
| Compliance | `transactions`, `audit_logs` |

### 4.2 Report Generation Methods

The `GenerateScheduledReports.php` command includes these generation methods:

| Method | Reports |
|--------|---------|
| `generateDailyOperationsReport()` | Daily transactions, loans, disbursements |
| `generateArrearsReport()` | PAR analysis, aging breakdown |
| `generateLoanPortfolioReport()` | Portfolio summary, product analysis |
| `generateDelinquencyReport()` | PAR metrics, delinquency trends |
| `generateTrialBalanceReport()` | GL account balances |
| `generateIncomeStatementReport()` | Revenue, expenses, net income |
| `generateStatementOfFinancialPosition()` | Assets, liabilities, equity |
| `generateCashFlowReport()` | Cash inflows/outflows |
| `generateExecutiveSummaryReport()` | KPI dashboard |
| `generateRegulatoryReport()` | BOT/TCDC compliance data |

---

## 5. Implementation Phases

### Phase 1: Core Reports (Implemented)
- [x] Daily Operations Report
- [x] Daily Arrears Report
- [x] Weekly Executive Summary
- [x] Weekly Arrears Analysis
- [x] Monthly Loan Portfolio
- [x] Monthly Delinquency
- [x] Monthly Trial Balance
- [x] Monthly Income Statement
- [x] Quarterly Regulatory Report

### Phase 2: Extended Credit Reports (Next)
- [ ] Daily Disbursements Report
- [ ] Daily Collections Report
- [ ] Weekly Credit Committee Report
- [ ] Monthly NPL Classification
- [ ] Monthly Loan Loss Provision
- [ ] Monthly Loan Officer Performance

### Phase 3: Regulatory Reports
- [ ] Quarterly Capital Adequacy (BOT)
- [ ] Quarterly Liquid Assets Return (BOT)
- [ ] Quarterly Large Exposures (BOT)
- [ ] Quarterly NPL Return (BOT)
- [ ] Quarterly TCDC Supervision
- [ ] Annual BOT Statutory Return

### Phase 4: Finance & Governance Reports
- [ ] Monthly Balance Sheet
- [ ] Monthly Cash Flow Statement
- [ ] Monthly Budget Variance
- [ ] Quarterly Board Pack
- [ ] Quarterly Audit Report
- [ ] Annual AGM Pack

### Phase 5: Specialized Reports
- [ ] Daily Large Transactions (AML)
- [ ] Monthly AML Compliance
- [ ] Weekly Risk Alerts
- [ ] Monthly Concentration Risk
- [ ] Staff Performance Reports

---

## 6. Role-Based Distribution Summary

### Executive Level
- **CEO/General Manager:** 35+ reports
- **Deputy CEO:** 12 reports
- **CFO:** 28 reports
- **COO:** 5 reports

### Credit Department
- **Credit Manager:** 22 reports
- **Senior Credit Officer:** 8 reports
- **Credit Officer:** 3 reports
- **Recovery Officer:** 7 reports
- **Collections Officer:** 8 reports

### Finance Department
- **Finance Manager:** 15 reports
- **Chief Accountant:** 12 reports
- **Senior Accountant:** 5 reports
- **Accountant:** 3 reports

### Risk & Compliance
- **Risk Manager:** 15 reports
- **Risk Officer:** 6 reports
- **Compliance Manager:** 14 reports
- **Compliance Officer:** 5 reports

### Governance
- **Board Chairperson:** 8 reports
- **Board Member:** 12 reports
- **Board Treasurer:** 8 reports
- **Board Secretary:** 4 reports

### Audit
- **Chief Internal Auditor:** 8 reports
- **Senior Auditor:** 2 reports
- **Internal Auditor:** 2 reports

---

## 7. Monitoring & Maintenance

### 7.1 Log Files

| Log File | Purpose |
|----------|---------|
| `storage/logs/scheduled-reports.log` | Main scheduler output |
| `storage/logs/laravel.log` | Application errors |

### 7.2 Monitoring Commands

```bash
# Check scheduler status
php artisan schedule:list

# View recent report logs
tail -f storage/logs/scheduled-reports.log

# Test specific report
php artisan reports:scheduled-generation --report=daily_arrears --dry-run
```

### 7.3 Troubleshooting

| Issue | Solution |
|-------|----------|
| Reports not sending | Check email configuration in `.env` |
| No recipients found | Verify roles exist and users are assigned |
| Report data missing | Check database connections and data |
| Scheduler not running | Verify cron job: `* * * * * apache php artisan schedule:run` |

---

## 8. Configuration

### 8.1 Environment Variables

```env
# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=reports@nbcsaccos.co.tz
MAIL_PASSWORD=xxxxx
MAIL_FROM_ADDRESS=reports@nbcsaccos.co.tz
MAIL_FROM_NAME="NBC SACCOS Reports"
```

### 8.2 Adding New Reports

To add a new report, update the `$systemReports` array in `GenerateScheduledReports.php`:

```php
'new_report_key' => [
    'name' => 'Report Display Name',
    'frequency' => 'daily|weekly|monthly|quarterly|yearly',
    'day' => 1,  // For monthly/weekly
    'time' => '08:00',
    'roles' => ['Role Name 1', 'Role Name 2'],
    'description' => 'Report description',
    'category' => 'credit|finance|risk|regulatory|governance',
],
```

Then add the corresponding generation method:

```php
protected function generateNewReportData(): array
{
    // Query database and compile report data
    return [
        'report_type' => 'New Report',
        'generated_at' => now()->format('Y-m-d H:i:s'),
        // ... report data
    ];
}
```

---

## 9. Compliance Mapping

### 9.1 BOT Requirements

| BOT Requirement | Report(s) |
|-----------------|-----------|
| Prudential Returns | Quarterly BOT Regulatory Report |
| Capital Adequacy | Quarterly Capital Adequacy Report |
| Liquid Assets Ratio | Quarterly Liquid Assets Return |
| Large Exposures | Quarterly Large Exposures Report |
| NPL Classification | Monthly NPL Classification, Quarterly NPL Return |
| Annual Statutory Return | Annual BOT Statutory Return |

### 9.2 TCDC Requirements

| TCDC Requirement | Report(s) |
|------------------|-----------|
| Quarterly Supervision | Quarterly TCDC Supervision Report |
| Membership Statistics | Quarterly TCDC Membership Statistics |
| Annual Cooperative Return | Annual TCDC Cooperative Return |

### 9.3 IFRS Requirements

| IFRS Standard | Report(s) |
|---------------|-----------|
| IFRS 9 ECL | Monthly Loan Loss Provision Report |
| IAS 1 Financial Statements | Monthly/Quarterly Financial Statements |
| IAS 7 Cash Flows | Monthly Cash Flow Statement |

---

## 10. Next Steps

1. **Restart Services** - Apply the new scheduler configuration
2. **Test Reports** - Run dry-run tests for each report type
3. **Verify Recipients** - Ensure all target roles have active users
4. **Phase 2 Development** - Implement extended credit reports
5. **User Training** - Train staff on report interpretation

---

**Document End**
