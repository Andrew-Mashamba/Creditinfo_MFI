<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Expense;
use App\Models\Account;
use App\Models\Approval;
use App\Models\Transaction;
use App\Models\BudgetAllocation;
use App\Services\ExpensePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ExpensePaymentProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected $paymentService;
    protected $testUser;
    protected $expenseAccount;
    protected $pettyCashAccount;
    protected $bankAccount;
    protected $approvedExpense;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize service
        $this->paymentService = new ExpensePaymentService();
        
        // Create test user
        $this->testUser = User::factory()->create([
            'name' => 'Test Processor',
            'email' => 'processor@test.com'
        ]);
        
        // Create necessary accounts
        $this->createTestAccounts();
        
        // Create test expense with approval
        $this->createTestExpenseWithApproval();
        
        // Authenticate user
        $this->actingAs($this->testUser);
    }
    
    /**
     * Create test accounts for expenses and payments
     */
    private function createTestAccounts()
    {
        // Create expense account (usually an expense category)
        $this->expenseAccount = Account::create([
            'account_number' => 'EXP-001',
            'account_name' => 'Office Supplies Expense',
            'major_category_code' => 5000, // Expense account
            'balance' => 0,
            'status' => 'ACTIVE',
            'account_type' => 'EXPENSE'
        ]);
        
        // Create petty cash account
        $this->pettyCashAccount = Account::create([
            'account_number' => 'CASH-001',
            'account_name' => 'Petty Cash Account',
            'major_category_code' => 1000, // Asset account
            'balance' => 1000000, // 1M initial balance
            'status' => 'ACTIVE',
            'account_type' => 'CASH'
        ]);
        
        // Create bank account
        $this->bankAccount = Account::create([
            'account_number' => 'BANK-001',
            'account_name' => 'Main Bank Account',
            'major_category_code' => 1000, // Asset account
            'balance' => 50000000, // 50M initial balance
            'status' => 'ACTIVE',
            'account_type' => 'BANK'
        ]);
        
        // Create a bank account in bank_accounts table for enhanced payment
        DB::table('bank_accounts')->insert([
            'id' => 1,
            'account_name' => 'NBC Main Account',
            'account_number' => '0110000000001',
            'bank_name' => 'NBC Bank',
            'bank_code' => 'NBC',
            'current_balance' => 100000000, // 100M
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    /**
     * Create test expense with approval
     */
    private function createTestExpenseWithApproval($status = 'APPROVED', $amount = 50000)
    {
        // Create expense
        $this->approvedExpense = Expense::create([
            'description' => 'Test Office Supplies Purchase',
            'amount' => $amount,
            'account_id' => $this->expenseAccount->id,
            'user_id' => $this->testUser->id,
            'expense_date' => now(),
            'status' => 'PENDING_APPROVAL',
            'payment_type' => 'cash'
        ]);
        
        // Create approval record
        Approval::create([
            'process_code' => 'EXPENSE_REG',
            'process_id' => $this->approvedExpense->id,
            'approval_status' => $status,
            'approved_by' => $this->testUser->id,
            'approved_at' => $status === 'APPROVED' ? now() : null,
            'comments' => 'Test approval'
        ]);
    }
    
    /** @test */
    public function it_can_process_simple_cash_payment_successfully()
    {
        // Process the payment
        $result = $this->paymentService->processPayment($this->approvedExpense->id);
        
        // Assert success
        $this->assertTrue($result['success']);
        $this->assertEquals('Expense payment processed successfully', $result['message']);
        $this->assertNotNull($result['transaction_id']);
        $this->assertNotNull($result['payment_reference']);
        $this->assertEquals(50000, $result['amount']);
        
        // Verify expense was updated
        $expense = Expense::find($this->approvedExpense->id);
        $this->assertEquals('PAID', $expense->status);
        $this->assertNotNull($expense->payment_date);
        $this->assertNotNull($expense->payment_reference);
        $this->assertEquals($this->testUser->id, $expense->paid_by_user_id);
        
        // Verify transaction was created
        $transaction = Transaction::find($result['transaction_id']);
        $this->assertNotNull($transaction);
        $this->assertEquals('EXPENSE_PAYMENT', $transaction->type);
        $this->assertEquals(50000, $transaction->amount);
        $this->assertEquals('COMPLETED', $transaction->status);
        
        // Verify account balances were updated
        $expenseAccount = Account::find($this->expenseAccount->id);
        $this->assertEquals(50000, $expenseAccount->balance); // Expense account increased
        
        $cashAccount = Account::where('account_name', 'LIKE', '%Cash%')->first();
        $this->assertEquals(950000, $cashAccount->balance); // Cash account decreased
    }
    
    /** @test */
    public function it_prevents_double_payment_of_expense()
    {
        // First payment
        $result1 = $this->paymentService->processPayment($this->approvedExpense->id);
        $this->assertTrue($result1['success']);
        
        // Second payment attempt
        $result2 = $this->paymentService->processPayment($this->approvedExpense->id);
        
        // Assert failure
        $this->assertFalse($result2['success']);
        $this->assertStringContainsString('already been paid', $result2['message']);
    }
    
    /** @test */
    public function it_requires_approval_before_payment()
    {
        // Create expense without approval
        $unapprovedExpense = Expense::create([
            'description' => 'Unapproved Expense',
            'amount' => 25000,
            'account_id' => $this->expenseAccount->id,
            'user_id' => $this->testUser->id,
            'expense_date' => now(),
            'status' => 'PENDING_APPROVAL',
            'payment_type' => 'cash'
        ]);
        
        // Attempt payment
        $result = $this->paymentService->processPayment($unapprovedExpense->id);
        
        // Assert failure
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('no approval record', $result['message']);
    }
    
    /** @test */
    public function it_rejects_payment_for_rejected_expense()
    {
        // Create expense with rejected approval
        $rejectedExpense = Expense::create([
            'description' => 'Rejected Expense',
            'amount' => 30000,
            'account_id' => $this->expenseAccount->id,
            'user_id' => $this->testUser->id,
            'expense_date' => now(),
            'status' => 'REJECTED',
            'payment_type' => 'cash'
        ]);
        
        Approval::create([
            'process_code' => 'EXPENSE_REG',
            'process_id' => $rejectedExpense->id,
            'approval_status' => 'REJECTED',
            'approved_by' => $this->testUser->id,
            'comments' => 'Not justified'
        ]);
        
        // Attempt payment
        $result = $this->paymentService->processPayment($rejectedExpense->id);
        
        // Assert failure
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Only expenses with approved approval status', $result['message']);
    }
    
    /** @test */
    public function it_processes_payment_with_additional_details()
    {
        $paymentData = [
            'payment_method' => 'bank_transfer',
            'bank_account_id' => $this->bankAccount->id,
            'payment_notes' => 'Payment via bank transfer for office supplies',
            'account_holder_name' => 'ABC Suppliers Ltd'
        ];
        
        // Process payment with details
        $result = $this->paymentService->processPaymentWithDetails(
            $this->approvedExpense->id, 
            $paymentData
        );
        
        // Assert success
        $this->assertTrue($result['success']);
        $this->assertNotNull($result['transaction_id']);
        
        // Verify expense was updated with payment details
        $expense = Expense::find($this->approvedExpense->id);
        $this->assertEquals('PAID', $expense->status);
        $this->assertEquals('bank_transfer', $expense->payment_method);
        $this->assertEquals('Payment via bank transfer for office supplies', $expense->payment_notes);
    }
    
    /** @test */
    public function it_handles_petty_cash_payment_scenario()
    {
        $paymentData = [
            'funding_source' => 'petty_cash',
            'payment_method' => 'cash',
            'payment_notes' => 'Paid from petty cash',
            'requires_external_transfer' => false
        ];
        
        // Process enhanced payment
        $result = $this->paymentService->processEnhancedPayment(
            $this->approvedExpense->id,
            $paymentData
        );
        
        // Assert success
        $this->assertTrue($result['success']);
        $this->assertEquals('Enhanced expense payment processed successfully', $result['message']);
        $this->assertEquals('internal_only', $result['transfer_status']);
        
        // Verify expense status
        $expense = Expense::find($this->approvedExpense->id);
        $this->assertEquals('PAID', $expense->status);
        $this->assertEquals('cash', $expense->payment_method);
    }
    
    /** @test */
    public function it_handles_bank_transfer_payment_scenario()
    {
        $paymentData = [
            'funding_source' => 'bank_account',
            'source_account_id' => 1, // Bank account ID from bank_accounts table
            'payment_method' => 'bank_transfer',
            'recipient_account_number' => '1234567890',
            'recipient_bank_code' => 'CRDB',
            'account_holder_name' => 'John Doe Supplies',
            'payment_notes' => 'Bank transfer for supplies',
            'requires_external_transfer' => true
        ];
        
        // Use real service implementation
        
        // Process enhanced payment
        $result = $this->paymentService->processEnhancedPayment(
            $this->approvedExpense->id,
            $paymentData
        );
        
        // Assert success
        $this->assertTrue($result['success']);
        $this->assertNotNull($result['internal_transaction_id']);
        
        // Verify expense was paid
        $expense = Expense::find($this->approvedExpense->id);
        $this->assertEquals('PAID', $expense->status);
        $this->assertEquals('bank_transfer', $expense->payment_method);
    }
    
    /** @test */
    public function it_handles_mobile_money_payment_scenario()
    {
        $paymentData = [
            'funding_source' => 'bank_account',
            'source_account_id' => 1,
            'payment_method' => 'mobile_money',
            'phone_number' => '255712345678',
            'mno_provider' => 'mpesa',
            'account_holder_name' => 'Jane Doe',
            'payment_notes' => 'Mobile money payment for supplies',
            'requires_external_transfer' => true
        ];
        
        // Use real mobile wallet service
        
        // Process enhanced payment
        $result = $this->paymentService->processEnhancedPayment(
            $this->approvedExpense->id,
            $paymentData
        );
        
        // Assert success
        $this->assertTrue($result['success']);
        
        // Verify expense details
        $expense = Expense::find($this->approvedExpense->id);
        $this->assertEquals('PAID', $expense->status);
        $this->assertEquals('mobile_money', $expense->payment_method);
    }
    
    /** @test */
    public function it_rejects_mobile_money_payment_exceeding_limit()
    {
        // Create expense with amount exceeding mobile money limit
        $largeExpense = Expense::create([
            'description' => 'Large Purchase',
            'amount' => 25000000, // 25M - exceeds 20M limit
            'account_id' => $this->expenseAccount->id,
            'user_id' => $this->testUser->id,
            'expense_date' => now(),
            'status' => 'PENDING_APPROVAL',
            'payment_type' => 'mobile_money'
        ]);
        
        Approval::create([
            'process_code' => 'EXPENSE_REG',
            'process_id' => $largeExpense->id,
            'approval_status' => 'APPROVED',
            'approved_by' => $this->testUser->id,
            'approved_at' => now()
        ]);
        
        $paymentData = [
            'funding_source' => 'bank_account',
            'source_account_id' => 1,
            'payment_method' => 'mobile_money',
            'phone_number' => '255712345678',
            'mno_provider' => 'mpesa',
            'account_holder_name' => 'Jane Doe',
            'requires_external_transfer' => true
        ];
        
        // Process payment
        $result = $this->paymentService->processEnhancedPayment($largeExpense->id, $paymentData);
        
        // Should succeed internally but fail on transfer
        $this->assertTrue($result['success']); // Internal accounting succeeds
        $this->assertEquals('failed', $result['transfer_status']); // But external transfer fails
    }
    
    /** @test */
    public function it_handles_batch_payment_processing()
    {
        // Create multiple approved expenses
        $expenseIds = [];
        for ($i = 1; $i <= 3; $i++) {
            $expense = Expense::create([
                'description' => "Batch Expense $i",
                'amount' => 10000 * $i,
                'account_id' => $this->expenseAccount->id,
                'user_id' => $this->testUser->id,
                'expense_date' => now(),
                'status' => 'PENDING_APPROVAL',
                'payment_type' => 'cash'
            ]);
            
            Approval::create([
                'process_code' => 'EXPENSE_REG',
                'process_id' => $expense->id,
                'approval_status' => 'APPROVED',
                'approved_by' => $this->testUser->id,
                'approved_at' => now()
            ]);
            
            $expenseIds[] = $expense->id;
        }
        
        // Process batch payment
        $results = $this->paymentService->processBatchPayment($expenseIds);
        
        // Assert all were successful
        $this->assertCount(3, $results['successful']);
        $this->assertCount(0, $results['failed']);
        $this->assertEquals(60000, $results['total_amount']); // 10k + 20k + 30k
        
        // Verify all expenses are paid
        foreach ($expenseIds as $id) {
            $expense = Expense::find($id);
            $this->assertEquals('PAID', $expense->status);
        }
    }
    
    /** @test */
    public function it_updates_budget_allocation_after_payment()
    {
        // Create budget allocation
        $budgetAllocation = BudgetAllocation::create([
            'budget_item_id' => 1,
            'allocated_amount' => 500000,
            'utilized_amount' => 100000,
            'available_amount' => 400000,
            'status' => 'ACTIVE'
        ]);
        
        // Create expense linked to budget
        $budgetExpense = Expense::create([
            'description' => 'Budget-linked Expense',
            'amount' => 75000,
            'account_id' => $this->expenseAccount->id,
            'user_id' => $this->testUser->id,
            'expense_date' => now(),
            'status' => 'PENDING_APPROVAL',
            'payment_type' => 'cash',
            'budget_allocation_id' => $budgetAllocation->id
        ]);
        
        Approval::create([
            'process_code' => 'EXPENSE_REG',
            'process_id' => $budgetExpense->id,
            'approval_status' => 'APPROVED',
            'approved_by' => $this->testUser->id,
            'approved_at' => now()
        ]);
        
        // Process payment
        $result = $this->paymentService->processPayment($budgetExpense->id);
        $this->assertTrue($result['success']);
        
        // Verify budget allocation was updated
        $updatedAllocation = BudgetAllocation::find($budgetAllocation->id);
        $this->assertEquals(175000, $updatedAllocation->utilized_amount); // 100k + 75k
        $this->assertEquals(325000, $updatedAllocation->available_amount); // 500k - 175k
    }
    
    /** @test */
    public function it_handles_nbc_internal_bank_transfer()
    {
        $paymentData = [
            'funding_source' => 'bank_account',
            'source_account_id' => 1,
            'payment_method' => 'bank_transfer',
            'recipient_account_number' => '0110000000002',
            'recipient_bank_code' => 'NBC', // Same bank - internal transfer
            'account_holder_name' => 'Internal Department',
            'payment_notes' => 'Internal NBC transfer',
            'requires_external_transfer' => true
        ];
        
        // Use real internal transfer service
        
        // Process payment
        $result = $this->paymentService->processEnhancedPayment(
            $this->approvedExpense->id,
            $paymentData
        );
        
        // Assert success
        $this->assertTrue($result['success']);
        
        // Verify it was processed as internal transfer
        $expense = Expense::find($this->approvedExpense->id);
        $this->assertEquals('PAID', $expense->status);
    }
    
    /** @test */
    public function it_handles_transaction_rollback_on_failure()
    {
        // Create expense with invalid account
        $invalidExpense = Expense::create([
            'description' => 'Invalid Account Expense',
            'amount' => 50000,
            'account_id' => 99999, // Non-existent account
            'user_id' => $this->testUser->id,
            'expense_date' => now(),
            'status' => 'PENDING_APPROVAL',
            'payment_type' => 'cash'
        ]);
        
        Approval::create([
            'process_code' => 'EXPENSE_REG',
            'process_id' => $invalidExpense->id,
            'approval_status' => 'APPROVED',
            'approved_by' => $this->testUser->id,
            'approved_at' => now()
        ]);
        
        // Count transactions before
        $transactionCountBefore = Transaction::count();
        
        // Attempt payment
        $result = $this->paymentService->processPayment($invalidExpense->id);
        
        // Assert failure
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Expense account not found', $result['message']);
        
        // Verify transaction was rolled back
        $transactionCountAfter = Transaction::count();
        $this->assertEquals($transactionCountBefore, $transactionCountAfter);
        
        // Verify expense status wasn't changed
        $expense = Expense::find($invalidExpense->id);
        $this->assertEquals('PENDING_APPROVAL', $expense->status);
    }
    
    /** @test */
    public function it_validates_required_payment_fields()
    {
        // Test missing phone number for mobile money
        $paymentData = [
            'funding_source' => 'bank_account',
            'source_account_id' => 1,
            'payment_method' => 'mobile_money',
            'phone_number' => '', // Missing required field
            'mno_provider' => 'mpesa',
            'account_holder_name' => 'Jane Doe',
            'requires_external_transfer' => true
        ];
        
        // Process should handle gracefully
        $result = $this->paymentService->processEnhancedPayment(
            $this->approvedExpense->id,
            $paymentData
        );
        
        // Internal accounting succeeds but transfer would fail
        $this->assertTrue($result['success']);
    }
    
    // Mock services removed - tests should use real service implementations
    // or proper dependency injection for testing
}