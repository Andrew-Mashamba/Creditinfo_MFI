<?php

namespace App\Http\Livewire\ProductsManagement;

use Livewire\Component;
use App\Models\SavingsType;
use App\Models\sub_products;
use App\Models\approvals;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use Illuminate\Support\Facades\Log;

class Savings extends Component
{
    // Form Properties
    public $showAddModal = false;
    public $editingProduct = null;
    public $search = '';
    public $showFilters = false;
    public $sortField = 'product_name';
    public $sortDirection = 'asc';
    public $filters = [
        'status' => '',
        'type' => '',
        'min_balance' => '',
        'interest_rate' => ''
    ];

    // Form Data
    public $form = [
        'product_name' => '',
        'savings_type_id' => '',
        'interest_rate' => '',
        'min_balance' => '',
        'product_account' => '',
        'notes' => '',
        'currency' => 'KES',
        'deposit' => 1,
        'deposit_charge' => 0,
        'deposit_charge_min_value' => 0,
        'deposit_charge_max_value' => 0,
        'withdraw' => 1,
        'withdraw_charge' => 0,
        'withdraw_charge_min_value' => 0,
        'withdraw_charge_max_value' => 0,
        'interest_value' => 0,
        'interest_tenure' => 0,
        'maintenance_fees' => 0,
        'maintenance_fees_value' => 0,
        'profit_account' => '',
        'inactivity' => '0',
        'create_during_registration' => 0,
        'activated_by_lower_limit' => 0,
        'requires_approval' => 1,
        'generate_atm_card_profile' => 0,
        'allow_statement_generation' => 1,
        'send_notifications' => 1,
        'require_image_member' => 1,
        'require_image_id' => 1,
        'require_mobile_number' => 1,
        'generate_mobile_profile' => 0,
        'ledger_fees' => 0,
        'ledger_fees_value' => 0,
        'collection_account_withdraw_charges' => '',
        'collection_account_deposit_charges' => '',
        'collection_account_interest_charges' => '',
        'status' => 'PENDING'
    ];

    // Validation Rules
    protected $rules = [
        //'form.product_name' => 'required|min:3|max:50',
        'form.savings_type_id' => 'required|exists:savings_types,id',
        'form.interest_rate' => 'required|numeric|min:0|max:100',
        'form.min_balance' => 'required|numeric|min:0',
        'form.product_account' => 'required|exists:accounts,account_number',
        'form.notes' => 'nullable|max:500',
        'form.currency' => 'required|string|max:50',
        'form.deposit' => 'required|boolean',
        'form.deposit_charge' => 'required|numeric|min:0',
        'form.deposit_charge_min_value' => 'required|numeric|min:0',
        'form.deposit_charge_max_value' => 'required|numeric|min:0',
        'form.withdraw' => 'required|boolean',
        'form.withdraw_charge' => 'required|numeric|min:0',
        'form.withdraw_charge_min_value' => 'required|numeric|min:0',
        'form.withdraw_charge_max_value' => 'required|numeric|min:0',
        'form.interest_value' => 'required|numeric|min:0',
        'form.interest_tenure' => 'required|numeric|min:0',
        'form.maintenance_fees' => 'required|numeric|min:0',
        'form.maintenance_fees_value' => 'required|numeric|min:0',
        'form.profit_account' => 'nullable|string|max:50',
        'form.inactivity' => 'required|string|max:50',
        'form.create_during_registration' => 'required|boolean',
        'form.activated_by_lower_limit' => 'required|boolean',
        'form.requires_approval' => 'required|boolean',
        'form.generate_atm_card_profile' => 'required|boolean',
        'form.allow_statement_generation' => 'required|boolean',
        'form.send_notifications' => 'required|boolean',
        'form.require_image_member' => 'required|boolean',
        'form.require_image_id' => 'required|boolean',
        'form.require_mobile_number' => 'required|boolean',
        'form.generate_mobile_profile' => 'required|boolean',
        'form.ledger_fees' => 'required|numeric|min:0',
        'form.ledger_fees_value' => 'required|numeric|min:0',
        'form.collection_account_withdraw_charges' => 'nullable|string|max:30',
        'form.collection_account_deposit_charges' => 'nullable|string|max:30',
        'form.collection_account_interest_charges' => 'nullable|string|max:30',
        'form.status' => 'required|string|max:50'
    ];

