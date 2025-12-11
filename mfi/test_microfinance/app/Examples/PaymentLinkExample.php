<?php

namespace App\Examples;

use App\Services\PaymentLinkService;
use Exception;

class PaymentLinkExample
{
    private $paymentLinkService;
    
    public function __construct()
    {
        $this->paymentLinkService = new PaymentLinkService();
    }
    
    /**
     * Example 1: Generate payment link for member shares and deposits
     */
    public function generateMemberPaymentExample()
    {
        try {
            // Member details
            $memberReference = 'MEMBER2001';
            $memberName = 'Sarah Johnson';
            $memberPhone = '0723456789'; // or '255723456789'
            $memberEmail = 'sarah@email.com';
            
            // Payment amounts
            $sharesAmount = 200000; // 200,000 TZS
            $depositsAmount = 500000; // 500,000 TZS
            
            // Generate payment link
            $response = $this->paymentLinkService->generateMemberPaymentLink(
                $memberReference,
                $memberName,
                $memberPhone,
                $memberEmail,
                $sharesAmount,
                $depositsAmount
            );
            
            // Extract payment URL
            $paymentUrl = $response['data']['payment_url'];
            
            echo "Payment URL: " . $paymentUrl . PHP_EOL;
            echo "Link ID: " . $response['data']['link_id'] . PHP_EOL;
            echo "Short Code: " . $response['data']['short_code'] . PHP_EOL;
            echo "Total Amount: " . number_format($response['data']['total_amount']) . " TZS" . PHP_EOL;
            
            return $paymentUrl;
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . PHP_EOL;
            return null;
        }
    }
    
    /**
     * Example 2: Get only payment URL directly
     */
    public function getPaymentUrlDirectly()
    {
        try {
            $data = [
                'description' => 'Saccos services',
                'target' => 'individual',
                'customer_reference' => 'MEMBER2001',
                'customer_name' => 'Sarah Johnson',
                'customer_phone' => '255723456789',
                'customer_email' => 'sarah@email.com',
                'expires_at' => '2025-07-27T10:00:00Z',
                'items' => [
                    [
                        'type' => 'service',
                        'product_service_reference' => 'SHARES_01',
                        'product_service_name' => 'MANDATORY SHARES',
                        'amount' => 200000,
                        'is_required' => true,
                        'allow_partial' => false
                    ],
                    [
                        'type' => 'service',
                        'product_service_reference' => 'DEPOSITS_07',
                        'product_service_name' => 'DEPOSITS',
                        'amount' => 500000,
                        'is_required' => true,
                        'allow_partial' => true
                    ]
                ]
            ];
            
            // Get payment URL directly
            $paymentUrl = $this->paymentLinkService->getPaymentUrl($data);
            
            echo "Payment URL: " . $paymentUrl . PHP_EOL;
            
            return $paymentUrl;
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . PHP_EOL;
            return null;
        }
    }
    
    /**
     * Example 3: Generate loan repayment link
     */
    public function generateLoanPaymentExample()
    {
        try {
            $loanReference = 'LOAN2025001';
            $memberName = 'John Doe';
            $memberPhone = '0712345678';
            $repaymentAmount = 150000; // 150,000 TZS
            
            // Get payment URL for loan repayment
            $paymentUrl = $this->paymentLinkService->generateLoanPaymentUrl(
                $loanReference,
                $memberName,
                $memberPhone,
                $repaymentAmount,
                [
                    'email' => 'john.doe@email.com',
                    'allow_partial' => true,
                    'description' => 'Monthly loan repayment'
                ]
            );
            
            echo "Loan Payment URL: " . $paymentUrl . PHP_EOL;
            
            return $paymentUrl;
            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . PHP_EOL;
            return null;
        }
    }
    
    /**
     * Example 4: Check payment status
     */
    public function checkPaymentStatusExample($linkId)
    {
        try {
            $status = $this->paymentLinkService->checkPaymentStatus($linkId);
            
            echo "Payment Status: " . json_encode($status, JSON_PRETTY_PRINT) . PHP_EOL;
            
            return $status;
            
        } catch (Exception $e) {
            echo "Error checking status: " . $e->getMessage() . PHP_EOL;
            return null;
        }
    }
}

// Usage in Laravel Controller
/*
use App\Services\PaymentLinkService;

class PaymentController extends Controller
{
    private $paymentLinkService;
    
    public function __construct(PaymentLinkService $paymentLinkService)
    {
        $this->paymentLinkService = $paymentLinkService;
    }
    
    public function createPaymentLink(Request $request)
    {
        try {
            // Get payment URL
            $paymentUrl = $this->paymentLinkService->getPaymentUrl($request->all());
            
            return response()->json([
                'success' => true,
                'payment_url' => $paymentUrl
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
*/