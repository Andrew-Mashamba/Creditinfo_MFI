<?php

namespace App\Http\Livewire\Savings;

use App\Models\sub_products;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

use App\Models\SavingsModel;
use App\Models\approvals;
use App\Models\AccountsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\TransactionPostingService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\ClientsModel;
use App\Services\AccountCreationService;

use App\Models\TeamUser;

use Livewire\WithFileUploads;
use App\Models\issured_savings;

use App\Models\general_ledger;

use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use App\Models\Client;

use App\Models\general_ledger as GeneralLedgerModel;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Exception;
use App\Services\MembershipVerificationService;
use App\Models\BankAccount;
use App\Traits\Livewire\WithModulePermissions;

class Savings extends Component
{
    use WithPagination;
    use WithFileUploads;
    use WithModulePermissions;

    public $tab_id = '10';
    public $title = 'Savings Management';

    // Dashboard Properties
    public $totalSavings = 0;
    public $activeAccounts = 0;
    public $inactiveAccounts = 0;
    public $totalProducts = 0;
    public $recentTransactions = [];
    public $monthlySavings = [];
    public $topSavers = [];
    public $savingsByProduct = [];

    // Filter Properties
    public $search = '';
    public $selectedProduct = '';
    public $statusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Modal Properties
    public $showIssueNewSavings = false;
    public $showEditSavingsAccount = false;
    public $showDeleteSavingsAccount = false;
    public $showPendingTransactions = false;
    public $showAdjustBalance = false;
    public $showAuditLogs = false;

    // Form Properties
    public $member;
    public $product;
    public $number_of_savings;
    public $linked_savings_account;
    public $account_number;

    public $nominal_price;
    public $selected = 10;
    public $accountSelected;
    public $sub_product_number;
    public $savingsAvailable;
    public $amount;
    public $notes;
    public $bank;

    public $memberDetails;
    public $memberName;
    public $reference_number;
    public $availableProducts = [];

    public $activeSavingsCount;
    public $inactiveSavingsCount;

    public $name;
    public $region;
    public $wilaya;
    public $membershipNumber;
    public $parentSavingsAccount;
    public $pendingSavingsAccount;
    public $pendingSavingsAccountname;
    public $SavingsAccount;
    public $showAddSavingsAccount;

    public $email;
    public $Savingsstatus;
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
    public $showCreateNewSavingsAccount = false;

    // Form Inputs
    public $clientNumber;
    public $productId;
    public $accountNumber;
    public $accountName;
    public $balance;

    // Receive Savings Properties
    public $showReceiveSavingsModal = false;
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

    // Withdraw Savings Properties
    public $showWithdrawSavingsModal = false;
    public $withdrawMembershipNumber;
    public $withdrawSelectedAccount;
    public $withdrawAmount;
    public $withdrawPaymentMethod = 'cash'; // 'cash', 'internal_transfer', 'tips_mno', 'tips_bank'
    public $withdrawSelectedBank;
    public $withdrawSourceAccount;
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
    
    // OTP Properties
    public $withdrawOtpCode = '';
    public $generatedWithdrawOTP = '';
    public $withdrawOtpSent = false;
    public $withdrawOtpSentTime = null;
    public $withdrawOtpVerified = false;

    // Pay Loan Properties
    public $showPayLoanModal = false;
    public $loanSearchType = 'loan_id'; // 'loan_id' or 'member_number'
    public $loanSearchValue = '';
    public $foundLoans = []; // All found loans for selection
    public $selectedLoan = null;
    public $payLoanSourceAccount = '';
    public $payLoanSourceAccountBalance = 0;
    public $loanPaymentAmount = '';
    public $loanPaymentNarration = '';
    public $memberSavingsAccounts = [];

    // Transfer to Deposits Properties
    public $showTransferToDepositsModal = false;

    // Test Modal Property
    public $showTestModal = false;

    // Source Member & Account
    public $transferSourceMemberNumber = '';
    public $transferSourceVerifiedMember = null;
    public $transferSourceAccount = '';
    public $transferSourceAccountBalance = 0;
    public $transferSourceAccountType = ''; // 'savings' or 'deposit'
    public $transferSourceMemberAccounts = []; // All accounts (savings + deposits)

    // Destination Member & Account
    public $transferDestinationMemberNumber = '';
    public $transferDestinationVerifiedMember = null;
    public $transferDestinationAccount = '';
    public $transferDestinationAccountType = ''; // 'savings' or 'deposit'
    public $transferDestinationMemberAccounts = []; // All accounts (savings + deposits)

    // Transfer Details
    public $transferAmount = '';
    public $transferNarration = '';

    // Legacy properties for backward compatibility (to be removed)
    public $transferMemberNumber = '';
    public $transferVerifiedMember = null;
    public $transferSourceSavingsAccount = '';
    public $transferSourceSavingsBalance = 0;
    public $transferDestinationDepositAccount = '';
    public $transferMemberSavingsAccounts = [];
    public $transferMemberDepositAccounts = [];