    protected $messages = [
        'form.product_name.required' => 'The product name is required.',
        'form.product_name.min' => 'The product name must be at least 3 characters.',
        'form.product_name.max' => 'The product name cannot exceed 50 characters.',
        'form.savings_type_id.required' => 'Please select a savings type.',
        'form.savings_type_id.exists' => 'The selected savings type is invalid.',
        'form.interest_rate.required' => 'The interest rate is required.',
        'form.interest_rate.numeric' => 'The interest rate must be a number.',
        'form.interest_rate.min' => 'The interest rate cannot be negative.',
        'form.interest_rate.max' => 'The interest rate cannot exceed 100%.',
        'form.min_balance.required' => 'The minimum balance is required.',
        'form.min_balance.numeric' => 'The minimum balance must be a number.',
        'form.min_balance.min' => 'The minimum balance cannot be negative.',
        'form.product_account.required' => 'Please select a product account.',
        'form.product_account.exists' => 'The selected product account is invalid.',
        'form.notes.max' => 'The notes cannot exceed 500 characters.',
        'form.currency.required' => 'The currency is required.',
        'form.currency.string' => 'The currency must be a string.',
        'form.currency.max' => 'The currency cannot exceed 50 characters.',
        'form.deposit.required' => 'The deposit status is required.',
        'form.deposit.boolean' => 'The deposit status must be a boolean.',
        'form.deposit_charge.required' => 'The deposit charge is required.',
        'form.deposit_charge.numeric' => 'The deposit charge must be a number.',
        'form.deposit_charge_min_value.required' => 'The deposit charge minimum value is required.',
        'form.deposit_charge_min_value.numeric' => 'The deposit charge minimum value must be a number.',
        'form.deposit_charge_max_value.required' => 'The deposit charge maximum value is required.',
        'form.deposit_charge_max_value.numeric' => 'The deposit charge maximum value must be a number.',
        'form.withdraw.required' => 'The withdraw status is required.',
        'form.withdraw.boolean' => 'The withdraw status must be a boolean.',
        'form.withdraw_charge.required' => 'The withdraw charge is required.',
        'form.withdraw_charge.numeric' => 'The withdraw charge must be a number.',
        'form.withdraw_charge_min_value.required' => 'The withdraw charge minimum value is required.',
        'form.withdraw_charge_min_value.numeric' => 'The withdraw charge minimum value must be a number.',
        'form.withdraw_charge_max_value.required' => 'The withdraw charge maximum value is required.',
        'form.withdraw_charge_max_value.numeric' => 'The withdraw charge maximum value must be a number.',
        'form.interest_value.required' => 'The interest value is required.',
        'form.interest_value.numeric' => 'The interest value must be a number.',
        'form.interest_tenure.required' => 'The interest tenure is required.',
        'form.interest_tenure.numeric' => 'The interest tenure must be a number.',
        'form.maintenance_fees.required' => 'The maintenance fees is required.',
        'form.maintenance_fees.numeric' => 'The maintenance fees must be a number.',
        'form.maintenance_fees_value.required' => 'The maintenance fees value is required.',
        'form.maintenance_fees_value.numeric' => 'The maintenance fees value must be a number.',
        'form.profit_account.max' => 'The profit account cannot exceed 50 characters.',
        'form.inactivity.required' => 'The inactivity status is required.',
        'form.inactivity.string' => 'The inactivity status must be a string.',
        'form.inactivity.max' => 'The inactivity status cannot exceed 50 characters.',
        'form.create_during_registration.required' => 'The create during registration status is required.',
        'form.create_during_registration.boolean' => 'The create during registration status must be a boolean.',
        'form.activated_by_lower_limit.required' => 'The activated by lower limit status is required.',
        'form.activated_by_lower_limit.boolean' => 'The activated by lower limit status must be a boolean.',
        'form.requires_approval.required' => 'The requires approval status is required.',
        'form.requires_approval.boolean' => 'The requires approval status must be a boolean.',
        'form.generate_atm_card_profile.required' => 'The generate ATM card profile status is required.',
        'form.generate_atm_card_profile.boolean' => 'The generate ATM card profile status must be a boolean.',
        'form.allow_statement_generation.required' => 'The allow statement generation status is required.',
        'form.allow_statement_generation.boolean' => 'The allow statement generation status must be a boolean.',
        'form.send_notifications.required' => 'The send notifications status is required.',
        'form.send_notifications.boolean' => 'The send notifications status must be a boolean.',
        'form.require_image_member.required' => 'The require image member status is required.',
        'form.require_image_member.boolean' => 'The require image member status must be a boolean.',
        'form.require_image_id.required' => 'The require image ID status is required.',
        'form.require_image_id.boolean' => 'The require image ID status must be a boolean.',
        'form.require_mobile_number.required' => 'The require mobile number status is required.',
        'form.require_mobile_number.boolean' => 'The require mobile number status must be a boolean.',
        'form.generate_mobile_profile.required' => 'The generate mobile profile status is required.',
        'form.generate_mobile_profile.boolean' => 'The generate mobile profile status must be a boolean.',
        'form.ledger_fees.required' => 'The ledger fees is required.',
        'form.ledger_fees.numeric' => 'The ledger fees must be a number.',
        'form.ledger_fees_value.required' => 'The ledger fees value is required.',
        'form.ledger_fees_value.numeric' => 'The ledger fees value must be a number.',
        'form.collection_account_withdraw_charges.max' => 'The collection account withdraw charges cannot exceed 30 characters.',
        'form.collection_account_deposit_charges.max' => 'The collection account deposit charges cannot exceed 30 characters.',
        'form.collection_account_interest_charges.max' => 'The collection account interest charges cannot exceed 30 characters.',
        'form.status.required' => 'The status is required.',
        'form.status.string' => 'The status must be a string.',
        'form.status.max' => 'The status cannot exceed 50 characters.'
    ];

