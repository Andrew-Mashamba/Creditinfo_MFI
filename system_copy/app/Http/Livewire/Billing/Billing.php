<?php

namespace App\Http\Livewire\Billing;

use Livewire\Component;
use App\Models\ClientsModel;
use App\Models\Service;
use App\Models\Bill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;
use App\Traits\Livewire\WithModulePermissions;

class Billing extends Component
{
    use WithPagination, WithModulePermissions;

    // Properties for form inputs
    public $client_number;
    public $service_id;
    public $amount;
    public $is_recurring = 1;
    public $payment_mode = 1;
    public $due_date;
    public $is_mandatory = false;

    // Properties for display
    public $clients = [];
    public $services = [];
    public $selectedBill = null;
    public $searchControlNumber = '';
    public $searchResults = null;

    // Properties for filtering and sorting
    public $search = '';
    public $status = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    // Properties for bill management
    public $showDeleteModal = false;
    public $billToDelete = null;
    public $showPauseModal = false;
    public $billToPause = null;
    public $pauseReason = '';

    // Navigation property
    public $selectedMenuItem = 1; // Default to Dashboard Overview

    // Validation rules
    protected $rules = [
        'client_number' => 'required|exists:clients,client_number',
        'service_id' => 'required|exists:services,id',
        'amount' => 'required|numeric|min:0',
        'is_recurring' => 'required|in:1,2',
        'payment_mode' => 'required|in:1,2,3,4,5',
        'due_date' => 'required|date|after:today',
        'is_mandatory' => 'boolean'
    ];

    protected $listeners = ['refreshBills' => '$refresh'];

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        $this->loadInitialData();
    }
    
    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'billing';
    }

    public function loadInitialData()
    {
        $this->services = Service::all();
        $this->loadClients();
    }

    public function loadClients()
    {
        $this->clients = ClientsModel::select([
            'id',
            'client_number',
            'first_name',
            'middle_name',
            'last_name',
            'business_name',
            'mobile_phone_number',
            'membership_type'
        ])->get()->map(function ($client) {
            return [
                'id' => $client->id,
                'client_number' => $client->client_number,
                'mobile_phone_number' => $client->mobile_phone_number,
                'full_name' => $client->full_name
            ];
        })->toArray();
    }

    public function selectedMenu($menuId)
    {
        if (!$this->authorize('view', 'You do not have permission to view this section')) {
            return;
        }
        
        $this->selectedMenuItem = $menuId;
    }

    public function updatedServiceId($value)
    {
        $service = Service::find($value);
        if ($service) {
            $this->emit('serviceSelected', [
                'lower_limit' => $service->lower_limit,
                'upper_limit' => $service->upper_limit,
                'is_mandatory' => $service->is_mandatory
            ]);

            if ($service->is_mandatory) {
                $this->payment_mode = '2';
            }
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function generateControlNumber()
    {
        $client = ClientsModel::where('client_number', $this->client_number)->first();
        $service = Service::find($this->service_id);

        if (!$client || !$service) {
            $this->addError('control_number', 'Invalid data for control number generation');
            return null;
        }

        return '1' .
            str_pad('0001', 4, '0', STR_PAD_LEFT) . // Default NBC code
            str_pad($client->client_number, 5, '0', STR_PAD_LEFT) .
            $service->code .
            $this->is_recurring .
            $this->payment_mode;
    }

    public function createBill()
    {
        if (!$this->authorize('create', 'You do not have permission to create bills')) {
            return;
        }
        
        $this->validate();

        try {
            DB::beginTransaction();

            $controlNumber = $this->generateControlNumber();
            if (!$controlNumber) {
                throw new \Exception('Failed to generate control number');
            }

            $bill = Bill::create([
                'client_number' => $this->client_number,
                'service_id' => $this->service_id,
                'amount_due' => $this->amount,
                'amount_paid' => 0,
                'control_number' => $controlNumber,
                'is_mandatory' => $this->is_mandatory,
                'is_recurring' => $this->is_recurring,
                'payment_mode' => $this->payment_mode,
                'due_date' => $this->due_date,
                'status' => 'PENDING',
                'created_by' => auth()->id()
            ]);

            DB::commit();
            $this->reset(['amount', 'due_date', 'is_mandatory']);
            session()->flash('message', 'Bill created successfully with control number: ' . $controlNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bill creation failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to create bill: ' . $e->getMessage());
        }
    }

    public function searchBill()
    {
        $this->validate([
            'searchControlNumber' => 'required|string|size:13'
        ]);

        $this->searchResults = Bill::with(['client', 'service'])
            ->where('control_number', $this->searchControlNumber)
            ->first();

        if (!$this->searchResults) {
            session()->flash('error', 'Bill not found');
        }
    }

    public function viewBill($billId)
    {
        if (!$this->authorize('view', 'You do not have permission to view bills')) {
            return;
        }
        
        $this->selectedBill = Bill::with(['client', 'service', 'payments'])
            ->find($billId);
    }

    public function confirmDelete($billId)
    {
        $this->billToDelete = $billId;
        $this->showDeleteModal = true;
    }

    public function deleteBill()
    {
        if (!$this->authorize('delete', 'You do not have permission to delete bills')) {
            return;
        }
        
        try {
            $bill = Bill::findOrFail($this->billToDelete);
            
            // Only allow deletion of pending bills
            if ($bill->status !== 'PENDING') {
                throw new \Exception('Only pending bills can be deleted');
            }

            $bill->delete();
            $this->showDeleteModal = false;
            $this->billToDelete = null;
            session()->flash('message', 'Bill deleted successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete bill: ' . $e->getMessage());
        }
    }

    public function confirmPause($billId)
    {
        $this->billToPause = $billId;
        $this->showPauseModal = true;
    }

    public function pauseBill()
    {
        if (!$this->authorize('edit', 'You do not have permission to pause bills')) {
            return;
        }
        
        try {
            $bill = Bill::findOrFail($this->billToPause);
            
            // Only allow pausing of pending bills
            if ($bill->status !== 'PENDING') {
                throw new \Exception('Only pending bills can be paused');
            }

            $bill->update([
                'status' => 'CANCELLED',
                'pause_reason' => $this->pauseReason
            ]);

            $this->showPauseModal = false;
            $this->billToPause = null;
            $this->pauseReason = '';
            session()->flash('message', 'Bill paused successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to pause bill: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Bill::with(['client', 'service'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('control_number', 'like', '%' . $this->search . '%')
                      ->orWhere('client_number', 'like', '%' . $this->search . '%')
                      ->orWhere('member_id', 'like', '%' . $this->search . '%')
                      ->orWhereHas('client', function($q) {
                          $q->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%')
                            ->orWhere('business_name', 'like', '%' . $this->search . '%')
                            ->orWhere('client_number', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('service', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->status, function($query) {
                $query->where('status', $this->status);
            })
            ->when($this->dateFrom, function($query) {
                $query->where('due_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function($query) {
                $query->where('due_date', '<=', $this->dateTo);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $bills = $query->paginate($this->perPage);

        return view('livewire.billing.billing', array_merge(
            $this->permissions,
            [
                'bills' => $bills,
                'permissions' => $this->permissions
            ]
        ));
    }
}
