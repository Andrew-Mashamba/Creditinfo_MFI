<?php

namespace App\Http\Livewire\Accounting;

use App\Services\TransactionPostingService;
use App\Services\SystemReferenceNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class StandingInstruction extends Component
{
    use WithPagination;

    // Modal and UI state
    public $showModal = false;
    public $currentStep = 1;
    public $editMode = false;
    public $selectedInstructionId = null;
    
    // Search and filter
    public $search = '';
    public $statusFilter = '';
    public $frequencyFilter = '';
    
    // Step 1: Source Account
    public $member_id;
    public $member_search = '';
    public $source_account_id;
    public $source_account_number;
    public $source_account_type = 'member';
    public $source_bank_id;
    public $showMemberDropdown = false;
    public $members = [];
    
    // Step 2: Destination Account
    public $destination_type = 'member';
    public $destination_member_id;
    public $destination_member_search = '';
    public $destination_account_id;
    public $destination_account_number;
    public $destination_account_name;
    public $showDestMemberDropdown = false;
    public $destination_members = [];
    
    // Step 3: Schedule Details
    public $amount;
    public $frequency = 'monthly';
    public $start_date;
    public $end_date;
    public $day_of_month;
    public $day_of_week;
    public $description;
    public $reference_number;
    public $max_executions;
    
    // Statistics
    public $activeCount = 0;
    public $executedToday = 0;
    public $pendingCount = 0;
    public $failedCount = 0;
    
    protected $listeners = ['refreshComponent' => '$refresh'];
    
    protected function rules()
    {
        $rules = [];
        
        if ($this->currentStep == 1) {
            $rules = [
                'member_id' => 'required|exists:clients,id',
                'source_account_id' => 'required|exists:accounts,id',
            ];
        } elseif ($this->currentStep == 2) {
            $rules = [
                'destination_type' => 'required|in:member,internal',
                'destination_account_id' => 'required|exists:accounts,id',
            ];
        } elseif ($this->currentStep == 3) {
            $rules = [
                'amount' => 'required|numeric|min:0.01',
                'frequency' => 'required|in:daily,weekly,monthly,quarterly,annually',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'nullable|date|after:start_date',
                'description' => 'required|string|max:255',
            ];
        }
        
        return $rules;
    }


    public function mount()
    {
        $this->showModal = false;
        $this->loadStatistics();
        $this->start_date = Carbon::now()->format('Y-m-d');
    }
    
    public function loadStatistics()
    {
        $this->activeCount = DB::table('standing_instructions')
            ->where('status', 'ACTIVE')
            ->count();
            
        $this->executedToday = DB::table('standing_instructions_executions')
            ->whereDate('executed_at', Carbon::today())
            ->where('status', 'SUCCESS')
            ->count();
            
        $this->pendingCount = DB::table('standing_instructions')
            ->where('status', 'PENDING')
            ->count();
            
        $this->failedCount = DB::table('standing_instructions_executions')
            ->whereDate('executed_at', Carbon::today())
            ->where('status', 'FAILED')
            ->count();
    }
    
    public function updatedMemberSearch($value)
    {
        if (strlen($value) >= 2) {
            $this->showMemberDropdown = true;
            $this->members = DB::table('clients')
                ->where(function($query) use ($value) {
                    $query->where('first_name', 'like', '%' . $value . '%')
                        ->orWhere('middle_name', 'like', '%' . $value . '%')
                        ->orWhere('last_name', 'like', '%' . $value . '%')
                        ->orWhere('client_number', 'like', '%' . $value . '%');
                })
                ->where('client_status', 'ACTIVE')
                ->limit(10)
                ->get();
        } else {
            $this->showMemberDropdown = false;
            $this->members = [];
        }
    }
    
    public function selectMember($memberId)
    {
        $member = DB::table('clients')->find($memberId);
        if ($member) {
            $this->member_id = $memberId;
            $this->member_search = $member->first_name . ' ' . $member->middle_name . ' ' . $member->last_name;
            $this->showMemberDropdown = false;
            $this->loadMemberAccounts();
        }
    }
    
    public function loadMemberAccounts()
    {
        if ($this->member_id) {
            // Load member's accounts for selection
            $accounts = DB::table('accounts')
                ->where('member_number', $this->member_id)
                ->where('status', 'ACTIVE')
                ->get();
                
            if ($accounts->count() == 1) {
                // Auto-select if only one account
                $this->source_account_id = $accounts->first()->id;
                $this->source_account_number = $accounts->first()->account_number;
            }
        }
    }
    
    public function updatedDestinationMemberSearch($value)
    {
        if (strlen($value) >= 2 && $this->destination_type == 'member') {
            $this->showDestMemberDropdown = true;
            $this->destination_members = DB::table('clients')
                ->where(function($query) use ($value) {
                    $query->where('first_name', 'like', '%' . $value . '%')
                        ->orWhere('middle_name', 'like', '%' . $value . '%')
                        ->orWhere('last_name', 'like', '%' . $value . '%')
                        ->orWhere('client_number', 'like', '%' . $value . '%');
                })
                ->where('client_status', 'ACTIVE')
                ->where('id', '!=', $this->member_id) // Exclude source member
                ->limit(10)
                ->get();
        } else {
            $this->showDestMemberDropdown = false;
            $this->destination_members = [];
        }
    }
    
    public function selectDestinationMember($memberId)
    {
        $member = DB::table('clients')->find($memberId);
        if ($member) {
            $this->destination_member_id = $memberId;
            $this->destination_member_search = $member->first_name . ' ' . $member->middle_name . ' ' . $member->last_name;
            $this->showDestMemberDropdown = false;
            $this->loadDestinationMemberAccounts();
        }
    }
    
    public function loadDestinationMemberAccounts()
    {
        if ($this->destination_member_id) {
            $accounts = DB::table('accounts')
                ->where('member_number', $this->destination_member_id)
                ->where('status', 'ACTIVE')
                ->get();
                
            if ($accounts->count() == 1) {
                $this->destination_account_id = $accounts->first()->id;
                $this->destination_account_number = $accounts->first()->account_number;
                $this->destination_account_name = $accounts->first()->account_name;
            }
        }
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->currentStep = 1;
        $this->editMode = false;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }
    
    public function resetForm()
    {
        $this->reset([
            'member_id', 'member_search', 'source_account_id', 'source_account_number',
            'destination_type', 'destination_member_id', 'destination_member_search',
            'destination_account_id', 'destination_account_number', 'destination_account_name',
            'amount', 'frequency', 'start_date', 'end_date', 'day_of_month', 'day_of_week',
            'description', 'reference_number', 'showMemberDropdown', 'showDestMemberDropdown',
            'members', 'destination_members', 'currentStep', 'editMode', 'selectedInstructionId'
        ]);
        
        $this->start_date = Carbon::now()->format('Y-m-d');
    }
    
    public function nextStep()
    {
        $this->validate();
        
        if ($this->currentStep < 4) {
            $this->currentStep++;
            
            // Generate reference number when reaching review step
            if ($this->currentStep == 4 && !$this->reference_number) {
                $this->generateReferenceNumber();
            }
        }
    }
    
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }
    
    public function generateReferenceNumber()
    {
        $refService = new SystemReferenceNumberService();
        $member = DB::table('clients')->find($this->member_id);
        
        if ($member) {
            $this->reference_number = $refService->generateReferenceNumber(
                1, // Organization ID
                $member->client_number,
                'STANDING_ORDER',
                rand(100000, 999999)
            );
        }
    }

    public function save()
    {
        // Validate all steps
        $this->currentStep = 1;
        $this->validate();
        $this->currentStep = 2;
        $this->validate();
        $this->currentStep = 3;
        $this->validate();
        $this->currentStep = 4;
        
        try {
            DB::beginTransaction();
            
            $data = [
                'member_id' => $this->member_id,
                'source_account_id' => $this->source_account_id,
                'source_account_number' => $this->source_account_number,
                'destination_type' => $this->destination_type,
                'destination_account_id' => $this->destination_account_id,
                'destination_account_number' => $this->destination_account_number,
                'destination_account_name' => $this->destination_account_name,
                'amount' => $this->amount,
                'frequency' => $this->frequency,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'day_of_month' => $this->day_of_month,
                'day_of_week' => $this->day_of_week,
                'description' => $this->description,
                'reference_number' => $this->reference_number,
                'status' => 'PENDING',
                'next_execution_date' => $this->calculateNextExecutionDate(),
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            if ($this->editMode && $this->selectedInstructionId) {
                DB::table('standing_instructions')
                    ->where('id', $this->selectedInstructionId)
                    ->update($data);
                    
                session()->flash('message', 'Standing instruction updated successfully!');
            } else {
                DB::table('standing_instructions')->insert($data);
                session()->flash('message', 'Standing instruction created successfully!');
            }
            
            DB::commit();
            
            $this->closeModal();
            $this->loadStatistics();
            $this->emit('refreshComponent');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save standing instruction: ' . $e->getMessage());
            session()->flash('error', 'Failed to save standing instruction. Please try again.');
        }
    }
    
    private function calculateNextExecutionDate()
    {
        $startDate = Carbon::parse($this->start_date);
        $today = Carbon::today();
        
        if ($startDate->isFuture()) {
            return $startDate->format('Y-m-d');
        }
        
        switch ($this->frequency) {
            case 'daily':
                return $today->addDay()->format('Y-m-d');
            case 'weekly':
                return $today->next($this->day_of_week ?? Carbon::MONDAY)->format('Y-m-d');
            case 'monthly':
                $nextDate = $today->copy()->day($this->day_of_month ?? 1);
                if ($nextDate->isPast()) {
                    $nextDate->addMonth();
                }
                return $nextDate->format('Y-m-d');
            case 'quarterly':
                return $today->addQuarter()->format('Y-m-d');
            case 'annually':
                return $today->addYear()->format('Y-m-d');
            default:
                return $startDate->format('Y-m-d');
        }
    }



    public function editInstruction($id)
    {
        $instruction = DB::table('standing_instructions')->find($id);
        
        if ($instruction) {
            $this->selectedInstructionId = $id;
            $this->editMode = true;
            
            // Load source member
            $this->member_id = $instruction->member_id;
            $member = DB::table('clients')->find($this->member_id);
            if ($member) {
                $this->member_search = $member->first_name . ' ' . $member->middle_name . ' ' . $member->last_name;
            }
            
            // Load source account
            $this->source_account_id = $instruction->source_account_id;
            $this->source_account_number = $instruction->source_account_number;
            
            // Load destination
            $this->destination_type = $instruction->destination_type;
            $this->destination_account_id = $instruction->destination_account_id;
            $this->destination_account_number = $instruction->destination_account_number;
            $this->destination_account_name = $instruction->destination_account_name;
            
            if ($instruction->destination_type == 'member') {
                $destAccount = DB::table('accounts')->find($instruction->destination_account_id);
                if ($destAccount) {
                    $this->destination_member_id = $destAccount->member_number;
                    $destMember = DB::table('clients')->find($this->destination_member_id);
                    if ($destMember) {
                        $this->destination_member_search = $destMember->first_name . ' ' . $destMember->middle_name . ' ' . $destMember->last_name;
                    }
                }
            }
            
            // Load schedule details
            $this->amount = $instruction->amount;
            $this->frequency = $instruction->frequency;
            $this->start_date = $instruction->start_date;
            $this->end_date = $instruction->end_date;
            $this->day_of_month = $instruction->day_of_month;
            $this->day_of_week = $instruction->day_of_week;
            $this->description = $instruction->description;
            $this->reference_number = $instruction->reference_number;
            
            $this->showModal = true;
            $this->currentStep = 1;
        }
    }
    
    public function toggleStatus($id)
    {
        $instruction = DB::table('standing_instructions')->find($id);
        
        if ($instruction) {
            $newStatus = $instruction->status == 'ACTIVE' ? 'SUSPENDED' : 'ACTIVE';
            
            DB::table('standing_instructions')
                ->where('id', $id)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);
            
            session()->flash('message', 'Standing instruction status updated successfully!');
            $this->loadStatistics();
            $this->emit('refreshComponent');
        }
    }
    
    public function deleteInstruction($id)
    {
        try {
            DB::table('standing_instructions')
                ->where('id', $id)
                ->update([
                    'status' => 'DELETED',
                    'deleted_at' => now(),
                ]);
            
            session()->flash('message', 'Standing instruction deleted successfully!');
            $this->loadStatistics();
            $this->emit('refreshComponent');
            
        } catch (\Exception $e) {
            Log::error('Failed to delete standing instruction: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete standing instruction.');
        }
    }
    
    public function executeNow($id)
    {
        try {
            $instruction = DB::table('standing_instructions')->find($id);
            
            if (!$instruction) {
                throw new \Exception('Standing instruction not found');
            }
            
            // Use TransactionPostingService to execute the transaction
            $postingService = new TransactionPostingService();
            
            $result = $postingService->processTransaction(
                $instruction->source_account_number,
                $instruction->destination_account_number,
                $instruction->amount,
                $instruction->description . ' - Manual Execution',
                $instruction->reference_number . '-MANUAL-' . time(),
                'STANDING_ORDER'
            );
            
            // Log the execution
            DB::table('standing_instructions_executions')->insert([
                'standing_instruction_id' => $id,
                'executed_at' => now(),
                'amount' => $instruction->amount,
                'status' => $result['success'] ? 'SUCCESS' : 'FAILED',
                'transaction_reference' => $result['reference'] ?? null,
                'error_message' => $result['error'] ?? null,
                'created_at' => now(),
            ]);
            
            if ($result['success']) {
                session()->flash('message', 'Standing instruction executed successfully!');
            } else {
                session()->flash('error', 'Execution failed: ' . ($result['error'] ?? 'Unknown error'));
            }
            
            $this->loadStatistics();
            $this->emit('refreshComponent');
            
        } catch (\Exception $e) {
            Log::error('Failed to execute standing instruction: ' . $e->getMessage());
            session()->flash('error', 'Failed to execute: ' . $e->getMessage());
        }
    }




    public function getInternalAccounts()
    {
        return DB::table('accounts')
            ->whereIn('category_code', [1000, 2000, 3000, 4000, 5000])
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
    }
    
    public function getMemberAccounts($memberId)
    {
        return DB::table('accounts')
            ->where('member_number', $memberId)
            ->where('status', 'ACTIVE')
            ->get();
    }
    
    // Computed properties for selected accounts
    public function getSourceAccountProperty()
    {
        if ($this->source_account_id) {
            return DB::table('accounts')->find($this->source_account_id);
        }
        return null;
    }
    
    public function getDestinationAccountProperty()
    {
        if ($this->destination_account_id) {
            return DB::table('accounts')->find($this->destination_account_id);
        }
        return null;
    }
    
    public function render()
    {
        $query = DB::table('standing_instructions as si')
            ->leftJoin('clients as c', 'si.member_id', '=', 'c.id')
            ->leftJoin('accounts as sa', 'si.source_account_id', '=', 'sa.id')
            ->leftJoin('accounts as da', 'si.destination_account_id', '=', 'da.id')
            ->select(
                'si.*',
                DB::raw("CONCAT(c.first_name, ' ', c.middle_name, ' ', c.last_name) as member_name"),
                'sa.account_name as source_account_name',
                'da.account_name as dest_account_name'
            )
            ->whereNull('si.deleted_at');
        
        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('si.reference_number', 'like', '%' . $this->search . '%')
                    ->orWhere('si.description', 'like', '%' . $this->search . '%')
                    ->orWhere('c.first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('c.last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('si.source_account_number', 'like', '%' . $this->search . '%')
                    ->orWhere('si.destination_account_number', 'like', '%' . $this->search . '%');
            });
        }
        
        // Apply status filter
        if ($this->statusFilter) {
            $query->where('si.status', $this->statusFilter);
        }
        
        // Apply frequency filter
        if ($this->frequencyFilter) {
            $query->where('si.frequency', $this->frequencyFilter);
        }
        
        $instructions = $query->orderBy('si.created_at', 'desc')->paginate(10);
        
        return view('livewire.accounting.standing-instruction', [
            'instructions' => $instructions,
            'internalAccounts' => $this->destination_type == 'internal' ? $this->getInternalAccounts() : [],
            'sourceAccounts' => $this->member_id ? $this->getMemberAccounts($this->member_id) : [],
            'destinationAccounts' => $this->destination_member_id ? $this->getMemberAccounts($this->destination_member_id) : [],
            'banks' => DB::table('banks')->get(),
        ]);
    }
}