    public function mount()
    {
        // Initialize common savings types if they don't exist
        $commonSavingsTypes = [
            [
                'type' => 'Regular Savings',
                'summary' => 'Basic savings account with standard interest rates'
            ],
            [
                'type' => 'Fixed Deposit',
                'summary' => 'Time-locked savings with higher interest rates'
            ],
            [
                'type' => 'Goal Savings',
                'summary' => 'Savings account for specific financial goals'
            ],
            [
                'type' => 'Youth Savings',
                'summary' => 'Savings account for young members'
            ],
            [
                'type' => 'Senior Savings',
                'summary' => 'Savings account with benefits for senior members'
            ]
        ];

        foreach ($commonSavingsTypes as $type) {
            SavingsType::firstOrCreate(
                ['type' => $type['type']],
                [
                    'summary' => $type['summary'],
                    'status' => true
                ]
            );
        }
    }

    public function getSavingsTypesProperty()
    {
        return SavingsType::where('status', true)->get();
    }

    public function getAccountsProperty()
    {
        return Account::where('category_code', '2100') 
            //->where('status', 'ACTIVE')
            ->where('account_use', 'internal')
            ->get();
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

    public function getProductsProperty()
    {
        $query = sub_products::query()
            ->where('product_type', '2000')
            ->with('savingsType')
            ->when($this->search, function ($query) {
                $query->where('product_name', 'ilike', '%' . $this->search . '%');
            })
            ->when($this->filters['status'] !== '', function ($query) {
                $query->where('status', $this->filters['status']);
            })
            ->when($this->filters['type'] !== '', function ($query) {
                $query->where('savings_type_id', $this->filters['type']);
            })
            ->when($this->filters['min_balance'] !== '', function ($query) {
                $query->where('min_balance', '>=', $this->filters['min_balance']);
            })
            ->when($this->filters['interest_rate'] !== '', function ($query) {
                $query->where('interest', '>=', $this->filters['interest_rate']);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        return $query->get();
    }

    public function createProduct()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            Log::info('Starting savings product creation', [
                'user_id' => auth()->id(),
             
                'form_data' => $this->form
            ]);

            $product = sub_products::create([
                'sub_product_name' => $this->form['product_name'],
                'product_name' => $this->form['product_name'],
                'product_type' => '2000',
                'savings_type_id' => $this->form['savings_type_id'],
                'interest' => $this->form['interest_rate'],
                'min_balance' => $this->form['min_balance'],
                'product_account' => $this->form['product_account'],
                'notes' => $this->form['notes'],
                'currency' => $this->form['currency'],
                'deposit' => $this->form['deposit'],
                'deposit_charge' => $this->form['deposit_charge'],
                'deposit_charge_min_value' => $this->form['deposit_charge_min_value'],
                'deposit_charge_max_value' => $this->form['deposit_charge_max_value'],
                'withdraw' => $this->form['withdraw'],
                'withdraw_charge' => $this->form['withdraw_charge'],
                'withdraw_charge_min_value' => $this->form['withdraw_charge_min_value'],
                'withdraw_charge_max_value' => $this->form['withdraw_charge_max_value'],
                'interest_value' => $this->form['interest_value'],
                'interest_tenure' => $this->form['interest_tenure'],
                'maintenance_fees' => $this->form['maintenance_fees'],
                'maintenance_fees_value' => $this->form['maintenance_fees_value'],
                'profit_account' => $this->form['profit_account'],
                'inactivity' => $this->form['inactivity'],
                'create_during_registration' => $this->form['create_during_registration'],
                'activated_by_lower_limit' => $this->form['activated_by_lower_limit'],
                'requires_approval' => $this->form['requires_approval'],
                'generate_atm_card_profile' => $this->form['generate_atm_card_profile'],
                'allow_statement_generation' => $this->form['allow_statement_generation'],
                'send_notifications' => $this->form['send_notifications'],
                'require_image_member' => $this->form['require_image_member'],
                'require_image_id' => $this->form['require_image_id'],
                'require_mobile_number' => $this->form['require_mobile_number'],
                'generate_mobile_profile' => $this->form['generate_mobile_profile'],
                'ledger_fees' => $this->form['ledger_fees'],
                'ledger_fees_value' => $this->form['ledger_fees_value'],
                'collection_account_withdraw_charges' => $this->form['collection_account_withdraw_charges'],
                'collection_account_deposit_charges' => $this->form['collection_account_deposit_charges'],
                'collection_account_interest_charges' => $this->form['collection_account_interest_charges'],
                'status' => $this->form['status'],
                'created_by' => auth()->id(),
            ]);

            Log::info('Savings product created successfully', [
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'savings_type_id' => $product->savings_type_id
            ]);

            // Get current user
            $user = auth()->user();
            if (!$user) {
                Log::error('User not authenticated');
                throw new \Exception('User not authenticated.');
            }

            // Prepare approval data
            $approvalData = [              
                'process_name' => 'createSavingsProduct',
                'process_description' => $user->name . ' has created a new savings product',
                'approval_process_description' => 'has approved a transaction',
                'process_code' => 'PRODUCT_CRE',
                'process_id' => $product->id,
                'approval_status' => 'PENDING',
                'process_status' => 'PENDING',
                'user_id' => $user->id,
                'team_id' => 1 // Set default team ID to 1
            ];

            Log::info('Creating approval request', [
                'approval_data' => $approvalData
            ]);

            // Create approval request
            $approval = approvals::create($approvalData);

            if (!$approval) {
                Log::error('Failed to create approval request', [
                    'approval_data' => $approvalData
                ]);
                throw new \Exception('Failed to create approval request.');
            }

            Log::info('Approval request created successfully', [
                'approval_id' => $approval->id,
                'process_id' => $approval->process_id
            ]);

            DB::commit();

            Log::info('Savings product creation completed successfully', [
                'product_id' => $product->id,
                'approval_id' => $approval->id,
                'transaction_completed' => true
            ]);

            $this->resetForm();
            $this->showAddModal = false;
            session()->flash('message', 'Savings product created successfully and pending approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating savings product', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'form_data' => $this->form,
                'user_id' => auth()->id(),
             
            ]);
            session()->flash('error', 'Error creating savings product. Please try again.');
        }
    }

