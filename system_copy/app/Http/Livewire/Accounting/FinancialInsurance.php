<?php

namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Models\LoansModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use App\Services\BalanceSheetItemIntegrationService;

class FinancialInsurance extends Component
{
    use WithPagination, WithFileUploads;

    // View Control
    public $activeTab = 'overview';
    public $showCreateModal = false;
    public $showClaimModal = false;
    public $editMode = false;
    
    // Search and Filters
    public $search = '';
    public $typeFilter = 'all';
    public $statusFilter = 'all';
    public $dateFrom;
    public $dateTo;
    
    // Insurance Form Data
    public $insuranceId;
    public $insurance_type = '';
    public $policy_number = '';
    public $insurer_name = '';
    public $insurer_contact = '';
    public $coverage_type = '';
    public $coverage_amount = 0;
    public $premium_amount = 0;
    public $premium_frequency = 'monthly';
    public $policy_start_date;
    public $policy_end_date;
    public $insured_entity = '';
    public $insured_entity_id;
    public $beneficiary = '';
    public $deductible = 0;
    public $copayment_percentage = 0;
    public $status = 'active';
    public $notes = '';
    
    // Claim Form Data
    public $claimId;
    public $claim_insurance_id;
    public $claim_number = '';
    public $claim_date;
    public $claim_amount = 0;
    public $claim_reason = '';
    public $claim_status = 'pending';
    public $settlement_amount = 0;
    public $settlement_date;
    public $claim_notes = '';
    
    // Premium Payment Data
    public $payment_date;
    public $payment_amount = 0;
    public $payment_reference = '';
    public $payment_account_id;
    
    // File Uploads
    public $policy_document;
    public $claim_documents = [];
    
    // Account selection for proper flow
    public $parent_account_number; // Parent account to create insurance account under
    public $other_account_id; // The other account for double-entry (Cash/Bank - credit side)
    
    // Statistics
    public $totalPolicies = 0;
    public $activePolicies = 0;
    public $totalCoverage = 0;
    public $totalPremiumsPaid = 0;
    public $totalClaimsPaid = 0;
    public $claimsRatio = 0;
    public $upcomingRenewals = [];
    
    // Collections
    public $insuranceTypes = [];
    public $insurers = [];
    public $expenseAccounts = [];
    public $assetAccounts = [];
    public $bankAccounts = [];
    public $loans = [];
    public $members = [];
    
    // Insurance Types
    public $predefinedTypes = [
        'credit_life' => 'Credit Life Insurance',
        'loan_protection' => 'Loan Protection Insurance',
        'deposit_insurance' => 'Deposit Insurance',
        'property_insurance' => 'Property Insurance',
        'vehicle_insurance' => 'Vehicle Insurance',
        'fidelity_guarantee' => 'Fidelity Guarantee',
        'cash_in_transit' => 'Cash in Transit',
        'directors_liability' => 'Directors & Officers Liability',
        'professional_indemnity' => 'Professional Indemnity',
        'cyber_insurance' => 'Cyber Insurance',
        'business_interruption' => 'Business Interruption',
        'key_person' => 'Key Person Insurance'
    ];
    
    protected $rules = [
        'insurance_type' => 'required',
        'policy_number' => 'required|unique:financial_insurance,policy_number',
        'insurer_name' => 'required|min:3',
        'coverage_amount' => 'required|numeric|min:0',
        'premium_amount' => 'required|numeric|min:0',
        'premium_frequency' => 'required',
        'policy_start_date' => 'required|date',
        'policy_end_date' => 'required|date|after:policy_start_date',
        'insured_entity' => 'required',
        'policy_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
    ];
    
    protected $listeners = [
        'refreshInsurance' => 'loadStatistics',
        'deleteInsurance' => 'delete',
        'processClaim' => 'submitClaim',
        'renewPolicy' => 'renew',
    ];
    
    public function mount()
    {
        $this->initializeData();
        $this->loadStatistics();
        $this->policy_start_date = now()->format('Y-m-d');
        $this->policy_end_date = now()->addYear()->format('Y-m-d');
        $this->claim_date = now()->format('Y-m-d');
        $this->payment_date = now()->format('Y-m-d');
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo = now()->endOfYear()->format('Y-m-d');
    }
    
