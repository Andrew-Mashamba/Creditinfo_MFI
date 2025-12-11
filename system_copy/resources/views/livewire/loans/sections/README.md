# Loan Assessment Modular Sections

This directory contains modular Blade files for the loan assessment page. These files can be included in the main `assessment.blade.php` file to make it more maintainable and organized.

## Available Sections

1. **credit-score.blade.php** - CREDIT SCORE section with gauge visualization
2. **client-information.blade.php** - CLIENT'S INFORMATION section with all client details
3. **product-parameters.blade.php** - PRODUCT PARAMETERS section with loan product details
4. **exceptions.blade.php** - EXCEPTIONS section with validation results and actions
5. **loans-to-be-settled.blade.php** - LOANS TO BE SETTLED section with settlement options
6. **loan-repayment-schedule.blade.php** - LOAN REPAYMENT SCHEDULE section with payment details
7. **assessment.blade.php** - ASSESSMENT section with evaluation results
8. **select-loan-to-restructure.blade.php** - SELECT LOAN TO RESTRUCTURE section (conditional)

## How to Use

### In the main assessment.blade.php file, replace the large sections with includes:

```blade
{{-- Replace the CREDIT SCORE section --}}
@include('livewire.loans.sections.credit-score')

{{-- Replace the CLIENT'S INFORMATION section --}}
@include('livewire.loans.sections.client-information')

{{-- Replace the PRODUCT PARAMETERS section --}}
@include('livewire.loans.sections.product-parameters')

{{-- Replace the EXCEPTIONS section --}}
@include('livewire.loans.sections.exceptions')

{{-- Replace the LOANS TO BE SETTLED section --}}
@include('livewire.loans.sections.loans-to-be-settled')

{{-- Replace the LOAN REPAYMENT SCHEDULE section --}}
@include('livewire.loans.sections.loan-repayment-schedule')

{{-- Replace the ASSESSMENT section --}}
@include('livewire.loans.sections.assessment')

{{-- Replace the SELECT LOAN TO RESTRUCTURE section --}}
@include('livewire.loans.sections.select-loan-to-restructure')
```

## Required Variables

Each section expects specific variables to be available in the component. Make sure these are defined in your Livewire component:

### Credit Score Section
- `$creditScoreValue`
- `$creditScoreGrade`
- `$creditScoreRisk`
- `$creditScoreTrend`
- `$creditScore` (array with reasons)

### Client Information Section
- `$basicInfo` (array with client basic details)
- `$contactInfo` (array with contact details)
- `$employmentInfo` (array with employment details)
- `$financialInfo` (array with financial details)
- `$riskIndicators` (array with risk indicators)
- `$statusIndicators` (array with status details)

### Product Parameters Section
- `$productBasicInfo` (array with product basic details)
- `$productLoanLimits` (array with loan limits)
- `$productInterestInfo` (array with interest details)
- `$productGracePeriods` (array with grace period details)
- `$productFeesAndCharges` (array with fees and charges)
- `$productInsuranceInfo` (array with insurance details)
- `$productRepaymentInfo` (array with repayment details)
- `$productRequirements` (array with requirements)
- `$productValidation` (array with validation results)

### Exceptions Section
- `$exceptionData` (array with exception details)
- `$showActionButtons` (boolean)
- `$loan` (loan model)

### Loans to be Settled Section
- `$settlementService` (settlement service instance)
- `$selectedLoansToSettle` (array of selected loan IDs)

### Loan Repayment Schedule Section
- `$loanSchedule` (array with schedule details)
- `$loanAmount`
- `$totalInterest`
- `$monthlyPayment`
- `$loanTerm`
- `$completedPayments`
- `$pendingPayments`
- `$overduePayments`
- `$interestRate`
- `$paymentFrequency`
- `$amortizationMethod`

### Assessment Section
- `$assessmentResult` (array with assessment details)

### Select Loan to Restructure Section
- `$loan` (loan model with loan_type_2)
- `$activeLoans` (collection of active loans)
- `$selectedLoan` (selected loan ID)

## Benefits

1. **Modularity**: Each section is self-contained and can be maintained independently
2. **Reusability**: Sections can be reused in other parts of the application
3. **Maintainability**: Easier to find and fix issues in specific sections
4. **Readability**: Main assessment file becomes much cleaner and easier to read
5. **Testing**: Each section can be tested independently

## Notes

- All sections use consistent styling with Tailwind CSS classes
- Each section includes proper error handling with null coalescing operators
- JavaScript is included where needed (e.g., credit score gauge)
- Conditional rendering is used where appropriate
- All sections are responsive and mobile-friendly 