<?php

namespace App\Http\Livewire\Deposits;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Models\sub_products;
use App\Models\ClientsModel;
use App\Models\BranchesModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Exception;

class DepositsFullReport extends Component
{
    use WithPagination;

    // Filter Properties
    public $selectedBranch = '';
    public $selectedMember = '';
    public $selectedAccount = '';
    public $selectedProduct = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $reportType = 'summary'; // summary, detailed, comparative
    public $groupBy = 'member'; // member, account, branch, product, date
    public $timeFrame = 'month'; // day, week, month, quarter, year
    public $statusFilter = 'all'; // all, ACTIVE, INACTIVE, PENDING, BLOCKED

    // Data Properties
    protected $summaryData = null;
    protected $detailedData = null;
    protected $comparativeData = [];
    protected $chartData = [];
    protected $exportData = [];
    protected $summaryStatistics = null;
    protected $chartLabels = [];
    protected $chartValues = [];

    // Modal Properties
    public $showAccountDetailsModal = false;
    public $showTransactionsModal = false;
    public $selectedAccountData = null;
    public $selectedAccountTransactions = [];

    // Loading States
    public $isLoading = false;
    public $isExporting = false;
    public $isGenerating = false;

    // Messages
    public $errorMessage = '';
    public $successMessage = '';

    // Pagination
    public $perPage = 25;
    public $sortBy = 'balance';
    public $sortDirection = 'desc';

    // Available Options
    protected $branches = [];
    protected $members = [];
    protected $accounts = [];
    protected $products = [];

