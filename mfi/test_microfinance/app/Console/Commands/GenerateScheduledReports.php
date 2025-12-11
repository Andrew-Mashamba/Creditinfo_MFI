<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\ScheduledReport;
use App\Models\User;
use App\Models\Role;
use App\Models\LoansModel;
use App\Models\Transaction;
use App\Models\ClientsModel;
use App\Mail\ScheduledReportMail;
use App\Mail\SystemReportMail;
use App\Services\ScheduledReportDataService;
use App\Services\MonthlyReportDataService;
use App\Services\QuarterlyAnnualReportDataService;
use Carbon\Carbon;
use Exception;
use PDF;

class GenerateScheduledReports extends Command
{
    protected $signature = 'reports:scheduled-generation
                            {--dry-run : Run without sending emails or updating records}
                            {--type=all : Type of reports to process (all|user|system)}
                            {--force : Force generation even if not due}
                            {--report= : Generate specific system report type}
                            {--frequency= : Filter by frequency (daily|weekly|monthly|quarterly|annual)}';

    protected $description = 'Generate and distribute scheduled reports to users';

    /**
     * System report configurations
     * Defines automatic reports that are generated based on schedule
     *
     * Report Categories:
     * 1. DAILY REPORTS - Operations, Arrears, Transactions
     * 2. WEEKLY REPORTS - Executive Summary, Credit Review, Collections
     * 3. MONTHLY REPORTS - Financial Statements, Portfolio, NPL, Provisions
     * 4. QUARTERLY REPORTS - BOT Regulatory, TCDC Supervision, Risk Assessment
     * 5. ANNUAL REPORTS - AGM, Audit, Financial Year End
     */
    protected $systemReports = [
        // ========================================
        // DAILY REPORTS
        // ========================================
        'daily_operations' => [
            'name' => 'Daily Operations Report',
            'frequency' => 'daily',
            'time' => '06:00',
            'roles' => ['CEO/General Manager', 'Deputy CEO', 'Chief Operations Officer', 'Finance Manager'],
            'description' => 'Daily summary of transactions, new loans, disbursements, and collections',
            'category' => 'operations',
        ],
        'daily_arrears' => [
            'name' => 'Daily Arrears Report',
            'frequency' => 'daily',
            'time' => '07:00',
            'roles' => ['Credit Manager', 'Senior Credit Officer', 'Recovery Officer', 'Collections Officer'],
            'description' => 'Daily update on loan arrears and collection status with aging analysis',
            'category' => 'credit',
        ],
        'daily_cash_position' => [
            'name' => 'Daily Cash Position Report',
            'frequency' => 'daily',
            'time' => '06:30',
            'roles' => ['CEO/General Manager', 'Chief Finance Officer', 'Finance Manager', 'Chief Accountant'],
            'description' => 'Daily cash and bank balances including NBC account reconciliation',
            'category' => 'finance',
        ],
        'daily_disbursements' => [
            'name' => 'Daily Loan Disbursements Report',
            'frequency' => 'daily',
            'time' => '17:00',
            'roles' => ['Credit Manager', 'Chief Finance Officer', 'CEO/General Manager'],
            'description' => 'Summary of all loan disbursements made during the day',
            'category' => 'credit',
        ],
        'daily_collections' => [
            'name' => 'Daily Collections Report',
            'frequency' => 'daily',
            'time' => '17:30',
            'roles' => ['Credit Manager', 'Recovery Officer', 'Collections Officer', 'Finance Manager'],
            'description' => 'Daily loan repayments, fees collected, and payment methods breakdown',
            'category' => 'credit',
        ],
        'daily_member_activity' => [
            'name' => 'Daily Member Activity Report',
            'frequency' => 'daily',
            'time' => '08:00',
            'roles' => ['Member Services Manager', 'Membership Officer', 'Member Relations Officer'],
            'description' => 'New member registrations, account openings, deposits and withdrawals',
            'category' => 'membership',
        ],

        // ========================================
        // WEEKLY REPORTS
        // ========================================
        'weekly_executive_summary' => [
            'name' => 'Weekly Executive Summary',
            'frequency' => 'weekly',
            'day' => 'monday',
            'time' => '08:00',
            'roles' => ['CEO/General Manager', 'Deputy CEO', 'Chief Finance Officer', 'Chief Operations Officer', 'Board Member'],
            'description' => 'Weekly KPI dashboard and performance summary for executive review',
            'category' => 'executive',
        ],
        'weekly_arrears' => [
            'name' => 'Weekly Arrears Analysis',
            'frequency' => 'weekly',
            'day' => 'monday',
            'time' => '07:30',
            'roles' => ['Credit Manager', 'Senior Credit Officer', 'Recovery Officer', 'Collections Officer'],
            'description' => 'Detailed weekly arrears analysis with aging breakdown and action items',
            'category' => 'credit',
        ],
        'weekly_credit_committee' => [
            'name' => 'Weekly Credit Committee Report',
            'frequency' => 'weekly',
            'day' => 'tuesday',
            'time' => '08:00',
            'roles' => ['Credit Manager', 'Senior Credit Officer', 'Credit Analyst', 'CEO/General Manager', 'Chief Finance Officer'],
            'description' => 'Loan applications pending, approved, declined, and pipeline analysis',
            'category' => 'credit',
        ],
        'weekly_collections_performance' => [
            'name' => 'Weekly Collections Performance',
            'frequency' => 'weekly',
            'day' => 'friday',
            'time' => '16:00',
            'roles' => ['Credit Manager', 'Recovery Officer', 'Collections Officer'],
            'description' => 'Weekly collections target vs actual, officer performance, recovery rate',
            'category' => 'credit',
        ],
        'weekly_risk_alerts' => [
            'name' => 'Weekly Risk Alerts Report',
            'frequency' => 'weekly',
            'day' => 'friday',
            'time' => '14:00',
            'roles' => ['Risk Manager', 'Risk Officer', 'Risk Analyst', 'CEO/General Manager', 'Chief Internal Auditor'],
            'description' => 'Risk indicators, threshold breaches, and early warning signals',
            'category' => 'risk',
        ],
        'weekly_savings_mobilization' => [
            'name' => 'Weekly Savings Mobilization Report',
            'frequency' => 'weekly',
            'day' => 'monday',
            'time' => '09:00',
            'roles' => ['Savings Manager', 'Savings Mobilization Officer', 'Member Services Manager'],
            'description' => 'Weekly savings deposits, withdrawals, new accounts, and mobilization targets',
            'category' => 'savings',
        ],

        // ========================================
        // MONTHLY REPORTS - CREDIT
        // ========================================
        'monthly_loan_portfolio' => [
            'name' => 'Monthly Loan Portfolio Report',
            'frequency' => 'monthly',
            'day' => 1,
            'time' => '08:00',
            'roles' => ['CEO/General Manager', 'Deputy CEO', 'Chief Finance Officer', 'Credit Manager', 'Risk Manager', 'Board Member'],
            'description' => 'Comprehensive monthly loan portfolio analysis with product, branch, and officer breakdown',
            'category' => 'credit',
        ],
        'monthly_delinquency' => [
            'name' => 'Monthly Delinquency Report',
            'frequency' => 'monthly',
            'day' => 1,
            'time' => '08:30',
            'roles' => ['Credit Manager', 'Risk Manager', 'Recovery Officer', 'CEO/General Manager', 'Board Member'],
            'description' => 'Monthly PAR analysis (PAR1, PAR30, PAR60, PAR90) and delinquency trends',
            'category' => 'credit',
        ],
        'monthly_npl_classification' => [
            'name' => 'Monthly NPL Classification Report',
            'frequency' => 'monthly',
            'day' => 5,
            'time' => '08:00',
            'roles' => ['Credit Manager', 'Risk Manager', 'Chief Finance Officer', 'Compliance Manager'],
            'description' => 'Non-Performing Loans by classification (WATCH, SUBSTANDARD, DOUBTFUL, LOSS) per BOT guidelines',
            'category' => 'credit',
        ],
        'monthly_provision' => [
            'name' => 'Monthly Loan Loss Provision Report',
            'frequency' => 'monthly',
            'day' => 5,
            'time' => '09:00',
            'roles' => ['Chief Finance Officer', 'Finance Manager', 'Chief Accountant', 'Risk Manager', 'Credit Manager'],
            'description' => 'Loan loss provision calculations per IFRS 9 ECL and BOT prudential guidelines',
            'category' => 'finance',
        ],
        'monthly_loan_officer_performance' => [
            'name' => 'Monthly Loan Officer Performance',
            'frequency' => 'monthly',
            'day' => 3,
            'time' => '08:00',
            'roles' => ['Credit Manager', 'Senior Credit Officer', 'HR Manager', 'CEO/General Manager'],
            'description' => 'Individual loan officer disbursement, collection, and PAR performance',
            'category' => 'credit',
        ],
        'monthly_loan_product_analysis' => [
            'name' => 'Monthly Loan Product Performance',
            'frequency' => 'monthly',
            'day' => 3,
            'time' => '09:00',
            'roles' => ['Credit Manager', 'Business Development Officer', 'CEO/General Manager'],
            'description' => 'Loan product profitability, utilization, and growth analysis',
            'category' => 'credit',
        ],

        // ========================================
        // MONTHLY REPORTS - FINANCE
        // ========================================
        'monthly_trial_balance' => [
            'name' => 'Monthly Trial Balance',
            'frequency' => 'monthly',
            'day' => 1,
            'time' => '09:00',
            'roles' => ['Chief Finance Officer', 'Finance Manager', 'Chief Accountant', 'Senior Accountant', 'Accountant'],
            'description' => 'Monthly trial balance for accounting review and reconciliation',
            'category' => 'finance',
        ],
        'monthly_income_statement' => [
            'name' => 'Monthly Income Statement',
            'frequency' => 'monthly',
            'day' => 5,
            'time' => '09:00',
            'roles' => ['CEO/General Manager', 'Deputy CEO', 'Chief Finance Officer', 'Finance Manager', 'Board Member', 'Board Treasurer'],
            'description' => 'Statement of Comprehensive Income showing revenue, expenses, and net income',
            'category' => 'finance',
        ],
        'monthly_balance_sheet' => [
            'name' => 'Monthly Balance Sheet',
            'frequency' => 'monthly',
            'day' => 5,
            'time' => '09:30',
            'roles' => ['CEO/General Manager', 'Deputy CEO', 'Chief Finance Officer', 'Finance Manager', 'Board Member', 'Board Treasurer'],
            'description' => 'Statement of Financial Position showing assets, liabilities, and equity',
            'category' => 'finance',
        ],
        'monthly_cash_flow' => [
            'name' => 'Monthly Cash Flow Statement',
            'frequency' => 'monthly',
            'day' => 5,
            'time' => '10:00',
            'roles' => ['CEO/General Manager', 'Chief Finance Officer', 'Finance Manager', 'Chief Accountant'],
            'description' => 'Statement of Cash Flows - operating, investing, and financing activities',
            'category' => 'finance',
        ],
        'monthly_budget_variance' => [
            'name' => 'Monthly Budget Variance Report',
            'frequency' => 'monthly',
            'day' => 7,
            'time' => '08:00',
            'roles' => ['CEO/General Manager', 'Chief Finance Officer', 'Finance Manager', 'Chief Accountant', 'Board Treasurer'],
            'description' => 'Budget vs actual analysis with variance explanations',
            'category' => 'finance',
        ],
        'monthly_bank_reconciliation' => [
            'name' => 'Monthly Bank Reconciliation Report',
            'frequency' => 'monthly',
            'day' => 3,
            'time' => '10:00',
            'roles' => ['Chief Finance Officer', 'Finance Manager', 'Chief Accountant', 'Chief Internal Auditor'],
            'description' => 'NBC and other bank account reconciliation summary',
            'category' => 'finance',
        ],

        // ========================================
        // MONTHLY REPORTS - MEMBERSHIP & SAVINGS
        // ========================================
        'monthly_membership' => [
            'name' => 'Monthly Membership Report',
            'frequency' => 'monthly',
            'day' => 2,
            'time' => '08:00',
            'roles' => ['Member Services Manager', 'Membership Officer', 'CEO/General Manager', 'Board Member'],
            'description' => 'Member growth, demographics, active/dormant status, and retention analysis',
            'category' => 'membership',
        ],
        'monthly_savings_deposits' => [
            'name' => 'Monthly Savings & Deposits Report',
            'frequency' => 'monthly',
            'day' => 2,
            'time' => '09:00',
            'roles' => ['Savings Manager', 'Chief Finance Officer', 'Finance Manager', 'Member Services Manager'],
            'description' => 'Savings product performance, deposit mobilization, and interest accrual',
            'category' => 'savings',
        ],
        'monthly_shares_dividends' => [
            'name' => 'Monthly Share Capital Report',
            'frequency' => 'monthly',
            'day' => 2,
            'time' => '10:00',
            'roles' => ['Chief Finance Officer', 'Finance Manager', 'Member Services Manager', 'Board Treasurer'],
            'description' => 'Share capital movements, dividend accrual, and member share balances',
            'category' => 'finance',
        ],
        'monthly_mandatory_savings' => [
            'name' => 'Monthly Mandatory Savings Report',
            'frequency' => 'monthly',
            'day' => 25,
            'time' => '08:00',
            'roles' => ['Member Services Manager', 'Credit Manager', 'CEO/General Manager', 'Collections Officer'],
            'description' => 'Mandatory savings collection rate, arrears, and defaulters list',
            'category' => 'membership',
        ],

        // ========================================
        // MONTHLY REPORTS - RISK & COMPLIANCE
        // ========================================
        'monthly_risk_assessment' => [
            'name' => 'Monthly Risk Assessment Report',
            'frequency' => 'monthly',
            'day' => 10,
            'time' => '08:00',
            'roles' => ['Risk Manager', 'Risk Officer', 'Risk Analyst', 'CEO/General Manager', 'Board Member'],
            'description' => 'Credit, operational, liquidity, and market risk indicators',
            'category' => 'risk',
        ],
        'monthly_concentration_risk' => [
            'name' => 'Monthly Concentration Risk Report',
            'frequency' => 'monthly',
            'day' => 10,
            'time' => '09:00',
            'roles' => ['Risk Manager', 'Credit Manager', 'CEO/General Manager', 'Compliance Manager'],
            'description' => 'Loan concentration by borrower, sector, and product (BOT single borrower limits)',
            'category' => 'risk',
        ],
        'monthly_liquidity' => [
            'name' => 'Monthly Liquidity Report',
            'frequency' => 'monthly',
            'day' => 5,
            'time' => '11:00',
            'roles' => ['Chief Finance Officer', 'Risk Manager', 'CEO/General Manager', 'Compliance Manager'],
            'description' => 'Liquidity ratios, liquid assets, and cash flow projections per BOT requirements',
            'category' => 'finance',
        ],

        // ========================================
        // QUARTERLY REPORTS - REGULATORY (BOT)
        // ========================================
        'quarterly_regulatory' => [
            'name' => 'Quarterly BOT Regulatory Report',
            'frequency' => 'quarterly',
            'months' => [1, 4, 7, 10],
            'day' => 15,
            'time' => '08:00',
            'roles' => ['CEO/General Manager', 'Chief Finance Officer', 'Compliance Manager', 'Compliance Officer', 'Board Chairperson'],
            'description' => 'Quarterly returns to Bank of Tanzania - MFI supervision',
            'category' => 'regulatory',
        ],
        'quarterly_capital_adequacy' => [
            'name' => 'Quarterly Capital Adequacy Report',
            'frequency' => 'quarterly',
            'months' => [1, 4, 7, 10],
            'day' => 10,
            'time' => '08:00',
            'roles' => ['CEO/General Manager', 'Chief Finance Officer', 'Compliance Manager', 'Risk Manager', 'Board Treasurer'],
            'description' => 'Capital adequacy ratios, core capital, and risk-weighted assets per BOT prudential guidelines',
            'category' => 'regulatory',
        ],
        'quarterly_liquid_assets' => [
            'name' => 'Quarterly Liquid Assets Return',
            'frequency' => 'quarterly',
            'months' => [1, 4, 7, 10],
            'day' => 10,
            'time' => '09:00',
            'roles' => ['Chief Finance Officer', 'Compliance Manager', 'Risk Manager'],
            'description' => 'Liquid assets ratio calculation and BOT minimum requirements compliance',
            'category' => 'regulatory',
        ],
        'quarterly_large_exposures' => [
            'name' => 'Quarterly Large Exposures Report',
            'frequency' => 'quarterly',
            'months' => [1, 4, 7, 10],
            'day' => 10,
            'time' => '10:00',
            'roles' => ['Credit Manager', 'Risk Manager', 'Compliance Manager', 'CEO/General Manager'],
            'description' => 'Large exposure limits (25% of core capital) and connected lending report per BOT',
            'category' => 'regulatory',
        ],
        'quarterly_npl_return' => [
            'name' => 'Quarterly NPL Return (BOT)',
            'frequency' => 'quarterly',
            'months' => [1, 4, 7, 10],
            'day' => 15,
            'time' => '08:30',
            'roles' => ['Credit Manager', 'Chief Finance Officer', 'Compliance Manager', 'Risk Manager'],
            'description' => 'Non-Performing Loans return with classification and provision per BOT format',
            'category' => 'regulatory',
        ],

        // ========================================
        // QUARTERLY REPORTS - TCDC
        // ========================================
        'quarterly_tcdc_supervision' => [
            'name' => 'Quarterly TCDC Supervision Report',
            'frequency' => 'quarterly',
            'months' => [1, 4, 7, 10],
            'day' => 20,
            'time' => '08:00',
            'roles' => ['CEO/General Manager', 'Chief Finance Officer', 'Compliance Manager', 'Board Chairperson', 'Board Secretary'],
            'description' => 'Tanzania Cooperative Development Commission quarterly supervision returns',
            'category' => 'regulatory',
        ],
        'quarterly_tcdc_membership' => [
            'name' => 'Quarterly TCDC Membership Statistics',
            'frequency' => 'quarterly',
            'months' => [1, 4, 7, 10],
            'day' => 20,
            'time' => '09:00',
            'roles' => ['CEO/General Manager', 'Member Services Manager', 'Compliance Manager'],
            'description' => 'Member statistics, growth, and cooperative principles compliance for TCDC',
            'category' => 'regulatory',
        ],

        // ========================================
        // QUARTERLY REPORTS - BOARD & MANAGEMENT
        // ========================================
        'quarterly_board_pack' => [
            'name' => 'Quarterly Board Report Pack',
            'frequency' => 'quarterly',
            'months' => [1, 4, 7, 10],
            'day' => 5,
            'time' => '08:00',
            'roles' => ['Board Chairperson', 'Vice Chairperson', 'Board Member', 'Board Secretary', 'Board Treasurer', 'CEO/General Manager'],
            'description' => 'Comprehensive quarterly report pack for Board of Directors meeting',
            'category' => 'governance',
        ],
        'quarterly_supervisory_report' => [
            'name' => 'Quarterly Supervisory Committee Report',
            'frequency' => 'quarterly',
            'months' => [1, 4, 7, 10],
            'day' => 5,
            'time' => '09:00',
            'roles' => ['Supervisory Chairperson', 'Supervisory Vice Chair', 'Supervisory Secretary', 'Supervisory Member', 'CEO/General Manager'],
            'description' => 'Internal supervision findings, compliance checks, and recommendations',
            'category' => 'governance',
        ],
        'quarterly_audit_report' => [
            'name' => 'Quarterly Internal Audit Report',
            'frequency' => 'quarterly',
            'months' => [1, 4, 7, 10],
            'day' => 8,
            'time' => '08:00',
            'roles' => ['Chief Internal Auditor', 'Senior Auditor', 'Internal Auditor', 'Board Chairperson', 'CEO/General Manager'],
            'description' => 'Quarterly internal audit findings, control weaknesses, and management responses',
            'category' => 'audit',
        ],
        'quarterly_risk_report' => [
            'name' => 'Quarterly Risk Management Report',
            'frequency' => 'quarterly',
            'months' => [1, 4, 7, 10],
            'day' => 7,
            'time' => '08:00',
            'roles' => ['Risk Manager', 'Risk Officer', 'CEO/General Manager', 'Board Member', 'Chief Internal Auditor'],
            'description' => 'Comprehensive quarterly risk assessment and mitigation report',
            'category' => 'risk',
        ],
        'quarterly_financial_statements' => [
            'name' => 'Quarterly Financial Statements',
            'frequency' => 'quarterly',
            'months' => [1, 4, 7, 10],
            'day' => 12,
            'time' => '08:00',
            'roles' => ['CEO/General Manager', 'Deputy CEO', 'Chief Finance Officer', 'Finance Manager', 'Board Member', 'Board Treasurer'],
            'description' => 'Complete quarterly financial statements package (Income, Balance Sheet, Cash Flow)',
            'category' => 'finance',
        ],

        // ========================================
        // ANNUAL REPORTS
        // ========================================
        'annual_financial_statements' => [
            'name' => 'Annual Financial Statements',
            'frequency' => 'yearly',
            'month' => 1,
            'day' => 31,
            'time' => '08:00',
            'roles' => ['CEO/General Manager', 'Deputy CEO', 'Chief Finance Officer', 'Board Chairperson', 'Board Member', 'Board Treasurer'],
            'description' => 'Complete annual audited financial statements for AGM preparation',
            'category' => 'finance',
        ],
        'annual_agm_pack' => [
            'name' => 'Annual AGM Report Pack',
            'frequency' => 'yearly',
            'month' => 2,
            'day' => 15,
            'time' => '08:00',
            'roles' => ['Board Chairperson', 'Vice Chairperson', 'Board Secretary', 'Board Member', 'CEO/General Manager', 'Chief Finance Officer'],
            'description' => 'Complete Annual General Meeting documentation package',
            'category' => 'governance',
        ],
        'annual_bot_return' => [
            'name' => 'Annual BOT Statutory Return',
            'frequency' => 'yearly',
            'month' => 1,
            'day' => 31,
            'time' => '08:00',
            'roles' => ['CEO/General Manager', 'Chief Finance Officer', 'Compliance Manager', 'Compliance Officer'],
            'description' => 'Annual statutory return to Bank of Tanzania',
            'category' => 'regulatory',
        ],
        'annual_tcdc_return' => [
            'name' => 'Annual TCDC Cooperative Return',
            'frequency' => 'yearly',
            'month' => 2,
            'day' => 28,
            'time' => '08:00',
            'roles' => ['CEO/General Manager', 'Chief Finance Officer', 'Compliance Manager', 'Board Secretary'],
            'description' => 'Annual return to Tanzania Cooperative Development Commission',
            'category' => 'regulatory',
        ],

        // ========================================
        // LOAN OFFICER INDIVIDUAL REPORTS
        // ========================================
        'daily_loan_officer_portfolio' => [
            'name' => 'Daily Loan Officer Portfolio',
            'frequency' => 'daily',
            'time' => '07:00',
            'roles' => ['Credit Officer', 'Senior Credit Officer', 'Credit Analyst'],
            'description' => 'Individual loan officer portfolio summary and action items',
            'category' => 'credit',
        ],
        'weekly_loan_officer_targets' => [
            'name' => 'Weekly Loan Officer Targets Report',
            'frequency' => 'weekly',
            'day' => 'monday',
            'time' => '06:30',
            'roles' => ['Credit Officer', 'Senior Credit Officer', 'Credit Manager'],
            'description' => 'Weekly disbursement and collection targets vs achievement',
            'category' => 'credit',
        ],

        // ========================================
        // RECOVERY & COLLECTIONS REPORTS
        // ========================================
        'daily_recovery_action' => [
            'name' => 'Daily Recovery Action List',
            'frequency' => 'daily',
            'time' => '07:00',
            'roles' => ['Recovery Officer', 'Collections Officer', 'Credit Manager'],
            'description' => 'Daily list of loans requiring follow-up calls, visits, or legal action',
            'category' => 'credit',
        ],
        'weekly_recovery_performance' => [
            'name' => 'Weekly Recovery Performance',
            'frequency' => 'weekly',
            'day' => 'friday',
            'time' => '15:00',
            'roles' => ['Recovery Officer', 'Collections Officer', 'Credit Manager', 'CEO/General Manager'],
            'description' => 'Weekly recovery success rate, promise-to-pay tracking, and write-off candidates',
            'category' => 'credit',
        ],
        'monthly_write_off_report' => [
            'name' => 'Monthly Write-off Report',
            'frequency' => 'monthly',
            'day' => 10,
            'time' => '08:00',
            'roles' => ['Credit Manager', 'Chief Finance Officer', 'CEO/General Manager', 'Board Member'],
            'description' => 'Loans written off, write-off approval pipeline, and recovery on written-off loans',
            'category' => 'credit',
        ],

        // ========================================
        // ACCOUNTING & RECONCILIATION REPORTS
        // ========================================
        'daily_gl_summary' => [
            'name' => 'Daily GL Posting Summary',
            'frequency' => 'daily',
            'time' => '18:00',
            'roles' => ['Chief Accountant', 'Senior Accountant', 'Accountant', 'Chief Finance Officer'],
            'description' => 'Daily general ledger posting summary and unposted transactions',
            'category' => 'finance',
        ],
        'weekly_suspense_account' => [
            'name' => 'Weekly Suspense Account Report',
            'frequency' => 'weekly',
            'day' => 'friday',
            'time' => '16:00',
            'roles' => ['Chief Accountant', 'Senior Accountant', 'Chief Finance Officer', 'Chief Internal Auditor'],
            'description' => 'Suspense account balances and aging for clearance',
            'category' => 'finance',
        ],
        'monthly_interest_accrual' => [
            'name' => 'Monthly Interest Accrual Report',
            'frequency' => 'monthly',
            'day' => 1,
            'time' => '10:00',
            'roles' => ['Chief Accountant', 'Finance Manager', 'Chief Finance Officer'],
            'description' => 'Loan interest accrual, savings interest accrual, and dividend accrual summary',
            'category' => 'finance',
        ],

        // ========================================
        // COMPLIANCE & AML REPORTS
        // ========================================
        'daily_large_transactions' => [
            'name' => 'Daily Large Transactions Report',
            'frequency' => 'daily',
            'time' => '17:00',
            'roles' => ['Compliance Manager', 'Compliance Officer', 'Chief Finance Officer', 'CEO/General Manager'],
            'description' => 'Transactions above TZS 10M for AML monitoring and FIU reporting',
            'category' => 'compliance',
        ],
        'monthly_aml_report' => [
            'name' => 'Monthly AML Compliance Report',
            'frequency' => 'monthly',
            'day' => 5,
            'time' => '08:00',
            'roles' => ['Compliance Manager', 'Compliance Officer', 'CEO/General Manager', 'Chief Internal Auditor'],
            'description' => 'Anti-Money Laundering monitoring, suspicious transactions, and STR summary',
            'category' => 'compliance',
        ],

        // ========================================
        // HR & ADMINISTRATION REPORTS
        // ========================================
        'monthly_staff_performance' => [
            'name' => 'Monthly Staff Performance Summary',
            'frequency' => 'monthly',
            'day' => 5,
            'time' => '08:00',
            'roles' => ['HR Manager', 'HR Officer', 'CEO/General Manager', 'Administration Manager'],
            'description' => 'Staff attendance, leave, and performance metrics summary',
            'category' => 'hr',
        ],
    ];

