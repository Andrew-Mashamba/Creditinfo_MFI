<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Payments\ExternalFundsTransferService;
use Illuminate\Support\Facades\Log;

/**
 * Manual Testing Command for Letshego EFT Service
 * 
 * Usage: php artisan test:letshego-eft [operation]
 * 
 * Operations:
 * - institutions: Get list of supported institutions
 * - lookup: Test account lookup
 * - transfer: Test money transfer
 * - status: Check transaction status
 * - reverse: Test transaction reversal
 * - all: Run all tests
 */
class TestLetsegoEftCommand extends Command
{
    protected $signature = 'test:letshego-eft 
                           {operation=all : Operation to test (institutions|lookup|transfer|status|reverse|all|direct-transfer|direct-tiss)}
                           {--account= : Account number for lookup/transfer}
                           {--bank= : Bank code for lookup/transfer}
                           {--amount=50000 : Amount for transfer}
                           {--ref= : Payer reference for status/reverse}';

    protected $description = 'Test Letshego EFT Service operations manually';

    protected ExternalFundsTransferService $eftService;

    public function handle()
    {
        $this->eftService = new ExternalFundsTransferService();
        $operation = $this->argument('operation');
        
        $this->info("🚀 Testing Letshego EFT Service");
        $this->info("Operation: " . strtoupper($operation));
        $this->newLine();
        
        switch ($operation) {
            case 'institutions':
                $this->testInstitutions();
                break;
            case 'lookup':
                $this->testAccountLookup();
                break;
            case 'transfer':
                $this->testTransfer();
                break;
            case 'direct-transfer':
                $this->testDirectTransfer();
                break;
            case 'direct-tiss':
                $this->testDirectTissTransfer();
                break;
            case 'mobile-money':
                $this->testMobileMoneyTransfer();
                break;
            case 'status':
                $this->testTransactionStatus();
                break;
            case 'reverse':
                $this->testTransactionReversal();
                break;
            case 'all':
                $this->testAll();
                break;
            default:
                $this->error("Unknown operation: {$operation}");
                $this->info("Available operations: institutions, lookup, transfer, status, reverse, all, direct-transfer, direct-tiss, mobile-money");
        }
    }

    protected function testInstitutions()
    {
        $this->info("🏦 Testing Institution List");
        $this->info("Endpoint: GET /{consumer_id}/outwards/institution");
        $this->newLine();
        
        $startTime = microtime(true);
        $response = $this->eftService->getInstitutionList();
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        $this->displayResponse($response, $duration);
        
        if ($response['success'] && isset($response['institutions'])) {
            $this->info("📋 Institution List:");
            foreach ($response['institutions'] as $index => $institution) {
                $this->line("  {$index}: " . json_encode($institution, JSON_PRETTY_PRINT));
            }
        }
    }

    protected function testAccountLookup()
    {
        $this->info("🔍 Testing Account Lookup");
        $this->info("Endpoint: GET /{consumer_id}/outwards/accountenquiry/{account}");
        
        $account = $this->option('account') ?? $this->ask('Enter account number', '0752518001');
        $bankCode = $this->option('bank') ?? $this->ask('Enter bank code', '016');
        
        $this->info("Account: {$account}");
        $this->info("Bank Code: {$bankCode}");
        $this->newLine();
        
        $startTime = microtime(true);
        $response = $this->eftService->lookupAccount($account, $bankCode);
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        $this->displayResponse($response, $duration);
        
        if ($response['success']) {
            $this->info("✅ Account Details:");
            $this->line("  Account Number: {$response['account_number']}");
            $this->line("  Account Name: {$response['account_name']}");
            $this->line("  Bank Code: {$response['bank_code']}");
            $this->line("  Can Receive: " . ($response['can_receive'] ? 'Yes' : 'No'));
        }
    }

