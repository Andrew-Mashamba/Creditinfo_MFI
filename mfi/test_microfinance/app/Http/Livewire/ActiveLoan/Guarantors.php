<?php

namespace App\Http\Livewire\ActiveLoan;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Guarantors extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    // Guarantor details
    public $selectedLoan = null;
    public $guarantorType = 'individual'; // individual, group, collateral
    public $guarantorName = '';
    public $guarantorIdNumber = '';
    public $guarantorPhone = '';
    public $guarantorEmail = '';
    public $guarantorAddress = '';
    public $guarantorRelationship = '';
    public $guarantorOccupation = '';
    public $guarantorIncome = '';
    public $guaranteedAmount = '';
    public $guarantorNotes = '';
    
    // Collateral details
    public $collateralType = '';
    public $collateralDescription = '';
    public $collateralValue = '';
    public $collateralLocation = '';
    public $valuationDate = '';
    public $valuationReport = null;
    
    // Group guarantor
    public $groupName = '';
    public $groupRegNumber = '';
    public $groupMembers = [];
    
    // Search and filters
    public $searchTerm = '';
    public $statusFilter = 'all';
    public $typeFilter = 'all';
    
    // Modal states
    public $showAddGuarantorModal = false;
    public $showDetailsModal = false;
    public $showReleaseModal = false;
    
    // Selected items
    public $selectedGuarantor = null;
    public $releaseReason = '';

    protected $rules = [
        'guarantorType' => 'required|in:individual,group,collateral',
        'guarantorName' => 'required_if:guarantorType,individual|string|min:3',
        'guarantorIdNumber' => 'required_if:guarantorType,individual|string',
        'guarantorPhone' => 'required_if:guarantorType,individual|string',
        'guarantorEmail' => 'nullable|email',
        'guarantorAddress' => 'required_if:guarantorType,individual|string',
        'guarantorRelationship' => 'required_if:guarantorType,individual|string',
        'guaranteedAmount' => 'required|numeric|min:1',
        'collateralType' => 'required_if:guarantorType,collateral|string',
        'collateralDescription' => 'required_if:guarantorType,collateral|string',
        'collateralValue' => 'required_if:guarantorType,collateral|numeric|min:1',
        'collateralLocation' => 'required_if:guarantorType,collateral|string',
        'groupName' => 'required_if:guarantorType,group|string',
        'groupRegNumber' => 'required_if:guarantorType,group|string'
    ];

    protected $listeners = [
        'refreshGuarantors' => '$refresh'
    ];

    public function mount()
    {
        $this->valuationDate = now()->format('Y-m-d');
    }

    public function getLoansWithGuarantorsProperty()
    {
        return LoansModel::with(['client'])
            ->whereIn('loan_status', ['ACTIVE', 'OVERDUE'])
            ->whereHas('guarantors')
            ->when($this->searchTerm, function($query) {
                $query->where(function($q) {
                    $q->where('loan_id', 'like', "%{$this->searchTerm}%")
                      ->orWhereHas('client', function($subQ) {
                          $subQ->where('first_name', 'like', "%{$this->searchTerm}%")
                               ->orWhere('last_name', 'like', "%{$this->searchTerm}%")
                               ->orWhere('client_number', 'like', "%{$this->searchTerm}%");
                      });
                });
            })
            ->paginate(10);
    }

    public function getLoansNeedingGuarantorsProperty()
    {
        return LoansModel::with(['client', 'product'])
            ->whereIn('loan_status', ['PENDING', 'APPROVED'])
            ->where('principle', '>', 5000000) // Loans above 5M need guarantors
            ->whereDoesntHave('guarantors')
            ->when($this->searchTerm, function($query) {
                $query->where(function($q) {
                    $q->where('loan_id', 'like', "%{$this->searchTerm}%")
                      ->orWhereHas('client', function($subQ) {
                          $subQ->where('first_name', 'like', "%{$this->searchTerm}%")
                               ->orWhere('last_name', 'like', "%{$this->searchTerm}%");
                      });
                });
            })
            ->paginate(10, ['*'], 'needingPage');
    }

    public function getAllGuarantorsProperty()
    {
        return DB::table('loan_guarantors')
            ->join('loans', 'loan_guarantors.loan_id', '=', 'loans.loan_id')
            ->join('clients', 'loans.client_number', '=', 'clients.client_number')
            ->select(
                'loan_guarantors.*',
                'loans.loan_id',
                'loans.principle',
                'loans.loan_status',
                DB::raw("CONCAT(clients.first_name, ' ', clients.last_name) as borrower_name"),
                'clients.client_number'
            )
            ->when($this->searchTerm, function($query) {
                $query->where(function($q) {
                    $q->where('loan_guarantors.guarantor_name', 'like', "%{$this->searchTerm}%")
                      ->orWhere('loan_guarantors.guarantor_id_number', 'like', "%{$this->searchTerm}%")
                      ->orWhere('loans.loan_id', 'like', "%{$this->searchTerm}%");
                });
            })
            ->when($this->statusFilter && $this->statusFilter !== 'all', function($query) {
                $query->where('loan_guarantors.status', $this->statusFilter);
            })
            ->when($this->typeFilter && $this->typeFilter !== 'all', function($query) {
                $query->where('loan_guarantors.guarantor_type', $this->typeFilter);
            })
            ->orderBy('loan_guarantors.created_at', 'desc')
            ->paginate(10, ['*'], 'allPage');
    }

    public function addGuarantor($loanId)
    {
        $this->selectedLoan = LoansModel::with(['client', 'product'])->find($loanId);
        
        if (!$this->selectedLoan) {
            session()->flash('error', 'Loan not found');
            return;
        }
        
        $this->resetGuarantorForm();
        $this->showAddGuarantorModal = true;
    }

    public function saveGuarantor()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            $guarantorData = [
                'loan_id' => $this->selectedLoan->loan_id,
                'guarantor_type' => $this->guarantorType,
                'guaranteed_amount' => $this->guaranteedAmount,
                'status' => 'active',
                'notes' => $this->guarantorNotes,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            if ($this->guarantorType === 'individual') {
                $guarantorData = array_merge($guarantorData, [
                    'guarantor_name' => $this->guarantorName,
                    'guarantor_id_number' => $this->guarantorIdNumber,
                    'guarantor_phone' => $this->guarantorPhone,
                    'guarantor_email' => $this->guarantorEmail,
                    'guarantor_address' => $this->guarantorAddress,
                    'relationship' => $this->guarantorRelationship,
                    'occupation' => $this->guarantorOccupation,
                    'monthly_income' => $this->guarantorIncome
                ]);
            } elseif ($this->guarantorType === 'collateral') {
                $guarantorData = array_merge($guarantorData, [
                    'collateral_type' => $this->collateralType,
                    'collateral_description' => $this->collateralDescription,
                    'collateral_value' => $this->collateralValue,
                    'collateral_location' => $this->collateralLocation,
                    'valuation_date' => $this->valuationDate
                ]);
                
                // Handle valuation report upload
                if ($this->valuationReport) {
                    $path = $this->valuationReport->store('guarantors/valuations', 'public');
                    $guarantorData['valuation_report_path'] = $path;
                }
            } elseif ($this->guarantorType === 'group') {
                $guarantorData = array_merge($guarantorData, [
                    'group_name' => $this->groupName,
                    'group_registration_number' => $this->groupRegNumber,
                    'group_members' => json_encode($this->groupMembers)
                ]);
            }
            
            DB::table('loan_guarantors')->insert($guarantorData);
            
            // Update loan guarantor status
            DB::table('loans')
                ->where('loan_id', $this->selectedLoan->loan_id)
                ->update([
                    'has_guarantor' => true,
                    'updated_at' => now()
                ]);
            
            // Log the action
            Log::info('Guarantor added', [
                'loan_id' => $this->selectedLoan->loan_id,
                'guarantor_type' => $this->guarantorType,
                'user' => Auth::user()->name
            ]);
            
            DB::commit();
            
            session()->flash('success', 'Guarantor added successfully');
            $this->closeAddGuarantorModal();
            $this->emit('refreshGuarantors');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to add guarantor', [
                'loan_id' => $this->selectedLoan->loan_id,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to add guarantor: ' . $e->getMessage());
        }
    }

    public function viewGuarantorDetails($guarantorId)
    {
        $this->selectedGuarantor = DB::table('loan_guarantors')
            ->where('id', $guarantorId)
            ->first();
        
        if (!$this->selectedGuarantor) {
            session()->flash('error', 'Guarantor not found');
            return;
        }
        
        $this->showDetailsModal = true;
    }

    public function initiateRelease($guarantorId)
    {
        $this->selectedGuarantor = DB::table('loan_guarantors')
            ->where('id', $guarantorId)
            ->first();
        
        if (!$this->selectedGuarantor) {
            session()->flash('error', 'Guarantor not found');
            return;
        }
        
        $this->showReleaseModal = true;
    }

    public function releaseGuarantor()
    {
        $this->validate([
            'releaseReason' => 'required|string|min:10'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Update guarantor status
            DB::table('loan_guarantors')
                ->where('id', $this->selectedGuarantor->id)
                ->update([
                    'status' => 'released',
                    'release_date' => now(),
                    'release_reason' => $this->releaseReason,
                    'released_by' => Auth::id(),
                    'updated_at' => now()
                ]);
            
            // Check if loan still has active guarantors
            $activeGuarantors = DB::table('loan_guarantors')
                ->where('loan_id', $this->selectedGuarantor->loan_id)
                ->where('status', 'active')
                ->count();
            
            if ($activeGuarantors == 0) {
                DB::table('loans')
                    ->where('loan_id', $this->selectedGuarantor->loan_id)
                    ->update([
                        'has_guarantor' => false,
                        'updated_at' => now()
                    ]);
            }
            
            DB::commit();
            
            session()->flash('success', 'Guarantor released successfully');
            $this->closeReleaseModal();
            $this->emit('refreshGuarantors');
            
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Failed to release guarantor: ' . $e->getMessage());
        }
    }

    public function addGroupMember()
    {
        $this->groupMembers[] = [
            'name' => '',
            'id_number' => '',
            'phone' => '',
            'share' => ''
        ];
    }

    public function removeGroupMember($index)
    {
        unset($this->groupMembers[$index]);
        $this->groupMembers = array_values($this->groupMembers);
    }

    public function exportGuarantors()
    {
        $guarantors = DB::table('loan_guarantors')
            ->join('loans', 'loan_guarantors.loan_id', '=', 'loans.loan_id')
            ->join('clients', 'loans.client_number', '=', 'clients.client_number')
            ->select(
                'loan_guarantors.*',
                'loans.loan_id',
                'loans.principle',
                DB::raw("CONCAT(clients.first_name, ' ', clients.last_name) as borrower_name")
            )
            ->get();
        
        $csv = "Loan ID,Borrower,Guarantor Type,Guarantor Name,ID Number,Phone,Guaranteed Amount,Status,Date Added\n";
        
        foreach ($guarantors as $guarantor) {
            $name = $guarantor->guarantor_name ?? $guarantor->group_name ?? 'Collateral';
            $idNumber = $guarantor->guarantor_id_number ?? $guarantor->group_registration_number ?? 'N/A';
            $phone = $guarantor->guarantor_phone ?? 'N/A';
            
            $csv .= "{$guarantor->loan_id},"
                  . "{$guarantor->borrower_name},"
                  . "{$guarantor->guarantor_type},"
                  . "{$name},"
                  . "{$idNumber},"
                  . "{$phone},"
                  . "{$guarantor->guaranteed_amount},"
                  . "{$guarantor->status},"
                  . "{$guarantor->created_at}\n";
        }
        
        return response()->streamDownload(function() use ($csv) {
            echo $csv;
        }, 'guarantors_' . now()->format('Y-m-d') . '.csv');
    }

    public function closeAddGuarantorModal()
    {
        $this->showAddGuarantorModal = false;
        $this->resetGuarantorForm();
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedGuarantor = null;
    }

    public function closeReleaseModal()
    {
        $this->showReleaseModal = false;
        $this->selectedGuarantor = null;
        $this->releaseReason = '';
    }

    private function resetGuarantorForm()
    {
        $this->guarantorType = 'individual';
        $this->guarantorName = '';
        $this->guarantorIdNumber = '';
        $this->guarantorPhone = '';
        $this->guarantorEmail = '';
        $this->guarantorAddress = '';
        $this->guarantorRelationship = '';
        $this->guarantorOccupation = '';
        $this->guarantorIncome = '';
        $this->guaranteedAmount = '';
        $this->guarantorNotes = '';
        $this->collateralType = '';
        $this->collateralDescription = '';
        $this->collateralValue = '';
        $this->collateralLocation = '';
        $this->valuationDate = now()->format('Y-m-d');
        $this->valuationReport = null;
        $this->groupName = '';
        $this->groupRegNumber = '';
        $this->groupMembers = [];
    }

    public function render()
    {
        return view('livewire.active-loan.guarantors', [
            'loansWithGuarantors' => $this->loansWithGuarantors,
            'loansNeedingGuarantors' => $this->loansNeedingGuarantors,
            'allGuarantors' => $this->allGuarantors
        ]);
    }
}