    public function editProduct($id)
    {
        $product = sub_products::findOrFail($id);
        $this->editingProduct = $product;
        $this->form = [
            'sub_product_name' => $product->sub_product_name,
            'product_name' => $product->product_name,
            'savings_type_id' => $product->savings_type_id,            
            'interest_value' => $product->interest_value,
            'interest_tenure' => $product->interest_tenure,
            'interest_rate' => $product->interest,
            'min_balance' => $product->min_balance,
            'product_account' => $product->product_account,
            'notes' => $product->notes,
            'currency' => $product->currency,
            'deposit' => $product->deposit,
            'deposit_charge' => $product->deposit_charge,
            'deposit_charge_min_value' => $product->deposit_charge_min_value,
            'deposit_charge_max_value' => $product->deposit_charge_max_value,
            'withdraw' => $product->withdraw,
            'withdraw_charge' => $product->withdraw_charge,
            'withdraw_charge_min_value' => $product->withdraw_charge_min_value,
            'withdraw_charge_max_value' => $product->withdraw_charge_max_value,            
            'maintenance_fees' => $product->maintenance_fees,
            'maintenance_fees_value' => $product->maintenance_fees_value,
            'profit_account' => $product->profit_account,
            'inactivity' => $product->inactivity,
            'create_during_registration' => $product->create_during_registration,
            'activated_by_lower_limit' => $product->activated_by_lower_limit,
            'requires_approval' => $product->requires_approval,
            'generate_atm_card_profile' => $product->generate_atm_card_profile,
            'allow_statement_generation' => $product->allow_statement_generation,
            'send_notifications' => $product->send_notifications,
            'require_image_member' => $product->require_image_member,
            'require_image_id' => $product->require_image_id,
            'require_mobile_number' => $product->require_mobile_number,
            'generate_mobile_profile' => $product->generate_mobile_profile,
            'ledger_fees' => $product->ledger_fees,
            'ledger_fees_value' => $product->ledger_fees_value,
            'collection_account_withdraw_charges' => $product->collection_account_withdraw_charges,
            'collection_account_deposit_charges' => $product->collection_account_deposit_charges,
            'collection_account_interest_charges' => $product->collection_account_interest_charges,
            'status' => $product->status
        ];
        $this->showAddModal = true;
    }