    protected function testTransfer()
    {
        $this->info("💸 Testing Money Transfer");
        $this->info("Endpoint: POST /{consumer_id}/outwards/transfers");
        
        $fromAccount = $this->ask('Enter source account', '21710176786');
        $toAccount = $this->option('account') ?? $this->ask('Enter destination account', '0752518001');
        $bankCode = $this->option('bank') ?? $this->ask('Enter bank code', '016');
        $amount = (float) $this->option('amount');
        
        $transferData = [
            'from_account' => $fromAccount,
            'to_account' => $toAccount,
            'bank_code' => $bankCode,
            'amount' => $amount,
            'narration' => 'Test transfer via Letshego EFT Service',
            'sender_name' => 'Test User',
            'sender_phone' => '+255715000001',
            'sender_email' => 'test@letshego.co.tz',
            'initiated_by' => 'CLI_TEST',
            'branch_code' => '044',
            'charge_bearer' => 'OUR'
        ];
        
        $this->info("Transfer Details:");
        $this->line("  From: {$fromAccount}");
        $this->line("  To: {$toAccount} (Bank: {$bankCode})");
        $this->line("  Amount: TZS " . number_format($amount, 2));
        $this->newLine();
        
        if ($this->confirm('Proceed with transfer?', false)) {
            $startTime = microtime(true);
            $response = $this->eftService->transfer($transferData);
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->displayResponse($response, $duration);
            
            if ($response['success']) {
                $this->info("✅ Transfer Details:");
                $this->line("  Reference: {$response['reference']}");
                $this->line("  Routing System: {$response['routing_system']}");
                $this->line("  Letshego Reference: " . ($response['letshego_reference'] ?? 'N/A'));
                $this->line("  Message: {$response['message']}");
                
                // Save reference for status checking
                $this->info("💡 Use this reference to check status:");
                $this->line("php artisan test:letshego-eft status --ref={$response['letshego_reference']}");
            }
        } else {
            $this->warn("Transfer cancelled by user");
        }
    }

    protected function testTransactionStatus()
    {
        $this->info("📊 Testing Transaction Status");
        $this->info("Endpoint: GET /{consumer_id}/outwards/status/{ref}");
        
        $payerRef = $this->option('ref') ?? $this->ask('Enter payer reference', '044-WK175371850125227076');
        
        $this->info("Payer Reference: {$payerRef}");
        $this->newLine();
        
        $startTime = microtime(true);
        $response = $this->eftService->getTransactionStatus($payerRef);
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        $this->displayResponse($response, $duration);
        
        if ($response['success'] && isset($response['status'])) {
            $this->info("📋 Transaction Status:");
            foreach ($response['status'] as $key => $value) {
                $this->line("  {$key}: {$value}");
            }
        }
    }

    protected function testTransactionReversal()
    {
        $this->info("🔄 Testing Transaction Reversal");
        $this->info("Endpoint: POST /{consumer_id}/outwards/transfers/reversal");
        
        $payerRef = $this->option('ref') ?? $this->ask('Enter payer reference', '044-20220616000009');
        $reason = $this->ask('Enter reversal reason', 'Test reversal via CLI');
        
        $this->info("Payer Reference: {$payerRef}");
        $this->info("Reversal Reason: {$reason}");
        $this->newLine();
        
        if ($this->confirm('Proceed with reversal?', false)) {
            $startTime = microtime(true);
            $response = $this->eftService->reverseTransaction($payerRef, $reason);
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->displayResponse($response, $duration);
            
            if ($response['success'] && isset($response['reversal'])) {
                $this->info("✅ Reversal Details:");
                foreach ($response['reversal'] as $key => $value) {
                    $this->line("  {$key}: {$value}");
                }
            }
        } else {
            $this->warn("Reversal cancelled by user");
        }
    }

    protected function testAll()
    {
        $this->info("🔄 Running All Tests");
        $this->newLine();
        
        // 1. Test Institutions
        $this->comment("1. Testing Institution List");
        $this->testInstitutions();
        $this->newLine();
        
        // 2. Test Account Lookup
        $this->comment("2. Testing Account Lookup");
        $this->testAccountLookup();
        $this->newLine();
        
        // 3. Test Internal Account Validation
        $this->comment("3. Testing Internal Account Validation");
        $this->testInternalAccounts();
        $this->newLine();
        
        // 4. Test Transaction Status (with sample ref)
        $this->comment("4. Testing Transaction Status");
        $sampleRef = '044-WK175371850125227076';
        $this->info("Using sample reference: {$sampleRef}");
        $response = $this->eftService->getTransactionStatus($sampleRef);
        $this->displayResponse($response, 0);
        $this->newLine();
        
        $this->info("🎉 All tests completed!");
        $this->info("💡 To test transfers and reversals, run:");
        $this->line("php artisan test:letshego-eft transfer");
        $this->line("php artisan test:letshego-eft reverse --ref=YOUR_REF");
    }