    public function initializeData()
    {
        // Load insurance types
        $this->insuranceTypes = collect($this->predefinedTypes);
        
        // Load insurers
        if (Schema::hasTable('insurance_companies')) {
            $this->insurers = DB::table('insurance_companies')
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        } else {
            $this->insurers = collect();
        }
        
        // Load expense accounts for premiums
        $this->expenseAccounts = AccountsModel::where('account_type', 'EXPENSE')
            ->where(function($query) {
                $query->where('account_name', 'like', '%insurance%')
                      ->orWhere('account_name', 'like', '%premium%');
            })
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
        
        // Load asset accounts for prepaid insurance
        $this->assetAccounts = AccountsModel::where('account_type', 'ASSET')
            ->where(function($query) {
                $query->where('account_name', 'like', '%prepaid%')
                      ->orWhere('account_name', 'like', '%insurance%');
            })
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
        
        // Load bank accounts
        $this->bankAccounts = AccountsModel::where(function($query) {
                $query->where('account_name', 'LIKE', '%BANK%')
                      ->orWhere('account_name', 'LIKE', '%CASH%')
                      ->orWhere('major_category_code', '1000');
            })
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
        
        // Load active loans for loan protection insurance
        $this->loans = LoansModel::where('status', 'ACTIVE')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Load members
        $this->members = DB::table('clients')
            ->where('status', 'ACTIVE')
            ->orderBy('first_name')
            ->get();
    }
    
    public function loadStatistics()
    {
        if (!Schema::hasTable('financial_insurance')) {
            $this->totalPolicies = 0;
            $this->activePolicies = 0;
            $this->totalCoverage = 0;
            $this->totalPremiumsPaid = 0;
            $this->totalClaimsPaid = 0;
            $this->claimsRatio = 0;
            $this->upcomingRenewals = [];
            return;
        }
        
        // Total and active policies
        $this->totalPolicies = DB::table('financial_insurance')->count();
        $this->activePolicies = DB::table('financial_insurance')
            ->where('status', 'active')
            ->where('policy_end_date', '>=', now())
            ->count();
        
        // Total coverage amount
        $this->totalCoverage = DB::table('financial_insurance')
            ->where('status', 'active')
            ->sum('coverage_amount') ?? 0;
        
        // Total premiums paid
        if (Schema::hasTable('insurance_premium_payments')) {
            $this->totalPremiumsPaid = DB::table('insurance_premium_payments')
                ->whereRaw('EXTRACT(YEAR FROM payment_date) = ?', [now()->year])
                ->sum('amount') ?? 0;
        } else {
            $this->totalPremiumsPaid = 0;
        }
        
        // Total claims paid
        if (Schema::hasTable('insurance_claims')) {
            $this->totalClaimsPaid = DB::table('insurance_claims')
                ->where('claim_status', 'paid')
                ->whereRaw('EXTRACT(YEAR FROM settlement_date) = ?', [now()->year])
                ->sum('settlement_amount') ?? 0;
        } else {
            $this->totalClaimsPaid = 0;
        }
        
        // Claims ratio
        if ($this->totalPremiumsPaid > 0) {
            $this->claimsRatio = ($this->totalClaimsPaid / $this->totalPremiumsPaid) * 100;
        }
        
        // Upcoming renewals (next 30 days)
        $this->upcomingRenewals = DB::table('financial_insurance')
            ->where('status', 'active')
            ->whereBetween('policy_end_date', [now(), now()->addDays(30)])
            ->orderBy('policy_end_date')
            ->limit(5)
            ->get();
    }
    
    public function openCreateModal()
    {
        $this->reset(['insuranceId', 'insurance_type', 'policy_number', 'insurer_name', 
                     'insurer_contact', 'coverage_type', 'coverage_amount', 'premium_amount',
                     'insured_entity', 'insured_entity_id', 'beneficiary', 'deductible',
                     'copayment_percentage', 'notes']);
        
        $this->editMode = false;
        $this->policy_start_date = now()->format('Y-m-d');
        $this->policy_end_date = now()->addYear()->format('Y-m-d');
        $this->premium_frequency = 'monthly';
        $this->status = 'active';
        $this->generatePolicyNumber();
        $this->showCreateModal = true;
    }
    
    public function generatePolicyNumber()
    {
        $prefix = 'FI';
        $year = date('Y');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        $this->policy_number = "$prefix-$year-$random";
    }
    
