<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentLinkController;

/*
|--------------------------------------------------------------------------
| Payment Link API Routes
|--------------------------------------------------------------------------
|
| Here are the payment link API routes for generating payment URLs
|
*/

Route::prefix('api/payment-links')->middleware(['api'])->group(function () {
    
    // Generate payment URL (returns only URL)
    Route::post('/generate-url', [PaymentLinkController::class, 'generatePaymentUrl']);
    
    // Generate payment link (returns full response)
    Route::post('/generate', [PaymentLinkController::class, 'generatePaymentLink']);
    
    // Generate member payment link (shares and deposits)
    Route::post('/member', [PaymentLinkController::class, 'generateMemberPaymentLink']);
    
    // Generate loan payment link
    Route::post('/loan', [PaymentLinkController::class, 'generateLoanPaymentLink']);
    
    // Check payment status
    Route::get('/{linkId}/status', [PaymentLinkController::class, 'checkPaymentStatus']);
});

/*
|--------------------------------------------------------------------------
| Example API Requests
|--------------------------------------------------------------------------
|
| 1. Generate Payment URL Only:
| POST /api/payment-links/generate-url
| Response: { "success": true, "payment_url": "http://172.240.241.188/pay/fVbxJnB0" }
|
| 2. Generate Member Payment Link:
| POST /api/payment-links/member
| {
|   "member_reference": "MEMBER2001",
|   "member_name": "Sarah Johnson",
|   "member_phone": "0723456789",
|   "member_email": "sarah@email.com",
|   "shares_amount": 200000,
|   "deposits_amount": 500000
| }
|
| 3. Generate Loan Payment Link:
| POST /api/payment-links/loan
| {
|   "loan_reference": "LOAN2025001",
|   "member_name": "John Doe",
|   "member_phone": "0712345678",
|   "amount": 150000
| }
|
| 4. Check Payment Status:
| GET /api/payment-links/{linkId}/status
|
*/