    protected $rules = [
        'member'=> 'required|min:1',
        'reference_number'=>'required',
        'product'=> 'required|min:1',
        'number_of_savings'=> 'required|min:1',
        'linked_savings_account'=> 'required|min:1',
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
        'withdrawSourceAccount' => 'required_if:withdrawPaymentMethod,internal_transfer|exists:bank_accounts,id',
        // TIPS MNO validation
        'withdrawMnoProvider' => 'required_if:withdrawPaymentMethod,tips_mno|string|max:255',
        'withdrawPhoneNumber' => 'required_if:withdrawPaymentMethod,tips_mno|string|max:255',
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
        'blockSavingsAccount' => 'blockSavingsAccountModal',
        'editSavingsAccount' => 'editSavingsAccountModal'
    ];

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        $this->loadStatistics();
        $this->loadAvailableProducts();
    }

    public function boot()
    {
        //$this->authorize('view-savings');
        $this->loadStatistics();
        $this->loadAvailableProducts();
    }


    public function showSavingsBulkUploadPage()
    {
        if (!$this->authorize('export', 'You do not have permission to upload savings data')) {
            return;
        }
        $this->selected = 12;
    }

    protected function loadStatistics()
    {
        try {
            $this->isLoading = true;

            // Total Savings
            $this->totalSavings = DB::table('accounts')
                ->whereNotNull('client_number')
                ->where('product_number', 2000)
                ->where('client_number', '!=', '0000')
                ->sum(DB::raw('CAST(balance AS DECIMAL(15,2))'));

            // Active Accounts
            $this->activeAccounts = DB::table('accounts')
                ->whereNotNull('client_number')
                ->where('product_number', 2000)
                ->where('status', 'ACTIVE')
                ->where('client_number', '!=', '0000')
                ->count();

            // Inactive Accounts
            $this->inactiveAccounts = DB::table('accounts')
                ->whereNotNull('client_number')
                ->where('product_number', 2000)
                ->where('status', '!=', 'ACTIVE')
                ->where('client_number', '!=', '0000')
                ->count();

            // Total Products
            $this->totalProducts = DB::table('sub_products')
                ->where('product_type', 2000)
                ->where('status', 'ACTIVE')
                ->count();

            // Recent Transactions
            $this->recentTransactions = general_ledger::with(['account.client'])
                ->whereHas('account', function ($query) {
                    $query->where('product_number', 2000);
                })
                ->latest()
                ->take(5)
                ->get();

            // Monthly Savings - Fixed: Use credit instead of amount
            $this->monthlySavings = general_ledger::whereHas('account', function ($query) {
                    $query->where('product_number', 2000);
                })
                //->where('transaction_type', 'CREDIT')
                ->whereYear('created_at', Carbon::now()->year)
                ->selectRaw('EXTRACT(MONTH FROM created_at) as month, SUM(credit) as total')
                ->groupBy('month')
                ->get();

            // Top Savers
            $this->topSavers = AccountsModel::with('client')
                ->whereNotNull('client_number')
                ->where('product_number', 2000)
                ->where('status', 'ACTIVE')
                ->where('client_number', '!=', '0000')
                ->where('balance', '>=', 0)
                ->orderBy('balance', 'desc')
                ->take(5)
                ->get();

            // Savings by Product - Fixed: Get product info without join since sub_product fields are NULL
            $this->savingsByProduct = DB::table('sub_products')
                ->where('product_type', 2000)
                ->where('status', 'ACTIVE')
                ->select('product_name', DB::raw('(SELECT SUM(CAST(balance AS DECIMAL(15,2))) FROM accounts WHERE product_number = \'2000\') as total_balance'))
                ->get();
           

        } catch (Exception $e) {
            Log::error('Error loading savings statistics: ' . $e->getMessage());
            $this->errorMessage = 'Failed to load statistics. Please try again.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function showSavingsFullReportPage()
    {
        if (!$this->authorize('view', 'You do not have permission to view savings reports')) {
            return;
        }
        $this->selected = 11;
    }

    public function showInterestOnSavingsPage()
    {
        if (!$this->authorize('view', 'You do not have permission to view interest reports')) {
            return;
        }
        $this->selected = 15;
    }

    protected function loadAvailableProducts()
    {
        try {
            $this->availableProducts = sub_products::where('product_type', 2000)
            //->where('status', 'ACTIVE')
            ->get();
//dd($this->availableProducts);

        } catch (Exception $e) {
            Log::error('Error loading available products: ' . $e->getMessage());
            $this->errorMessage = 'Failed to load available products. Please try again.';
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

    public function showAddSavingsAccountModal($selected){
        $randomNumber = rand(9000, 9999);
        $this->membershipNumber= str_pad($randomNumber, 4, '0', STR_PAD_LEFT);
        $this->selected = $selected;
        $this->showAddSavingsAccount = true;
    }

    public function closeShowAddSavingsAccount(){
        $this->resetData();
        $this->showAddSavingsAccount = false;
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
            'institution_number' => auth()->user()->institution_id,
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

    public function showIssueNewSavingsModal($selected){        
        if($selected == 1) {
            $randomNumber = rand(9000, 9999);
            $this->membershipNumber = str_pad($randomNumber, 4, '0', STR_PAD_LEFT);
            $this->selected = $selected;
            $this->showCreateNewSavingsAccount = true;
            $this->resetData();
        } elseif($selected == 2) {
            $randomNumber = rand(9000, 9999);
            $this->membershipNumber = str_pad($randomNumber, 4, '0', STR_PAD_LEFT);
            $this->selected = $selected;
            $this->showIssueNewSavings = true;
            $this->resetData();
        } else {
            $this->selected = $selected;
        }
    }

    public function closeShowIssueNewSavings(){
        $this->resetData();
        $this->showIssueNewSavings = false;
    }

    public function updatedSavingsAccount(){

        $SavingsAccountData = SavingsModel::select('membershipNumber', 'name', 'region', 'wilaya', 'email')
        ->where('id', '=', $this->SavingsAccount)
        ->get();

    foreach ($SavingsAccountData as $SavingsAccount){
        $this->membershipNumber=$SavingsAccount->membershipNumber;
        $this->name=$SavingsAccount->name;
        $this->region=$SavingsAccount->region;
        $this->wilaya=$SavingsAccount->wilaya;
        $this->email=$SavingsAccount->email;
        $this->status=$SavingsAccount->status;
    }

    }




    public function updateSavingsAccount(){

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
                'process_id' => $this->SavingsAccount,
                'user_id' => Auth::user()->id

                ],
            [
                'institution' => $this->SavingsAccount,
                'process_name' => 'editSavingsAccount',
                'process_description' => 'has edited a SavingsAccount',
                'approval_process_description' => 'has approved changes to a SavingsAccount',
                'process_code' => '02',
                'process_id' => $this->SavingsAccount,
                'process_status' => 'Pending',
                'user_id'  => Auth::user()->id,
                'team_id'  => $this->SavingsAccount,
                'edit_package'=> json_encode($data)
            ]
        );
        Session::flash('message', 'Awaiting approval');
        Session::flash('alert-class', 'alert-success');
        $this->resetData();
        $this->showAddSavingsAccount = false;
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

    public function addSavingsAccount()
    {
        // Check if the savings account already exists
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
            'process_name' => 'createSavingAccount',
            'process_description' => Auth::user()->name . ' has added a new saving account for ' . $memberName,
            'approval_process_description' => 'has approved a new account',
            'process_code' => '04',
            'process_status' => 'Pending',
            'user_id' => Auth::user()->id,
            'team_id' => "",
            'edit_package' => $editPackage
        ]);

        // Reset the form data and close the modal
        $this->resetData2();
        $this->closeShowAddSavingsAccount();

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


           //dd($bankAccountNumber, $selectedAccount );



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
            Session::flash('message', 'Savings has been successfully issued!');
            Session::flash('alert-class', 'alert-success');

            // Close modal or redirect
            $this->closeShowIssueNewSavings();
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
            $this->title = 'Savings list';
        }
        if($tabId == '2'){
            $this->title = 'Enter new SavingsAccount details';
        }
    }

    public function createNewSavingsAccount()
    {
        $this->showCreateNewSavingsAccount = true;
    }

    public function blockSavingsAccountModal($id)
    {
        $this->showDeleteSavingsAccount = true;
        $this->SavingsAccountSelected = $id;
    }

    public function editSavingsAccountModal($id)
    {
        $this->showEditSavingsAccount = true;
        $this->pendingSavingsAccount = $id;
        $this->SavingsAccount = $id;
        $this->pendingSavingsAccountname = SavingsModel::where('id',$id)->value('name');
        $this->updatedSavingsAccount();
    }

    public function closeModal(){
        $this->showCreateNewSavingsAccount = false;
        $this->showDeleteSavingsAccount = false;
        $this->showEditSavingsAccount = false;
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
        return view('livewire.savings.savings', array_merge(
            $this->permissions,
            [
                'accounts' => $this->getFilteredAccounts(),
                'products' => $this->availableProducts,
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

    // public function createSavingsAccount()
    // {
    //     $this->validate([
    //         'clientNumber' => 'required|exists:clients,client_number',
    //         'productId' => 'required|exists:sub_products,sub_product_id',
    //         'accountNumber' => 'required|unique:accounts,account_number',
    //         'accountName' => 'required|string|max:255',
    //         'balance' => 'required|numeric|min:0'
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         // Create the account
    //         $account = AccountsModel::create([
    //             'account_number' => $this->accountNumber,
    //             'account_name' => $this->accountName,
    //             'client_number' => $this->clientNumber,
    //             'product_number' => $this->productId,
    //             'balance' => $this->balance,
    //             'status' => 'ACTIVE'
    //         ]);

    //         // If there's an initial balance, create a transaction
    //         if ($this->balance > 0) {
    //             $creditService = new \App\Services\CreditService();
    //             $creditService->credit(
    //                 'INIT-' . time(), // reference
    //                 '0000', // source_account_number (system account)
    //                 $this->accountNumber, // destination_account_number
    //                 $this->balance, // credit amount
    //                 'Initial deposit for account ' . $this->accountNumber, // narration
    //                 $this->balance, // running_balance
    //                 'System Account', // source_account_name
    //                 $this->accountName // destination_account_name
    //             );
    //         }

    //         DB::commit();

    //         $this->showCreateNewSavingsAccount = false;
    //         $this->resetForm();
    //         session()->flash('success', 'Savings account created successfully.');

    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         Log::error('Error creating savings account: ' . $e->getMessage());
    //         session()->flash('error', 'Failed to create savings account. Please try again.');
    //     }
    // }


    public function validateMemberNumber()
    {
        
        $this->resetErrorBag('member');
        
        //Remove any non-numeric characters
        //$this->member = preg_replace('/[^0-9]/', '', $this->member);
        
        if (empty($this->member)) {
            $this->memberDetails = null;
            $this->memberName = null;
            return;
        }
        
        if (strlen($this->member) > 5) {
            $this->addError('member', 'Member number must be exactly 5 digits');
            $this->memberDetails = null;
            $this->memberName = null;
            return;
        }

      
        
        try {
            // dd($this->member);
            $member = ClientsModel::where('client_number', $this->member)
                //->where('status', 'ACTIVE')
                ->first();

                // dd($member);
            
            if (!$member) {
                $this->addError('member', 'Member not found or not active');
                $this->memberDetails = null;
                $this->memberName = null;
                return;
            }
            
            $this->memberDetails = $member;
            $this->memberName = trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name);
            
            Log::info('Member validated successfully', [
                'client_number' => $this->member,
                'member_id' => $member->id,
                'member_name' => $this->memberName
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error validating member number', [
                'client_number' => $this->member,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('member', 'Error validating member number');
            $this->memberDetails = null;
            $this->memberName = null;
        }
    }
    public function createSavingsAccount(){ 
        if (!$this->authorize('create', 'You do not have permission to create savings accounts')) {
            return;
        }

       $productAccount = sub_products::where('id',$this->productId)->first();
        try{
            DB::beginTransaction();
            $min_code=AccountsModel::where('account_number',$productAccount->product_account)->value('sub_category_code');
            $existingAccount = AccountsModel::where('client_number', $this->member)
            ->where('sub_category_code', $min_code)
            ->exists();
           
            if ($existingAccount || is_null($min_code)) {
            Session::flash('message_fail', 'Your account already exists!');
            Session::flash('alert-class', 'alert-success');
            return;
            }
            $branch_number = Auth::user()->branch;
            $branch_code = $branch_number;
            $product_code =  $min_code;
            $account_number = $this->generate_account_number($branch_code, $product_code);
            $parent=AccountsModel::where('account_number',$productAccount->product_account)->first();
            $memberName = ClientsModel::where('client_number', $this->member)->value('first_name').' '.ClientsModel::where('client_number', $this->member)->value('middle_name').' '.ClientsModel::where('client_number', $this->member)->value('last_name');

            $accountService = new AccountCreationService();
            $sharesAccount = $accountService->createAccount([
                'account_use' => 'external',
                'account_name' => $parent->account_name.':'.$memberName,
                'type' => 'capital_accounts',
                'product_number' => '2000',
                'member_number' => $this->member,
                'branch_number' => auth()->user()->branch
            ], $parent->account_number);


            $newAccountData = [
                'account_use' => 'external',
                'institution_number'=> '1000',
                'branch_number'=> Auth::user()->branch,
                'client_number'=> $this->member,
                'product_number'=> '2000',
                'sub_product_number'=>  $this->productId,
                'major_category_code'=> $parent->major_category_code,
                'category_code'=>  $parent->category_code,
                'sub_category_code'=>  $parent->sub_category_code,
                'balance'=>  0,
                'account_name'=> $memberName,
                'account_number'=>$account_number,
                'parent_account_number'=>$productAccount->product_account,
            ];
            $editPackage = json_encode($newAccountData);
            approvals::create([
                'process_name' => 'create_savings_account',
                'process_description' => Auth::user()->name .  ' has added a new savings account ' .$memberName,
                'approval_process_description' => 'Savings issuance approval required',
                'process_code' => 'ACC_CREATE',
                'process_id' => $sharesAccount->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => $editPackage
            ]);
            
            Session::flash('message', 'The process has been completed! Awaiting approval');
            DB::commit();            
            $this->showCreateNewSavingsAccount = false;
            $this->resetData();
        }catch(\Exception $e){
            DB::rollBack();
            Log::error('Error creating savings account: ' . $e->getMessage());
            Session::flash('message', 'Error creating savings account: ' . $e->getMessage());
            Session::flash('alert-class', 'alert-warning');
            //dd($e->getMessage());
            return ;
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

    public function showCreateNewSavingsAccount()
    {
        if (!$this->authorize('create', 'You do not have permission to create savings accounts')) {
            return;
        }
        $this->reset([
            'member',
            'productId',
            'accountNumber',
            'accountName',
            'balance'
        ]);
        $this->resetErrorBag();
        $this->memberDetails = null;
        $this->memberName = null;        
        $this->showCreateNewSavingsAccount = true;
    }

    public function showReceiveSavingsModal()
    {
        if (!$this->authorize('deposit', 'You do not have permission to process savings deposits')) {
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
        $this->showReceiveSavingsModal = true;
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

                    //dd($this->memberAccounts);
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

    public function submitReceiveSavings()
    {
        // STEP 1: Log method entry
        Log::info('submitReceiveSavings - Method started', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'timestamp' => now()->toDateTimeString(),
            'membership_number' => $this->membershipNumber,
            'selected_account' => $this->selectedAccount,
            'amount' => $this->amount,
            'payment_method' => $this->paymentMethod
        ]);

        // STEP 2: Authorization check
        if (!$this->authorize('deposit', 'You do not have permission to process savings deposits')) {
            Log::warning('submitReceiveSavings - Authorization failed', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name
            ]);
            return;
        }

        Log::info('submitReceiveSavings - Authorization passed');

        // STEP 3: Begin database transaction
        DB::beginTransaction();
        Log::info('submitReceiveSavings - Database transaction started');

        try{
            // STEP 4: Fetch member account
            Log::info('submitReceiveSavings - Fetching member account', [
                'account_number' => $this->selectedAccount
            ]);

            $memberAccount = AccountsModel::where('account_number', $this->selectedAccount)->first();

            if(!$memberAccount){
                Log::error('submitReceiveSavings - Member account not found', [
                    'account_number' => $this->selectedAccount
                ]);
                throw new \Exception('Member account not found.');
            }

            Log::info('submitReceiveSavings - Member account found', [
                'account_id' => $memberAccount->id,
                'account_number' => $memberAccount->account_number,
                'account_name' => $memberAccount->account_name,
                'current_balance' => $memberAccount->balance
            ]);

            // STEP 5: Determine payment method and route to appropriate handler
            Log::info('submitReceiveSavings - Payment method determined', [
                'payment_method' => $this->paymentMethod,
                'handler' => $this->paymentMethod === 'bank' ? 'handleBankDeposit' : 'handleCashDeposit'
            ]);

            // STEP 6: Process the deposit
            if ($this->paymentMethod === 'bank') {
                Log::info('submitReceiveSavings - Calling handleBankDeposit', [
                    'selected_bank' => $this->selectedBank,
                    'reference_number' => $this->referenceNumber,
                    'deposit_date' => $this->depositDate,
                    'deposit_time' => $this->depositTime
                ]);
                $this->handleBankDeposit($memberAccount);
                Log::info('submitReceiveSavings - handleBankDeposit completed successfully');
            } else {
                Log::info('submitReceiveSavings - Calling handleCashDeposit');
                $this->handleCashDeposit($memberAccount);
                Log::info('submitReceiveSavings - handleCashDeposit completed successfully');
            }

            // STEP 7: Generate receipt
            Log::info('submitReceiveSavings - Generating receipt data');
            $this->receiptData = $this->generateReceiptData($memberAccount);
            Log::info('submitReceiveSavings - Receipt generated', [
                'receipt_number' => $this->receiptData['receipt_number'] ?? 'N/A'
            ]);

            // STEP 8: Flash success message
            session()->flash('successMessage', 'Savings received successfully.');
            Log::info('submitReceiveSavings - Success message flashed');

            // STEP 9: Commit transaction
            Log::info('submitReceiveSavings - Committing database transaction');
            DB::commit();
            Log::info('submitReceiveSavings - Database transaction committed successfully');

            // STEP 10: Update UI states
            $this->showReceiveSavingsModal = false;
            $this->showReceiptModal = true;

            Log::info('submitReceiveSavings - Process completed successfully', [
                'receipt_number' => $this->receiptData['receipt_number'] ?? 'N/A',
                'amount_deposited' => $this->amount,
                'final_balance' => $this->receiptData['balance_after'] ?? 'N/A'
            ]);

        }catch(\Exception $e){
            // STEP 11: Handle errors
            DB::rollBack();

            Log::error('submitReceiveSavings - Transaction failed and rolled back', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'membership_number' => $this->membershipNumber,
                'account_number' => $this->selectedAccount,
                'amount' => $this->amount,
                'payment_method' => $this->paymentMethod,
                'timestamp' => now()->toDateTimeString()
            ]);

            Session::flash('errorMessage', 'Error receiving savings: ' . $e->getMessage());
            Session::flash('alert-class', 'alert-warning');
            return;
        }
    }
    
    private function generateReceiptData($memberAccount)
    {
        $receiptNumber = 'RCP-' . strtoupper(uniqid());
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
            'transaction_type' => 'Savings Deposit',
            'balance_after' => number_format($memberAccount->balance + $this->amount, 2)
        ];
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


    private function handleBankDeposit($memberAccount)
    {
        Log::info('handleBankDeposit - Method started', [
            'member_account_id' => $memberAccount->id,
            'member_account_number' => $memberAccount->account_number,
            'amount' => $this->amount
        ]);

        // STEP 1: Validate bank deposit inputs
        Log::info('handleBankDeposit - Validating inputs');
        $this->validate([
            'depositDate' => 'required|date',
            'depositTime' => 'required|date_format:H:i',
            'selectedBank' => 'required',
            'referenceNumber' => 'required|string|max:255',
            'narration' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'depositorName' => 'required|string|max:255',
            'paymentMethod' => 'required|string|in:bank'
        ]);
        Log::info('handleBankDeposit - Validation passed');

        // STEP 2: Check if bank details and account exist
        if (!empty($this->selectedBankDetails->internal_mirror_account_number) && !empty($this->selectedAccount)) {

            Log::info('handleBankDeposit - Bank details validated', [
                'bank_id' => $this->selectedBank,
                'bank_name' => $this->selectedBankDetails->bank_name,
                'internal_mirror_account' => $this->selectedBankDetails->internal_mirror_account_number,
                'member_account' => $this->selectedAccount
            ]);

            $totalAmount = $this->amount;

            // STEP 3: Prepare transaction data
            $transactionData = [
                'first_account' => $this->selectedBankDetails->internal_mirror_account_number, // Bank account (Asset)
                'second_account' => $this->selectedAccount, // Member account (Liability)
                'amount' => $totalAmount,
                'narration' => 'Savings deposit : ' . $this->amount . ' : ' . $this->depositorName . ' : ' . $this->selectedBankDetails->bank_name . ' : ' . $this->referenceNumber,
                'action' => 'savings deposit by bank',
                'source_account' => $this->selectedBankDetails->internal_mirror_account_number, // Money flows FROM bank
                'destination_account' => $this->selectedAccount // Money flows TO member savings
            ];

            Log::info('handleBankDeposit - Transaction data prepared', [
                'transaction_data' => $transactionData,
                'depositor_name' => $this->depositorName,
                'reference_number' => $this->referenceNumber,
                'deposit_date' => $this->depositDate,
                'deposit_time' => $this->depositTime
            ]);

            // STEP 4: Post the transaction
            Log::info('handleBankDeposit - Posting transaction to TransactionPostingService');
            $transactionService = new TransactionPostingService();
            $result = $transactionService->postTransaction($transactionData);

            Log::info('handleBankDeposit - Transaction service response received', [
                'status' => $result['status'] ?? 'unknown',
                'result' => $result
            ]);

            // STEP 5: Check transaction result
            if ($result['status'] !== 'success') {
                Log::error('handleBankDeposit - Transaction posting failed', [
                    'error' => $result['message'] ?? 'Unknown error',
                    'transaction_data' => $transactionData,
                    'result' => $result
                ]);
                throw new \Exception('Failed to post transaction: ' . ($result['message'] ?? 'Unknown error'));
            }

            Log::info('handleBankDeposit - Transaction posted successfully', [
                'transaction_reference' => $result['reference_number'] ?? null,
                'amount' => $totalAmount
            ]);

            // STEP 6: Create transaction record
            Log::info('handleBankDeposit - Creating transaction record');
            $this->createTransactionRecord(
                $memberAccount,
                $totalAmount,
                'bank', //bank deposit
                'savings_deposit',
                'bank_deposit',
                $this->narration,
                $result['reference_number'] ?? null,
                $this->selectedBankDetails->bank_name,
                $this->referenceNumber
            );

            Log::info('handleBankDeposit - Transaction record created successfully');
            Log::info('handleBankDeposit - Method completed successfully', [
                'final_reference' => $result['reference_number'] ?? null
            ]);
        } else {
            Log::error('handleBankDeposit - Missing required data', [
                'has_internal_mirror_account' => !empty($this->selectedBankDetails->internal_mirror_account_number),
                'has_selected_account' => !empty($this->selectedAccount),
                'selected_bank_details' => $this->selectedBankDetails
            ]);
            throw new \Exception('Bank details or member account information is missing');
        }
    }
    
    private function handleCashDeposit($memberAccount)
    {
        Log::info('handleCashDeposit - Method started', [
            'member_account_id' => $memberAccount->id,
            'member_account_number' => $memberAccount->account_number,
            'amount' => $this->amount
        ]);

        // STEP 1: Auto-generate reference and timestamps
        $this->referenceNumber = 'CASH-' . strtoupper(uniqid());
        $this->depositDate = now()->format('Y-m-d');
        $this->depositTime = now()->format('H:i');

        Log::info('handleCashDeposit - Reference number generated', [
            'reference_number' => $this->referenceNumber,
            'deposit_date' => $this->depositDate,
            'deposit_time' => $this->depositTime
        ]);

        // STEP 2: Validate cash deposit inputs
        Log::info('handleCashDeposit - Validating inputs');
        $this->validate([
            'depositDate' => 'required|date',
            'depositTime' => 'required|date_format:H:i',
            'referenceNumber' => 'required|string|max:255',
            'narration' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'depositorName' => 'required|string|max:255',
            'paymentMethod' => 'required|string|in:cash'
        ]);
        Log::info('handleCashDeposit - Validation passed');

        // STEP 3: Get vault account from institution settings
        Log::info('handleCashDeposit - Fetching vault account from institution');
        $institution = \App\Models\institutions::find(1);

        if (!$institution) {
            Log::error('handleCashDeposit - Institution not found', [
                'institution_id' => 1
            ]);
            throw new \Exception('Institution settings not found. Please contact administrator.');
        }

        Log::info('handleCashDeposit - Institution found', [
            'institution_id' => $institution->id,
            'institution_name' => $institution->name,
            'vault_account' => $institution->main_vaults_account
        ]);

        if (!$institution->main_vaults_account) {
            Log::error('handleCashDeposit - Vault account not configured', [
                'institution_id' => $institution->id
            ]);
            throw new \Exception('Vault account not configured in institution settings. Please contact administrator.');
        }

        $cashAccountNumber = $institution->main_vaults_account;

        Log::info('handleCashDeposit - Vault account fetched successfully', [
            'vault_account_number' => $cashAccountNumber,
            'source' => 'institution.main_vaults_account'
        ]);

        // STEP 4: Verify vault account exists and is active
        Log::info('handleCashDeposit - Verifying vault account exists');
        $vaultAccount = AccountsModel::where('account_number', $cashAccountNumber)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$vaultAccount) {
            Log::error('handleCashDeposit - Vault account not found or inactive', [
                'account_number' => $cashAccountNumber,
                'institution_id' => $institution->id
            ]);
            throw new \Exception('Vault account ' . $cashAccountNumber . ' not found or inactive. Please contact administrator.');
        }

        Log::info('handleCashDeposit - Vault account verified', [
            'account_id' => $vaultAccount->id,
            'account_number' => $vaultAccount->account_number,
            'account_name' => $vaultAccount->account_name,
            'account_status' => $vaultAccount->status,
            'current_balance' => $vaultAccount->balance
        ]);

        // STEP 5: Prepare transaction data
        $transactionData = [
            'first_account' => $cashAccountNumber, // Vault cash account (Asset)
            'second_account' => $this->selectedAccount, // Member's account (Liability)
            'amount' => $this->amount,
            'narration' => 'Cash savings deposit: ' . $this->amount . ' : ' . $this->depositorName . ' : ' . $this->referenceNumber,
            'action' => 'savings deposit by cash',
            'source_account' => $cashAccountNumber, // Money flows FROM vault
            'destination_account' => $this->selectedAccount // Money flows TO member savings
        ];

        Log::info('handleCashDeposit - Transaction data prepared', [
            'transaction_data' => $transactionData,
            'depositor_name' => $this->depositorName,
            'vault_account_name' => $vaultAccount->account_name
        ]);

        // STEP 6: Post the transaction
        Log::info('handleCashDeposit - Posting transaction to TransactionPostingService');
        $transactionService = new TransactionPostingService();
        $result = $transactionService->postTransaction($transactionData);

        Log::info('handleCashDeposit - Transaction service response received', [
            'status' => $result['status'] ?? 'unknown',
            'result' => $result
        ]);

        // STEP 7: Check transaction result
        if ($result['status'] !== 'success') {
            Log::error('handleCashDeposit - Transaction posting failed', [
                'error' => $result['message'] ?? 'Unknown error',
                'transaction_data' => $transactionData,
                'result' => $result
            ]);
            throw new \Exception('Failed to post cash transaction: ' . ($result['message'] ?? 'Unknown error'));
        }

        Log::info('handleCashDeposit - Transaction posted successfully', [
            'transaction_reference' => $result['reference_number'] ?? null,
            'amount' => $this->amount,
            'vault_account_used' => $cashAccountNumber
        ]);

        // STEP 8: Create transaction record
        Log::info('handleCashDeposit - Creating transaction record');
        $this->createTransactionRecord(
            $memberAccount,
            $this->amount,
            'cash', //cash deposit
            'savings_deposit',
            'cash_deposit',
            $this->narration,
            $result['reference_number'] ?? null,
            'Cash',
            $this->referenceNumber,
        );

        Log::info('handleCashDeposit - Transaction record created successfully');
        Log::info('handleCashDeposit - Method completed successfully', [
            'final_reference' => $result['reference_number'] ?? null
        ]);
    }

    /**
     * Create a transaction record in the transactions table
     */
    private function createTransactionRecord($account, $amount, $type, $category, $subcategory, $narration, $reference, $externalSystem = null, $externalReference = null)
    {
        // Get the current balance before the transaction
        $balanceBefore = $account->balance;
        $balanceAfter = $balanceBefore + $amount;

        $transaction = \App\Models\Transaction::create([
            'account_id' => $account->id,
            'amount' => $amount,
            'currency' => 'TZS',
            'type' => $type,
            'transaction_category' => $category,
            'transaction_subcategory' => $subcategory,
            'narration' => $narration,
            'description' => 'Savings deposit processed by ' . auth()->user()->name,
            'reference' => $reference,
            'external_reference' => $externalReference,
            'status' => 'COMPLETED',
            'reconciliation_status' => 'UNRECONCILED',
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'running_balance' => $balanceAfter,
            'external_system' => $externalSystem,
            'external_transaction_id' => $externalReference,
            'initiated_at' => now(),
            'processed_at' => now(),
            'completed_at' => now(),
            'initiated_by' => auth()->id(),
            'processed_by' => auth()->id(),
            'client_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'is_manual' => true,
            'is_system_generated' => false,
            'requires_approval' => false,
            'is_approved' => true,
            'approved_at' => now(),
            'metadata' => [
                'member_number' => $this->membershipNumber,
                'depositor_name' => $this->depositorName,
                'payment_method' => $this->paymentMethod,
                'deposit_date' => $this->depositDate ?? now()->format('Y-m-d'),
                'deposit_time' => $this->depositTime ?? now()->format('H:i'),
                'bank_name' => $this->selectedBankDetails->bank_name ?? null,
                'bank_account' => $this->selectedBankDetails->account_number ?? null,
            ],
            'tags' => ['savings', 'deposit', $this->paymentMethod],
            'batch_id' => 'SAVINGS_DEPOSIT_' . date('Y-m-d'),
            'process_id' => 'SAVINGS_' . $this->membershipNumber . '_' . time(),
            'regulatory_category' => 'savings_deposit',
            'reporting_period' => date('Y-m'),
            'risk_level' => 'low'
        ]);

        // Create receipt record
        $this->createReceiptRecord($transaction, $account, $amount);

        // Log the audit trail
        $transaction->logAudit(
            'created',
            null,
            'completed',
            'Savings deposit transaction created',
            [
                'amount' => $amount,
                'payment_method' => $this->paymentMethod,
                'member' => $this->membershipNumber,
                'depositor' => $this->depositorName
            ]
        );

        Log::info('Transaction record created', [
            'transaction_id' => $transaction->id,
            'transaction_uuid' => $transaction->transaction_uuid,
            'account' => $account->account_number,
            'amount' => $amount,
            'reference' => $externalReference,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter
        ]);

        return $transaction;
    }
    
    private function createReceiptRecord($transaction, $account, $amount)
    {
        $receiptNumber = 'RCP-' . strtoupper(uniqid());
        
        \App\Models\Receipt::create([
            'receipt_number' => $receiptNumber,
            'transaction_id' => $transaction->id,
            'account_id' => $account->id,
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
            'transaction_type' => 'Savings Deposit',
            'status' => 'GENERATED',
            'generated_at' => now(),
            'printed_at' => null,
            'metadata' => [
                'balance_before' => $account->balance,
                'balance_after' => $account->balance + $amount,
                'deposit_date' => $this->depositDate ?? now()->format('Y-m-d'),
                'deposit_time' => $this->depositTime ?? now()->format('H:i'),
            ]
        ]);
        
        // Update the receipt number in the receipt data
        $this->receiptData['receipt_number'] = $receiptNumber;
    }

    public function showWithdrawSavingsModal()
    {
        if (!$this->authorize('withdraw', 'You do not have permission to process savings withdrawals')) {
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
        $this->showWithdrawSavingsModal = true;
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
        // Reset OTP verification when payment method changes
        $this->withdrawOtpCode = '';
        $this->withdrawOtpSent = false;
        $this->withdrawOtpSentTime = null;
        $this->withdrawOtpVerified = false;
        $this->generatedWithdrawOTP = null;

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

    public function submitWithdrawSavings()
    {
        if (!$this->authorize('withdraw', 'You do not have permission to process savings withdrawals')) {
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

            // For cash withdrawals, verify OTP first
            if ($this->withdrawPaymentMethod === 'cash' && !$this->withdrawOtpVerified) {
                $this->addError('withdrawOtpCode', 'Please verify OTP before processing cash withdrawal.');
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

            $this->showWithdrawSavingsModal = false;
            $this->resetWithdrawForm();
            session()->flash('success', 'Savings withdrawal processed successfully.');

        } catch (Exception $e) {
            Log::error('Error processing savings withdrawal: ' . $e->getMessage());
            session()->flash('error', 'Failed to process withdrawal. Please try again.');
        }
    }

    public function sendWithdrawOTP()
    {
        if (!$this->withdrawVerifiedMember) {
            session()->flash('error', 'Please verify member first.');
            return;
        }

        try {
            // Generate 6-digit OTP
            $this->generatedWithdrawOTP = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            // Invalidate any previous OTPs for this member (mark as verified to prevent reuse)
            \App\Models\WithdrawalOtp::where('membership_number', $this->withdrawMembershipNumber)
                ->where('is_verified', false)
                ->update(['is_verified' => true]);

            // Store OTP in database with 5 minute expiry
            $otpRecord = \App\Models\WithdrawalOtp::create([
                'membership_number' => $this->withdrawMembershipNumber,
                'otp_code' => $this->generatedWithdrawOTP,
                'expires_at' => now()->addMinutes(5),
                'is_verified' => false,
            ]);

            // LOG: OTP Generation and Storage
            Log::info('🔐 WITHDRAW OTP GENERATED', [
                'membership_number' => $this->withdrawMembershipNumber,
                'otp_id' => $otpRecord->id,
                'otp_value' => $this->generatedWithdrawOTP,
                'db_stored_successfully' => $otpRecord->exists,
                'expiry' => $otpRecord->expires_at->toDateTimeString(),
                'timestamp' => now()->toDateTimeString()
            ]);
            
            // Get member's details
            $member = ClientsModel::where('client_number', $this->withdrawMembershipNumber)->first();
            $memberPhone = $member->phone_number ?? null;
            $memberEmail = $member->email ?? null;
            $memberName = $member->first_name . ' ' . $member->last_name;
            
            $otpSentVia = [];
            
            // Send OTP via SMS
            if ($memberPhone) {
                $smsMessage = "Dear {$memberName}, your savings withdrawal OTP is: {$this->generatedWithdrawOTP}. Valid for 5 minutes. Do not share with anyone. - MFI";
                
                try {
                    $smsService = app(SmsService::class);
                    $smsService->send($memberPhone, $smsMessage, $member);
                    $otpSentVia[] = 'SMS (' . substr($memberPhone, 0, 3) . '****' . substr($memberPhone, -2) . ')';
                } catch (\Exception $e) {
                    Log::error('Failed to send withdrawal OTP via SMS', [
                        'member' => $this->withdrawMembershipNumber,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Send OTP via Email
            if ($memberEmail) {
                try {
                    $otp = $this->generatedWithdrawOTP;
                    Mail::send([], [], function ($message) use ($memberEmail, $memberName, $otp) {
                        $emailBody = "
                        <h3>Savings Withdrawal OTP</h3>
                        <p>Dear {$memberName},</p>
                        <p>Your OTP for savings withdrawal is:</p>
                        <h1 style='color: #2563eb; font-size: 32px; letter-spacing: 5px;'>{$otp}</h1>
                        <p>This OTP is valid for 5 minutes.</p>
                        <p><strong>Security Notice:</strong> Do not share this OTP with anyone. MFI staff will never ask for your OTP.</p>
                        <br>
                        <p>Best regards,<br>MFI Core System</p>
                        ";
                        
                        $message->to($memberEmail, $memberName)
                            ->subject('Savings Withdrawal OTP - MFI')
                            ->html($emailBody);
                    });
                    $otpSentVia[] = 'Email (' . substr($memberEmail, 0, 3) . '****' . substr($memberEmail, strpos($memberEmail, '@')) . ')';
                } catch (\Exception $e) {
                    Log::error('Failed to send withdrawal OTP via Email', [
                        'member' => $this->withdrawMembershipNumber,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $this->withdrawOtpSent = true;
            $this->withdrawOtpSentTime = now();
            
            if (!empty($otpSentVia)) {
                session()->flash('success', 'OTP has been sent via ' . implode(' and ', $otpSentVia));
            } else {
                // If no phone or email, show OTP on screen (for testing)
                session()->flash('info', 'No contact details found. OTP for testing: ' . $this->generatedWithdrawOTP);
            }
            
        } catch (\Exception $e) {
            Log::error('Error generating withdrawal OTP', [
                'member' => $this->withdrawMembershipNumber,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error generating OTP: ' . $e->getMessage());
        }
    }

    public function verifyWithdrawOTP()
    {
        if (!$this->withdrawOtpCode) {
            session()->flash('error', 'Please enter the OTP code.');
            return false;
        }

        // Check all possible cache keys (last 5 minutes)
        $found = false;
        for ($i = 0; $i < 300; $i++) { // Check last 5 minutes
            $time = Carbon::now()->subSeconds($i);
            $cacheKey = 'withdrawal_otp_' . $this->withdrawMembershipNumber . '_' . $time->format('YmdHis');
            $storedOTP = Cache::get($cacheKey);

            if ($storedOTP && $storedOTP === $this->withdrawOtpCode) {
                Cache::forget($cacheKey);
                $found = true;
                break;
            }
        }

        if (!$found) {
            session()->flash('error', 'Invalid or expired OTP. Please request a new one.');
            return false;
        }

        $this->withdrawOtpVerified = true;
        session()->flash('success', 'OTP verified successfully.');
        return true;
    }

    // Livewire lifecycle hook - automatically called when withdrawOtpCode property updates
    public function updatedWithdrawOtpCode($value)
    {
        Log::info('🔄 WITHDRAW OTP INPUT CHANGED', [
            'input_value' => $value,
            'input_length' => strlen($value),
            'membership_number' => $this->withdrawMembershipNumber,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Only attempt verification when OTP is 6 digits
        if (strlen($value) !== 6) {
            // Reset verification if less than 6 digits
            $this->withdrawOtpVerified = false;
            Log::info('⏸️ OTP VERIFICATION SKIPPED - Not 6 digits yet', [
                'current_length' => strlen($value),
                'required_length' => 6
            ]);
            return;
        }

        Log::info('✅ OTP REACHED 6 DIGITS - Starting verification', [
            'entered_otp' => $value
        ]);

        // Database lookup using the WithdrawalOtp model
        $otpRecord = \App\Models\WithdrawalOtp::getValidOtp($this->withdrawMembershipNumber, $value);

        Log::info('🔍 DATABASE LOOKUP RESULT', [
            'membership_number' => $this->withdrawMembershipNumber,
            'otp_found' => $otpRecord !== null,
            'otp_id' => $otpRecord->id ?? null,
            'stored_otp' => $otpRecord->otp_code ?? null,
            'entered_otp' => $value,
            'is_expired' => $otpRecord ? $otpRecord->isExpired() : 'N/A',
            'expires_at' => $otpRecord ? $otpRecord->expires_at->toDateTimeString() : 'N/A',
            'match' => ($otpRecord && $otpRecord->otp_code === $value)
        ]);

        if ($otpRecord) {
            // OTP is correct and valid!
            $this->withdrawOtpVerified = true;
            $otpRecord->markAsVerified(); // Mark as verified in database

            Log::info('🎉 OTP VERIFICATION SUCCESS', [
                'membership_number' => $this->withdrawMembershipNumber,
                'otp_id' => $otpRecord->id,
                'verified_at' => now()->toDateTimeString(),
                'withdrawOtpVerified' => $this->withdrawOtpVerified
            ]);

            session()->flash('success', 'OTP verified successfully! You can now process the withdrawal.');
        } else {
            // OTP is incorrect or expired
            $this->withdrawOtpVerified = false;

            // Check why verification failed
            $existingOtp = \App\Models\WithdrawalOtp::where('membership_number', $this->withdrawMembershipNumber)
                ->where('otp_code', $value)
                ->first();

            $reason = 'OTP not found or expired';
            if ($existingOtp) {
                if ($existingOtp->is_verified) {
                    $reason = 'OTP already used';
                } elseif ($existingOtp->isExpired()) {
                    $reason = 'OTP expired';
                } else {
                    $reason = 'OTP mismatch';
                }
            }

            Log::warning('❌ OTP VERIFICATION FAILED', [
                'membership_number' => $this->withdrawMembershipNumber,
                'entered_otp' => $value,
                'reason' => $reason,
                'failed_at' => now()->toDateTimeString()
            ]);

            session()->flash('error', 'Invalid OTP code. Please check and try again.');
        }
    }

    private function processCashWithdrawal()
    {
        // Verify OTP first
        if (!$this->withdrawOtpVerified) {
            if (!$this->verifyWithdrawOTP()) {
                throw new \Exception('OTP verification failed. Please enter valid OTP.');
            }
        }
        
        // Auto-generate reference number if not provided
        if (empty($this->withdrawReferenceNumber)) {
            $this->withdrawReferenceNumber = 'CASH-' . date('YmdHis') . '-' . rand(1000, 9999);
        }
        
        // Set current date and time automatically
        $this->withdrawDate = date('Y-m-d');
        $this->withdrawTime = date('H:i');

        // Get cash account from institution settings
        $institution = \App\Models\institutions::find(1);
        if (!$institution || !$institution->main_till_account) {
            throw new \Exception('Institution cash account not configured. Please contact administrator.');
        }

        $cashInSafeAccount = AccountsModel::where('account_number', $institution->main_till_account)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$cashInSafeAccount) {
            // Fallback to vault cash or petty cash account
            $cashInSafeAccount = AccountsModel::where('account_number', $institution->main_petty_cash_account)
                ->where('status', 'ACTIVE')
                ->first();
        }

        if (!$cashInSafeAccount) {
            // Last fallback to searching by name
            $cashInSafeAccount = AccountsModel::where('account_name', 'LIKE', '%VAULT CASH%')
                ->orWhere('account_name', 'LIKE', '%TILL%')
                ->orWhere('account_name', 'LIKE', '%PETTY CASH%')
                ->where('status', 'ACTIVE')
                ->first();
        }

        if (!$cashInSafeAccount) {
            throw new \Exception('Cash account not found. Please contact administrator to configure cash accounts.');
        }

        // Post the cash withdrawal transaction
        $transactionService = new TransactionPostingService();
        $transactionData = [
            'first_account' => $cashInSafeAccount->account_number, // Will be CREDITED (cash leaving vault - decreases)
            'second_account' => $this->withdrawSelectedAccount, // Will be DEBITED (money leaving member - decreases)
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
        Log::info('🏦 INTERNAL TRANSFER WITHDRAWAL - STARTED', [
            'timestamp' => now()->toDateTimeString(),
            'member_account' => $this->withdrawSelectedAccount,
            'amount' => $this->withdrawAmount,
            'narration' => $this->withdrawNarration,
            'source_account_id' => $this->withdrawSourceAccount,
            'reference_number' => $this->withdrawReferenceNumber
        ]);

        try {
            // STEP 1: Validation
            Log::info('🔍 STEP 1: Validating source account', [
                'source_account_id' => $this->withdrawSourceAccount,
                'validation_rule' => 'required|exists:bank_accounts,id'
            ]);

            $this->validate([
                'withdrawSourceAccount' => 'required|exists:bank_accounts,id'
            ]);

            Log::info('✅ Validation passed');

            // Always generate a fresh reference number at submission time
            $this->withdrawReferenceNumber = 'INT-' . date('YmdHis') . '-' . rand(1000, 9999);
            Log::info('🔢 Generated fresh reference number', ['reference' => $this->withdrawReferenceNumber]);

            // Set current date and time automatically
            $this->withdrawDate = date('Y-m-d');
            $this->withdrawTime = date('H:i');
            Log::info('📅 Set transaction date/time', [
                'date' => $this->withdrawDate,
                'time' => $this->withdrawTime
            ]);

            // STEP 2: Get source bank account
            Log::info('🏦 STEP 2: Retrieving source bank account', [
                'source_account_id' => $this->withdrawSourceAccount
            ]);

            $sourceBankAccount = \App\Models\BankAccount::find($this->withdrawSourceAccount);
            if (!$sourceBankAccount) {
                Log::error('❌ Source bank account not found', [
                    'source_account_id' => $this->withdrawSourceAccount
                ]);
                throw new \Exception('Source bank account not found.');
            }

            Log::info('✅ Source bank account retrieved', [
                'id' => $sourceBankAccount->id,
                'account_number' => $sourceBankAccount->account_number,
                'account_name' => $sourceBankAccount->account_name,
                'internal_mirror_account' => $sourceBankAccount->internal_mirror_account_number ?? 'N/A'
            ]);

            // STEP 3: Prepare transfer data
            Log::info('📋 STEP 3: Preparing NBC API transfer data', [
                'verified_member' => $this->withdrawVerifiedMember,
                'member_has_account_number' => isset($this->withdrawVerifiedMember['account_number']),
                'member_account_number' => $this->withdrawVerifiedMember['account_number'] ?? 'MISSING'
            ]);

            // Process internal fund transfer using NBC API
            $internalTransferService = new \App\Services\NbcPayments\InternalFundTransferService();

            $transferData = [
                'debitAccount' => $sourceBankAccount->account_number, // SACCO's NBC account from selected source
                'creditAccount' => $this->withdrawVerifiedMember['account_number'] ?? '', // Member's NBC account from client record
                'amount' => $this->withdrawAmount,
                'debitCurrency' => 'TZS',
                'creditCurrency' => 'TZS',
                'narration' => 'Internal transfer: ' . $this->withdrawNarration,
                'channelId' => config('services.nbc_internal_fund_transfer.channel_id'),
                'channelRef' => $this->withdrawReferenceNumber,
                'pyrName' => $this->withdrawVerifiedMember['name'] ?? 'Member'
            ];

            Log::info('📤 Transfer data prepared', [
                'debitAccount' => $transferData['debitAccount'],
                'creditAccount' => $transferData['creditAccount'],
                'amount' => $transferData['amount'],
                'channelId' => $transferData['channelId'],
                'channelRef' => $transferData['channelRef'],
                'pyrName' => $transferData['pyrName'],
                'narration' => $transferData['narration']
            ]);

            // STEP 4: Call NBC API
            Log::info('🌐 STEP 4: Calling NBC Internal Transfer API', [
                'service_class' => get_class($internalTransferService),
                'transfer_data' => $transferData
            ]);

            $result = $internalTransferService->processInternalTransfer($transferData);

            Log::info('📥 NBC API Response received', [
                'success' => $result['success'] ?? false,
                'full_response' => $result
            ]);

            if (!$result['success']) {
                Log::error('❌ NBC API Transfer Failed', [
                    'error_message' => $result['message'] ?? 'Unknown error',
                    'error_code' => $result['code'] ?? 'N/A',
                    'full_response' => $result,
                    'transfer_data_sent' => $transferData
                ]);
                throw new \Exception('Internal transfer failed: ' . ($result['message'] ?? 'Unknown error'));
            }

            Log::info('✅ NBC API Transfer Successful', [
                'host_reference' => $result['data']['hostReferenceCbs'] ?? null,
                'response_data' => $result['data'] ?? []
            ]);

            // STEP 5: Post transaction internally
            Log::info('💾 STEP 5: Posting transaction to internal system', [
                'first_account' => $sourceBankAccount->internal_mirror_account_number ?? $sourceBankAccount->account_number,
                'second_account' => $this->withdrawSelectedAccount,
                'amount' => $this->withdrawAmount
            ]);

            $transactionService = new TransactionPostingService();
            $transactionData = [
                'first_account' => $sourceBankAccount->internal_mirror_account_number ?? $sourceBankAccount->account_number, // Will be CREDITED (bank sending funds)
                'second_account' => $this->withdrawSelectedAccount, // Will be DEBITED (money leaving member - decreases)
                'amount' => $this->withdrawAmount,
                'narration' => 'Internal transfer withdrawal: ' . $this->withdrawAmount . ' to ' . ($this->withdrawVerifiedMember['account_number'] ?? '') . ' : ' . $this->withdrawReferenceNumber,
                'action' => 'internal_transfer_withdrawal'
            ];

            Log::info('📋 Transaction data for posting', $transactionData);

            $transactionResult = $transactionService->postTransaction($transactionData);

            Log::info('📥 Transaction posting result', [
                'status' => $transactionResult['status'] ?? 'unknown',
                'full_result' => $transactionResult
            ]);

            if ($transactionResult['status'] !== 'success') {
                Log::error('❌ Failed to post internal transaction', [
                    'error_message' => $transactionResult['message'] ?? 'Unknown error',
                    'transaction_data' => $transactionData,
                    'full_result' => $transactionResult
                ]);
                throw new \Exception('Failed to post internal transfer transaction: ' . ($transactionResult['message'] ?? 'Unknown error'));
            }

            Log::info('✅✅✅ INTERNAL TRANSFER WITHDRAWAL - COMPLETED SUCCESSFULLY', [
                'member_account' => $this->withdrawSelectedAccount,
                'nbc_account' => $this->withdrawVerifiedMember['account_number'] ?? '',
                'source_bank_account' => $sourceBankAccount->account_number,
                'amount' => $this->withdrawAmount,
                'reference' => $this->withdrawReferenceNumber,
                'nbc_reference' => $result['data']['hostReferenceCbs'] ?? null,
                'journal_entry_id' => $transactionResult['journal_entry_id'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('❌❌❌ INTERNAL TRANSFER WITHDRAWAL - FAILED', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
                'member_account' => $this->withdrawSelectedAccount,
                'amount' => $this->withdrawAmount,
                'source_account_id' => $this->withdrawSourceAccount,
                'reference' => $this->withdrawReferenceNumber
            ]);
            throw $e;
        }
    }

    private function processTipsMnoWithdrawal()
    {
        $this->validate([
            'withdrawMnoProvider' => 'required|string|max:255',
            'withdrawPhoneNumber' => 'required|string|max:255'
        ]);
        
        // Auto-generate reference number if not provided
        if (empty($this->withdrawReferenceNumber)) {
            $this->withdrawReferenceNumber = 'MNO-' . date('YmdHis') . '-' . rand(1000, 9999);
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
            'first_account' => $cashAtNbcAccount->account_number, // Will be CREDITED (NBC cash decreases - sent via TIPS)
            'second_account' => $this->withdrawSelectedAccount, // Will be DEBITED (money leaving member - decreases)
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
            $this->withdrawReferenceNumber = 'TIPS-' . date('YmdHis') . '-' . rand(1000, 9999);
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
            'first_account' => $cashAtNbcAccount->account_number, // Will be CREDITED (NBC cash decreases - sent via TIPS)
            'second_account' => $this->withdrawSelectedAccount, // Will be DEBITED (money leaving member - decreases)
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
            'withdrawSourceAccount',
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
            'withdrawBankAccountHolderName',
            'withdrawOtpCode',
            'generatedWithdrawOTP',
            'withdrawOtpSent',
            'withdrawOtpSentTime',
            'withdrawOtpVerified'
        ]);
        $this->resetErrorBag();
    }

    // ==================== PAY LOAN METHODS ====================

    /**
     * Show Pay Loan Modal
     */
    public function showPayLoanModal()
    {
        $this->selected = 13;
        $this->showPayLoanModal = true;
        $this->resetPayLoanFields();
    }

    /**
     * Search for loan by ID or member number
     */
    public function searchLoanForPayment()
    {
        Log::info('🔍 LOAN SEARCH INITIATED', [
            'search_type' => $this->loanSearchType,
            'search_value' => $this->loanSearchValue,
            'user' => auth()->user()->name ?? 'Unknown'
        ]);

        $this->validate([
            'loanSearchValue' => 'required|string'
        ]);

        try {
            $query = DB::table('loans')
                ->leftJoin('clients', 'loans.client_number', '=', 'clients.client_number')
                ->select(
                    'loans.*',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name',
                    'clients.phone_number',
                    'clients.email'
                );

            // Apply search filters
            if ($this->loanSearchType === 'loan_id') {
                $query->where('loans.loan_id', $this->loanSearchValue)
                      ->whereIn('loans.status', ['ACTIVE', 'RESTRUCTURED']);

                Log::info('📋 Searching by Loan ID', [
                    'loan_id' => $this->loanSearchValue,
                    'sql' => $query->toSql()
                ]);
            } else {
                $query->where('loans.client_number', $this->loanSearchValue)
                      ->whereIn('loans.status', ['ACTIVE', 'RESTRUCTURED']);

                Log::info('📋 Searching by Member Number', [
                    'client_number' => $this->loanSearchValue,
                    'sql' => $query->toSql()
                ]);
            }

            $loans = $query->get();

            Log::info('📊 Query executed', [
                'loans_found' => $loans->count(),
                'loan_data' => $loans->map(function($loan) {
                    return [
                        'loan_id' => $loan->loan_id ?? 'N/A',
                        'client_number' => $loan->client_number ?? 'N/A',
                        'status' => $loan->status ?? 'N/A',
                        'principal' => $loan->principle ?? 0
                    ];
                })->toArray()
            ]);

            if ($loans->isEmpty()) {
                Log::warning('❌ No loans found', [
                    'search_type' => $this->loanSearchType,
                    'search_value' => $this->loanSearchValue
                ]);

                session()->flash('error', 'No active loans found with the provided ' . str_replace('_', ' ', $this->loanSearchType) . '.');
                $this->selectedLoan = null;
                $this->foundLoans = [];
                $this->memberSavingsAccounts = [];
                return;
            }

            // If only one loan found, auto-select it
            if ($loans->count() === 1) {
                Log::info('📌 Single loan found - auto-selecting');
                $this->selectLoanForPayment($loans->first()->loan_id);
                return;
            }

            // Multiple loans found - show list for selection
            Log::info('📋 Multiple loans found - showing selection list', [
                'count' => $loans->count()
            ]);

            $loanRepaymentService = app(\App\Services\LoanRepaymentService::class);

            // Build found loans array with outstanding balances
            $this->foundLoans = $loans->map(function($loan) use ($loanRepaymentService) {
                $outstandingBalance = $loanRepaymentService->calculateOutstandingBalances($loan);

                $memberName = 'N/A';
                if ($loan->first_name || $loan->last_name) {
                    $memberName = trim(($loan->first_name ?? '') . ' ' . ($loan->middle_name ?? '') . ' ' . ($loan->last_name ?? ''));
                } elseif ($loan->client_number) {
                    $memberName = 'Client: ' . $loan->client_number;
                }

                return [
                    'loan_id' => $loan->loan_id,
                    'account_number' => $loan->loan_account_number,
                    'member_name' => $memberName,
                    'member_number' => $loan->client_number,
                    'principal' => $loan->principle,
                    'outstanding' => $outstandingBalance['total'],
                    'outstanding_principal' => $outstandingBalance['principal'],
                    'outstanding_interest' => $outstandingBalance['interest'],
                    'outstanding_penalties' => $outstandingBalance['penalties'],
                    'status' => $loan->status,
                    'disbursed_date' => $loan->created_at ?? null
                ];
            })->toArray();

            Log::info('✅ Found loans prepared for selection', [
                'count' => count($this->foundLoans),
                'loan_ids' => array_column($this->foundLoans, 'loan_id')
            ]);

            session()->flash('success', count($this->foundLoans) . ' active loan(s) found. Please select one to proceed.');

            Log::info('✅ LOAN SEARCH COMPLETED SUCCESSFULLY');

        } catch (Exception $e) {
            Log::error('❌ LOAN SEARCH FAILED', [
                'search_type' => $this->loanSearchType,
                'search_value' => $this->loanSearchValue,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error searching for loan: ' . $e->getMessage());
        }
    }

    /**
     * Select a specific loan for payment from the found loans list
     *
     * @param string $loanId
     */
    public function selectLoanForPayment($loanId)
    {
        try {
            Log::info('🎯 Selecting loan for payment', [
                'loan_id' => $loanId,
                'user' => auth()->user()->name ?? 'Unknown'
            ]);

            // Get loan details from database
            $loan = DB::table('loans')
                ->leftJoin('clients', 'loans.client_number', '=', 'clients.client_number')
                ->select(
                    'loans.*',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name'
                )
                ->where('loans.loan_id', $loanId)
                ->first();

            if (!$loan) {
                session()->flash('error', 'Loan not found.');
                return;
            }

            // Calculate outstanding balance
            $loanRepaymentService = app(\App\Services\LoanRepaymentService::class);
            $outstandingBalance = $loanRepaymentService->calculateOutstandingBalances($loan);

            // Build member name
            $memberName = 'N/A';
            if ($loan->first_name || $loan->last_name) {
                $memberName = trim(($loan->first_name ?? '') . ' ' . ($loan->middle_name ?? '') . ' ' . ($loan->last_name ?? ''));
            } elseif ($loan->client_number) {
                $memberName = 'Client: ' . $loan->client_number;
            }

            // Set selected loan
            $this->selectedLoan = [
                'loan_id' => $loan->loan_id,
                'account_number' => $loan->loan_account_number,
                'member_name' => $memberName,
                'member_number' => $loan->client_number,
                'principal' => $loan->principle,
                'outstanding' => $outstandingBalance['total'],
                'outstanding_principal' => $outstandingBalance['principal'],
                'outstanding_interest' => $outstandingBalance['interest'],
                'outstanding_penalties' => $outstandingBalance['penalties'],
            ];

            Log::info('✅ Loan selected', [
                'loan_id' => $this->selectedLoan['loan_id'],
                'member' => $this->selectedLoan['member_name'],
                'outstanding' => $this->selectedLoan['outstanding']
            ]);

            // Load member's savings accounts
            $accounts = DB::table('accounts')
                ->where('client_number', $loan->client_number)
                ->where('product_number', 2000) // Savings product
                ->where('status', 'ACTIVE')
                ->get();

            $this->memberSavingsAccounts = $accounts->map(function ($account) {
                return [
                    'account_number' => $account->account_number,
                    'account_name' => $account->account_name,
                    'balance' => $account->balance,
                    'status' => $account->status
                ];
            })->toArray();

            Log::info('🏦 Member savings accounts loaded', [
                'count' => count($this->memberSavingsAccounts),
                'accounts' => array_column($this->memberSavingsAccounts, 'account_number')
            ]);

            // Clear found loans since we've selected one
            $this->foundLoans = [];

            session()->flash('success', 'Loan selected successfully!');

        } catch (Exception $e) {
            Log::error('❌ Error selecting loan', [
                'loan_id' => $loanId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error selecting loan: ' . $e->getMessage());
        }
    }

    /**
     * Update source account balance when account is selected
     */
    public function updatedPayLoanSourceAccount($value)
    {
        if ($value) {
            $account = DB::table('accounts')
                ->where('account_number', $value)
                ->first();

            $this->payLoanSourceAccountBalance = $account ? $account->balance : 0;
        } else {
            $this->payLoanSourceAccountBalance = 0;
        }
    }

    /**
     * Submit loan payment
     */
    public function submitPayLoan()
    {
        // Validate
        $this->validate([
            'loanPaymentAmount' => 'required|numeric|min:100',
            'payLoanSourceAccount' => 'required|string'
        ]);

        // Check if loan is selected
        if (!$this->selectedLoan) {
            session()->flash('error', 'Please select a loan first.');
            return;
        }

        // Check sufficient balance
        if ($this->loanPaymentAmount > $this->payLoanSourceAccountBalance) {
            session()->flash('error', 'Insufficient funds in savings account.');
            return;
        }

        try {
            DB::beginTransaction();

            // Get source account details
            $sourceAccount = AccountsModel::where('account_number', $this->payLoanSourceAccount)->first();
            if (!$sourceAccount) {
                throw new Exception('Source savings account not found.');
            }

            // Get loan account details
            $loanAccount = AccountsModel::where('account_number', $this->selectedLoan['account_number'])->first();
            if (!$loanAccount) {
                throw new Exception('Loan account not found.');
            }

            // Process loan repayment using LoanRepaymentService
            $loanRepaymentService = app(\App\Services\LoanRepaymentService::class);
            $paymentDetails = [
                'narration' => $this->loanPaymentNarration ?: "Loan payment from savings account {$this->payLoanSourceAccount}",
                'reference' => 'SAV_' . time(),
                'source_account' => $this->payLoanSourceAccount
            ];

            $result = $loanRepaymentService->processRepayment(
                $this->selectedLoan['loan_id'],
                $this->loanPaymentAmount,
                'INTERNAL', // Internal transfer from savings
                $paymentDetails
            );

            // Deduct from savings account
            DB::table('accounts')
                ->where('account_number', $this->payLoanSourceAccount)
                ->decrement('balance', $this->loanPaymentAmount);

            // Note: Double-entry bookkeeping and general ledger posting is handled by LoanRepaymentService

            DB::commit();

            session()->flash('success', 'Loan payment processed successfully! Receipt: ' . $result['receipt_number']);

            // Reset form
            $this->resetPayLoanFields();
            $this->showPayLoanModal = false;

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Loan payment failed', [
                'loan_id' => $this->selectedLoan['loan_id'] ?? null,
                'amount' => $this->loanPaymentAmount,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error processing loan payment: ' . $e->getMessage());
        }
    }

    /**
     * Reset pay loan fields
     */
    private function resetPayLoanFields()
    {
        $this->loanSearchType = 'loan_id';
        $this->loanSearchValue = '';
        $this->foundLoans = [];
        $this->selectedLoan = null;
        $this->payLoanSourceAccount = '';
        $this->payLoanSourceAccountBalance = 0;
        $this->loanPaymentAmount = '';
        $this->loanPaymentNarration = '';
        $this->memberSavingsAccounts = [];
    }

    // ==================== TRANSFER TO DEPOSITS METHODS ====================

    /**
     * Show Transfer to Deposits Modal
     */
    public function showTransferToDepositsModal()
    {
        $this->selected = 14;
        $this->showTransferToDepositsModal = true;
        $this->resetTransferFields();
    }

    /**
     * Verify SOURCE member for transfer
     */
    public function verifySourceMemberForTransfer()
    {
        Log::info('👤 SOURCE MEMBER VERIFICATION INITIATED', [
            'member_number' => $this->transferSourceMemberNumber,
            'user' => auth()->user()->name ?? 'Unknown'
        ]);

        $this->validate([
            'transferSourceMemberNumber' => 'required|string'
        ]);

        try {
            $client = DB::table('clients')
                ->where('client_number', $this->transferSourceMemberNumber)
                ->first();

            if (!$client) {
                session()->flash('error', 'Source member not found: ' . $this->transferSourceMemberNumber);
                $this->transferSourceVerifiedMember = null;
                $this->transferSourceMemberAccounts = [];
                return;
            }

            // Set verified source member
            $this->transferSourceVerifiedMember = [
                'name' => trim("{$client->first_name} {$client->middle_name} {$client->last_name}"),
                'client_number' => $client->client_number,
            ];

            // Load ALL member's accounts (savings AND deposits)
            $allAccounts = DB::table('accounts')
                ->where('client_number', $client->client_number)
                ->whereIn('product_number', [2000, 3000]) // Savings (2000) + Deposits (3000)
                ->where('status', 'ACTIVE')
                ->get();

            $this->transferSourceMemberAccounts = $allAccounts->map(function ($account) {
                $type = $account->product_number == 2000 ? 'Savings' : 'Deposit';
                return [
                    'account_number' => $account->account_number,
                    'account_name' => $account->account_name,
                    'balance' => $account->balance,
                    'product_number' => $account->product_number,
                    'type' => $type,
                    'display_name' => "[$type] {$account->account_name} ({$account->account_number}) - TZS " . number_format($account->balance, 2)
                ];
            })->toArray();

            Log::info('✅ Source member verified', [
                'client_number' => $this->transferSourceVerifiedMember['client_number'],
                'accounts_count' => count($this->transferSourceMemberAccounts)
            ]);

            session()->flash('success', 'Source member verified successfully!');

        } catch (Exception $e) {
            Log::error('❌ SOURCE MEMBER VERIFICATION FAILED', [
                'member_number' => $this->transferSourceMemberNumber,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error verifying source member: ' . $e->getMessage());
        }
    }

    /**
     * Verify DESTINATION member for transfer
     */
    public function verifyDestinationMemberForTransfer()
    {
        Log::info('👤 DESTINATION MEMBER VERIFICATION INITIATED', [
            'member_number' => $this->transferDestinationMemberNumber,
            'user' => auth()->user()->name ?? 'Unknown'
        ]);

        $this->validate([
            'transferDestinationMemberNumber' => 'required|string'
        ]);

        try {
            $client = DB::table('clients')
                ->where('client_number', $this->transferDestinationMemberNumber)
                ->first();

            if (!$client) {
                session()->flash('error', 'Destination member not found: ' . $this->transferDestinationMemberNumber);
                $this->transferDestinationVerifiedMember = null;
                $this->transferDestinationMemberAccounts = [];
                return;
            }

            // Set verified destination member
            $this->transferDestinationVerifiedMember = [
                'name' => trim("{$client->first_name} {$client->middle_name} {$client->last_name}"),
                'client_number' => $client->client_number,
            ];

            // Load ALL member's accounts (savings AND deposits)
            $allAccounts = DB::table('accounts')
                ->where('client_number', $client->client_number)
                ->whereIn('product_number', [2000, 3000]) // Savings (2000) + Deposits (3000)
                ->where('status', 'ACTIVE')
                ->get();

            $this->transferDestinationMemberAccounts = $allAccounts->map(function ($account) {
                $type = $account->product_number == 2000 ? 'Savings' : 'Deposit';
                return [
                    'account_number' => $account->account_number,
                    'account_name' => $account->account_name,
                    'balance' => $account->balance,
                    'product_number' => $account->product_number,
                    'type' => $type,
                    'display_name' => "[$type] {$account->account_name} ({$account->account_number}) - TZS " . number_format($account->balance, 2)
                ];
            })->toArray();

            Log::info('✅ Destination member verified', [
                'client_number' => $this->transferDestinationVerifiedMember['client_number'],
                'accounts_count' => count($this->transferDestinationMemberAccounts)
            ]);

            session()->flash('success', 'Destination member verified successfully!');

        } catch (Exception $e) {
            Log::error('❌ DESTINATION MEMBER VERIFICATION FAILED', [
                'member_number' => $this->transferDestinationMemberNumber,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error verifying destination member: ' . $e->getMessage());
        }
    }

    /**
     * LEGACY: Verify member for transfer (kept for backward compatibility)
     */
    public function verifyMemberForTransfer()
    {
        Log::info('👤 MEMBER VERIFICATION INITIATED', [
            'member_number' => $this->transferMemberNumber,
            'user' => auth()->user()->name ?? 'Unknown'
        ]);

        $this->validate([
            'transferMemberNumber' => 'required|string'
        ]);

        try {
            // Find member
            $query = DB::table('clients')
                ->where('client_number', $this->transferMemberNumber);

            Log::info('📋 Searching for member', [
                'member_number' => $this->transferMemberNumber,
                'sql' => $query->toSql()
            ]);

            $client = $query->first();

            Log::info('📊 Query executed', [
                'client_found' => $client ? 'YES' : 'NO',
                'client_data' => $client ? [
                    'client_number' => $client->client_number ?? 'N/A',
                    'membership_number' => $client->membership_number ?? 'N/A',
                    'name' => ($client->first_name ?? '') . ' ' . ($client->last_name ?? '')
                ] : null
            ]);

            if (!$client) {
                Log::warning('❌ Member not found', [
                    'member_number' => $this->transferMemberNumber
                ]);

                session()->flash('error', 'Member not found with number: ' . $this->transferMemberNumber);
                $this->transferVerifiedMember = null;
                $this->transferMemberSavingsAccounts = [];
                $this->transferMemberDepositAccounts = [];
                return;
            }

            // Set verified member
            $this->transferVerifiedMember = [
                'name' => trim("{$client->first_name} {$client->middle_name} {$client->last_name}"),
                'client_number' => $client->client_number,
            ];

            Log::info('✅ Member verified', [
                'client_number' => $this->transferVerifiedMember['client_number'],
                'name' => $this->transferVerifiedMember['name']
            ]);

            // Load member's savings accounts (product_number = 2000)
            $savingsAccounts = DB::table('accounts')
                ->where('client_number', $client->client_number)
                ->where('product_number', 2000) // Savings
                ->where('status', 'ACTIVE')
                ->get();

            // Convert to array for Livewire serialization
            $this->transferMemberSavingsAccounts = $savingsAccounts->map(function ($account) {
                return [
                    'account_number' => $account->account_number,
                    'account_name' => $account->account_name,
                    'balance' => $account->balance,
                    'status' => $account->status
                ];
            })->toArray();

            Log::info('💰 Savings accounts loaded', [
                'count' => count($this->transferMemberSavingsAccounts),
                'accounts' => array_column($this->transferMemberSavingsAccounts, 'account_number')
            ]);

            // Load member's deposit accounts (product_number = 3000)
            $depositAccounts = DB::table('accounts')
                ->where('client_number', $client->client_number)
                ->where('product_number', 3000) // Deposits
                ->where('status', 'ACTIVE')
                ->get();

            // Convert to array for Livewire serialization
            $this->transferMemberDepositAccounts = $depositAccounts->map(function ($account) {
                return [
                    'account_number' => $account->account_number,
                    'account_name' => $account->account_name,
                    'balance' => $account->balance,
                    'status' => $account->status
                ];
            })->toArray();

            Log::info('🏦 Deposit accounts loaded', [
                'count' => count($this->transferMemberDepositAccounts),
                'accounts' => array_column($this->transferMemberDepositAccounts, 'account_number')
            ]);

            session()->flash('success', 'Member verified successfully!');

            Log::info('✅ MEMBER VERIFICATION COMPLETED SUCCESSFULLY');

        } catch (Exception $e) {
            Log::error('❌ MEMBER VERIFICATION FAILED', [
                'member_number' => $this->transferMemberNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error verifying member: ' . $e->getMessage());
        }
    }

    /**
     * Update source account balance when account is selected
     */
    public function updatedTransferSourceAccount($value)
    {
        if ($value) {
            $account = DB::table('accounts')
                ->where('account_number', $value)
                ->first();

            if ($account) {
                $this->transferSourceAccountBalance = $account->balance;
                $this->transferSourceAccountType = $account->product_number == 2000 ? 'savings' : 'deposit';

                Log::info('✅ Source account selected', [
                    'account_number' => $value,
                    'type' => $this->transferSourceAccountType,
                    'balance' => $this->transferSourceAccountBalance
                ]);
            }
        } else {
            $this->transferSourceAccountBalance = 0;
            $this->transferSourceAccountType = '';
        }
    }

    /**
     * LEGACY: Update source savings account balance (backward compatibility)
     */
    public function updatedTransferSourceSavingsAccount($value)
    {
        if ($value) {
            $account = DB::table('accounts')
                ->where('account_number', $value)
                ->first();

            $this->transferSourceSavingsBalance = $account ? $account->balance : 0;
        } else {
            $this->transferSourceSavingsBalance = 0;
        }
    }

    /**
     * Submit transfer between any member accounts (Savings or Deposits)
     */
    public function submitTransferToDeposits()
    {
        Log::info('💸 INTER-MEMBER ACCOUNT TRANSFER INITIATED', [
            'source_member' => $this->transferSourceMemberNumber,
            'source_account' => $this->transferSourceAccount,
            'destination_member' => $this->transferDestinationMemberNumber,
            'destination_account' => $this->transferDestinationAccount,
            'amount' => $this->transferAmount,
            'user' => auth()->user()->name ?? 'Unknown'
        ]);

        // Validate
        $this->validate([
            'transferSourceMemberNumber' => 'required|string',
            'transferSourceAccount' => 'required|string',
            'transferDestinationMemberNumber' => 'required|string',
            'transferDestinationAccount' => 'required|string',
            'transferAmount' => 'required|numeric|min:0.01'
        ]);

        // Check sufficient balance
        if ($this->transferAmount > $this->transferSourceAccountBalance) {
            Log::warning('❌ Insufficient funds', [
                'required' => $this->transferAmount,
                'available' => $this->transferSourceAccountBalance
            ]);
            session()->flash('error', 'Insufficient funds in source account.');
            return;
        }

        // Prevent transfer to same account
        if ($this->transferSourceAccount === $this->transferDestinationAccount) {
            session()->flash('error', 'Cannot transfer to the same account.');
            return;
        }

        try {
            DB::beginTransaction();

            Log::info('🔍 Looking up accounts...', [
                'source_account_number' => $this->transferSourceAccount,
                'destination_account_number' => $this->transferDestinationAccount
            ]);

            // Get source account
            $sourceAccount = AccountsModel::where('account_number', $this->transferSourceAccount)->first();

            if (!$sourceAccount) {
                throw new Exception('Source account not found: ' . $this->transferSourceAccount);
            }

            Log::info('📋 Source account found', [
                'account_number' => $sourceAccount->account_number,
                'account_name' => $sourceAccount->account_name,
                'type' => $sourceAccount->product_number == 2000 ? 'Savings' : 'Deposit',
                'balance' => $sourceAccount->balance
            ]);

            // Get destination account
            $destinationAccount = AccountsModel::where('account_number', $this->transferDestinationAccount)->first();

            if (!$destinationAccount) {
                throw new Exception('Destination account not found: ' . $this->transferDestinationAccount);
            }

            Log::info('📋 Destination account found', [
                'account_number' => $destinationAccount->account_number,
                'account_name' => $destinationAccount->account_name,
                'type' => $destinationAccount->product_number == 2000 ? 'Savings' : 'Deposit',
                'balance' => $destinationAccount->balance
            ]);

            // Build transfer narration
            $sourceType = $sourceAccount->product_number == 2000 ? 'Savings' : 'Deposit';
            $destType = $destinationAccount->product_number == 2000 ? 'Savings' : 'Deposit';
            $sourceMember = $this->transferSourceVerifiedMember['name'] ?? $this->transferSourceMemberNumber;
            $destMember = $this->transferDestinationVerifiedMember['name'] ?? $this->transferDestinationMemberNumber;

            $narration = $this->transferNarration ?: "Transfer from {$sourceMember} {$sourceType} ({$sourceAccount->account_number}) to {$destMember} {$destType} ({$destinationAccount->account_number})";

            // Post transaction using TransactionPostingService
            $transactionService = new TransactionPostingService();

            $transactionData = [
                'first_account' => $sourceAccount->account_number,
                'second_account' => $destinationAccount->account_number,
                'amount' => $this->transferAmount,
                'narration' => $narration,
                'source_account' => $sourceAccount->account_number,
                'destination_account' => $destinationAccount->account_number,
            ];

            Log::info('📤 Posting inter-member transfer', [
                'from' => "{$sourceMember} ({$sourceType})",
                'to' => "{$destMember} ({$destType})",
                'amount' => $this->transferAmount
            ]);

            $result = $transactionService->postTransaction($transactionData);

            Log::info('✅ INTER-MEMBER TRANSFER COMPLETED SUCCESSFULLY', [
                'source' => $sourceAccount->account_number,
                'destination' => $destinationAccount->account_number,
                'amount' => $this->transferAmount,
                'reference_number' => $result['reference_number'] ?? 'N/A'
            ]);

            DB::commit();

            session()->flash('success', 'Transfer completed successfully! TZS ' . number_format($this->transferAmount, 2) . " transferred from {$sourceMember}'s {$sourceType} to {$destMember}'s {$destType}.");

            // Reset form
            $this->resetTransferFields();
            $this->showTransferToDepositsModal = false;

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Transfer to deposits failed', [
                'source' => $this->transferSourceSavingsAccount,
                'destination' => $this->transferDestinationDepositAccount,
                'amount' => $this->transferAmount,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error processing transfer: ' . $e->getMessage());
        }
    }

    /**
     * Reset transfer fields
     */
    private function resetTransferFields()
    {
        // New properties
        $this->transferSourceMemberNumber = '';
        $this->transferSourceVerifiedMember = null;
        $this->transferSourceAccount = '';
        $this->transferSourceAccountBalance = 0;
        $this->transferSourceAccountType = '';
        $this->transferSourceMemberAccounts = [];

        $this->transferDestinationMemberNumber = '';
        $this->transferDestinationVerifiedMember = null;
        $this->transferDestinationAccount = '';
        $this->transferDestinationAccountType = '';
        $this->transferDestinationMemberAccounts = [];

        $this->transferAmount = '';
        $this->transferNarration = '';

        // Legacy properties (for backward compatibility)
        $this->transferMemberNumber = '';
        $this->transferVerifiedMember = null;
        $this->transferSourceSavingsAccount = '';
        $this->transferSourceSavingsBalance = 0;
        $this->transferDestinationDepositAccount = '';
        $this->transferMemberSavingsAccounts = [];
        $this->transferMemberDepositAccounts = [];
    }

    /**
     * Override to specify the module name for permissions
     *
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'savings';
    }
}
