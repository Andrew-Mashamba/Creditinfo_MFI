<?php

namespace App\Http\Livewire\Transactions;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\AccountsModel;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Carbon\Carbon;
use App\Traits\Livewire\WithModulePermissions;

class Transactions extends Component
{
    use WithPagination, WithModulePermissions;

    // Navigation
    public $selectedMenuItem = 1;

    // Search and Filter Properties
    public $search = '';
    public $status = '';
    public $type = '';
    public $category = '';
    public $subcategory = '';
    public $externalSystem = '';
    public $reconciliationStatus = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $amountFrom = '';
    public $amountTo = '';
    public $accountId = '';
    public $isManual = '';
    public $requiresApproval = '';
    public $source = '';
    public $serviceName = '';
    public $channelCode = '';

    // Sorting
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 25;

    // Transaction Details
    public $selectedTransaction = null;
    public $showTransactionModal = false;
    public $showReversalModal = false;
    public $showRetryModal = false;
    public $showReconciliationModal = false;

    // Modal Properties
    public $reversalReason = '';
    public $retryReason = '';
    public $reconciliationNotes = '';

    // Statistics
    public $totalTransactions = 0;
    public $totalAmount = 0;
    public $pendingCount = 0;
    public $completedCount = 0;
    public $failedCount = 0;
    public $unreconciledCount = 0;

    // Dashboard Data
    public $todayTransactions = 0;
    public $todayAmount = 0;
    public $weekTransactions = 0;
    public $weekAmount = 0;
    public $monthTransactions = 0;
    public $monthAmount = 0;
    public $transactionsByCategory = [];
    public $transactionsBySource = [];
    public $transactionsByStatus = [];
    public $recentTransactions = [];
    public $dailyTrend = [];

    protected $listeners = ['refreshTransactions' => '$refresh'];

    public function mount()
    {
        $this->initializeWithModulePermissions();
        $this->loadStatistics();
        $this->loadDashboardData();
    }

    public function selectedMenu($menuId)
    {
        $requiredPermission = $this->getRequiredPermissionForSection($menuId);
        $permissionKey = 'can' . ucfirst($requiredPermission);

        if (!($this->permissions[$permissionKey] ?? false)) {
            session()->flash('error', 'You do not have permission to access this transactions section');
            return;
        }

        $this->selectedMenuItem = $menuId;
        $this->resetPage();
    }

    public function loadStatistics()
    {
        $this->totalTransactions = Transaction::count();
        $this->totalAmount = Transaction::where('status', 'COMPLETED')->sum('amount');
        $this->pendingCount = Transaction::where('status', 'PENDING')->count();
        $this->completedCount = Transaction::where('status', 'COMPLETED')->count();
        $this->failedCount = Transaction::where('status', 'FAILED')->count();
        $this->unreconciledCount = Transaction::where('reconciliation_status', 'UNRECONCILED')->count();
    }

    public function loadDashboardData()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        // Today's statistics
        $this->todayTransactions = Transaction::whereDate('created_at', $today)->count();
        $this->todayAmount = Transaction::whereDate('created_at', $today)
            ->where('status', 'COMPLETED')->sum('amount');

        // This week's statistics
        $this->weekTransactions = Transaction::where('created_at', '>=', $weekStart)->count();
        $this->weekAmount = Transaction::where('created_at', '>=', $weekStart)
            ->where('status', 'COMPLETED')->sum('amount');

        // This month's statistics
        $this->monthTransactions = Transaction::where('created_at', '>=', $monthStart)->count();
        $this->monthAmount = Transaction::where('created_at', '>=', $monthStart)
            ->where('status', 'COMPLETED')->sum('amount');