    public function viewNewSavingProduct()
    {
        $this->selectedAction = 0;
        $this->showAddModal = true;
        $this->resetForm();
    }

    public function updateProduct()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            Log::info('Starting savings product update', [
                'product_id' => $this->editingProduct->id,
                'user_id' => auth()->id(),
         
                'form_data' => $this->form
            ]);

            $product = sub_products::findOrFail($this->editingProduct->id);
            
            // Log the original values before update
            Log::info('Original product values', [
                'product_id' => $product->id,
                'original_values' => $product->toArray()
            ]);

            $editPackage = json_encode([
                'sub_product_name' => $this->form['sub_product_name'],
                'product_name' => $this->form['product_name'],
                'savings_type_id' => $this->form['savings_type_id'],
                'interest' => $this->form['interest_rate'],
                'interest_value' => $this->form['interest_value'],
                'interest_tenure' => $this->form['interest_tenure'],
                'min_balance' => $this->form['min_balance'],
                'product_account' => $this->form['product_account'],
                'notes' => $this->form['notes'],
                'currency' => $this->form['currency'],
                'deposit' => $this->form['deposit'],
                'deposit_charge' => $this->form['deposit_charge'],
                'deposit_charge_min_value' => $this->form['deposit_charge_min_value'],
                'deposit_charge_max_value' => $this->form['deposit_charge_max_value'],
                'withdraw' => $this->form['withdraw'],
                'withdraw_charge' => $this->form['withdraw_charge'],
                'withdraw_charge_min_value' => $this->form['withdraw_charge_min_value'],
                'withdraw_charge_max_value' => $this->form['withdraw_charge_max_value'],                
                'maintenance_fees' => $this->form['maintenance_fees'],
                'maintenance_fees_value' => $this->form['maintenance_fees_value'],
                'profit_account' => $this->form['profit_account'],
                'inactivity' => $this->form['inactivity'],
                'create_during_registration' => $this->form['create_during_registration'],
                'activated_by_lower_limit' => $this->form['activated_by_lower_limit'],
                'requires_approval' => $this->form['requires_approval'],
                'generate_atm_card_profile' => $this->form['generate_atm_card_profile'],
                'allow_statement_generation' => $this->form['allow_statement_generation'],
                'send_notifications' => $this->form['send_notifications'],
                'require_image_member' => $this->form['require_image_member'],
                'require_image_id' => $this->form['require_image_id'],
                'require_mobile_number' => $this->form['require_mobile_number'],
                'generate_mobile_profile' => $this->form['generate_mobile_profile'],
                'ledger_fees' => $this->form['ledger_fees'],
                'ledger_fees_value' => $this->form['ledger_fees_value'],
                'collection_account_withdraw_charges' => $this->form['collection_account_withdraw_charges'],
                'collection_account_deposit_charges' => $this->form['collection_account_deposit_charges'],
                'collection_account_interest_charges' => $this->form['collection_account_interest_charges'],
                'status' => $this->form['status'],
                'updated_by' => auth()->id()
            ]);
            

