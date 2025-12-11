<?php
namespace App\Http\Livewire\Shares;

use Illuminate\Support\Facades\Config;
use Livewire\Component;
use App\Traits\Livewire\WithModulePermissions;
use App\Models\SharesModel;
use App\Models\approvals;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Services\TransactionPostingService;
use App\Models\AccountsModel;
use App\Models\ClientsModel;
use App\Helper\NewMemberUpdateStatusHelper;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;
use App\Models\issured_shares;
use App\Models\general_ledger;
use App\Models\sub_products;
use App\Models\User;
use App\Models\DividendModel;
use App\Models\NotificationModel;
use App\Models\ShareTransaction;
use App\Models\AuditLog;
use Livewire\WithPagination;
use Asantibanez\LivewireCharts\Models\LineChartModel;
use Asantibanez\LivewireCharts\Models\PieChartModel;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;
use App\Services\AccountCreationService;
use App\Models\TeamUser;
use App\Models\ShareRegister;
use App\Models\ShareOwnership;
use App\Models\IssuedShares;
use App\Models\Share;
use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Models\ShareAccount;
use App\Models\Member;
use App\Models\ShareWithdrawal;
use App\Models\ShareTransfer;
use App\Models\SubProduct;


class Shares extends Component
{
    use WithPagination;
    use WithFileUploads;
    use WithModulePermissions;

    public $tab_id = '10';
    public $title = 'Shares list';

    public $selected;
    public $activeSharesCount;
    public $inactiveSharesCount;
    public $showCreateNewSharesAccount;
    public $name;
    public $region;
    public $wilaya;
    public $membershipNumber;
    public $parentSharesAccount;
    public $showDeleteSharesAccount;
    public $SharesAccountSelected;
    public $activationMode = false; // Add this property for activation mode
    public $showEditSharesAccount;
    public $pendingSharesAccount;
    public $SharesList;
    public $pendingSharesAccountname;
    public $SharesAccount;
    public $showAddSharesAccount;
    public $email;
    public $Sharesstatus;
    public $permission = 'BLOCKED';
    public $password;
    public $shareTransactions = []; // Add this line

    // Member and Share Account Properties
    public $member;
    public $memberDetails = null;
    public $shareAccount;
    public $totalShares = 0;
    public $currentShareValue = 0;
    public $totalShareWorth = 0;
    public $transactions;
    public $status;

    public $product;
    public $number_of_shares;
    public $linked_savings_account;
    public $account_number;
    public $balance;
    public $nominal_price;
    public $showIssueNewShares;
    public $product_price_on;
    public $product_id_on;

    public $accountSelected;
    public $sub_product_number;
    public $sharesAvailable = 0;

    // Modal Properties
    public $showCreateShareAccount = false;
    public $showEditShareAccount = false;
    public $showDeleteShareAccount = false;
    public $showPendingTransactions = false;
    public $showAdjustBalance = false;
    public $showDeclareDividend = false;
    public $showSetSharePrice = false;
    public $showBulkUpload = false;
    public $showAuditLogs = false;
    public $showSmsSettings = false;

    // Form Properties
    public $account_name;
    public $membership_number;
    public $parent_share_account;
    public $parentAccounts = [];

    public $members = [];
    public $dividends = [];
    public $notifications = [];

    // Chart and Statistics Properties
    public $otherContributions = 0;
    public $shareValueHistory = [];
    public $availableForWithdrawal = 0;
    public $minimumRequiredShares = 0;
    public $lockInPeriod = 0;
    public $dividendEligibilityPeriod = 0;
    public $dividendPaymentFrequency = '';

    // Share Action Properties
    public $investmentAmount = 0;
    public $paymentMethod = '';
    public $withdrawalShares = 0;
    public $withdrawalReason = '';
    public $recipientMember = '';
    public $transferShares = 0;
    public $transferReason = '';

    // Share Account Properties
    protected $shareAccounts = [];
    public $shares_limit_exceeded = false;
    public $userShares = 0;

    // Modal Display Properties
    public $showViewTransactions = false;
    public $selectedAccount = null;
    protected $pendingTransactions = [];
    public $search = '';

    // Balance Adjustment Properties
    public $selectedAccountId = '';
    public $adjustmentType = '';
    public $shares = 0;
    public $pricePerShare = 0;
    public $narration = '';

    // Dividend properties
    public $dividendYear;
    public $dividendRate;
    public $dividendAmount;
    public $dividendPaymentMode;
    public $dividendNarration;

    // Share price properties
    public $newSharePrice;
    public $effectiveDate;

    // Bulk upload properties
    public $uploadFile;

    // SMS settings properties
    public $smsSenderName;
    public $smsApiKey;
    public $smsEnabled = false;

    // Audit logs properties
    public $searchLogs = '';
    protected $auditLogs;

    public $totalSharesIssued = 0;
    public $sharesIssuedToday = 0;
    public $totalShareCapital = 0;
    public $averageSharePrice = 0;
    public $totalDividendsThisYear = 0;
    public $monthlySharePurchases = 0;
    public $totalSavings = 0;
    public $totalDeposits = 0;
    public $monthlyPurchasesData = [];
    public $topShareholders = [];
    public $shareTypeDistribution = [];
    public $shareValueByProduct = [];
    public $totalMembers = 0;
    public $max_allowed_shares = 0;
    public $current_member_shares = 0;
    public $selectedProduct = null;
    public $total_value = 0;

    public $showViewAccountModal = false;
    protected $selectedAccountDetails = null;

    public $institution_id = 1; // Fixed institution ID

    // Add date properties
    protected $startOfYear;
    protected $endOfYear;

    public $memberName;
    public $availableProducts = [];

    // Chart Models
    protected $lineChartModel;
    protected $pieChartModel;
    protected $columnChartModel;

    public $shareProducts = [];
    public $selectedShareProduct;
    public $shareProductDetails;
    public $selectedProductAccounts = [];
    public $loading = false;

    // Modal state
    public $showIssueShares = false;
    public $showRedeemShares = false;

    // Form properties
    public $savingsAccounts = [];

    // Redemption properties
    public $redemption_product = '';
    public $redemption_client_number = '';
    public $redemption_number_of_shares = 0;
    public $redemption_price_per_share = 0;
    public $redemption_total_value = 0;
    public $redemption_linked_savings_account = '';
    public $redemption_share_account = '';
    public $redemptionSelectedProduct = null;
    public $redemptionMemberDetails = null;

    // Dashboard Summary Properties
    public $totalAuthorizedShares = 0;
    public $totalIssuedShares = 0;
    public $totalPaidUpShares = 0;
    public $totalShareValue = 0;
    public $pendingShareIssuances = 0;
    public $recentShareTransactions = [];
    public $monthlyShareIssuance = [];
    public $totalShareProducts = 0;
    public $activeShareProducts = 0;
    public $total_issued_shares = 0;
    public $price_per_share = 0;

    // Member Search Properties
    public $client_number = '';
    public $memberFound = false;

    public $memberShareTypes = [];
    public $receivingAccounts = [];

    // Share Withdrawal Properties
    public $selectedSourceAccount = null;
    public $selectedReceivingAccount = null;

    public $selectedShareProducts = []; // Array to store selected share products and their withdrawal amounts
    public $totalWithdrawalValue = 0;
    public $totalWithdrawalShares = 0;

    public $showShareWithdrawal = false;
    public $showShareTransferModalx = false;
    public $sender_client_number = '';
    public $receiver_client_number = '';
    public $senderMemberDetails = null;
    public $receiverMemberDetails = null;
    public $senderShareTypes = [];
    public $receiverShareTypes = [];
    public $sender_share_type = '';
    public $transfer_shares = 1;
    public $transfer_reason = '';
    public $recentTransfers = [];
    public $sourceAccounts = [];  // Add this property for share accounts
    public $selectedShareType = null;
    public $showShareWithdrawalReport = false;
    public $showWithdrawalDetails = false;
    public $selectedWithdrawal = null;
    public $shareStatementData = null;
    public $showShareStatement = false;
    public $shareReceiptData = null;
    public $showShareReceiptModal = false;
    public $dateFrom = null;
    public $dateTo = null;
    public $withdrawalStatus = '';
    public $withdrawals = [];
    public $totalWithdrawals = 0;
    public $totalSharesWithdrawn = 0;
    public $receiver_share_type = '';
    public $selectedReceiverShareType = null;
    public $share_account = null;
    public $totalValue = 0; // Define the property

    // Share Transfer Properties

    public $transferValidationErrors = [];

    // Add these properties at the top of your class
    public $showShareTransfersReport = false;
    public $showTransferDetails = false;
    public $transfer_date_range = 'this_month';
    public $transfer_start_date;
    public $transfer_end_date;
    public $transfer_status = '';
    public $selectedTransfer;
    public $shareTransfers = [];
    public $shareTransfersPaginator = [];
    protected $shareTransfersLinks;

    public $dividendOverview = false;
    public $showBulkUploadArea = false;
    public $showShareStatementView = false;

    public $homeDashboard = true;

    public $sidebar_view = 'home_dashboard';

    public function showBulkUpload()
    {
        $this->sidebar_view = 'bulk_upload';
        $this->showBulkUploadArea = true;
        $this->dividendOverview = false;
        $this->showShareStatementView = false;
        $this->homeDashboard = false;
    }

    public function homeDashboard()
    {
        $this->sidebar_view = 'home_dashboard';
        $this->homeDashboard = true;
        $this->showBulkUploadArea = false;
        $this->dividendOverview = false;
        $this->showShareStatementView = false;
    }


    public function showDividendOverview()
    {
        $this->sidebar_view = 'dividend_overview';
        $this->homeDashboard = false;
        $this->dividendOverview = true;
        $this->showBulkUploadArea = false;
        $this->showShareStatementView = false;
    }

    public function showShareStatementView()
    {
        $this->sidebar_view = 'share_statement';
        $this->homeDashboard = false;
        $this->dividendOverview = false;
        $this->showBulkUploadArea = false;
        $this->showShareStatementView = true;
    }

    // Add these methods to your class
    public function getShareTransfersLinksProperty()
    {
        return $this->shareTransfersLinks;
    }

    public function openShareTransfersReport()
    {
        $this->sidebar_view = 'share_transfers_report';      
        $this->showShareTransfersReport = true;
        $this->loadShareTransfers();
    }

    public function closeShareTransfersReport()
    {
        $this->showShareTransfersReport = false;

    }

    public function viewTransferDetails($transferId)
    {
        $this->sidebar_view = 'share_transfers_report';
        $this->selectedTransfer = DB::table('share_transfers')->find($transferId);
        $this->showTransferDetails = true;
    }

    public function closeTransferDetails()
    {
        $this->showTransferDetails = false;
        $this->selectedTransfer = null;
    }

