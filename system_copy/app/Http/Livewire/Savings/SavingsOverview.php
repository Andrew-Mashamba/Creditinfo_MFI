<?php

namespace App\Http\Livewire\Savings;

use Livewire\Component;
use App\Models\Account;
use App\Models\general_ledger;
use App\Models\sub_products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;
use App\Models\AccountsModel;
use App\Models\ClientsModel;
use App\Traits\Livewire\WithModulePermissions;

class SavingsOverview extends Component
{
    use WithModulePermissions;
    // Properties for data
    public $recentTransactions = [];
    public $topSavers = [];
    public $savingsByProduct = [];
    public $monthlySavings = [];

    // Loading States
    public $isLoading = false;
    public $isProcessing = false;

    // Messages
    public $errorMessage = '';
    public $successMessage = '';

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

            // Load recent transactions
            $this->recentTransactions = general_ledger::with(['account.client'])
                ->whereHas('account', function ($query) {
                    $query->where('product_number', 2000);
                })
                ->latest()
                ->take(5)
                ->get();

            // Load top savers
            $this->topSavers = AccountsModel::with('client')
                ->whereNotNull('client_number')
                ->where('product_number', 2000)
                ->where('status', 'ACTIVE')
                ->where('client_number', '!=', '0000')
                ->where('balance', '>=', 0)
                ->orderBy('balance', 'desc')
                ->take(5)
                ->get();

            // Load savings by product
            $this->savingsByProduct = DB::table('sub_products')
                ->where('product_type', 2000)
                ->where('status', 'ACTIVE')
                ->select('product_name', DB::raw('(SELECT SUM(CAST(balance AS DECIMAL(15,2))) FROM accounts WHERE product_number = \'2000\') as total_balance'))
                ->get();

            // Load monthly savings data - Fixed: Use credit instead of amount and EXTRACT for PostgreSQL
            $this->monthlySavings = general_ledger::whereHas('account', function ($query) {
                    $query->where('product_number', 2000);
                })
                ->where('transaction_type', 'credit')
                ->whereYear('created_at', Carbon::now()->year)
                ->selectRaw('EXTRACT(MONTH FROM created_at) as month, SUM(credit) as total')
                ->groupBy('month')
                ->get();

        } catch (Exception $e) {
            Log::error('Error loading savings overview data: ' . $e->getMessage());
            $this->errorMessage = 'Failed to load savings overview data. Please try again.';
            // Initialize empty collections on error
            $this->recentTransactions = collect([]);
            $this->topSavers = collect([]);
            $this->savingsByProduct = collect([]);
            $this->monthlySavings = collect([]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function refreshData()
    {
        try {
            $this->isProcessing = true;
            
            // Clear cache
            Cache::forget('recent_savings_transactions');
            Cache::forget('top_savers');
            Cache::forget('savings_by_product');
            Cache::forget('monthly_savings');
            
            // Reload data
            $this->loadData();
            
            $this->successMessage = 'Data refreshed successfully.';
        } catch (Exception $e) {
            Log::error('Error refreshing savings overview data: ' . $e->getMessage());
            $this->errorMessage = 'Failed to refresh data. Please try again.';
        } finally {
            $this->isProcessing = false;
        }
    }

    public function render()
    {
        return view('livewire.savings.savings-overview', $this->permissions);
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
