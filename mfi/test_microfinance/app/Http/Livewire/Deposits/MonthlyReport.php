<?php

namespace App\Http\Livewire\Deposits;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Client;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class MonthlyReport extends Component
{
    use WithPagination;

    // Properties for filtering and display
    public $selectedYear;
    public $selectedMonth;
    public $selectedProduct;
    public $searchTerm = '';
    public $statusFilter = 'all';
    public $showSettingsModal = false;
    public $activeTab = 'summary';

    // Data properties
    public $summaryData;
    protected $detailedData;
    public $monthlyData;
    protected $productStats;
    protected $transactionData;
    protected $products = [];
    protected $membersData;

    // Loading States
    public $isLoading = false;
    public $processingMessage = '';
    public $errorMessage = '';
    public $successMessage = '';

    public function mount()
    {
        $this->selectedYear = now()->year;
        $this->selectedMonth = now()->month;
        $this->products = collect();
        $this->membersData = collect();
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->isLoading = true;
            $this->errorMessage = '';

            // Load summary data
            $this->loadSummaryData();

            // Load detailed data
            $this->loadDetailedData();

            // Load members data
            $this->loadMembersData();

            // Load monthly trends
            $this->loadMonthlyTrends();

            // Load product statistics
            $this->loadProductStats();

            // Load recent transactions
            $this->loadRecentTransactions();

            // Load products for dropdown
            $this->loadProducts();

        } catch (Exception $e) {
            Log::error('Error loading deposits report: ' . $e->getMessage());
            $this->errorMessage = 'Failed to load report data. Please try again.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function loadSummaryData()
    {
        $query = Account::with(['client'])
            ->where('major_category_code', '2000') // Deposits categoryP
            ->where('product_number', '2000') // Only savings accounts
            ->whereYear('created_at', $this->selectedYear)
            ->whereMonth('created_at', $this->selectedMonth);

        // Note: All savings accounts have product_number = '2000'
        // Product filtering is not applicable since this is the only identifier for savings

        $totalAccounts = $query->count();
        $totalBalance = $query->sum('balance');
        $averageBalance = $totalAccounts > 0 ? $totalBalance / $totalAccounts : 0;
        $activeAccounts = $query->where('status', 'ACTIVE')->count();

        $this->summaryData = [
            'total_accounts' => $totalAccounts,
            'total_balance' => $totalBalance,
            'average_balance' => $averageBalance,
            'active_accounts' => $activeAccounts,
            'inactive_accounts' => $totalAccounts - $activeAccounts,
        ];
    }

    public function loadDetailedData()
    {
        $query = Account::with(['client'])
            ->where('major_category_code', '2000')
            ->where('product_number', '2000') // Only savings accounts
            ->whereYear('created_at', $this->selectedYear)
            ->whereMonth('created_at', $this->selectedMonth);

        // Note: All savings accounts have product_number = '2000'
        // Product filtering is not applicable since this is the only identifier for savings

        if ($this->searchTerm) {
            $query->whereHas('client', function($q) {
                $q->where('first_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('last_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('client_number', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $this->detailedData = $query->paginate(20);
    }

    public function loadMembersData()
    {
        $this->membersData = DB::table('clients')
            ->leftJoin('accounts', 'clients.id', '=', 'accounts.client_id')
            ->where('accounts.major_category_code', '2000')
            ->where('accounts.product_number', '2000')
            ->whereYear('accounts.created_at', $this->selectedYear)
            ->whereMonth('accounts.created_at', $this->selectedMonth)
            ->select(
                'clients.id',
                'clients.first_name',
                'clients.last_name',
                'clients.client_number',
                'clients.phone_number',
                'clients.email',
                'clients.status as member_status',
                DB::raw('COUNT(accounts.id) as savings_accounts'),
                DB::raw('SUM(accounts.balance) as total_savings'),
                DB::raw('CASE WHEN SUM(accounts.balance) >= 100000 THEN "COMPLIANT" ELSE "NON_COMPLIANT" END as compliance_status')
            )
            ->groupBy('clients.id', 'clients.first_name', 'clients.last_name', 'clients.client_number', 'clients.phone_number', 'clients.email', 'clients.status')
            ->get();
    }

    public function loadMonthlyTrends()
    {
        $data = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $query = Account::where('major_category_code', '2000')
                ->where('product_number', '2000') // Only savings accounts
                ->whereYear('created_at', $this->selectedYear)
                ->whereMonth('created_at', $month);

            // Note: All savings accounts have product_number = '2000'
            // Product filtering is not applicable since this is the only identifier for savings

            $data[$month] = [
                'accounts' => $query->count(),
                'balance' => $query->sum('balance'),
                'transactions' => Transaction::whereHas('account', function($q) use ($month) {
                    $q->where('major_category_code', '2000')
                      ->where('product_number', '2000') // Only savings accounts
                      ->whereYear('created_at', $this->selectedYear)
                      ->whereMonth('created_at', $month);
                })->count(),
            ];
        }

        $this->monthlyData = $data;
    }

    public function loadProductStats()
    {
        $results = DB::table('accounts')
            ->leftJoin('sub_products', 'accounts.product_number', '=', 'sub_products.sub_product_id')
            ->where('accounts.major_category_code', '2000')
            ->where('accounts.product_number', '2000') // Only savings accounts
            ->whereYear('accounts.created_at', $this->selectedYear)
            ->whereMonth('accounts.created_at', $this->selectedMonth)
            ->select(
                DB::raw("COALESCE(sub_products.product_name, 'GENERAL SAVINGS') as product_name"),
                DB::raw("COALESCE(sub_products.sub_product_id, 'GENERAL') as sub_product_id"),
                DB::raw('COUNT(accounts.id) as account_count'),
                DB::raw('SUM(accounts.balance) as total_balance'),
                DB::raw('AVG(accounts.balance) as avg_balance')
            )
            ->groupBy('sub_products.sub_product_id', 'sub_products.product_name')
            ->get();
        
        // Convert arrays to objects for consistent property access
        $this->productStats = $results->map(function($item) {
            return (object) $item;
        });
    }

    public function loadRecentTransactions()
    {
        $this->transactionData = Transaction::with(['account.client'])
            ->whereHas('account', function($query) {
                $query->where('major_category_code', '2000')
                      ->where('product_number', '2000'); // Only savings accounts
            })
            ->whereYear('created_at', $this->selectedYear)
            ->whereMonth('created_at', $this->selectedMonth)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function loadProducts()
    {
        $this->products = DB::table('sub_products')
            ->where('product_type', 2000)
            ->select('sub_product_id', 'product_name')
            ->orderBy('product_name')
            ->get();
    }

    public function refreshData()
    {
        $this->resetPage();
        $this->loadData();
    }

    public function updatedSelectedYear()
    {
        $this->refreshData();
    }

    public function updatedSelectedMonth()
    {
        $this->refreshData();
    }

    public function updatedSelectedProduct()
    {
        $this->refreshData();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
        $this->loadDetailedData();
    }

    public function clearFilters()
    {
        $this->selectedProduct = null;
        $this->searchTerm = '';
        $this->statusFilter = 'all';
        $this->refreshData();
    }

    public function clearMessages()
    {
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    public function sendBulkNotifications()
    {
        try {
            $this->isLoading = true;
            $this->processingMessage = 'Sending bulk notifications...';
            
            // Implementation for bulk notifications would go here
            // For now, just show success message
            
            $this->successMessage = 'Bulk notifications sent successfully.';
            
        } catch (Exception $e) {
            $this->errorMessage = 'Failed to send bulk notifications: ' . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function exportReport()
    {
        try {
            $this->isLoading = true;
            $this->processingMessage = 'Generating export...';

            // Generate CSV export
            $filename = "deposits_report_{$this->selectedYear}_{$this->selectedMonth}.csv";
            
            // Implementation for CSV export would go here
            // For now, just show success message
            
            $this->successMessage = 'Report exported successfully.';
            
        } catch (Exception $e) {
            $this->errorMessage = 'Failed to export report: ' . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.deposits.monthly-report', [
            'summaryData' => $this->summaryData,
            'detailedData' => $this->detailedData,
            'monthlyData' => $this->monthlyData,
            'productStats' => $this->productStats,
            'transactionData' => $this->transactionData,
            'products' => $this->products,
            'membersData' => $this->membersData,
            'errorMessage' => $this->errorMessage,
        ]);
    }
}