    protected function testInternalAccounts()
    {
        $this->info("🏦 Testing Internal Account Validation");
        
        $testAccounts = [
            '21710176786' => 'Letshego Main Account',
            '21710176787' => 'Letshego Test Account',
            '21710176788' => 'Letshego Business Account',
            '21710176789' => 'Letshego Default Account',
            '21710176790' => 'Generic Letshego Account',
            '123456789' => 'Invalid Account (too short)',
            'abcdef123456' => 'Invalid Account (non-numeric)'
        ];
        
        foreach ($testAccounts as $account => $description) {
            try {
                $reflection = new \ReflectionClass($this->eftService);
                $method = $reflection->getMethod('lookupInternalAccount');
                $method->setAccessible(true);
                
                $response = $method->invoke($this->eftService, $account);
                
                $status = $response['success'] ? '✅ VALID' : '❌ INVALID';
                $name = $response['account_name'] ?? $response['error'] ?? 'Unknown';
                
                $this->line("  {$account}: {$status} - {$name}");
                
            } catch (\Exception $e) {
                $this->line("  {$account}: ❌ ERROR - {$e->getMessage()}");
            }
        }
    }

    protected function testDirectTransfer()
    {
        $this->info("💸 Testing TIPS Transfer (Direct - No Prompts)");
        $this->info("Endpoint: POST /{consumer_id}/outwards/transfers");
        
        $toAccount = $this->option('account') ?? '0752518001';
        $bankCode = $this->option('bank') ?? '016';
        $amount = (float) $this->option('amount') ?? 10000000; // 10M for TIPS
        
        $transferData = [
            'from_account' => '21710176786',
            'to_account' => $toAccount,
            'bank_code' => $bankCode,
            'amount' => $amount,
            'narration' => 'TIPS Test transfer via CLI (Direct)',
            'sender_name' => 'CLI Test User',
            'sender_phone' => '+255715000001',
            'sender_email' => 'test@letshego.co.tz',
            'initiated_by' => 'CLI_DIRECT_TEST',
            'branch_code' => '044',
            'charge_bearer' => 'OUR'
        ];
        
        $this->info("Transfer Details:");
        $this->line("  From: 21710176786");
        $this->line("  To: {$toAccount} (Bank: {$bankCode})");
        $this->line("  Amount: TZS " . number_format($amount, 2) . " (Should use TIPS)");
        $this->newLine();
        
        $startTime = microtime(true);
        $response = $this->eftService->transfer($transferData);
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        $this->displayResponse($response, $duration);
        
        if ($response['success']) {
            $this->info("✅ TIPS Transfer Details:");
            $this->line("  Reference: {$response['reference']}");
            $this->line("  Routing System: {$response['routing_system']}");
            $this->line("  Letshego Reference: " . ($response['letshego_reference'] ?? 'N/A'));
            $this->line("  Message: {$response['message']}");
        }
    }