            Log::info('Savings product updated successfully', [
                'product_id' => $product->id,
                'updated_values' => $product->fresh()->toArray()
            ]);

            // Get current user
            $user = auth()->user();
            if (!$user) {
                Log::error('User not authenticated');
                throw new \Exception('User not authenticated.');
            }

            // Prepare approval data
            $approvalData = [         
                'process_name' => 'updateSavingsProduct',
                'process_description' => $user->name . ' has updated a savings product',
                'approval_process_description' => 'has approved a transaction',
                'process_code' => 'PROD_EDIT',
                'process_id' => $product->id,
                'approval_status' => 'PENDING',
                'process_status' => 'PENDING',
                'user_id' => $user->id,
                'team_id' => 1, // Set default team ID to 1
                'edit_package' => $editPackage
            ];

            Log::info('Creating approval request for update', [
                'approval_data' => $approvalData
            ]);

            // Create approval request for the update
            $approval = approvals::create($approvalData);

            if (!$approval) {
                Log::error('Failed to create approval request for update', [
                    'approval_data' => $approvalData
                ]);
                throw new \Exception('Failed to create approval request for update.');
            }

            Log::info('Approval request created successfully for update', [
                'approval_id' => $approval->id,
                'process_id' => $approval->process_id
            ]);

            DB::commit();

            Log::info('Savings product update completed successfully', [
                'product_id' => $product->id,
                'approval_id' => $approval->id,
                'transaction_completed' => true
            ]);

