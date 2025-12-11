<?php

namespace App\Http\Livewire\Deposits;

use App\Models\sub_products;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

use App\Models\DepositsModel;
use App\Models\approvals;
use App\Models\AccountsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\TransactionPostingService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

use App\Models\ClientsModel;
use App\Models\TeamUser;

use Livewire\WithFileUploads;
use App\Models\issured_deposits;

use App\Models\general_ledger;

use Livewire\WithPagination;


use App\Models\Client;

use App\Models\general_ledger as GeneralLedgerModel;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Exception;
use App\Services\MembershipVerificationService;
use App\Models\BankAccount;
use App\Services\NbcPayments\InternalFundTransferService;
use App\Services\NbcPayments\NbcPaymentService;
use App\Services\NbcPayments\NbcLookupService;
use App\Traits\Livewire\WithModulePermissions;

class Deposits extends Component
{
    use WithPagination;
    use WithFileUploads;
    use WithModulePermissions;

    public $tab_id = '10';
    public $title = 'Deposits Management';

    // Dashboard Properties
    public $totalDeposits = 0;
    public $activeAccounts = 0;
    public $inactiveAccounts = 0;
    public $totalProducts = 0;
    public $recentTransactions = [];
    public $monthlyDeposits = [];
    public $topSavers = [];
    public $depositsByProduct = [];

    // Filter Properties
    public $search = '';
    public $selectedProduct = '';
    public $statusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Modal Properties
    public $showIssueNewDeposits = false;
    public $showEditDepositsAccount = false;
    public $showDeleteDepositsAccount = false;
    public $showPendingTransactions = false;
    public $showAdjustBalance = false;
    public $showAuditLogs = false;

    // Form Properties
    public $member;
    public $product;
    public $number_of_deposits;
    public $linked_deposits_account;
    public $account_number;

    public $nominal_price;
    public $selected = 1;
    public $accountSelected;
    public $sub_product_number;
    public $depositsAvailable;
    public $amount;
    public $notes;
    public $bank;
    public $reference_number;
    public $availableProducts = [];

    public $activeDepositsCount;
    public $inactiveDepositsCount;

    public $name;
    public $region;
    public $wilaya;
    public $membershipNumber;
    public $parentDepositsAccount;
    public $pendingDepositsAccount;
    public $pendingDepositsAccountname;
    public $DepositsAccount;
    public $showAddDepositsAccount;

    public $email;
    public $Depositsstatus;
    public $permission = 'BLOCKED';
    public $password;

    public $deposit_charge_min_value;


    // Loading States
    public $isLoading = false;
    public $isSubmitting = false;
    public $isProcessing = false;

    // Messages
    public $successMessage = '';
    public $errorMessage = '';
    public $validationErrors = [];

    // Modal States
    public $showCreateNewDepositsAccount = false;

    // Form Inputs
    public $clientNumber;
    public $productId;
    public $accountNumber;
    public $accountName;
    public $balance;
    public $depositsMemberDetails = null;

    // Receive Deposits Properties
    public $showReceiveDepositsModal = false;
    public $selectedAccount;
    public $paymentMethod = 'cash'; // 'cash' or 'bank'
    public $selectedBank;
    public $referenceNumber;
    public $depositDate;
    public $depositTime;
    public $depositorName;
    public $narration;
    public $verifiedMember = null;
    public $memberAccounts = [];
    public $bankAccounts = [];
    public $selectedBankDetails = null;

    // Withdraw Deposits Properties
    public $showWithdrawDepositsModal = false;
    public $withdrawMembershipNumber;
    public $withdrawSelectedAccount;
    public $withdrawAmount;
    public $withdrawPaymentMethod = 'cash'; // 'cash', 'internal_transfer', 'tips_mno', 'tips_bank'
    public $withdrawSelectedBank;
    public $withdrawReferenceNumber;
    public $withdrawDate;
    public $withdrawTime;
    public $withdrawerName;
    public $withdrawNarration;
    public $withdrawVerifiedMember = null;
    public $withdrawMemberAccounts = [];
    public $withdrawBankAccounts = [];
    public $withdrawSelectedBankDetails = null;
    public $withdrawSelectedAccountBalance = 0;

    // Receipt Properties
    public $showReceiptModal = false;
    public $receiptData = null;

    // Additional withdrawal properties for different methods
    public $withdrawNbcAccount;
    public $withdrawAccountHolderName;
    public $withdrawMnoProvider;
    public $withdrawPhoneNumber;
    public $withdrawWalletHolderName;
    public $withdrawBankCode;
    public $withdrawBankAccountNumber;
    public $withdrawBankAccountHolderName;

    protected $rules = [
        'member'=> 'required|min:1',
        'reference_number'=>'required',
        'product'=> 'required|min:1',
        'number_of_deposits'=> 'required|min:1',
        'linked_deposits_account'=> 'required|min:1',
        'account_number'=> 'required|min:1',
        'membershipNumber' => 'required|min:1',
        'selectedAccount' => 'required|min:1',
        'amount' => 'required|numeric|min:0.01',
        'paymentMethod' => 'required|in:cash,bank',
        'selectedBank' => 'required_if:paymentMethod,bank',
        'referenceNumber' => 'required_if:paymentMethod,bank',
        'depositDate' => 'required_if:paymentMethod,bank|date',
        'depositTime' => 'required_if:paymentMethod,bank',
        'depositorName' => 'required',
        'narration' => 'required|min:3',
        'withdrawMembershipNumber' => 'required|min:1',
        'withdrawSelectedAccount' => 'required|min:1',
        'withdrawAmount' => 'required|numeric|min:0.01',
        'withdrawPaymentMethod' => 'required|in:cash,internal_transfer,tips_mno,tips_bank',
        'withdrawNarration' => 'required|string|max:255',
        // Internal transfer validation
        'withdrawNbcAccount' => 'required_if:withdrawPaymentMethod,internal_transfer|string|max:255',
        'withdrawAccountHolderName' => 'required_if:withdrawPaymentMethod,internal_transfer|string|max:255',
        // TIPS MNO validation
        'withdrawMnoProvider' => 'required_if:withdrawPaymentMethod,tips_mno|string|max:255',
        'withdrawPhoneNumber' => 'required_if:withdrawPaymentMethod,tips_mno|string|max:255',
        'withdrawWalletHolderName' => 'required_if:withdrawPaymentMethod,tips_mno|string|max:255',
        // TIPS Bank validation
        'withdrawBankCode' => 'required_if:withdrawPaymentMethod,tips_bank|string|max:255',
        'withdrawBankAccountNumber' => 'required_if:withdrawPaymentMethod,tips_bank|string|max:255',
        'withdrawBankAccountHolderName' => 'required_if:withdrawPaymentMethod,tips_bank|string|max:255',
        // Common validation for non-cash methods
        'withdrawReferenceNumber' => 'required_if:withdrawPaymentMethod,internal_transfer,tips_mno,tips_bank|string|max:255',
        'withdrawDate' => 'required_if:withdrawPaymentMethod,internal_transfer,tips_mno,tips_bank|date',
        'withdrawTime' => 'required_if:withdrawPaymentMethod,internal_transfer,tips_mno,tips_bank|date_format:H:i'
    ];

