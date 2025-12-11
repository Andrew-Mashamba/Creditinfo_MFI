<?php

namespace App\Http\Livewire\ProductsManagement;

use Livewire\Component;
// Removed Share model import - shares table not needed
use App\Models\sub_products;
use App\Models\approvals;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use Illuminate\Support\Facades\Log;

class Shares extends Component
{
    // Form Properties
    public $selectedAction = 0;
    public $search = '';
    public $sortField = 'sub_product_name';
    public $sortDirection = 'asc';
    public $showFilters = false;
    public $selectedProductId;
    public $filters = [
        'status' => '',
        'type' => '',
        'min_shares' => '',
        'price_range' => ''
    ];

    // New Product Form Properties
    public $sub_product_name;
    public $productStatus = 'PENDING';
    public $currency = 'TZS';
    public $selectedShareType = 1;
    public $allocated_shares;
    public $available_shares;
    public $nominal_price;
    public $product_account;
    public $notes;
    public $shares;
    public $shareAccounts;
    public $summary;
    public $totalValue = 0;
    public $deposit = 1;
    public $withdraw = 1;
    public $inactivity = '30';
    public $create_during_registration = 0;
    public $activated_by_lower_limit = 0;
    public $requires_approval = 1;
    public $generate_atm_card_profile = 0;
    public $allow_statement_generation = 1;
    public $send_notifications = 1;
    public $require_image_member = 1;
    public $require_image_id = 1;
    public $require_mobile_number = 1;
    public $generate_mobile_profile = 0;

    // Share Settings
    public $minimum_required_shares = 100;
    public $lock_in_period = 30;
    public $dividend_eligibility_period = 90;
    public $dividend_payment_frequency = 'annual';
    public $payment_methods = [];
    public $withdrawal_approval_level = 1;

    // Additional Options
    public $allow_share_transfer = false;
    public $allow_share_withdrawal = false;
    public $enable_dividend_calculation = false;

    // SMS Settings
    public $sms_sender_name;
    public $sms_api_key;
    public $sms_enabled = false;

    protected $rules = [
        'sub_product_name' => 'required|min:3',
        //'selectedShareType' => 'required',
        'currency' => 'required',
        'allocated_shares' => 'required|numeric|min:1',
        'nominal_price' => 'required|numeric|min:0',
        'product_account' => 'required',
        'inactivity' => 'required|numeric|min:1',
        'minimum_required_shares' => 'required|numeric|min:1',
        'lock_in_period' => 'required|numeric|min:1',
        'dividend_eligibility_period' => 'required|numeric|min:1',
        'dividend_payment_frequency' => 'required|in:monthly,quarterly,semi_annual,annual',
        'payment_methods' => 'required|array|min:1',
        'withdrawal_approval_level' => 'required|in:1,2,3',
        'sms_sender_name' => 'required_if:sms_enabled,true|max:11',
        'sms_api_key' => 'required_if:sms_enabled,true',
    ];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        // Get the current user's institution ID
        $institutionId = 1;

        // Initialize common SACCO share types if they don't exist
        $commonShareTypes = [
            [
                'type' => 'Ordinary Shares',
                'summary' => 'Basic membership shares that give voting rights and dividend eligibility'
            ],
            [
                'type' => 'Preference Shares',
                'summary' => 'Shares with priority in dividend payments and capital repayment'
            ],
            [
                'type' => 'Development Shares',
                'summary' => 'Shares specifically for SACCO development and infrastructure'
            ],
            [
                'type' => 'Bonus Shares',
                'summary' => 'Additional shares issued as a bonus to existing shareholders'
            ],
            [
                'type' => 'Rights Shares',
                'summary' => 'Shares offered to existing members at a preferential rate'
            ],
            [
                'type' => 'Employee Shares',
                'summary' => 'Shares allocated to SACCO employees as part of their benefits'
            ],
            [
                'type' => 'Special Purpose Shares',
                'summary' => 'Shares created for specific SACCO projects or initiatives'
            ]
        ];


