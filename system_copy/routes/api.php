<?php

use App\Services\DisbursementService;
use App\Services\LoanScheduleServiceVersionTwo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanDecisionController;

use App\Http\Controllers\BillerController;

use App\Http\Controllers\PaymentCallbackController;


use App\Http\Controllers\LukuCallbackController;

use App\Http\Controllers\Api\BillingController;

use App\Http\Controllers\LukuGatewayController;

use App\Models\RoleMenuAction;

use App\Http\Controllers\Api\TransactionProcessingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('test', [\App\Http\Controllers\TestController::class, 'test']);

Route::post('testApi',[DisbursementService::class,'testAPi'])->name('api.loan_test');

Route::middleware('auth:sanctum')->get('/user', [\App\Http\Controllers\TestController::class, 'getUser']);

Route::post('institution-product-info',[\App\Http\Controllers\InstitutionInformationApi::class,'getInstitution'])->name('institution-info');
Route::post('bank_funds_transfer_request',[\App\Http\Controllers\InstitutionInformationApi::class,'internalBankTransfer'])->name('institution-request');
//Route::get('bank_funds_transfer_request', function (){
//    return 123;
//});



Route::post('/loan-decision', [LoanDecisionController::class, 'processLoanDecision']);




// Route::prefix('billers')->group(function () {
//     Route::get('/', [BillerController::class, 'index']);
//     Route::get('/category/{category}', [BillerController::class, 'byCategory']);
// });


Route::post('/nbc/payment/callback', [PaymentCallbackController::class, 'handlePaymentCallback'])->name('nbc.payment.callback');


Route::post('/luku/callback', [LukuCallbackController::class, 'handleCallback'])->name('luku.callback');


// // GEPG Routes
// Route::prefix('gepg')->group(function () {
//     Route::get('/payment', \App\Http\Livewire\GepgPaymentProcessor::class)->name('gepg.payment');
//     Route::post('/callback', [\App\Http\Controllers\GepgCallbackController::class, 'handleCallback'])->name('gepg.callback');
// });

// NBC Payment Callback
Route::post('v1/nbc-payments/callback', [App\Http\Controllers\Api\V1\NbcPaymentCallbackController::class, 'handle'])
    ->name('api.v1.nbc-payments.callback');

Route::prefix('billing')->group(function () {
    Route::post('/inquiry', [BillingController::class, 'inquiry']);
    Route::post('/payment-notify', [BillingController::class, 'paymentNotification']);
    Route::post('/status-check', [BillingController::class, 'status']); // updated
});

// Luku Gateway Routes
Route::prefix('luku-gateway')->group(function () {
    Route::post('/meter/lookup', [LukuGatewayController::class, 'meterLookup']);
    Route::post('/payment', [LukuGatewayController::class, 'processPayment']);
    Route::post('/token/status', [LukuGatewayController::class, 'checkTokenStatus']);
    Route::post('/callback', [LukuGatewayController::class, 'paymentCallback']);
});

Route::middleware(['auth:sanctum'])->post('/check-menu-action', [\App\Http\Controllers\TestController::class, 'checkMenuAction']);

// Account Setup Routes
Route::post('/accounts/setup', [App\Http\Controllers\Api\AccountSetupController::class, 'setupAccounts'])
    ->name('api.accounts.setup');

// Account Details API Routes
Route::prefix('v1')->group(function () {
    Route::post('/account-details', [App\Http\Controllers\Api\V1\AccountDetailsController::class, 'getAccountDetails']);
    Route::get('/account-details/test', [App\Http\Controllers\Api\V1\AccountDetailsController::class, 'testConnectivity']);
    Route::get('/account-details/stats', [App\Http\Controllers\Api\V1\AccountDetailsController::class, 'getStatistics']);
});

// Secure API Routes with Authentication and IP Whitelisting
Route::middleware(['api.key', 'ip.whitelist', 'security.headers'])->prefix('secure')->group(function () {
    // Transaction Processing API
    Route::post('/transactions/process', [TransactionProcessingController::class, 'process'])
        ->name('api.secure.transactions.process');
    
    // Transaction Status API
    Route::get('/transactions/{reference}/status', [TransactionProcessingController::class, 'getStatus'])
        ->name('api.secure.transactions.status');
    
    // Transaction History API
    Route::get('/transactions', [TransactionProcessingController::class, 'getHistory'])
        ->name('api.secure.transactions.history');
});

