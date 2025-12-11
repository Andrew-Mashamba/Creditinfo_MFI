<?php

namespace App\Http\Livewire\Savings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AccountsModel;
use App\Models\ClientsModel;
use App\Models\sub_products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class InterestOnSavings extends Component
{
    use WithPagination;

    // Filter Properties
    public $searchTerm = '';
    public $selectedProduct = '';
    public $interestRate = 3.00; // Default 3% annual interest
    public $perPage = 20;

    // Data Properties
    public $savingsAccounts = [];
    public $summaryData = [];
    public $productsData = [];

    // Loading States
    public $isLoading = false;
    public $errorMessage = '';
    public $successMessage = '';

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        // Load default interest rate from config or settings
        $this->loadInterestRate();
        $this->loadData();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['searchTerm', 'selectedProduct', 'interestRate'])) {
            $this->resetPage();
            $this->loadData();
        }
    }

    public function loadInterestRate()
    {
        try {
            // Try to get interest rate from sub_products for savings (product_type = 2000)
            $savingsProduct = DB::table('sub_products')
                ->where('product_type', 2000)
                ->where('status', 'ACTIVE')
                ->first();

            if ($savingsProduct && isset($savingsProduct->interest_value)) {
                $this->interestRate = $savingsProduct->interest_value;
            } else {
                // Fallback to config or default
                $this->interestRate = config('interest.savings_rate', 3.00);
            }
        } catch (Exception $e) {
            Log::error('Error loading interest rate: ' . $e->getMessage());
            $this->interestRate = 3.00; // Default fallback
        }
    }

    public function loadData()
    {
        try {
            $this->isLoading = true;
            $this->errorMessage = '';

            $this->loadSavingsAccounts();
            $this->loadSummaryData();
            $this->loadProductsData();

        } catch (Exception $e) {
            Log::error('Error loading interest on savings data: ' . $e->getMessage());
            $this->errorMessage = 'Failed to load interest data. Please try again.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function loadSavingsAccounts()
    {
        try {
            $query = DB::table('accounts')
                ->leftJoin('clients', 'accounts.client_number', '=', 'clients.client_number')
                ->where('accounts.product_number', 2000) // Savings accounts
                ->where('accounts.status', 'ACTIVE')
                ->where('accounts.client_number', '!=', '0000')
                ->select(
                    'accounts.id',
                    'accounts.account_number',
                    'accounts.account_name',
                    'accounts.balance',
                    'accounts.created_at',
                    'accounts.updated_at',
                    'clients.first_name',
                    'clients.last_name',
                    'clients.client_number'
                );

            if ($this->selectedProduct) {
                $query->where('accounts.sub_product_number', $this->selectedProduct);
            }

            if ($this->searchTerm) {
                $query->where(function($q) {
                    $q->where('clients.first_name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('clients.last_name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('clients.client_number', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('accounts.account_number', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('accounts.account_name', 'like', '%' . $this->searchTerm . '%');
                });
            }

            $accounts = $query->orderBy('accounts.balance', 'desc')->get();

            $this->savingsAccounts = collect();

            foreach ($accounts as $account) {
                // Calculate accrued interest
                $interestData = $this->calculateAccruedInterest($account);

                $accountArray = [
                    'account_number' => $account->account_number ?? '',
                    'account_name' => $account->account_name ?? '',
                    'balance' => $account->balance ?? 0,
                    'first_name' => $account->first_name ?? '',
                    'last_name' => $account->last_name ?? '',
                    'client_number' => $account->client_number ?? '',
                    'member_name' => trim(($account->first_name ?? '') . ' ' . ($account->last_name ?? '')),
                    'created_at' => $account->created_at,
                    'updated_at' => $account->updated_at,
                    'days_accrued' => $interestData['days_accrued'],
                    'daily_interest' => $interestData['daily_interest'],
                    'accrued_interest' => $interestData['accrued_interest'],
                    'annual_interest' => $interestData['annual_interest'],
                ];

                $this->savingsAccounts->push($accountArray);
            }

        } catch (Exception $e) {
            Log::error('Error loading savings accounts: ' . $e->getMessage());
            $this->savingsAccounts = collect();
        }
    }

    protected function calculateAccruedInterest($account)
    {
        // Get accrued interest from the interest_accruals table - filter by SAVINGS product_type
        $accruals = DB::table('interest_accruals')
            ->where('account_number', $account->account_number)
            ->where('status', 'ACCRUED')
            ->where(function($query) {
                $query->where('product_type', 'SAVINGS')
                      ->orWhereNull('product_type'); // For backwards compatibility with old records
            })
            ->get();

        // Get the most recent accrual for daily rate and current balance
        $latestAccrual = DB::table('interest_accruals')
            ->where('account_number', $account->account_number)
            ->where('status', 'ACCRUED')
            ->where(function($query) {
                $query->where('product_type', 'SAVINGS')
                      ->orWhereNull('product_type'); // For backwards compatibility with old records
            })
            ->orderBy('accrual_date', 'desc')
            ->first();

        // Calculate totals from accruals
        $totalAccruedInterest = $accruals->sum('interest_amount');
        $daysAccrued = $accruals->count();

        // Get daily interest and rate from most recent accrual, or calculate if no accruals exist
        if ($latestAccrual) {
            $dailyInterest = $latestAccrual->interest_amount;
            $annualRate = $latestAccrual->annual_rate;
        } else {
            // Fallback to calculation if no accruals exist yet
            $annualRate = $this->interestRate;
            $balance = $account->balance ?? 0;
            $dailyRate = $annualRate / 365 / 100;
            $dailyInterest = $balance * $dailyRate;
        }

        // Calculate projected annual interest based on current balance and rate
        $balance = $account->balance ?? 0;
        $annualInterest = $balance * ($annualRate / 100);

        return [
            'days_accrued' => $daysAccrued,
            'daily_interest' => round($dailyInterest, 2),
            'accrued_interest' => round($totalAccruedInterest, 2),
            'annual_interest' => round($annualInterest, 2),
        ];
    }

    public function loadSummaryData()
    {
        $totalAccounts = $this->savingsAccounts->count();
        $totalBalance = $this->savingsAccounts->sum('balance');
        $totalAccruedInterest = $this->savingsAccounts->sum('accrued_interest');
        $totalDailyInterest = $this->savingsAccounts->sum('daily_interest');
        $totalAnnualInterest = $this->savingsAccounts->sum('annual_interest');

        $this->summaryData = [
            'total_accounts' => $totalAccounts,
            'total_balance' => $totalBalance,
            'total_accrued_interest' => $totalAccruedInterest,
            'total_daily_interest' => $totalDailyInterest,
            'total_annual_interest' => $totalAnnualInterest,
            'interest_rate' => $this->interestRate,
            'average_balance' => $totalAccounts > 0 ? $totalBalance / $totalAccounts : 0,
        ];
    }

    public function loadProductsData()
    {
        try {
            $this->productsData = DB::table('sub_products')
                ->where('product_type', 2000)
                ->select('sub_product_id', 'product_name', 'interest_value')
                ->get();
        } catch (Exception $e) {
            Log::error('Error loading products: ' . $e->getMessage());
            $this->productsData = collect();
        }
    }

    public function refreshData()
    {
        $this->loadData();
        $this->successMessage = 'Data refreshed successfully.';
    }

    public function exportToExcel()
    {
        try {
            $this->isLoading = true;
            $fileName = 'interest_on_savings_' . now()->format('Y-m-d') . '.xlsx';

            // You would implement Excel export here
            $this->successMessage = 'Export functionality coming soon.';

        } catch (Exception $e) {
            Log::error('Error exporting data: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export data.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function clearMessages()
    {
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    public function render()
    {
        return view('livewire.savings.interest-on-savings');
    }
}
