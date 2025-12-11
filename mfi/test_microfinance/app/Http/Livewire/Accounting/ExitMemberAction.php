<?php

namespace App\Http\Livewire\Accounting;

use App\Models\ClientsModel;
use App\Models\Expense;
use App\Models\MemberExitTransaction;
use App\Services\TransactionPostingService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ExitMemberAction extends Component
{
    public $member;
    public $exitCalculation = [];
    public $exitStep = 1; // 1 = Calculation, 2 = Settlement, 3 = Confirmation
    public $settlementMethod = 'cash'; // cash, bank_transfer, offset
    public $settlementNotes = '';
    public $showSettlementModal = false;

    protected $transactionPostingService;

    public function boot(TransactionPostingService $transactionPostingService)
    {
        $this->transactionPostingService = $transactionPostingService;
    }

    public function mount()
    {
        $memberId = session()->get('viewMemberId_details');
        if ($memberId) {
            $this->member = ClientsModel::with([
                'accounts',
                'loans.loanAccount',
                'bills.service',
                'dividends',
                'interestPayables'
            ])->find($memberId);

            if ($this->member) {
                $this->calculateExitAmount();
            }
        }
    }

    public function calculateExitAmount()
    {
        if (!$this->member) {
            return;
        }

        // 1. Total Dividends
        $totalDividends = $this->member->dividends->sum('amount');

        // 2. Total Interest on Savings
        $totalInterest = $this->member->interestPayables->sum('interest_payable');

        // 3. Total Accounts Balances
        $totalAccountsBalance = $this->member->accounts->sum('balance');

        // 4. Total Loan Account Balance
        $totalLoanBalance = $this->member->loans->sum(function($loan) {
            return $loan->loanAccount->balance ?? 0;
        });

        // 5. Sum of Unpaid Control Numbers
        $unpaidBills = $this->member->bills->where('status', '!=', 'PAID');
        $totalUnpaidBills = $unpaidBills->sum('amount_due');

        // 6. Calculate Exit Amount
        $exitAmount = $totalDividends + $totalInterest + $totalAccountsBalance - $totalLoanBalance - $totalUnpaidBills;

        $this->exitCalculation = [
            'total_dividends' => $totalDividends,
            'total_interest' => $totalInterest,
            'total_accounts_balance' => $totalAccountsBalance,
            'total_loan_balance' => $totalLoanBalance,
            'total_unpaid_bills' => $totalUnpaidBills,
            'exit_amount' => $exitAmount,
            'unpaid_bills_count' => $unpaidBills->count(),
            'loans_count' => $this->member->loans->count(),
            'accounts_count' => $this->member->accounts->count(),
            'dividends_count' => $this->member->dividends->count(),
            'interest_records_count' => $this->member->interestPayables->count(),
            'exit_type' => $this->determineExitType($exitAmount),
        ];
    }

    private function determineExitType($exitAmount)
    {
        if ($exitAmount > 0) {
            return 'refund'; // Member receives money
        } elseif ($exitAmount < 0) {
            return 'settlement'; // Member owes money
        } else {
            return 'clean'; // Zero balance
        }
    }

    public function initiateExit()
    {
        \Log::info('=== INITIATE MEMBER EXIT ===', [
            'member_id' => $this->member->id,
            'exit_amount' => $this->exitCalculation['exit_amount'] ?? null,
            'exit_type' => $this->exitCalculation['exit_type'] ?? null
        ]);

        // Check if member already exited
        if ($this->member->status === 'EXIT') {
            session()->flash('error', 'Member has already been exited.');
            return;
        }

        // Determine next step based on exit type
        $exitType = $this->exitCalculation['exit_type'];

        if ($exitType === 'clean') {
            // Zero balance - proceed directly to exit
            $this->processCleanExit();
        } elseif ($exitType === 'refund') {
            // Positive balance - show refund modal
            $this->showSettlementModal = true;
            $this->exitStep = 2;
        } elseif ($exitType === 'settlement') {
            // Negative balance - show settlement modal
            $this->showSettlementModal = true;
            $this->exitStep = 2;
        }
    }

    public function processCleanExit()
    {
        \Log::info('=== PROCESS CLEAN EXIT (Zero Balance) ===', [
            'member_id' => $this->member->id,
        ]);

        try {
            DB::beginTransaction();

            // Update member status to EXIT
            $this->member->update([
                'status' => 'EXIT',
                'exit_date' => now(),
                'exit_notes' => $this->buildExitNotes() . ' Exit Type: Clean (Zero Balance)',
                'updated_by' => auth()->id()
            ]);

            // Close all member accounts
            foreach ($this->member->accounts as $account) {
                $account->update([
                    'status' => 'CLOSED',
                    'closed_date' => now(),
                    'updated_by' => auth()->id()
                ]);
            }

            \Log::info('✅ Clean exit processed successfully', [
                'member_id' => $this->member->id,
                'client_number' => $this->member->client_number,
            ]);

            DB::commit();

            session()->flash('message', 'Member ' . $this->member->client_number . ' has been successfully exited (Zero balance - No settlement required).');
            $this->member->refresh();
            $this->showSettlementModal = false;
            $this->exitStep = 1;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('❌ ERROR PROCESSING CLEAN EXIT', [
                'member_id' => $this->member->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error processing member exit: ' . $e->getMessage());
        }
    }

    public function processRefundExit()
    {
        $this->validate([
            'settlementMethod' => 'required|in:cash,bank_transfer,internal_transfer',
            'settlementNotes' => 'required|min:10',
        ]);

        \Log::info('=== PROCESS REFUND EXIT (Member receives money) ===', [
            'member_id' => $this->member->id,
            'refund_amount' => $this->exitCalculation['exit_amount'],
            'method' => $this->settlementMethod,
        ]);

        try {
            DB::beginTransaction();

            $refundAmount = abs($this->exitCalculation['exit_amount']);

            // Create expense record for the refund
            $expense = Expense::create([
                'expense_number' => 'EXP-EXIT-' . date('YmdHis') . '-' . rand(1000, 9999),
                'member_id' => $this->member->id,
                'category' => 'Member Exit Refund',
                'amount' => $refundAmount,
                'description' => 'Member exit refund - ' . $this->settlementNotes,
                'payment_method' => $this->settlementMethod,
                'status' => 'APPROVED', // Auto-approve exit refunds
                'requested_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'created_at' => now(),
            ]);

            // Update member status to EXIT
            $this->member->update([
                'status' => 'EXIT',
                'exit_date' => now(),
                'exit_notes' => $this->buildExitNotes() . ' Exit Type: Refund. Amount: ' . number_format($refundAmount, 2) . '. Method: ' . $this->settlementMethod . '. Notes: ' . $this->settlementNotes,
                'updated_by' => auth()->id()
            ]);

            // Close all member accounts
            foreach ($this->member->accounts as $account) {
                $account->update([
                    'status' => 'CLOSED',
                    'closed_date' => now(),
                    'balance' => 0, // Clear balance as it's being refunded
                    'updated_by' => auth()->id()
                ]);
            }

            \Log::info('✅ Refund exit processed successfully', [
                'member_id' => $this->member->id,
                'expense_id' => $expense->id,
                'refund_amount' => $refundAmount,
            ]);

            DB::commit();

            session()->flash('message', 'Member ' . $this->member->client_number . ' has been successfully exited. Refund expense created: ' . $expense->expense_number . ' for ' . number_format($refundAmount, 2));
            $this->member->refresh();
            $this->showSettlementModal = false;
            $this->exitStep = 1;
            $this->resetSettlementForm();

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('❌ ERROR PROCESSING REFUND EXIT', [
                'member_id' => $this->member->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error processing refund exit: ' . $e->getMessage());
        }
    }

    public function processSettlementExit()
    {
        $this->validate([
            'settlementMethod' => 'required|in:cash,bank_transfer,offset,write_off',
            'settlementNotes' => 'required|min:10',
        ]);

        \Log::info('=== PROCESS SETTLEMENT EXIT (Member owes money) ===', [
            'member_id' => $this->member->id,
            'settlement_amount' => abs($this->exitCalculation['exit_amount']),
            'method' => $this->settlementMethod,
        ]);

        try {
            DB::beginTransaction();

            $settlementAmount = abs($this->exitCalculation['exit_amount']);

            if ($this->settlementMethod === 'write_off') {
                // Write off the debt
                $this->processWriteOffExit($settlementAmount);
            } else {
                // Create a bill for payment OR mark as offset
                $this->createSettlementBill($settlementAmount);
            }

            // Update member status to EXIT
            $this->member->update([
                'status' => 'EXIT',
                'exit_date' => now(),
                'exit_notes' => $this->buildExitNotes() . ' Exit Type: Settlement. Amount Owed: ' . number_format($settlementAmount, 2) . '. Method: ' . $this->settlementMethod . '. Notes: ' . $this->settlementNotes,
                'updated_by' => auth()->id()
            ]);

            // Close all member accounts
            foreach ($this->member->accounts as $account) {
                $account->update([
                    'status' => 'CLOSED',
                    'closed_date' => now(),
                    'balance' => 0,
                    'updated_by' => auth()->id()
                ]);
            }

            \Log::info('✅ Settlement exit processed successfully', [
                'member_id' => $this->member->id,
                'settlement_amount' => $settlementAmount,
                'method' => $this->settlementMethod,
            ]);

            DB::commit();

            session()->flash('message', 'Member ' . $this->member->client_number . ' has been successfully exited. Settlement: ' . $this->settlementMethod . ' for ' . number_format($settlementAmount, 2));
            $this->member->refresh();
            $this->showSettlementModal = false;
            $this->exitStep = 1;
            $this->resetSettlementForm();

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('❌ ERROR PROCESSING SETTLEMENT EXIT', [
                'member_id' => $this->member->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error processing settlement exit: ' . $e->getMessage());
        }
    }

    private function processWriteOffExit($amount)
    {
        // Create expense record for write-off (requires approval)
        Expense::create([
            'expense_number' => 'EXP-WRITEOFF-' . date('YmdHis') . '-' . rand(1000, 9999),
            'member_id' => $this->member->id,
            'category' => 'Member Exit Write-off',
            'amount' => $amount,
            'description' => 'Member exit debt write-off - ' . $this->settlementNotes,
            'payment_method' => 'write_off',
            'status' => 'PENDING', // Requires approval
            'requested_by' => auth()->id(),
            'created_at' => now(),
        ]);

        \Log::info('Write-off expense created for member exit', [
            'member_id' => $this->member->id,
            'amount' => $amount,
        ]);
    }

    private function createSettlementBill($amount)
    {
        // This would create a bill/control number for the member to pay
        // Implementation depends on your billing system
        \Log::info('Settlement bill should be created', [
            'member_id' => $this->member->id,
            'amount' => $amount,
            'method' => $this->settlementMethod,
        ]);
    }

    private function buildExitNotes()
    {
        return 'Member exited on ' . now()->format('Y-m-d H:i:s') . '. ' .
               'Exit calculation: Dividends=' . number_format($this->exitCalculation['total_dividends'], 2) .
               ', Interest=' . number_format($this->exitCalculation['total_interest'], 2) .
               ', Accounts Balance=' . number_format($this->exitCalculation['total_accounts_balance'], 2) .
               ', Loan Balance=' . number_format($this->exitCalculation['total_loan_balance'], 2) .
               ', Unpaid Bills=' . number_format($this->exitCalculation['total_unpaid_bills'], 2) .
               '. Final Settlement: ' . number_format($this->exitCalculation['exit_amount'], 2) . '.';
    }

    private function resetSettlementForm()
    {
        $this->settlementMethod = 'cash';
        $this->settlementNotes = '';
    }

    public function cancelSettlement()
    {
        $this->showSettlementModal = false;
        $this->exitStep = 1;
        $this->resetSettlementForm();
    }

    public function render()
    {
        return view('livewire.accounting.exit-member-action');
    }

    public function download(){
        $member_exit_document=ClientsModel::where('id',session()->get('viewMemberId_details'))->value('member_exit_document');
        $filePath = storage_path('app/public/' .$member_exit_document);
        return response()->download($filePath);
    }
}