            $this->resetForm();
            $this->showAddModal = false;
            session()->flash('message', 'Savings product updated successfully and pending approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating savings product', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'product_id' => $this->editingProduct->id,
                'form_data' => $this->form,
                'user_id' => auth()->id(),
                'institution_id' => auth()->user()->institution_id
            ]);
            session()->flash('error', 'Error updating savings product. Please try again.');
        }
    }

    public function deleteProduct($id)
    {
        try {
            DB::beginTransaction();

            Log::info('Starting savings product deletion', [
                'product_id' => $id,
                'user_id' => auth()->id(),
                'institution_id' => auth()->user()->institution_id
            ]);

            $product = sub_products::findOrFail($id);
            
            // Get current user
            $user = auth()->user();
            if (!$user) {
                Log::error('User not authenticated');
                throw new \Exception('User not authenticated.');
            }

            // Prepare approval data
            $approvalData = [
         
                'process_name' => 'deleteSavingsProduct',
                'process_description' => $user->name . ' has requested to delete a savings product',
                'approval_process_description' => 'has approved a transaction',
                'process_code' => 'PROD_DEACTIVATE',
                'process_id' => $product->id,
                'approval_status' => 'PENDING',
                'process_status' => 'PENDING',
                'user_id' => $user->id,
                'team_id' => 1 // Set default team ID to 1
            ];

            Log::info('Creating approval request for deletion', [
                'approval_data' => $approvalData
            ]);

            // Create approval request for deletion
            $approval = approvals::create($approvalData);

            if (!$approval) {
                Log::error('Failed to create approval request for deletion', [
                    'approval_data' => $approvalData
                ]);
                throw new \Exception('Failed to create approval request for deletion.');
            }

            Log::info('Approval request created successfully for deletion', [
                'approval_id' => $approval->id,
                'process_id' => $approval->process_id
            ]);

            DB::commit();

            Log::info('Savings product deletion request completed successfully', [
                'product_id' => $product->id,
                'approval_id' => $approval->id,
                'transaction_completed' => true
            ]);

            session()->flash('message', 'Savings product deletion request submitted for approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting savings product', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'product_id' => $id,
                'user_id' => auth()->id(),
                'institution_id' => auth()->user()->institution_id
            ]);
            session()->flash('error', 'Error deleting savings product. Please try again.');
        }
    }

    public function resetForm()
    {
        $this->form = [
            'sub_product_name' => '',
            'product_name' => '',
            'savings_type_id' => '',
            'interest_value' => '',
            'interest_tenure' => '',
            'interest_rate' => '',
            'min_balance' => '',
            'product_account' => '',
            'notes' => '',
            'currency' => 'KES',
            'deposit' => 1,
            'deposit_charge' => 0,
            'deposit_charge_min_value' => 0,
            'deposit_charge_max_value' => 0,
            'withdraw' => 1,
            'withdraw_charge' => 0,
            'withdraw_charge_min_value' => 0,
            'withdraw_charge_max_value' => 0,
            'maintenance_fees' => 0,
            'maintenance_fees_value' => 0,
            'profit_account' => '',
            'inactivity' => '0',
            'create_during_registration' => 0,
            'activated_by_lower_limit' => 0,
            'requires_approval' => 1,
            'generate_atm_card_profile' => 0,
            'allow_statement_generation' => 1,
            'send_notifications' => 1,
            'require_image_member' => 1,
            'require_image_id' => 1,
            'require_mobile_number' => 1,
            'generate_mobile_profile' => 0,
            'ledger_fees' => 0,
            'ledger_fees_value' => 0,
            'collection_account_withdraw_charges' => '',
            'collection_account_deposit_charges' => '',
            'collection_account_interest_charges' => '',
            'status' => 'PENDING'
        ];
        $this->editingProduct = null;
    }

    public function updated($property)
    {
        if ($property === 'form.product_account') {
            $account = Account::where('account_number', $this->form['product_account'])->first();
            if ($account) {
                $this->form['product_name'] = $account->account_name;
            }
        }
    }

    public function render()
    {
        return view('livewire.products-management.savings', [
            'products' => $this->products,
            'savingsTypes' => $this->savingsTypes,
            'accounts' => $this->accounts
        ]);
    }
}
