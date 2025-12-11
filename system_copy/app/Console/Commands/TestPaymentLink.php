<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PaymentLinkService;

class TestPaymentLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:test-link {--type=url : Type of test (url|full|member|loan|installments)} {--real : Use real data from database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test payment link generation service';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->option('type');
        $paymentService = new PaymentLinkService();
        
        try {
            switch ($type) {
                case 'url':
                    $this->testGetPaymentUrl($paymentService);
                    break;
                    
                case 'full':
                    $this->testFullResponse($paymentService);
                    break;
                    
                case 'member':
                    $this->testMemberPayment($paymentService);
                    break;
                    
                case 'loan':
                    $this->testLoanPayment($paymentService);
                    break;
                    
                case 'installments':
                    $this->testLoanInstallments($paymentService);
                    break;
                    
                default:
                    $this->error('Invalid type. Use: url, full, member, loan, or installments');
                    return Command::FAILURE;
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    private function testGetPaymentUrl($paymentService)
    {
        $this->info('Testing payment URL generation...');
        
        $data = [
            'description' => 'Test Saccos services',
            'target' => 'individual',
            'customer_reference' => 'TEST_' . time(),
            'customer_name' => 'Test User',
            'customer_phone' => '255723456789',
            'customer_email' => 'test@example.com',
            'items' => [
                [
                    'type' => 'service',
                    'product_service_reference' => 'TEST_SHARES',
                    'product_service_name' => 'TEST SHARES',
                    'amount' => 100000,
                    'is_required' => true,
                    'allow_partial' => false
                ]
            ]
        ];
        
        $paymentUrl = $paymentService->getPaymentUrl($data);
        
        $this->info('✅ Payment URL generated successfully:');
        $this->line($paymentUrl);
    }
    
    private function testFullResponse($paymentService)
    {
        $this->info('Testing full payment link response...');
        
        $data = [
            'description' => 'Test Saccos services - Full',
            'target' => 'individual',
            'customer_reference' => 'TEST_FULL_' . time(),
            'customer_name' => 'Test Full User',
            'customer_phone' => '255723456789',
            'customer_email' => 'test.full@example.com',
            'items' => [
                [
                    'type' => 'service',
                    'product_service_reference' => 'TEST_SERVICE',
                    'product_service_name' => 'TEST SERVICE',
                    'amount' => 50000,
                    'is_required' => true,
                    'allow_partial' => true
                ]
            ]
        ];
        
        $response = $paymentService->generateUniversalPaymentLink($data);
        
        $this->info('✅ Full response received:');
        $this->line('Payment URL: ' . $response['data']['payment_url']);
        $this->line('Link ID: ' . $response['data']['link_id']);
        $this->line('Short Code: ' . $response['data']['short_code']);
        $this->line('Total Amount: ' . number_format($response['data']['total_amount']) . ' TZS');
    }
    
    private function testMemberPayment($paymentService)
    {
        $this->info('Testing member payment link...');
        
        $response = $paymentService->generateMemberPaymentLink(
            'MEMBER_TEST_' . time(),
            'Test Member',
            '0723456789',
            'member@test.com',
            200000, // Shares
            300000  // Deposits
        );
        
        $this->info('✅ Member payment link generated:');
        $this->line('Payment URL: ' . $response['data']['payment_url']);
        $this->line('Total Amount: ' . number_format($response['data']['total_amount']) . ' TZS');
        $this->table(
            ['Item', 'Amount', 'Required', 'Partial'],
            array_map(function($item) {
                return [
                    $item['product_service_name'],
                    number_format($item['amount']),
                    $item['is_required'] ? 'Yes' : 'No',
                    $item['allow_partial'] ? 'Yes' : 'No'
                ];
            }, $response['data']['items'])
        );
    }
    
    private function testLoanPayment($paymentService)
    {
        $this->info('Testing loan payment link...');
        
        $paymentUrl = $paymentService->generateLoanPaymentUrl(
            'LOAN_TEST_' . time(),
            'Test Borrower',
            '0712345678',
            150000
        );
        
        $this->info('✅ Loan payment URL generated:');
        $this->line($paymentUrl);
    }
    
    private function testLoanInstallments($paymentService)
    {
        $this->info('========================================');
        $this->info('  Testing Loan Installments Payment Link');
        $this->info('========================================');
        $this->newLine();
        
        $useRealData = $this->option('real');
        
        if ($useRealData) {
            // Test with real data from database
            $loanId = $this->ask('Enter Loan ID from database');
            
            $loan = \DB::table('loans')->where('id', $loanId)->first();
            if (!$loan) {
                $this->error("Loan ID $loanId not found!");
                return;
            }
            
            $client = \DB::table('clients')->where('client_number', $loan->client_number)->first();
            if (!$client) {
                $this->error("Client not found for loan!");
                return;
            }
            
            $loanSchedules = \DB::table('loans_schedules')
                ->where('loan_id', $loanId)
                ->orderBy('installment')
                ->get();
                
            if ($loanSchedules->isEmpty()) {
                $this->error("No schedules found for loan!");
                return;
            }
            
            $this->info("Found {$loanSchedules->count()} installments for loan ID $loanId");
            
        } else {
            // Create test data
            $loanId = 1001;
            
            // Create test client
            $client = new \stdClass();
            $client->id = 2001;
            $client->client_number = 'MEMBER2001';
            $client->first_name = 'Simon';
            $client->middle_name = 'Peter';
            $client->last_name = 'Mpembee';
            $client->present_surname = 'Mpembee';
            $client->phone_number = '0742099713';
            $client->email = 'mpembeesimon@email.com';
            
            // Create test loan schedules
            $loanSchedules = collect();
            $baseDate = \Carbon\Carbon::now();
            
            for ($i = 1; $i <= 6; $i++) {
                $schedule = new \stdClass();
                $schedule->id = 5000 + $i;
                $schedule->loan_id = $loanId;
                $schedule->installment = $i;
                $schedule->repayment_date = $baseDate->copy()->addMonths($i)->format('Y-m-d');
                $schedule->principle = 100000; // 100,000 TZS
                $schedule->interest = 15000;   // 15,000 TZS
                $schedule->penalties = 0;
                $schedule->charges = ($i == 1) ? 5000 : 0; // First installment charge
                $schedule->status = 'PENDING';
                
                $loanSchedules->push($schedule);
            }
            
            $this->info('Created 6 test installments');
        }
        
        // Display client info
        $this->info('👤 Client Information:');
        $this->line("   Name: {$client->first_name} {$client->middle_name} {$client->last_name}");
        $this->line("   Client Number: {$client->client_number}");
        $this->line("   Phone: {$client->phone_number}");
        $this->line("   Email: {$client->email}");
        $this->newLine();
        
        // Display schedules
        $this->info('📅 Loan Installments:');
        $headers = ['#', 'Due Date', 'Principal', 'Interest', 'Charges', 'Total'];
        $rows = [];
        $totalAmount = 0;
        
        foreach ($loanSchedules as $schedule) {
            $installmentTotal = ($schedule->principle ?? 0) + 
                              ($schedule->interest ?? 0) + 
                              ($schedule->penalties ?? 0) + 
                              ($schedule->charges ?? 0);
            $totalAmount += $installmentTotal;
            
            $rows[] = [
                $schedule->installment,
                $schedule->repayment_date,
                number_format($schedule->principle ?? 0, 2),
                number_format($schedule->interest ?? 0, 2),
                number_format($schedule->charges ?? 0, 2),
                number_format($installmentTotal, 2)
            ];
        }
        
        $this->table($headers, $rows);
        $this->line("Total Loan Amount: " . number_format($totalAmount, 2) . " TZS");
        $this->newLine();
        
        // Generate payment link
        $this->info('🔧 Generating Payment Link...');
        
        try {
            $response = $paymentService->generateLoanInstallmentsPaymentLink(
                $loanId,
                $client,
                $loanSchedules,
                [
                    'description' => 'SACCOS Loan Services - Loan ID: ' . $loanId
                ]
            );
            
            if (isset($response['status']) && $response['status'] === 'success') {
                $data = $response['data'] ?? [];
                
                $this->newLine();
                $this->info('✅ SUCCESS: Payment Link Generated!');
                $this->newLine();
                
                $this->info('📊 Response Details:');
                $this->line('   Status: ' . ($response['status'] ?? 'N/A'));
                $this->line('   Message: ' . ($response['message'] ?? 'N/A'));
                $this->line('   Link ID: ' . ($data['link_id'] ?? 'N/A'));
                $this->line('   Short Code: ' . ($data['short_code'] ?? 'N/A'));
                $this->line('   Total Amount: ' . number_format($data['total_amount'] ?? 0, 2) . ' ' . ($data['currency'] ?? 'TZS'));
                $this->line('   Expires At: ' . ($data['expires_at'] ?? 'N/A'));
                $this->newLine();
                
                $this->info('🔗 Payment URL:');
                $this->line($data['payment_url'] ?? 'N/A');
                $this->newLine();
                
                if (!empty($data['qr_code_data'])) {
                    $this->info('📱 QR Code Data:');
                    $this->line($data['qr_code_data']);
                    $this->newLine();
                }
                
                // Display items
                if (!empty($data['items'])) {
                    $this->info('📦 Payment Items (' . count($data['items']) . ' items):');
                    $itemHeaders = ['Name', 'Reference', 'Amount', 'Required', 'Partial'];
                    $itemRows = [];
                    
                    foreach ($data['items'] as $item) {
                        $itemRows[] = [
                            $item['product_service_name'] ?? 'N/A',
                            $item['product_service_reference'] ?? 'N/A',
                            number_format($item['amount'] ?? 0, 2),
                            ($item['is_required'] ?? false) ? 'Yes' : 'No',
                            ($item['allow_partial'] ?? false) ? 'Yes' : 'No'
                        ];
                    }
                    
                    $this->table($itemHeaders, $itemRows);
                }
                
            } else {
                $this->error('Unexpected response format');
                $this->line(json_encode($response, JSON_PRETTY_PRINT));
            }
            
        } catch (\Exception $e) {
            $this->error('❌ ERROR: ' . $e->getMessage());
            
            if ($this->output->isVerbose()) {
                $this->line('File: ' . $e->getFile());
                $this->line('Line: ' . $e->getLine());
                $this->line('Trace:');
                $this->line($e->getTraceAsString());
            }
        }
        
        $this->newLine();
        $this->info('Test completed!');
    }
}