    protected $listeners = [
        'showUsersList' => 'showUsersList',
        'blockDepositsAccount' => 'blockDepositsAccountModal',
        'editDepositsAccount' => 'editDepositsAccountModal'
    ];

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        $this->loadStatistics();
        $this->loadAvailableProducts();
    }


    public function showDepositsBulkUploadPage()
    {
        if (!$this->authorize('export', 'You do not have permission to upload deposits data')) {
            return;
        }
        $this->selected = 12;
    }

    protected function loadStatistics()
    {
        try {
            // Total deposits balance (using major_category_code 1000 for deposits)
            $this->totalDeposits = AccountsModel::whereNotNull('client_number')
                ->where('major_category_code', 1000)
                ->where('client_number', '!=', '0000')
                ->sum('balance');

            // Active accounts
            $this->activeAccounts = AccountsModel::whereNotNull('client_number')
                ->where('major_category_code', 1000)
                ->where('status', 'ACTIVE')
                ->where('client_number', '!=', '0000')
                ->count();

            // Inactive accounts
            $this->inactiveAccounts = AccountsModel::whereNotNull('client_number')
                ->where('major_category_code', 1000)
                ->where('status', '!=', 'ACTIVE')
                ->where('client_number', '!=', '0000')
                ->count();

            // Total products
            $this->totalProducts = sub_products::where('major_category_code', 1000)
                ->where('status', 'ACTIVE')
                ->count();

        } catch (Exception $e) {
            Log::error('Error loading deposits statistics: ' . $e->getMessage());
            $this->errorMessage = 'Failed to load statistics. Please try again.';
        }
    }

    public function showDepositsFullReportPage()
    {
        if (!$this->authorize('view', 'You do not have permission to view deposits reports')) {
            return;
        }
        $this->selected = 11;
    }

    protected function loadAvailableProducts()
    {
        try {
            $this->availableProducts = sub_products::where('product_type', 3000)
                ->get();
        } catch (Exception $e) {
            Log::error('Error loading available products: ' . $e->getMessage());
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedProduct()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function setAccount($id){
        $account_number = AccountsModel::where('id',$id)->value('account_number');
        $this->accountSelected = $account_number;

        $this->product = AccountsModel::where('account_number', $account_number)->value('sub_category_code');
    }

    public function showAddDepositsAccountModal($selected){
        $randomNumber = rand(9000, 9999);
        $this->membershipNumber= str_pad($randomNumber, 4, '0', STR_PAD_LEFT);
        $this->selected = $selected;
        $this->showAddDepositsAccount = true;
    }

    public function closeShowAddDepositsAccount(){
        $this->resetData();
        $this->showAddDepositsAccount = false;
    }

    public function generate_account_number_two($branch_code, $product_code) {
        do {
            // Generate a 5-digit random number for the unique account identifier
            $unique_identifier = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

            // Concatenate branch code, unique identifier, and product code
            $partial_account_number = $branch_code . $unique_identifier . $product_code;

            // Calculate the checksum digit
            $checksum = (10 - $this->luhn_checksum($partial_account_number . '0')) % 10;

            // Form the final 12-digit account number
            $full_account_number = $partial_account_number . $checksum;

            // Check for uniqueness using Laravel's Eloquent model
            $is_unique = !AccountsModel::where('account_number', $full_account_number)->exists();

        } while (!$is_unique);

        return $full_account_number;
    }

    public function createNewAccount($major_category_code,$category_code,$sub_category_code,$account_name,$client_number )
    {
        //dd();

        // Generate account number
        $account_number = $this->generate_account_number(auth()->user()->branch, $sub_category_code);

        // Create a new account entry in the AccountsModel
        $account_number =

            [
            'account_use' => 'external',
            'institution_number' => 1,
            'branch_number' => auth()->user()->branch,
            'major_category_code' => $major_category_code,
            'category_code' => $category_code,
            'sub_category_code' => $this->product,
            'account_name' => $account_name,
            'client_number'=>$client_number,
            'account_number' => $account_number,
            'notes' => 'account on member on boarding ',
            'bank_id' => null,
            'mirror_account' => null,
            'account_level' => '3',
        ];

        return $account_number;
    }

    public function showIssueNewDepositsModal($selected){
        if($selected == 1) {
            $randomNumber = rand(9000, 9999);
            $this->membershipNumber = str_pad($randomNumber, 4, '0', STR_PAD_LEFT);
            $this->selected = $selected;
            $this->showCreateNewDepositsAccount = true;
        } elseif($selected == 2) {
            $randomNumber = rand(9000, 9999);
            $this->membershipNumber = str_pad($randomNumber, 4, '0', STR_PAD_LEFT);
            $this->selected = $selected;
            $this->showIssueNewDeposits = true;
        } else {
            $this->selected = $selected;
        }
    }

    public function closeShowIssueNewDeposits(){
        $this->resetData();
        $this->showIssueNewDeposits = false;
    }

    public function updatedDepositsAccount(){

        $DepositsAccountData = DepositsModel::select('membershipNumber', 'name', 'region', 'wilaya', 'email')
        ->where('id', '=', $this->DepositsAccount)
        ->get();

    foreach ($DepositsAccountData as $DepositsAccount){
        $this->membershipNumber=$DepositsAccount->membershipNumber;
        $this->name=$DepositsAccount->name;
        $this->region=$DepositsAccount->region;
        $this->wilaya=$DepositsAccount->wilaya;
        $this->email=$DepositsAccount->email;
        $this->status=$DepositsAccount->status;
    }

    }




    public function updateDepositsAccount(){

        $user = auth()->user();


        $data = [
            'membershipNumber' =>$this->membershipNumber,
            'name' =>$this->name,
            'region' =>$this->region,
            'wilaya' =>$this->wilaya,
            'email' =>$this->email
        ];

        $update_value = approvals::updateOrCreate(
            [
                'process_id' => $this->DepositsAccount,
                'user_id' => Auth::user()->id

                ],
            [
                'institution' => $this->DepositsAccount,
                'process_name' => 'editDepositsAccount',
                'process_description' => 'has edited a DepositsAccount',
                'approval_process_description' => 'has approved changes to a DepositsAccount',
                'process_code' => '02',
                'process_id' => $this->DepositsAccount,
                'process_status' => 'Pending',
                'user_id'  => Auth::user()->id,
                'team_id'  => $this->DepositsAccount,
                'edit_package'=> json_encode($data)
            ]
        );
        Session::flash('message', 'Awaiting approval');
        Session::flash('alert-class', 'alert-success');
        $this->resetData();
        $this->showAddDepositsAccount = false;
    }
    function luhn_checksum($number) {
        $digits = str_split($number);
        $sum = 0;
        $alt = false;
        for ($i = count($digits) - 1; $i >= 0; $i--) {
            $n = $digits[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = !$alt;
        }
        return $sum % 10;
    }
    function generate_account_number($branch_code, $product_code) {
        do {
            // Generate a 5-digit random number for the unique account identifier
            $unique_identifier = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

            // Concatenate branch code, unique identifier, and product code
            $partial_account_number = $branch_code . $unique_identifier . $product_code;

            // Calculate the checksum digit
            $checksum = (10 - $this->luhn_checksum($partial_account_number . '0')) % 10;

            // Form the final 12-digit account number
            $full_account_number = $partial_account_number . $checksum;

            // Check for uniqueness using Laravel's Eloquent model
            $is_unique = !AccountsModel::where('account_number', $full_account_number)->exists();

        } while (!$is_unique);

        return $full_account_number;
    }

    public function addDepositsAccount()
    {
        // Check if the deposits account already exists
        $existingAccount = AccountsModel::where('client_number', $this->member)
                            ->where('product_number', 2000)
                               ->where('sub_category_code', $this->product)
                                      ->exists();



        if ($existingAccount) {
            Session::flash('message_fail', 'Your account already exists!');
            Session::flash('alert-class', 'alert-success');
            return;
        }

        // Fetch the branch code, padded to 2 digits
        $branchCode = Auth::user()->branch;

        // Generate the new account number
        $accountNumber = $this->generate_account_number($branchCode, $this->product);

        // Get the full member name
        $memberName = ClientsModel::where('client_number', $this->member)
                                  ->selectRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name")
                                  ->value('full_name');

        $parent=AccountsModel::where('sub_category_code',$this->product)->first();

        $category = 'liability_accounts';

        //dd($this->member);

        // Prepare the data package for saving the new account
        $newAccountData = [
            'account_use' => 'external',
            'institution_number' => '1000',
            'branch_number' => Auth::user()->branch,
            'client_number' => $this->member,
            'product_number' => '2000',
            'sub_product_number'=>  $this->product,
            'major_category_code'=> $parent->major_category_code,
            'category_code'=>  $parent->category_code,
            'sub_category_code'=>  $parent->sub_category_code,
            'balance'=>  0,
            'account_name'=> $memberName,
            'account_number'=>$accountNumber,
            'account_level' => '3',
            'parent_account_number' => $parent->account_number,
            'type' => $category
        ];

        // Encode the data for approvals
        $editPackage = json_encode($newAccountData);

        // Create an approval record
        approvals::create([
            'institution' => '',
            'process_name' => 'createDepositsAccount',
            'process_description' => Auth::user()->name . ' has added a new deposits account for ' . $memberName,
            'approval_process_description' => 'has approved a new account',
            'process_code' => '04',
            'process_status' => 'Pending',
            'user_id' => Auth::user()->id,
            'team_id' => "",
            'edit_package' => $editPackage
        ]);

        // Reset the form data and close the modal
        $this->resetData2();
        $this->closeShowAddDepositsAccount();

        // Display success message
        Session::flash('message', 'The process has been completed!');
        Session::flash('alert-class', 'alert-success');
    }

    public function resetData2()
    {
        // Code to reset the data goes here
        // You can define the logic to reset the desired data properties or perform any necessary actions
        // For example:
        $this->member = null;
        $this->product = null;
        // Reset other data properties as needed
    }

    public function save()
    {
        // Start database transaction
        DB::beginTransaction();

        try {
            // Ensure all operations are within the transaction scope
            $bankAccountNumber = $this->bank;
            $selectedAccount = $this->accountSelected;
            $amount = (double) $this->amount;


            $institutionId = 1;
            $referenceNumber = time();


           // dd($bankAccountNumber, $selectedAccount );



            $debited_account = AccountsModel::where('account_number', $bankAccountNumber)->first();
            //$debited_account ='27186310028';
            $credited_account  =AccountsModel::where('account_number', $selectedAccount)->first();

            //dd($debited_account,$credited_account);


                   // debit suspense account
                   $data = [
                    'first_account' => $debited_account,
                    'second_account' => $credited_account,
                    'amount' => $amount,
                    'narration' => $this->notes,
                ];
              // dd($data);
              //  Ensure $this->transactionService is initialized
               $transactionServicex = new TransactionPostingService();

                $response = $transactionServicex->postTransaction($data);



            // Reset data after successful operation
            $this->resetData();

            // Commit the transaction
            DB::commit();

            // Flash success message
            Session::flash('message', 'Deposits has been successfully issued!');
            Session::flash('alert-class', 'alert-success');

            // Close modal or redirect
            $this->closeShowIssueNewDeposits();
        } catch (\Exception $e) {
            DB::rollBack();

            Session::flash('message', 'Transaction failed! Please try again.');
            Session::flash('alert-class', 'alert-danger');

        }
    }

    public function sendApproval($id,$msg,$code){



        approvals::create([
            'institution' => "",
            'process_name' => 'Deposit',
            'process_description' => $msg,
            'approval_process_description' => 'has approved a transaction',
            'process_code' => $code,
            'process_id' => $id,
            'process_status' => 'Pending',
            'user_id'  => Auth::user()->id,
            'team_id'  => ""
        ]);

    }

    public function resetData()
    {
        $this->member = '';
        $this->product = '';
        $this->accountSelected = '';
        $this->amount = '';
        $this->account_number = '';
        $this->notes = '';
        $this->bank = '';
        $this->reference_number = '';
    }

    public function menuItemClicked($tabId){
        $this->tab_id = $tabId;
        if($tabId == '1'){
            $this->title = 'Deposits list';
        }
        if($tabId == '2'){
            $this->title = 'Enter new DepositsAccount details';
        }
    }

    public function createNewDepositsAccount()
    {
        $this->showCreateNewDepositsAccount = true;
    }

    public function blockDepositsAccountModal($id)
    {
        $this->showDeleteDepositsAccount = true;
        $this->DepositsAccountSelected = $id;
    }

    public function editDepositsAccountModal($id)
    {
        $this->showEditDepositsAccount = true;
        $this->pendingDepositsAccount = $id;
        $this->DepositsAccount = $id;
        $this->pendingDepositsAccountname = DepositsModel::where('id',$id)->value('name');
        $this->updatedDepositsAccount();
    }

    public function closeModal(){
        $this->showCreateNewDepositsAccount = false;
        $this->showDeleteDepositsAccount = false;
        $this->showEditDepositsAccount = false;
    }

    public function confirmPassword(): void
    {
        // Check if password matches for logged-in user
        if (Hash::check($this->password, auth()->user()->password)) {
            //dd('password matches');
            $this->delete();
        } else {
            //dd('password does not match');
            Session::flash('message', 'This password does not match our records');
            Session::flash('alert-class', 'alert-warning');
        }
        $this->resetPassword();
    }

    public function resetPassword(): void
    {
        $this->password = null;
    }

    public function delete(): void
    {
        $user = User::where('id',$this->userSelected)->first();
        $action = '';
        if ($user) {

            if($this->permission == 'BLOCKED'){
                $action = 'blockUser';
            }
            if($this->permission == 'ACTIVE'){
                $action = 'activateUser';
            }
            if($this->permission == 'DELETED'){
                $action = 'deleteUser';
            }

            $update_value = approvals::updateOrCreate(
                [
                    'process_id' => $this->userSelected,
                    'user_id' => Auth::user()->id

                ],
                [
                    'institution' => null,
                    'process_name' => $action,
                    'process_description' => $this->permission.' user - '.$user->name,
                    'approval_process_description' => null,
                    'process_code' => '29',
                    'process_id' => $this->userSelected,
                    'process_status' => $this->permission,
                    'approval_status' => 'PENDING',
                    'user_id'  => Auth::user()->id,
                    'team_id'  => null,
                    'edit_package'=> null
                ]
            );


            // Delete the record
            //$node->delete();
            // Add your logic here for successful deletion
            Session::flash('message', 'Awaiting approval');
            Session::flash('alert-class', 'alert-success');

            $this->closeModal();
            $this->render();


        } else {
            // Handle case where record was not found
            // Add your logic here
            Session::flash('message', 'Node error');
            Session::flash('alert-class', 'alert-warning');
        }
    }

    public function render()
    {
        $this->loadAvailableProducts();
        return view('livewire.deposits.deposits', array_merge(
            $this->permissions,
            [
                'permissions' => $this->permissions
            ]
        ));
    }

    protected function getFilteredAccounts()
    {
        try {
            $query = AccountsModel::with(['client', 'shareProduct'])
                ->whereNotNull('client_number')
                ->where('product_number', 2000)
                ->where('client_number', '!=', '0000');

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('account_number', 'like', '%' . $this->search . '%')
                        ->orWhere('account_name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('client', function ($q) {
                            $q->where('client_number', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                                ->orWhere('last_name', 'like', '%' . $this->search . '%');
                        });
                });
            }

            if ($this->selectedProduct) {
                $query->where('product_number', $this->selectedProduct);
            }

            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }

            return $query->paginate(10);

        } catch (Exception $e) {
            Log::error('Error filtering accounts: ' . $e->getMessage());
            $this->errorMessage = 'Failed to filter accounts. Please try again.';
            return collect();
        }
    }

    public function createDepositsAccount()
    {
        if (!$this->authorize('create', 'You do not have permission to create deposits accounts')) {
            return;
        }

        $this->validate([
            'clientNumber' => 'required|exists:clients,client_number',
            'productId' => 'required|exists:sub_products,id'
        ]);

        try {
            // Check if the deposits account already exists
            $existingAccount = AccountsModel::where('client_number', $this->clientNumber)
                ->where('product_number', 3000)
                ->where('sub_product_number', $this->productId)
                ->exists();

            if ($existingAccount) {
                $this->addError('productId', 'This member already has an account for this product.');
                return;
            }

            // Fetch the branch code
            $branchCode = Auth::user()->branch;

            // Generate the new account number
            $accountNumber = $this->generate_account_number($branchCode, $this->productId);

            // Get the full member name
            $memberName = ClientsModel::where('client_number', $this->clientNumber)
                ->selectRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name")
                ->value('full_name');

            // Get parent account details from the selected product
            $parent = AccountsModel::where('sub_category_code', $this->productId)->first();

            if (!$parent) {
                $this->addError('productId', 'Parent account not found for this product.');
                return;
            }

            // Prepare the data package for saving the new account
            $newAccountData = [
                'account_use' => 'external',
                'institution_number' => '1000',
                'branch_number' => Auth::user()->branch,
                'client_number' => $this->clientNumber,
                'product_number' => '3000',
                'sub_product_number' => $this->productId,
                'major_category_code' => $parent->major_category_code,
                'category_code' => $parent->category_code,
                'sub_category_code' => $parent->sub_category_code,
                'balance' => 0,
                'account_name' => $memberName,
                'account_number' => $accountNumber,
                'account_level' => '3',
                'parent_account_number' => $parent->account_number,
                'type' => 'liability_accounts',
                'status' => 'PENDING'
            ];

            // Encode the data for approvals
            $editPackage = json_encode($newAccountData);

            // Create an approval record
            approvals::create([
                'institution' => '',
                'process_name' => 'createDepositsAccount',
                'process_description' => Auth::user()->name . ' has added a new deposits account for ' . $memberName,
                'approval_process_description' => 'has approved a new account',
                'process_code' => '04',
                'process_status' => 'Pending',
                'user_id' => Auth::user()->id,
                'team_id' => "",
                'edit_package' => $editPackage
            ]);

            Log::info('Deposits account creation submitted for approval', [
                'account_number' => $accountNumber,
                'client_number' => $this->clientNumber,
                'product_id' => $this->productId
            ]);

            $this->showCreateNewDepositsAccount = false;
            $this->resetForm();
            Session::flash('message', 'Deposits account has been submitted for approval!');
            Session::flash('alert-class', 'alert-success');

        } catch (Exception $e) {
            Log::error('Error creating deposits account: ' . $e->getMessage());
            $this->addError('productId', 'Failed to create deposits account. Please try again.');
        }
    }

    public function resetForm()
    {
        $this->reset([
            'clientNumber',
            'productId',
            'accountNumber',
            'accountName',
            'balance'
        ]);
        $this->resetErrorBag();
    }

    public function showCreateNewDepositsAccount()
    {
        if (!$this->authorize('create', 'You do not have permission to create deposits accounts')) {
            return;
        }
        $this->reset([
            'clientNumber',
            'productId',
            'accountNumber',
            'accountName',
            'balance'
        ]);
        $this->resetErrorBag();
        $this->showCreateNewDepositsAccount = true;
    }

    public function validateDepositsMemberNumber()
    {
        if (strlen($this->clientNumber) === 5) {
            // Query member from database
            $member = ClientsModel::where('client_number', str_pad($this->clientNumber, 5, '0', STR_PAD_LEFT))->first();

            if ($member) {
                $this->depositsMemberDetails = [
                    'first_name' => $member->first_name,
                    'last_name' => $member->last_name,
                    'client_number' => $member->client_number,
                    'phone_number' => $member->phone_number,
                    'email' => $member->email,
                ];
                $this->resetErrorBag('clientNumber');
            } else {
                $this->depositsMemberDetails = null;
                $this->addError('clientNumber', 'Client not found. Please check the client number.');
            }
        } else {
            $this->depositsMemberDetails = null;
            if (strlen($this->clientNumber) > 0 && strlen($this->clientNumber) < 5) {
                $this->addError('clientNumber', 'Client number must be 5 digits.');
            } else {
                $this->resetErrorBag('clientNumber');
            }
        }
    }

    public function showReceiveDepositsModal()
    {
        if (!$this->authorize('deposit', 'You do not have permission to process deposits')) {
            return;
        }
        $this->reset([
            'membershipNumber',
            'selectedAccount',
            'amount',
            'paymentMethod',
            'selectedBank',
            'referenceNumber',
            'depositDate',
            'depositTime',
            'depositorName',
            'narration',
            'verifiedMember',
            'memberAccounts',
            'selectedBankDetails'
        ]);
        $this->resetErrorBag();
        $this->showReceiveDepositsModal = true;
    }

    public function verifyMembership()
    {
        $this->validate([
            'membershipNumber' => 'required|min:1'
        ]);

        try {
            $verificationService = app(MembershipVerificationService::class);
            $result = $verificationService->verifyMembership($this->membershipNumber);

            if ($result['exists'] === true) {
                $this->verifiedMember = $result['member'];
                $this->memberAccounts = AccountsModel::where('client_number', $this->membershipNumber)
                    ->where('product_number', '2000')
                    ->where('status', 'ACTIVE')
                    ->get();
                $this->bankAccounts = BankAccount::where('status', 'ACTIVE')->get();
                
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'success',
                    'message' => $result['message']
                ]);
            } else {
                $this->addError('membershipNumber', $result['message']);
                $this->verifiedMember = null;
                $this->memberAccounts = [];
            }
        } catch (Exception $e) {
            $this->addError('membershipNumber', 'Failed to verify membership. Please try again.');
            Log::error('Membership verification error: ' . $e->getMessage());
            $this->verifiedMember = null;
            $this->memberAccounts = [];
        }
    }

    public function updatedPaymentMethod()
    {
       
        if ($this->paymentMethod === 'cash') {
            $this->referenceNumber = 'CASH-' . strtoupper(uniqid());
            $this->depositDate = now()->format('Y-m-d');
            $this->depositTime = now()->format('H:i');
        }
    }

    public function updatedSelectedBank()
    {
        if ($this->selectedBank) {
            $this->selectedBankDetails = BankAccount::find($this->selectedBank);
        }
    }

    public function submitReceiveDeposits()
    {
        if (!$this->authorize('deposit', 'You do not have permission to process deposits')) {
            return;
        }
        DB::beginTransaction();
        try {
            $memberAccount = AccountsModel::where('account_number', $this->selectedAccount)->first();
            if(!$memberAccount){
                throw new \Exception('Member account not found.');
            }

            if($this->paymentMethod === 'bank'){
                $this->validate([
                    'depositDate' => 'required|date',
                    'depositTime' => 'required|date_format:H:i',
                    'selectedBank' => 'required',
                    'referenceNumber' => 'required|string|max:255',
                    'narration' => 'required|string|max:255',
                    'amount' => 'required|numeric|min:0',
                    'depositorName' => 'required|string|max:255',
                    'paymentMethod' => 'required|string|in:bank,cash'
                ]);

                if (!empty($this->selectedBankDetails->internal_mirror_account_number) && !empty($this->selectedAccount)) {
                    $totalAmount = $this->amount;
                    
                    // Post the transaction using TransactionPostingService
                    $transactionService = new TransactionPostingService();
                    $transactionData = [
                        'first_account' => $this->selectedBankDetails->internal_mirror_account_number, // Debit account 
                        'second_account' => $this->selectedAccount, // Credit account 
                        'amount' => $totalAmount,
                        'narration' => 'Deposits deposit : ' . $this->amount . ' : ' . $this->depositorName . ' : ' . $this->selectedBankDetails->bank_name . ' : ' . $this->referenceNumber,
                        'action' => 'deposits_deposit'
                    ];

                    Log::info('Posting deposits deposit transaction', [
                        'transaction_data' => $transactionData
                    ]);

                    $result = $transactionService->postTransaction($transactionData);
                    
                    if ($result['status'] !== 'success') {
                        Log::error('Transaction posting failed', [
                            'error' => $result['message'] ?? 'Unknown error',
                            'transaction_data' => $transactionData
                        ]);
                        throw new \Exception('Failed to post transaction: ' . ($result['message'] ?? 'Unknown error'));
                    }

                    Log::info('Transaction posted successfully', [
                        'transaction_reference' => $result['reference'] ?? null,
                        'amount' => $totalAmount
                    ]);
                }
            }

            if($this->paymentMethod === 'cash'){
                $this->validate([
                    'depositDate' => 'required|date',
                    'depositTime' => 'required|date_format:H:i',
                    'referenceNumber' => 'required|string|max:255',
                    'narration' => 'required|string|max:255',
                    'amount' => 'required|numeric|min:0',
                    'depositorName' => 'required|string|max:255',
                    'paymentMethod' => 'required|string|in:bank,cash'
                ]);

                // Handle cash deposit transaction
                $transactionService = new TransactionPostingService();
                $transactionData = [
                    'first_account' => '010000010024', // Cash account
                    'second_account' => $this->selectedAccount, // Credit member's account
                    'amount' => $this->amount,
                    'narration' => 'Cash deposits deposit: ' . $this->amount . ' : ' . $this->depositorName . ' : ' . $this->referenceNumber,
                    'action' => 'deposits_deposit_by_cash'
                ];

                $result = $transactionService->postTransaction($transactionData);
                
                if ($result['status'] !== 'success') {
                    throw new \Exception('Failed to post cash transaction: ' . ($result['message'] ?? 'Unknown error'));
                }
            }

            // Generate receipt after successful transaction
            $this->receiptData = $this->generateReceiptData($memberAccount);
            
            // Create receipt record in database
            $this->createReceiptRecord($memberAccount, $this->amount);
            
            session()->flash('success', 'Deposits received successfully.');
            DB::commit();
            $this->showReceiveDepositsModal = false;
            $this->showReceiptModal = true;
            
        } catch(\Exception $e) {
            DB::rollBack();
            Log::error('Error receiving deposits: ' . $e->getMessage());
            session()->flash('error', 'Error receiving deposits');
            return;
        }
    }
    
    private function generateReceiptData($memberAccount)
    {
        $receiptNumber = 'RCP-DEP-' . strtoupper(uniqid());
        $transactionDate = now();
        
        return [
            'receipt_number' => $receiptNumber,
            'transaction_date' => $transactionDate->format('d/m/Y H:i:s'),
            'member_name' => $this->verifiedMember['name'] ?? 'N/A',
            'member_number' => $this->membershipNumber,
            'account_number' => $this->selectedAccount,
            'account_name' => $memberAccount->account_name,
            'amount' => number_format($this->amount, 2),
            'payment_method' => ucfirst($this->paymentMethod),
            'depositor_name' => $this->depositorName,
            'narration' => $this->narration,
            'reference_number' => $this->referenceNumber,
            'bank_name' => $this->selectedBankDetails->bank_name ?? 'Cash',
            'processed_by' => auth()->user()->name,
            'branch' => auth()->user()->branch_id ?? 1,
            'currency' => 'TZS',
            'transaction_type' => 'Deposits Deposit',
            'balance_after' => number_format($memberAccount->balance + $this->amount, 2)
        ];
    }
    
    private function createReceiptRecord($memberAccount, $amount)
    {
        $receiptNumber = 'RCP-DEP-' . strtoupper(uniqid());
        
        \App\Models\Receipt::create([
            'receipt_number' => $receiptNumber,
            'transaction_id' => null, // Will be updated when transaction is created
            'account_id' => $memberAccount->id,
            'member_number' => $this->membershipNumber,
            'member_name' => $this->verifiedMember['name'] ?? 'N/A',
            'amount' => $amount,
            'currency' => 'TZS',
            'payment_method' => $this->paymentMethod,
            'depositor_name' => $this->depositorName,
            'narration' => $this->narration,
            'reference_number' => $this->referenceNumber,
            'bank_name' => $this->selectedBankDetails->bank_name ?? 'Cash',
            'processed_by' => auth()->id(),
            'branch' => auth()->user()->branch_id ?? 1,
            'transaction_type' => 'Deposits Deposit',
            'status' => 'GENERATED',
            'generated_at' => now(),
            'printed_at' => null,
            'metadata' => [
                'balance_before' => $memberAccount->balance,
                'balance_after' => $memberAccount->balance + $amount,
                'deposit_date' => $this->depositDate ?? now()->format('Y-m-d'),
                'deposit_time' => $this->depositTime ?? now()->format('H:i'),
            ]
        ]);
        
        // Update the receipt number in the receipt data
        $this->receiptData['receipt_number'] = $receiptNumber;
    }
    
    public function closeReceiptModal()
    {
        $this->showReceiptModal = false;
        $this->receiptData = null;
        $this->resetForm();
    }
    
    public function printReceipt()
    {
        if ($this->receiptData) {
            $this->dispatchBrowserEvent('printReceipt', [
                'receiptData' => $this->receiptData
            ]);
        }
    }

    public function showWithdrawDepositsModal()
    {
        if (!$this->authorize('withdraw', 'You do not have permission to process deposits withdrawals')) {
            return;
        }
        $this->reset([
            'withdrawMembershipNumber',
            'withdrawSelectedAccount',
            'withdrawAmount',
            'withdrawPaymentMethod',
            'withdrawSelectedBank',
            'withdrawReferenceNumber',
            'withdrawDate',
            'withdrawTime',
            'withdrawerName',
            'withdrawNarration',
            'withdrawVerifiedMember',
            'withdrawMemberAccounts',
            'withdrawSelectedBankDetails',
            'withdrawSelectedAccountBalance'
        ]);
        $this->resetErrorBag();
        $this->showWithdrawDepositsModal = true;
    }

    public function verifyWithdrawMembership()
    {
        $this->validate([
            'withdrawMembershipNumber' => 'required|min:1'
        ]);

        try {
            $verificationService = app(MembershipVerificationService::class);
            $result = $verificationService->verifyMembership($this->withdrawMembershipNumber);

            if ($result['exists'] === true) {
                $this->withdrawVerifiedMember = $result['member'];
                $this->withdrawMemberAccounts = AccountsModel::where('client_number', $this->withdrawMembershipNumber)
                    ->where('product_number', '2000')
                    ->where('status', 'ACTIVE')
                    ->get();
                $this->withdrawBankAccounts = BankAccount::where('status', 'ACTIVE')->get();
                
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'success',
                    'message' => $result['message']
                ]);
            } else {
                $this->addError('withdrawMembershipNumber', $result['message']);
                $this->withdrawVerifiedMember = null;
                $this->withdrawMemberAccounts = [];
            }
        } catch (Exception $e) {
            $this->addError('withdrawMembershipNumber', 'Failed to verify membership. Please try again.');
            Log::error('Withdrawal membership verification error: ' . $e->getMessage());
            $this->withdrawVerifiedMember = null;
            $this->withdrawMemberAccounts = [];
        }
    }

    public function updatedWithdrawPaymentMethod()
    {
        if ($this->withdrawPaymentMethod === 'cash') {
            $this->withdrawReferenceNumber = 'CASH-' . strtoupper(uniqid());
            $this->withdrawDate = now()->format('Y-m-d');
            $this->withdrawTime = now()->format('H:i');
        } elseif ($this->withdrawPaymentMethod === 'internal_transfer') {
            $this->withdrawReferenceNumber = 'IFT-' . strtoupper(uniqid());
            $this->withdrawDate = now()->format('Y-m-d');
            $this->withdrawTime = now()->format('H:i');
        } elseif ($this->withdrawPaymentMethod === 'tips_mno') {
            $this->withdrawReferenceNumber = 'TIPS-MNO-' . strtoupper(uniqid());
            $this->withdrawDate = now()->format('Y-m-d');
            $this->withdrawTime = now()->format('H:i');
        } elseif ($this->withdrawPaymentMethod === 'tips_bank') {
            $this->withdrawReferenceNumber = 'TIPS-BANK-' . strtoupper(uniqid());
            $this->withdrawDate = now()->format('Y-m-d');
            $this->withdrawTime = now()->format('H:i');
        }
    }

    public function updatedWithdrawSelectedBank()
    {
        if ($this->withdrawSelectedBank) {
            $this->withdrawSelectedBankDetails = BankAccount::find($this->withdrawSelectedBank);
        }
    }

    public function updatedWithdrawSelectedAccount()
    {
        if ($this->withdrawSelectedAccount) {
            $account = AccountsModel::where('account_number', $this->withdrawSelectedAccount)->first();
            if ($account) {
                $this->withdrawSelectedAccountBalance = $account->balance;
            }
        }
    }

    public function submitWithdrawDeposits()
    {
        if (!$this->authorize('withdraw', 'You do not have permission to process deposits withdrawals')) {
            return;
        }
        try {
            // Validate basic withdrawal requirements
            $this->validate([
                'withdrawSelectedAccount' => 'required|min:1',
                'withdrawAmount' => 'required|numeric|min:0.01',
                'withdrawPaymentMethod' => 'required|in:cash,internal_transfer,tips_mno,tips_bank',
                'withdrawNarration' => 'required|string|max:255'
            ]);

            // Check if account has sufficient balance
            $account = AccountsModel::where('account_number', $this->withdrawSelectedAccount)->first();
            if (!$account) {
                throw new \Exception('Account not found.');
            }

            if ($account->balance < $this->withdrawAmount) {
                $this->addError('withdrawAmount', 'Insufficient balance. Available balance: ' . number_format($account->balance, 2));
                return;
            }

            // Process withdrawal based on payment method
            switch ($this->withdrawPaymentMethod) {
                case 'cash':
                    $this->processCashWithdrawal();
                    break;
                case 'internal_transfer':
                    $this->processInternalTransferWithdrawal();
                    break;
                case 'tips_mno':
                    $this->processTipsMnoWithdrawal();
                    break;
                case 'tips_bank':
                    $this->processTipsBankWithdrawal();
                    break;
                default:
                    throw new \Exception('Invalid withdrawal method.');
            }

            $this->showWithdrawDepositsModal = false;
            $this->resetWithdrawForm();
            session()->flash('success', 'Deposits withdrawal processed successfully.');

        } catch (Exception $e) {
            Log::error('Error processing deposits withdrawal: ' . $e->getMessage());
            session()->flash('error', 'Failed to process withdrawal. Please try again.');
        }
    }

    private function processCashWithdrawal()
    {
        // Auto-generate reference number if not provided
        if (empty($this->withdrawReferenceNumber)) {
            $this->withdrawReferenceNumber = 'DCASH-' . date('YmdHis') . '-' . rand(1000, 9999);
        }
        
        // Set current date and time automatically
        $this->withdrawDate = date('Y-m-d');
        $this->withdrawTime = date('H:i');

        // Get cash in safe account (this should be configured in the system)
        $cashInSafeAccount = AccountsModel::where('account_name', 'LIKE', '%cash in safe%')
            ->orWhere('account_name', 'LIKE', '%cash%')
            ->where('status', 'ACTIVE')
            ->first();

        if (!$cashInSafeAccount) {
            throw new \Exception('Cash in safe account not found. Please contact administrator.');
        }

        // Post the cash withdrawal transaction
        $transactionService = new TransactionPostingService();
        $transactionData = [
            'first_account' => $this->withdrawSelectedAccount, // Debit member's account
            'second_account' => $cashInSafeAccount->account_number, // Credit cash in safe account
            'amount' => $this->withdrawAmount,
            'narration' => 'Cash withdrawal: ' . $this->withdrawAmount . ' : ' . ($this->withdrawVerifiedMember['name'] ?? 'Member') . ' : ' . $this->withdrawReferenceNumber,
            'action' => 'cash_withdrawal'
        ];

        $result = $transactionService->postTransaction($transactionData);
        
        if ($result['status'] !== 'success') {
            throw new \Exception('Failed to post cash withdrawal transaction: ' . ($result['message'] ?? 'Unknown error'));
        }

        Log::info('Cash withdrawal processed successfully', [
            'account' => $this->withdrawSelectedAccount,
            'amount' => $this->withdrawAmount,
            'withdrawer' => $this->withdrawVerifiedMember['name'] ?? 'Member',
            'reference' => $this->withdrawReferenceNumber
        ]);
    }

    private function processInternalTransferWithdrawal()
    {
        $this->validate([
            'withdrawNbcAccount' => 'required|string|max:255',
            'withdrawAccountHolderName' => 'required|string|max:255'
        ]);
        
        // Auto-generate reference number if not provided
        if (empty($this->withdrawReferenceNumber)) {
            $this->withdrawReferenceNumber = 'DINT-' . date('YmdHis') . '-' . rand(1000, 9999);
        }
        
        // Set current date and time automatically
        $this->withdrawDate = date('Y-m-d');
        $this->withdrawTime = date('H:i');

        // Get cash at NBC account (this should be configured in the system)
        $cashAtNbcAccount = AccountsModel::where('account_name', 'LIKE', '%cash at NBC%')
            ->orWhere('account_name', 'LIKE', '%NBC%')
            ->where('status', 'ACTIVE')
            ->first();

        if (!$cashAtNbcAccount) {
            throw new \Exception('Cash at NBC account not found. Please contact administrator.');
        }

        // Process internal fund transfer using NBC API
        $internalTransferService = new \App\Services\NbcPayments\InternalFundTransferService();
        
        $transferData = [
            'debitAccount' => $cashAtNbcAccount->account_number, // SACCO's NBC account
            'creditAccount' => $this->withdrawNbcAccount, // Member's NBC account
            'amount' => $this->withdrawAmount,
            'debitCurrency' => 'TZS',
            'creditCurrency' => 'TZS',
            'narration' => 'Internal transfer: ' . $this->withdrawNarration,
            'channelId' => config('services.nbc_internal_fund_transfer.channel_id'),
            'channelRef' => $this->withdrawReferenceNumber,
            'pyrName' => $this->withdrawAccountHolderName
        ];

        $result = $internalTransferService->processInternalTransfer($transferData);

        if (!$result['success']) {
            throw new \Exception('Internal transfer failed: ' . ($result['message'] ?? 'Unknown error'));
        }

        // Post the internal transaction in our system
        $transactionService = new TransactionPostingService();
        $transactionData = [
            'first_account' => $this->withdrawSelectedAccount, // Debit member's savings account
            'second_account' => $cashAtNbcAccount->account_number, // Credit cash at NBC account
            'amount' => $this->withdrawAmount,
            'narration' => 'Internal transfer withdrawal: ' . $this->withdrawAmount . ' to ' . $this->withdrawNbcAccount . ' : ' . $this->withdrawReferenceNumber,
            'action' => 'internal_transfer_withdrawal'
        ];

        $transactionResult = $transactionService->postTransaction($transactionData);
        
        if ($transactionResult['status'] !== 'success') {
            throw new \Exception('Failed to post internal transfer transaction: ' . ($transactionResult['message'] ?? 'Unknown error'));
        }

        Log::info('Internal transfer withdrawal processed successfully', [
            'member_account' => $this->withdrawSelectedAccount,
            'nbc_account' => $this->withdrawNbcAccount,
            'amount' => $this->withdrawAmount,
            'reference' => $this->withdrawReferenceNumber,
            'nbc_reference' => $result['data']['hostReferenceCbs'] ?? null
        ]);
    }

    private function processTipsMnoWithdrawal()
    {
        $this->validate([
            'withdrawMnoProvider' => 'required|string|max:255',
            'withdrawPhoneNumber' => 'required|string|max:255',
            'withdrawWalletHolderName' => 'required|string|max:255'
        ]);
        
        // Auto-generate reference number if not provided
        if (empty($this->withdrawReferenceNumber)) {
            $this->withdrawReferenceNumber = 'DMNO-' . date('YmdHis') . '-' . rand(1000, 9999);
        }
        
        // Set current date and time automatically
        $this->withdrawDate = date('Y-m-d');
        $this->withdrawTime = date('H:i');

        // Get cash at NBC account
        $cashAtNbcAccount = AccountsModel::where('account_name', 'LIKE', '%cash at NBC%')
            ->orWhere('account_name', 'LIKE', '%NBC%')
            ->where('status', 'ACTIVE')
            ->first();

        if (!$cashAtNbcAccount) {
            throw new \Exception('Cash at NBC account not found. Please contact administrator.');
        }

        // Process TIPS MNO transfer using NBC API
        $nbcPaymentService = new \App\Services\NbcPayments\NbcPaymentService();
        $nbcLookupService = new \App\Services\NbcPayments\NbcLookupService();

        // First, perform lookup
        $lookupResult = $nbcLookupService->bankToWalletLookup(
            $this->withdrawPhoneNumber,
            $this->withdrawMnoProvider,
            $cashAtNbcAccount->account_number,
            $this->withdrawAmount,
            'PERSON'
        );

        if (!$lookupResult['success']) {
            throw new \Exception('TIPS lookup failed: ' . ($lookupResult['message'] ?? 'Unknown error'));
        }

        // Then process the transfer
        $transferResult = $nbcPaymentService->processBankToWalletTransfer(
            $lookupResult['data'],
            $cashAtNbcAccount->account_number,
            $this->withdrawAmount,
            $this->withdrawPhoneNumber,
            time(), // initiatorId
            'TIPS MNO transfer: ' . $this->withdrawNarration
        );

        if (!$transferResult['success']) {
            throw new \Exception('TIPS MNO transfer failed: ' . ($transferResult['message'] ?? 'Unknown error'));
        }

        // Post the transaction in our system
        $transactionService = new TransactionPostingService();
        $transactionData = [
            'first_account' => $this->withdrawSelectedAccount, // Debit member's savings account
            'second_account' => $cashAtNbcAccount->account_number, // Credit cash at NBC account
            'amount' => $this->withdrawAmount,
            'narration' => 'TIPS MNO withdrawal: ' . $this->withdrawAmount . ' to ' . $this->withdrawPhoneNumber . ' (' . $this->withdrawMnoProvider . ') : ' . $this->withdrawReferenceNumber,
            'action' => 'tips_mno_withdrawal'
        ];

        $transactionResult = $transactionService->postTransaction($transactionData);
        
        if ($transactionResult['status'] !== 'success') {
            throw new \Exception('Failed to post TIPS MNO transaction: ' . ($transactionResult['message'] ?? 'Unknown error'));
        }

        Log::info('TIPS MNO withdrawal processed successfully', [
            'member_account' => $this->withdrawSelectedAccount,
            'phone_number' => $this->withdrawPhoneNumber,
            'mno_provider' => $this->withdrawMnoProvider,
            'amount' => $this->withdrawAmount,
            'reference' => $this->withdrawReferenceNumber,
            'tips_reference' => $transferResult['engineRef'] ?? null
        ]);
    }

    private function processTipsBankWithdrawal()
    {
        $this->validate([
            'withdrawBankCode' => 'required|string|max:255',
            'withdrawBankAccountNumber' => 'required|string|max:255',
            'withdrawBankAccountHolderName' => 'required|string|max:255'
        ]);
        
        // Auto-generate reference number if not provided
        if (empty($this->withdrawReferenceNumber)) {
            $this->withdrawReferenceNumber = 'DTIPS-' . date('YmdHis') . '-' . rand(1000, 9999);
        }
        
        // Set current date and time automatically
        $this->withdrawDate = date('Y-m-d');
        $this->withdrawTime = date('H:i');

        // Get cash at NBC account
        $cashAtNbcAccount = AccountsModel::where('account_name', 'LIKE', '%cash at NBC%')
            ->orWhere('account_name', 'LIKE', '%NBC%')
            ->where('status', 'ACTIVE')
            ->first();

        if (!$cashAtNbcAccount) {
            throw new \Exception('Cash at NBC account not found. Please contact administrator.');
        }

        // Process TIPS Bank transfer using NBC API
        $nbcPaymentService = new \App\Services\NbcPayments\NbcPaymentService();
        $nbcLookupService = new \App\Services\NbcPayments\NbcLookupService();

        // First, perform lookup
        $lookupResult = $nbcLookupService->bankToBankLookup(
            $this->withdrawBankAccountNumber,
            $this->withdrawBankCode,
            $cashAtNbcAccount->account_number,
            $this->withdrawAmount,
            'PERSON'
        );

        if (!$lookupResult['success']) {
            throw new \Exception('TIPS bank lookup failed: ' . ($lookupResult['message'] ?? 'Unknown error'));
        }

        // Then process the transfer
        $transferResult = $nbcPaymentService->processBankToBankTransfer(
            $lookupResult['data'],
            $cashAtNbcAccount->account_number,
            $this->withdrawAmount,
            $this->withdrawPhoneNumber ?? '255000000000', // Default phone number if not provided
            time(), // initiatorId
            'TIPS Bank transfer: ' . $this->withdrawNarration,
            'FTLC'
        );

        if (!$transferResult['success']) {
            throw new \Exception('TIPS bank transfer failed: ' . ($transferResult['message'] ?? 'Unknown error'));
        }

        // Post the transaction in our system
        $transactionService = new TransactionPostingService();
        $transactionData = [
            'first_account' => $this->withdrawSelectedAccount, // Debit member's savings account
            'second_account' => $cashAtNbcAccount->account_number, // Credit cash at NBC account
            'amount' => $this->withdrawAmount,
            'narration' => 'TIPS Bank withdrawal: ' . $this->withdrawAmount . ' to ' . $this->withdrawBankAccountNumber . ' (' . $this->withdrawBankCode . ') : ' . $this->withdrawReferenceNumber,
            'action' => 'tips_bank_withdrawal'
        ];

        $transactionResult = $transactionService->postTransaction($transactionData);
        
        if ($transactionResult['status'] !== 'success') {
            throw new \Exception('Failed to post TIPS bank transaction: ' . ($transactionResult['message'] ?? 'Unknown error'));
        }

        Log::info('TIPS Bank withdrawal processed successfully', [
            'member_account' => $this->withdrawSelectedAccount,
            'bank_account' => $this->withdrawBankAccountNumber,
            'bank_code' => $this->withdrawBankCode,
            'amount' => $this->withdrawAmount,
            'reference' => $this->withdrawReferenceNumber,
            'tips_reference' => $transferResult['engineRef'] ?? null
        ]);
    }

    public function resetWithdrawForm()
    {
        $this->reset([
            'withdrawMembershipNumber',
            'withdrawSelectedAccount',
            'withdrawAmount',
            'withdrawPaymentMethod',
            'withdrawSelectedBank',
            'withdrawReferenceNumber',
            'withdrawDate',
            'withdrawTime',
            'withdrawerName',
            'withdrawNarration',
            'withdrawVerifiedMember',
            'withdrawMemberAccounts',
            'withdrawSelectedBankDetails',
            'withdrawSelectedAccountBalance',
            'withdrawNbcAccount',
            'withdrawAccountHolderName',
            'withdrawMnoProvider',
            'withdrawPhoneNumber',
            'withdrawWalletHolderName',
            'withdrawBankCode',
            'withdrawBankAccountNumber',
            'withdrawBankAccountHolderName'
        ]);
        $this->resetErrorBag();
    }

    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'deposits';
    }
}
