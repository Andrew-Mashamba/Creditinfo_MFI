# Comprehensive Loan Provision Management System

## Overview
A complete IFRS 9 Expected Credit Loss (ECL) provision system has been implemented for the SACCOS Core System, fully replacing the traditional incurred loss model with a forward-looking approach. The new system is now the default provision management system.

## Key Features Implemented

### 1. IFRS 9 ECL Model
- **3-Stage Classification System**:
  - **Stage 1**: Performing loans (12-month ECL)
  - **Stage 2**: Underperforming with SICR (Lifetime ECL)
  - **Stage 3**: Non-performing/Credit-impaired (Lifetime ECL)

- **ECL Calculation**: `ECL = PD × LGD × EAD`
  - **PD** (Probability of Default): Risk-adjusted based on client history
  - **LGD** (Loss Given Default): Collateral and guarantor adjusted
  - **EAD** (Exposure at Default): Outstanding balance + undrawn commitments

### 2. Provision Methods
- **IFRS 9 Method**: Full ECL model implementation
- **Regulatory Method**: Traditional classification-based provisioning
- **Hybrid Method**: Higher of IFRS 9 and regulatory (conservative approach)

### 3. Forward-Looking Adjustments
- **Economic Scenarios**:
  - Optimistic: -20% provision adjustment
  - Base Case: No adjustment
  - Pessimistic: +30% provision adjustment
- **SICR Indicators**:
  - Quantitative: Days past due, PD increase, credit score deterioration
  - Qualitative: Restructuring, business changes, forbearance

### 4. Comprehensive Features

#### Calculation & Processing
- Automated provision calculation for all active loans
- Stage migration tracking
- Multiple calculation methods support
- Economic scenario modeling
- Batch processing capability

#### Financial Integration
- Automated GL posting with journal entries
- Provision reversal capability
- Audit trail for all transactions
- Integration with chart of accounts

#### Reporting & Analytics
- ECL calculation reports
- Stage migration analysis
- Coverage ratio tracking
- Provision movement reports
- NPL ratio monitoring
- Regulatory compliance reports

#### User Interface
- Tabbed interface for easy navigation
- Real-time provision calculations
- Interactive dashboards
- Export capabilities (CSV, Excel)
- Visual analytics with charts

## System Access

### Location
The provision system can be accessed through:
- **Path**: Accounting Module → Provision
- **Component**: `App\Http\Livewire\Accounting\Provision`
- **View**: `resources/views/livewire/accounting/provision.blade.php`

### Direct Access
The IFRS 9 ECL provision system is now the default. The legacy system has been fully replaced.

### Tabs Available
1. **Overview**: Summary statistics and ECL stage breakdown
2. **Loan Details**: Individual loan provisions with search/filter
3. **ECL Staging**: Stage migration analysis and SICR indicators
4. **Journal Entries**: GL posting and reversal management
5. **Analytics**: Visual charts and trend analysis
6. **Reports**: Standard and custom report generation

## Database Structure

### New Tables
- `provision_summaries` - Daily provision summary records
- `loan_restructures` - Loan restructuring for SICR tracking
- `loan_commitments` - Undrawn commitment tracking
- `journal_entries` - General ledger journal entries
- `journal_entry_lines` - Journal entry line items

### Enhanced Tables
- `loan_loss_provisions` - Added ECL fields (stage, PD, LGD, EAD)
- `loan_provision_settings` - Added stage threshold configurations

## Configuration

### Default Provision Rates
| Classification | Traditional Rate | ECL Stage | Default Rate |
|---------------|------------------|-----------|--------------|
| PERFORMING    | 1%              | Stage 1   | 1%          |
| WATCH         | 5%              | Stage 1   | 1%          |
| SUBSTANDARD   | 25%             | Stage 2   | 10%         |
| DOUBTFUL      | 50%             | Stage 2   | 10%         |
| LOSS          | 100%            | Stage 3   | 100%        |

### Stage Thresholds
- **Stage 1**: 0-30 days in arrears
- **Stage 2**: 31-90 days in arrears
- **Stage 3**: >90 days in arrears

## Services & Components

### Core Services
- `LoanProvisionCalculationService` - Main ECL calculation engine
- `LoanLossProvisionService` - Service layer for provision operations

### Livewire Components
- `Provision` - Comprehensive IFRS 9 ECL provision management component

## Usage Instructions

### Calculate Provisions
1. Navigate to Provision Management
2. Click "Calculate Provisions"
3. Select calculation date and method
4. Choose economic scenario
5. Click "Calculate"

### Post to General Ledger
1. Review calculated provisions
2. Go to "Journal Entries" tab
3. Click "Post to GL"
4. Verify journal entry details
5. Confirm posting

### Generate Reports
1. Go to "Reports" tab
2. Select date range
3. Choose report type
4. Click "Generate Report"

## Migration from Legacy System

### Differences
| Feature | Legacy System | New IFRS 9 System |
|---------|--------------|-------------------|
| Model | Incurred Loss | Expected Credit Loss |
| Approach | Backward-looking | Forward-looking |
| Stages | 5 Classifications | 3 ECL Stages |
| Calculation | Fixed rates | PD × LGD × EAD |
| Scenarios | None | Economic scenarios |
| GL Posting | Manual | Automated |
| Analytics | Basic | Comprehensive |

### Migration Steps
1. Run migration: `php artisan migrate`
2. Review current provisions
3. Calculate using new system
4. Compare results
5. Adjust settings if needed
6. Switch to production use

## Compliance

### IFRS 9 Requirements Met
- ✅ Expected credit loss model
- ✅ 3-stage classification
- ✅ Forward-looking information
- ✅ 12-month vs lifetime ECL
- ✅ SICR assessment
- ✅ Stage migration tracking

### Regulatory Compliance
- SASRA requirements for Kenyan SACCOs
- Basel III capital adequacy
- Central Bank provisioning guidelines

## Technical Notes

### Performance Optimization
- Batch processing for large portfolios
- Caching of provision rates
- Indexed database queries
- Pagination for large datasets

### Security
- Role-based access control
- Audit trail for all changes
- Journal entry approval workflow
- Reversal authorization required

## Testing Checklist
- [ ] Provision calculations are accurate
- [ ] Stage classification is correct
- [ ] GL posting creates proper journal entries
- [ ] Reports generate successfully
- [ ] Charts display correctly
- [ ] Export functions work
- [ ] Search and filter features operate properly

## Future Enhancements
1. Machine learning for PD estimation
2. Stress testing scenarios
3. IFRS 9 backtesting framework
4. Automated regulatory reporting
5. Integration with core banking APIs

---
*Implementation Date: January 9, 2025*
*Version: 2.0*
*IFRS 9 Compliant*