<?php

namespace App\Http\Livewire\Deposits;

use Livewire\Component;
use App\Models\AccountsModel;
use App\Models\general_ledger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;
use App\Traits\Livewire\WithModulePermissions;

class DepositsOverview extends Component
{
    use WithModulePermissions;
    public $accounts = [];
    public $recentTransactions = [];
    public $monthlyDeposits = [];
    public $topDepositors = [];
    public $depositsByProduct = [];
    
    // Loading and error states
    public $isLoading = false;
    public $errorMessage = '';
    public $successMessage = '';
    
    // Business logic properties
    protected $validMajorCategoryCodes = [1000, 2000, 3000, 4000]; // SACCO account categories
    protected $depositsCategoryCode = 2000;
    protected $cacheDuration = 300; // 5 minutes

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->isLoading = true;
            $this->errorMessage = '';

            // Validate account category code
            $this->validateAccountCategoryCode();

            // Load deposits accounts with proper business logic
            $this->loadDepositsAccounts();

            // Load recent transactions with validation
            $this->loadRecentTransactions();

            // Load monthly deposits with proper aggregation
            $this->loadMonthlyDeposits();

            // Load top depositors with business rules
            $this->loadTopDepositors();

            // Load deposits by product with validation
            $this->loadDepositsByProduct();

        } catch (Exception $e) {
            Log::error('Error loading deposits overview data: ' . $e->getMessage());
            $this->errorMessage = 'Failed to load deposits data. Please try again.';
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Validate account category code for SACCO compliance
     */
    private function validateAccountCategoryCode()
    {
        if (!in_array($this->depositsCategoryCode, $this->validMajorCategoryCodes)) {
            throw new Exception('Invalid account category code for deposits. Please contact administrator.');
        }
    }

    /**
     * Load deposits accounts with proper business logic
     */
    private function loadDepositsAccounts()
    {
        $cacheKey = "deposits_overview_accounts_" . date('Y-m-d');

        $this->accounts = Cache::remember($cacheKey, $this->cacheDuration, function () {
            return AccountsModel::whereNotNull('client_number')
                ->where('major_category_code', $this->depositsCategoryCode)
                ->where('client_number', '!=', '0000')
                ->where('status', 'ACTIVE')
                ->where('balance', '>', 0) // Only accounts with positive balance
                ->orderBy('balance', 'desc')
                ->take(10)
                ->get();
        });
    }

    /**
     * Load recent transactions with proper validation
     */
    private function loadRecentTransactions()
    {
        $cacheKey = "deposits_recent_transactions_" . date('Y-m-d-H');

        $this->recentTransactions = Cache::remember($cacheKey, $this->cacheDuration, function () {
            return general_ledger::with(['account.client'])
                ->whereHas('account', function ($query) {
                    $query->where('major_category_code', $this->depositsCategoryCode)
                          ->where('status', 'ACTIVE');
                })
                ->where('credit', '>', 0) // Only credit transactions (deposits)
                ->where('created_at', '>=', Carbon::now()->subDays(7)) // Last 7 days
                ->latest()
                ->take(5)
                ->get();
        });
    }

    /**
     * Load monthly deposits with proper aggregation
     */
    private function loadMonthlyDeposits()
    {
        $cacheKey = "deposits_monthly_data_" . Carbon::now()->year;

        $this->monthlyDeposits = Cache::remember($cacheKey, $this->cacheDuration, function () {
            return general_ledger::whereHas('account', function ($query) {
                    $query->where('major_category_code', $this->depositsCategoryCode)
                          ->where('status', 'ACTIVE');
                })
                ->whereYear('created_at', Carbon::now()->year)
                ->where('credit', '>', 0) // Only credit transactions
                ->selectRaw('EXTRACT(MONTH FROM created_at) as month, SUM(credit) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        });
    }

    /**
     * Load top depositors with business rules
     */
    private function loadTopDepositors()
    {
        $cacheKey = "deposits_top_depositors_" . date('Y-m-d');

        $this->topDepositors = Cache::remember($cacheKey, $this->cacheDuration, function () {
            return AccountsModel::with('client')
                ->whereNotNull('client_number')
                ->where('major_category_code', $this->depositsCategoryCode)
                ->where('status', 'ACTIVE')
                ->where('client_number', '!=', '0000')
                ->where('balance', '>', 0) // Only positive balances
                ->orderBy('balance', 'desc')
                ->take(5)
                ->get();
        });
    }

    /**
     * Load deposits by product with validation
     */
    private function loadDepositsByProduct()
    {
        $cacheKey = "deposits_by_product_" . date('Y-m-d');

        $this->depositsByProduct = Cache::remember($cacheKey, $this->cacheDuration, function () {
            return DB::table('accounts')
                ->join('sub_products', 'accounts.product_number', '=', 'sub_products.sub_product_id')
                ->where('accounts.major_category_code', $this->depositsCategoryCode)
                ->where('accounts.status', 'ACTIVE')
                ->where('accounts.balance', '>', 0)
                ->select(
                    'sub_products.product_name', 
                    DB::raw('SUM(accounts.balance) as total_balance'),
                    DB::raw('COUNT(accounts.id) as account_count'),
                    DB::raw('AVG(accounts.balance) as avg_balance')
                )
                ->groupBy('sub_products.product_name')
                ->orderBy('total_balance', 'desc')
                ->get();
        });
    }

    /**
     * Refresh data with proper error handling
     */
    public function refreshData()
    {
        try {
            // Clear cache for fresh data
            $this->clearCache();
            
            // Reload data
            $this->loadData();
            
            $this->successMessage = 'Data refreshed successfully.';
            
        } catch (Exception $e) {
            Log::error('Error refreshing deposits data: ' . $e->getMessage());
            $this->errorMessage = 'Failed to refresh data. Please try again.';
        }
    }

    /**
     * Clear cache for data refresh
     */
    private function clearCache()
    {
        $cacheKeys = [
            "deposits_overview_accounts_" . date('Y-m-d'),
            "deposits_recent_transactions_" . date('Y-m-d-H'),
            "deposits_monthly_data_" . Carbon::now()->year,
            "deposits_top_depositors_" . date('Y-m-d'),
            "deposits_by_product_" . date('Y-m-d')
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get summary statistics for dashboard
     */
    public function getSummaryStatistics()
    {
        try {
            $cacheKey = "deposits_summary_stats_" . date('Y-m-d');

            return Cache::remember($cacheKey, $this->cacheDuration, function () {
                $totalAccounts = AccountsModel::where('major_category_code', $this->depositsCategoryCode)
                    ->where('status', 'ACTIVE')
                    ->where('balance', '>', 0)
                    ->count();

                $totalBalance = AccountsModel::where('major_category_code', $this->depositsCategoryCode)
                    ->where('status', 'ACTIVE')
                    ->where('balance', '>', 0)
                    ->sum('balance');

                $activeMembers = AccountsModel::where('major_category_code', $this->depositsCategoryCode)
                    ->where('status', 'ACTIVE')
                    ->where('balance', '>', 0)
                    ->distinct('client_number')
                    ->count('client_number');

                $avgBalance = $totalAccounts > 0 ? $totalBalance / $totalAccounts : 0;

                return [
                    'total_accounts' => $totalAccounts,
                    'total_balance' => $totalBalance,
                    'active_members' => $activeMembers,
                    'average_balance' => $avgBalance
                ];
            });

        } catch (Exception $e) {
            Log::error('Error getting summary statistics: ' . $e->getMessage());
            return [
                'total_accounts' => 0,
                'total_balance' => 0,
                'active_members' => 0,
                'average_balance' => 0
            ];
        }
    }

    /**
     * Clear success/error messages
     */
    public function clearMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        $summaryStats = $this->getSummaryStatistics();
        
        return view('livewire.deposits.deposits-overview', array_merge(
            $this->permissions,
            [
                'summaryStats' => $summaryStats,
                'permissions' => $this->permissions
            ]
        ));
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