// API Key Management Routes (requires web authentication)
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::apiResource('api-keys', \App\Http\Controllers\Api\ApiKeyController::class);
    Route::post('/api-keys/{id}/regenerate', [\App\Http\Controllers\Api\ApiKeyController::class, 'regenerate'])
        ->name('api.admin.api-keys.regenerate');
    Route::get('/api-keys/{id}/stats', [\App\Http\Controllers\Api\ApiKeyController::class, 'stats'])
        ->name('api.admin.api-keys.stats');
});

// Legacy route (deprecated - use secure route above)
Route::post('/transactions/process', [TransactionProcessingController::class, 'process'])
    ->middleware(['api.key', 'ip.whitelist'])
    ->name('api.transactions.process');

// AI Agent Routes - REMOVED for security reasons

// Test Services API Routes
Route::prefix('test-services')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\ServiceTestController::class, 'listServices'])
        ->name('api.test-services.list');
    
    Route::post('/internal-funds-transfer', [App\Http\Controllers\Api\ServiceTestController::class, 'internalFundsTransfer'])
        ->name('api.test-services.internal-funds-transfer');
    
    Route::post('/external-funds-transfer', [App\Http\Controllers\Api\ServiceTestController::class, 'externalFundsTransfer'])
        ->name('api.test-services.external-funds-transfer');
    
    Route::post('/mini-statement', [App\Http\Controllers\Api\ServiceTestController::class, 'miniStatement'])
        ->name('api.test-services.mini-statement');
    
    Route::post('/full-statement', [App\Http\Controllers\Api\ServiceTestController::class, 'fullStatement'])
        ->name('api.test-services.full-statement');
    
    Route::post('/account-lookup', [App\Http\Controllers\Api\ServiceTestController::class, 'accountLookup'])
        ->name('api.test-services.account-lookup');
    
    Route::post('/utilities', [App\Http\Controllers\Api\ServiceTestController::class, 'utilities'])
        ->name('api.test-services.utilities');
    
    Route::post('/sms', [App\Http\Controllers\Api\ServiceTestController::class, 'sms'])
        ->name('api.test-services.sms');
    
    Route::post('/direct-debit', [App\Http\Controllers\Api\ServiceTestController::class, 'directDebit'])
        ->name('api.test-services.direct-debit');
    
    Route::post('/control-number-payments', [App\Http\Controllers\Api\ServiceTestController::class, 'controlNumberPayments'])
        ->name('api.test-services.control-number-payments');
    
    Route::post('/luku', [App\Http\Controllers\Api\ServiceTestController::class, 'luku'])
        ->name('api.test-services.luku');
    
    Route::post('/gepg', [App\Http\Controllers\Api\ServiceTestController::class, 'gepg'])
        ->name('api.test-services.gepg');
    
    Route::post('/pay-by-link', [App\Http\Controllers\Api\ServiceTestController::class, 'payByLink'])
        ->name('api.test-services.pay-by-link');
});

// Loan Disbursement API Routes
Route::middleware(['api.key', 'ip.whitelist', 'security.headers'])->prefix('v1/loans')->group(function () {
    // Simplified automatic loan creation and disbursement (only requires client_number and amount)
    Route::post('/auto-disburse', [App\Http\Controllers\Api\LoanDisbursementController::class, 'autoDisburse'])
        ->name('api.v1.loans.auto-disburse');

    // Single loan disbursement
    Route::post('/disburse', [App\Http\Controllers\Api\LoanDisbursementController::class, 'disburse'])
        ->name('api.v1.loans.disburse');

    // Bulk loan disbursement
    Route::post('/bulk-disburse', [App\Http\Controllers\Api\LoanDisbursementController::class, 'bulkDisburse'])
        ->name('api.v1.loans.bulk-disburse');

    // Get disbursement status
    Route::get('/disbursement/{transactionId}/status', [App\Http\Controllers\Api\LoanDisbursementController::class, 'status'])
        ->name('api.v1.loans.disbursement.status');
});

