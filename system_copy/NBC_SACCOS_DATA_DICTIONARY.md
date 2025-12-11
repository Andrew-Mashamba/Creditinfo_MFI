# NBC SACCOS Database - Data Dictionary

**Generated:** 2025-11-20  
**Databases:** nbc_saccos_db (363 tables), administration (66 tables)  
**Read-Only Access:** data_team_readonly / DataTeam2024  
**Host:** 22.32.225.150:5432

---

## Quick Start Guide

### Connecting to the Database

```bash
# Connect to main SACCOS database
psql -h 22.32.225.150 -p 5432 -U data_team_readonly -d nbc_saccos_db

# Connect to administration database
psql -h 22.32.225.150 -p 5432 -U data_team_readonly -d administration
```

### Useful Commands

```sql
-- List all tables
\dt

-- Describe a specific table
\d table_name

-- List all columns in a table with details
\d+ table_name

-- Show table sizes
SELECT 
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as size
FROM pg_tables 
WHERE schemaname = 'public'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;

-- Get column information
SELECT 
    column_name,
    data_type,
    character_maximum_length,
    is_nullable,
    column_default
FROM information_schema.columns
WHERE table_name = 'your_table_name'
ORDER BY ordinal_position;
```

---

## Database: nbc_saccos_db

### Core Modules

#### Members & Clients
- `users` - System users and member accounts
- `clients` - Client/member personal information
- `client_next_of_kin` - Member next of kin details
- `client_businesses` - Member business information
- `client_employers` - Member employment details

#### Shares
- `shares` - Share capital records
- `share_types` - Types of shares available
- `share_transactions` - Share purchase/sale transactions

#### Savings & Deposits
- `savings` - Savings account records
- `savings_transactions` - Savings deposits/withdrawals
- `deposits` - Fixed deposit accounts
- `deposit_transactions` - Deposit transactions

#### Loans
- `loans` - Loan applications and records
- `loan_products` - Available loan products
- `loan_transactions` - Loan disbursements and repayments
- `loan_schedules` - Loan repayment schedules
- `loan_guarantors` - Loan guarantor information
- `loan_collaterals` - Loan collateral/security details

#### Accounting
- `accounts` - Chart of accounts
- `journal_entries` - General ledger entries
- `transactions` - All financial transactions
- `internal_transfers` - Internal fund transfers

#### Products & Services
- `products` - Financial products catalog
- `product_categories` - Product categorization

#### Operations
- `branches` - Branch information
- `tellers` - Teller records and operations
- `approvals` - Approval workflows
- `notifications` - System notifications

---

## Database: administration

### Core Modules

#### API Management
- `developer_api_keys` - API authentication keys
- `api_request_logs` - API request logging
- `api_transactions` - Transaction records from APIs
- `api_configurations` - API settings

#### External Integrations
- `institutions` - SACCOS institutions
- `nbc_api_credentials` - NBC API authentication
- `gepg_bill_payments` - GEPG payment gateway transactions
- `luku_token_purchases` - Luku electricity token purchases

#### Security & Fraud Detection
- `security_alerts` - Security event alerts
- `api_fraud_logs` - Fraud detection logs
- `api_blacklist` - Blacklisted entities
- `device_fingerprints` - Device tracking
- `ip_intelligence` - IP reputation data

#### Monitoring
- `api_health_checks` - API health monitoring
- `api_audit_logs` - Audit trail
- `session_analytics` - User session tracking

---

## Key Relationships

### Member Journey
```
users → clients → shares/savings/loans
```

### Loan Workflow  
```
loans → loan_schedules → loan_transactions
loans → loan_guarantors
loans → loan_collaterals
```

### Transaction Flow
```
transactions → journal_entries → accounts
```

### API Integration Flow
```
developer_api_keys → api_request_logs → api_transactions
```

---

## Common Queries

### Get Member Information
```sql
SELECT 
    u.id,
    u.name,
    u.email,
    c.phone_number,
    c.national_id
FROM users u
JOIN clients c ON u.id = c.user_id
WHERE u.status = 'active'
LIMIT 10;
```

### Loan Summary
```sql
SELECT 
    COUNT(*) as total_loans,
    SUM(principal_amount) as total_principal,
    SUM(outstanding_balance) as total_outstanding,
    loan_status
FROM loans
GROUP BY loan_status;
```

### Recent Transactions
```sql
SELECT 
    transaction_id,
    transaction_type,
    amount,
    status,
    created_at
FROM transactions
ORDER BY created_at DESC
LIMIT 20;
```

### API Usage Statistics
```sql
SELECT 
    api_key_id,
    COUNT(*) as request_count,
    AVG(response_time_ms) as avg_response_time,
    DATE(created_at) as date
FROM api_request_logs
WHERE created_at >= CURRENT_DATE - INTERVAL '7 days'
GROUP BY api_key_id, DATE(created_at)
ORDER BY date DESC;
```

---

## Data Types Reference

| PostgreSQL Type | Description | Example |
|----------------|-------------|---------|
| `integer` | Whole numbers | 123 |
| `bigint` | Large integers | 9223372036854775807 |
| `numeric(p,s)` | Exact decimal | 1234.56 |
| `varchar(n)` | Variable text | 'John Doe' |
| `text` | Unlimited text | 'Long description...' |
| `boolean` | True/False | true |
| `timestamp` | Date and time | 2025-11-20 14:30:00 |
| `date` | Date only | 2025-11-20 |
| `json/jsonb` | JSON data | {"key": "value"} |

---

## Best Practices

1. **Always use WHERE clauses** to limit result sets
2. **Use LIMIT** when exploring data
3. **Check table sizes** before running full scans
4. **Use indexes** - most foreign keys and primary keys are indexed
5. **Join carefully** - large tables may slow down queries
6. **Time ranges** - Always filter by date for transaction tables

---

## Need More Details?

To get comprehensive details about any table:
```sql
\d+ table_name
```

To export detailed schema:
```bash
pg_dump -h 22.32.225.150 -U data_team_readonly -d nbc_saccos_db --schema-only > schema.sql
```

---

## Support

For questions about the database structure or access issues, contact:
- Database Admin: Andrew Mashamba
- Access Level: Read-Only SELECT queries only