    public function save()
    {
        $this->validate();
        
        DB::beginTransaction();
        try {
            // Prepare data
            $data = [
                'insurance_type' => $this->insurance_type,
                'policy_number' => $this->policy_number,
                'insurer_name' => $this->insurer_name,
                'insurer_contact' => $this->insurer_contact,
                'coverage_type' => $this->coverage_type,
                'coverage_amount' => $this->coverage_amount,
                'premium_amount' => $this->premium_amount,
                'premium_frequency' => $this->premium_frequency,
                'policy_start_date' => $this->policy_start_date,
                'policy_end_date' => $this->policy_end_date,
                'insured_entity' => $this->insured_entity,
                'insured_entity_id' => $this->insured_entity_id,
                'beneficiary' => $this->beneficiary,
                'deductible' => $this->deductible,
                'copayment_percentage' => $this->copayment_percentage,
                'status' => $this->status,
                'notes' => $this->notes,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Calculate annual premium
            $annualPremium = $this->calculateAnnualPremium($this->premium_amount, $this->premium_frequency);
            $data['annual_premium'] = $annualPremium;
            
            // Handle file upload
            if ($this->policy_document) {
                $path = $this->policy_document->store('financial_insurance/policies', 'public');
                $data['policy_document'] = $path;
            }
            
            if ($this->editMode && $this->insuranceId) {
                // Update existing insurance
                unset($data['created_at'], $data['created_by']);
                DB::table('financial_insurance')
                    ->where('id', $this->insuranceId)
                    ->update($data);
                
                $message = 'Insurance policy updated successfully!';
            } else {
                // Create new insurance
                $insuranceId = DB::table('financial_insurance')->insertGetId($data);
                
                // Create premium schedule
                $this->createPremiumSchedule($insuranceId, $data);
                
                // Create initial GL entry for prepaid insurance if applicable
                if ($this->isPrepaidInsurance($data)) {
                    $this->createPrepaidGLEntry($insuranceId, $data);
                }

                // Use Balance Sheet Integration Service to create accounts and post to GL
                $integrationService = new BalanceSheetItemIntegrationService();
                
                try {
                    $insuranceObj = (object) array_merge($data, ['id' => $insuranceId]);
                    $integrationService->createFinancialInsuranceAccount(
                        $insuranceObj,
                        $this->parent_account_number,  // Parent account to create insurance account under
                        $this->other_account_id        // The other account for double-entry (Cash/Bank - credit side)
                    );
                    
                    Log::info('Financial insurance integrated with accounts table', [
                        'insurance_id' => $insuranceId,
                        'policy_number' => $data['policy_number'],
                        'coverage_amount' => $data['coverage_amount']
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to integrate financial insurance with accounts table: ' . $e->getMessage());
                }
                
                $message = 'Insurance policy created and integrated successfully!';
            }
            
            DB::commit();
            
            $this->showCreateModal = false;
            $this->loadStatistics();
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving financial insurance: ' . $e->getMessage());
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error saving insurance: ' . $e->getMessage()
            ]);
        }
    }
    
    private function calculateAnnualPremium($amount, $frequency)
    {
        switch ($frequency) {
            case 'monthly':
                return $amount * 12;
            case 'quarterly':
                return $amount * 4;
            case 'semi_annually':
                return $amount * 2;
            case 'annually':
                return $amount;
            default:
                return $amount * 12;
        }
    }
    
    private function isPrepaidInsurance($data)
    {
        // Check if premium is paid in advance (annually or semi-annually)
        return in_array($data['premium_frequency'], ['annually', 'semi_annually']);
    }
    
    private function createPremiumSchedule($insuranceId, $data)
    {
        if (!Schema::hasTable('insurance_premium_schedule')) {
            return;
        }
        
        $startDate = Carbon::parse($data['policy_start_date']);
        $endDate = Carbon::parse($data['policy_end_date']);
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            DB::table('insurance_premium_schedule')->insert([
                'insurance_id' => $insuranceId,
                'due_date' => $currentDate->format('Y-m-d'),
                'amount' => $data['premium_amount'],
                'status' => 'pending',
                'created_at' => now(),
            ]);
            
            // Move to next payment date based on frequency
            switch ($data['premium_frequency']) {
                case 'monthly':
                    $currentDate->addMonth();
                    break;
                case 'quarterly':
                    $currentDate->addQuarter();
                    break;
                case 'semi_annually':
                    $currentDate->addMonths(6);
                    break;
                case 'annually':
                    $currentDate->addYear();
                    break;
            }
        }
    }
    