// Mock NBC PVAS API Endpoints (for testing without real NBC credentials)
Route::prefix('mock/nbc')->group(function () {
    // Mock Authentication Endpoint
    Route::post('/auth/login', function (Request $request) {
        return response()->json([
            'token' => 'mock_jwt_token_' . bin2hex(random_bytes(16)),
            'expiry' => 86400000 // 24 hours in milliseconds
        ]);
    })->name('mock.nbc.auth.login');

    // Mock Statement Endpoint
    Route::post('/api/v1/casa/statement', function (Request $request) {
        $statementDate = $request->input('statementDate', now()->subDay()->format('Y-m-d'));
        $accountNumber = $request->input('accountNumber', '011191000035');

        // Generate 20 realistic NBC transactions
        $transactions = [];
        $balance = 4500000.00; // Starting balance

        $narrations = [
            'SC|' . now()->format('ymdHis') . '|DEPOSIT|MEMBER SAVINGS',
            'FT|' . now()->format('ymdHis') . '|LOAN DISBURSEMENT',
            'WD|' . now()->format('ymdHis') . '|ATM WITHDRAWAL',
            'SC|' . now()->format('ymdHis') . '|SHARE CAPITAL CONTRIBUTION',
            'FT|' . now()->format('ymdHis') . '|INTERNAL TRANSFER',
            'SC|' . now()->format('ymdHis') . '|LOAN REPAYMENT',
            'FT|' . now()->format('ymdHis') . '|SALARY PAYMENT',
            'SC|' . now()->format('ymdHis') . '|UTILITY PAYMENT',
            'WD|' . now()->format('ymdHis') . '|BRANCH WITHDRAWAL',
            'SC|' . now()->format('ymdHis') . '|DEPOSIT - TELLER',
        ];

        for ($i = 0; $i < 20; $i++) {
            $isDebit = $i % 3 == 0; // Every 3rd transaction is debit
            $amount = rand(50000, 500000); // Random amount between 50k and 500k

            if ($isDebit) {
                $debitAmount = $amount;
                $creditAmount = 0;
                $balance -= $amount;
            } else {
                $debitAmount = 0;
                $creditAmount = $amount;
                $balance += $amount;
            }

            $transactions[] = [
                'transactionDate' => now()->parse($statementDate)->addHours($i)->toIso8601String(),
                'postingDate' => now()->parse($statementDate)->addHours($i)->toIso8601String(),
                'valueDate' => now()->parse($statementDate)->toIso8601String(),
                'currency' => 'TZS',
                'amount' => $amount,
                'balance' => $balance,
                'reference' => '99520' . now()->format('ymd') . str_pad($i + 1, 10, '0', STR_PAD_LEFT),
                'description' => $narrations[$i % count($narrations)],
                'debitCredit' => $isDebit ? 'D' : 'C',
                'debitAmount' => $debitAmount,
                'creditAmount' => $creditAmount
            ];
        }

        return response()->json([
            'statusCode' => 600,
            'message' => 'Successful',
            'serviceCode' => 'SC990003',
            'partnerRef' => $request->input('partnerRef', 'CB' . now()->format('ymdHis')),
            'bankRef' => 'PVS' . now()->format('ymdHis') . rand(1000, 9999),
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'transactions' => $transactions
            ]
        ]);
    })->name('mock.nbc.statement');

    // Mock Balance Endpoint
    Route::post('/api/v1/casa/balance', function (Request $request) {
        return response()->json([
            'statusCode' => 600,
            'message' => 'Successful',
            'serviceCode' => 'SC990001',
            'partnerRef' => $request->input('partnerRef', 'CB' . now()->format('ymdHis')),
            'bankRef' => 'PVS' . now()->format('ymdHis') . rand(1000, 9999),
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'currency' => 'TZS',
                'openingBalance' => 4500000.00,
                'closingBalance' => 4750000.00,
                'totalTransactionsCount' => 20,
                'totalDebitAmount' => 450000.00,
                'totalDebitCount' => 7,
                'totalCreditAmount' => 700000.00,
                'totalCreditCount' => 13
            ]
        ]);
    })->name('mock.nbc.balance');
});
