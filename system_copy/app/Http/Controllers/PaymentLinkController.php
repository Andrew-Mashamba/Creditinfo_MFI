<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PaymentLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class PaymentLinkController extends Controller
{
    private $paymentLinkService;
    
    public function __construct(PaymentLinkService $paymentLinkService)
    {
        $this->paymentLinkService = $paymentLinkService;
    }
    
    /**
     * Generate a payment link and return the payment URL
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generatePaymentUrl(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'description' => 'required|string',
                'target' => 'required|in:individual,business',
                'customer_reference' => 'required|string',
                'customer_name' => 'required|string',
                'customer_phone' => 'required|string',
                'customer_email' => 'nullable|email',
                'expires_at' => 'nullable|date',
                'items' => 'required|array|min:1',
                'items.*.type' => 'required|in:service,product',
                'items.*.product_service_reference' => 'required|string',
                'items.*.product_service_name' => 'required|string',
                'items.*.amount' => 'required|numeric|min:0',
                'items.*.is_required' => 'boolean',
                'items.*.allow_partial' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Get payment URL directly
            $paymentUrl = $this->paymentLinkService->getPaymentUrl($request->all());
            
            return response()->json([
                'success' => true,
                'payment_url' => $paymentUrl,
                'message' => 'Payment link generated successfully'
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate a payment link with full response details
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generatePaymentLink(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'description' => 'required|string',
                'target' => 'required|in:individual,business',
                'customer_reference' => 'required|string',
                'customer_name' => 'required|string',
                'customer_phone' => 'required|string',
                'customer_email' => 'nullable|email',
                'expires_at' => 'nullable|date',
                'items' => 'required|array|min:1',
                'items.*.type' => 'required|in:service,product',
                'items.*.product_service_reference' => 'required|string',
                'items.*.product_service_name' => 'required|string',
                'items.*.amount' => 'required|numeric|min:0',
                'items.*.is_required' => 'boolean',
                'items.*.allow_partial' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Generate payment link
            $response = $this->paymentLinkService->generateUniversalPaymentLink($request->all());
            
            return response()->json($response);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate payment link for member (shares and deposits)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateMemberPaymentLink(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'member_reference' => 'required|string',
                'member_name' => 'required|string',
                'member_phone' => 'required|string',
                'member_email' => 'required|email',
                'shares_amount' => 'required|numeric|min:0',
                'deposits_amount' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'expires_at' => 'nullable|date',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // At least one amount must be greater than 0
            if ($request->shares_amount <= 0 && $request->deposits_amount <= 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'At least one payment amount (shares or deposits) must be greater than 0'
                ], 422);
            }
            
            // Generate member payment link
            $response = $this->paymentLinkService->generateMemberPaymentLink(
                $request->member_reference,
                $request->member_name,
                $request->member_phone,
                $request->member_email,
                $request->shares_amount,
                $request->deposits_amount,
                $request->only(['description', 'expires_at'])
            );
            
            return response()->json([
                'success' => true,
                'payment_url' => $response['data']['payment_url'],
                'link_id' => $response['data']['link_id'],
                'short_code' => $response['data']['short_code'],
                'total_amount' => $response['data']['total_amount'],
                'expires_at' => $response['data']['expires_at']
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate loan repayment link
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateLoanPaymentLink(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'loan_reference' => 'required|string',
                'member_name' => 'required|string',
                'member_phone' => 'required|string',
                'amount' => 'required|numeric|min:1',
                'member_email' => 'nullable|email',
                'description' => 'nullable|string',
                'allow_partial' => 'nullable|boolean',
                'expires_at' => 'nullable|date',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Generate loan payment URL
            $paymentUrl = $this->paymentLinkService->generateLoanPaymentUrl(
                $request->loan_reference,
                $request->member_name,
                $request->member_phone,
                $request->amount,
                [
                    'email' => $request->member_email,
                    'description' => $request->description,
                    'allow_partial' => $request->allow_partial ?? true,
                    'expires_at' => $request->expires_at
                ]
            );
            
            return response()->json([
                'success' => true,
                'payment_url' => $paymentUrl,
                'message' => 'Loan payment link generated successfully'
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check payment status
     * 
     * @param string $linkId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPaymentStatus($linkId)
    {
        try {
            $status = $this->paymentLinkService->checkPaymentStatus($linkId);
            
            return response()->json($status);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}