    private function createPrepaidGLEntry($insuranceId, $data)
    {
        $reference = 'INS-' . $data['policy_number'];
        $description = $data['insurance_type'] . ' - ' . $data['insurer_name'];
        
        // Get prepaid insurance account
        $prepaidAccount = AccountsModel::where('account_name', 'like', '%prepaid%insurance%')
            ->where('account_type', 'ASSET')
            ->first();
        
        if (!$prepaidAccount) {
            return;
        }
        
        // Get insurance expense account
        $expenseAccount = AccountsModel::where('account_name', 'like', '%insurance%expense%')
            ->where('account_type', 'EXPENSE')
            ->first();
        
        if (!$expenseAccount) {
            return;
        }
        
        // Debit Prepaid Insurance
        general_ledger::create([
            'reference_number' => $reference,
            'transaction_type' => 'INSURANCE',
            'transaction_date' => $data['policy_start_date'],
            'account_id' => $prepaidAccount->id,
            'debit_amount' => $data['annual_premium'],
            'credit_amount' => 0,
            'description' => 'Prepaid ' . $description,
            'created_by' => auth()->id(),
            'status' => 'POSTED',
            'source_id' => $insuranceId,
            'source_type' => 'financial_insurance'
        ]);
    }
    
    public function openClaimModal($insuranceId)
    {
        $insurance = DB::table('financial_insurance')->find($insuranceId);
        
        if ($insurance) {
            $this->claim_insurance_id = $insuranceId;
            $this->claim_date = now()->format('Y-m-d');
            $this->claim_status = 'pending';
            $this->generateClaimNumber();
            $this->showClaimModal = true;
        }
    }
    
    public function generateClaimNumber()
    {
        $prefix = 'CLM';
        $year = date('Y');
        $month = date('m');
        
        if (Schema::hasTable('insurance_claims')) {
            $lastClaim = DB::table('insurance_claims')
                ->where('claim_number', 'like', "$prefix-$year$month-%")
                ->orderBy('claim_number', 'desc')
                ->first();
            
            if ($lastClaim) {
                $lastNumber = intval(substr($lastClaim->claim_number, -4));
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }
        } else {
            $newNumber = '0001';
        }
        
        $this->claim_number = "$prefix-$year$month-$newNumber";
    }
    
    public function submitClaim()
    {
        $this->validate([
            'claim_amount' => 'required|numeric|min:0',
            'claim_reason' => 'required|min:10',
            'claim_date' => 'required|date',
        ]);
        
        DB::beginTransaction();
        try {
            // Create claim record
            $claimId = DB::table('insurance_claims')->insertGetId([
                'insurance_id' => $this->claim_insurance_id,
                'claim_number' => $this->claim_number,
                'claim_date' => $this->claim_date,
                'claim_amount' => $this->claim_amount,
                'claim_reason' => $this->claim_reason,
                'claim_status' => $this->claim_status,
                'notes' => $this->claim_notes,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
            
            // Handle file uploads
            if ($this->claim_documents) {
                foreach ($this->claim_documents as $document) {
                    $path = $document->store('financial_insurance/claims', 'public');
                    DB::table('insurance_claim_documents')->insert([
                        'claim_id' => $claimId,
                        'document_path' => $path,
                        'uploaded_by' => auth()->id(),
                        'uploaded_at' => now(),
                    ]);
                }
            }
            
            DB::commit();
            
            $this->showClaimModal = false;
            $this->reset(['claim_amount', 'claim_reason', 'claim_notes', 'claim_documents']);
            $this->loadStatistics();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Insurance claim submitted successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error submitting claim: ' . $e->getMessage()
            ]);
        }
    }
    