        // Transactions by category
        $this->transactionsByCategory = Transaction::select('transaction_category', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->whereNotNull('transaction_category')
            ->groupBy('transaction_category')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->toArray();

        // Transactions by source
        $this->transactionsBySource = Transaction::select('source', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->whereNotNull('source')
            ->groupBy('source')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->toArray();

        // Transactions by status
        $this->transactionsByStatus = Transaction::select('status', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('status')
            ->orderByDesc('count')
            ->get()
            ->toArray();

        // Recent transactions
        $this->recentTransactions = Transaction::select('id', 'reference', 'amount', 'status', 'source', 'service_name', 'payer_name', 'created_at')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->toArray();

        // Daily trend (last 7 days)
        $this->dailyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = Transaction::whereDate('created_at', $date)->count();
            $amount = Transaction::whereDate('created_at', $date)->where('status', 'COMPLETED')->sum('amount');
            $this->dailyTrend[] = [
                'date' => $date->format('M d'),
                'day' => $date->format('D'),
                'count' => $count,
                'amount' => $amount
            ];
        }
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatus() { $this->resetPage(); }
    public function updatingType() { $this->resetPage(); }
    public function updatingCategory() { $this->resetPage(); }
    public function updatingSubcategory() { $this->resetPage(); }
    public function updatingExternalSystem() { $this->resetPage(); }
    public function updatingReconciliationStatus() { $this->resetPage(); }
    public function updatingDateFrom() { $this->resetPage(); }
    public function updatingDateTo() { $this->resetPage(); }
    public function updatingAmountFrom() { $this->resetPage(); }
    public function updatingAmountTo() { $this->resetPage(); }
    public function updatingAccountId() { $this->resetPage(); }
    public function updatingSource() { $this->resetPage(); }
    public function updatingServiceName() { $this->resetPage(); }
    public function updatingChannelCode() { $this->resetPage(); }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function viewTransaction($transactionId)
    {
        if (!($this->permissions['canView'] ?? false)) {
            session()->flash('error', 'You do not have permission to view transaction details');
            return;
        }

        $this->selectedTransaction = Transaction::with([
            'account', 'auditLogs', 'retryLogs', 'reconciliations', 'originalTransaction', 'reversalTransaction'
        ])->find($transactionId);
        $this->showTransactionModal = true;
    }

    public function closeTransactionModal()
    {
        $this->showTransactionModal = false;
        $this->selectedTransaction = null;
    }

    public function reverseTransaction($transactionId)
    {
        if (!($this->permissions['canManage'] ?? false)) {
            session()->flash('error', 'You do not have permission to reverse transactions');
            return;
        }
        $this->selectedTransaction = Transaction::find($transactionId);
        $this->showReversalModal = true;
    }

    public function confirmReversal()
    {
        if (!($this->permissions['canManage'] ?? false)) {
            session()->flash('error', 'You do not have permission to reverse transactions');
            return;
        }
        $this->validate(['reversalReason' => 'required|string|min:10']);
        try {
            $this->selectedTransaction->reverse($this->reversalReason, auth()->id());
            $this->showReversalModal = false;
            $this->reversalReason = '';
            $this->loadStatistics();
            session()->flash('message', 'Transaction reversed successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to reverse transaction: ' . $e->getMessage());
        }
    }

    public function retryTransaction($transactionId)
    {
        if (!($this->permissions['canManage'] ?? false)) {
            session()->flash('error', 'You do not have permission to retry transactions');
            return;
        }
        $this->selectedTransaction = Transaction::find($transactionId);
        $this->showRetryModal = true;
    }

    public function confirmRetry()
    {
        if (!($this->permissions['canManage'] ?? false)) {
            session()->flash('error', 'You do not have permission to retry transactions');
            return;
        }
        $this->validate(['retryReason' => 'required|string|min:10']);
        try {
            $this->selectedTransaction->markForRetry($this->retryReason);
            $this->showRetryModal = false;
            $this->retryReason = '';
            $this->loadStatistics();
            session()->flash('message', 'Transaction marked for retry');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to mark transaction for retry: ' . $e->getMessage());
        }
    }

    public function reconcileTransaction($transactionId)
    {
        if (!($this->permissions['canManage'] ?? false)) {
            session()->flash('error', 'You do not have permission to reconcile transactions');
            return;
        }
        $this->selectedTransaction = Transaction::find($transactionId);
        $this->showReconciliationModal = true;
    }

    public function confirmReconciliation()
    {
        if (!($this->permissions['canManage'] ?? false)) {
            session()->flash('error', 'You do not have permission to reconcile transactions');
            return;
        }
        try {
            $this->selectedTransaction->reconcile([
                'notes' => $this->reconciliationNotes,
                'reconciled_by' => auth()->id()
            ]);
            $this->showReconciliationModal = false;
            $this->reconciliationNotes = '';
            $this->loadStatistics();
            session()->flash('message', 'Transaction reconciled successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to reconcile transaction: ' . $e->getMessage());
        }
    }

    public function exportTransactions()
    {
        if (!($this->permissions['canExport'] ?? false)) {
            session()->flash('error', 'You do not have permission to export transactions');
            return;
        }
        session()->flash('message', 'Export feature coming soon');
    }

    public function clearFilters()
    {
        $this->reset([
            'search', 'status', 'type', 'category', 'subcategory', 'externalSystem',
            'reconciliationStatus', 'dateFrom', 'dateTo', 'amountFrom', 'amountTo',
            'accountId', 'isManual', 'requiresApproval', 'source', 'serviceName', 'channelCode'
        ]);
        $this->resetPage();
    }

    public function render()
    {
        $query = Transaction::with(['account'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('reference', 'like', '%' . $this->search . '%')
                      ->orWhere('external_reference', 'like', '%' . $this->search . '%')
                      ->orWhere('correlation_id', 'like', '%' . $this->search . '%')
                      ->orWhere('narration', 'like', '%' . $this->search . '%')
                      ->orWhere('payer_name', 'like', '%' . $this->search . '%')
                      ->orWhere('payer_phone', 'like', '%' . $this->search . '%')
                      ->orWhere('gateway_ref', 'like', '%' . $this->search . '%')
                      ->orWhere('biller_receipt', 'like', '%' . $this->search . '%')
                      ->orWhere('credit_account', 'like', '%' . $this->search . '%')
                      ->orWhereHas('account', function($q) {
                          $q->where('account_number', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->status, fn($query) => $query->where('status', $this->status))
            ->when($this->type, fn($query) => $query->where('type', $this->type))
            ->when($this->category, fn($query) => $query->where('transaction_category', $this->category))
            ->when($this->subcategory, fn($query) => $query->where('transaction_subcategory', $this->subcategory))
            ->when($this->externalSystem, fn($query) => $query->where('external_system', $this->externalSystem))
            ->when($this->reconciliationStatus, fn($query) => $query->where('reconciliation_status', $this->reconciliationStatus))
            ->when($this->dateFrom, fn($query) => $query->where('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($query) => $query->where('created_at', '<=', $this->dateTo . ' 23:59:59'))
            ->when($this->amountFrom, fn($query) => $query->where('amount', '>=', $this->amountFrom))
            ->when($this->amountTo, fn($query) => $query->where('amount', '<=', $this->amountTo))
            ->when($this->accountId, fn($query) => $query->where('account_id', $this->accountId))
            ->when($this->isManual !== '', fn($query) => $query->where('is_manual', $this->isManual))
            ->when($this->requiresApproval !== '', fn($query) => $query->where('requires_approval', $this->requiresApproval))
            ->when($this->source, fn($query) => $query->where('source', $this->source))
            ->when($this->serviceName, fn($query) => $query->where('service_name', $this->serviceName))
            ->when($this->channelCode, fn($query) => $query->where('channel_code', $this->channelCode))
            ->orderBy($this->sortField, $this->sortDirection);

        $transactions = $query->paginate($this->perPage);

        // Get filter options
        $accounts = AccountsModel::select('id', 'account_number', 'account_name')->get();
        $statuses = Transaction::distinct()->pluck('status')->filter();
        $types = Transaction::distinct()->pluck('type')->filter();
        $categories = Transaction::distinct()->pluck('transaction_category')->filter();
        $subcategories = Transaction::distinct()->pluck('transaction_subcategory')->filter();
        $externalSystems = Transaction::distinct()->pluck('external_system')->filter();
        $reconciliationStatuses = Transaction::distinct()->pluck('reconciliation_status')->filter();
        $sources = Transaction::distinct()->pluck('source')->filter();
        $serviceNames = Transaction::distinct()->pluck('service_name')->filter();
        $channelCodes = Transaction::distinct()->pluck('channel_code')->filter();

        return view('livewire.transactions.transactions', array_merge(
            $this->permissions,
            [
                'transactions' => $transactions,
                'accounts' => $accounts,
                'statuses' => $statuses,
                'types' => $types,
                'categories' => $categories,
                'subcategories' => $subcategories,
                'externalSystems' => $externalSystems,
                'reconciliationStatuses' => $reconciliationStatuses,
                'sources' => $sources,
                'serviceNames' => $serviceNames,
                'channelCodes' => $channelCodes,
                'permissions' => $this->permissions
            ]
        ));
    }

    private function getRequiredPermissionForSection($sectionId)
    {
        $sectionPermissionMap = [
            1 => 'view', 2 => 'create', 3 => 'view', 4 => 'view', 5 => 'manage', 6 => 'view',
        ];
        return $sectionPermissionMap[$sectionId] ?? 'view';
    }

    protected function getModuleName(): string
    {
        return 'transactions';
    }
}