        try {
            $accounts = DB::table('accounts')
                ->where('category_code', '3000')
                ->where('client_number', '0000')
                ->select('account_number', 'account_name', 'category_code', 'sub_category_code')
                ->orderBy('account_name')
                ->get()
                ->map(function ($account) {
                    return [
                        'account_number' => $account->account_number,
                        'account_name' => $account->account_name,
                        'category_code' => $account->category_code,
                        'sub_category_code' => $account->sub_category_code
                    ];
                });

            $this->shareAccounts = $accounts;

            Log::info('Share Accounts Query Result:', [
                'count' => $this->shareAccounts->count(),
                'accounts' => $this->shareAccounts->toArray()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching share accounts: ' . $e->getMessage());
        }
    }

    public function test($id)
    {
        $this->selectedAction = $id;
        if ($id === 0) {
            $this->resetForm();
        }
    }

    public function updatedSelectedShareType($value)
    {
        // Removed Share model reference - shares table not needed
        $this->summary = '';
    }

    public function updatedProductAccount($value)
    {
        if ($value) {
            $account = collect($this->shareAccounts)->firstWhere('account_number', $value);
            if ($account) {
                $this->sub_product_name = $account['account_name'];
            }
        }
    }

    public function updatedAllocatedShares($value)
    {
        $this->totalValue = $this->getTotalValueProperty();
    }

    public function updatedNominalPrice($value)
    {
        $this->totalValue = $this->getTotalValueProperty();
    }

    public function getTotalValueProperty()
    {
        $allocatedShares = is_numeric($this->allocated_shares) ? (float)$this->allocated_shares : 0;
        $nominalPrice = is_numeric($this->nominal_price) ? (float)$this->nominal_price : 0;
        return $allocatedShares * $nominalPrice;
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

    public function resetFilters()
    {
        $this->filters = [
            'status' => '',
            'type' => '',
            'min_shares' => '',
            'price_range' => ''
        ];
    }

    public function resetForm()
    {
        $this->reset([
            'sub_product_name',
            'productStatus',
            'currency',
            'selectedShareType',
            'allocated_shares',
            'nominal_price',
            'product_account',
            'notes',
            'summary',
            'totalValue',
            'deposit',
            'withdraw',
            'inactivity',
            'create_during_registration',
            'activated_by_lower_limit',
            'requires_approval',
            'generate_atm_card_profile',
            'allow_statement_generation',
            'send_notifications',
            'require_image_member',
            'require_image_id',
            'require_mobile_number',
            'generate_mobile_profile',
            'minimum_required_shares',
            'lock_in_period',
            'dividend_eligibility_period',
            'dividend_payment_frequency',
            'payment_methods',
            'withdrawal_approval_level',
            'allow_share_transfer',
            'allow_share_withdrawal',
            'enable_dividend_calculation',
            'sms_sender_name',
            'sms_api_key',
            'sms_enabled'
        ]);
    }

    public function saveProduct()
    {
        try {
            // Log validation attempt
            Log::info('Attempting to save share product', [
                'user_id' => Auth::id(),
                'product_name' => $this->sub_product_name,
                'share_type_id' => $this->selectedShareType,
                'currency' => $this->currency,
                'allocated_shares' => $this->allocated_shares,
                'nominal_price' => $this->nominal_price,
                'product_account' => $this->product_account
            ]);

            // Validate the form data
            $this->validate();

            DB::beginTransaction();

            // Get account details
            $account = DB::table('accounts')
                ->where('account_number', $this->product_account)
                ->first();

            if (!$account) {
                throw new \Exception('Selected product account not found');
            }

            Log::info('Creating share product with account details', [
                'account_number' => $account->account_number,
                'account_name' => $account->account_name,
                'category_code' => $account->category_code
            ]);

            $productData = [
                'product_name' => $this->sub_product_name,
                'product_type' => 1000,
                'sub_product_name' => $this->sub_product_name,
                'sub_product_status' => true, //always true
                'status' => $this->productStatus,
                'currency' => $this->currency,
                'share_type_id' => 1,
                'shares_allocated' => $this->allocated_shares,
                'available_shares' => $this->allocated_shares, //available shares is the same as shares allocated on creation
                'nominal_price' => $this->nominal_price,
                'product_account' => $this->product_account,
                'notes' => $this->notes,
                'deposit' => $this->deposit,
                'withdraw' => $this->withdraw,
                'inactivity' => $this->inactivity,
                'create_during_registration' => $this->create_during_registration,
                'activated_by_lower_limit' => $this->activated_by_lower_limit,
                'requires_approval' => $this->requires_approval,
                'generate_atm_card_profile' => $this->generate_atm_card_profile,
                'allow_statement_generation' => $this->allow_statement_generation,
                'send_notifications' => $this->send_notifications,
                'require_image_member' => $this->require_image_member,
                'require_image_id' => $this->require_image_id,
                'require_mobile_number' => $this->require_mobile_number,
                'generate_mobile_profile' => $this->generate_mobile_profile,
                'minimum_required_shares' => $this->minimum_required_shares,
                'lock_in_period' => $this->lock_in_period,
                'dividend_eligibility_period' => $this->dividend_eligibility_period,
                'dividend_payment_frequency' => $this->dividend_payment_frequency,
                'payment_methods' => $this->payment_methods,
                'withdrawal_approval_level' => $this->withdrawal_approval_level,
                'allow_share_transfer' => $this->allow_share_transfer,
                'allow_share_withdrawal' => $this->allow_share_withdrawal,
                'enable_dividend_calculation' => $this->enable_dividend_calculation,
                'sms_sender_name' => $this->sms_sender_name,
                'sms_api_key' => $this->sms_api_key,
                'sms_enabled' => $this->sms_enabled,
                'created_by' => Auth::id()
            ];

            if ($this->selectedAction === 1) {
            $product = sub_products::create($productData);
            // $editPackage = json_encode($productData);
            approvals::create([
                'process_name' => 'create_share_product',
                'process_description' => Auth::user()->name . ' has added a new share product ' . $this->sub_product_name,
                'approval_process_description' => 'Share product creation approval required',
                'process_code' => 'PRODUCT_CRE',
                'process_id' => $product->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => null
            ]);
            session()->flash('message', 'Share product created successfully and pending approval.');
            $this->selectedAction = 0;
            $this->resetForm();
            }
            if ($this->selectedAction === 2) {                
                $product = sub_products::find($this->selectedProductId);               
                // $product->update($productData);
                // $product->save();
                $editPackage = json_encode($productData);
                approvals::create([
                    'process_name' => 'update_share_product',
                    'process_description' => Auth::user()->name . ' has updated the share product ' . $this->sub_product_name,
                    'approval_process_description' => 'Share product update approval required',
                    'process_code' => 'PROD_EDIT',
                    'process_id' => $product->id,
                    'process_status' => 'PENDING',
                    'user_id' => auth()->user()->id,
                    'approver_id' => null,
                    'approval_status' => 'PENDING',
                    'edit_package' => $editPackage
                ]);
                session()->flash('message', 'Share product updated successfully and pending approval.');
                $this->selectedAction = 0;
                $this->resetForm();                
            }

            DB::commit();

            Log::info('Share product created successfully', [
                'product_id' => $product?->id ?? null,
                'product_name' => $product?->sub_product_name ?? $productData['sub_product_name'],
                'created_by' => Auth::id()
            ]);

            

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Share product validation failed', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
                'product_name' => $this->sub_product_name
            ]);
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create share product', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'product_name' => $this->sub_product_name,
                'share_type_id' => $this->selectedShareType,
                'product_account' => $this->product_account
            ]);
            session()->flash('error', 'Failed to create share product: ' . $e->getMessage());
        }
    }

    public function editProduct($id)
    {
        $product = sub_products::find($id);
        if ($product) {
            $this->selectedProductId = $id;
            $this->sub_product_name = $product->sub_product_name;
            $this->productStatus = $product->status;            
            $this->currency = $product->currency;
            $this->selectedShareType = $product->share_type_id;
            $this->allocated_shares = $product->shares_allocated;
            // $this->available_shares = $product->shares_allocated;            
            $this->nominal_price = $product->nominal_price;
            $this->product_account = $product->product_account;
            $this->notes = $product->notes;
            $this->deposit = $product->deposit;
            $this->withdraw = $product->withdraw;
            $this->inactivity = $product->inactivity;
            $this->create_during_registration = $product->create_during_registration;
            $this->activated_by_lower_limit = $product->activated_by_lower_limit;
            $this->requires_approval = $product->requires_approval;
            $this->generate_atm_card_profile = $product->generate_atm_card_profile;
            $this->allow_statement_generation = $product->allow_statement_generation;
            $this->send_notifications = $product->send_notifications;
            $this->require_image_member = $product->require_image_member;
            $this->require_image_id = $product->require_image_id;
            $this->require_mobile_number = $product->require_mobile_number;
            $this->generate_mobile_profile = $product->generate_mobile_profile;
            $this->minimum_required_shares = $product->minimum_required_shares;
            $this->lock_in_period = $product->lock_in_period;
            $this->dividend_eligibility_period = $product->dividend_eligibility_period;
            $this->dividend_payment_frequency = $product->dividend_payment_frequency;
            $this->payment_methods = $product->payment_methods;
            $this->withdrawal_approval_level = $product->withdrawal_approval_level;
            $this->allow_share_transfer = $product->allow_share_transfer;
            $this->allow_share_withdrawal = $product->allow_share_withdrawal;
            $this->enable_dividend_calculation = $product->enable_dividend_calculation;
            $this->sms_sender_name = $product->sms_sender_name;
            $this->sms_api_key = $product->sms_api_key;
            $this->sms_enabled = $product->sms_enabled;
            $this->totalValue = $this->getTotalValueProperty();
            $this->selectedAction = 2;
        }
    }

    public function deleteProduct($id)
    {
        try {
            DB::beginTransaction();

            $product = sub_products::find($id);
            if (!$product) {
                throw new \Exception('Product not found.');
            }

            // Create approval request for deletion
            $user = auth()->user();
            if (!$user) {
                throw new \Exception('User not authenticated');
            }

            $approvalData = [
                'process_name' => 'Share Product Deletion',
                'process_description' => 'Delete share product: ' . $product->sub_product_name,
                'approval_process_description' => 'has requested to delete a share product',
                'process_code' => 'PROD_DEACTIVATE',
                'process_id' => $product->id,
                'approval_status' => 'PENDING',
                'process_status' => 'PENDING',
                'user_id' => $user->id,
                'team_id' => $user->currentTeam->id ?? null
            ];

            $approval = approvals::create($approvalData);

            Log::info('Approval request created for share product deletion', [
                'approval_id' => $approval->id,
                'product_id' => $product->id
            ]);

            DB::commit();

            session()->flash('message', 'Share product deletion request submitted for approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error requesting share product deletion: ' . $e->getMessage());
            session()->flash('error', 'Error requesting share product deletion: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = sub_products::query()
            ->where('product_type', 1000); // Load the share type relationship

        // // Apply filters
        // if ($this->filters['status']) {
        //     $query->where('sub_product_status', $this->filters['status'] === 'active' ? 1 : 0);
        // }
        // if ($this->filters['type']) {
        //     $query->where('share_type_id', $this->filters['type']);
        // }
        // if ($this->filters['min_shares']) {
        //     $query->where('shares_allocated', '>=', $this->filters['min_shares']);
        // }
        // if ($this->filters['price_range']) {
        //     $query->where('nominal_price', '>=', $this->filters['price_range']);
        // }

        // // Apply search
        // if ($this->search) {
        //     $query->where('sub_product_name', 'like', '%' . $this->search . '%');
        // }

        // // Apply sorting
        // $query->orderBy($this->sortField, $this->sortDirection);

        $products = $query->get();

        Log::info('Loading share products', [
            'count' => $products->count(),
            'filters' => $this->filters,
            'search' => $this->search,
            'sort' => [
                'field' => $this->sortField,
                'direction' => $this->sortDirection
            ]
        ]);

        return view('livewire.products-management.shares', [
            'products' => $products
        ]);
    }
}