    public function processPremiumPayment($insuranceId)
    {
        $insurance = DB::table('financial_insurance')->find($insuranceId);
        
        if (!$insurance) {
            return;
        }
        
        DB::beginTransaction();
        try {
            // Record payment
            $paymentId = DB::table('insurance_premium_payments')->insertGetId([
                'insurance_id' => $insuranceId,
                'payment_date' => $this->payment_date,
                'amount' => $this->payment_amount,
                'reference_number' => $this->payment_reference,
                'payment_account_id' => $this->payment_account_id,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
            
            // Create GL entries
            $this->createPremiumPaymentGLEntries($insurance, $paymentId);
            
            // Update premium schedule
            if (Schema::hasTable('insurance_premium_schedule')) {
                DB::table('insurance_premium_schedule')
                    ->where('insurance_id', $insuranceId)
                    ->where('due_date', '<=', $this->payment_date)
                    ->where('status', 'pending')
                    ->update(['status' => 'paid', 'payment_id' => $paymentId]);
            }
            
            DB::commit();
            
            $this->loadStatistics();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Premium payment processed successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error processing payment: ' . $e->getMessage()
            ]);
        }
    }
    
    private function createPremiumPaymentGLEntries($insurance, $paymentId)
    {
        $reference = 'INSPAY-' . str_pad($paymentId, 6, '0', STR_PAD_LEFT);
        $description = 'Premium payment for ' . $insurance->policy_number;
        
        // Get insurance expense account
        $expenseAccount = AccountsModel::where('account_name', 'like', '%insurance%')
            ->where('account_type', 'EXPENSE')
            ->first();
        
        if ($expenseAccount) {
            // Debit Insurance Expense
            general_ledger::create([
                'reference_number' => $reference,
                'transaction_type' => 'INSURANCE_PAYMENT',
                'transaction_date' => $this->payment_date,
                'account_id' => $expenseAccount->id,
                'debit_amount' => $this->payment_amount,
                'credit_amount' => 0,
                'description' => $description,
                'created_by' => auth()->id(),
                'status' => 'POSTED',
                'source_id' => $paymentId,
                'source_type' => 'insurance_premium_payments'
            ]);
            
            // Credit Bank Account
            general_ledger::create([
                'reference_number' => $reference,
                'transaction_type' => 'INSURANCE_PAYMENT',
                'transaction_date' => $this->payment_date,
                'account_id' => $this->payment_account_id,
                'debit_amount' => 0,
                'credit_amount' => $this->payment_amount,
                'description' => $description,
                'created_by' => auth()->id(),
                'status' => 'POSTED',
                'source_id' => $paymentId,
                'source_type' => 'insurance_premium_payments'
            ]);
        }
    }
    
    public function renew($id)
    {
        $insurance = DB::table('financial_insurance')->find($id);
        
        if ($insurance) {
            $this->insuranceId = null; // Force new record
            $this->insurance_type = $insurance->insurance_type;
            $this->insurer_name = $insurance->insurer_name;
            $this->insurer_contact = $insurance->insurer_contact;
            $this->coverage_type = $insurance->coverage_type;
            $this->coverage_amount = $insurance->coverage_amount;
            $this->premium_amount = $insurance->premium_amount;
            $this->premium_frequency = $insurance->premium_frequency;
            $this->policy_start_date = Carbon::parse($insurance->policy_end_date)->addDay()->format('Y-m-d');
            $this->policy_end_date = Carbon::parse($insurance->policy_end_date)->addYear()->format('Y-m-d');
            $this->insured_entity = $insurance->insured_entity;
            $this->insured_entity_id = $insurance->insured_entity_id;
            $this->beneficiary = $insurance->beneficiary;
            $this->deductible = $insurance->deductible;
            $this->copayment_percentage = $insurance->copayment_percentage;
            $this->notes = 'Renewal of policy ' . $insurance->policy_number;
            
            $this->generatePolicyNumber();
            $this->editMode = false;
            $this->showCreateModal = true;
        }
    }
    