    /**
     * Statistics for the current run
     */
    protected $stats = [
        'user_reports_processed' => 0,
        'user_reports_sent' => 0,
        'system_reports_processed' => 0,
        'system_reports_sent' => 0,
        'emails_sent' => 0,
        'errors' => 0,
    ];

    /**
     * Service instances for report data generation
     */
    protected $dailyWeeklyService;
    protected $monthlyService;
    protected $quarterlyAnnualService;

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $type = $this->option('type');
        $force = $this->option('force');
        $specificReport = $this->option('report');

        $this->info('===========================================');
        $this->info('  Scheduled Reports Generation');
        $this->info('===========================================');
        $this->info('Started at: ' . now()->format('Y-m-d H:i:s'));

        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode - no emails will be sent');
        }

        // Initialize report data services
        $this->dailyWeeklyService = new ScheduledReportDataService();
        $this->monthlyService = new MonthlyReportDataService();
        $this->quarterlyAnnualService = new QuarterlyAnnualReportDataService();

        try {
            // Process user-scheduled reports
            if (in_array($type, ['all', 'user'])) {
                $this->processUserScheduledReports($dryRun, $force);
            }

            // Process system-scheduled reports
            if (in_array($type, ['all', 'system'])) {
                $this->processSystemScheduledReports($dryRun, $force, $specificReport);
            }

            $this->displaySummary();

        } catch (Exception $e) {
            $this->error('Fatal error: ' . $e->getMessage());
            Log::error('Scheduled reports generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Process user-scheduled reports from the scheduled_reports table
     */
    protected function processUserScheduledReports(bool $dryRun, bool $force): void
    {
        $this->info("\n--- Processing User-Scheduled Reports ---");

        $query = ScheduledReport::where('status', ScheduledReport::STATUS_SCHEDULED);

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('last_run_at')
                  ->orWhere('scheduled_at', '<=', now());
            });
        }

        $reports = $query->get();

        if ($reports->isEmpty()) {
            $this->info('No user-scheduled reports due for generation.');
            return;
        }

        $this->info("Found {$reports->count()} user-scheduled report(s) to process");

        foreach ($reports as $report) {
            $this->stats['user_reports_processed']++;

            if (!$this->isUserReportDue($report, $force)) {
                $this->line("  - Skipping '{$report->report_type}' (not due)");
                continue;
            }

            $this->info("  Processing: {$report->report_type}");

            try {
                // Mark as processing
                if (!$dryRun) {
                    $report->markAsProcessing();
                }

                // Generate report data
                $reportConfig = $report->report_config ?? [];
                $reportData = $this->generateReportData(
                    $reportConfig['report_type'] ?? $report->report_type,
                    $reportConfig
                );

                // Generate report file
                $reportFile = null;
                if (!$dryRun) {
                    $reportFile = $this->generateReportFile($reportData, $reportConfig, $report->report_type);
                }

                // Send email
                if (!empty($report->email_recipients)) {
                    $recipients = is_array($report->email_recipients)
                        ? $report->email_recipients
                        : json_decode($report->email_recipients, true);

                    if (!$dryRun && !empty($recipients)) {
                        foreach ($recipients as $recipient) {
                            Mail::to($recipient)->send(new ScheduledReportMail($report, $reportFile, $reportData));
                            $this->stats['emails_sent']++;
                        }
                    }
                }

                // Mark as completed
                if (!$dryRun) {
                    $report->markAsCompleted($reportFile);
                    $this->scheduleNextOccurrence($report);
                    $this->stats['user_reports_sent']++;
                }

                $this->info("    ✓ Generated successfully");

            } catch (Exception $e) {
                $this->stats['errors']++;
                $this->error("    ✗ Error: " . $e->getMessage());

                if (!$dryRun) {
                    $report->markAsFailed($e->getMessage());
                }

                Log::error('User report generation failed', [
                    'report_id' => $report->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Process system-scheduled automatic reports
     */
    protected function processSystemScheduledReports(bool $dryRun, bool $force, ?string $specificReport): void
    {
        $this->info("\n--- Processing System-Scheduled Reports ---");

        $frequencyFilter = $this->option('frequency');

        $reportsToProcess = $specificReport
            ? [$specificReport => $this->systemReports[$specificReport] ?? null]
            : $this->systemReports;

        // Filter by frequency if specified
        if ($frequencyFilter) {
            $reportsToProcess = array_filter($reportsToProcess, function ($config) use ($frequencyFilter) {
                return $config && ($config['frequency'] ?? '') === $frequencyFilter;
            });
            $this->info("  Filtering by frequency: {$frequencyFilter} (" . count($reportsToProcess) . " reports)");
        }

        foreach ($reportsToProcess as $reportType => $config) {
            if (!$config) {
                $this->warn("Unknown report type: {$specificReport}");
                continue;
            }

            $this->stats['system_reports_processed']++;

            if (!$force && !$this->isSystemReportDue($config)) {
                $this->line("  - Skipping '{$config['name']}' (not due)");
                continue;
            }

            $this->info("  Processing: {$config['name']}");

            try {
                $reportData = $this->generateSystemReportData($reportType, $config);
                $recipients = $this->getReportRecipients($config['roles']);

                if ($recipients->isEmpty()) {
                    $this->warn("    ! No recipients found for roles: " . implode(', ', $config['roles']));
                    continue;
                }

                $this->info("    Recipients: {$recipients->count()} user(s)");

                if (!$dryRun) {
                    foreach ($recipients as $user) {
                        $this->sendSystemReport($user, $reportType, $config, $reportData);
                        $this->stats['emails_sent']++;
                    }
                    $this->stats['system_reports_sent']++;
                }

                $this->info("    ✓ Generated successfully");

            } catch (Exception $e) {
                $this->stats['errors']++;
                $this->error("    ✗ Error: " . $e->getMessage());
                Log::error('System report generation failed', [
                    'report_type' => $reportType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if a user-scheduled report is due
     */
    protected function isUserReportDue(ScheduledReport $report, bool $force): bool
    {
        if ($force) {
            return true;
        }

        if (!$report->last_run_at) {
            return $report->scheduled_at <= now();
        }

        $now = now();
        $lastRun = Carbon::parse($report->last_run_at);

        return match ($report->frequency) {
            ScheduledReport::FREQUENCY_ONCE => false, // Already run
            ScheduledReport::FREQUENCY_DAILY => $lastRun->diffInHours($now) >= 24,
            ScheduledReport::FREQUENCY_WEEKLY => $lastRun->diffInDays($now) >= 7,
            ScheduledReport::FREQUENCY_MONTHLY => $lastRun->diffInDays($now) >= 28,
            ScheduledReport::FREQUENCY_QUARTERLY => $lastRun->diffInDays($now) >= 90,
            ScheduledReport::FREQUENCY_ANNUALLY => $lastRun->diffInDays($now) >= 365,
            default => false,
        };
    }

    /**
     * Check if a system report is due based on current time
     */
    protected function isSystemReportDue(array $config): bool
    {
        $now = now();
        $frequency = $config['frequency'];

        switch ($frequency) {
            case 'daily':
                return true; // Run every day at scheduled time

            case 'weekly':
                $targetDay = strtolower($config['day'] ?? 'monday');
                return strtolower($now->format('l')) === $targetDay;

            case 'monthly':
                $targetDay = $config['day'] ?? 1;
                return $now->day === $targetDay;

            case 'quarterly':
                $targetMonths = $config['months'] ?? [1, 4, 7, 10];
                $targetDay = $config['day'] ?? 1;
                return in_array($now->month, $targetMonths) && $now->day === $targetDay;

            case 'yearly':
            case 'annually':
                $targetMonth = $config['month'] ?? 1;
                $targetDay = $config['day'] ?? 1;
                return $now->month === $targetMonth && $now->day === $targetDay;

            default:
                return false;
        }
    }

    /**
     * Generate report data based on type (for user-scheduled reports)
     * Delegates to the appropriate service based on report type
     */
    protected function generateReportData(string $reportType, array $parameters = []): array
    {
        // Normalize report type for matching
        $normalizedType = strtolower(str_replace(['-', ' '], '_', $reportType));

        // Try to match with system report mappings first
        if (str_starts_with($normalizedType, 'daily_')) {
            return $this->generateDailyReportFromService($normalizedType);
        }

        if (str_starts_with($normalizedType, 'weekly_')) {
            return $this->generateWeeklyReportFromService($normalizedType);
        }

        if (str_starts_with($normalizedType, 'monthly_')) {
            return $this->generateMonthlyReportFromService($normalizedType);
        }

        if (str_starts_with($normalizedType, 'quarterly_')) {
            return $this->generateQuarterlyReportFromService($normalizedType);
        }

        if (str_starts_with($normalizedType, 'annual_')) {
            return $this->generateAnnualReportFromService($normalizedType);
        }

        // Handle legacy/alias report types
        return match ($normalizedType) {
            'operations' => $this->dailyWeeklyService->generateDailyOperationsReport(),
            'arrears' => $this->dailyWeeklyService->generateDailyArrearsReport(),
            'loan_portfolio' => $this->monthlyService->generateMonthlyLoanPortfolioReport(),
            'delinquency' => $this->monthlyService->generateMonthlyDelinquencyReport(),
            'trial_balance' => $this->monthlyService->generateMonthlyTrialBalanceReport(),
            'income_statement', 'statement_of_comprehensive_income' => $this->monthlyService->generateMonthlyIncomeStatementReport(),
            'statement_of_financial_position', 'balance_sheet' => $this->monthlyService->generateMonthlyBalanceSheetReport(),
            'cash_flow' => $this->monthlyService->generateMonthlyCashFlowReport(),
            'npl_classification' => $this->monthlyService->generateMonthlyNPLClassificationReport(),
            'provision', 'loan_loss_provision' => $this->monthlyService->generateMonthlyProvisionReport(),
            'regulatory' => $this->quarterlyAnnualService->generateQuarterlyBOTRegulatoryReport(),
            default => $this->generateGenericReport($reportType,
                $parameters['start_date'] ?? now()->startOfMonth()->format('Y-m-d'),
                $parameters['end_date'] ?? now()->format('Y-m-d')
            ),
        };
    }

    /**
     * Generate system report data using the appropriate service
     * Maps report types to their service methods for real data generation
     */
    protected function generateSystemReportData(string $reportType, array $config): array
    {
        try {
            // Map report types to their service methods
            // DAILY REPORTS - Use ScheduledReportDataService
            if (str_starts_with($reportType, 'daily_')) {
                return $this->generateDailyReportFromService($reportType);
            }

            // WEEKLY REPORTS - Use ScheduledReportDataService
            if (str_starts_with($reportType, 'weekly_')) {
                return $this->generateWeeklyReportFromService($reportType);
            }

            // MONTHLY CREDIT & FINANCE REPORTS - Use MonthlyReportDataService
            if (str_starts_with($reportType, 'monthly_')) {
                return $this->generateMonthlyReportFromService($reportType);
            }

            // QUARTERLY REPORTS - Use QuarterlyAnnualReportDataService
            if (str_starts_with($reportType, 'quarterly_')) {
                return $this->generateQuarterlyReportFromService($reportType);
            }

            // ANNUAL REPORTS - Use QuarterlyAnnualReportDataService
            if (str_starts_with($reportType, 'annual_')) {
                return $this->generateAnnualReportFromService($reportType);
            }

            // Fallback for unknown report types
            Log::warning("Unknown report type: {$reportType}");
            return [
                'report_type' => $reportType,
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'error' => 'Unknown report type - no data generation method available',
            ];

        } catch (Exception $e) {
            Log::error("Error generating report data for {$reportType}: " . $e->getMessage());
            return [
                'report_type' => $reportType,
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate daily report data using ScheduledReportDataService
     */
    protected function generateDailyReportFromService(string $reportType): array
    {
        return match ($reportType) {
            'daily_operations' => $this->dailyWeeklyService->generateDailyOperationsReport(),
            'daily_arrears' => $this->dailyWeeklyService->generateDailyArrearsReport(),
            'daily_cash_position' => $this->dailyWeeklyService->generateDailyCashPositionReport(),
            'daily_disbursements' => $this->dailyWeeklyService->generateDailyDisbursementsReport(),
            'daily_collections' => $this->dailyWeeklyService->generateDailyCollectionsReport(),
            'daily_member_activity' => $this->dailyWeeklyService->generateDailyMemberActivityReport(),
            'daily_loan_officer_portfolio' => $this->dailyWeeklyService->generateDailyLoanOfficerPortfolioReport(),
            'daily_recovery_action' => $this->dailyWeeklyService->generateDailyRecoveryActionReport(),
            'daily_gl_summary' => $this->dailyWeeklyService->generateDailyGLSummaryReport(),
            'daily_large_transactions' => $this->dailyWeeklyService->generateDailyLargeTransactionsReport(),
            default => ['error' => "Unknown daily report type: {$reportType}"],
        };
    }

    /**
     * Generate weekly report data using ScheduledReportDataService
     */
    protected function generateWeeklyReportFromService(string $reportType): array
    {
        return match ($reportType) {
            'weekly_executive_summary' => $this->dailyWeeklyService->generateWeeklyExecutiveSummaryReport(),
            'weekly_arrears' => $this->dailyWeeklyService->generateWeeklyArrearsReport(),
            'weekly_credit_committee' => $this->dailyWeeklyService->generateWeeklyCreditCommitteeReport(),
            'weekly_collections_performance' => $this->dailyWeeklyService->generateWeeklyCollectionsPerformanceReport(),
            'weekly_risk_alerts' => $this->dailyWeeklyService->generateWeeklyRiskAlertsReport(),
            'weekly_savings_mobilization' => $this->dailyWeeklyService->generateWeeklySavingsMobilizationReport(),
            'weekly_loan_officer_targets' => $this->dailyWeeklyService->generateWeeklyLoanOfficerTargetsReport(),
            'weekly_suspense_account' => $this->dailyWeeklyService->generateWeeklySuspenseAccountReport(),
            'weekly_recovery_performance' => $this->dailyWeeklyService->generateWeeklyArrearsReport(), // Uses arrears data
            default => ['error' => "Unknown weekly report type: {$reportType}"],
        };
    }

    /**
     * Generate monthly report data using MonthlyReportDataService
     */
    protected function generateMonthlyReportFromService(string $reportType): array
    {
        return match ($reportType) {
            // Credit Reports
            'monthly_loan_portfolio' => $this->monthlyService->generateMonthlyLoanPortfolioReport(),
            'monthly_delinquency' => $this->monthlyService->generateMonthlyDelinquencyReport(),
            'monthly_npl_classification' => $this->monthlyService->generateMonthlyNPLClassificationReport(),
            'monthly_provision' => $this->monthlyService->generateMonthlyProvisionReport(),
            'monthly_loan_officer_performance' => $this->monthlyService->generateMonthlyLoanOfficerPerformanceReport(),
            'monthly_loan_product_analysis' => $this->monthlyService->generateMonthlyProductAnalysisReport(),

            // Finance Reports
            'monthly_trial_balance' => $this->monthlyService->generateMonthlyTrialBalanceReport(),
            'monthly_income_statement' => $this->monthlyService->generateMonthlyIncomeStatementReport(),
            'monthly_balance_sheet' => $this->monthlyService->generateMonthlyBalanceSheetReport(),
            'monthly_cash_flow' => $this->monthlyService->generateMonthlyCashFlowReport(),
            'monthly_budget_variance' => $this->monthlyService->generateMonthlyBudgetVarianceReport(),
            'monthly_bank_reconciliation' => $this->monthlyService->generateMonthlyBankReconciliationReport(),
            'monthly_interest_accrual' => $this->monthlyService->generateMonthlyInterestAccrualReport(),
            'monthly_liquidity' => $this->monthlyService->generateMonthlyLiquidityReport(),

            // Membership & Savings Reports (from QuarterlyAnnualService)
            'monthly_membership' => $this->quarterlyAnnualService->generateMonthlyMembershipReport(),
            'monthly_savings_deposits' => $this->quarterlyAnnualService->generateMonthlySavingsDepositsReport(),
            'monthly_shares_dividends' => $this->quarterlyAnnualService->generateMonthlyShareCapitalReport(),
            'monthly_mandatory_savings' => $this->quarterlyAnnualService->generateMonthlyMandatorySavingsReport(),

            // Risk & Compliance Reports (from QuarterlyAnnualService)
            'monthly_risk_assessment' => $this->quarterlyAnnualService->generateMonthlyRiskAssessmentReport(),
            'monthly_concentration_risk' => $this->quarterlyAnnualService->generateMonthlyConcentrationRiskReport(),
            'monthly_aml_report' => $this->quarterlyAnnualService->generateMonthlyAMLComplianceReport(),
            'monthly_write_off_report' => $this->quarterlyAnnualService->generateMonthlyWriteOffReport(),
            'monthly_staff_performance' => $this->generateStaffPerformanceReport(),

            default => ['error' => "Unknown monthly report type: {$reportType}"],
        };
    }

    /**
     * Generate quarterly report data using QuarterlyAnnualReportDataService
     */
    protected function generateQuarterlyReportFromService(string $reportType): array
    {
        return match ($reportType) {
            // Regulatory Reports
            'quarterly_regulatory' => $this->quarterlyAnnualService->generateQuarterlyBOTRegulatoryReport(),
            'quarterly_capital_adequacy' => $this->quarterlyAnnualService->generateQuarterlyCapitalAdequacyReport(),
            'quarterly_liquid_assets' => $this->quarterlyAnnualService->generateQuarterlyLiquidAssetsReport(),
            'quarterly_large_exposures' => $this->quarterlyAnnualService->generateQuarterlyLargeExposuresReport(),
            'quarterly_npl_return' => $this->quarterlyAnnualService->generateQuarterlyNPLReturnReport(),
            'quarterly_tcdc_supervision' => $this->quarterlyAnnualService->generateQuarterlyTCDCSupervisionReport(),
            'quarterly_tcdc_membership' => $this->quarterlyAnnualService->generateQuarterlyTCDCMembershipReport(),

            // Governance Reports
            'quarterly_board_pack' => $this->quarterlyAnnualService->generateQuarterlyBoardPackReport(),
            'quarterly_supervisory_report' => $this->quarterlyAnnualService->generateQuarterlySupervisoryReport(),
            'quarterly_audit_report' => $this->quarterlyAnnualService->generateQuarterlyAuditReport(),
            'quarterly_risk_report' => $this->quarterlyAnnualService->generateQuarterlyRiskReport(),
            'quarterly_financial_statements' => $this->quarterlyAnnualService->generateQuarterlyFinancialStatementsReport(),

            default => ['error' => "Unknown quarterly report type: {$reportType}"],
        };
    }

    /**
     * Generate annual report data using QuarterlyAnnualReportDataService
     */
    protected function generateAnnualReportFromService(string $reportType): array
    {
        return match ($reportType) {
            'annual_financial_statements' => $this->quarterlyAnnualService->generateAnnualFinancialStatementsReport(),
            'annual_agm_pack' => $this->quarterlyAnnualService->generateAnnualAGMPackReport(),
            'annual_bot_return' => $this->quarterlyAnnualService->generateAnnualBOTReturnReport(),
            'annual_tcdc_return' => $this->quarterlyAnnualService->generateAnnualTCDCReturnReport(),
            default => ['error' => "Unknown annual report type: {$reportType}"],
        };
    }

    /**
     * Generate Staff Performance Report (simplified version)
     */
    protected function generateStaffPerformanceReport(): array
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $now->copy()->endOfMonth()->format('Y-m-d');

        // Get staff performance metrics
        $loanOfficers = DB::table('users')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', 'LIKE', '%Credit%')
            ->select('users.id', 'users.name', 'users.email')
            ->distinct()
            ->get();

        $performance = $loanOfficers->map(function ($officer) use ($startOfMonth, $endOfMonth) {
            $disbursements = DB::table('loans')
                ->where('created_by', $officer->id)
                ->whereBetween('disbursement_date', [$startOfMonth, $endOfMonth])
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(CAST(principle AS DECIMAL(15,2))), 0) as amount')
                ->first();

            $collections = DB::table('loan_payments')
                ->join('loans', 'loans.id', '=', 'loan_payments.loan_id')
                ->where('loans.created_by', $officer->id)
                ->whereBetween('loan_payments.payment_date', [$startOfMonth, $endOfMonth])
                ->selectRaw('COALESCE(SUM(loan_payments.amount), 0) as amount')
                ->first();

            return [
                'name' => $officer->name,
                'disbursements_count' => $disbursements->count ?? 0,
                'disbursements_amount' => $disbursements->amount ?? 0,
                'collections_amount' => $collections->amount ?? 0,
            ];
        })->toArray();

        return [
            'report_type' => 'Monthly Staff Performance Summary',
            'report_info' => [
                'name' => 'Monthly Staff Performance Summary',
                'description' => 'Staff performance metrics including disbursements and collections',
                'compliance' => ['Internal'],
            ],
            'period' => ['start' => $startOfMonth, 'end' => $endOfMonth],
            'generated_at' => $now->format('Y-m-d H:i:s'),
            'staff_performance' => $performance,
            'summary' => [
                'total_staff' => count($performance),
                'total_disbursements' => collect($performance)->sum('disbursements_amount'),
                'total_collections' => collect($performance)->sum('collections_amount'),
            ],
        ];
    }

    /**
     * Generate Daily Operations Report
     */
    protected function generateDailyOperationsReport(string $startDate, string $endDate): array
    {
        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])->get();

        $newLoans = LoansModel::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])->get();

        $disbursements = LoansModel::where('status', 'disbursed')
            ->whereBetween('disbursement_date', [$startDate, $endDate])
            ->get();

        $repayments = Transaction::where('type', 'credit')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->get();

        return [
            'report_type' => 'Daily Operations Report',
            'report_info' => [
                'name' => 'Daily Operations Report',
                'description' => 'Summary of daily transactions, loan applications, disbursements, and repayments',
                'compliance' => ['BOT', 'Internal'],
            ],
            'period' => ['start' => $startDate, 'end' => $endDate],
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'transactions' => [
                'count' => $transactions->count(),
                'total_amount' => $transactions->sum('amount'),
                'by_type' => $transactions->groupBy('type')
                    ->map(fn($group) => [
                        'count' => $group->count(),
                        'amount' => $group->sum('amount'),
                    ])->toArray(),
            ],
            'new_loans' => [
                'count' => $newLoans->count(),
                'total_amount' => $newLoans->sum('principle'),
                'average_amount' => $newLoans->count() > 0 ? round($newLoans->avg('principle'), 2) : 0,
            ],
            'disbursements' => [
                'count' => $disbursements->count(),
                'total_amount' => $disbursements->sum('principle'),
            ],
            'repayments' => [
                'count' => $repayments->count(),
                'total_amount' => $repayments->sum('amount'),
            ],
            'totals' => [
                'total_transactions' => $transactions->count(),
                'total_value' => $transactions->sum('amount'),
            ],
        ];
    }

    /**
     * Generate Arrears Report
     */
    protected function generateArrearsReport(): array
    {
        $arrearsLoans = LoansModel::whereIn('status', ['ACTIVE', 'DISBURSED'])
            ->where('total_arrears', '>', 0)
            ->with(['client', 'loanProduct'])
            ->get();

        $agingBuckets = [
            '1-30 days' => $arrearsLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) >= 1 && ($l->days_in_arrears ?? 0) <= 30),
            '31-60 days' => $arrearsLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) >= 31 && ($l->days_in_arrears ?? 0) <= 60),
            '61-90 days' => $arrearsLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) >= 61 && ($l->days_in_arrears ?? 0) <= 90),
            '91-180 days' => $arrearsLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) >= 91 && ($l->days_in_arrears ?? 0) <= 180),
            '180+ days' => $arrearsLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) > 180),
        ];

        $totalPortfolio = LoansModel::whereIn('status', ['ACTIVE', 'DISBURSED'])->sum('principle');

        return [
            'report_type' => 'Arrears Report',
            'report_info' => [
                'name' => 'Loan Arrears Report',
                'description' => 'Detailed analysis of loans in arrears with aging breakdown',
                'compliance' => ['BOT', 'IFRS9'],
            ],
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_loans_in_arrears' => $arrearsLoans->count(),
                'total_arrears_amount' => $arrearsLoans->sum('total_arrears'),
                'total_outstanding' => $arrearsLoans->sum('principle') - $arrearsLoans->sum('total_principal_paid'),
                'total_portfolio' => $totalPortfolio,
                'par_ratio' => $totalPortfolio > 0 ? round(($arrearsLoans->sum('principle') / $totalPortfolio) * 100, 2) : 0,
            ],
            'aging_analysis' => collect($agingBuckets)->map(fn($loans, $bucket) => [
                'bucket' => $bucket,
                'count' => $loans->count(),
                'arrears_amount' => $loans->sum('total_arrears'),
                'outstanding_balance' => $loans->sum('principle') - $loans->sum('total_principal_paid'),
                'percentage' => $arrearsLoans->count() > 0 ? round(($loans->count() / $arrearsLoans->count()) * 100, 2) : 0,
            ])->values()->toArray(),
            'top_defaulters' => $arrearsLoans->sortByDesc('total_arrears')->take(10)->map(fn($loan) => [
                'loan_id' => $loan->loan_id ?? 'L-' . $loan->id,
                'client_name' => $loan->client ? $loan->client->first_name . ' ' . $loan->client->last_name : 'N/A',
                'arrears_amount' => $loan->total_arrears ?? 0,
                'days_in_arrears' => $loan->days_in_arrears ?? 0,
                'outstanding_balance' => $loan->principle - ($loan->total_principal_paid ?? 0),
            ])->values()->toArray(),
            'totals' => [
                'total_arrears' => $arrearsLoans->sum('total_arrears'),
            ],
        ];
    }

    /**
     * Generate Loan Portfolio Report
     */
    protected function generateLoanPortfolioReport(): array
    {
        $activeLoans = LoansModel::whereIn('status', ['ACTIVE', 'DISBURSED'])
            ->with(['loanProduct'])
            ->get();

        $totalPortfolio = $activeLoans->sum('principle');
        $arrearsAmount = $activeLoans->where('total_arrears', '>', 0)->sum('principle');

        $byProduct = $activeLoans->groupBy('loan_product_id')->map(function($loans) use ($totalPortfolio) {
            $productName = $loans->first()->loanProduct->name ?? 'Unknown';
            $outstanding = $loans->sum('principle') - $loans->sum('total_principal_paid');
            return [
                'product_name' => $productName,
                'count' => $loans->count(),
                'outstanding' => $outstanding,
                'arrears' => $loans->sum('total_arrears'),
                'percentage' => $totalPortfolio > 0 ? round(($outstanding / $totalPortfolio) * 100, 2) : 0,
            ];
        })->values()->toArray();

        return [
            'report_type' => 'Loan Portfolio Report',
            'report_info' => [
                'name' => 'Loan Portfolio Analysis',
                'description' => 'Comprehensive analysis of the loan portfolio including product distribution and risk metrics',
                'compliance' => ['BOT', 'IFRS9'],
            ],
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'portfolio_summary' => [
                'total_active_loans' => $activeLoans->count(),
                'total_outstanding' => $totalPortfolio,
                'total_arrears' => $arrearsAmount,
                'par_ratio' => $totalPortfolio > 0 ? round(($arrearsAmount / $totalPortfolio) * 100, 2) : 0,
                'average_loan_size' => $activeLoans->count() > 0 ? round($activeLoans->avg('principle'), 2) : 0,
            ],
            'by_product' => $byProduct,
            'risk_distribution' => [
                'performing' => [
                    'count' => $activeLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) == 0)->count(),
                    'amount' => $activeLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) == 0)->sum('principle'),
                ],
                'watch' => [
                    'count' => $activeLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) > 0 && ($l->days_in_arrears ?? 0) <= 30)->count(),
                    'amount' => $activeLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) > 0 && ($l->days_in_arrears ?? 0) <= 30)->sum('principle'),
                ],
                'substandard' => [
                    'count' => $activeLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) > 30 && ($l->days_in_arrears ?? 0) <= 90)->count(),
                    'amount' => $activeLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) > 30 && ($l->days_in_arrears ?? 0) <= 90)->sum('principle'),
                ],
                'doubtful' => [
                    'count' => $activeLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) > 90 && ($l->days_in_arrears ?? 0) <= 180)->count(),
                    'amount' => $activeLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) > 90 && ($l->days_in_arrears ?? 0) <= 180)->sum('principle'),
                ],
                'loss' => [
                    'count' => $activeLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) > 180)->count(),
                    'amount' => $activeLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) > 180)->sum('principle'),
                ],
            ],
            'totals' => [
                'total_portfolio' => $totalPortfolio,
                'total_loans' => $activeLoans->count(),
            ],
        ];
    }

    /**
     * Generate Delinquency Report
     */
    protected function generateDelinquencyReport(): array
    {
        $activeLoans = LoansModel::whereIn('status', ['ACTIVE', 'DISBURSED'])->get();
        $delinquentLoans = $activeLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) > 0);

        $totalPortfolio = $activeLoans->sum('principle');

        $par30 = $delinquentLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) > 30)->sum('principle');
        $par60 = $delinquentLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) > 60)->sum('principle');
        $par90 = $delinquentLoans->filter(fn($l) => ($l->days_in_arrears ?? 0) > 90)->sum('principle');

        return [
            'report_type' => 'Delinquency Report',
            'report_info' => [
                'name' => 'Loan Delinquency Report',
                'description' => 'Portfolio at Risk (PAR) analysis and delinquency metrics',
                'compliance' => ['BOT', 'IFRS9'],
            ],
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'metrics' => [
                'par_1' => $totalPortfolio > 0 ? round(($delinquentLoans->sum('principle') / $totalPortfolio) * 100, 2) : 0,
                'par_30' => $totalPortfolio > 0 ? round(($par30 / $totalPortfolio) * 100, 2) : 0,
                'par_60' => $totalPortfolio > 0 ? round(($par60 / $totalPortfolio) * 100, 2) : 0,
                'par_90' => $totalPortfolio > 0 ? round(($par90 / $totalPortfolio) * 100, 2) : 0,
            ],
            'delinquent_loans' => [
                'count' => $delinquentLoans->count(),
                'total_outstanding' => $delinquentLoans->sum('principle'),
                'total_arrears' => $delinquentLoans->sum('total_arrears'),
                'percentage_of_portfolio' => $activeLoans->count() > 0
                    ? round(($delinquentLoans->count() / $activeLoans->count()) * 100, 2) : 0,
            ],
            'par_amounts' => [
                'par_1_amount' => $delinquentLoans->sum('principle'),
                'par_30_amount' => $par30,
                'par_60_amount' => $par60,
                'par_90_amount' => $par90,
            ],
            'totals' => [
                'total_portfolio' => $totalPortfolio,
                'total_delinquent' => $delinquentLoans->sum('principle'),
            ],
        ];
    }

    /**
     * Generate Trial Balance Report
     */
    protected function generateTrialBalanceReport(string $asOfDate): array
    {
        // Get accounts from the accounts table (GL accounts)
        $accounts = DB::table('accounts')
            ->whereNotNull('major_category_code')
            ->where('major_category_code', '!=', '')
            ->orderBy('account_number')
            ->get();

        $debits = 0;
        $credits = 0;

        $balances = $accounts->map(function ($account) use (&$debits, &$credits, $asOfDate) {
            // Get balance from general_ledger
            $glBalance = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->where('created_at', '<=', $asOfDate . ' 23:59:59')
                ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
                ->value('balance') ?? 0;

            // Use account's balance if no GL entries
            $balance = $glBalance != 0 ? $glBalance : floatval($account->balance ?? 0);

            if ($balance >= 0) {
                $debits += $balance;
            } else {
                $credits += abs($balance);
            }

            return [
                'account_code' => $account->account_number,
                'account_name' => $account->account_name,
                'debit' => $balance >= 0 ? $balance : 0,
                'credit' => $balance < 0 ? abs($balance) : 0,
            ];
        })->filter(fn($a) => $a['debit'] != 0 || $a['credit'] != 0)->values()->toArray();

        return [
            'report_type' => 'Trial Balance',
            'report_info' => [
                'name' => 'Trial Balance',
                'description' => 'Summary of all GL account balances as of the specified date',
                'compliance' => ['IFRS', 'Internal'],
            ],
            'as_of_date' => $asOfDate,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_debits' => $debits,
                'total_credits' => $credits,
                'difference' => abs($debits - $credits),
                'balanced' => abs($debits - $credits) < 0.01,
            ],
            'accounts' => $balances,
            'totals' => [
                'total_debits' => $debits,
                'total_credits' => $credits,
            ],
        ];
    }

    /**
     * Generate Income Statement Report
     */
    protected function generateIncomeStatementReport(string $startDate, string $endDate): array
    {
        // Revenue accounts (major_category_code 4000) from general_ledger
        $revenueAccounts = DB::table('general_ledger')
            ->join('accounts', 'accounts.account_number', '=', 'general_ledger.record_on_account_number')
            ->where('accounts.major_category_code', '4000')
            ->whereBetween('general_ledger.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->select('accounts.account_number as account_code', 'accounts.account_name')
            ->selectRaw('COALESCE(SUM(general_ledger.credit), 0) - COALESCE(SUM(general_ledger.debit), 0) as amount')
            ->groupBy('accounts.id', 'accounts.account_number', 'accounts.account_name')
            ->get();

        $totalRevenue = $revenueAccounts->sum('amount');

        // Expense accounts (major_category_code 5000) from general_ledger
        $expenseAccounts = DB::table('general_ledger')
            ->join('accounts', 'accounts.account_number', '=', 'general_ledger.record_on_account_number')
            ->where('accounts.major_category_code', '5000')
            ->whereBetween('general_ledger.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->select('accounts.account_number as account_code', 'accounts.account_name')
            ->selectRaw('COALESCE(SUM(general_ledger.debit), 0) - COALESCE(SUM(general_ledger.credit), 0) as amount')
            ->groupBy('accounts.id', 'accounts.account_number', 'accounts.account_name')
            ->get();

        $totalExpenses = $expenseAccounts->sum('amount');

        return [
            'report_type' => 'Income Statement',
            'report_info' => [
                'name' => 'Statement of Comprehensive Income',
                'description' => 'Revenue and expense summary for the reporting period',
                'compliance' => ['IFRS', 'BOT'],
            ],
            'period' => ['start' => $startDate, 'end' => $endDate],
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'revenue' => [
                'accounts' => $revenueAccounts->toArray(),
                'total' => $totalRevenue,
            ],
            'expenses' => [
                'accounts' => $expenseAccounts->toArray(),
                'total' => $totalExpenses,
            ],
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'net_income' => $totalRevenue - $totalExpenses,
                'profit_margin' => $totalRevenue > 0 ? round((($totalRevenue - $totalExpenses) / $totalRevenue) * 100, 2) : 0,
            ],
            'totals' => [
                'net_income' => $totalRevenue - $totalExpenses,
            ],
        ];
    }

    /**
     * Generate Statement of Financial Position
     */
    protected function generateStatementOfFinancialPosition(string $asOfDate): array
    {
        // Assets (major_category_code 1000)
        $assets = DB::table('accounts')
            ->where('major_category_code', '1000')
            ->sum('balance') ?? 0;

        // Liabilities (major_category_code 2000)
        $liabilities = DB::table('accounts')
            ->where('major_category_code', '2000')
            ->sum('balance') ?? 0;

        // Equity (major_category_code 3000)
        $equity = DB::table('accounts')
            ->where('major_category_code', '3000')
            ->sum('balance') ?? 0;

        return [
            'report_type' => 'Statement of Financial Position',
            'report_info' => [
                'name' => 'Statement of Financial Position',
                'description' => 'Balance sheet showing assets, liabilities, and equity',
                'compliance' => ['IFRS', 'BOT'],
            ],
            'as_of_date' => $asOfDate,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_assets' => $assets,
                'total_liabilities' => $liabilities,
                'total_equity' => $equity,
                'balance_check' => abs($assets - ($liabilities + $equity)) < 0.01,
            ],
            'totals' => [
                'total_assets' => $assets,
                'total_liabilities' => $liabilities,
                'total_equity' => $equity,
            ],
        ];
    }

    /**
     * Generate Cash Flow Report
     */
    protected function generateCashFlowReport(string $startDate, string $endDate): array
    {
        // Simplified cash flow
        $cashReceipts = Transaction::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->whereIn('transaction_type', ['deposit', 'loan_repayment', 'fee_payment'])
            ->sum('amount');

        $cashPayments = Transaction::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->whereIn('transaction_type', ['withdrawal', 'disbursement', 'expense'])
            ->sum('amount');

        return [
            'report_type' => 'Cash Flow Statement',
            'report_info' => [
                'name' => 'Statement of Cash Flows',
                'description' => 'Summary of cash inflows and outflows for the period',
                'compliance' => ['IFRS', 'BOT'],
            ],
            'period' => ['start' => $startDate, 'end' => $endDate],
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'operating_activities' => [
                'cash_receipts' => $cashReceipts,
                'cash_payments' => $cashPayments,
                'net_cash_from_operations' => $cashReceipts - $cashPayments,
            ],
            'totals' => [
                'net_cash_flow' => $cashReceipts - $cashPayments,
            ],
        ];
    }

    /**
     * Generate Executive Summary Report
     */
    protected function generateExecutiveSummaryReport(string $startDate, string $endDate): array
    {
        $operations = $this->generateDailyOperationsReport($startDate, $endDate);
        $portfolio = $this->generateLoanPortfolioReport();
        $delinquency = $this->generateDelinquencyReport();
        $arrears = $this->generateArrearsReport();

        return [
            'report_type' => 'Executive Summary',
            'report_info' => [
                'name' => 'Weekly Executive Summary',
                'description' => 'High-level overview of key performance metrics for executive review',
                'compliance' => ['Internal'],
            ],
            'period' => ['start' => $startDate, 'end' => $endDate],
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'highlights' => [
                'transactions' => [
                    'count' => $operations['transactions']['count'],
                    'amount' => $operations['transactions']['total_amount'],
                ],
                'new_loans' => $operations['new_loans'],
                'disbursements' => $operations['disbursements'],
                'portfolio' => $portfolio['portfolio_summary'],
                'delinquency' => $delinquency['metrics'],
                'arrears_summary' => [
                    'total_in_arrears' => $arrears['summary']['total_loans_in_arrears'],
                    'total_amount' => $arrears['summary']['total_arrears_amount'],
                ],
            ],
            'totals' => [
                'total_portfolio' => $portfolio['portfolio_summary']['total_outstanding'],
                'par_ratio' => $portfolio['portfolio_summary']['par_ratio'],
            ],
        ];
    }

    /**
     * Generate Regulatory Report
     */
    protected function generateRegulatoryReport(): array
    {
        $portfolio = $this->generateLoanPortfolioReport();
        $delinquency = $this->generateDelinquencyReport();

        $totalMembers = ClientsModel::where('status', 'active')->count();
        $totalDeposits = DB::table('accounts')
            ->where('account_type', 'savings')
            ->sum('balance') ?? 0;

        $quarter = 'Q' . ceil(now()->month / 3);
        $year = now()->year;

        return [
            'report_type' => 'Quarterly Regulatory Report',
            'report_info' => [
                'name' => 'Quarterly Regulatory Compliance Report',
                'description' => 'Comprehensive report for regulatory submission to Bank of Tanzania',
                'compliance' => ['BOT', 'TCDC'],
            ],
            'quarter' => $quarter . ' ' . $year,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'membership' => [
                'total_members' => $totalMembers,
                'new_members_this_quarter' => ClientsModel::where('status', 'active')
                    ->where('created_at', '>=', now()->startOfQuarter())
                    ->count(),
            ],
            'deposits' => [
                'total_deposits' => $totalDeposits,
            ],
            'loans' => $portfolio['portfolio_summary'],
            'asset_quality' => $delinquency['metrics'],
            'capital_adequacy' => [
                'note' => 'Capital adequacy ratios to be calculated based on regulatory requirements',
            ],
            'totals' => [
                'total_assets' => $portfolio['portfolio_summary']['total_outstanding'] + $totalDeposits,
                'total_members' => $totalMembers,
            ],
        ];
    }

    /**
     * Generate generic report
     */
    protected function generateGenericReport(string $reportType, string $startDate, string $endDate): array
    {
        return [
            'report_type' => $reportType,
            'report_info' => [
                'name' => ucwords(str_replace('_', ' ', $reportType)),
                'description' => 'Generated report',
            ],
            'period' => ['start' => $startDate, 'end' => $endDate],
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'message' => 'Report data not available for this report type',
        ];
    }

    /**
     * Get recipients for report based on roles
     */
    protected function getReportRecipients(array $roleNames): \Illuminate\Support\Collection
    {
        $roleIds = Role::whereIn('name', $roleNames)->pluck('id')->toArray();

        if (empty($roleIds)) {
            return collect();
        }

        return User::whereHas('roles', function ($query) use ($roleIds) {
            $query->whereIn('roles.id', $roleIds);
        })
        ->whereRaw("UPPER(status) = 'ACTIVE'")
        ->whereNotNull('email')
        ->get();
    }

    /**
     * Send system-scheduled report
     */
    protected function sendSystemReport(User $user, string $reportType, array $config, array $reportData): void
    {
        // Check if SystemReportMail exists, if not use ScheduledReportMail
        if (class_exists('App\Mail\SystemReportMail')) {
            Mail::to($user->email)->send(new SystemReportMail(
                $reportData,
                $config['name'],
                $user->name ?? 'User',
                $config['description']
            ));
        } else {
            // Create a simple stdClass to mimic the report object
            $report = new \stdClass();
            $report->report_type = $config['name'];
            $report->email_subject = $config['name'];
            $report->frequency = $config['frequency'];
            $report->status = 'completed';
            $report->email_message = $config['description'];
            $report->id = 'SYS-' . strtoupper($reportType);

            Mail::to($user->email)->send(new ScheduledReportMail($report, null, $reportData));
        }
    }

    /**
     * Generate report file (PDF)
     */
    protected function generateReportFile(array $reportData, array $config, string $reportType): ?string
    {
        try {
            $format = $config['format'] ?? 'pdf';
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "scheduled_report_{$reportType}_{$timestamp}.{$format}";
            $filepath = storage_path("app/reports/scheduled/{$filename}");

            // Ensure directory exists
            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            if ($format === 'pdf' && class_exists('PDF')) {
                $viewName = 'reports.pdf.' . $reportType;
                if (view()->exists($viewName)) {
                    $pdf = PDF::loadView($viewName, ['data' => $reportData, 'config' => $config]);
                    $pdf->save($filepath);
                    return $filepath;
                }
            }

            // Fallback: save as JSON
            $jsonPath = str_replace('.pdf', '.json', $filepath);
            file_put_contents($jsonPath, json_encode($reportData, JSON_PRETTY_PRINT));
            return $jsonPath;

        } catch (Exception $e) {
            Log::warning('Could not generate report file: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Schedule next occurrence for recurring report
     */
    protected function scheduleNextOccurrence(ScheduledReport $report): void
    {
        if ($report->frequency === ScheduledReport::FREQUENCY_ONCE) {
            return;
        }

        $nextRun = $this->calculateNextScheduledDate($report);

        $report->update([
            'next_run_at' => $nextRun,
            'scheduled_at' => $nextRun,
            'status' => ScheduledReport::STATUS_SCHEDULED,
        ]);
    }

    /**
     * Calculate next scheduled date
     */
    protected function calculateNextScheduledDate(ScheduledReport $report): Carbon
    {
        $lastScheduled = Carbon::parse($report->scheduled_at ?? now());

        return match ($report->frequency) {
            ScheduledReport::FREQUENCY_DAILY => $lastScheduled->copy()->addDay(),
            ScheduledReport::FREQUENCY_WEEKLY => $lastScheduled->copy()->addWeek(),
            ScheduledReport::FREQUENCY_MONTHLY => $lastScheduled->copy()->addMonth(),
            ScheduledReport::FREQUENCY_QUARTERLY => $lastScheduled->copy()->addMonths(3),
            ScheduledReport::FREQUENCY_ANNUALLY => $lastScheduled->copy()->addYear(),
            default => $lastScheduled->copy()->addDay(),
        };
    }

    /**
     * Display summary of the run
     */
    protected function displaySummary(): void
    {
        $this->info("\n===========================================");
        $this->info('  Summary');
        $this->info('===========================================');
        $this->info("User reports processed: {$this->stats['user_reports_processed']}");
        $this->info("User reports sent: {$this->stats['user_reports_sent']}");
        $this->info("System reports processed: {$this->stats['system_reports_processed']}");
        $this->info("System reports sent: {$this->stats['system_reports_sent']}");
        $this->info("Total emails sent: {$this->stats['emails_sent']}");

        if ($this->stats['errors'] > 0) {
            $this->error("Errors encountered: {$this->stats['errors']}");
        }

        $this->info('Completed at: ' . now()->format('Y-m-d H:i:s'));
    }
}
