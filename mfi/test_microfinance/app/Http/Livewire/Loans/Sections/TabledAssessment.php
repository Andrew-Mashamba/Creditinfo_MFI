<?php

namespace App\Http\Livewire\Loans\Sections;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class TabledAssessment extends Component
{
    public $loan;
    public $member;
    public $product;
    public $assessmentData = [];
    public $topUpData = [];
    public $restructureData = [];
    public $collateralData = [];
    public $chargesData = [];
    public $insuranceData = [];
    public $scheduleData = [];
    public $savings = 0;
    public $activeLoans = [];
    public $totalDeductions = 0;
    public $netDisbursement = 0;
    public $approved_loan_value = 0;
    public $approved_term = 0;
    public $take_home = 0;
    public $firstInstallmentInterestAmount = 0;
    public $totalCharges = 0;
    public $totalInsurance = 0;
    public $topUpAmount = 0;
    public $penaltyAmount = 0;
    public $restructureAmount = 0;
    public $collateral_value = 0;
    public $interestRate = 0;
    public $monthlyPayment = 0;
    public $totalToRepay = 0;
    public $creditScore = 500;
    public $creditScoreGrade = 'E';
    public $creditScoreRisk = 'Very High Risk - No Data';
    public $age = 0;
    public $monthsToRetirement = 0;
    public $retirementAge = 60;
    public $chargesBreakdown = [];
    public $insuranceBreakdown = [];
    public $settlementData = [];
    public $nbcLoansData = [];

    protected $listeners = [
        'refreshTabledAssessment' => '$refresh',
        'assessmentDataUpdated' => 'loadAssessmentData',
        'syncAssessmentData' => 'syncFromParent',
        'parentDataUpdated' => 'handleParentDataUpdate'
    ];

    public function mount()
    {
        $this->loadAssessmentData();
    }

    public function syncFromParent($data)
    {
        // Sync data from parent Assessment component
        if (isset($data['approved_loan_value'])) {
            $this->approved_loan_value = $data['approved_loan_value'];
        }
        if (isset($data['approved_term'])) {
            $this->approved_term = $data['approved_term'];
        }
        if (isset($data['take_home'])) {
            $this->take_home = $data['take_home'];
        }
        if (isset($data['totalCharges'])) {
            $this->totalCharges = $data['totalCharges'];
        }
        if (isset($data['totalInsurance'])) {
            $this->totalInsurance = $data['totalInsurance'];
        }
        if (isset($data['firstInstallmentInterestAmount'])) {
            $this->firstInstallmentInterestAmount = $data['firstInstallmentInterestAmount'];
        }
        if (isset($data['topUpAmount'])) {
            $this->topUpAmount = $data['topUpAmount'];
        }
        if (isset($data['penaltyAmount'])) {
            $this->penaltyAmount = $data['penaltyAmount'];
        }
        if (isset($data['chargesBreakdown'])) {
            $this->chargesBreakdown = $data['chargesBreakdown'];
        }
        if (isset($data['insuranceBreakdown'])) {
            $this->insuranceBreakdown = $data['insuranceBreakdown'];
        }
        if (isset($data['settlementData'])) {
            $this->settlementData = $data['settlementData'];
        }
        if (isset($data['nbcLoansData'])) {
            $this->nbcLoansData = $data['nbcLoansData'];
        }

        // Recalculate financial parameters
        $this->calculateFinancialParameters();
    }

    public function handleParentDataUpdate()
    {
        // Trigger a refresh when parent data is updated
        $this->loadAssessmentData();
    }

