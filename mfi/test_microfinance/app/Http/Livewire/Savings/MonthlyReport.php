<?php

namespace App\Http\Livewire\Savings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Models\ClientsModel;
use App\Models\sub_products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class MonthlyReport extends Component
{
    use WithPagination;

    // Filter Properties
    public $selectedMonth;
    public $selectedYear;
    public $selectedProduct = '';
    public $searchTerm = '';
    public $activeTab = 'summary'; // summary, accounts, members, notifications

    // Data Properties
    public $summaryData = [];
    public $accountsData;
    public $membersData;
    public $productsData;
    public $nonCompliantMembers;
    public $monthlyTotals;

    // Loading States
    public $isLoading = false;
    public $errorMessage = '';
    public $successMessage = '';

    // Notification Properties
    public $showNotificationModal = false;
    public $selectedMemberForNotification = null;
    public $notificationMessage = '';

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->selectedMonth = Carbon::now()->month;
        $this->selectedYear = Carbon::now()->year;
        $this->productsData = collect(); // Initialize as empty collection
        $this->accountsData = collect(); // Initialize as empty collection
        $this->membersData = collect(); // Initialize as empty collection
        $this->nonCompliantMembers = collect(); // Initialize as empty collection
        $this->monthlyTotals = collect(); // Initialize as empty collection
        $this->loadData();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['selectedMonth', 'selectedYear', 'selectedProduct', 'searchTerm'])) {
            $this->loadData();
        }
    }

    public function loadData()
    {
        try {
            $this->isLoading = true;
            $this->errorMessage = '';

            // Load summary data
            $this->loadSummaryData();
            
            // Load accounts data
            $this->loadAccountsData();
            
            // Load members data
            $this->loadMembersData();
            
            // Load products data
            $this->loadProductsData();
            
            // Ensure productsData is always a collection
            if (!isset($this->productsData) || !is_object($this->productsData) || !method_exists($this->productsData, 'count')) {
                $this->productsData = collect();
            }
            
            // Load non-compliant members
            $this->loadNonCompliantMembers();
            
            // Load monthly totals
            $this->loadMonthlyTotals();

        } catch (Exception $e) {
            Log::error('Error loading monthly report data: ' . $e->getMessage());
            $this->errorMessage = 'Failed to load monthly report data. Please try again.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function loadSummaryData()
    {
        // Total savings accounts (only product_number = 2000)
        $totalAccounts = DB::table('accounts')
            ->where('major_category_code', '2000')
            ->where('product_number', '2000')
            ->count();

        // Active savings accounts
        $activeAccounts = DB::table('accounts')
            ->where('major_category_code', '2000')
            ->where('product_number', '2000')
            ->where('status', 'ACTIVE')
            ->count();

        // Total members
        $totalMembers = DB::table('clients')->count();

        // Members with savings
        $membersWithSavings = DB::table('accounts')
            ->where('major_category_code', '2000')
            ->where('product_number', '2000')
            ->where('client_number', '!=', '0000')
            ->where('balance', '>', 0)
            ->distinct('client_number')
            ->count();

        // Total savings balance
        $totalBalance = DB::table('accounts')
            ->where('major_category_code', '2000')
            ->where('product_number', '2000')
            ->sum('balance');

        // Monthly transactions
        $monthlyTransactions = DB::table('general_ledger')
            ->where('major_category_code', '2000')
            ->whereYear('created_at', $this->selectedYear)
            ->whereMonth('created_at', $this->selectedMonth);

        $totalDeposits = $monthlyTransactions->sum('credit');
        $totalWithdrawals = $monthlyTransactions->sum('debit');
        $transactionCount = $monthlyTransactions->count();

        $this->summaryData = [
            'total_accounts' => $totalAccounts,
            'active_accounts' => $activeAccounts,
            'total_members' => $totalMembers,
            'members_with_savings' => $membersWithSavings,
            'members_without_savings' => $totalMembers - $membersWithSavings,
            'total_balance' => $totalBalance,
            'monthly_deposits' => $totalDeposits,
            'monthly_withdrawals' => $totalWithdrawals,
            'monthly_net_change' => $totalDeposits - $totalWithdrawals,
            'transaction_count' => $transactionCount,
            'compliance_rate' => $totalMembers > 0 ? round(($membersWithSavings / $totalMembers) * 100, 1) : 0
        ];
    }

    public function loadAccountsData()
    {
        try {
            $query = DB::table('accounts')
                ->join('clients', 'accounts.client_number', '=', 'clients.client_number')
                ->where('accounts.major_category_code', '2000')
                ->where('accounts.product_number', '2000') // Only savings products
                ->where('accounts.client_number', '!=', '0000')
                ->select(
                    'accounts.account_number',
                    'accounts.balance',
                    'accounts.status',
                    'accounts.created_at',
                    'clients.first_name',
                    'clients.last_name',
                    'clients.client_number',
                    'clients.phone_number',
                    DB::raw("'SAVINGS' as product_name") // All savings accounts show as 'SAVINGS'
                );

            if ($this->selectedProduct) {
                $query->where('accounts.product_number', $this->selectedProduct);
            }

            if ($this->searchTerm) {
                $query->where(function($q) {
                    $q->where('clients.first_name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('clients.last_name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('clients.client_number', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('accounts.account_number', 'like', '%' . $this->searchTerm . '%');
                });
            }

            $results = $query->orderBy('accounts.balance', 'desc')->get();
            
            // Ensure we have a proper collection of objects
            $this->accountsData = collect();
            
            foreach ($results as $item) {
                // Convert to array for consistent access in blade template
                $accountArray = [
                    'account_number' => $item->account_number ?? '',
                    'balance' => $item->balance ?? 0,
                    'status' => $item->status ?? '',
                    'created_at' => $item->created_at ?? '',
                    'first_name' => $item->first_name ?? '',
                    'last_name' => $item->last_name ?? '',
                    'client_number' => $item->client_number ?? '',
                    'phone_number' => $item->phone_number ?? '',
                    'product_name' => $item->product_name ?? 'SAVINGS',
                    'account_name' => $item->account_name ?? '',
                    'updated_at' => $item->updated_at ?? ''
                ];
                
                $this->accountsData->push($accountArray);
            }
            
        } catch (Exception $e) {
            Log::error('Error loading accounts data: ' . $e->getMessage());
            $this->accountsData = collect();
        }
    }

    public function loadMembersData()
    {
        try {
            // Get all members with their savings status (only savings products)
            $results = DB::table('clients')
                ->leftJoin('accounts', function($join) {
                    $join->on('clients.client_number', '=', 'accounts.client_number')
                         ->where('accounts.major_category_code', '2000')
                         ->where('accounts.product_number', '2000')
                         ->where('accounts.balance', '>', 0);
                })
                ->select(
                    'clients.client_number',
                    'clients.first_name',
                    'clients.last_name',
                    'clients.phone_number',
                    'clients.email',
                    'clients.status as member_status',
                    DB::raw('COALESCE(SUM(accounts.balance), 0) as total_savings'),
                    DB::raw('COUNT(accounts.id) as savings_accounts'),
                    DB::raw("CASE WHEN SUM(accounts.balance) > 0 THEN 'COMPLIANT' ELSE 'NON_COMPLIANT' END as compliance_status")
                )
                ->groupBy('clients.client_number', 'clients.first_name', 'clients.last_name', 'clients.phone_number', 'clients.email', 'clients.status')
                ->orderBy('total_savings', 'desc')
                ->get();
            
            // Ensure we have a proper collection of objects
            $this->membersData = collect();
            
            foreach ($results as $item) {
                // Convert to array for consistent access in blade template
                $memberArray = [
                    'client_number' => $item->client_number ?? '',
                    'first_name' => $item->first_name ?? '',
                    'last_name' => $item->last_name ?? '',
                    'phone_number' => $item->phone_number ?? '',
                    'email' => $item->email ?? '',
                    'member_status' => $item->member_status ?? '',
                    'total_savings' => $item->total_savings ?? 0,
                    'savings_accounts' => $item->savings_accounts ?? 0,
                    'compliance_status' => $item->compliance_status ?? 'NON_COMPLIANT',
                    'created_at' => $item->created_at ?? ''
                ];
                
                $this->membersData->push($memberArray);
            }
            
        } catch (Exception $e) {
            Log::error('Error loading members data: ' . $e->getMessage());
            $this->membersData = collect();
        }
    }

    public function loadProductsData()
    {
        try {
            // Get all savings products from sub_products table where product_type = 2000
            $products = DB::table('sub_products')
                ->where('sub_products.product_type', '2000')
                ->select(
                    'sub_products.product_name',
                    'sub_products.sub_product_id',
                    'sub_products.status',
                    DB::raw('0 as account_count'), // Will be updated below
                    DB::raw('0 as total_balance'),
                    DB::raw('0 as average_balance')
                )
                ->get();
            
            // Get the total savings accounts data (since all accounts use product_number = 2000)
            $savingsAccountsData = DB::table('accounts')
                ->where('major_category_code', '2000')
                ->where('product_number', '2000')
                ->where('client_number', '!=', '0000')
                ->select(
                    DB::raw('COUNT(*) as account_count'),
                    DB::raw('SUM(balance) as total_balance'),
                    DB::raw('AVG(balance) as average_balance')
                )
                ->first();
            
            // Ensure we have a proper collection of objects
            $this->productsData = collect();
            
            foreach ($products as $item) {
                // Force conversion to object and ensure all properties are accessible
                $productObject = (object) [
                    'product_name' => $item->product_name ?? '',
                    'sub_product_id' => $item->sub_product_id ?? '',
                    'status' => $item->status ?? '',
                    'account_count' => $savingsAccountsData->account_count ?? 0,
                    'total_balance' => $savingsAccountsData->total_balance ?? 0,
                    'average_balance' => $savingsAccountsData->average_balance ?? 0
                ];
                
                $this->productsData->push($productObject);
            }
        } catch (Exception $e) {
            Log::error('Error loading products data: ' . $e->getMessage());
            $this->productsData = collect();
        }
    }

    public function loadNonCompliantMembers()
    {
        $results = DB::table('clients')
            ->leftJoin('accounts', function($join) {
                $join->on('clients.client_number', '=', 'accounts.client_number')
                     ->where('accounts.major_category_code', '2000')
                     ->where('accounts.product_number', '2000')
                     ->where('accounts.balance', '>', 0);
            })
            ->whereNull('accounts.id')
            ->select(
                'clients.client_number',
                'clients.first_name',
                'clients.last_name',
                'clients.phone_number',
                'clients.email',
                'clients.status'
            )
            ->get();
        
        // Convert to arrays for consistent access in blade template
        $this->nonCompliantMembers = $results->map(function($item) {
            return (array) $item;
        });
    }

    public function loadMonthlyTotals()
    {
        // Get monthly totals for the selected year
        $results = DB::table('general_ledger')
            ->where('major_category_code', '2000')
            ->whereYear('created_at', $this->selectedYear)
            ->selectRaw('
                EXTRACT(MONTH FROM created_at) as month,
                SUM(credit) as total_deposits,
                SUM(debit) as total_withdrawals,
                COUNT(*) as transaction_count
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Convert to arrays for consistent access in blade template
        $this->monthlyTotals = $results->map(function($item) {
            return (array) $item;
        });
    }

    public function getProductsDataProperty()
    {
        if (!isset($this->productsData) || !is_object($this->productsData) || !method_exists($this->productsData, 'count')) {
            return collect();
        }
        return $this->productsData;
    }

    public function sendNotification($memberId)
    {
        try {
            $member = $this->nonCompliantMembers->firstWhere('client_number', $memberId);
            if (!$member) {
                $this->errorMessage = 'Member not found.';
                return;
            }

            // Here you would integrate with your notification system
            // For now, we'll just log the notification
            Log::info('Savings compliance notification sent', [
                'member' => $member->client_number,
                'name' => $member->first_name . ' ' . $member->last_name,
                'phone' => $member->phone_number,
                'email' => $member->email,
                'month' => $this->selectedMonth,
                'year' => $this->selectedYear
            ]);

            $this->successMessage = "Notification sent to {$member->first_name} {$member->last_name} ({$member->client_number})";
            $this->selectedMemberForNotification = null;

        } catch (Exception $e) {
            Log::error('Error sending notification: ' . $e->getMessage());
            $this->errorMessage = 'Failed to send notification. Please try again.';
        }
    }

    public function sendBulkNotifications()
    {
        try {
            $this->isLoading = true;
            $sentCount = 0;

            foreach ($this->nonCompliantMembers as $member) {
                // Send notification to each non-compliant member
                Log::info('Bulk savings compliance notification sent', [
                    'member' => $member->client_number,
                    'name' => $member->first_name . ' ' . $member->last_name,
                    'phone' => $member->phone_number,
                    'email' => $member->email,
                    'month' => $this->selectedMonth,
                    'year' => $this->selectedYear
                ]);
                $sentCount++;
            }

            $this->successMessage = "Bulk notifications sent to {$sentCount} non-compliant members.";

        } catch (Exception $e) {
            Log::error('Error sending bulk notifications: ' . $e->getMessage());
            $this->errorMessage = 'Failed to send bulk notifications. Please try again.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function refreshData()
    {
        $this->loadData();
        $this->successMessage = 'Data refreshed successfully.';
    }

    public function clearMessages()
    {
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    // Excel Export Methods
    public function exportSummary()
    {
        try {
            $this->isLoading = true;
            $fileName = 'savings_summary_' . $this->selectedMonth . '_' . $this->selectedYear . '.xlsx';
            
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\Savings\SummaryExport($this->summaryData, $this->selectedMonth, $this->selectedYear),
                $fileName
            );
        } catch (Exception $e) {
            Log::error('Error exporting summary: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export summary report.';
        } finally {
            $this->isLoading = false;
        }
    }
    
    public function exportAccounts()
    {
        try {
            $this->isLoading = true;
            $fileName = 'savings_accounts_' . $this->selectedMonth . '_' . $this->selectedYear . '.xlsx';
            
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\Savings\AccountsExport($this->accountsData, $this->selectedMonth, $this->selectedYear),
                $fileName
            );
        } catch (Exception $e) {
            Log::error('Error exporting accounts: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export accounts report.';
        } finally {
            $this->isLoading = false;
        }
    }
    
    public function exportTransactions()
    {
        try {
            $this->isLoading = true;
            $fileName = 'savings_transactions_' . $this->selectedMonth . '_' . $this->selectedYear . '.xlsx';
            
            // Get transaction data
            $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
            $endDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();
            
            $transactions = general_ledger::whereBetween('created_at', [$startDate, $endDate])
                ->where(function($query) {
                    $query->whereHas('account', function($q) {
                        $q->where('account_type', 'Savings');
                    })
                    ->orWhereHas('creditAccount', function($q) {
                        $q->where('account_type', 'Savings');
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get();
            
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\Savings\TransactionsExport($transactions, $this->selectedMonth, $this->selectedYear),
                $fileName
            );
        } catch (Exception $e) {
            Log::error('Error exporting transactions: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export transactions report.';
        } finally {
            $this->isLoading = false;
        }
    }
    
    public function exportMembers()
    {
        try {
            $this->isLoading = true;
            $fileName = 'savings_members_' . $this->selectedMonth . '_' . $this->selectedYear . '.xlsx';
            
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\Savings\MembersExport($this->membersData, $this->selectedMonth, $this->selectedYear),
                $fileName
            );
        } catch (Exception $e) {
            Log::error('Error exporting members: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export members report.';
        } finally {
            $this->isLoading = false;
        }
    }
    
    public function exportMonthlyTrends()
    {
        try {
            $this->isLoading = true;
            $fileName = 'savings_monthly_trends_' . $this->selectedYear . '.xlsx';
            
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\Savings\MonthlyTrendsExport($this->monthlyTotals, $this->selectedYear),
                $fileName
            );
        } catch (Exception $e) {
            Log::error('Error exporting monthly trends: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export monthly trends report.';
        } finally {
            $this->isLoading = false;
        }
    }
    
    public function exportFullReport()
    {
        try {
            $this->isLoading = true;
            $fileName = 'savings_full_report_' . $this->selectedMonth . '_' . $this->selectedYear . '.xlsx';
            
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\Savings\FullReportExport(
                    $this->summaryData,
                    $this->accountsData,
                    $this->membersData,
                    $this->monthlyTotals,
                    $this->nonCompliantMembers,
                    $this->selectedMonth,
                    $this->selectedYear
                ),
                $fileName
            );
        } catch (Exception $e) {
            Log::error('Error exporting full report: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export full report.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.savings.monthly-report');
    }
}
