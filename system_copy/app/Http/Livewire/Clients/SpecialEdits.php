<?php

namespace App\Http\Livewire\Clients;

use Livewire\Component;
use App\Models\ClientsModel;
use App\Models\approvals;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpecialEdits extends Component
{
    public $searchTerm = '';
    public $selectedClient = null;
    public $clients = [];
    
    // Editable fields
    public $phone_number = '';
    public $account_number = '';
    public $email = '';
    
    // Form validation
    public $showEditForm = false;
    public $showConfirmation = false;
    public $editPackage = [];
    
    protected $rules = [
        'phone_number' => 'required|string|max:10',
        'account_number' => 'required|string|max:50',
        'email' => 'nullable|email|max:100',
    ];
    
    protected $messages = [
        'phone_number.required' => 'Phone number is required.',
        'phone_number.max' => 'Phone number must be a valid number.',
        'account_number.required' => 'Account number is required.',
        'email.email' => 'Please enter a valid email address.',
    ];

    public function render()
    {
        return view('livewire.clients.special-edits');
    }
    
    public function searchClients()
    {
        if (empty($this->searchTerm)) {
            $this->clients = [];
            return;
        }
        
        $this->clients = ClientsModel::where(function($query) {
                $query->where('client_number', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('first_name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('last_name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('phone_number', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $this->searchTerm . '%');
            })
            ->limit(10)
            ->get();
    }
    
    public function selectClient($clientId)
    {
        $this->selectedClient = ClientsModel::find($clientId);
        
        if ($this->selectedClient) {
            $this->phone_number = $this->selectedClient->phone_number ?? '';
            $this->account_number = $this->selectedClient->account_number ?? '';
            $this->email = $this->selectedClient->email ?? '';
            $this->showEditForm = true;
            $this->clients = []; // Clear search results
        }
    }
    
    public function prepareEditPackage()
    {
        $this->validate();
        
        if (!$this->selectedClient) {
            session()->flash('error', 'No client selected.');
            return;
        }
        
        // Prepare edit package with old and new values
        $this->editPackage = [
            'phone_number' => [
                'old' => $this->selectedClient->phone_number,
                'new' => $this->phone_number
            ],
            'account_number' => [
                'old' => $this->selectedClient->account_number,
                'new' => $this->account_number
            ],
            'email' => [
                'old' => $this->selectedClient->email,
                'new' => $this->email
            ]
        ];
        
        $this->showConfirmation = true;
    }
    
    public function generateApprovalPackage()
    {
        try {
            DB::beginTransaction();
            
            // Create approval record
            $approval = approvals::create([
                // 'institution_id' => auth()->user()->institution_id,
                'process_name' => 'client_details_edit',
                'process_description' => auth()->user()->name . ' has requested to edit details for client: ' . 
                    $this->selectedClient->first_name . ' ' . $this->selectedClient->last_name . 
                    ' (ID: ' . $this->selectedClient->client_number . ')',
                'approval_process_description' => 'Client details modification approval required',
                'process_code' => 'CLIENT_EDIT',
                'process_id' => $this->selectedClient->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->id(),
                'team_id' => auth()->user()->current_team_id ?? '',
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => json_encode($this->editPackage)
            ]);
            
            DB::commit();
            
            Log::info('Client edit approval package generated', [
                'approval_id' => $approval->id,
                'client_id' => $this->selectedClient->id,
                'client_number' => $this->selectedClient->client_number,
                'edited_fields' => array_keys($this->editPackage)
            ]);
            
            session()->flash('success', 'Approval package generated successfully. Waiting for approval.');
            $this->resetForm();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating approval package', [
                'error' => $e->getMessage(),
                'client_id' => $this->selectedClient->id ?? null
            ]);
            session()->flash('error', 'Failed to generate approval package: ' . $e->getMessage());
        }
    }
    
    public function cancelEdit()
    {
        $this->resetForm();
    }
    
    public function resetForm()
    {
        $this->selectedClient = null;
        $this->phone_number = '';
        $this->account_number = '';
        $this->email = '';
        $this->showEditForm = false;
        $this->showConfirmation = false;
        $this->editPackage = [];
        $this->resetValidation();
    }
    
    public function updatedSearchTerm()
    {
        $this->searchClients();
    }
}
