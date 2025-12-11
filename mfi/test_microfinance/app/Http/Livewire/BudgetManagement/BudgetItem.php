<?php

namespace App\Http\Livewire\BudgetManagement;

use App\Models\approvals;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Models\BudgetManagement;
use Livewire\WithPagination;

class BudgetItem extends Component
{
    use WithPagination;

    // Form properties
    public $new_budget_item = false;
    public $annual_budget;
    public $monthly_allocation;
    public $start_date;
    public $end_date;
    public $notes;
    public $budget_name;
    public $expense_account_id;

    // Modal properties
    public $viewModal = false;
    public $editRequestModal = false;
    public $deleteRequestModal = false;
    public $selectedItemId = null;
    public $selectedItem = null;

    // Edit request properties
    public $edit_budget_name;
    public $edit_annual_budget;
    public $edit_monthly_allocation;
    public $edit_start_date;
    public $edit_end_date;
    public $edit_notes;
    public $edit_expense_account_id;
    public $edit_justification;

    // Delete request properties
    public $delete_justification;

    // Table properties
    public $search = '';
    public $statusFilter = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;


    protected $messages = [
        'budget_name.required' => 'Budget name is required.',
        'budget_name.max' => 'Budget name cannot exceed 40 characters.',
        'annual_budget.required' => 'Annual budget amount is required.',
        'annual_budget.numeric' => 'Annual budget must be a valid number.',
        'annual_budget.min' => 'Annual budget cannot be negative.',
        'start_date.required' => 'Budget start date is required.',
        'start_date.first_day_of_month' => 'Start date must be the first day of the month.',
        'end_date.required' => 'Budget end date is required.',
        'end_date.after' => 'End date must be after start date.',
        'end_date.last_day_of_month' => 'End date must be the last day of the month.',
        'expense_account_id.required' => 'Please select an expense account.',
        'expense_account_id.exists' => 'Please select a valid expense account.',
    ];

    public function mount()
    {
        $this->resetPage();
    }

    // Custom validation rules
    protected function rules()
    {
        return [
            'budget_name' => 'required|string|max:40',
            'annual_budget' => 'required|numeric|min:0',
            'start_date' => ['required', 'date', function ($attribute, $value, $fail) {
                if ($value && \Carbon\Carbon::parse($value)->day !== 1) {
                    $fail('Start date must be the first day of the month.');
                }
            }],
            'end_date' => ['required', 'date', 'after:start_date', function ($attribute, $value, $fail) {
                if ($value) {
                    $endDate = \Carbon\Carbon::parse($value);
                    $lastDayOfMonth = $endDate->copy()->endOfMonth();
                    if (!$endDate->isSameDay($lastDayOfMonth)) {
                        $fail('End date must be the last day of the month.');
                    }
                }
            }],
            'notes' => 'nullable|string|max:1000',
            'expense_account_id' => 'required|exists:accounts,id',
        ];
    }

    public function menuItemClick()
    {
        Log::channel('budget_management')->info('New budget item modal opened', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'timestamp' => now()
        ]);

        $this->new_budget_item = true;
        $this->resetBudgetData();
        