    protected function testDirectTissTransfer()
    {
        $this->info("💸 Testing TISS Transfer (Direct - No Prompts)");
        $this->info("Endpoint: Should route to TISS for large amounts");
        
        $toAccount = $this->option('account') ?? '0752518001';
        $bankCode = $this->option('bank') ?? '016';
        $amount = 25000000; // 25M for TISS (above 20M threshold)
        
        $transferData = [
            'from_account' => '21710176786',
            'to_account' => $toAccount,
            'bank_code' => $bankCode,
            'amount' => $amount,
            'narration' => 'TISS Test transfer via CLI (Direct)',
            'sender_name' => 'CLI Test User',
            'sender_phone' => '+255715000001',
            'sender_email' => 'test@letshego.co.tz',
            'initiated_by' => 'CLI_DIRECT_TEST',
            'branch_code' => '044',
            'charge_bearer' => 'OUR'
        ];
        
        $this->info("Transfer Details:");
        $this->line("  From: 21710176786");
        $this->line("  To: {$toAccount} (Bank: {$bankCode})");
        $this->line("  Amount: TZS " . number_format($amount, 2) . " (Should use TISS)");
        $this->newLine();
        
        $startTime = microtime(true);
        $response = $this->eftService->transfer($transferData);
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        $this->displayResponse($response, $duration);
        
        if ($response['success']) {
            $this->info("✅ TISS Transfer Details:");
            $this->line("  Reference: {$response['reference']}");
            $this->line("  Routing System: {$response['routing_system']}");
            $this->line("  Letshego Reference: " . ($response['letshego_reference'] ?? 'N/A'));
            $this->line("  Message: {$response['message']}");
        }
    }

    protected function testMobileMoneyTransfer()
    {
        $this->info("📱 Testing Mobile Money Transfer (M-Pesa)");
        $this->info("Bypassing account verification for mobile money test");
        
        $phoneNumber = $this->option('account') ?? '255749908161';
        $bankCode = $this->option('bank') ?? '503'; // Vodacom M-Pesa
        $amount = (float) ($this->option('amount') ?? 50000);
        
        // Mock destination account for mobile money (since lookup fails)
        $mockDestAccount = [
            'success' => true,
            'account_number' => $phoneNumber,
            'account_name' => 'Mobile Money User',
            'can_receive' => true
        ];
        
        $transferData = [
            'from_account' => '21710176786',
            'to_account' => $phoneNumber,
            'bank_code' => $bankCode,
            'amount' => $amount,
            'narration' => 'Mobile Money Test Transfer',
            'sender_name' => 'CLI Test User',
            'sender_phone' => '+255715000001',
            'sender_email' => 'test@letshego.co.tz',
            'initiated_by' => 'CLI_MOBILE_TEST',
            'branch_code' => '044',
            'charge_bearer' => 'OUR'
        ];
        
        $this->info("Mobile Money Transfer Details:");
        $this->line("  From: 21710176786 (Letshego Account)");
        $this->line("  To: {$phoneNumber} (Vodacom M-Pesa)");
        $this->line("  Amount: TZS " . number_format($amount, 2));
        $this->newLine();
        
        // Call EFT service's executeTIPSTransfer directly to test mobile money payload
        try {
            $reflection = new \ReflectionClass($this->eftService);
            $method = $reflection->getMethod('executeTIPSTransfer');
            $method->setAccessible(true);
            
            $sourceAccount = [
                'account_number' => '21710176786',
                'account_name' => 'Letshego Main Account'
            ];
            
            $reference = 'MOBILE_TEST_' . time();
            
            $startTime = microtime(true);
            $response = $method->invoke($this->eftService, $reference, $transferData, $sourceAccount, $mockDestAccount);
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->displayResponse($response, $duration);
            
            if ($response['success']) {
                $this->info("✅ Mobile Money Transfer Details:");
                $this->line("  Reference: {$reference}");
                $this->line("  Routing System: TIPS");
                $this->line("  Phone Number: {$phoneNumber}");
                $this->line("  Provider: Vodacom M-Pesa");
            }
            
        } catch (\Exception $e) {
            $this->error("Mobile money test failed: " . $e->getMessage());
        }
    }

    protected function displayResponse(array $response, float $duration)
    {
        $status = $response['success'] ? '✅ SUCCESS' : '❌ FAILED';
        $this->info("Status: {$status}");
        $this->info("Duration: {$duration}ms");
        
        if (!$response['success'] && isset($response['error'])) {
            $this->error("Error: {$response['error']}");
        }
        
        if ($this->option('verbose')) {
            $this->info("Full Response:");
            $this->line(json_encode($response, JSON_PRETTY_PRINT));
        }
        
        $this->newLine();
    }
}