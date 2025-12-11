<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Livewire\Accounting\ExternalAccounts;
use App\Models\BankAccount;
use App\Services\AccountDetailsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

class ExternalAccountsBalanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the external API configuration
        config([
            'services.account_details.base_url' => 'https://api.test.com',
            'services.account_details.api_key' => 'test-api-key',
            'services.account_details.private_key_path' => storage_path('test-keys/private.pem'),
            'services.account_details.channel_name' => 'TEST_CHANNEL',
            'services.account_details.channel_code' => 'TEST001',
        ]);
    }

    /**
     * Test creating bank account with external balance check
     */
    public function test_create_bank_account_with_external_balance()
    {
        // Mock external API response
        Http::fake([
            'https://api.test.com/api/v1/account-details' => Http::response([
                'statusCode' => 600,
                'message' => 'SUCCESS',
                'body' => [
                    'currencyShortName' => 'TZS',
                    'availableBalance' => '1500000.50',
                    'blocked' => false,
                    'replyCode' => '0',
                    'accountTitle' => 'TEST ACCOUNT',
                    'branchCode' => '12',
                    'branchShortName' => 'TEST',
                    'customerShortName' => 'TEST CUSTOMER',
                    'restrictedAccount' => false,
                    'casaAccountStatus' => 'ACCOUNT OPEN REGULAR',
                    'casaAccountStatusCode' => '8',
                    'customerId' => '123456'
                ]
            ], 200)
        ]);

        Livewire::test(ExternalAccounts::class)
            ->set('newBankAccount', [
                'bank_name' => 'Test Bank',
                'account_name' => 'Test Account',
                'account_number' => '011101018916',
                'branch_name' => 'Test Branch',
                'swift_code' => 'TESTUS33',
                'currency' => 'TZS',
                'opening_balance' => 0,
                'current_balance' => 0,
                'internal_mirror_account_number' => 'INT001',
                'status' => 'active',
                'description' => 'Test account'
            ])
            ->call('createBankAccount')
            ->assertHasNoErrors();

        // Verify bank account was created with external balance
        $bankAccount = BankAccount::where('account_number', '011101018916')->first();
        $this->assertNotNull($bankAccount);
        $this->assertEquals(1500000.50, $bankAccount->opening_balance);
        $this->assertEquals(1500000.50, $bankAccount->current_balance);
    }

    /**
     * Test creating bank account when external API fails
     */
    public function test_create_bank_account_when_external_api_fails()
    {
        // Mock external API failure
        Http::fake([
            'https://api.test.com/api/v1/account-details' => Http::response([
                'statusCode' => 605,
                'message' => 'Account not found',
                'body' => []
            ], 200)
        ]);

        Livewire::test(ExternalAccounts::class)
            ->set('newBankAccount', [
                'bank_name' => 'Test Bank',
                'account_name' => 'Test Account',
                'account_number' => '999999999999',
                'branch_name' => 'Test Branch',
                'swift_code' => 'TESTUS33',
                'currency' => 'TZS',
                'opening_balance' => 0,
                'current_balance' => 0,
                'internal_mirror_account_number' => 'INT001',
                'status' => 'active',
                'description' => 'Test account'
            ])
            ->call('createBankAccount')
            ->assertHasNoErrors();

        // Verify bank account was created with zero balance
        $bankAccount = BankAccount::where('account_number', '999999999999')->first();
        $this->assertNotNull($bankAccount);
        $this->assertEquals(0, $bankAccount->opening_balance);
        $this->assertEquals(0, $bankAccount->current_balance);
    }

    /**
     * Test refreshing account balance for existing bank account
     */
    public function test_refresh_account_balance()
    {
        // Create a bank account
        $bankAccount = BankAccount::create([
            'bank_name' => 'Test Bank',
            'account_name' => 'Test Account',
            'account_number' => '011101018916',
            'branch_name' => 'Test Branch',
            'swift_code' => 'TESTUS33',
            'currency' => 'TZS',
            'opening_balance' => 1000000.00,
            'current_balance' => 1000000.00,
            'internal_mirror_account_number' => 'INT001',
            'description' => 'Test account'
        ]);

        // Mock external API response with new balance
        Http::fake([
            'https://api.test.com/api/v1/account-details' => Http::response([
                'statusCode' => 600,
                'message' => 'SUCCESS',
                'body' => [
                    'currencyShortName' => 'TZS',
                    'availableBalance' => '2500000.75',
                    'blocked' => false,
                    'replyCode' => '0',
                    'accountTitle' => 'TEST ACCOUNT',
                    'branchCode' => '12',
                    'branchShortName' => 'TEST',
                    'customerShortName' => 'TEST CUSTOMER',
                    'restrictedAccount' => false,
                    'casaAccountStatus' => 'ACCOUNT OPEN REGULAR',
                    'casaAccountStatusCode' => '8',
                    'customerId' => '123456'
                ]
            ], 200)
        ]);

        Livewire::test(ExternalAccounts::class)
            ->call('refreshAccountBalance', $bankAccount->id)
            ->assertHasNoErrors();

        // Verify balance was updated
        $bankAccount->refresh();
        $this->assertEquals(2500000.75, $bankAccount->current_balance);
    }

    /**
     * Test updating bank account with account number change
     */
    public function test_update_bank_account_with_account_number_change()
    {
        // Create a bank account
        $bankAccount = BankAccount::create([
            'bank_name' => 'Test Bank',
            'account_name' => 'Test Account',
            'account_number' => '011101018916',
            'branch_name' => 'Test Branch',
            'swift_code' => 'TESTUS33',
            'currency' => 'TZS',
            'opening_balance' => 1000000.00,
            'current_balance' => 1000000.00,
            'internal_mirror_account_number' => 'INT001',
            'description' => 'Test account'
        ]);

        // Mock external API response for new account number
        Http::fake([
            'https://api.test.com/api/v1/account-details' => Http::response([
                'statusCode' => 600,
                'message' => 'SUCCESS',
                'body' => [
                    'currencyShortName' => 'TZS',
                    'availableBalance' => '3000000.25',
                    'blocked' => false,
                    'replyCode' => '0',
                    'accountTitle' => 'NEW TEST ACCOUNT',
                    'branchCode' => '12',
                    'branchShortName' => 'TEST',
                    'customerShortName' => 'NEW TEST CUSTOMER',
                    'restrictedAccount' => false,
                    'casaAccountStatus' => 'ACCOUNT OPEN REGULAR',
                    'casaAccountStatusCode' => '8',
                    'customerId' => '789012'
                ]
            ], 200)
        ]);

        Livewire::test(ExternalAccounts::class)
            ->set('selectedBankAccount', $bankAccount)
            ->set('editing', [
                'bank_name' => 'Updated Bank',
                'account_name' => 'Updated Account',
                'account_number' => '022202029927', // Changed account number
                'branch_name' => 'Updated Branch',
                'swift_code' => 'UPDTUS33',
                'currency' => 'TZS',
                'current_balance' => 1000000.00,
                'internal_mirror_account_number' => 'INT002',
                'status' => 'active',
                'description' => 'Updated account'
            ])
            ->call('updateBankAccount')
            ->assertHasNoErrors();

        // Verify bank account was updated with new balance
        $bankAccount->refresh();
        $this->assertEquals('022202029927', $bankAccount->account_number);
        $this->assertEquals(3000000.25, $bankAccount->current_balance);
    }

    /**
     * Test getAccountBalance method with empty account number
     */
    public function test_get_account_balance_with_empty_account_number()
    {
        Livewire::test(ExternalAccounts::class)
            ->set('newBankAccount.account_number', '')
            ->call('getAccountBalance');

        // Should return 0.0 for empty account number
        $this->assertEquals(0.0, Livewire::test(ExternalAccounts::class)->call('getAccountBalance'));
    }

    /**
     * Test error handling in createBankAccount
     */
    public function test_create_bank_account_error_handling()
    {
        // Mock external API timeout
        Http::fake([
            'https://api.test.com/api/v1/account-details' => Http::response('', 408)
        ]);

        Livewire::test(ExternalAccounts::class)
            ->set('newBankAccount', [
                'bank_name' => 'Test Bank',
                'account_name' => 'Test Account',
                'account_number' => '011101018916',
                'branch_name' => 'Test Branch',
                'swift_code' => 'TESTUS33',
                'currency' => 'TZS',
                'opening_balance' => 0,
                'current_balance' => 0,
                'internal_mirror_account_number' => 'INT001',
                'status' => 'active',
                'description' => 'Test account'
            ])
            ->call('createBankAccount')
            ->assertHasNoErrors();

        // Verify bank account was still created with zero balance
        $bankAccount = BankAccount::where('account_number', '011101018916')->first();
        $this->assertNotNull($bankAccount);
        $this->assertEquals(0, $bankAccount->opening_balance);
        $this->assertEquals(0, $bankAccount->current_balance);
    }
} 