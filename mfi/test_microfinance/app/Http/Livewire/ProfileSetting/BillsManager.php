<?php

namespace App\Http\Livewire\ProfileSetting;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BillsManager extends Component
{
    use WithPagination;

    public $search = '';
    public $show_service_details = false;
    public $selected_service = null;
    
    // Service form properties
    public $name;
    public $code;
    public $description;
    public $is_mandatory = false;
    public $lower_limit;
    public $upper_limit;
    public $isRecurring = false;
    public $paymentMode = '3';
    public $debit_account;
    public $credit_account;
    public $right_category;
    public $right_category_code;
    public $right_account;
    public $debit_category;
    public $debit_category_code;
    public $debit_subcategory;
    public $debit_subcategory_code;

    protected $rules = [
        'name' => 'required|min:3',
        'code' => 'required|size:3',
        'description' => 'nullable',
        'is_mandatory' => 'boolean',
        'lower_limit' => 'nullable|numeric|min:0',
        'upper_limit' => 'nullable|numeric|min:0|gt:lower_limit',
        'isRecurring' => 'boolean',
        'paymentMode' => 'required|in:1,2,3,4,5',
        'debit_account' => 'required',
        'right_account' => 'required'
    ];

    protected $messages = [
        'code.unique' => 'This service code is already in use.',
        'upper_limit.gt' => 'Upper limit must be greater than lower limit.',
        'debit_account.required' => 'Please select a debit account.',
        'right_account.required' => 'Please select a credit account.'
    ];

    public function updated($propertyName)
    {
        if ($propertyName === 'code') {
            $this->validateOnly('code', [
                'code' => [
                    'required',
                    'size:3',
                    function ($attribute, $value, $fail) {
                        $query = DB::table('services')->where('code', $value);
                        if ($this->selected_service) {
                            $query->where('id', '!=', $this->selected_service);
                        }
                        if ($query->exists()) {
                            $fail('This service code is already in use.');
                        }
                    }
                ]
            ]);
        }
    }

    public function updatedDebitCategory($value)
    {
        if ($value) {
            $this->debit_category_code = $value;
            $this->debit_subcategory = null;
            $this->debit_subcategory_code = null;
            $this->debit_account = null;
        } else {
            $this->debit_category_code = null;
            $this->debit_subcategory = null;
            $this->debit_subcategory_code = null;
            $this->debit_account = null;
        }
    }

    public function updatedDebitSubcategory($value)
    {
        if ($value) {
            $this->debit_subcategory_code = $value;
            $this->debit_account = null;
        } else {
            $this->debit_subcategory_code = null;
            $this->debit_account = null;
        }
    }

    public function updatedRightCategory($value)
    {
        if ($value) {
            $this->right_category_code = $value;
            $this->right_account = null;
        } else {
            $this->right_category_code = null;
            $this->right_account = null;
        }
    }

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->is_mandatory = false;
        $this->lower_limit = null;
        $this->upper_limit = null;
        $this->isRecurring = false;
        $this->paymentMode = '3';
        $this->selected_service = null;
        $this->debit_account = null;
        $this->credit_account = null;
        $this->right_category = null;
        $this->right_category_code = null;
        $this->right_account = null;
        $this->debit_category = null;
        $this->debit_category_code = null;
        $this->debit_subcategory = null;
        $this->debit_subcategory_code = null;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function viewServiceDetails($serviceId)
    {
        $service = DB::table('services')
            ->where('id', $serviceId)
            ->first();
        
        if ($service) {
            $this->selected_service = $serviceId;
            $this->name = $service->name;
            $this->code = $service->code;
            $this->description = $service->description;
            $this->is_mandatory = $service->is_mandatory;
            $this->lower_limit = $service->lower_limit;
            $this->upper_limit = $service->upper_limit;
            $this->isRecurring = $service->isRecurring;
            $this->paymentMode = $service->paymentMode;
            $this->debit_account = $service->debit_account;
            $this->credit_account = $service->credit_account;
            
            // Set up debit account selection
            if ($service->debit_account) {
                $account = DB::table('accounts')
                    ->where('account_number', $service->debit_account)
                    ->first();
                if ($account) {
                    $this->debit_category = DB::table('accounts')
                        ->where('major_category_code', $account->major_category_code)
                        ->whereNotNull('account_name')
                        ->value('account_name');
                    $this->debit_category_code = $account->major_category_code;
                    $this->debit_subcategory = DB::table('accounts')
                        ->where('account_number', $account->parent_account_number)
                        ->value('account_name');
                    $this->debit_subcategory_code = $account->category_code;
                }
            }
            
            // Set up credit account selection
            if ($service->credit_account) {
                $account = DB::table('accounts')
                    ->where('account_number', $service->credit_account)
                    ->first();
                if ($account) {
                    $this->right_category = DB::table('accounts')
                        ->where('major_category_code', $account->major_category_code)
                        ->whereNotNull('account_name')
                        ->value('account_name');
                    $this->right_category_code = $account->major_category_code;
                    $this->right_account = $account->account_number;
                }
            }
        }
        
        $this->show_service_details = true;
    }

    public function closeServiceDetails()
    {
        $this->show_service_details = false;
        $this->resetForm();
    }

    public function saveService()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
                'is_mandatory' => $this->is_mandatory,
                'lower_limit' => $this->lower_limit,
                'upper_limit' => $this->upper_limit,
                'isRecurring' => $this->isRecurring,
                'paymentMode' => $this->paymentMode,
                'debit_account' => $this->debit_account,
                'credit_account' => $this->right_account,
                'updated_at' => now()
            ];

            if ($this->selected_service) {
                // Update existing service
                DB::table('services')
                    ->where('id', $this->selected_service)
                    ->update($data);
            } else {
                // Create new service
                $data['created_at'] = now();
                DB::table('services')->insert($data);
            }

            DB::commit();
            $this->closeServiceDetails();
            session()->flash('message', 'Service saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving service: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $services = DB::table('services')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        // Get asset accounts (debit accounts)
        $debitAccounts = DB::table('accounts')
            ->where('category_code', 'like', '1%')  // Asset accounts start with 1
            ->orderBy('account_number')
            ->get();

        return view('livewire.profile-setting.bills-manager', [
            'services' => $services,
            'debitAccounts' => $debitAccounts
        ]);
    }
}