    public function loadAssessmentData()
    {
        // Initialize all variables to prevent undefined variable errors
        $this->initializeVariables();
        
        $loanID = session('currentloanID');
        if (!$loanID) {
            return;
        }

        // Load basic loan data
        $this->loan = DB::table('loans')->find($loanID);
        if (!$this->loan) {
            return;
        }

        // Load member data
        $this->member = DB::table('clients')->where('client_number', $this->loan->client_number)->first();
        
        // Load product data
        $this->product = DB::table('loan_sub_products')->where('sub_product_id', $this->loan->loan_sub_product)->first();

        // Load savings
        $this->savings = DB::table('accounts')
            ->where('product_number', '20000')
            ->where('client_number', $this->loan->client_number)
            ->sum('balance');

        // Calculate age and retirement data
        $this->calculateAgeAndRetirement();

        // Load assessment data from parent component
        $this->loadParentAssessmentData();

        // Load top-up data if applicable
        if ($this->loan->loan_type_2 == 'Top-up') {
            $this->loadTopUpData();
        }

        // Load restructure data if applicable
        if ($this->loan->loan_type_2 == 'Restructure') {
            $this->loadRestructureData();
        }

        // Load collateral data
        $this->loadCollateralData();

        // Load charges and insurance data
        $this->loadChargesAndInsuranceData();

        // Calculate financial parameters
        $this->calculateFinancialParameters();
    }

    private function initializeVariables()
    {
        // Initialize all public variables to prevent undefined variable errors
        $this->loan = null;
        $this->member = null;
        $this->product = null;
        $this->assessmentData = [];
        $this->topUpData = [];
        $this->restructureData = [];
        $this->collateralData = [];
        $this->chargesData = [];
        $this->insuranceData = [];
        $this->scheduleData = [];
        $this->savings = 0;
        $this->activeLoans = [];
        $this->totalDeductions = 0;
        $this->netDisbursement = 0;
        $this->approved_loan_value = 0;
        $this->approved_term = 0;
        $this->take_home = 0;
        $this->firstInstallmentInterestAmount = 0;
        $this->totalCharges = 0;
        $this->totalInsurance = 0;
        $this->topUpAmount = 0;
        $this->penaltyAmount = 0;
        $this->restructureAmount = 0;
        $this->collateral_value = 0;
        $this->interestRate = 0;
        $this->monthlyPayment = 0;
        $this->totalToRepay = 0;
        $this->creditScore = 500;
        $this->creditScoreGrade = 'E';
        $this->creditScoreRisk = 'Very High Risk - No Data';
        $this->age = 0;
        $this->monthsToRetirement = 0;
        $this->retirementAge = 60;
        $this->chargesBreakdown = [];
        $this->insuranceBreakdown = [];
        $this->settlementData = [];
        $this->nbcLoansData = [];
    }

    private function calculateAgeAndRetirement()
    {
        if ($this->member && $this->member->date_of_birth) {
            $dob = Carbon::parse($this->member->date_of_birth);
            $this->age = $dob->age;
            
            $yearsToRetirement = $this->retirementAge - $this->age;
            $yearsToRetirement = max(0, $yearsToRetirement);
            
            $retirementDate = $dob->copy()->addYears($this->retirementAge);
            $lastDayOfMonth = $retirementDate->endOfMonth()->day;
            $retirementDay = $lastDayOfMonth == 31 ? 31 : 30;
            $retirementDate->day = $retirementDay;
            
            $this->monthsToRetirement = now()->diffInMonths($retirementDate);
        }
    }

    private function loadParentAssessmentData()
    {
        // These would typically come from the parent Assessment component
        // For now, we'll use the loan data directly
        $this->approved_loan_value = $this->loan->approved_loan_value ?? 0;
        $this->approved_term = $this->loan->approved_term ?? 12;
        $this->take_home = $this->loan->take_home ?? 0;
        $this->collateral_value = $this->loan->collateral_value ?? 0;
        $this->interestRate = $this->product->interest_value ?? 0;
        
        // Calculate monthly payment
        if ($this->approved_loan_value > 0 && $this->approved_term > 0) {
            $monthlyInterestRate = ($this->interestRate / 100) / 12;
            if ($monthlyInterestRate > 0) {
                $this->monthlyPayment = ($this->approved_loan_value * $monthlyInterestRate * pow(1 + $monthlyInterestRate, $this->approved_term)) / 
                                       (pow(1 + $monthlyInterestRate, $this->approved_term) - 1);
            } else {
                $this->monthlyPayment = $this->approved_loan_value / $this->approved_term;
            }
        }

        // Calculate total to repay
        $this->totalToRepay = $this->monthlyPayment * $this->approved_term;
    }