    public function loadShareTransfers()
    {
        $this->sidebar_view = 'share_transfers_report';
        try {
            Log::info('[ShareTransfers] Starting to load share transfers');

            $query = DB::table('share_transfers')
                ->select([
                    'share_transfers.*',
                    'sender.member_number as sender_member_number',
                    'sender.first_name as sender_first_name',
                    'sender.last_name as sender_last_name',
                    'receiver.member_number as receiver_member_number',
                    'receiver.first_name as receiver_first_name',
                    'receiver.last_name as receiver_last_name'
                ])
                ->leftJoin('clients as sender', 'share_transfers.sender_member_id', '=', 'sender.id')
                ->leftJoin('clients as receiver', 'share_transfers.receiver_member_id', '=', 'receiver.id');

            Log::info('[ShareTransfers] Base query prepared');

            $start = now()->startOfDay();
            $end = now()->endOfDay();

            if ($this->transfer_date_range === 'custom' && $this->transfer_start_date && $this->transfer_end_date) {
                $start = Carbon::parse($this->transfer_start_date)->startOfDay();
                $end = Carbon::parse($this->transfer_end_date)->endOfDay();
                Log::info('[ShareTransfers] Custom date range applied', [
                    'start' => $start->toDateTimeString(),
                    'end' => $end->toDateTimeString(),
                ]);
            } else {
                $dateRange = $this->getDateRange();
                $start = $dateRange['start'];
                $end = $dateRange['end'];
                Log::info('[ShareTransfers] Predefined date range applied', [
                    'range' => $this->transfer_date_range,
                    'start' => $start->toDateTimeString(),
                    'end' => $end->toDateTimeString(),
                ]);
            }

            $query->whereBetween('share_transfers.created_at', [$start, $end]);

            if ($this->transfer_status) {
                $query->where('share_transfers.status', $this->transfer_status);
                Log::info('[ShareTransfers] Status filter applied', ['status' => $this->transfer_status]);
            }

            $paginator = $query->latest('share_transfers.created_at')->paginate(10);

            $this->shareTransfers = $paginator->items();
            $this->shareTransfersLinks = $paginator->render();

            Log::info('[ShareTransfers] Share transfers loaded successfully', [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage()
            ]);
        } catch (\Throwable $e) {
            Log::error('[ShareTransfers] Error loading share transfers', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => collect($e->getTrace())->take(5), // limit for brevity
            ]);

            $this->shareTransfers = [];
            $this->shareTransfersLinks = '';
        }
    }


    protected function getDateRange()
    {
        $now = now();

        $range = match ($this->transfer_date_range) {
            'today'      => ['start' => $now->startOfDay(), 'end' => $now->endOfDay()],
            'yesterday'  => ['start' => $now->copy()->subDay()->startOfDay(), 'end' => $now->copy()->subDay()->endOfDay()],
            'this_week'  => ['start' => $now->startOfWeek(), 'end' => $now->endOfWeek()],
            'last_week'  => ['start' => $now->copy()->subWeek()->startOfWeek(), 'end' => $now->copy()->subWeek()->endOfWeek()],
            'this_month' => ['start' => $now->startOfMonth(), 'end' => $now->endOfMonth()],
            'last_month' => ['start' => $now->copy()->subMonth()->startOfMonth(), 'end' => $now->copy()->subMonth()->endOfMonth()],
            default      => ['start' => $now->startOfDay(), 'end' => $now->endOfDay()],
        };

        Log::debug('[ShareTransfers] getDateRange result', [
            'range' => $this->transfer_date_range,
            'start' => $range['start']->toDateTimeString(),
            'end' => $range['end']->toDateTimeString(),
        ]);

        return $range;
    }

    // Add these listeners to handle real-time updates
    protected $listeners = [
        'transferApproved' => 'loadShareTransfers',
        'transferRejected' => 'loadShareTransfers',
    ];





    
    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        
        $this->sidebar_view = 'home_dashboard';
        $this->getShareSummary();
        $this->loadAvailableProducts();
        $this->loadShareProducts();
        $this->loadWithdrawals();
        $this->loadShareTransfers();
    }
    
    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'shares';
    }

    public function loadShareProducts()
    {
        $this->shareProducts = DB::table('sub_products')
            ->where('product_type', '1000')
            ->get();
    }

    public function updatedProduct($value)
    {
       
        if ($value) {
            $product = DB::table('sub_products')
                ->where('id', $value)
                ->first();

                // dd($product, $value);
                
            $this->selectedProduct = [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'nominal_price' => $product->nominal_price,
                'available_shares' => $product->available_shares,
                'shares_per_member' => 10,   //$product->shares_per_member, //to be reviewed now 10
                'min_balance' => $product->min_balance,
                'shares_allocated' => $product->shares_allocated
            ];
            
            $this->price_per_share = $product->nominal_price;
            $this->calculateTotalValue();
            
        } else {
            $this->selectedProduct = null;
            $this->price_per_share = 0;
            $this->total_value = 0;
        }
       
    }

    protected function getShareSummary()
    {
        try {
            // Get total shares and capital from share_registers
            $sharesSummary = DB::table('share_registers')
                ->selectRaw('
                    SUM(total_shares_issued) as total_authorized,
                    SUM(current_share_balance) as total_issued,
                    SUM(total_share_value) as total_capital,
                    AVG(current_price) as average_price,
                    COUNT(DISTINCT member_id) as total_members
                ')
                ->where('status', 'active')
                ->first();

            // Get pending share issuances
            $this->pendingShareIssuances = DB::table('issued_shares')
                ->where('status', 'pending')
                ->count();

            // Get recent share transactions
            $this->recentShareTransactions = DB::table('issued_shares')
                ->join('users', 'issued_shares.created_by', '=', 'users.id')
                ->select(
                    'issued_shares.*',
                    'users.name as posted_by_name'
                )
                ->latest('issued_shares.created_at')
                ->take(5)
                ->get();

            // Get share type distribution
            $this->shareTypeDistribution = DB::table('share_registers')
                ->join('sub_products', 'share_registers.product_id', '=', 'sub_products.id')
                ->select(
                    'sub_products.product_type',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(share_registers.current_share_balance) as total_shares'),
                    DB::raw('SUM(share_registers.total_share_value) as total_value'),
                    DB::raw('AVG(share_registers.current_price) as average_price')
                )
                ->where('share_registers.status', 'active')
                ->groupBy('sub_products.product_type')
                ->get();

            // Monthly share issuance statistics
            $this->monthlyShareIssuance = DB::table('issued_shares')
                ->select(
                    DB::raw('EXTRACT(MONTH FROM created_at)::integer as month'),
                    DB::raw('SUM(number_of_shares) as total_shares'),
                    DB::raw('SUM(total_value) as total_value'),
                    DB::raw('AVG(nominal_price) as average_price'),
                    DB::raw('COUNT(DISTINCT client_number) as total_members')
                )
                ->whereYear('created_at', now()->year)
                ->where('status', 'approved')
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get()
                ->map(function ($item) {
                    $item->month_name = Carbon::create()->month($item->month)->format('F');
                    return $item;
                });

            // Get top shareholders
            $this->topShareholders = DB::table('share_registers')
                ->join('clients', 'share_registers.member_id', '=', 'clients.id')
                ->select(
                    'clients.client_number',
                    'clients.first_name',
                    'clients.last_name',
                    'share_registers.total_share_value',
                    'share_registers.current_share_balance',
                    'share_registers.product_name',
                    'share_registers.current_price'
                )
                ->where('share_registers.status', 'active')
                ->orderBy('share_registers.total_share_value', 'desc')
                ->take(5)
                ->get();

            // Update the summary properties
            $this->totalAuthorizedShares = $sharesSummary->total_authorized ?? 0;
            $this->totalIssuedShares = $sharesSummary->total_issued ?? 0;
            $this->totalShareCapital = $sharesSummary->total_capital ?? 0;
            $this->averageSharePrice = $sharesSummary->average_price ?? 0;
            $this->totalMembers = $sharesSummary->total_members ?? 0;

        } catch (\Exception $e) {
            Log::error('Error loading share summary', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function setAccount($id){
        $account_number = AccountsModel::where('id',$id)->value('account_number');
        $this->accountSelected = $account_number;
    }

    public function goMo($gui){
        $account_number = AccountsModel::where('id',$gui)->value('account_number');
        // dd($account_number);
    }

    public function showAddSharesAccountModal($selected = null)
    {

        $this->sidebar_view = 'add_shares_account';
        $this->reset(['member', 'product', 'memberName']);
        $this->showCreateShareAccount = true;
        if ($selected) {
            $this->member = $selected;
            $this->updatedMember($selected);
        }
    }

    public function closeModal()
    {
        $this->showCreateShareAccount = false;
        $this->reset(['member', 'product', 'memberName']);
    }

    public function updatedSharesAccount(){
        $SharesAccountData = SharesModel::select('membershipNumber', 'name', 'region', 'wilaya', 'email')
        ->where('id', '=', $this->SharesAccount)
        ->get();
    foreach ($SharesAccountData as $SharesAccount){
        $this->membershipNumber=$SharesAccount->membershipNumber;
        $this->name=$SharesAccount->name;
        $this->region=$SharesAccount->region;
        $this->wilaya=$SharesAccount->wilaya;
        $this->email=$SharesAccount->email;
        $this->status=$SharesAccount->status;
    }
    }

    public function updateSharesAccount(){
        if (!$this->authorize('edit', 'You do not have permission to update share accounts')) {
            return;
        }
        
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
                'process_id' => $this->SharesAccount,
                'user_id' => Auth::user()->id
            ],
            [
                'institution' => $this->SharesAccount,
                'process_name' => 'editSharesAccount',
                'process_description' => 'has edited a SharesAccount',
                'approval_process_description' => 'has approved changes to a SharesAccount',
                'process_code' => '02',
                'process_id' => $this->SharesAccount,
                'process_status' => 'Pending',
                'user_id'  => Auth::user()->id,
                'team_id'  => $this->SharesAccount,
                'edit_package'=> json_encode($data)
            ]
        );
        Session::flash('message', 'Awaiting approval');
        Session::flash('alert-class', 'alert-success');
        $this->resetData();
        $this->showAddSharesAccount = false;
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

    public function generate_account_number($branch_code, $product_code) {
        do {
            $unique_identifier = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $partial_account_number = $branch_code . $unique_identifier . $product_code;
            $checksum = (10 - $this->luhn_checksum($partial_account_number . '0')) % 10;
            $full_account_number = $partial_account_number . $checksum;
            $is_unique = !AccountsModel::where('account_number', $full_account_number)->exists();
        } while (!$is_unique);
        return $full_account_number;
    }

    public function createNewAccount($major_category_code,$category_code,$sub_category_code,$account_name,$client_number )
    {
        $account_number = $this->generate_account_number(auth()->user()->branch, $sub_category_code);
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
            'product_number'=>1000,
            'notes' => 'account on member on boarding',
            'bank_id' => null,
            'mirror_account' => null,
            'account_level' => '3',
        ];
        return $account_number;
    }

    public function addSharesAccount(){        
        $productAccount = sub_products::where('id',$this->product)->first();        
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
                'product_number' => '1000',
                'member_number' => $this->member,
                'branch_number' => auth()->user()->branch
            ], $parent->account_number);


            $newAccountData = [
                'account_use' => 'external',
                'institution_number'=> '1000',
                'branch_number'=> Auth::user()->branch,
                'client_number'=> $this->member,
                'product_number'=> '1000',
                'sub_product_number'=>  $this->product,
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
                'process_name' => 'create_share_account',
                'process_description' => Auth::user()->name .  ' has added a new share account ' .$memberName,
                'approval_process_description' => 'Share issuance approval required',
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
            $this->showCreateShareAccount = false;
            $this->resetData();
        }catch(\Exception $e){
            DB::rollBack();
            // dd($e->getMessage());
            return ;
        }
    }

    public function showIssueSharesModal()
    {
        $this->sidebar_view = 'issue_shares';
        $this->showIssueShares = true;      
        $this->selectedProduct = null;
        $this->reset('product', 'client_number', 'number_of_shares', 'linked_savings_account', 'share_account', 'total_value');
        $this->resetValidation();
    }

    protected function validateShareIssuance()
    {
        $rules = [
            'product' => 'required|exists:sub_products,id',
            'client_number' => 'required|string|size:5',
            'number_of_shares' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($this->selectedProduct && $value > $this->selectedProduct['available_shares']) {
                        $fail('Number of shares exceeds available shares in the product.');
                    }
                }
            ],
            'linked_savings_account' => 'required|exists:accounts,account_number',
            'share_account' => 'required|exists:accounts,account_number'
        ];

        $messages = [
            'product.required' => 'Please select a share product.',
            'product.exists' => 'Selected share product is invalid.',
            'client_number.required' => 'Client number is required.',
            'client_number.size' => 'Client number must be exactly 5 digits.',
            'number_of_shares.required' => 'Number of shares is required.',
            'number_of_shares.numeric' => 'Number of shares must be a number.',
            'number_of_shares.min' => 'Number of shares must be at least 1.',
            'linked_savings_account.required' => 'Please select a linked savings account.',
            'linked_savings_account.exists' => 'Selected savings account is invalid.',
            'share_account.required' => 'Please select a share account.',
            'share_account.exists' => 'Selected share account is invalid.'
        ];

        $this->validate($rules, $messages);
    }

    protected function validateMemberStatus()
    {
        //dd($this->memberDetails);
        if (!$this->memberDetails) {
            $this->addError('client_number', 'Member not found.');
            return false;
        }

        if ($this->memberDetails['status'] !== 'ACTIVE') {
            $this->addError('client_number', 'Member account is not active.');
            return false;
        }

        return true;
    }

    protected function validateAccountBalance()
    {
        $selectedAccount = DB::table('accounts')->where('account_number', $this->linked_savings_account)->first();

        //dd($selectedAccount);
        
        if (!$selectedAccount) {
            $this->addError('linked_savings_account', 'Selected savings account not found.');
            return false;
        }

        if ($selectedAccount->balance < $this->total_value) {
            $this->addError('linked_savings_account', 'Insufficient balance in the selected savings account.');
            return false;
        }

        return true;
    }

    public function issueShares()
    {
        if (!$this->authorize('create', 'You do not have permission to issue shares')) {
            return;
        }
        
        try {
            Log::info('Starting share issuance process', [
                'client_number' => $this->client_number,
                'product_id' => $this->product,
                'number_of_shares' => $this->number_of_shares,
                'price_per_share' => $this->price_per_share,
                'total_value' => $this->total_value,
                'user_id' => auth()->id()
            ]);

            $this->total_value = $this->number_of_shares * $this->price_per_share;

            // Validate all inputs
            Log::info('Validating share issuance inputs');
            try {
                $this->validateShareIssuance();
                Log::info('Share issuance inputs validated successfully');
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::warning('Share issuance validation failed', [
                    'errors' => $e->errors(),
                    'client_number' => $this->client_number
                ]);
                
                session()->flash('validation_errors', $e->errors());
                return;
            }
            
            // Validate member status
            Log::info('Validating member status', ['client_number' => $this->client_number]);
            if (!$this->validateMemberStatus()) {
                Log::warning('Member status validation failed', ['client_number' => $this->client_number]);
                session()->flash('error', 'Member status validation failed');
                return;
            }
            Log::info('Member status validated successfully');

            // Validate account balance
            Log::info('Validating account balance', [
                'linked_savings_account' => $this->linked_savings_account,
                'total_value' => $this->total_value
            ]);
            if (!$this->validateAccountBalance()) {
                Log::warning('Account balance validation failed', [
                    'linked_savings_account' => $this->linked_savings_account,
                    'total_value' => $this->total_value
                ]);
                session()->flash('error', 'Insufficient account balance');
                return;
            }
            Log::info('Account balance validated successfully');

            DB::beginTransaction();
            Log::info('Database transaction started');

            // Generate reference number
            $referenceNumber = 'SH' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Create share issuance record
            Log::info('Creating share issuance record', [
                'client_number' => $this->client_number,
                'product_id' => $this->product,
                'number_of_shares' => $this->number_of_shares,
                'nominal_price' => $this->price_per_share,
                'total_value' => $this->total_value
            ]);

            $issuance = DB::table('issued_shares')->insertGetId([
               
                'reference_number' => $referenceNumber,
                'share_id' => $this->product, // Using product as share_id
                'member' => $this->memberDetails['full_name'] ?? null,
                'product' => $this->product,
                'account_number' => $this->share_account,
                'price' => $this->price_per_share,
                'branch' => auth()->user()->branch ?? null,
                'client_number' => $this->client_number,
                'number_of_shares' => $this->number_of_shares,
                'nominal_price' => $this->price_per_share,
                'total_value' => $this->total_value,
                'linked_savings_account' => $this->linked_savings_account,
                'linked_share_account' => $this->share_account,
                'status' => 'PENDING',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info('Share issuance record created successfully', ['issuance_id' => $issuance]);

            // Create approval request
            Log::info('Creating approval request', [
                'issuance_id' => $issuance,
                'shares' => $this->number_of_shares
            ]);



            $newAccountData = [
                'type' => 'share_issuance',
                'reference_number'=> '1000',
                'member_id'=> $this->client_number,
                'member_name'=> $this->memberDetails['full_name'],
                'product_id'=> $this->product,
                'product_name'=> DB::table('sub_products')->where('id', $this->product)->value('product_name'),
                'number_of_shares'=> $this->number_of_shares,
                'nominal_price'=> $this->price_per_share,
                'total_amount'=> $this->total_value,
                'linked_savings_account'=> $this->linked_savings_account,
                'share_account'=> $this->share_account,
                'status'=> 'COMPLETED',
                'created_by'=> auth()->id()
            ];
            $editPackage = json_encode($newAccountData);
            approvals::create([
                'process_name' => 'share_issuance',
                'process_description' => Auth::user()->name .  ' has issued ' .$this->number_of_shares . ' shares to ' .$this->memberDetails['full_name'],
                'approval_process_description' => 'Share issuance approval required', 
                'process_code' => 'SHARE_ISS',
                'process_id' => $issuance,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'approver_id' => null,
                'approval_status' => 'PENDING',
                'edit_package' => $editPackage
            ]);


            Log::info('Approval request created successfully');

            DB::commit();
            Log::info('Database transaction committed successfully');

            session()->flash('success', 'Share issuance request submitted successfully.');
            $this->closeShowIssueNewShares();
            $this->reset(['product', 'client_number', 'number_of_shares', 'linked_savings_account', 'share_account']);

            Log::info('Share issuance process completed successfully', [
                'issuance_id' => $issuance,
                'client_number' => $this->client_number
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in share issuance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'client_number' => $this->client_number ?? null,
                'product_id' => $this->product ?? null,
                'number_of_shares' => $this->number_of_shares ?? null,
                'user_id' => auth()->id()
            ]);
            
            session()->flash('error', 'An error occurred while processing your request.');
        }
    }


    public function issueShareAfterApproval($editPackage){
        $editPackage = json_decode($editPackage, true);
        $this->member = $editPackage['member_id'];
        $this->product = $editPackage['product_id'];
        $this->number_of_shares = $editPackage['number_of_shares'];
        $this->linked_savings_account = $editPackage['linked_savings_account'];
        $this->share_account = $editPackage['share_account'];
        $this->issueShares();

        //post transaction to share register
    }

    public function sendApproval($id,$msg,$code){
        approvals::create([
            'institution' => '',
            'process_name' => 'createBranch',
            'process_description' => $msg,
            'approval_process_description' => 'has approved a transaction',
            'process_code' => $code,
            'process_id' => $id,
            'process_status' => 'Pending',
            'user_id'  => auth()->user()->id,
            'team_id'  => ""
        ]);
    }

    public function resetData()
    {
        $this->member = '';
        $this->product = '';
        $this->number_of_shares = '';
        $this->linked_savings_account = '';
        $this->account_number = '';
    }

    public function menuItemClicked($tabId){
        $this->tab_id = $tabId;
        if($tabId == '1'){
            $this->title = 'Shares list';
        }
        if($tabId == '2'){
            $this->title = 'Enter new SharesAccount details';
        }
    }

    public function createNewSharesAccount()
    {
        if (!$this->authorize('create', 'You do not have permission to create share accounts')) {
            return;
        }
        $this->showCreateNewSharesAccount = true;
    }    


    public function blockSharesAccount(){
       
        try {
            // Get the current share account details
            $issuedShares = DB::table('issued_shares')->where('id', $this->SharesAccountSelected)->first();
            $accountRegister = DB::table('share_registers')->where('product_id', $issuedShares->share_id)
                                ->where('member_number', $issuedShares->client_number)
                                ->first();

            if (!$accountRegister) {
                Session::flash('error', 'Share account not found');
                return;
            }

            // Check if account is already blocked
            if ($accountRegister->status === 'FROZEN') {
                Session::flash('error', 'Share account is already blocked');
                return;
            }

            // Create approval request
            $approval = approvals::create([
                'institution_id' => auth()->user()->institution_id,
                'process_name' => 'blockSharesAccount',
                'process_description' => 'Block Shares Account - ' . $accountRegister->member_name . ' (Account: ' . $accountRegister->share_account_number . ')',
                'approval_process_description' => 'Block shares account for member: ' . $accountRegister->member_name . ' (Account: ' . $accountRegister->share_account_number . ')',
                'process_code' => 'BLOCK_SHARE_ACC',
                'process_id' => $accountRegister->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'approver_id' => null,
                'approval_status' => 'PENDING',               
            ]);

            Session::flash('success', 'Share account blocking request submitted for approval');
            $this->closeDeleteSharesAccount();
            
        } catch (\Exception $e) {
            Log::error('Error blocking share account: ' . $e->getMessage());
            Session::flash('error', 'Error submitting block request: ' . $e->getMessage());
        }
    }

    public function closeDeleteSharesAccount(){
        $this->showDeleteSharesAccount = false;
        $this->SharesAccountSelected = null;
        $this->activationMode = false;
        $this->resetData();
    }

    public function blockSharesAccountModal($id)
    {       
        $this->showDeleteSharesAccount = true;
        $this->SharesAccountSelected = $id;
        $accountRegister = $this->getAccount($id);
        if($accountRegister->status === 'FROZEN'){
            $this->activationMode = true;
        }else{
            $this->activationMode = false;
        }
    }

    private function getAccount($id){
        $issuedShares = DB::table('issued_shares')->where('id', $id)->first();
        $accountRegister = DB::table('share_registers')->where('product_id', $issuedShares->share_id)
                                ->where('member_number', $issuedShares->client_number)
                                ->first();
        return $accountRegister;
    }

    public function activateSharesAccount(){
        try {
            // Get the current share account details
            $accountRegister = $this->getAccount($this->SharesAccountSelected);
            
            if (!$accountRegister) {
                Session::flash('error', 'Share account not found');
                return;
            }

            // Check if account is already active
            if ($accountRegister->status === 'ACTIVE') {
                Session::flash('error', 'Share account is already active');
                return;
            }
            

            // Create approval request
            $approval = approvals::create([
                'institution_id' => auth()->user()->institution_id,
                'process_name' => 'activateSharesAccount',
                'process_description' => 'Activate Shares Account - ' . $accountRegister->member_name . ' (Account: ' . $accountRegister->share_account_number . ')',
                'approval_process_description' => 'Activate shares account for member: ' . $accountRegister->member_name . ' (Account: ' . $accountRegister->share_account_number . ')',
                'process_code' => 'ACTIVATE_SHARE_ACC',
                'process_id' => $accountRegister->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'approver_id' => null,
                'approval_status' => 'PENDING',                
            ]);

            Session::flash('success', 'Share account activation request submitted for approval');
            $this->closeDeleteSharesAccount();
            
        } catch (\Exception $e) {
            Log::error('Error activating share account: ' . $e->getMessage());
            Session::flash('error', 'Error submitting activation request: ' . $e->getMessage());
        }
    }

    

    public function editSharesAccountModal($id)
    {
        if (!$this->authorize('edit', 'You do not have permission to edit share accounts')) {
            return;
        }
        $this->showEditSharesAccount = true;
        $this->pendingSharesAccount = $id;
        $this->SharesAccount = $id;
        $this->pendingSharesAccountname = SharesModel::where('id',$id)->value('name');
        $this->updatedSharesAccount();
    }



    public function viewAccount($id)
    {
        // Get the share account details
        $issuedShares = DB::table('issued_shares')->where('id', $id)->first();
        $account = DB::table('share_registers')
            ->join('clients', 'share_registers.member_number', '=', 'clients.client_number')
            ->join('sub_products', 'share_registers.product_id', '=', 'sub_products.id')
            ->select(
                'share_registers.*',
                'clients.first_name',
                'clients.last_name',
                'clients.email',
                'clients.phone_number',
                'clients.status as member_status',
                'sub_products.product_name'
            )
            ->where('share_registers.product_id', $issuedShares->share_id)
            ->first();
        
        if ($account) {
            $this->selectedAccountDetails = $account;
            $this->showViewAccountModal = true;
        } else {
            Session::flash('error', 'Account not found');
        }
    }

    public function closeViewAccountModal()
    {
        $this->showViewAccountModal = false;
        $this->selectedAccountDetails = null;
    }

    public function getSelectedAccountDetailsProperty()
    {
        return $this->selectedAccountDetails;
    }

    public function closeShareAccountModal(){
        $this->showCreateNewSharesAccount = false;
        $this->showDeleteSharesAccount = false;
        $this->showEditSharesAccount = false;
        $this->activationMode = false; // Reset activation mode
        $this->resetData();
    }

    public function confirmPassword(): void
    {
        if (Hash::check($this->password, auth()->user()->password)) {
            $this->delete();
        } else {
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
        if (!$this->authorize('delete', 'You do not have permission to delete share accounts')) {
            return;
        }
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
                    'institution' => '',
                    'process_name' => $action,
                    'process_description' => $this->permission.' user - '.$user->name,
                    'approval_process_description' => '',
                    'process_code' => '29',
                    'process_id' => $this->userSelected,
                    'process_status' => $this->permission,
                    'approval_status' => 'PENDING',
                    'user_id'  => Auth::user()->id,
                    'team_id'  => '',
                    'edit_package'=> null
                ]
            );
            Session::flash('message', 'Awaiting approval');
            Session::flash('alert-class', 'alert-success');
            $this->closeShareAccountModal();
            $this->render();
        } else {
            Session::flash('message', 'Node error');
            Session::flash('alert-class', 'alert-warning');
        }
    }

    public function boot()
    {
        try {
            // Monthly share issuance statistics
            $this->monthlyShareIssuance = DB::table('issued_shares')
                ->select(
                    DB::raw('EXTRACT(MONTH FROM created_at)::integer as month'),
                    DB::raw('SUM(number_of_shares) as total_shares'),
                    DB::raw('SUM(total_value) as total_value'),
                    DB::raw('AVG(nominal_price) as average_price'),
                    DB::raw('COUNT(DISTINCT client_number) as total_members')
                )
                ->whereYear('created_at', now()->year)
                ->where('status', 'approved')
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get()
                ->map(function ($item) {
                    $item->month_name = Carbon::create()->month($item->month)->format('F');
                    return $item;
                });

            // Get total shares and capital
            $sharesSummary = DB::table('share_registers')
                ->selectRaw('
                    SUM(total_shares_issued) as total_authorized,
                    SUM(current_share_balance) as total_issued,
                    SUM(total_share_value) as total_capital,
                    AVG(current_price) as average_price,
                    COUNT(DISTINCT member_id) as total_members
                ')
                ->where('status', 'active')
                ->first();

            // Get pending share issuances
            $this->pendingShareIssuances = DB::table('issued_shares')
                ->where('status', 'pending')
                ->count();

            // Get recent share transactions
            $this->recentShareTransactions = DB::table('issued_shares')
                ->join('users', 'issued_shares.created_by', '=', 'users.id')
                ->select(
                    'issued_shares.*',
                    'users.name as posted_by_name'
                )
                ->latest('issued_shares.created_at')
                ->take(5)
                ->get();

            // Get share type distribution
            $this->shareTypeDistribution = DB::table('share_registers')
                ->join('sub_products', 'share_registers.product_id', '=', 'sub_products.id')
                ->select(
                    'sub_products.product_type',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(share_registers.current_share_balance) as total_shares'),
                    DB::raw('SUM(share_registers.total_share_value) as total_value'),
                    DB::raw('AVG(share_registers.current_price) as average_price')
                )
                ->where('share_registers.status', 'active')
                ->groupBy('sub_products.product_type')
                ->get();

            // Update the summary properties
            $this->totalAuthorizedShares = $sharesSummary->total_authorized ?? 0;
            $this->totalIssuedShares = $sharesSummary->total_issued ?? 0;
            $this->totalShareCapital = $sharesSummary->total_capital ?? 0;
            $this->averageSharePrice = $sharesSummary->average_price ?? 0;
            $this->totalMembers = $sharesSummary->total_members ?? 0;

        } catch (\Exception $e) {
            Log::error('Error loading share summary', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function updatedMember($value)
    {
        //dd($value);
        if ($value) {
            // Get member details
            $this->memberDetails = ClientsModel::where('client_number', $value)->first();
            
            // Get member's full name
            $this->memberName = ClientsModel::where('client_number', $value)
                ->selectRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name")
                ->value('full_name');
            
            // Get savings accounts (product_number = 2000)
            $this->savingsAccounts = AccountsModel::where('client_number', $value)
                ->where('product_number', 2000)
                ->get();
            
            // Get share accounts (product_number = 1000)
            $this->shareAccounts = AccountsModel::where('client_number', $value)
                ->where('product_number', 1000)
                ->get();
        } else {
            $this->memberDetails = null;
            $this->memberName = null;
            $this->savingsAccounts = [];
            $this->shareAccounts = [];
        }
    }

    public function processApprovedShareIssuance($approval)
    {
        try {
            Log::info('Processing approved share issuance', ['approval_id' => $approval->id]);
            
            // Decode the edit_package data
            $editPackage = $approval->edit_package;

            if (!isset($editPackage['product_id'])) {
                throw new \Exception('Invalid edit package format');
            }

            // Get share product details
            $shareProduct = DB::table('sub_products')
                ->where('id', $editPackage['product_id'])
                ->first();

            if (!$shareProduct) {
                throw new \Exception('Share product not found');
            }

            // Get member details
            $member = DB::table('clients')
                ->where('client_number', $editPackage['member_id'])
                ->first();

            if (!$member) {
                throw new \Exception('Member not found');
            }

            // Start transaction
            DB::beginTransaction();

            try {
                // Create or update share register
                $shareRegister = DB::table('share_registers')
                    ->where('member_id', $member->client_number)
                    ->where('product_id', $shareProduct->id)
                    ->first();

                if (!$shareRegister) {
                    // Create new share register
                    $shareRegisterId = DB::table('share_registers')->insertGetId([
                        'branch_id' => $member->branch_id,
                        'member_id' => $member->client_number,
                        'member_number' => $member->client_number,
                        'member_name' => trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name),
                        'product_id' => $shareProduct->id,
                        'product_name' => $shareProduct->product_name,
                        'product_type' => $shareProduct->product_type,
                        'share_account_number' => $editPackage['share_account'],
                        'nominal_price' => $shareProduct->nominal_price,
                        'current_price' => $shareProduct->nominal_price,
                        'total_shares_issued' => $editPackage['number_of_shares'],
                        'current_share_balance' => $editPackage['number_of_shares'],
                        'total_share_value' => $editPackage['number_of_shares'] * $shareProduct->nominal_price,
                        'linked_savings_account' => $editPackage['linked_savings_account'],
                        'status' => 'ACTIVE',
                        'opening_date' => now(),
                        'last_activity_date' => now(),
                        'last_transaction_type' => 'ISSUE',
                        'last_transaction_reference' => $approval->reference_number,
                        'last_transaction_date' => now(),
                        'created_by' => $approval->created_by,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    // Update existing share register
                    DB::table('share_registers')
                        ->where('id', $shareRegister->id)
                        ->update([
                            'total_shares_issued' => DB::raw('total_shares_issued + ' . $editPackage['number_of_shares']),
                            'current_share_balance' => DB::raw('current_share_balance + ' . $editPackage['number_of_shares']),
                            'total_share_value' => DB::raw('total_share_value + (' . $editPackage['number_of_shares'] . ' * nominal_price)'),
                            'last_activity_date' => now(),
                            'last_transaction_type' => 'ISSUE',
                            'last_transaction_reference' => $approval->reference_number,
                            'last_transaction_date' => now(),
                            'updated_by' => $approval->created_by,
                            'updated_at' => now()
                        ]);
                    $shareRegisterId = $shareRegister->id;
                }

                // Record share issuance
                DB::table('issued_shares')
                    ->where('id', $approval->process_id)
                    ->update([
                        'status' => 'COMPLETED',
                        'updated_at' => now()
                    ]);

                // Update available shares in sub_products
                DB::table('sub_products')
                    ->where('id', $shareProduct->id)
                    ->update([
                        //'shares_allocated' => DB::raw('shares_allocated + ' . $editPackage['number_of_shares']),
                        'issued_shares' => DB::raw('CAST(COALESCE(issued_shares, \'0\') AS INTEGER) + ' . intval($editPackage['number_of_shares'])),
                        'available_shares' => DB::raw('CAST(COALESCE(available_shares, \'0\') AS INTEGER) - ' . intval($editPackage['number_of_shares'])),
                        'updated_at' => now()
                    ]);

                // Process payment transaction
                $totalAmount = $editPackage['number_of_shares'] * $shareProduct->nominal_price;

                // Determine source and destination accounts based on payment source
                if (!empty($editPackage['source']) && $editPackage['source'] === 'NBC_BILLS_PAYMENT') {
                    // NBC Bills Payment: money is in OPERATING ACCOUNT, transfer to member's share account
                    // Get the bill to retrieve the debit account (operating account)
                    $bill = DB::table('bills')->where('id', $editPackage['bill_id'])->first();

                    if (!$bill) {
                        throw new \Exception('Bill not found for NBC Bills Payment share issuance');
                    }

                    $sourceAccount = $bill->debit_account_number; // OPERATING ACCOUNT (where money is)
                    $destinationAccount = $editPackage['share_account']; // Member's share account (where we want it)

                    Log::info('NBC Bills Payment share issuance transaction', [
                        'source_account' => $sourceAccount,
                        'destination_account' => $destinationAccount,
                        'amount' => $totalAmount,
                        'bill_id' => $editPackage['bill_id']
                    ]);
                } else {
                    // Manual share issuance: deduct from member's savings account
                    $sourceAccount = $editPackage['linked_savings_account']; // Savings account (source)
                    $destinationAccount = $editPackage['share_account']; // Member's share account (destination)

                    Log::info('Manual share issuance transaction', [
                        'source_account' => $sourceAccount,
                        'destination_account' => $destinationAccount,
                        'amount' => $totalAmount
                    ]);
                }

                // Post the transaction using TransactionPostingService
                $transactionService = new TransactionPostingService();
                $transactionData = [
                    'first_account' => $sourceAccount,
                    'second_account' => $destinationAccount,
                    'source_account' => $sourceAccount,
                    'destination_account' => $destinationAccount,
                    'amount' => $totalAmount,
                    'narration' => 'Share issuance - ' . $editPackage['number_of_shares'] . ' shares for ' . $member->first_name . ' ' . $member->last_name,
                    'action' => 'share_issuance'
                ];

                $result = $transactionService->postTransaction($transactionData);

                if ($result['status'] !== 'success') {
                    throw new \Exception('Failed to post transaction: ' . ($result['message'] ?? 'Unknown error'));
                }

                Log::info('Share issuance transaction posted successfully', [
                    'reference_number' => $result['reference_number'] ?? 'N/A',
                    'source_account' => $sourceAccount,
                    'destination_account' => $destinationAccount,
                    'amount' => $totalAmount
                ]);

                // Generate receipt for share purchase
                $this->generateSharePurchaseReceipt(
                    $member,
                    $editPackage,
                    $shareProduct,
                    $totalAmount,
                    $result['reference_number'] ?? $approval->reference_number
                );

                DB::commit();
                Log::info('Share issuance processed successfully', [
                    'approval_id' => $approval->id,
                    'member_id' => $member->client_number,
                    'shares' => $editPackage['number_of_shares']
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error processing share issuance', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function generateShareAccountNumber($institutionId, $memberNumber)
    {
        // Get institution prefix
        $institution = DB::table('institutions')
            ->where('id', $institutionId)
            ->first();

        if (!$institution) {
            throw new \Exception('Institution not found');
        }

        // Generate unique share account number
        $prefix = $institution->institution_code ?? 'SH';
        $timestamp = time();
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . 'S' . $memberNumber . $timestamp . $random;
    }

    protected function loadStatistics()
    {
        try {
            // Share Products Statistics
            $this->totalShareProducts = DB::table('sub_products')
                ->where('product_type', '1000')
                ->count();

            $this->activeShareProducts = DB::table('sub_products')
                ->where('product_type', '1000')
                ->where('status', 'ACTIVE')
                ->count();

            // Total Share Value
            $this->totalShareValue = DB::table('share_registers')
                ->sum('total_share_value');

            // Share Issuance Activity
            $this->totalSharesIssued = DB::table('issued_shares')
                ->where('status', 'completed')
                ->sum('number_of_shares');

            $this->sharesIssuedToday = DB::table('issued_shares')
                ->where('status', 'completed')
                ->whereDate('created_at', today())
                ->sum('number_of_shares');

            // Pending Approvals
            $this->pendingShareIssuances = DB::table('issued_shares')
                ->where('status', 'pending')
                ->count();

            // Monthly share issuance statistics
            $this->monthlyShareIssuance = DB::table('issued_shares')
                ->select(
                    DB::raw('EXTRACT(MONTH FROM created_at)::integer as month'),
                    DB::raw('SUM(number_of_shares) as total_shares'),
                    DB::raw('SUM(total_value) as total_value'),
                    DB::raw('AVG(nominal_price) as average_price'),
                    DB::raw('COUNT(DISTINCT client_number) as total_members')
                )
                ->whereYear('created_at', now()->year)
                ->where('status', 'approved')
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get()
                ->map(function ($item) {
                    $item->month_name = Carbon::create()->month($item->month)->format('F');
                    return $item;
                });

            // Total Members (from share_registers table)
            $this->totalMembers = DB::table('share_registers')
                ->distinct('member_id')
                ->count('member_id');

        } catch (\Exception $e) {
            Log::error('Error loading share statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function render()
    {
        $this->loadStatistics();

        $this->getShareSummary();

        $this->loadShareProducts();

        $query = IssuedShares::query()
            ->leftJoin('clients', 'issued_shares.client_number', '=', 'clients.client_number')
            ->select('issued_shares.*',
                'clients.first_name',
                'clients.middle_name',
                'clients.last_name',
                'clients.email',
                'clients.phone_number',
                'clients.mobile_phone_number'
            )
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('issued_shares.account_number', 'like', '%' . $this->search . '%')
                      ->orWhere('clients.first_name', 'ilike', '%' . $this->search . '%')
                      ->orWhere('clients.middle_name', 'ilike', '%' . $this->search . '%')
                      ->orWhere('clients.last_name', 'ilike', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedShareProduct, function($query) {
                $query->where('issued_shares.product', $this->selectedShareProduct);
            });

        $shareAccounts = $query->paginate(10);

        return view('livewire.shares.shares', array_merge(
            $this->permissions,
            [
                'shareAccounts' => $shareAccounts,
                'permissions' => $this->permissions
            ]
        ));
    }

    protected function loadAvailableProducts()
    {
        $this->availableProducts = sub_products::where('product_type', '1000')
            ->get();
    }

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

    public function checkClientNumberLength()
    {

        // dd($this->client_number);
        // Reset member details if client number is cleared
        if (empty($this->client_number)) {
            $this->memberDetails = null;
            return;
        }

        // Only proceed if exactly 5 digits
        if (strlen($this->client_number) === 5) {
            $this->searchMember();
        } else {
            // Clear member details if not exactly 5 digits
            $this->memberDetails = null;
        }
    }



    public function validateMemberNumber2()
    {
        
        $this->resetErrorBag('member');
        
        //Remove any non-numeric characters
        //$this->member = preg_replace('/[^0-9]/', '', $this->member);
        
        if (empty($this->client_number)) {
            $this->memberDetails = null;
            $this->memberName = null;
            return;
        }
        
        if (!empty($this->client_number) && strlen($this->client_number) !== 5) {
            $this->addError('member', 'Member number must be exactly 5 digits');
            $this->memberDetails = null;
            $this->memberName = null;
            return;
        }

      
        
        try {
            $member = ClientsModel::where('client_number', $this->client_number)
                //->where('status', 'ACTIVE')
                ->first();

                //dd($member);
            
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

            $this->loadReceivingAccounts();
            
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



    protected function searchMember()
    {
        try {
            $member = DB::table('clients')
                ->where('client_number', $this->client_number)
                //->where('status', 'ACTIVE')  // Only search for active clients
                ->first();

            if ($member) {
                // Get current shares for this member
                $currentShares = DB::table('share_registers')
                    ->where('member_id', $member->id)
                    ->sum('current_share_balance');

                // Format full name
                $fullName = trim($member->first_name . ' ' . 
                               ($member->middle_name ? $member->middle_name . ' ' : '') . 
                               $member->last_name);

                $this->memberDetails = [
                    'id' => $member->id,
                    'full_name' => $fullName,
                    'status' => $member->status,
                    'phone_number' => $member->phone_number ?? $member->mobile_phone_number,
                    'email' => $member->email,
                    'current_shares' => $currentShares,
                    'join_date' => $member->registration_date ? Carbon::parse($member->registration_date)->format('M d, Y') : 'N/A',
                    'member_number' => $member->member_number,
                    'client_number' => $member->client_number,
                    'address' => $member->address,
                    'region' => $member->region,
                    'district' => $member->district,
                    'ward' => $member->ward,
                    'occupation' => $member->occupation,
                    'income_source' => $member->income_source,
                    'member_category' => $member->member_category
                ];

                // Log successful member search
                Log::info('Member found', [
                    'client_number' => $this->client_number,
                    'member_id' => $member->id,
                    'member_name' => $fullName
                ]);
            } else {
                $this->memberDetails = null;
                $this->addError('client_number', 'No active member found with this client number.');
            }
        } catch (\Exception $e) {
            Log::error('Error searching member', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->memberDetails = null;
            $this->addError('client_number', 'Error searching for member. Please try again.');
        }
    }

    protected function getCurrentPrice($shareType)
    {
        if (is_array($shareType)) {
            return $shareType['current_price'] ?? 0;
        }
        return $shareType->current_price ?? 0;
    }

    public function updatedSelectedShareType($value)
    {
        if (empty($value)) {
            $this->selectedShareType = null;
            return;
        }

        try {
            // Ensure we're working with an object
            if (is_array($value)) {
                $this->selectedShareType = (object) $value;
            } else {
                $this->selectedShareType = $value;
            }

            // Validate the share type
            if (!isset($this->selectedShareType->current_price)) {
                $this->addError('selectedShareType', 'Invalid share type selected');
                $this->selectedShareType = null;
                return;
            }

            // Log successful selection
            Log::info('Share type selected', [
                'share_type_id' => $this->selectedShareType->id ?? null,
                'current_price' => $this->getCurrentPrice($this->selectedShareType)
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating selected share type', [
                'error' => $e->getMessage(),
                'value' => $value
            ]);
            $this->addError('selectedShareType', 'Error processing share type');
            $this->selectedShareType = null;
        }
    }

    public function updatedWithdrawalShares($value)
    {
        Log::info('Withdrawal shares updated', [
            'old_value' => $this->withdrawalShares,
            'new_value' => $value,
            'selected_share_type' => $this->selectedShareType
        ]);

        if ($value && $this->selectedShareType) {
            $selectedProduct = collect($this->memberShareTypes)->firstWhere('id', $this->selectedShareType);
            if ($selectedProduct) {
                $remainingShares = $selectedProduct['current_balance'] - $value;
                $shareProduct = \App\Models\sub_products::find($this->selectedShareType);
                
                Log::info('Calculated remaining shares', [
                    'current_balance' => $selectedProduct['current_balance'],
                    'withdrawal_amount' => $value,
                    'remaining_shares' => $remainingShares,
                    'minimum_required' => $shareProduct->minimum_required_shares
                ]);

                if ($remainingShares < $shareProduct->minimum_required_shares) {
                    Log::warning('Withdrawal would leave less than minimum required shares', [
                        'remaining_shares' => $remainingShares,
                        'minimum_required' => $shareProduct->minimum_required_shares
                    ]);
                }
            }
        }
    }

    public function updatedSelectedReceivingAccount($value)
    {
        Log::info('Selected receiving account updated', [
            'old_value' => $this->selectedReceivingAccount,
            'new_value' => $value
        ]);

        if ($value) {
            $account = collect($this->receivingAccounts)->firstWhere('id', $value);
            if ($account) {
                Log::info('Receiving account details', [
                    'account_id' => $account['id'],
                    'account_number' => $account['account_number'],
                    'account_type' => $account['type'],
                    'current_balance' => $account['balance']
                ]);
            }
        }
    }

    public function updatedWithdrawalReason($value)
    {
        Log::info('Withdrawal reason updated', [
            'old_value' => $this->withdrawalReason,
            'new_value' => $value,
            'length' => strlen($value)
        ]);
    }

    public function showShareWithdrawalModal()
    {
        Log::info('Opening share withdrawal modal');
        $this->showShareWithdrawal = true;
        $this->sidebar_view = 'share_withdrawal';
        $this->reset(['client_number', 'memberDetails', 'current_member_shares', 'selectedShareType', 'memberShareTypes', 'withdrawalShares', 'withdrawalReason', 'recipientMember']);
        Log::info('Share withdrawal form reset');
    }

    public function closeShareWithdrawalModal()
    {
        Log::info('Closing share withdrawal modal');
        $this->showShareWithdrawal = false;
        $this->reset(['client_number', 'memberDetails', 'current_member_shares', 'selectedShareType', 'memberShareTypes', 'withdrawalShares', 'withdrawalReason', 'recipientMember']);
        Log::info('Share withdrawal form reset');
    }

    public function showShareTransferModal()
    {
        $this->resetShareTransferForm();
        $this->showShareTransferModalx = true;
        $this->sidebar_view = 'share_transfer';
    }

    public function closeShareTransferModal()
    {
        $this->resetShareTransferForm();
        $this->showShareTransferModalx = false;
    }

    protected function resetShareTransferForm()
    {
        $this->sender_client_number = '';
        $this->receiver_client_number = '';
        $this->senderMemberDetails = null;
        $this->receiverMemberDetails = null;
        $this->senderShareTypes = [];
        $this->receiverShareTypes = [];
        $this->selectedReceiverShareType = null;
        $this->sender_share_type = '';
        $this->transfer_shares = '';
        $this->transfer_reason = '';
        $this->recentTransfers = [];
        $this->selectedShareType = null;
        $this->transferValidationErrors = [];
    }

    public function updatedSenderClientNumber()
    {
        $this->validateSenderClientNumber();
    }

    public function updatedReceiverClientNumber()
    {
        $this->validateReceiverClientNumber();
    }

    public function updatedSenderShareType()
    {
        $this->validateSenderShareType();
        //$this->loadRecentTransfers();
    }

    protected function validateSenderClientNumber()
    {
        try {
            // Remove any non-numeric characters
            //$this->sender_client_number = preg_replace('/[^0-9]/', '', $this->sender_client_number);
            
            if (empty($this->sender_client_number)) {
                $this->senderMemberDetails = null;
                $this->senderShareTypes = [];
                return;
            }

            // Only proceed if exactly 5 digits
            if (strlen($this->sender_client_number) !== 5) {
                $this->senderMemberDetails = null;
                $this->senderShareTypes = [];
                return;
            }

            $member = ClientsModel::where('client_number', $this->sender_client_number)
                //->where('status', 'ACTIVE')
                ->first();

            if (!$member) {
                $this->addError('sender_client_number', 'Member not found or not active');
                $this->senderMemberDetails = null;
                $this->senderShareTypes = [];
                return;
            }

            $this->senderMemberDetails = $member;
            $this->loadSenderShareTypes();

            Log::info('Sender member details loaded', [
                'client_number' => $this->sender_client_number,
                'member_id' => $member->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading sender details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('sender_client_number', 'Error loading member details');
        }
    }

    protected function validateReceiverShareType()
    {
        try {
            if (empty($this->receiver_share_type)) {
                $this->selectedReceiverShareType = null;
                return;
            }



            if (empty($this->receiver_client_number)) {
                $this->receiverMemberDetails = null; 
                $this->receiverShareTypes = [];
                return;
            }

            // Only proceed if exactly 5 digits
            if (strlen($this->receiver_client_number) !== 5) {
                $this->receiverMemberDetails = null;
                $this->receiverShareTypes = [];
                return;
            }

            $member = ClientsModel::where('client_number', $this->receiver_client_number) 
                //->where('status', 'ACTIVE')
                ->first();

            if (!$member) {
                $this->addError('receiver_client_number', 'Member not found or not active');
                $this->receiverMemberDetails = null;
                $this->receiverShareTypes = [];
                return;
            }

            $this->receiverMemberDetails = $member;
            $this->loadReceiverShareTypes();

            Log::info('Receiver member details loaded', [
                'client_number' => $this->receiver_client_number,
                'member_id' => $member->id
            ]);


        }
        catch (\Exception $e) {
            Log::error('Error validating receiver share type', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }


    protected function validateReceiverClientNumber()
    {
        try {
            if (empty($this->receiver_client_number)) {
                $this->receiverMemberDetails = null;
                $this->receiverShareTypes = [];
                return;
            }

            if ($this->receiver_client_number === $this->sender_client_number) {
                $this->addError('receiver_client_number', 'Cannot transfer shares to the same member');
                $this->receiverMemberDetails = null;
                $this->receiverShareTypes = [];
                return;
            }

            $member = ClientsModel::where('client_number', $this->receiver_client_number)
                //->where('status', 'ACTIVE')
                ->first();

            if (!$member) {
                $this->addError('receiver_client_number', 'Member not found or not active');
                $this->receiverMemberDetails = null;
                $this->receiverShareTypes = [];
                return;
            }

            $this->receiverMemberDetails = $member;
            $this->loadReceiverShareTypes();

            Log::info('Receiver member details loaded', [
                'client_number' => $this->receiver_client_number,
                'member_id' => $member->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading receiver details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('receiver_client_number', 'Error loading member details');
        }
    }

    protected function validateSenderShareType()
    {
        try {
            if (empty($this->sender_share_type)) {
                $this->selectedShareType = null;
                return;
            }

            $shareType = collect($this->senderShareTypes)
                ->firstWhere('id', $this->sender_share_type);

            if (!$shareType) {
                $this->addError('sender_share_type', 'Invalid share type selected');
                $this->selectedShareType = null;
                return;
            }

            $this->selectedShareType = $shareType;
            //$this->loadRecentTransfers();

            Log::info('Share type selected', [
                'share_type_id' => $this->sender_share_type,
                'current_balance' => $shareType['balance']
            ]);
        } catch (\Exception $e) {
            Log::error('Error validating share type', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('sender_share_type', 'Error validating share type');
        }
    }




    protected function loadSenderShareTypes()
    {
        try {
            if (!$this->senderMemberDetails) {
                $this->senderShareTypes = [];
                return;
            }

            // Use member_number instead of member_id for consistency
            $shareRegisters = ShareRegister::where('member_number', $this->senderMemberDetails->client_number)
                //->where('status', 'active')
                ->get();

            $this->senderShareTypes = [];

            foreach ($shareRegisters as $register) {
                $this->senderShareTypes[] = [
                    'id' => $register->id, // Use the register ID, not product_id
                    'name' => $register->product_name,
                    'type' => $register->product_type,
                    'account_number' => $register->share_account_number,
                    'balance' => $register->current_share_balance,
                    'value' => $register->total_share_value,
                    'nominal_price' => $register->nominal_price,
                    'current_price' => $register->current_price,
                    'total_issued' => $register->total_shares_issued,
                    'total_redeemed' => $register->total_shares_redeemed,
                    'total_transferred_in' => $register->total_shares_transferred_in,
                    'total_transferred_out' => $register->total_shares_transferred_out,
                    'is_restricted' => $register->is_restricted,
                    'requires_approval' => $register->requires_approval,
                    'restriction_notes' => $register->restriction_notes,
                    'last_transaction_type' => $register->last_transaction_type,
                    'last_transaction_date' => $register->last_transaction_date,
                    'opening_date' => $register->opening_date,
                    'last_activity_date' => $register->last_activity_date
                ];
            }

            Log::info('Sender share types loaded', [
                'client_number' => $this->sender_client_number,
                'share_types_count' => count($this->senderShareTypes)
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading sender share types', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('sender_client_number', 'Error loading share types');
        }
    }


    protected function loadReceiverShareTypes()
    {
        try {
            if (!$this->receiverMemberDetails) {
                $this->receiverShareTypes = [];
                return;
            }

            // Use member_number instead of member_id for consistency
            $shareRegisters = ShareRegister::where('member_number', $this->receiverMemberDetails->client_number)
                //->where('status', 'active')
                ->get();

            $this->receiverShareTypes = [];

            foreach ($shareRegisters as $register) {
                $this->receiverShareTypes[] = [
                    'id' => $register->id, // Use the register ID, not product_id
                    'name' => $register->product_name,
                    'type' => $register->product_type,
                    'account_number' => $register->share_account_number,
                    'balance' => $register->current_share_balance,
                    'value' => $register->total_share_value,
                    'nominal_price' => $register->nominal_price,
                    'current_price' => $register->current_price,
                    'total_issued' => $register->total_shares_issued,
                    'total_redeemed' => $register->total_shares_redeemed,
                    'total_transferred_in' => $register->total_shares_transferred_in,
                    'total_transferred_out' => $register->total_shares_transferred_out,
                    'is_restricted' => $register->is_restricted,
                    'requires_approval' => $register->requires_approval,
                    'restriction_notes' => $register->restriction_notes,
                    'last_transaction_type' => $register->last_transaction_type,
                    'last_transaction_date' => $register->last_transaction_date,
                    'opening_date' => $register->opening_date,
                    'last_activity_date' => $register->last_activity_date
                ];
            }

            Log::info('Receiver share types loaded', [
                'client_number' => $this->receiver_client_number,
                'share_types_count' => count($this->receiverShareTypes)
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading receiver share types', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('receiver_client_number', 'Error loading share types');
        }
    }


    protected function loadRecentTransfers()
    {
       
    }

    public function processShareTransfer()
    {
        if (!$this->authorize('transfer', 'You do not have permission to transfer shares')) {
            return;
        }
        
        try {
            Log::info('Starting share transfer process', [
                'sender' => $this->senderMemberDetails->client_number,
                'receiver' => $this->receiverMemberDetails->client_number,
                'shares' => $this->transfer_shares
            ]);

            DB::beginTransaction();

            // Generate validations for these fields
            $this->validate([
                'sender_client_number' => 'required',
                'sender_share_type' => 'required',
                'receiver_client_number' => 'required',
                'receiver_share_type' => 'required',
                'transfer_shares' => 'required',
                'transfer_reason' => 'required',
            ]);

            // Get sender's share register
            $senderShareRegister = DB::table('share_registers')
                ->where('id', $this->sender_share_type)
                ->first();

            if (!$senderShareRegister) {
                Log::error('Sender share register not found', ['share_type_id' => $this->sender_share_type]);
                throw new \Exception('Sender share register not found');
            }

            // Get receiver's share register
            $receiverShareRegister = DB::table('share_registers')
                ->where('id', $this->receiver_share_type)
                ->first();

            if (!$receiverShareRegister) {
                Log::error('Receiver share register not found', ['share_type_id' => $this->receiver_share_type]);
                throw new \Exception('Receiver share register not found');
            }

            // Check if transfer is allowed for this share type
            $shareProduct = DB::table('sub_products')
                ->where('id', $senderShareRegister->product_id)
                ->first();

            if (!$shareProduct || !$shareProduct->allow_share_transfer) {
                Log::error('Share transfer not allowed', [
                    'product_id' => $senderShareRegister->product_id,
                    'allow_share_transfer' => $shareProduct ? $shareProduct->allow_share_transfer : false
                ]);
                throw new \Exception('Share transfers are not allowed for this share type');
            }

            // Check if sender has enough shares
            if ($senderShareRegister->current_share_balance < $this->transfer_shares) {
                Log::error('Insufficient shares', [
                    'current_balance' => $senderShareRegister->current_share_balance,
                    'requested_shares' => $this->transfer_shares
                ]);
                throw new \Exception('Insufficient shares available for transfer');
            }

            // Calculate total value and check if it's a cross-product transfer
            $totalValue = $this->transfer_shares * $senderShareRegister->nominal_price;
            $isCrossProductTransfer = $senderShareRegister->product_id !== $receiverShareRegister->product_id;
            $equivalentShares = $isCrossProductTransfer ? round($totalValue / $receiverShareRegister->nominal_price, 2) : $this->transfer_shares;

            Log::info('Creating share transfer record', [
                'sender' => $this->senderMemberDetails->client_number,
                'receiver' => $this->receiverMemberDetails->client_number,
                'shares' => $this->transfer_shares,
                'total_value' => $totalValue,
                'is_cross_product' => $isCrossProductTransfer,
                'equivalent_shares' => $equivalentShares,
                'sender_product' => $senderShareRegister->product_name,
                'receiver_product' => $receiverShareRegister->product_name
            ]);

            // Create share transfer record
            $transferId = DB::table('share_transfers')->insertGetId([
                'transaction_reference' => 'TRF-' . time(),
                'sender_member_id' => $this->senderMemberDetails->id,
                'sender_client_number' => $this->senderMemberDetails->client_number,
                'sender_member_name' => trim($this->senderMemberDetails->first_name . ' ' . ($this->senderMemberDetails->middle_name ?? '') . ' ' . $this->senderMemberDetails->last_name),
                'sender_share_register_id' => $senderShareRegister->id,
                'sender_share_account_number' => $senderShareRegister->share_account_number,
                'receiver_member_id' => $this->receiverMemberDetails->id,
                'receiver_client_number' => $this->receiverMemberDetails->client_number,
                'receiver_member_name' => trim($this->receiverMemberDetails->first_name . ' ' . ($this->receiverMemberDetails->middle_name ?? '') . ' ' . $this->receiverMemberDetails->last_name),
                'receiver_share_register_id' => $receiverShareRegister->id,
                'receiver_share_account_number' => $receiverShareRegister->share_account_number,
                'share_product_id' => $senderShareRegister->product_id,
                'share_product_name' => $senderShareRegister->product_name,
                'number_of_shares' => $this->transfer_shares,
                'nominal_price' => $senderShareRegister->nominal_price,
                'total_value' => $totalValue,
                'transfer_reason' => $this->transfer_reason,
                'is_cross_product_transfer' => $isCrossProductTransfer,
                'equivalent_shares' => $equivalentShares,
                'status' => 'PENDING',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info('Creating approval request', [
                'transfer_id' => $transferId,
                'sender' => $this->senderMemberDetails->client_number ?? $senderShareRegister->member_number,
                'shares' => $this->transfer_shares
            ]);

            // Create approval request
            approvals::create([
                'process_name' => 'share_transfer',
                'process_description' => "Share transfer request for {$senderShareRegister->product_name}",
                'approval_process_description' => "Member " . ($this->senderMemberDetails->first_name ?? 'Unknown') . " " . ($this->senderMemberDetails->last_name ?? 'Member') . " is requesting to transfer {$this->transfer_shares} shares worth TZS " . number_format($totalValue, 2),
                'process_code' => 'SHARE_TRF',
                'process_id' => $transferId,
                'process_status' => 'PENDING',
                'user_id' => auth()->id(),
                'team_id' => '',
                'edit_package' => null
            ]);

            DB::commit();

            $this->closeShareTransferModal();
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Share transfer request submitted successfully'
            ]);

            Log::info('Share transfer request completed', [
                'transfer_id' => $transferId,
                'shares' => $this->transfer_shares,
                'total_value' => $totalValue,
                'source_member' => $this->senderMemberDetails->client_number ?? $senderShareRegister->member_number,
                'destination_member' => $this->receiverMemberDetails->client_number ?? $receiverShareRegister->member_number,
                'reason' => $this->transfer_reason
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing share transfer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'sender' => $this->senderMemberDetails->client_number ?? null,
                'receiver' => $this->receiverMemberDetails->client_number ?? null,
                'shares' => $this->transfer_shares ?? null
            ]);
            $this->addError('transfer', 'Error processing transfer: ' . $e->getMessage());
        }
    }


    public function processApprovedTransfer($transferId)
    {
        Log::info('Starting approved transfer processing', [
            'transfer_id' => $transferId,
            'user_id' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);

        try {
            DB::transaction(function () use ($transferId) {
                // Fetch transfer details
                $transfer = DB::table('share_transfers')->where('id', $transferId)->first();

                if (!$transfer) {
                    Log::error('Transfer record not found', [
                        'transfer_id' => $transferId,
                        'user_id' => auth()->id(),
                        'timestamp' => now()->toDateTimeString()
                    ]);
                    throw new \Exception('Transfer record not found');
                }

                Log::info('Retrieved transfer details', [
                    'transfer_id' => $transferId,
                    'sender' => $transfer->sender_client_number,
                    'receiver' => $transfer->receiver_client_number,
                    'shares' => $transfer->number_of_shares,
                    'total_value' => $transfer->total_value
                ]);

                // Update sender's share register
                Log::info('Updating sender share register', [
                    'register_id' => $transfer->sender_share_register_id,
                    'shares_to_deduct' => $transfer->number_of_shares,
                    'current_balance' => DB::table('share_registers')
                        ->where('id', $transfer->sender_share_register_id)
                        ->value('current_share_balance')
                ]);

                DB::table('share_registers')
                    ->where('id', $transfer->sender_share_register_id)
                    ->update([
                        'total_shares_transferred_out' => DB::raw('total_shares_transferred_out + ' . $transfer->number_of_shares),
                        'current_share_balance' => DB::raw('current_share_balance - ' . $transfer->number_of_shares),
                        'total_share_value' => DB::raw('(current_share_balance - ' . $transfer->number_of_shares . ') * nominal_price'),
                        'last_transaction_type' => 'TRANSFER_OUT',
                        'last_transaction_reference' => $transferId,
                        'last_transaction_date' => now(),
                        'last_activity_date' => now(),
                        'updated_by' => auth()->id(),
                        'updated_at' => now()
                    ]);

                Log::info('Sender share register updated successfully', [
                    'register_id' => $transfer->sender_share_register_id,
                    'new_balance' => DB::table('share_registers')
                        ->where('id', $transfer->sender_share_register_id)
                        ->value('current_share_balance')
                ]);

                // Get sender and receiver share register details for cross-product transfer calculation
                $senderRegister = DB::table('share_registers')->where('id', $transfer->sender_share_register_id)->first();
                $receiverRegister = DB::table('share_registers')->where('id', $transfer->receiver_share_register_id)->first();
                
                // Calculate equivalent shares for cross-product transfer
                $senderValue = $transfer->number_of_shares * $senderRegister->nominal_price;
                $equivalentShares = round($senderValue / $receiverRegister->nominal_price, 2);
                
                Log::info('Cross-product transfer calculation', [
                    'sender_shares' => $transfer->number_of_shares,
                    'sender_price' => $senderRegister->nominal_price,
                    'sender_value' => $senderValue,
                    'receiver_price' => $receiverRegister->nominal_price,
                    'equivalent_shares' => $equivalentShares,
                    'is_same_product' => $senderRegister->product_id === $receiverRegister->product_id
                ]);

                // Update receiver's share register
                $sharesToAdd = $senderRegister->product_id === $receiverRegister->product_id 
                    ? $transfer->number_of_shares 
                    : $equivalentShares;
                    
                Log::info('Updating receiver share register', [
                    'register_id' => $transfer->receiver_share_register_id,
                    'shares_to_add' => $sharesToAdd,
                    'is_cross_product' => $senderRegister->product_id !== $receiverRegister->product_id,
                    'current_balance' => DB::table('share_registers')
                        ->where('id', $transfer->receiver_share_register_id)
                        ->value('current_share_balance')
                ]);

                DB::table('share_registers')
                    ->where('id', $transfer->receiver_share_register_id)
                    ->update([
                        'total_shares_transferred_in' => DB::raw('total_shares_transferred_in + ' . $sharesToAdd),
                        'current_share_balance' => DB::raw('current_share_balance + ' . $sharesToAdd),
                        'total_share_value' => DB::raw('(current_share_balance + ' . $sharesToAdd . ') * nominal_price'),
                        'last_transaction_type' => 'TRANSFER_IN',
                        'last_transaction_reference' => $transferId,
                        'last_transaction_date' => now(),
                        'last_activity_date' => now(),
                        'updated_by' => auth()->id(),
                        'updated_at' => now()
                    ]);

                Log::info('Receiver share register updated successfully', [
                    'register_id' => $transfer->receiver_share_register_id,
                    'new_balance' => DB::table('share_registers')
                        ->where('id', $transfer->receiver_share_register_id)
                        ->value('current_share_balance')
                ]);

                // Process payment if linked to savings account
                if (!empty($transfer->sender_share_account_number) && !empty($transfer->receiver_share_account_number)) {
                    $totalAmount = $transfer->total_value;
                    
                    Log::info('Processing linked savings account transaction', [
                        'sender_account' => $transfer->sender_share_account_number,
                        'receiver_account' => $transfer->receiver_share_account_number,
                        'amount' => $totalAmount
                    ]);

                    // Post the transaction using TransactionPostingService
                    $transactionService = new TransactionPostingService();
                    $transactionData = [
                        'first_account' => $transfer->sender_share_account_number,
                        'second_account' => $transfer->receiver_share_account_number,
                        'amount' => $totalAmount,
                        'narration' => 'Share transfer - ' . $transfer->number_of_shares . ' shares from ' . $transfer->share_product_name,
                        'action' => 'share_transfer'
                    ];

                    Log::info('Posting share transfer transaction', [
                        'transaction_data' => $transactionData
                    ]);

                    $result = $transactionService->postTransaction($transactionData);
                    
                    if ($result['status'] !== 'success') {
                        Log::error('Transaction posting failed', [
                            'error' => $result['message'] ?? 'Unknown error',
                            'transaction_data' => $transactionData,
                            'transfer_id' => $transferId
                        ]);
                        throw new \Exception('Failed to post transaction: ' . ($result['message'] ?? 'Unknown error'));
                    }

                    Log::info('Transaction posted successfully', [
                        'transaction_reference' => $result['reference'] ?? null,
                        'amount' => $totalAmount,
                        'transfer_id' => $transferId
                    ]);
                } else {
                    Log::info('No linked savings accounts found, skipping transaction posting', [
                        'transfer_id' => $transferId,
                        'sender_account' => $transfer->sender_share_account_number,
                        'receiver_account' => $transfer->receiver_share_account_number
                    ]);
                }

                // Update transfer status to completed
                Log::info('Updating transfer status to completed', [
                    'transfer_id' => $transferId,
                    'previous_status' => $transfer->status
                ]);

                DB::table('share_transfers')
                    ->where('id', $transferId)
                    ->update([
                        'status' => 'COMPLETED',
                        'updated_by' => auth()->id(),
                        'updated_at' => now()
                    ]);

                Log::info('Transfer process completed successfully', [
                    'transfer_id' => $transferId,
                    'sender' => $transfer->sender_client_number,
                    'receiver' => $transfer->receiver_client_number,
                    'shares' => $transfer->number_of_shares,
                    'total_value' => $transfer->total_value,
                    'completed_at' => now()->toDateTimeString()
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Error processing approved transfer', [
                'transfer_id' => $transferId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'timestamp' => now()->toDateTimeString()
            ]);
            throw $e;
        }
    }

    protected function validateTransferRequest()
    {
        $this->validate([
            'sender_client_number' => 'required|exists:members,client_number',
            'receiver_client_number' => 'required|exists:members,client_number|different:sender_client_number',
            'sender_share_type' => 'required',
            'transfer_shares' => 'required|numeric|min:1',
            'transfer_reason' => 'required|min:10'
        ]);

        if (!$this->selectedShareType) {
            throw new \Exception('Invalid share type selected');
        }

        if ($this->transfer_shares > $this->selectedShareType->current_balance) {
            throw new \Exception('Insufficient shares available for transfer');
        }

        if (!$this->senderMemberDetails || !$this->receiverMemberDetails) {
            throw new \Exception('Invalid member details');
        }
    }

    protected function createTransferTransactions($transfer)
    {
        // Create debit transaction for source account
        Transaction::create([
            'account_id' => $transfer->source_account_id,
            'transaction_type' => 'SHARE_TRANSFER_DEBIT',
            'amount' => $transfer->total_value,
            'balance' => ShareAccount::find($transfer->source_account_id)->current_balance,
            'narration' => "Share transfer to {$this->receiverMemberDetails->client_number}",
            'reference' => $transfer->id,
            'status' => 'COMPLETED',
            'created_by' => auth()->id()
        ]);

        // Create credit transaction for destination account
        Transaction::create([
            'account_id' => $transfer->destination_account_id,
            'transaction_type' => 'SHARE_TRANSFER_CREDIT',
            'amount' => $transfer->total_value,
            'balance' => ShareAccount::find($transfer->destination_account_id)->current_balance,
            'narration' => "Share transfer from {$this->senderMemberDetails->client_number}",
            'reference' => $transfer->id,
            'status' => 'COMPLETED',
            'created_by' => auth()->id()
        ]);
    }

 
    private function resetWithdrawalForm()
    {
        Log::info('Resetting withdrawal form');
        $this->selectedShareType = null;
        $this->withdrawalShares = null;
        $this->selectedReceivingAccount = null;
        $this->withdrawalReason = null;
        $this->showShareWithdrawal = false;
        Log::info('Withdrawal form reset completed');
    }

    protected function loadMemberShareTypes()
    {
        Log::info('Loading member share types', [
            'member_id' => $this->memberDetails['id'] ?? null,
            'client_number' => $this->memberDetails['client_number'] ?? null
        ]);

        if (!$this->memberDetails) {
            Log::warning('Cannot load share types - no member details available');
            return;
        }

        try {
            $shareTypes = DB::table('share_registers')
                ->join('sub_products', 'share_registers.product_id', '=', 'sub_products.id')
                ->where('share_registers.member_id', $this->memberDetails['id'])
                ->where('share_registers.status', 'ACTIVE')
                ->select([
                    'sub_products.id',
                    'sub_products.product_name',
                    'sub_products.nominal_price',
                    'sub_products.minimum_required_shares',
                    'sub_products.lock_in_period',
                    'share_registers.current_share_balance as current_balance',
                    DB::raw('share_registers.current_share_balance * sub_products.nominal_price as total_value')
                ])
                ->get()
                ->map(function($item) {
                    return (array) $item;
                })
                ->toArray();

            Log::info('Share types loaded', [
                'count' => count($shareTypes),
                'share_types' => $shareTypes
            ]);

            $this->memberShareTypes = $shareTypes;
        } catch (\Exception $e) {
            Log::error('Error loading member share types', [
                'member_id' => $this->memberDetails['id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('selectedShareType', 'Error loading share types');
        }
    }

    protected function loadReceivingAccounts()
    {
        Log::info('Loading receiving accounts', [
            'client_number' => $this->memberDetails['client_number'] ?? null,
            'member_id' => $this->memberDetails['id'] ?? null
        ]);

        if (!$this->memberDetails) {
            Log::warning('Cannot load receiving accounts - no member details available');
            return;
        }

        try {
            // Load regular accounts (savings, etc.)
            $accounts = AccountsModel::where('client_number', $this->memberDetails['client_number'])
                ->get()
                ->map(function($account) {
                    return [
                        'id' => $account->id,
                        'account_name' => $account->account_name,
                        'account_number' => $account->account_number,
                        'type' => $account->type,
                        'balance' => $account->balance
                    ];
                })
                ->toArray();

            Log::info('Regular accounts loaded', [
                'count' => count($accounts),
                'accounts' => $accounts
            ]);

            $this->receivingAccounts = $accounts;

            // Load share accounts with eager loading           
            $shareRegisters = ShareRegister::with(['account', 'product'])
                ->where('member_id', $this->memberDetails['client_number'])
                ->where('status', 'ACTIVE')
                ->get();               

            $shareAccounts = $shareRegisters->map(function($shareRegister) {
                return [
                    'id' => $shareRegister->id,
                    'product_name' => $shareRegister->product_name,
                    'member_name' => $shareRegister->member_name,
                    'price_per_share' => $shareRegister->nominal_price,
                    'total_shares_issued' => $shareRegister->total_shares_issued,
                    'total_shares_redeemed' => $shareRegister->total_shares_redeemed,
                    'current_share_balance' => $shareRegister->current_share_balance,
                    'total_share_value' => $shareRegister->total_share_value,
                    'accumulated_dividends' => $shareRegister->accumulated_dividends,
                    'account_name' => $shareRegister->account?->account_name,
                    'account_number' => $shareRegister->account?->account_number,
                    'balance' => $shareRegister->account?->balance,
                    'minimum_required_shares' => 0,
                    'lock_in_period' => $shareRegister->product?->lock_in_period,
                    'product_id' => $shareRegister->product?->id
                ];
            })->toArray();


            Log::info('Share accounts loaded', [
                'count' => count($shareAccounts),
                'accounts' => $shareAccounts
            ]);

            $this->sourceAccounts = $shareAccounts;

        } catch (\Exception $e) {
            Log::error('Error loading accounts', [
                'client_number' => $this->memberDetails['client_number'] ?? null,
                'member_id' => $this->memberDetails['id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('selectedReceivingAccount', 'Error loading accounts');
        }
    }

    private function showSuccessMessage($message)
    {
        Log::info('Showing success message', [
            'message' => $message
        ]);
        session()->flash('message', $message);
    }

    private function showErrorMessage($message)
    {
        Log::error('Showing error message', [
            'message' => $message
        ]);
        session()->flash('error', $message);
    }

    public function loadWithdrawals()
    {
        try {
            $query = ShareWithdrawal::with(['member', 'approver'])
                ->when($this->dateFrom, function($q) {
                    return $q->whereDate('created_at', '>=', $this->dateFrom);
                })
                ->when($this->dateTo, function($q) {
                    return $q->whereDate('created_at', '<=', $this->dateTo);
                })
                ->when($this->withdrawalStatus, function($q) {
                    return $q->where('status', $this->withdrawalStatus);
                });

            $paginator = $query->latest()->paginate(10);
            $this->withdrawals = [
                'data' => $paginator->items(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total()
            ];
            
            $this->totalWithdrawals = $query->count();
            $this->totalSharesWithdrawn = $query->sum('withdrawal_amount');
            $this->totalWithdrawalValue = $query->sum('total_value');

            Log::info('Withdrawals loaded successfully', [
                'total' => $this->totalWithdrawals,
                'total_shares' => $this->totalSharesWithdrawn,
                'total_value' => $this->totalWithdrawalValue
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading withdrawals', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('withdrawals', 'Failed to load withdrawals. Please try again.');
        }
    }

    public function showShareWithdrawalReport()
    {
        $this->showShareWithdrawalReport = true;
        $this->sidebar_view = 'share_withdrawal_report';
        $this->loadWithdrawals();
    }

    public function hideShareWithdrawalReport()
    {
        $this->showShareWithdrawalReport = false;
    }

    public function showWithdrawalDetails($withdrawalId)
    {
        $this->selectedWithdrawal = ShareWithdrawal::with(['member', 'approver'])->find($withdrawalId);
        $this->showWithdrawalDetails = true;
    }

    public function hideWithdrawalDetails()
    {
        $this->showWithdrawalDetails = false;
        $this->selectedWithdrawal = null;
    }

    public function updatedDateFrom()
    {
        $this->loadWithdrawals();
    }

    public function updatedDateTo()
    {
        $this->loadWithdrawals();
    }

    public function updatedWithdrawalStatus()
    {
        $this->loadWithdrawals();
    }

    public function viewWithdrawalDetails($withdrawalId)
    {
        $this->selectedWithdrawal = ShareWithdrawal::with(['member', 'product', 'approver'])
            ->find($withdrawalId);
        $this->showWithdrawalDetails = true;

        Log::info('Viewing withdrawal details', [
            'withdrawal_id' => $withdrawalId,
            'member_id' => $this->selectedWithdrawal->member_id,
            'product_id' => $this->selectedWithdrawal->product_id
        ]);
    }

    public function exportWithdrawalReport()
    {
        $query = ShareWithdrawal::with(['member', 'product', 'approver'])
            ->when($this->dateFrom, function ($query) {
                return $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                return $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->when($this->withdrawalStatus, function ($query) {
                return $query->where('status', $this->withdrawalStatus);
            });

        $withdrawals = $query->get();

        $headers = [
            'Date',
            'Member Name',
            'Client Number',
            'Product',
            'Shares',
            'Value',
            'Status',
            'Reason',
            'Processed By',
            'Processed At'
        ];

        $rows = $withdrawals->map(function ($withdrawal) {
            return [
                $withdrawal->created_at->format('Y-m-d'),
                $withdrawal->member->first_name . ' ' . $withdrawal->member->last_name,
                $withdrawal->client_number,
                $withdrawal->product_name,
                number_format($withdrawal->withdrawal_amount),
                number_format($withdrawal->total_value),
                $withdrawal->status,
                $withdrawal->reason,
                $withdrawal->approver->name ?? 'N/A',
                $withdrawal->approved_at ? $withdrawal->approved_at->format('Y-m-d H:i:s') : 'N/A'
            ];
        });

        $filename = 'share_withdrawals_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename);

        Log::info('Share withdrawal report exported', [
            'filename' => $filename,
            'total_records' => $withdrawals->count()
        ]);
    }

    public function updatedSelectedSourceAccount($value)
    {
        // Add any additional logic you want to execute when selectedSourceAccount is updated
        Log::info('Selected source account updated', ['value' => $value]);
    }

    protected function calculateTotalValue()
    {
        if (!$this->number_of_shares || !$this->price_per_share) {
            $this->total_value = 0;
            return;
        }

        // Validate against available shares
        if ($this->selectedProduct && $this->number_of_shares > $this->selectedProduct['available_shares']) {
            $this->addError('number_of_shares', 'Number of shares exceeds available shares in the product.');
            $this->total_value = 0;
            return;
        }

        // Validate against minimum required shares
        if ($this->selectedProduct && $this->number_of_shares < $this->selectedProduct['minimum_required_shares']) {
            $this->addError('number_of_shares', 'Number of shares is below minimum required.');
            $this->total_value = 0;
            return;
        }

        // Calculate total value
        $this->total_value = $this->number_of_shares * $this->price_per_share;
    }

    public function closeShowIssueNewShares()
    {
        $this->reset([
            'showIssueShares',
            'client_number',
            'memberDetails',
            'selectedShareType',
            'number_of_shares',
            'linked_savings_account',

            'current_member_shares',
            'memberShareTypes',
            'withdrawalShares',
            'withdrawalReason',
            'recipientMember'
        ]);
    }

    // REDEMPTION METHODS

    public function showRedeemSharesModal()
    {
        if (!$this->authorize('create', 'You do not have permission to redeem shares')) {
            return;
        }
        $this->sidebar_view = 'redeem_shares';
        $this->showRedeemShares = true;
    }

    public function closeShowRedeemShares()
    {
        $this->reset([
            'showRedeemShares',
            'redemption_product',
            'redemption_client_number',
            'redemption_number_of_shares',
            'redemption_price_per_share',
            'redemption_total_value',
            'redemption_linked_savings_account',
            'redemption_share_account',
            'redemptionSelectedProduct',
            'redemptionMemberDetails'
        ]);
    }

    public function checkRedemptionClientNumberLength()
    {
        if (strlen($this->redemption_client_number) === 5) {
            // Fetch member details
            $client = DB::table('clients')
                ->where('client_number', $this->redemption_client_number)
                ->first();

            if ($client) {
                // Get member's current shares for the selected product
                $currentShares = 0;
                if ($this->redemption_product) {
                    $shareRegister = DB::table('share_registers')
                        ->where('member_id', $this->redemption_client_number)
                        ->where('product_id', $this->redemption_product)
                        ->first();
                    $currentShares = $shareRegister->current_share_balance ?? 0;
                }

                $this->redemptionMemberDetails = [
                    'full_name' => trim($client->first_name . ' ' . ($client->middle_name ?? '') . ' ' . $client->last_name),
                    'client_number' => $client->client_number,
                    'status' => $client->status,
                    'phone_number' => $client->phone_number ?? 'N/A',
                    'email' => $client->email ?? 'N/A',
                    'current_shares' => $currentShares,
                    'join_date' => $client->created_at ?? 'N/A'
                ];
            } else {
                $this->redemptionMemberDetails = null;
            }
        } else {
            $this->redemptionMemberDetails = null;
        }
    }

    public function updatedRedemptionProduct()
    {
        if ($this->redemption_product) {
            $product = DB::table('sub_products')->find($this->redemption_product);
            if ($product) {
                $this->redemptionSelectedProduct = [
                    'id' => $product->id,
                    'product_name' => $product->product_name,
                    'nominal_price' => $product->nominal_price,
                    'available_shares' => $product->available_shares ?? 0
                ];
                $this->redemption_price_per_share = $product->nominal_price;

                // Update member's current shares for this product
                if ($this->redemption_client_number && strlen($this->redemption_client_number) === 5) {
                    $this->checkRedemptionClientNumberLength();
                }
            }
        } else {
            $this->redemptionSelectedProduct = null;
            $this->redemption_price_per_share = 0;
        }
    }

    public function redeemShares()
    {
        if (!$this->authorize('create', 'You do not have permission to redeem shares')) {
            return;
        }

        try {
            Log::info('Starting share redemption process', [
                'redemption_client_number' => $this->redemption_client_number,
                'redemption_product' => $this->redemption_product,
                'redemption_number_of_shares' => $this->redemption_number_of_shares,
                'user_id' => auth()->id()
            ]);

            // Calculate total value
            $this->redemption_total_value = $this->redemption_number_of_shares * $this->redemption_price_per_share;

            // Prepare data for service
            $data = [
                'product_id' => $this->redemption_product,
                'client_number' => $this->redemption_client_number,
                'number_of_shares' => $this->redemption_number_of_shares,
                'price_per_share' => $this->redemption_price_per_share,
                'linked_savings_account' => $this->redemption_linked_savings_account,
                'share_account' => $this->redemption_share_account,
                'total_value' => $this->redemption_total_value
            ];

            // Use ShareRedemptionService
            $redemptionService = new \App\Services\ShareRedemptionService(
                new \App\Services\TransactionPostingService()
            );

            $result = $redemptionService->redeemShares($data);

            if ($result['success']) {
                session()->flash('success', $result['message']);
                $this->closeShowRedeemShares();
            } else {
                if (isset($result['errors'])) {
                    session()->flash('validation_errors', $result['errors']);
                } else {
                    session()->flash('error', $result['message']);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error in share redemption', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            session()->flash('error', 'An error occurred while processing your request.');
        }
    }

    /**
     * Add a share product to the selectedShareProducts array by its ID.
     * Prevents duplicates. Uses data from sourceAccounts.
     */
    public function addShareProduct($id)
    {
        // Find the account in sourceAccounts by ID
        $account = collect($this->sourceAccounts)->firstWhere('id', $id);
        if (!$account) {
            $this->showErrorMessage('Account not found.');
            return;
        }
        // Prevent duplicates
        if (isset($this->selectedShareProducts[$id])) {
            $this->showErrorMessage('Product already selected.');
            return;
        }
        // Add to selectedShareProducts
        $this->selectedShareProducts[$id] = [
            'product_name' => $account['product_name'] ?? '',
            'current_balance' => $account['current_share_balance'] ?? 0,
            'total_share_value' => $account['total_share_value'] ?? 0,
            'withdrawal_amount' => 0, // Initialize withdrawal_amount
            'total_value' => 0, // Initialize total_value
            'account_number' => $account['account_number'] ?? 0,
            'product_id' => $account['product_id'] ?? 0,
        ];
       
        $this->showSuccessMessage('Product added successfully.');
    }

    public function removeShareProduct($id)
    {
        unset($this->selectedShareProducts[$id]);
        $this->showSuccessMessage('Product removed successfully.');
    }



    // Add a method to update total_value when withdrawal_amount changes
    public function updatedSelectedShareProducts($value, $key)
    {
        if (strpos($key, '.withdrawal_amount') !== false) {
            $productId = explode('.', $key)[0];
            if (isset($this->selectedShareProducts[$productId])) {
                $withdrawalAmount = floatval(str_replace(',', '', $this->selectedShareProducts[$productId]['withdrawal_amount'] ?? 0));
                $totalShareValue = floatval(str_replace(',', '', $this->selectedShareProducts[$productId]['total_share_value'] ?? 0));
                $currentBalance = floatval(str_replace(',', '', $this->selectedShareProducts[$productId]['current_balance'] ?? 0));
    
                if ($currentBalance > 0) {
                    $this->selectedShareProducts[$productId]['total_value'] = ($withdrawalAmount / $currentBalance) * $totalShareValue;
                } else {
                    $this->selectedShareProducts[$productId]['total_value'] = 0;
                }
            }
        }
        $this->updateWithdrawalSummary();
    }

    public function updateWithdrawalSummary()
    {
        $totalShares = 0;
        $totalValue = 0;
        foreach ($this->selectedShareProducts as $product) {
            $totalShares += floatval(str_replace(',', '', $product['withdrawal_amount'] ?? 0));
            $totalValue += floatval(str_replace(',', '', $product['total_value'] ?? 0));
        }
        $this->totalWithdrawalShares = $totalShares; // Update to match Blade view
        $this->totalWithdrawalValue = $totalValue; // Update to match Blade view
    }

    public function processShareWithdrawal()
    {
        if (!$this->authorize('withdraw', 'You do not have permission to withdraw shares')) {
            return;
        }

        if(empty($this->withdrawalReason)){
            //$this->showErrorMessage('Please enter a withdrawal reason');
            return;
        }

  

        // Log all the data
        Log::info('Selected share products', ['selected_share_products' => $this->selectedShareProducts]);
        Log::info('Selected receiving account', ['selected_receiving_account' => $this->selectedReceivingAccount]);
        Log::info('Selected member details', ['member_details' => $this->memberDetails]);
        Log::info('Client number', ['client_number' => $this->client_number]);
        Log::info('Withdrawal reason', ['withdrawal_reason' => $this->withdrawalReason]);
        Log::info('Member details', ['member_details' => $this->memberDetails]);


        try {
            DB::beginTransaction();
            
            foreach ($this->selectedShareProducts as $productId => $product) {

                //dd($product);
                $withdrawalId = DB::table('share_withdrawals')
                    ->insertGetId([
                        'member_id' => $this->memberDetails->id,
                        'client_number' => $this->client_number,
                        'product_id' => $productId,
                        'product_name' => $product['product_name'],
                        'withdrawal_amount' => $product['withdrawal_amount'],
                        'nominal_price' => $product['total_share_value'] / $product['current_balance'],
                        'total_value' => $product['total_value'],
                        'receiving_account_id' => $this->selectedReceivingAccount,
                        'receiving_account_number' => $this->selectedReceivingAccount,
                        'source_account_id' => $product['product_id'],
                        'source_account_number' => $product['account_number'],
                        'reason' => $this->withdrawalReason,
                        'status' => 'PENDING',
                        'created_by' => auth()->id(),
                        'created_at' => now()
                    ]);

                approvals::create([
                    
                    'process_name' => 'share_withdrawal',
                    'process_description' => "Share withdrawal request for {$product['product_name']}",
                    'approval_process_description' => "Member {$this->memberDetails['first_name']} {$this->memberDetails['last_name']} is requesting to withdraw {$product['withdrawal_amount']} shares worth TZS " . number_format($product['total_value'], 2),
                    'process_code' => 'SHARE_WD',
                    'process_id' => $withdrawalId,
                    'process_status' => 'PENDING',
                    'user_id' => auth()->id(),
                    'team_id' => '',
                        'edit_package' => null
                ]);
            }

            DB::commit();
            $this->showSuccessMessage('Share withdrawal request submitted successfully.');
            $this->closeShareWithdrawalModal();
            $this->resetData();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->showErrorMessage('Error processing share withdrawal: ' . $e->getMessage());
        }
    }



    public function processApprovedWithdrawal($withdrawalId)
    {
        Log::info('Processing approved withdrawal', ['withdrawal_id' => $withdrawalId]);

        DB::transaction(function () use ($withdrawalId) {
            $withdrawal = DB::table('share_withdrawals')->find($withdrawalId);
            
            if (!$withdrawal) {
                Log::error('Withdrawal not found', ['withdrawal_id' => $withdrawalId]);
                throw new \Exception('Withdrawal record not found');
            }

            Log::info('Retrieved withdrawal details', [
                'withdrawal_id' => $withdrawalId,
                'member_id' => $withdrawal->member_id,
                'product_id' => $withdrawal->product_id,
                'withdrawal_amount' => $withdrawal->withdrawal_amount,
                'total_value' => $withdrawal->total_value,
                'receiving_account_id' => $withdrawal->receiving_account_id,
                'source_account_id' => $withdrawal->source_account_id
            ]);

            $memberId = DB::table('clients')->where('id', $withdrawal->member_id)->first()->client_number;

            // 1. Update share register
            DB::table('share_registers')
                ->where('member_id', $memberId)
                ->where('product_id', $withdrawal->product_id)
                ->update([
                    'total_shares_redeemed' => DB::raw('total_shares_redeemed + ' . $withdrawal->withdrawal_amount),
                    'current_share_balance' => DB::raw('current_share_balance - ' . $withdrawal->withdrawal_amount),
                    'total_share_value' => DB::raw('(current_share_balance - ' . $withdrawal->withdrawal_amount . ') * nominal_price'),
                    'last_transaction_type' => 'REDEEM',
                    'last_transaction_reference' => 'WD-' . $withdrawalId,
                    'last_transaction_date' => now(),
                    'last_activity_date' => now()
                ]);

            Log::info('Share register updated', [
                'member_id' => $withdrawal->member_id,
                'product_id' => $withdrawal->product_id,
                'shares_redeemed' => $withdrawal->withdrawal_amount,
                'transaction_reference' => 'WD-' . $withdrawalId
            ]);


            //2. Update share sub product
            DB::table('sub_products')
                ->where('id', $withdrawal->product_id)
                ->update([
                    'available_shares' => DB::raw('available_shares + ' . $withdrawal->withdrawal_amount),
                ]);

            Log::info('Share sub product updated', [
                'product_id' => $withdrawal->product_id,
                'available_shares' => $withdrawal->withdrawal_amount,
                'transaction_reference' => 'WD-' . $withdrawalId
            ]);
            

            // 3. Process payment if linked to savings account
            if (!empty($withdrawal->source_account_number) && !empty($withdrawal->receiving_account_number)) {
                $totalAmount = $withdrawal->total_value;
                
                // Post the transaction using TransactionPostingService
                $transactionService = new TransactionPostingService();
                $transactionData = [
                    'first_account' => $withdrawal->source_account_number, // Debit account (savings)
                    'second_account' => $withdrawal->receiving_account_number, // Credit account (shares)
                    'amount' => $totalAmount,
                    'narration' => 'Share withdrawal - ' . $withdrawal->withdrawal_amount . ' shares from ' . $withdrawal->product_name,
                    'action' => 'share_withdrawal'
                ];

                Log::info('Posting share withdrawal transaction', [
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
                
                // Generate receipt for share redemption
                $member = ClientsModel::where('client_number', $withdrawal->member_id)->first();
                $this->generateShareRedemptionReceipt(
                    $member,
                    $withdrawal,
                    $result['reference'] ?? 'WD-' . $withdrawalId
                );
            }

            // 4. Update withdrawal status
            DB::table('share_withdrawals')
                ->where('id', $withdrawalId)
                ->update([
                    'status' => 'COMPLETED',
                    'approved_by' => auth()->id(),
                    'approved_at' => now()
                ]);

            Log::info('Withdrawal status updated to completed', [
                'withdrawal_id' => $withdrawalId,
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);
        });

        Log::info('Approved withdrawal processing completed', [
            'withdrawal_id' => $withdrawalId,
            'processed_by' => auth()->id()
        ]);
    }

    public function exportTransfersReport()
    {
        try {
            // Get the filtered transfers data
            $transfers = $this->loadShareTransfers();

            if($transfers == null){
                session()->flash('error', 'Nothing to export');
                return;
            }
            
            // Generate filename with timestamp
            $filename = 'share_transfers_report_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            // Set headers for CSV download
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            // Create CSV content
            $callback = function() use ($transfers) {
                $file = fopen('php://output', 'w');
                
                // Add CSV headers
                fputcsv($file, [
                    'Date',
                    'Reference',
                    'Sender Name',
                    'Sender Number',
                    'Receiver Name', 
                    'Receiver Number',
                    'Shares',
                    'Value (TZS)',
                    'Status',
                    'Reason'
                ]);
                
                // Add data rows
                foreach ($transfers as $transfer) {
                    fputcsv($file, [
                        $transfer->created_at->format('Y-m-d H:i'),
                        $transfer->transaction_reference,
                        $transfer->sender_member_name,
                        $transfer->sender_client_number,
                        $transfer->receiver_member_name,
                        $transfer->receiver_client_number,
                        number_format($transfer->number_of_shares),
                        number_format($transfer->total_value, 2),
                        $transfer->status,
                        $transfer->transfer_reason ?? 'N/A'
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Error exporting transfers report: ' . $e->getMessage());
            $this->showErrorMessage('Error exporting report: ' . $e->getMessage());
        }
    }

    /**
     * Show Share Statement Modal
     */
    public function showShareStatementModal()
    {
        $this->showShareStatement = true;
        $this->dateFrom = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->shareStatementData = null;
    }

    /**
     * Hide Share Statement Modal
     */
    public function hideShareStatementModal()
    {
        $this->showShareStatement = false;
        $this->shareStatementData = null;
    }

    /**
     * Generate Share Statement from Account Number
     * Gets transactions from general_ledger where record_on_account_number matches the account
     * For the past 12 months
     */
    public function generateShareStatementFromAccount($accountNumber = null)
    {
        try {
            // Use provided account number or get from selected share register
            $account = $accountNumber ?? $this->shareAccountNumber ?? null;

            if (!$account) {
                // Try to get account from member's share register
                if ($this->memberDetails && $this->memberDetails->id) {
                    $shareRegister = DB::table('share_registers')
                        ->where('member_id', $this->memberDetails->id)
                        ->first();

                    if ($shareRegister) {
                        $account = $shareRegister->share_account_number;
                    }
                }
            }

            if (!$account) {
                session()->flash('error', 'Please provide an account number to generate statement');
                return null;
            }

            // Set date range to past 12 months
            $startDate = Carbon::now()->subMonths(12)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            // Query general_ledger for transactions where record_on_account_number matches
            $transactions = DB::table('general_ledger')
                ->where('record_on_account_number', $account)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get();

            // Calculate opening balance (sum of all transactions before start date)
            $openingTransactions = DB::table('general_ledger')
                ->where('record_on_account_number', $account)
                ->where('created_at', '<', $startDate)
                ->get();

            $openingBalance = 0;
            foreach ($openingTransactions as $trans) {
                $openingBalance += ($trans->debit ?? 0) - ($trans->credit ?? 0);
            }

            // Calculate running balance for each transaction
            $runningBalance = $openingBalance;
            $statementTransactions = [];
            $totalDebits = 0;
            $totalCredits = 0;

            foreach ($transactions as $transaction) {
                $debit = $transaction->debit ?? 0;
                $credit = $transaction->credit ?? 0;
                $runningBalance += $debit - $credit;

                $totalDebits += $debit;
                $totalCredits += $credit;

                // Convert to object format expected by modal
                $transObj = (object)[
                    'date' => Carbon::parse($transaction->created_at)->format('Y-m-d H:i:s'),
                    'type' => $transaction->transaction_type ?? 'OTHER',
                    'reference' => $transaction->reference_number ?? 'N/A',
                    'narration' => $transaction->narration ?? $transaction->transaction_type ?? '',
                    'shares' => $debit > 0 ? $debit : $credit,
                    'amount' => $debit > 0 ? $debit : $credit,
                    'balance_after' => $runningBalance,
                ];

                $statementTransactions[] = $transObj;
            }

            // Get account details
            $accountDetails = DB::table('accounts')
                ->where('account_number', $account)
                ->first();

            // Get member details from client_number
            $member = null;
            if ($accountDetails && $accountDetails->client_number) {
                $member = DB::table('clients')
                    ->where('client_number', $accountDetails->client_number)
                    ->first();
            }

            // Get share register and product details
            $shareRegister = DB::table('share_registers')
                ->where('share_account_number', $account)
                ->first();

            $productName = 'Share Account';
            $currentPrice = 0;
            if ($shareRegister && $shareRegister->product_id) {
                $product = DB::table('sub_products')
                    ->where('id', $shareRegister->product_id)
                    ->first();
                if ($product) {
                    $productName = $product->product_name;
                }
                $currentPrice = $shareRegister->current_price ?? 0;
            }

            // Prepare statement data with statements array structure
            $this->shareStatementData = [
                'member' => $member,
                'period_start' => $startDate,
                'period_end' => $endDate,
                'generated_at' => now(),
                'statements' => [
                    [
                        'product_name' => $productName,
                        'account_number' => $account,
                        'opening_balance' => $openingBalance,
                        'opening_value' => $openingBalance * $currentPrice,
                        'transactions' => $statementTransactions,
                        'total_purchases' => 0,
                        'total_redemptions' => 0,
                        'total_transfers_in' => 0,
                        'total_transfers_out' => 0,
                        'closing_balance' => $runningBalance,
                        'closing_value' => $runningBalance * $currentPrice,
                    ]
                ]
            ];

            // Show the modal
            $this->showShareStatement = true;

            session()->flash('success', 'Statement generated successfully for account: ' . $account);

            return $this->shareStatementData;

        } catch (\Exception $e) {
            Log::error('Error generating share statement from general ledger: ' . $e->getMessage());
            session()->flash('error', 'Failed to generate share statement: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate Share Statement for a member
     * Shows opening balance, purchases, redemptions, and closing balance
     */
    public function generateShareStatement($memberId = null, $dateFrom = null, $dateTo = null)
    {
        try {
            // Use provided member ID or current member
            $memberIdToUse = $memberId ?? $this->memberDetails->id ?? null;
            
            if (!$memberIdToUse) {
                throw new \Exception('Please select a member to generate statement');
            }

            // Get member details
            $member = ClientsModel::find($memberIdToUse);
            if (!$member) {
                throw new \Exception('Member not found');
            }

            // Set date range (default to current year if not provided)
            $startDate = $dateFrom ? Carbon::parse($dateFrom) : Carbon::now()->startOfYear();
            $endDate = $dateTo ? Carbon::parse($dateTo) : Carbon::now()->endOfDay();

            // Get all share registers for the member
            $shareRegisters = DB::table('share_registers')
                ->where('member_id', $memberIdToUse)
                ->get();

            $statementData = [];

            foreach ($shareRegisters as $register) {
                // Get opening balance (balance at start date)
                $openingBalanceData = $this->getShareBalanceAtDate($register->id, $startDate);
                
                // Get all transactions within the period
                $transactions = $this->getShareTransactions($register->id, $startDate, $endDate);
                
                // Calculate closing balance
                $closingBalance = $register->current_share_balance;
                $closingValue = $register->total_share_value;

                $statementData[] = [
                    'product_name' => $register->product_name ?? 'Share Account',
                    'account_number' => $register->share_account_number,
                    'opening_balance' => $openingBalanceData['shares'],
                    'opening_value' => $openingBalanceData['value'],
                    'transactions' => $transactions,
                    'total_purchases' => $transactions->where('type', 'PURCHASE')->sum('shares'),
                    'total_redemptions' => $transactions->where('type', 'REDEMPTION')->sum('shares'),
                    'total_transfers_in' => $transactions->where('type', 'TRANSFER_IN')->sum('shares'),
                    'total_transfers_out' => $transactions->where('type', 'TRANSFER_OUT')->sum('shares'),
                    'closing_balance' => $closingBalance,
                    'closing_value' => $closingValue,
                    'period_start' => $startDate->format('Y-m-d'),
                    'period_end' => $endDate->format('Y-m-d')
                ];
            }

            // Store statement data for display
            $this->shareStatementData = [
                'member' => $member,
                'statements' => $statementData,
                'period_start' => $startDate,
                'period_end' => $endDate,
                'generated_at' => now()
            ];

            return $this->shareStatementData;

        } catch (\Exception $e) {
            Log::error('Error generating share statement: ' . $e->getMessage());
            session()->flash('error', 'Failed to generate share statement: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get share balance at a specific date
     */
    private function getShareBalanceAtDate($shareRegisterId, $date)
    {
        // Get all transactions before the date
        $transactions = DB::table('general_ledger')
            ->where('share_register_id', $shareRegisterId)
            ->where('created_at', '<', $date)
            ->get();

        $balance = 0;
        $value = 0;

        foreach ($transactions as $transaction) {
            if (in_array($transaction->transaction_type, ['SHARE_PURCHASE', 'TRANSFER_IN'])) {
                $balance += $transaction->shares ?? 0;
                $value += $transaction->credit ?? 0;
            } elseif (in_array($transaction->transaction_type, ['SHARE_REDEMPTION', 'TRANSFER_OUT'])) {
                $balance -= $transaction->shares ?? 0;
                $value -= $transaction->debit ?? 0;
            }
        }

        return [
            'shares' => $balance,
            'value' => $value
        ];
    }

    /**
     * Get share transactions within a period
     */
    private function getShareTransactions($shareRegisterId, $startDate, $endDate)
    {
        return DB::table('general_ledger')
            ->where('share_register_id', $shareRegisterId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($transaction) {
                $type = 'OTHER';
                $shares = 0;
                $amount = 0;

                switch ($transaction->transaction_type) {
                    case 'SHARE_PURCHASE':
                        $type = 'PURCHASE';
                        $shares = $transaction->shares ?? 0;
                        $amount = $transaction->credit ?? 0;
                        break;
                    case 'SHARE_REDEMPTION':
                        $type = 'REDEMPTION';
                        $shares = $transaction->shares ?? 0;
                        $amount = $transaction->debit ?? 0;
                        break;
                    case 'SHARE_TRANSFER_IN':
                        $type = 'TRANSFER_IN';
                        $shares = $transaction->shares ?? 0;
                        $amount = $transaction->credit ?? 0;
                        break;
                    case 'SHARE_TRANSFER_OUT':
                        $type = 'TRANSFER_OUT';
                        $shares = $transaction->shares ?? 0;
                        $amount = $transaction->debit ?? 0;
                        break;
                }

                return (object)[
                    'date' => $transaction->created_at,
                    'type' => $type,
                    'reference' => $transaction->reference_number,
                    'narration' => $transaction->narration,
                    'shares' => $shares,
                    'amount' => $amount,
                    'balance_after' => $transaction->running_balance ?? 0
                ];
            });
    }

    /**
     * Export share statement to PDF
     */
    public function exportShareStatementPDF($memberId = null)
    {
        try {
            // Generate statement data
            $statementData = $this->generateShareStatement($memberId, $this->dateFrom, $this->dateTo);
            
            if (!$statementData) {
                return;
            }

            // Create PDF (you'll need to install a PDF package like dompdf or tcpdf)
            // For now, we'll export as CSV
            return $this->exportShareStatementCSV($memberId);

        } catch (\Exception $e) {
            Log::error('Error exporting share statement PDF: ' . $e->getMessage());
            session()->flash('error', 'Failed to export share statement: ' . $e->getMessage());
        }
    }

    /**
     * Export share statement to CSV
     */
    public function exportShareStatementCSV($memberId = null)
    {
        try {
            // Generate statement data
            $statementData = $this->generateShareStatement($memberId, $this->dateFrom, $this->dateTo);
            
            if (!$statementData) {
                return;
            }

            $filename = 'share_statement_' . $statementData['member']->client_number . '_' . now()->format('Y-m-d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($statementData) {
                $file = fopen('php://output', 'w');
                
                // Add header information
                fputcsv($file, ['SHARE STATEMENT']);
                fputcsv($file, ['Member Name:', $statementData['member']->first_name . ' ' . $statementData['member']->last_name]);
                fputcsv($file, ['Member Number:', $statementData['member']->client_number]);
                fputcsv($file, ['Period:', $statementData['period_start']->format('d/m/Y') . ' to ' . $statementData['period_end']->format('d/m/Y')]);
                fputcsv($file, ['Generated:', $statementData['generated_at']->format('d/m/Y H:i:s')]);
                fputcsv($file, []);
                
                foreach ($statementData['statements'] as $statement) {
                    // Product header
                    fputcsv($file, ['Product:', $statement['product_name']]);
                    fputcsv($file, ['Account Number:', $statement['account_number']]);
                    fputcsv($file, ['Opening Balance:', $statement['opening_balance'] . ' shares', 'Value: TZS ' . number_format($statement['opening_value'], 2)]);
                    fputcsv($file, []);
                    
                    // Transaction headers
                    fputcsv($file, ['Date', 'Type', 'Reference', 'Description', 'Shares', 'Amount', 'Balance']);
                    
                    // Transaction details
                    foreach ($statement['transactions'] as $transaction) {
                        fputcsv($file, [
                            Carbon::parse($transaction->date)->format('d/m/Y'),
                            $transaction->type,
                            $transaction->reference,
                            $transaction->narration,
                            $transaction->shares,
                            number_format($transaction->amount, 2),
                            $transaction->balance_after
                        ]);
                    }
                    
                    fputcsv($file, []);
                    // Summary
                    fputcsv($file, ['Summary:']);
                    fputcsv($file, ['Total Purchases:', $statement['total_purchases'] . ' shares']);
                    fputcsv($file, ['Total Redemptions:', $statement['total_redemptions'] . ' shares']);
                    fputcsv($file, ['Total Transfers In:', $statement['total_transfers_in'] . ' shares']);
                    fputcsv($file, ['Total Transfers Out:', $statement['total_transfers_out'] . ' shares']);
                    fputcsv($file, ['Closing Balance:', $statement['closing_balance'] . ' shares', 'Value: TZS ' . number_format($statement['closing_value'], 2)]);
                    fputcsv($file, []);
                    fputcsv($file, []);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting share statement CSV: ' . $e->getMessage());
            session()->flash('error', 'Failed to export share statement: ' . $e->getMessage());
        }
    }

    public function approveTransfer($transferId)
    {
        if (!$this->authorize('approve', 'You do not have permission to approve share transfers')) {
            return;
        }
        
        try {
            DB::beginTransaction();
            
            // Update transfer status to approved
            DB::table('share_transfers')
                ->where('id', $transferId)
                ->update([
                    'status' => 'COMPLETED',
                    'approved_by' => auth()->id(),
                    'approved_at' => now()
                ]);
            
            // Process the approved transfer
            $this->processApprovedTransfer($transferId);
            
            DB::commit();
            
            $this->showSuccessMessage('Transfer approved successfully.');
            $this->loadShareTransfers(); // Refresh the transfers list
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving transfer: ' . $e->getMessage());
            $this->showErrorMessage('Error approving transfer: ' . $e->getMessage());
        }
    }

    public function rejectTransfer($transferId)
    {
        try {
            // Update transfer status to rejected
            DB::table('share_transfers')
                ->where('id', $transferId)
                ->update([
                    'status' => 'REJECTED',
                    'rejected_by' => auth()->id(),
                    'rejected_at' => now()
                ]);
            
            $this->showSuccessMessage('Transfer rejected successfully.');
            $this->loadShareTransfers(); // Refresh the transfers list
            
        } catch (\Exception $e) {
            Log::error('Error rejecting transfer: ' . $e->getMessage());
            $this->showErrorMessage('Error rejecting transfer: ' . $e->getMessage());
        }
    }

    /**
     * Generate receipt for share purchase
     */
    private function generateSharePurchaseReceipt($member, $shareData, $shareProduct, $amount, $referenceNumber)
    {
        try {
            $receiptNumber = 'SHR-PUR-' . strtoupper(uniqid());
            
            \App\Models\Receipt::create([
                'receipt_number' => $receiptNumber,
                'transaction_id' => null, // Can be linked to transaction if needed
                'account_id' => null, // Can be linked to account if needed
                'member_number' => $member->client_number,
                'member_name' => trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name),
                'amount' => $amount,
                'currency' => 'TZS',
                'payment_method' => 'account_transfer',
                'depositor_name' => auth()->user()->name,
                'narration' => 'Purchase of ' . $shareData['number_of_shares'] . ' shares of ' . $shareProduct->product_name,
                'reference_number' => $referenceNumber,
                'bank_name' => 'Internal Transfer',
                'processed_by' => auth()->id(),
                'branch' => auth()->user()->branch_id ?? 1,
                'transaction_type' => 'Share Purchase',
                'status' => 'GENERATED',
                'generated_at' => now(),
                'printed_at' => null,
                'metadata' => [
                    'shares_purchased' => $shareData['number_of_shares'],
                    'price_per_share' => $shareProduct->nominal_price,
                    'total_value' => $amount,
                    'share_product' => $shareProduct->product_name,
                    'share_account' => $shareData['share_account'] ?? null,
                    'linked_savings_account' => $shareData['linked_savings_account'] ?? null
                ]
            ]);
            
            // Store receipt data for display
            $this->shareReceiptData = [
                'receipt_number' => $receiptNumber,
                'transaction_date' => now()->format('d/m/Y H:i:s'),
                'member_name' => trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name),
                'member_number' => $member->client_number,
                'shares_purchased' => $shareData['number_of_shares'],
                'share_product' => $shareProduct->product_name,
                'price_per_share' => number_format($shareProduct->nominal_price, 2),
                'total_amount' => number_format($amount, 2),
                'payment_method' => 'Account Transfer',
                'reference_number' => $referenceNumber,
                'processed_by' => auth()->user()->name,
                'branch' => auth()->user()->branch_id ?? 1,
                'currency' => 'TZS',
                'transaction_type' => 'Share Purchase'
            ];
            
            // Trigger receipt display
            $this->showShareReceiptModal = true;
            
            Log::info('Share purchase receipt generated', [
                'receipt_number' => $receiptNumber,
                'member' => $member->client_number,
                'amount' => $amount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating share purchase receipt: ' . $e->getMessage());
        }
    }

    /**
     * Generate receipt for share redemption/withdrawal
     */
    private function generateShareRedemptionReceipt($member, $withdrawal, $referenceNumber)
    {
        try {
            $receiptNumber = 'SHR-RED-' . strtoupper(uniqid());
            
            \App\Models\Receipt::create([
                'receipt_number' => $receiptNumber,
                'transaction_id' => null,
                'account_id' => null,
                'member_number' => $member->client_number,
                'member_name' => trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name),
                'amount' => $withdrawal->total_value,
                'currency' => 'TZS',
                'payment_method' => 'account_transfer',
                'depositor_name' => auth()->user()->name,
                'narration' => 'Redemption of ' . $withdrawal->withdrawal_amount . ' shares from ' . $withdrawal->product_name,
                'reference_number' => $referenceNumber,
                'bank_name' => 'Internal Transfer',
                'processed_by' => auth()->id(),
                'branch' => auth()->user()->branch_id ?? 1,
                'transaction_type' => 'Share Redemption',
                'status' => 'GENERATED',
                'generated_at' => now(),
                'printed_at' => null,
                'metadata' => [
                    'shares_redeemed' => $withdrawal->withdrawal_amount,
                    'total_value' => $withdrawal->total_value,
                    'share_product' => $withdrawal->product_name,
                    'source_account' => $withdrawal->source_account_number,
                    'receiving_account' => $withdrawal->receiving_account_number,
                    'reason' => $withdrawal->reason
                ]
            ]);
            
            // Store receipt data for display
            $this->shareReceiptData = [
                'receipt_number' => $receiptNumber,
                'transaction_date' => now()->format('d/m/Y H:i:s'),
                'member_name' => trim($member->first_name . ' ' . ($member->middle_name ?? '') . ' ' . $member->last_name),
                'member_number' => $member->client_number,
                'shares_redeemed' => $withdrawal->withdrawal_amount,
                'share_product' => $withdrawal->product_name,
                'total_amount' => number_format($withdrawal->total_value, 2),
                'payment_method' => 'Account Transfer',
                'reference_number' => $referenceNumber,
                'processed_by' => auth()->user()->name,
                'branch' => auth()->user()->branch_id ?? 1,
                'currency' => 'TZS',
                'transaction_type' => 'Share Redemption',
                'reason' => $withdrawal->reason
            ];
            
            // Trigger receipt display
            $this->showShareReceiptModal = true;
            
            Log::info('Share redemption receipt generated', [
                'receipt_number' => $receiptNumber,
                'member' => $member->client_number,
                'amount' => $withdrawal->total_value
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating share redemption receipt: ' . $e->getMessage());
        }
    }

    /**
     * Print share receipt
     */
    public function printShareReceipt()
    {
        if ($this->shareReceiptData) {
            $this->dispatchBrowserEvent('printShareReceipt', [
                'receiptData' => $this->shareReceiptData
            ]);
        }
    }

    /**
     * Close share receipt modal
     */
    public function closeShareReceiptModal()
    {
        $this->showShareReceiptModal = false;
        $this->shareReceiptData = null;
    }

    /**
     * Mark receipt as printed
     */
    public function markReceiptAsPrinted()
    {
        if ($this->shareReceiptData) {
            try {
                \App\Models\Receipt::where('receipt_number', $this->shareReceiptData['receipt_number'])
                    ->update(['printed_at' => now()]);
                    
                Log::info('Receipt marked as printed', [
                    'receipt_number' => $this->shareReceiptData['receipt_number']
                ]);
            } catch (\Exception $e) {
                Log::error('Error marking receipt as printed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Download share receipt as PDF
     */
    public function downloadShareReceipt()
    {
        if (!$this->shareReceiptData) {
            return;
        }
        
        try {
            // For now, we'll trigger a print dialog
            // In production, you would generate a PDF using a package like dompdf
            $this->dispatchBrowserEvent('printShareReceipt', [
                'receiptData' => $this->shareReceiptData
            ]);
            
            Log::info('Share receipt download triggered', [
                'receipt_number' => $this->shareReceiptData['receipt_number']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error downloading share receipt: ' . $e->getMessage());
            session()->flash('error', 'Failed to download receipt');
        }
    }
}