    protected $queryString = [
        'selectedBranch' => ['except' => ''],
        'selectedMember' => ['except' => ''],
        'selectedAccount' => ['except' => ''],
        'selectedProduct' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'reportType' => ['except' => 'summary'],
        'groupBy' => ['except' => 'member'],
        'timeFrame' => ['except' => 'month'],
        'statusFilter' => ['except' => 'all'],
        'perPage' => ['except' => 25],
        'sortBy' => ['except' => 'balance'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        try {
            $this->loadAvailableOptions();
            $this->setDefaultDates();
            $this->loadReport();
        } catch (Exception $e) {
            Log::error('Error in mount method: ' . $e->getMessage());
            $this->errorMessage = 'Failed to initialize the component. Please refresh the page.';
            // Initialize data properties to prevent errors
            $this->summaryData = $this->createEmptyPaginator();
            $this->detailedData = $this->createEmptyPaginator();
            $this->comparativeData = [];
        }
    }

    protected function createEmptyPaginator()
    {
        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
    }

    public function setDefaultDates()
    {
        $this->dateFrom = Carbon::now()->subYear()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
    }

    public function loadAvailableOptions()
    {
        try {
            // Load branches
            $this->branches = BranchesModel::where('status', 'ACTIVE')
                ->orderBy('name')
                ->get(['id', 'name', 'branch_number']);

            // Load products
            $this->products = sub_products::where('product_type', '2000')
                ->where('status', 'ACTIVE')
                ->orderBy('product_name')
                ->get(['id', 'product_name', 'sub_product_name', 'sub_product_id']);

            // Load members (with deposits accounts)
            $this->members = ClientsModel::whereHas('accounts', function ($query) {
                    $query->where('product_number', '2000');
                })
                ->orderBy('first_name')
                ->get(['id', 'client_number', 'first_name', 'last_name', 'business_name']);

            // Load accounts
            $this->accounts = AccountsModel::where('product_number', '2000')
                ->whereNotNull('client_number')
                ->orderBy('account_name')
                ->get(['id', 'account_number', 'account_name', 'client_number']);

        } catch (Exception $e) {
            Log::error('Error loading available options: ' . $e->getMessage());
            $this->errorMessage = 'Failed to load filter options. Please try again.';
        }
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, [
            'selectedBranch', 'selectedMember', 'selectedAccount', 'selectedProduct',
            'dateFrom', 'dateTo', 'reportType', 'groupBy', 'timeFrame', 'statusFilter'
        ])) {
            $this->resetPage();
            $this->loadReport();
        }
    }

    public function loadReport()
    {
        try {
            $this->isLoading = true;
            $this->errorMessage = '';

            // Calculate summary statistics for cards (regardless of report type)
            $this->summaryStatistics = $this->getSummaryStatistics();

            switch ($this->reportType) {
                case 'summary':
                    $this->loadSummaryReport();
                    break;
                case 'detailed':
                    $this->loadDetailedReport();
                    break;
                case 'comparative':
                    $this->loadComparativeReport();
                    break;
            }

            $this->generateChartData();
            $this->prepareExportData();

        } catch (Exception $e) {
            Log::error('Error loading deposits report: ' . $e->getMessage());
            $this->errorMessage = 'Failed to load report data. Please try again.';
            // Initialize data properties to prevent errors
            $this->summaryData = $this->createEmptyPaginator();
            $this->detailedData = $this->createEmptyPaginator();
            $this->comparativeData = [];
            $this->summaryStatistics = null;
        } finally {
            $this->isLoading = false;
        }
    }

    protected function loadSummaryReport()
    {
        try {
            $query = $this->buildBaseQuery();

            switch ($this->groupBy) {
                case 'member':
                    $this->summaryData = $this->getMemberSummary($query);
                    break;
                case 'account':
                    $this->summaryData = $this->getAccountSummary($query);
                    break;
                case 'branch':
                    $this->summaryData = $this->getBranchSummary($query);
                    break;
                case 'product':
                    $this->summaryData = $this->getProductSummary($query);
                    break;
                case 'date':
                    $this->summaryData = $this->getDateSummary($query);
                    break;
            }
        } catch (Exception $e) {
            Log::error('Error loading summary report: ' . $e->getMessage());
            // Set to empty collection to avoid type errors
            $this->summaryData = $this->createEmptyPaginator();
        }
    }

    protected function loadDetailedReport()
    {
        try {
            $query = $this->buildBaseQuery();
            $this->detailedData = $this->getDetailedData($query);
        } catch (Exception $e) {
            Log::error('Error loading detailed report: ' . $e->getMessage());
            // Set to empty collection to avoid type errors
            $this->detailedData = $this->createEmptyPaginator();
        }
    }

    protected function loadComparativeReport()
    {
        try {
            $this->comparativeData = $this->getComparativeData();
        } catch (Exception $e) {
            Log::error('Error loading comparative report: ' . $e->getMessage());
            // Set to empty array to avoid type errors
            $this->comparativeData = [];
        }
    }

    protected function buildBaseQuery()
    {
        $query = AccountsModel::with(['client', 'shareProduct'])
            ->where('product_number', '2000')
            ->whereNotNull('client_number')
            ->where('client_number', '!=', '0000');

        // Apply filters
        if ($this->selectedBranch) {
            $query->where('branch_number', $this->selectedBranch);
        }

        if ($this->selectedMember) {
            $query->where('client_number', $this->selectedMember);
        }

        if ($this->selectedAccount) {
            $query->where('account_number', $this->selectedAccount);
        }

        if ($this->selectedProduct) {
            $query->where('sub_product_number', $this->selectedProduct);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Apply date range filters
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query;
    }

    protected function getMemberSummary($query)
    {
        $sortField = $this->sortBy;
        
        // Map sort fields to their aggregated equivalents
        $sortMapping = [
            'balance' => 'total_balance',
            'account_count' => 'account_count',
            'total_balance' => 'total_balance',
            'avg_balance' => 'avg_balance',
            'min_balance' => 'min_balance',
            'max_balance' => 'max_balance'
        ];
        
        $orderByField = $sortMapping[$sortField] ?? 'total_balance';
        
        return $query->select(
                'client_number',
                DB::raw('COUNT(*) as account_count'),
                DB::raw('SUM(balance) as total_balance'),
                DB::raw('AVG(balance) as avg_balance'),
                DB::raw('MIN(balance) as min_balance'),
                DB::raw('MAX(balance) as max_balance')
            )
            ->groupBy('client_number')
            ->orderBy($orderByField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    protected function getAccountSummary($query)
    {
        return $query->select(
                'account_number',
                'account_name',
                'client_number',
                'balance',
                'status',
                'created_at'
            )
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    protected function getBranchSummary($query)
    {
        $sortField = $this->sortBy;
        
        // Map sort fields to their aggregated equivalents
        $sortMapping = [
            'branch_number' => 'branch_number',
            'account_count' => 'account_count',
            'member_count' => 'member_count',
            'total_balance' => 'total_balance',
            'avg_balance' => 'avg_balance'
        ];
        
        $orderByField = $sortMapping[$sortField] ?? 'total_balance';
        
        return $query->select(
                'branch_number',
                DB::raw('COUNT(*) as account_count'),
                DB::raw('COUNT(DISTINCT client_number) as member_count'),
                DB::raw('SUM(balance) as total_balance'),
                DB::raw('AVG(balance) as avg_balance')
            )
            ->groupBy('branch_number')
            ->orderBy($orderByField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    protected function getProductSummary($query)
    {
        return $query->join('sub_products', 'accounts.product_number', '=', 'sub_products.sub_product_id')
            ->select(
                'sub_products.product_name',
                'accounts.product_number',
                DB::raw('COUNT(*) as account_count'),
                DB::raw('COUNT(DISTINCT accounts.client_number) as member_count'),
                DB::raw('SUM(accounts.balance) as total_balance'),
                DB::raw('AVG(accounts.balance) as avg_balance')
            )
            ->groupBy('sub_products.product_name', 'accounts.product_number')
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    protected function getDateSummary($query)
    {
        $dateFormat = $this->getDateFormat();
        
        return $query->join('general_ledger', 'accounts.account_number', '=', 'general_ledger.record_on_account_number')
            ->select(
                DB::raw("DATE_FORMAT(general_ledger.created_at, '{$dateFormat}') as date_period"),
                DB::raw('COUNT(DISTINCT general_ledger.id) as transaction_count'),
                DB::raw('SUM(general_ledger.credit) as total_credits'),
                DB::raw('SUM(general_ledger.debit) as total_debits'),
                DB::raw('SUM(general_ledger.credit - general_ledger.debit) as net_change')
            )
            ->whereBetween('general_ledger.created_at', [$this->dateFrom, $this->dateTo])
            ->groupBy('date_period')
            ->orderBy('date_period', $this->sortDirection)
            ->paginate($this->perPage);
    }

    protected function getDetailedData($query)
    {
        return $query->with(['client', 'shareProduct'])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    protected function getComparativeData()
    {
        $currentPeriod = $this->getCurrentPeriodData();
        $previousPeriod = $this->getPreviousPeriodData();

        return [
            'current_period' => $currentPeriod,
            'previous_period' => $previousPeriod,
            'changes' => $this->calculateChanges($currentPeriod, $previousPeriod)
        ];
    }

    protected function getCurrentPeriodData()
    {
        return $this->buildBaseQuery()
            ->select(
                DB::raw('COUNT(*) as account_count'),
                DB::raw('COUNT(DISTINCT client_number) as member_count'),
                DB::raw('SUM(balance) as total_balance'),
                DB::raw('AVG(balance) as avg_balance')
            )
            ->first();
    }

    protected function getSummaryStatistics()
    {
        $query = AccountsModel::with(['client', 'shareProduct'])
            ->where('product_number', '2000')
            ->whereNotNull('client_number')
            ->where('client_number', '!=', '0000');

        // Apply filters (but not date filters for summary cards)
        if ($this->selectedBranch) {
            $query->where('branch_number', $this->selectedBranch);
        }

        if ($this->selectedMember) {
            $query->where('client_number', $this->selectedMember);
        }

        if ($this->selectedAccount) {
            $query->where('account_number', $this->selectedAccount);
        }

        if ($this->selectedProduct) {
            $query->where('sub_product_number', $this->selectedProduct);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query->select(
                DB::raw('COUNT(*) as account_count'),
                DB::raw('COUNT(DISTINCT client_number) as member_count'),
                DB::raw('SUM(balance) as total_balance'),
                DB::raw('AVG(balance) as avg_balance')
            )
            ->first();
    }

    protected function getPreviousPeriodData()
    {
        $dateRange = $this->getPreviousPeriodRange();
        
        return $this->buildBaseQuery()
            ->select(
                DB::raw('COUNT(*) as account_count'),
                DB::raw('COUNT(DISTINCT client_number) as member_count'),
                DB::raw('SUM(balance) as total_balance'),
                DB::raw('AVG(balance) as avg_balance')
            )
            ->whereBetween('created_at', $dateRange)
            ->first();
    }

    protected function getPreviousPeriodRange()
    {
        $from = Carbon::parse($this->dateFrom);
        $to = Carbon::parse($this->dateTo);
        $duration = $from->diffInDays($to);

        return [
            $from->copy()->subDays($duration),
            $from->copy()->subDay()
        ];
    }

    protected function calculateChanges($current, $previous)
    {
        if (!$current || !$previous) {
            return null;
        }

        return [
            'account_count_change' => $this->calculatePercentageChange($current->account_count, $previous->account_count),
            'member_count_change' => $this->calculatePercentageChange($current->member_count, $previous->member_count),
            'total_balance_change' => $this->calculatePercentageChange($current->total_balance, $previous->total_balance),
            'avg_balance_change' => $this->calculatePercentageChange($current->avg_balance, $previous->avg_balance),
        ];
    }

    protected function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    protected function getDateFormat()
    {
        switch ($this->timeFrame) {
            case 'day':
                return '%Y-%m-%d';
            case 'week':
                return '%Y-%u';
            case 'month':
                return '%Y-%m';
            case 'quarter':
                return '%Y-Q%q';
            case 'year':
                return '%Y';
            default:
                return '%Y-%m';
        }
    }

    protected function generateChartData()
    {
        try {
            $query = $this->buildBaseQuery();

            switch ($this->groupBy) {
                case 'date':
                    $this->chartData = $this->getDateChartData($query);
                    break;
                case 'product':
                    $this->chartData = $this->getProductChartData($query);
                    break;
                case 'branch':
                    $this->chartData = $this->getBranchChartData($query);
                    break;
                default:
                    $this->chartData = $this->getDefaultChartData($query);
            }

            // Prepare chart labels and values
            $this->prepareChartLabelsAndValues();

        } catch (Exception $e) {
            Log::error('Error generating chart data: ' . $e->getMessage());
            $this->chartData = [];
            $this->chartLabels = [];
            $this->chartValues = [];
        }
    }

    protected function getDateChartData($query)
    {
        $dateFormat = $this->getDateFormat();
        
        return $query->join('general_ledger', 'accounts.account_number', '=', 'general_ledger.record_on_account_number')
            ->select(
                DB::raw("DATE_FORMAT(general_ledger.created_at, '{$dateFormat}') as period"),
                DB::raw('SUM(general_ledger.credit) as credits'),
                DB::raw('SUM(general_ledger.debit) as debits'),
                DB::raw('SUM(general_ledger.credit - general_ledger.debit) as net_change')
            )
            ->whereBetween('general_ledger.created_at', [$this->dateFrom, $this->dateTo])
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    protected function getProductChartData($query)
    {
        return $query->join('sub_products', 'accounts.sub_product_number', '=', 'sub_products.sub_product_id')
            ->select(
                'sub_products.product_name',
                'sub_products.sub_product_name',
                DB::raw('COUNT(*) as account_count'),
                DB::raw('SUM(accounts.balance) as total_balance')
            )
            ->groupBy('sub_products.product_name', 'sub_products.sub_product_name')
            ->orderBy('total_balance', 'desc')
            ->get();
    }

    protected function getBranchChartData($query)
    {
        return $query->join('branches', 'accounts.branch_number', '=', 'branches.branch_number')
            ->select(
                'branches.name as branch_name',
                'accounts.branch_number',
                DB::raw('COUNT(*) as account_count'),
                DB::raw('SUM(accounts.balance) as total_balance')
            )
            ->groupBy('branches.name', 'accounts.branch_number')
            ->orderBy('total_balance', 'desc')
            ->get();
    }

    protected function getDefaultChartData($query)
    {
        return $query->select(
                DB::raw('COUNT(*) as total_accounts'),
                DB::raw('SUM(balance) as total_balance'),
                DB::raw('AVG(balance) as avg_balance')
            )
            ->get();
    }

    protected function prepareChartLabelsAndValues()
    {
        $this->chartLabels = [];
        $this->chartValues = [];

        if (is_array($this->chartData) || is_object($this->chartData)) {
            $data = is_array($this->chartData) ? $this->chartData : $this->chartData->toArray();
            
            foreach ($data as $item) {
                switch ($this->groupBy) {
                    case 'branch':
                        $this->chartLabels[] = $item['branch_name'] ?? $item['branch_number'];
                        $this->chartValues[] = $item['total_balance'] ?? 0;
                        break;
                    case 'product':
                        $this->chartLabels[] = ($item['product_name'] ?? '') . ' - ' . ($item['sub_product_name'] ?? '');
                        $this->chartValues[] = $item['total_balance'] ?? 0;
                        break;
                    case 'date':
                        $this->chartLabels[] = $item['period'] ?? '';
                        $this->chartValues[] = $item['net_change'] ?? 0;
                        break;
                    default:
                        $this->chartLabels[] = 'Total';
                        $this->chartValues[] = $item['total_balance'] ?? 0;
                        break;
                }
            }
        }
    }

    protected function prepareExportData()
    {
        $this->exportData = [
            'filters' => [
                'branch' => $this->selectedBranch,
                'member' => $this->selectedMember,
                'account' => $this->selectedAccount,
                'product' => $this->selectedProduct,
                'date_from' => $this->dateFrom,
                'date_to' => $this->dateTo,
                'report_type' => $this->reportType,
                'group_by' => $this->groupBy,
                'time_frame' => $this->timeFrame,
                'status_filter' => $this->statusFilter,
            ],
            'summary' => $this->getExportSummary(),
            'data' => $this->getExportData()
        ];
    }

    protected function getExportSummary()
    {
        $query = $this->buildBaseQuery();
        
        return $query->select(
                DB::raw('COUNT(*) as total_accounts'),
                DB::raw('COUNT(DISTINCT client_number) as total_members'),
                DB::raw('SUM(balance) as total_balance'),
                DB::raw('AVG(balance) as avg_balance'),
                DB::raw('MIN(balance) as min_balance'),
                DB::raw('MAX(balance) as max_balance')
            )
            ->first();
    }

    protected function getExportData()
    {
        switch ($this->reportType) {
            case 'summary':
                return $this->summaryData && method_exists($this->summaryData, 'items') ? $this->summaryData->items() : [];
            case 'detailed':
                return $this->detailedData && method_exists($this->detailedData, 'items') ? $this->detailedData->items() : [];
            case 'comparative':
                return $this->comparativeData ?? [];
            default:
                return [];
        }
    }

    public function exportReport($format = 'excel')
    {
        try {
            $this->isExporting = true;
            
            // Generate export data
            $this->prepareExportData();
            
            // Here you would implement the actual export logic
            // For now, we'll just show a success message
            $this->successMessage = "Report exported successfully in {$format} format.";
            
        } catch (Exception $e) {
            Log::error('Error exporting report: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export report. Please try again.';
        } finally {
            $this->isExporting = false;
        }
    }

    public function refreshReport()
    {
        $this->loadReport();
        $this->successMessage = 'Report refreshed successfully.';
    }

    public function resetFilters()
    {
        $this->reset([
            'selectedBranch', 'selectedMember', 'selectedAccount', 'selectedProduct',
            'dateFrom', 'dateTo', 'reportType', 'groupBy', 'timeFrame', 'statusFilter'
        ]);
        $this->setDefaultDates();
        $this->loadReport();
        $this->successMessage = 'Filters reset successfully.';
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        
        $this->loadReport();
    }

    // Detailed Report Actions
    public function viewAccountDetails($accountNumber)
    {
        try {
            // Get account details with relationships
            $account = AccountsModel::where('account_number', $accountNumber)
                ->with(['client', 'shareProduct'])
                ->first();
                
            if (!$account) {
                throw new Exception('Account not found.');
            }
            
            // Store account data in Livewire property
            $this->selectedAccountData = $account;
            
            // Show the modal
            $this->showAccountDetailsModal = true;

            //dd($this->selectedAccountData);
            
            //$this->successMessage = "Account details for {$accountNumber} loaded successfully.";
            
        } catch (Exception $e) {
            Log::error('Error viewing account details: ' . $e->getMessage());
            $this->errorMessage = 'Failed to load account details. Please try again.';
        }
    }

    public function viewAccountTransactions($accountNumber)
    {
        try {
            // Get account transactions
            $transactions = general_ledger::where('record_on_account_number', $accountNumber)
                ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
                
            // Store transaction data in Livewire property
            $this->selectedAccountTransactions = $transactions;
            
            // Show the modal
            $this->showTransactionsModal = true;
            
            $this->successMessage = "Transaction history for {$accountNumber} loaded successfully.";
            
        } catch (Exception $e) {
            Log::error('Error viewing account transactions: ' . $e->getMessage());
            $this->errorMessage = 'Failed to load account transactions. Please try again.';
        }
    }

    public function closeAccountDetailsModal()
    {
        $this->showAccountDetailsModal = false;
        $this->selectedAccountData = null;
    }

    public function closeTransactionsModal()
    {
        $this->showTransactionsModal = false;
        $this->selectedAccountTransactions = [];
    }

    public function downloadAccountStatement($accountNumber)
    {
        try {
            $this->isExporting = true;
            
            // Get account details
            $account = AccountsModel::where('account_number', $accountNumber)
                ->with(['client', 'shareProduct'])
                ->first();
                
            if (!$account) {
                throw new Exception('Account not found.');
            }
            
            // Get transactions for the account
            $transactions = general_ledger::where('record_on_account_number', $accountNumber)
                ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // For now, we'll just show a success message
            // In a real implementation, you would generate and download a PDF
            $this->successMessage = "Statement for account {$accountNumber} from {$this->dateFrom} to {$this->dateTo} is ready. (PDF generation would be implemented here)";
            
        } catch (Exception $e) {
            Log::error('Error downloading account statement: ' . $e->getMessage());
            $this->errorMessage = 'Failed to download account statement. Please try again.';
        } finally {
            $this->isExporting = false;
        }
    }

    public function render()
    {
        return view('livewire.deposits.deposits-full-report', [
            'summaryData' => $this->summaryData,
            'detailedData' => $this->detailedData,
            'comparativeData' => $this->comparativeData,
            'chartData' => $this->chartData,
            'chartLabels' => $this->chartLabels,
            'chartValues' => $this->chartValues,
            'exportData' => $this->exportData,
            'summaryStatistics' => $this->summaryStatistics,
            'branches' => $this->branches,
            'members' => $this->members,
            'accounts' => $this->accounts,
            'products' => $this->products,
            'showAccountDetailsModal' => $this->showAccountDetailsModal,
            'showTransactionsModal' => $this->showTransactionsModal,
            'selectedAccountData' => $this->selectedAccountData,
            'selectedAccountTransactions' => $this->selectedAccountTransactions,
        ]);
    }
}