    private function loadTopUpData()
    {
        if ($this->loan->selectedLoan) {
            $topupLoan = DB::table('loans')->where('id', $this->loan->selectedLoan)->first();
            if ($topupLoan && $topupLoan->loan_account_number) {
                $loanAccount = DB::table('accounts')->where('account_number', $topupLoan->loan_account_number)->first();
                $this->topUpAmount = abs($loanAccount->balance ?? 0);
                
                // Calculate penalty
                $disbursementDate = $topupLoan->disbursement_date ?? $topupLoan->created_at;
                $loanAge = Carbon::parse($disbursementDate)->diffInMonths(now());
                $productPenaltyValue = $this->product->penalty_value ?? 0;
                
                if ($loanAge < 6 && $productPenaltyValue > 0) {
                    $this->penaltyAmount = ($this->topUpAmount * $productPenaltyValue) / 100;
                }
            }
        }
    }

    private function loadRestructureData()
    {
        // Load restructure data
        $this->restructureAmount = $this->loan->restructure_amount ?? 0;
    }

    private function loadCollateralData()
    {
        $this->collateralData = DB::table('collaterals')
            ->where('loan_id', $this->loan->id)
            ->get()
            ->toArray();
    }

    private function loadChargesAndInsuranceData()
    {
        // Load charges data
        $this->chargesData = DB::table('loan_charges')
            ->where('loan_id', $this->loan->id)
            ->get()
            ->toArray();

        // Load insurance data
        $this->insuranceData = DB::table('loan_insurance')
            ->where('loan_id', $this->loan->id)
            ->get()
            ->toArray();

        // Calculate totals
        $this->totalCharges = collect($this->chargesData)->sum('amount');
        $this->totalInsurance = collect($this->insuranceData)->sum('amount');

        // Calculate first installment interest
        $this->calculateFirstInterest();
    }

    private function calculateFirstInterest()
    {
        if ($this->approved_loan_value > 0 && $this->interestRate > 0) {
            $monthlyInterestRate = ($this->interestRate / 100) / 12;
            $member_category = $this->member->member_category ?? 1;
            $dayOfMonth = DB::table('member_categories')->where('id', $member_category)->value('repayment_date') ?? 15;
            
            $disbursementDate = now();
            $nextDrawdownDate = clone $disbursementDate;
            $nextDrawdownDate->setDate($disbursementDate->format('Y'), $disbursementDate->format('m'), $dayOfMonth);
            
            if ($disbursementDate > $nextDrawdownDate) {
                $nextDrawdownDate->modify('first day of next month');
                $nextDrawdownDate->setDate($nextDrawdownDate->format('Y'), $nextDrawdownDate->format('m'), $dayOfMonth);
            }
            
            $daysBetween = $disbursementDate->diff($nextDrawdownDate)->days;
            $daysInMonth = (int) $disbursementDate->format('t');
            $dailyInterestRate = $monthlyInterestRate / $daysInMonth;
            
            $this->firstInstallmentInterestAmount = $this->approved_loan_value * $dailyInterestRate * $daysBetween;
        }
    }

    private function calculateFinancialParameters()
    {
        // Calculate total deductions
        $this->totalDeductions = $this->firstInstallmentInterestAmount + 
                                $this->totalCharges + 
                                $this->totalInsurance + 
                                $this->topUpAmount + 
                                $this->penaltyAmount;

        // Calculate net disbursement
        $this->netDisbursement = $this->approved_loan_value - $this->totalDeductions;
    }

    public function exportTabledAssessment()
    {
        // Method to export the tabulated assessment as PDF or Excel
        // This can be implemented later
        $this->emit('showMessage', 'Export functionality will be implemented soon.');
    }

    public function render()
    {
        return view('livewire.loans.sections.tabled-assessment');
    }
}