        Log::channel('budget_management')->info('Budget form data reset', [
            'user_id' => auth()->id(),
            'modal_opened' => $this->new_budget_item
        ]);
    }

    public function save()
    {
        // Log the start of budget creation process
        Log::channel('budget_management')->info('Budget item creation started', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'budget_name' => $this->budget_name,
            'expense_account_id' => $this->expense_account_id,
            'annual_budget' => $this->annual_budget,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'timestamp' => now()
        ]);

        try {
            // Validate the input data
            $this->validate();
            Log::channel('budget_management')->info('Budget item validation passed', [
                'user_id' => auth()->id(),
                'budget_name' => $this->budget_name
            ]);

            // Get expense account details for logging
            $expenseAccount = DB::table('accounts')
                ->where('id', $this->expense_account_id)
                ->select('account_name', 'account_number')
                ->first();

            Log::channel('budget_management')->info('Expense account details retrieved', [
                'account_id' => $this->expense_account_id,
                'account_name' => $expenseAccount->account_name ?? 'Unknown',
                'account_number' => $expenseAccount->account_number ?? 'Unknown'
            ]);

            // Create the budget item with DRAFT status initially
            $budgetData = [
                'revenue' => $this->annual_budget ?? 0,
                'capital_expenditure' => $this->monthly_allocation ?? 0,
                'budget_name' => $this->budget_name,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'approval_status' => "PENDING",
                'notes' => $this->notes,
                'status' => "DRAFT", // Start as DRAFT until approved
                'expense_account_id' => $this->expense_account_id,
            ];

            Log::channel('budget_management')->info('Creating budget item with data', [
                'budget_data' => $budgetData,
                'user_id' => auth()->id()
            ]);

            $budgetItem = BudgetManagement::create($budgetData);
            $id = $budgetItem->id;

            Log::channel('budget_management')->info('Budget item created successfully', [
                'budget_id' => $id,
                'budget_name' => $this->budget_name,
                'annual_budget' => $this->annual_budget,
                'monthly_allocation' => $this->monthly_allocation,
                'user_id' => auth()->id()
            ]);

            // Create approval request
            try {
                $approval = new approvals();
                $approvalMessage = auth()->user()->name . ' has created new budget: ' . $this->budget_name;
                
                // Prepare edit package with budget details for approval review
                $editPackage = json_encode([
                    'budget_id' => $id,
                    'budget_name' => $this->budget_name,
                    'annual_budget' => $this->annual_budget,
                    'monthly_allocation' => $this->monthly_allocation,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'expense_account_id' => $this->expense_account_id,
                    'expense_account_name' => $expenseAccount->account_name ?? '',
                    'notes' => $this->notes
                ]);
                
                $approval->sendApproval($id, 'BUDGET_CREATE', $approvalMessage, 'has created new budget', 'BUDGET_CREATE', $editPackage);
                
                // Get the approval request ID and link it to the budget item
                $approvalRequest = approvals::where('process_id', $id)
                    ->where('process_code', 'BUDGET_CREATE')
                    ->latest()
                    ->first();
                
                if ($approvalRequest) {
                    $budgetItem->update([
                        'approval_request_id' => $approvalRequest->id,
                        'approval_status' => 'PENDING'
                    ]);
                }
                
                Log::channel('budget_management')->info('Approval request created successfully', [
                    'budget_id' => $id,
                    'approval_type' => 'BUDGET_CREATE',
                    'approval_message' => $approvalMessage,
                    'approval_request_id' => $approvalRequest->id ?? null,
                    'user_id' => auth()->id()
                ]);
            } catch (\Exception $approvalException) {
                Log::channel('budget_management')->error('Failed to create approval request', [
                    'budget_id' => $id,
                    'error' => $approvalException->getMessage(),
                    'error_trace' => $approvalException->getTraceAsString(),
                    'user_id' => auth()->id()
                ]);
                
                // Continue with the process even if approval fails
                session()->flash('warning', 'Budget item created but approval request failed. Please contact administrator.');
            }
            
            // Log successful completion
            Log::channel('budget_management')->info('Budget item creation process completed successfully', [
                'budget_id' => $id,
                'budget_name' => $this->budget_name,
                'user_id' => auth()->id(),
                'total_process_time' => now()->diffInMilliseconds(now()->subSeconds(1))
            ]);

            session()->flash('message', 'Budget item created successfully and sent for approval.');
            $this->resetBudgetData();
            $this->new_budget_item = false;
            $this->resetPage();

        } catch (\Illuminate\Validation\ValidationException $validationException) {
            Log::channel('budget_management')->error('Budget item validation failed', [
                'user_id' => auth()->id(),
                'validation_errors' => $validationException->errors(),
                'input_data' => [
                    'budget_name' => $this->budget_name,
                    'annual_budget' => $this->annual_budget,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'expense_account_id' => $this->expense_account_id
                ]
            ]);
            
            session()->flash('error', 'Validation failed. Please check your input and try again.');
            throw $validationException;

        } catch (\Exception $e) {
            Log::channel('budget_management')->error('Budget item creation failed', [
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'input_data' => [
                    'budget_name' => $this->budget_name ?? 'null',
                    'annual_budget' => $this->annual_budget ?? 'null',
                    'start_date' => $this->start_date ?? 'null',
                    'end_date' => $this->end_date ?? 'null',
                    'expense_account_id' => $this->expense_account_id ?? 'null'
                ]
            ]);
            
            session()->flash('error', 'Failed to create budget item: ' . $e->getMessage());
        }
    }

    public function resetBudgetData()
    {
        Log::channel('budget_management')->info('Budget form data reset', [
            'user_id' => auth()->id(),
            'previous_data' => [
                'annual_budget' => $this->annual_budget,
                'monthly_allocation' => $this->monthly_allocation,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'budget_name' => $this->budget_name,
                'expense_account_id' => $this->expense_account_id
            ],
            'timestamp' => now()
        ]);

        $this->annual_budget = null;
        $this->monthly_allocation = null;
        // Set default dates to current month's first and last day
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
        $this->notes = null;
        $this->budget_name = null;
        $this->expense_account_id = null;
    }

    // Table methods
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedExpenseAccountId()
    {
        Log::channel('budget_management')->info('Expense account selection changed', [
            'user_id' => auth()->id(),
            'expense_account_id' => $this->expense_account_id,
            'timestamp' => now()
        ]);

        if ($this->expense_account_id) {
            try {
                $account = DB::table('accounts')
                    ->where('id', $this->expense_account_id)
                    ->select('account_name', 'account_number')
                    ->first();
                
                if ($account) {
                    $this->budget_name = $account->account_name;
                    
                    Log::channel('budget_management')->info('Budget name automatically set from expense account', [
                        'user_id' => auth()->id(),
                        'expense_account_id' => $this->expense_account_id,
                        'account_name' => $account->account_name,
                        'account_number' => $account->account_number ?? 'Unknown',
                        'budget_name_set' => $this->budget_name
                    ]);
                } else {
                    Log::channel('budget_management')->warning('Expense account not found', [
                        'user_id' => auth()->id(),
                        'expense_account_id' => $this->expense_account_id
                    ]);
                }
            } catch (\Exception $e) {
                Log::channel('budget_management')->error('Failed to retrieve expense account details', [
                    'user_id' => auth()->id(),
                    'expense_account_id' => $this->expense_account_id,
                    'error_message' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            $this->budget_name = null;
            Log::channel('budget_management')->info('Budget name cleared - no expense account selected', [
                'user_id' => auth()->id()
            ]);
        }
    }

    public function updatedAnnualBudget()
    {
        Log::channel('budget_management')->info('Annual budget amount changed', [
            'user_id' => auth()->id(),
            'annual_budget' => $this->annual_budget,
            'timestamp' => now()
        ]);

        if ($this->annual_budget && is_numeric($this->annual_budget)) {
            $this->monthly_allocation = round($this->annual_budget / 12, 2);
            
            Log::channel('budget_management')->info('Monthly allocation calculated', [
                'user_id' => auth()->id(),
                'annual_budget' => $this->annual_budget,
                'monthly_allocation' => $this->monthly_allocation,
                'calculation' => $this->annual_budget . ' ÷ 12 = ' . $this->monthly_allocation
            ]);
        } else {
            $this->monthly_allocation = null;
            Log::channel('budget_management')->info('Monthly allocation cleared - invalid annual budget', [
                'user_id' => auth()->id(),
                'annual_budget' => $this->annual_budget
            ]);
        }
    }

    public function exportToExcel()
    {
        // Implementation for Excel export
        session()->flash('message', 'Export functionality will be implemented soon.');
    }

    // View methods
    public function viewItem($id)
    {
        try {
            $this->selectedItem = BudgetManagement::with('expenseAccount')->findOrFail($id);
            $this->selectedItemId = $id;
            $this->viewModal = true;
            
            Log::channel('budget_management')->info('Viewing budget item', [
                'budget_id' => $id,
                'budget_name' => $this->selectedItem->budget_name,
                'user_id' => auth()->id()
            ]);
        } catch (\Exception $e) {
            Log::channel('budget_management')->error('Failed to view budget item', [
                'budget_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            session()->flash('error', 'Failed to load budget item details.');
        }
    }

    public function closeViewModal()
    {
        $this->viewModal = false;
        $this->selectedItem = null;
        $this->selectedItemId = null;
    }

    // Edit Request methods
    public function requestEdit($id)
    {
        try {
            $item = BudgetManagement::findOrFail($id);
            
            // Check if item is locked or has pending approval
            if ($item->is_locked) {
                session()->flash('error', 'This budget item is currently locked. Reason: ' . $item->locked_reason);
                return;
            }
            
            if ($item->edit_approval_status === 'PENDING') {
                session()->flash('error', 'This budget item already has a pending edit request.');
                return;
            }
            
            // Populate edit form fields
            $this->selectedItemId = $id;
            $this->edit_budget_name = $item->budget_name;
            $this->edit_annual_budget = $item->revenue;
            $this->edit_monthly_allocation = $item->capital_expenditure;
            $this->edit_start_date = $item->start_date;
            $this->edit_end_date = $item->end_date;
            $this->edit_notes = $item->notes;
            $this->edit_expense_account_id = $item->expense_account_id;
            $this->edit_justification = '';
            
            $this->editRequestModal = true;
            
            Log::channel('budget_management')->info('Opening edit request modal', [
                'budget_id' => $id,
                'budget_name' => $item->budget_name,
                'user_id' => auth()->id()
            ]);
        } catch (\Exception $e) {
            Log::channel('budget_management')->error('Failed to open edit request', [
                'budget_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            session()->flash('error', 'Failed to load budget item for editing.');
        }
    }

    public function submitEditRequest()
    {
        // Validate edit form
        $this->validate([
            'edit_budget_name' => 'required|string|max:40',
            'edit_annual_budget' => 'required|numeric|min:0',
            'edit_start_date' => ['required', 'date', function ($attribute, $value, $fail) {
                if ($value && \Carbon\Carbon::parse($value)->day !== 1) {
                    $fail('Start date must be the first day of the month.');
                }
            }],
            'edit_end_date' => ['required', 'date', 'after:edit_start_date', function ($attribute, $value, $fail) {
                if ($value) {
                    $endDate = \Carbon\Carbon::parse($value);
                    $lastDayOfMonth = $endDate->copy()->endOfMonth();
                    if (!$endDate->isSameDay($lastDayOfMonth)) {
                        $fail('End date must be the last day of the month.');
                    }
                }
            }],
            'edit_notes' => 'nullable|string|max:1000',
            'edit_expense_account_id' => 'required|exists:accounts,id',
            'edit_justification' => 'required|string|min:10|max:500'
        ]);

        try {
            $budget = BudgetManagement::findOrFail($this->selectedItemId);
            
            // Prepare the changes data
            $pendingChanges = [
                'budget_name' => $this->edit_budget_name,
                'revenue' => $this->edit_annual_budget,
                'capital_expenditure' => $this->edit_monthly_allocation,
                'start_date' => $this->edit_start_date,
                'end_date' => $this->edit_end_date,
                'notes' => $this->edit_notes,
                'expense_account_id' => $this->edit_expense_account_id,
                'justification' => $this->edit_justification,
                'requested_by' => auth()->id(),
                'requested_at' => now()
            ];
            
            // Store pending changes and lock the item
            $budget->update([
                'pending_changes' => $pendingChanges,
                'edit_approval_status' => 'PENDING',
                'is_locked' => true,
                'locked_reason' => 'Edit request pending approval',
                'locked_at' => now(),
                'locked_by' => auth()->id()
            ]);
            
            // Create approval request
            $approval = new approvals();
            $approvalMessage = auth()->user()->name . ' has requested to edit budget: ' . $budget->budget_name;
            
            // Prepare edit package with both old and new values for comparison
            $editPackage = json_encode([
                'budget_id' => $this->selectedItemId,
                'operation' => 'EDIT',
                'old_values' => [
                    'budget_name' => $budget->budget_name,
                    'revenue' => $budget->revenue,
                    'capital_expenditure' => $budget->capital_expenditure,
                    'start_date' => $budget->start_date,
                    'end_date' => $budget->end_date,
                    'notes' => $budget->notes,
                    'expense_account_id' => $budget->expense_account_id
                ],
                'new_values' => $pendingChanges,
                'justification' => $this->edit_justification
            ]);
            
            $approval->sendApproval($this->selectedItemId, 'BUDGET_EDIT', $approvalMessage, 'has requested budget edit', 'BUDGET_EDIT', $editPackage);
            
            // Get the approval request ID and link it
            $approvalRequest = approvals::where('process_id', $this->selectedItemId)
                ->where('process_code', 'BUDGET_EDIT')
                ->latest()
                ->first();
            
            if ($approvalRequest) {
                $budget->update(['approval_request_id' => $approvalRequest->id]);
            }
            
            Log::channel('budget_management')->info('Budget edit request submitted', [
                'budget_id' => $this->selectedItemId,
                'budget_name' => $budget->budget_name,
                'approval_request_id' => $approvalRequest->id ?? null,
                'user_id' => auth()->id()
            ]);
            
            session()->flash('message', 'Edit request submitted successfully and sent for approval.');
            $this->closeEditRequestModal();
            $this->resetPage();
            
        } catch (\Exception $e) {
            Log::channel('budget_management')->error('Failed to submit edit request', [
                'budget_id' => $this->selectedItemId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            session()->flash('error', 'Failed to submit edit request: ' . $e->getMessage());
        }
    }

    public function closeEditRequestModal()
    {
        $this->editRequestModal = false;
        $this->selectedItemId = null;
        $this->resetEditForm();
    }

    public function resetEditForm()
    {
        $this->edit_budget_name = null;
        $this->edit_annual_budget = null;
        $this->edit_monthly_allocation = null;
        // Set default dates to current month's first and last day
        $this->edit_start_date = now()->startOfMonth()->format('Y-m-d');
        $this->edit_end_date = now()->endOfMonth()->format('Y-m-d');
        $this->edit_notes = null;
        $this->edit_expense_account_id = null;
        $this->edit_justification = null;
    }

    public function updatedEditAnnualBudget()
    {
        if ($this->edit_annual_budget && is_numeric($this->edit_annual_budget)) {
            $this->edit_monthly_allocation = round($this->edit_annual_budget / 12, 2);
        } else {
            $this->edit_monthly_allocation = null;
        }
    }

    // Delete Request methods
    public function requestDelete($id)
    {
        try {
            $item = BudgetManagement::findOrFail($id);
            
            // Check if item is locked or has pending approval
            if ($item->is_locked) {
                session()->flash('error', 'This budget item is currently locked. Reason: ' . $item->locked_reason);
                return;
            }
            
            $this->selectedItem = $item;
            $this->selectedItemId = $id;
            $this->delete_justification = '';
            $this->deleteRequestModal = true;
            
            Log::channel('budget_management')->info('Opening delete request modal', [
                'budget_id' => $id,
                'budget_name' => $item->budget_name,
                'user_id' => auth()->id()
            ]);
        } catch (\Exception $e) {
            Log::channel('budget_management')->error('Failed to open delete request', [
                'budget_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            session()->flash('error', 'Failed to load budget item for deletion.');
        }
    }

    public function submitDeleteRequest()
    {
        $this->validate([
            'delete_justification' => 'required|string|min:10|max:500'
        ]);

        if (!$this->selectedItemId) {
            session()->flash('error', 'No budget item selected for deletion.');
            return;
        }

        try {
            $budget = BudgetManagement::findOrFail($this->selectedItemId);
            $budgetName = $budget->budget_name;
            
            // Lock the item for deletion
            $budget->update([
                'is_locked' => true,
                'locked_reason' => 'Delete request pending approval',
                'locked_at' => now(),
                'locked_by' => auth()->id(),
                'edit_approval_status' => 'PENDING_DELETE'
            ]);
            
            // Create approval request
            $approval = new approvals();
            $approvalMessage = auth()->user()->name . ' has requested to delete budget: ' . $budgetName;
            
            // Prepare edit package with delete details
            $editPackage = json_encode([
                'budget_id' => $this->selectedItemId,
                'operation' => 'DELETE',
                'budget_details' => [
                    'budget_name' => $budget->budget_name,
                    'revenue' => $budget->revenue,
                    'capital_expenditure' => $budget->capital_expenditure,
                    'start_date' => $budget->start_date,
                    'end_date' => $budget->end_date,
                    'expense_account_id' => $budget->expense_account_id
                ],
                'justification' => $this->delete_justification,
                'requested_by' => auth()->id(),
                'requested_at' => now()
            ]);
            
            $approval->sendApproval($this->selectedItemId, 'BUDGET_DELETE', $approvalMessage, 'has requested budget deletion', 'BUDGET_DELETE', $editPackage);
            
            // Get the approval request ID and link it
            $approvalRequest = approvals::where('process_id', $this->selectedItemId)
                ->where('process_code', 'BUDGET_DELETE')
                ->latest()
                ->first();
            
            if ($approvalRequest) {
                $budget->update(['approval_request_id' => $approvalRequest->id]);
            }
            
            Log::channel('budget_management')->info('Budget delete request submitted', [
                'budget_id' => $this->selectedItemId,
                'budget_name' => $budgetName,
                'approval_request_id' => $approvalRequest->id ?? null,
                'justification' => $this->delete_justification,
                'user_id' => auth()->id()
            ]);
            
            session()->flash('message', 'Delete request for "' . $budgetName . '" submitted successfully and sent for approval.');
            $this->closeDeleteRequestModal();
            $this->resetPage();
            
        } catch (\Exception $e) {
            Log::channel('budget_management')->error('Failed to submit delete request', [
                'budget_id' => $this->selectedItemId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            session()->flash('error', 'Failed to submit delete request: ' . $e->getMessage());
        }
    }

    public function closeDeleteRequestModal()
    {
        $this->deleteRequestModal = false;
        $this->selectedItem = null;
        $this->selectedItemId = null;
        $this->delete_justification = '';
    }

    public function render()
    {
        $query = BudgetManagement::query();

        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('budget_name', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if (!empty($this->statusFilter)) {
            $query->where('approval_status', $this->statusFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $budgetItems = $query->paginate($this->perPage);

        // Get expense accounts for dropdown
        $expenseAccounts = DB::table('accounts')
            ->where('major_category_code', '5000')
            ->select('id', 'account_name', 'account_number')
            ->orderBy('account_name')
            ->get();

        return view('livewire.budget-management.budget-item', [
            'budgetItems' => $budgetItems,
            'expenseAccounts' => $expenseAccounts
        ]);
    }
}