    public function edit($id)
    {
        $insurance = DB::table('financial_insurance')->find($id);
        
        if ($insurance) {
            $this->insuranceId = $id;
            $this->insurance_type = $insurance->insurance_type;
            $this->policy_number = $insurance->policy_number;
            $this->insurer_name = $insurance->insurer_name;
            $this->insurer_contact = $insurance->insurer_contact;
            $this->coverage_type = $insurance->coverage_type;
            $this->coverage_amount = $insurance->coverage_amount;
            $this->premium_amount = $insurance->premium_amount;
            $this->premium_frequency = $insurance->premium_frequency;
            $this->policy_start_date = $insurance->policy_start_date;
            $this->policy_end_date = $insurance->policy_end_date;
            $this->insured_entity = $insurance->insured_entity;
            $this->insured_entity_id = $insurance->insured_entity_id;
            $this->beneficiary = $insurance->beneficiary;
            $this->deductible = $insurance->deductible;
            $this->copayment_percentage = $insurance->copayment_percentage;
            $this->status = $insurance->status;
            $this->notes = $insurance->notes;
            
            $this->editMode = true;
            $this->showCreateModal = true;
        }
    }
    
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            // Check for claims
            if (Schema::hasTable('insurance_claims')) {
                $hasClaims = DB::table('insurance_claims')
                    ->where('insurance_id', $id)
                    ->exists();
                
                if ($hasClaims) {
                    throw new \Exception('Cannot delete insurance with claims. Please cancel the policy instead.');
                }
            }
            
            // Delete related records
            if (Schema::hasTable('insurance_premium_schedule')) {
                DB::table('insurance_premium_schedule')
                    ->where('insurance_id', $id)
                    ->delete();
            }
            
            if (Schema::hasTable('insurance_premium_payments')) {
                DB::table('insurance_premium_payments')
                    ->where('insurance_id', $id)
                    ->delete();
            }
            
            // Delete GL entries
            general_ledger::where('source_type', 'financial_insurance')
                ->where('source_id', $id)
                ->delete();
            
            // Delete insurance
            DB::table('financial_insurance')->where('id', $id)->delete();
            
            DB::commit();
            
            $this->loadStatistics();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Insurance policy deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function render()
    {
        $insurancePolicies = collect();
        $claims = collect();
        
        if (Schema::hasTable('financial_insurance')) {
            $query = DB::table('financial_insurance')
                ->select([
                    'financial_insurance.*',
                    DB::raw("DATE_PART('day', policy_end_date - CURRENT_DATE) as days_to_expiry")
                ]);
            
            // Apply filters
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('policy_number', 'like', '%' . $this->search . '%')
                      ->orWhere('insurer_name', 'like', '%' . $this->search . '%')
                      ->orWhere('insured_entity', 'like', '%' . $this->search . '%');
                });
            }
            
            if ($this->typeFilter && $this->typeFilter !== 'all') {
                $query->where('insurance_type', $this->typeFilter);
            }
            
            if ($this->statusFilter && $this->statusFilter !== 'all') {
                $query->where('status', $this->statusFilter);
            }
            
            if ($this->dateFrom) {
                $query->where('policy_start_date', '>=', $this->dateFrom);
            }
            
            if ($this->dateTo) {
                $query->where('policy_end_date', '<=', $this->dateTo);
            }
            
            $insurancePolicies = $query->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            $insurancePolicies = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                10
            );
        }
        
        // Get recent claims
        if (Schema::hasTable('insurance_claims')) {
            $claims = DB::table('insurance_claims')
                ->join('financial_insurance', 'insurance_claims.insurance_id', '=', 'financial_insurance.id')
                ->select([
                    'insurance_claims.*',
                    'financial_insurance.policy_number',
                    'financial_insurance.insurer_name'
                ])
                ->orderBy('insurance_claims.created_at', 'desc')
                ->limit(5)
                ->get();
        }
        
        // Get accounts for account selection
        $parentAccounts = DB::table('accounts')
            ->where('major_category_code', '1000') // Asset accounts
            ->where('account_level', '<=', 2) // Parent level accounts only
            ->where(function($query) {
                $query->where('account_name', 'LIKE', '%PREPAID%')
                      ->orWhere('account_name', 'LIKE', '%INSURANCE%')
                      ->orWhere('account_name', 'LIKE', '%ASSET%');
            })
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
        
        $otherAccounts = DB::table('bank_accounts')
            
            


            
            ->select('internal_mirror_account_number', 'bank_name', 'account_number')
            ->where('status', 'ACTIVE')
            ->orderBy('bank_name')
            ->get();

        return view('livewire.accounting.financial-insurance', [
            'insurancePolicies' => $insurancePolicies,
            'recentClaims' => $claims,
            'parentAccounts' => $parentAccounts,
            'otherAccounts' => $otherAccounts
        ]);
    }
}