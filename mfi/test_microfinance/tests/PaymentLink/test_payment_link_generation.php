<?php

/**
 * Test Script for Payment Link Generation Service
 * 
 * This script tests the PaymentLinkService::generateLoanInstallmentsPaymentLink() method
 * with realistic test data to ensure proper payment link generation.
 * 
 * Usage: php tests/PaymentLink/test_payment_link_generation.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../bootstrap/app.php';

use App\Services\PaymentLinkService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Boot Laravel application
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "========================================\n";
echo "  Payment Link Generation Test Script   \n";
echo "========================================\n\n";

try {
    // Test Configuration
    $testLoanId = 1001; // Test loan ID
    $testClientNumber = 'MEMBER2001';
    
    echo "📋 Test Configuration:\n";
    echo "   - Loan ID: $testLoanId\n";
    echo "   - Client Number: $testClientNumber\n\n";
    
    // Create test client object
    $client = new stdClass();
    $client->id = 2001;
    $client->client_number = $testClientNumber;
    $client->first_name = 'Simon';
    $client->middle_name = 'Peter';
    $client->last_name = 'Mpembee';
    $client->present_surname = 'Mpembee';
    $client->phone_number = '0742099713'; // Will be formatted to 255742099713
    $client->email = 'mpembeesimon@email.com';
    
    echo "👤 Test Client Data:\n";
    echo "   - Name: {$client->first_name} {$client->middle_name} {$client->last_name}\n";
    echo "   - Phone: {$client->phone_number}\n";
    echo "   - Email: {$client->email}\n\n";
    
    // Create test loan schedules (installments)
    $loanSchedules = [];
    $baseDate = Carbon::now();
    $principalPerInstallment = 100000; // 100,000 TZS
    $interestPerInstallment = 15000;   // 15,000 TZS
    $numberOfInstallments = 6;          // 6 months loan
    
    echo "📅 Creating Test Loan Schedules:\n";
    echo "   - Number of Installments: $numberOfInstallments\n";
    echo "   - Principal per Installment: " . number_format($principalPerInstallment, 2) . " TZS\n";
    echo "   - Interest per Installment: " . number_format($interestPerInstallment, 2) . " TZS\n\n";
    
    for ($i = 1; $i <= $numberOfInstallments; $i++) {
        $schedule = new stdClass();
        $schedule->id = 5000 + $i; // Schedule IDs starting from 5001
        $schedule->loan_id = $testLoanId;
        $schedule->installment = $i;
        $schedule->repayment_date = $baseDate->copy()->addMonths($i)->format('Y-m-d');
        $schedule->principle = $principalPerInstallment;
        $schedule->interest = $interestPerInstallment;
        $schedule->penalties = 0; // No penalties for test
        $schedule->charges = ($i == 1) ? 5000 : 0; // 5,000 TZS charge on first installment only
        $schedule->status = 'PENDING';
        
        $loanSchedules[] = $schedule;
        
        $totalAmount = $schedule->principle + $schedule->interest + $schedule->penalties + $schedule->charges;
        echo "   Installment $i:\n";
        echo "     - Due Date: {$schedule->repayment_date}\n";
        echo "     - Amount: " . number_format($totalAmount, 2) . " TZS\n";
    }
    
    // Calculate total loan amount
    $totalLoanAmount = 0;
    foreach ($loanSchedules as $schedule) {
        $totalLoanAmount += $schedule->principle + $schedule->interest + $schedule->penalties + $schedule->charges;
    }
    
    echo "\n💰 Total Loan Amount: " . number_format($totalLoanAmount, 2) . " TZS\n\n";
    
    // Initialize PaymentLinkService
    echo "🔧 Initializing Payment Link Service...\n\n";
    $paymentLinkService = new PaymentLinkService();
    
    // Test different scenarios
    echo "========================================\n";
    echo "         Running Test Scenarios         \n";
    echo "========================================\n\n";
    
    // Test 1: Generate payment link with all installments
    echo "📝 Test 1: Generate Payment Link with All Installments\n";
    echo "----------------------------------------\n";
    
    try {
        $paymentLinkResponse = $paymentLinkService->generateLoanInstallmentsPaymentLink(
            $testLoanId,
            $client,
            $loanSchedules,
            [
                'description' => 'SACCOS Loan Services - Test Loan ID: ' . $testLoanId
            ]
        );
        
        echo "✅ SUCCESS: Payment link generated!\n\n";
        
        // Display response details
        if (isset($paymentLinkResponse['status']) && $paymentLinkResponse['status'] === 'success') {
            $data = $paymentLinkResponse['data'] ?? [];
            
            echo "📊 Response Details:\n";
            echo "   - Status: " . ($paymentLinkResponse['status'] ?? 'N/A') . "\n";
            echo "   - Message: " . ($paymentLinkResponse['message'] ?? 'N/A') . "\n";
            echo "   - Link ID: " . ($data['link_id'] ?? 'N/A') . "\n";
            echo "   - Short Code: " . ($data['short_code'] ?? 'N/A') . "\n";
            echo "   - Payment URL: " . ($data['payment_url'] ?? 'N/A') . "\n";
            echo "   - QR Code: " . ($data['qr_code_data'] ?? 'N/A') . "\n";
            echo "   - Total Amount: " . number_format($data['total_amount'] ?? 0, 2) . " " . ($data['currency'] ?? 'TZS') . "\n";
            echo "   - Expires At: " . ($data['expires_at'] ?? 'N/A') . "\n";
            echo "   - Customer Reference: " . ($data['customer_reference'] ?? 'N/A') . "\n";
            echo "   - Customer Name: " . ($data['customer_name'] ?? 'N/A') . "\n";
            echo "   - Customer Phone: " . ($data['customer_phone'] ?? 'N/A') . "\n";
            
            if (!empty($data['items'])) {
                echo "\n📦 Payment Items (" . count($data['items']) . " items):\n";
                foreach ($data['items'] as $index => $item) {
                    echo "   Item " . ($index + 1) . ":\n";
                    echo "     - Name: " . ($item['product_service_name'] ?? 'N/A') . "\n";
                    echo "     - Reference: " . ($item['product_service_reference'] ?? 'N/A') . "\n";
                    echo "     - Amount: " . number_format($item['amount'] ?? 0, 2) . " TZS\n";
                    echo "     - Required: " . (($item['is_required'] ?? false) ? 'Yes' : 'No') . "\n";
                    echo "     - Allow Partial: " . (($item['allow_partial'] ?? false) ? 'Yes' : 'No') . "\n";
                }
            }
            
            echo "\n🔗 Payment Link:\n";
            echo "   " . ($data['payment_url'] ?? 'N/A') . "\n";
            
        } else {
            echo "⚠️  Unexpected Response Format:\n";
            echo json_encode($paymentLinkResponse, JSON_PRETTY_PRINT) . "\n";
        }
        
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . "\n";
        echo "   Line: " . $e->getLine() . "\n";
        echo "   Trace:\n";
        echo $e->getTraceAsString() . "\n";
    }
    
    echo "\n========================================\n\n";
    
    // Test 2: Generate payment link with partial installments
    echo "📝 Test 2: Generate Payment Link with First 3 Installments Only\n";
    echo "----------------------------------------\n";
    
    try {
        $partialSchedules = array_slice($loanSchedules, 0, 3); // First 3 installments only
        
        $paymentLinkResponse = $paymentLinkService->generateLoanInstallmentsPaymentLink(
            $testLoanId,
            $client,
            $partialSchedules,
            [
                'description' => 'SACCOS Loan Services - Partial Payment'
            ]
        );
        
        echo "✅ SUCCESS: Partial payment link generated!\n";
        
        if (isset($paymentLinkResponse['data']['payment_url'])) {
            echo "🔗 Payment URL: " . $paymentLinkResponse['data']['payment_url'] . "\n";
            echo "💰 Total Amount: " . number_format($paymentLinkResponse['data']['total_amount'] ?? 0, 2) . " TZS\n";
        }
        
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n========================================\n\n";
    
    // Test 3: Test with missing phone number
    echo "📝 Test 3: Generate Payment Link with Missing Phone Number\n";
    echo "----------------------------------------\n";
    
    try {
        $clientNoPhone = clone $client;
        $clientNoPhone->phone_number = null;
        
        $paymentLinkResponse = $paymentLinkService->generateLoanInstallmentsPaymentLink(
            $testLoanId,
            $clientNoPhone,
            $loanSchedules,
            [
                'description' => 'SACCOS Loan Services - No Phone Test'
            ]
        );
        
        echo "✅ SUCCESS: Payment link generated without phone!\n";
        
        if (isset($paymentLinkResponse['data']['payment_url'])) {
            echo "🔗 Payment URL: " . $paymentLinkResponse['data']['payment_url'] . "\n";
        }
        
    } catch (Exception $e) {
        echo "❌ ERROR (Expected): " . $e->getMessage() . "\n";
    }
    
    echo "\n========================================\n\n";
    
    // Test 4: Test with custom expiry date
    echo "📝 Test 4: Generate Payment Link with Custom Expiry (7 days)\n";
    echo "----------------------------------------\n";
    
    try {
        $customExpiry = Carbon::now()->addDays(7)->toIso8601String();
        
        $paymentLinkResponse = $paymentLinkService->generateLoanInstallmentsPaymentLink(
            $testLoanId,
            $client,
            $loanSchedules,
            [
                'description' => 'SACCOS Loan Services - Custom Expiry',
                'expires_at' => $customExpiry
            ]
        );
        
        echo "✅ SUCCESS: Payment link with custom expiry generated!\n";
        
        if (isset($paymentLinkResponse['data'])) {
            echo "🔗 Payment URL: " . ($paymentLinkResponse['data']['payment_url'] ?? 'N/A') . "\n";
            echo "⏰ Expires At: " . ($paymentLinkResponse['data']['expires_at'] ?? 'N/A') . "\n";
        }
        
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n========================================\n\n";
    
    // Test 5: Test with empty schedules (should fail)
    echo "📝 Test 5: Generate Payment Link with Empty Schedules (Should Fail)\n";
    echo "----------------------------------------\n";
    
    try {
        $emptySchedules = [];
        
        $paymentLinkResponse = $paymentLinkService->generateLoanInstallmentsPaymentLink(
            $testLoanId,
            $client,
            $emptySchedules,
            [
                'description' => 'SACCOS Loan Services - Empty Test'
            ]
        );
        
        echo "⚠️  UNEXPECTED: Payment link generated with empty schedules!\n";
        
    } catch (Exception $e) {
        echo "✅ EXPECTED ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n========================================\n";
    echo "         Test Summary                   \n";
    echo "========================================\n\n";
    
    echo "✅ Test script completed successfully!\n\n";
    
    echo "📌 Notes:\n";
    echo "   - The payment link API endpoint must be accessible\n";
    echo "   - Check the logs at storage/logs/laravel-*.log for details\n";
    echo "   - Payment links are stored in the 'payment_links' table\n";
    echo "   - The actual API response depends on the external service\n\n";
    
} catch (Exception $e) {
    echo "\n❌ FATAL ERROR:\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
    echo "   Trace:\n";
    echo $e->getTraceAsString() . "\n\n";
    exit(1);
}

echo "========================================\n\n";
exit(0);