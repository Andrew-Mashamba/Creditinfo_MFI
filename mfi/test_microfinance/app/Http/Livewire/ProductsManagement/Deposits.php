<?php

namespace App\Http\Livewire\ProductsManagement;

use Livewire\Component;
use App\Models\DepositType;
use App\Models\sub_products;
use App\Models\approvals;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use Illuminate\Support\Facades\Log;

class Deposits extends Component
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
        'deposit_type_id' => '',
        'interest_rate' => '',
        'min_balance' => '',
        'product_account' => '',
        'notes' => '',
        'currency' => 'TZS',
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
        'profit_account' => '100',
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
        'collection_account_interest_charges' => ''
    ];

    // Validation Rules
    protected $rules = [
        'form.product_name' => 'required|min:3|max:50',
        'form.deposit_type_id' => 'required|exists:deposit_types,id',
        'form.interest_rate' => 'required|numeric|min:0|max:100',
        'form.min_balance' => 'required|numeric|min:0',
        'form.product_account' => 'required|exists:accounts,id',
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
        'form.collection_account_interest_charges' => 'nullable|string|max:30'
    ];

    protected $messages = [
        'form.product_name.required' => 'The product name is required.',
        'form.product_name.min' => 'The product name must be at least 3 characters.',
        'form.product_name.max' => 'The product name cannot exceed 50 characters.',
        'form.deposit_type_id.required' => 'Please select a deposit type.',
        'form.deposit_type_id.exists' => 'The selected deposit type is invalid.',
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
        'form.deposit_charge.min' => 'The deposit charge cannot be negative.',
        'form.deposit_charge_min_value.required' => 'The minimum deposit charge value is required.',
        'form.deposit_charge_min_value.numeric' => 'The minimum deposit charge value must be a number.',
        'form.deposit_charge_min_value.min' => 'The minimum deposit charge value cannot be negative.',
        'form.deposit_charge_max_value.required' => 'The maximum deposit charge value is required.',
        'form.deposit_charge_max_value.numeric' => 'The maximum deposit charge value must be a number.',
        'form.deposit_charge_max_value.min' => 'The maximum deposit charge value cannot be negative.',
        'form.withdraw.required' => 'The withdrawal status is required.',
        'form.withdraw.boolean' => 'The withdrawal status must be a boolean.',
        'form.withdraw_charge.required' => 'The withdrawal charge is required.',
        'form.withdraw_charge.numeric' => 'The withdrawal charge must be a number.',
        'form.withdraw_charge.min' => 'The withdrawal charge cannot be negative.',
        'form.withdraw_charge_min_value.required' => 'The minimum withdrawal charge value is required.',
        'form.withdraw_charge_min_value.numeric' => 'The minimum withdrawal charge value must be a number.',
        'form.withdraw_charge_min_value.min' => 'The minimum withdrawal charge value cannot be negative.',
        'form.withdraw_charge_max_value.required' => 'The maximum withdrawal charge value is required.',
        'form.withdraw_charge_max_value.numeric' => 'The maximum withdrawal charge value must be a number.',
        'form.withdraw_charge_max_value.min' => 'The maximum withdrawal charge value cannot be negative.',
        'form.interest_value.required' => 'The interest value is required.',
        'form.interest_value.numeric' => 'The interest value must be a number.',
        'form.interest_value.min' => 'The interest value cannot be negative.',
        'form.interest_tenure.required' => 'The interest tenure is required.',
        'form.interest_tenure.numeric' => 'The interest tenure must be a number.',
        'form.interest_tenure.min' => 'The interest tenure cannot be negative.',
        'form.maintenance_fees.required' => 'The maintenance fees are required.',
        'form.maintenance_fees.numeric' => 'The maintenance fees must be a number.',
        'form.maintenance_fees.min' => 'The maintenance fees cannot be negative.',
        'form.maintenance_fees_value.required' => 'The maintenance fees value is required.',
        'form.maintenance_fees_value.numeric' => 'The maintenance fees value must be a number.',
        'form.maintenance_fees_value.min' => 'The maintenance fees value cannot be negative.',
        'form.profit_account.string' => 'The profit account must be a string.',
        'form.profit_account.max' => 'The profit account cannot exceed 50 characters.',
        'form.inactivity.required' => 'The inactivity period is required.',
        'form.inactivity.string' => 'The inactivity period must be a string.',
        'form.inactivity.max' => 'The inactivity period cannot exceed 50 characters.',
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
        'form.require_image_member.required' => 'The require member image status is required.',
        'form.require_image_member.boolean' => 'The require member image status must be a boolean.',
        'form.require_image_id.required' => 'The require ID image status is required.',
        'form.require_image_id.boolean' => 'The require ID image status must be a boolean.',
        'form.require_mobile_number.required' => 'The require mobile number status is required.',
        'form.require_mobile_number.boolean' => 'The require mobile number status must be a boolean.',
        'form.generate_mobile_profile.required' => 'The generate mobile profile status is required.',
        'form.generate_mobile_profile.boolean' => 'The generate mobile profile status must be a boolean.',
        'form.ledger_fees.required' => 'The ledger fees are required.',
        'form.ledger_fees.numeric' => 'The ledger fees must be a number.',
        'form.ledger_fees.min' => 'The ledger fees cannot be negative.',
        'form.ledger_fees_value.required' => 'The ledger fees value is required.',
        'form.ledger_fees_value.numeric' => 'The ledger fees value must be a number.',
        'form.ledger_fees_value.min' => 'The ledger fees value cannot be negative.',
        'form.collection_account_withdraw_charges.string' => 'The collection account for withdrawal charges must be a string.',
        'form.collection_account_withdraw_charges.max' => 'The collection account for withdrawal charges cannot exceed 30 characters.',
        'form.collection_account_deposit_charges.string' => 'The collection account for deposit charges must be a string.',
        'form.collection_account_deposit_charges.max' => 'The collection account for deposit charges cannot exceed 30 characters.',
        'form.collection_account_interest_charges.string' => 'The collection account for interest charges must be a string.',
        'form.collection_account_interest_charges.max' => 'The collection account for interest charges cannot exceed 30 characters.'
    ];

    public function mount()
    {
        // Initialize common deposit types if they don't exist
        $commonDepositTypes = [
            [
                'type' => 'Fixed Deposit',
                'summary' => 'Time-locked deposits with higher interest rates'
            ],
            [
                'type' => 'Recurring Deposit',
                'summary' => 'Regular deposits with fixed intervals'
            ],
            [
                'type' => 'Term Deposit',
                'summary' => 'Deposits with specific maturity periods'
            ],
            [
                'type' => 'Call Deposit',
                'summary' => 'Flexible deposits with immediate access'
            ],
            [
                'type' => 'Special Deposit',
                'summary' => 'Custom deposit products for specific needs'
            ]
        ];

        foreach ($commonDepositTypes as $type) {
            DepositType::firstOrCreate(
                ['type' => $type['type']],
                [
                    'summary' => $type['summary'],
                    'status' => true,
              
                ]
            );
        }
    }

    public function getDepositTypesProperty()
    {
        return DepositType::where('status', true)->get();
    }

    public function getAccountsProperty()
    {
        return Account::where('category_code', '2100')
            //->where('status', 'ACTIVE')
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
            ->where('product_type', 3000)
            ->with('depositType')
            ->when($this->search, function ($query) {
                $query->where('product_name', 'ilike', '%' . $this->search . '%');
            })
            ->when($this->filters['status'] !== '', function ($query) {
                $query->where('status', $this->filters['status']);
            })
            ->when($this->filters['type'] !== '', function ($query) {
                $query->where('deposit_type_id', $this->filters['type']);
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

            Log::info('Starting deposit product creation', [
                'user_id' => auth()->id(),
                'institution_id' => auth()->user()->institution_id,
                'form_data' => $this->form
            ]);

            $product = sub_products::create([
                'sub_product_name' => $this->form['product_name'],
                'product_name' => $this->form['product_name'],
                'product_type' => '3000',
                'deposit_type_id' => $this->form['deposit_type_id'],
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
                'created_by' => auth()->id(),
                'status' => 'PENDING'
            ]);

            Log::info('Deposit product created successfully', [
                'product_id' => $product->id,
                'product_name' => $product->product_name
            ]);

            // Create approval request
            if ($product->requires_approval) {
                $user = auth()->user();
                if (!$user) {
                    throw new \Exception('User not authenticated');
                }

                $approvalData = [
                   
                    'process_name' => 'Deposit Product Creation',
                    'process_description' => 'New deposit product: ' . $product->product_name,
                    'approval_process_description' => 'has created a new deposit product',
                    'process_code' => 'PRODUCT_CRE',
                    'process_id' => $product->id,
                    'approval_status' => 'PENDING',
                    'process_status' => 'PENDING',
                    'user_id' => $user->id,
                    'team_id' => $user->currentTeam->id ?? null
                ];

                $approval = approvals::create($approvalData);

                Log::info('Approval request created for deposit product', [
                    'approval_id' => $approval->id,
                    'product_id' => $product->id
                ]);
            }

            DB::commit();

            Log::info('Deposit product creation completed successfully', [
                'product_id' => $product->id
            ]);

            session()->flash('message', 'Deposit product created successfully and pending approval.');
            $this->resetForm();
            $this->showAddModal = false;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating deposit product', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error creating deposit product: ' . $e->getMessage());
        }
    }

    public function editProduct($id)
    {
        $this->editingProduct = sub_products::findOrFail($id);
        $this->form = [
            'sub_product_name' => $this->editingProduct->sub_product_name,
            'product_name' => $this->editingProduct->product_name,
            'deposit_type_id' => $this->editingProduct->deposit_type_id,
            'interest_rate' => $this->editingProduct->interest,
            'min_balance' => $this->editingProduct->min_balance,
            'product_account' => $this->editingProduct->product_account,
            'notes' => $this->editingProduct->notes,
            'currency' => $this->editingProduct->currency,
            'deposit' => $this->editingProduct->deposit,
            'deposit_charge' => $this->editingProduct->deposit_charge,
            'deposit_charge_min_value' => $this->editingProduct->deposit_charge_min_value,
            'deposit_charge_max_value' => $this->editingProduct->deposit_charge_max_value,
            'withdraw' => $this->editingProduct->withdraw,
            'withdraw_charge' => $this->editingProduct->withdraw_charge,
            'withdraw_charge_min_value' => $this->editingProduct->withdraw_charge_min_value,
            'withdraw_charge_max_value' => $this->editingProduct->withdraw_charge_max_value,
            'interest_value' => $this->editingProduct->interest_value,
            'interest_tenure' => $this->editingProduct->interest_tenure,
            'maintenance_fees' => $this->editingProduct->maintenance_fees,
            'maintenance_fees_value' => $this->editingProduct->maintenance_fees_value,
            'profit_account' => $this->editingProduct->profit_account,
            'inactivity' => $this->editingProduct->inactivity,
            'create_during_registration' => $this->editingProduct->create_during_registration,
            'activated_by_lower_limit' => $this->editingProduct->activated_by_lower_limit,
            'requires_approval' => $this->editingProduct->requires_approval,
            'generate_atm_card_profile' => $this->editingProduct->generate_atm_card_profile,
            'allow_statement_generation' => $this->editingProduct->allow_statement_generation,
            'send_notifications' => $this->editingProduct->send_notifications,
            'require_image_member' => $this->editingProduct->require_image_member,
            'require_image_id' => $this->editingProduct->require_image_id,
            'require_mobile_number' => $this->editingProduct->require_mobile_number,
            'generate_mobile_profile' => $this->editingProduct->generate_mobile_profile,
            'ledger_fees' => $this->editingProduct->ledger_fees,
            'ledger_fees_value' => $this->editingProduct->ledger_fees_value,
            'collection_account_withdraw_charges' => $this->editingProduct->collection_account_withdraw_charges,
            'collection_account_deposit_charges' => $this->editingProduct->collection_account_deposit_charges,
            'collection_account_interest_charges' => $this->editingProduct->collection_account_interest_charges,
            'status' => $this->editingProduct->status,            
        ];
        $this->showAddModal = true;
    }

    public function updateProduct()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            Log::info('Starting deposit product update', [
                'product_id' => $this->editingProduct->id,
                'user_id' => auth()->id(),
                'institution_id' => auth()->user()->institution_id,
                'form_data' => $this->form
            ]);

            $product = sub_products::findOrFail($this->editingProduct->id);

            // Log original values
            Log::info('Original deposit product values', [
                'product_id' => $product->id,
                'original_values' => $product->toArray()
            ]);

            $editPackage = json_encode([
                'sub_product_name' => $this->form['sub_product_name'],
                'product_name' => $this->form['product_name'],
                'deposit_type_id' => $this->form['deposit_type_id'],
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
                'updated_by' => auth()->id()
            ]);

            Log::info('Deposit product updated successfully', [
                'product_id' => $product->id,
                'updated_values' => $product->toArray()
            ]);

            // Create approval request
            if ($product->requires_approval) {
                $user = auth()->user();
                if (!$user) {
                    throw new \Exception('User not authenticated');
                }

                $approvalData = [                    
                    'edit_package' => $editPackage,
                    'process_name' => 'Deposit Product Update',
                    'process_description' => 'Updated deposit product: ' . $product->product_name,
                    'approval_process_description' => 'has updated a deposit product',
                    'process_code' => 'PROD_EDIT',
                    'process_id' => $product->id,
                    'approval_status' => 'PENDING',
                    'process_status' => 'PENDING',
                    'user_id' => $user->id,
                    'team_id' => $user->currentTeam->id ?? null
                ];

                $approval = approvals::create($approvalData);

                Log::info('Approval request created for deposit product update', [
                    'approval_id' => $approval->id,
                    'product_id' => $product->id
                ]);
            }

            DB::commit();

            Log::info('Deposit product update completed successfully', [
                'product_id' => $product->id
            ]);

            session()->flash('message', 'Deposit product updated successfully and pending approval.');
            $this->resetForm();
            $this->showAddModal = false;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating deposit product', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error updating deposit product: ' . $e->getMessage());
        }
    }

    public function deleteProduct($id)
    {
        try {
            DB::beginTransaction();

            Log::info('Starting deposit product deletion', [
                'product_id' => $id,
                'user_id' => auth()->id(),
                'institution_id' => auth()->user()->institution_id
            ]);

            $product = sub_products::findOrFail($id);

            // Create approval request
            $user = auth()->user();
            if (!$user) {
                throw new \Exception('User not authenticated');
            }

            $approvalData = [
               
                'process_name' => 'Deposit Product Deletion',
                'process_description' => 'Delete deposit product: ' . $product->product_name,
                'approval_process_description' => 'has requested to delete a deposit product',
                'process_code' => 'PROD_DEACTIVATE',
                'process_id' => $product->id,
                'approval_status' => 'PENDING',
                'process_status' => 'PENDING',
                'user_id' => $user->id,
                'team_id' => $user->currentTeam->id ?? null,
                'edit_package' => null
            ];

            $approval = approvals::create($approvalData);

            Log::info('Approval request created for deposit product deletion', [
                'approval_id' => $approval->id,
                'product_id' => $product->id
            ]);

            DB::commit();

            Log::info('Deposit product deletion request completed successfully', [
                'product_id' => $product->id
            ]);

            session()->flash('message', 'Deposit product deletion request submitted for approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error requesting deposit product deletion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error requesting deposit product deletion: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->form = [
            'sub_product_name' => '',
            'product_name' => '',
            'deposit_type_id' => '',
            'interest_rate' => '',
            'min_balance' => '',
            'product_account' => '',
            'notes' => '',
            'currency' => 'TZS',
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
        return view('livewire.products-management.deposits');
    }
}
