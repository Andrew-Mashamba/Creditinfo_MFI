<?php

use App\Models\departmentsList;
use App\Http\Livewire\VerifyOtp;
use App\Models\approvals;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataFeedController;
use App\Http\Controllers\SSOAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthenticationController;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Traits\MailSender;
use App\Migrations\CreateRegionsTable;
use App\Migrations\CreateDistrictsTable;
use App\Migrations\CreateWardsTable;


use App\Http\Controllers\TableExportController;
use App\Http\Controllers\BillingController;
use App\Http\Livewire\Users\Users;
use App\Http\Livewire\Users\Roles;
use App\Http\Livewire\Users\Permissions;
use App\Http\Livewire\Users\UserSettings;
use App\Http\Livewire\Users\AuditLogs;
use App\Http\Livewire\Departments\Departments;
use App\Http\Livewire\Loans\LoanCommittee;


// SECURITY: Rate limited to 20 write operations per minute
Route::post('/export-table', [TableExportController::class, 'exportTableData'])
    ->name('export.table')
    ->middleware('throttle:api-write');


Route::get('/microfinance_registration_link',[\App\Http\Controllers\CompanyRequest::class,'index']);

// SECURITY: Rate limited to 5 uploads per minute + 3 registrations per hour per IP
Route::post('registration/submition',[\App\Http\Controllers\CompanyRequest::class,'create'])
    ->name('saccossRequestForm')
    ->middleware(['throttle:uploads', 'throttle:registration']);

// NBC Routes - Must be placed before redirect to avoid conflicts
Route::get('/NBC/{memberNumber}/{clientNumber}', [App\Http\Controllers\NbcController::class, 'showMemberInfo'])
    ->name('nbc.member.info')
    ->where('memberNumber', '[0-9]+')
    ->where('clientNumber', '[0-9]+');

// SECURITY: Rate limited to 10 sensitive operations per minute
Route::post('/NBC/process-payment', [App\Http\Controllers\NbcController::class, 'processPayment'])
    ->name('nbc.process.payment')
    ->middleware('throttle:sensitive');

// Redirect to local authentication system with SSO simulation
Route::get('/', function(\Illuminate\Http\Request $request) {
    // Check if user is already authenticated
    if (\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('system');
    }
    
    // Check if SSO parameters are present (coming from auth system)
    if ($request->has('auth_token') && $request->has('user_email')) {
        // Redirect to /auth with the SSO parameters
        return redirect('/auth?' . http_build_query($request->all()));
    }
    
    // For testing: simulate SSO authentication with a test user
    // In production, this would redirect to the actual auth portal
    $testUser = 'test.admin@testmicrofinance.co.tz';
    $timestamp = time();
    $authToken = bin2hex(random_bytes(32));
    
    // Build SSO URL with parameters
    $ssoParams = http_build_query([
        'auth_token' => $authToken,
        'user_email' => $testUser,
        'user_name' => 'Test Admin',
        'source_system' => 'auth_portal',
        'timestamp' => $timestamp
    ]);
    
    // Redirect to /auth with SSO parameters
    return redirect('/auth?' . $ssoParams);
});

// Redirect /login to central authentication system (override Fortify default)
Route::get('/login', function() {
    return redirect()->away('http://127.0.0.1:8003/');
})->name('login');

// Redirect /register to central authentication system (no self-registration allowed)
Route::get('/register', function() {
    return redirect()->away('http://127.0.0.1:8003/');
})->name('register');

// DISABLED: Authentication handoff from central auth system
// Using SSO Authentication instead (see line 275)
// Route::match(['get', 'post'], '/auth', [AuthenticationController::class, 'handleAuth'])
//     ->name('central.auth')
//     ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
//     ->middleware('throttle:auth');

// Disable password reset - redirect to central authentication
Route::get('/forgot-password', function() {
    return redirect()->away('http://127.0.0.1:8003/');
})->name('password.request');

Route::get('/reset-password/{token}', function() {
    return redirect()->away('http://127.0.0.1:8003/');
})->name('password.reset');

// Disable two-factor challenge - redirect to central authentication
Route::get('/two-factor-challenge', function() {
    return redirect()->away('http://127.0.0.1:8003/');
})->name('two-factor.login');

// Disable password confirmation - redirect to central authentication
Route::get('/user/confirm-password', function() {
    return redirect()->away('http://127.0.0.1:8003/');
})->name('password.confirm');

// Group routes that require authentication
Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    // Route for the main dashboard page
    Route::get('/system', \App\Http\Livewire\System::class)->name('system');

    // Members Portal Route
    Route::get('/members', App\Http\Livewire\MembersWebPortal\MembersWebPortal::class)->name('members.portal');

    // Route for OTP verification page
    Route::get('/verify-user', [\App\Http\Controllers\WebRoutesController::class, 'verifyUser'])->name('verify-user');

    // Route for generating and verifying OTP
    Route::get('/verify-account', [\App\Http\Controllers\WebRoutesController::class, 'verifyAccount'])->name('verify-account');

    // Mandatory Savings Management Route
    Route::get('/mandatory-savings', \App\Http\Livewire\Savings\MandatorySavingsManagement::class)->name('mandatory-savings');

    // Till and Cash Management Route (Development Access)
    Route::get('/till-cash-management', \App\Http\Livewire\Accounting\TillAndCashManagement::class)->name('till-cash-management');
    
    // Budget Management Routes
    Route::get('/budget-management', \App\Http\Livewire\BudgetManagement\EnhancedBudgetManager::class)->name('budget.management');
    Route::get('/budget-dashboard', \App\Http\Livewire\BudgetManagement\BudgetDashboard::class)->name('budget.dashboard');
    Route::get('/budget/report/view/{id}', function($id) {
        return redirect()->route('budget.management')->with('message', 'Report generated successfully');
    })->name('budget.report.view');

    Route::fallback([\App\Http\Controllers\WebRoutesController::class, 'fallback']);
});

Route::middleware(['auth'])->group(function () {
    // Billing Routes
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/create', [BillingController::class, 'create'])->name('billing.create');

    // SECURITY: Rate limited to 10 sensitive operations per minute
    Route::post('/billing', [BillingController::class, 'store'])
        ->name('billing.store')
        ->middleware('throttle:sensitive');

    Route::get('/billing/{bill}', [BillingController::class, 'show'])->name('billing.show');

    // SECURITY: Rate limited to 10 sensitive operations per minute
    Route::post('/billing/{bill}/payment', [BillingController::class, 'processPayment'])
        ->name('billing.process-payment')
        ->middleware('throttle:sensitive');

    Route::get('/billing/status/{controlNumber}', [BillingController::class, 'checkStatus'])->name('billing.check-status');
    
    // Luku Payment Route
    Route::view('/payments/luku', 'payments.luku')->name('payments.luku');

    // GEPG Payment Route
    Route::view('/payments/gepg', 'payments.gepg')->name('payments.gepg');
    
    // Trade Receivables Invoice Routes
    Route::get('/receivables/invoice/view', function() {
        $invoiceId = session('view_invoice_id');
        if (!$invoiceId) {
            abort(404, 'Invoice not found');
        }
        
        $receivable = \DB::table('trade_receivables')->where('id', $invoiceId)->first();
        if (!$receivable || !$receivable->invoice_file_path) {
            abort(404, 'Invoice file not found');
        }
        
        $path = storage_path('app/' . $receivable->invoice_file_path);
        if (!file_exists($path)) {
            abort(404, 'Invoice file does not exist');
        }
        
        // Clear session
        session()->forget('view_invoice_id');
        
        return response()->file($path);
    })->name('view.invoice');
    
    Route::get('/receivables/invoice/download', function() {
        $path = session('download_invoice_path');
        $name = session('download_invoice_name', 'invoice.pdf');
        
        if (!$path || !file_exists($path)) {
            abort(404, 'Invoice file not found');
        }
        
        // Clear session
        session()->forget(['download_invoice_path', 'download_invoice_name']);
        
        return response()->download($path, $name);
    })->name('download.invoice');
});

// Payment Notification Webhooks
// SECURITY: Rate limited to 60 requests per minute (external callbacks)
Route::post('/api/payment-notification', [BillingController::class, 'handlePaymentNotification'])
    ->name('billing.payment-notification')
    ->middleware('throttle:api');

Route::post('/api/gepg-callback', [BillingController::class, 'handleGepgCallback'])
    ->name('gepg.callback')
    ->middleware('throttle:api');

// PDF Download Route
Route::get('/download-pdf', [\App\Http\Controllers\WebRoutesController::class, 'downloadPdf'])->name('download.pdf');

// CSV Download Route
Route::get('/download-csv', [\App\Http\Controllers\WebRoutesController::class, 'downloadCsv'])->name('download.csv');

// Share Certificate Download Route
Route::get('/share-certificate/download', function() {
    $data = session('certificate_data');
    $clientNumber = session('certificate_client');

    if (!$data || !$clientNumber) {
        abort(404, 'Certificate data not found');
    }

    // Clear session
    session()->forget(['certificate_data', 'certificate_client']);

    // Generate PDF
    $pdf = \PDF::loadView('pdfs.share-certificate', ['data' => $data]);

    return $pdf->download('Share_Certificate_' . $clientNumber . '_' . now()->format('Ymd') . '.pdf');
})->middleware('auth')->name('share.certificate.download');

// Loan Loss Report Download Route
Route::get('/loan-loss-report/download', [\App\Http\Controllers\LoanLossReportController::class, 'downloadCustomReport'])
    ->middleware('auth')
    ->name('loan-loss-report.download');

// AI Agent Routes - REMOVED

// Email Routes
Route::middleware('auth')->group(function () {
    Route::get('/email', App\Http\Livewire\Email\Email::class)->name('email');
    Route::get('/email-outlook', App\Http\Livewire\Email\EmailOutlook::class)->name('email.outlook');
    Route::get('/email/signatures', App\Http\Livewire\Email\EmailSignatures::class)->name('email.signatures');
    Route::get('/email/templates', App\Http\Livewire\Email\EmailTemplates::class)->name('email.templates');
    Route::get('/email/rules', App\Http\Livewire\Email\EmailRules::class)->name('email.rules');
});

// Email Tracking Routes (no auth required for tracking)
Route::get('/email/track/pixel/{id}', [App\Http\Controllers\EmailTrackingController::class, 'trackPixel'])->name('email.tracking.pixel');
Route::get('/email/track/click/{tracking_id}', [App\Http\Controllers\EmailTrackingController::class, 'trackClick'])->name('email.tracking.click');

// Status message route
Route::get('/status/{status}', \App\Http\Livewire\StatusMessage::class)
    ->name('status.message')
    ->where('status', 'PENDING|BLOCKED|DELETED');
// Redirect to local authentication system with SSO simulation
Route::get('/', function(\Illuminate\Http\Request $request) {
    // Check if user is already authenticated
    if (\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('system');
    }
    
    // Check if SSO parameters are present (coming from auth system)
    if ($request->has('auth_token') && $request->has('user_email')) {
        // Redirect to /auth with the SSO parameters
        return redirect('/auth?' . http_build_query($request->all()));
    }
    
    // For testing: simulate SSO authentication with a test user
    // In production, this would redirect to the actual auth portal
    $testUser = 'test.admin@testmicrofinance.co.tz';
    $timestamp = time();
    $authToken = bin2hex(random_bytes(32));
    
    // Build SSO URL with parameters
    $ssoParams = http_build_query([
        'auth_token' => $authToken,
        'user_email' => $testUser,
        'user_name' => 'Test Admin',
        'source_system' => 'auth_portal',
        'timestamp' => $timestamp
    ]);
    
    // Redirect to /auth with SSO parameters
    return redirect('/auth?' . $ssoParams);
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/system/logs', [App\Http\Controllers\SystemLogsController::class, 'index'])->name('system.logs');
});

// Test AI Routes - REMOVED for security reasons
// Terminal Console Route - REMOVED for security reasons










// SSO Authentication Routes
// GET route for SSO authentication (redirects from auth system)
Route::get('/auth', function(\Illuminate\Http\Request $request) {
    // Extract SSO parameters from query string
    $authToken = $request->query('auth_token');
    $userEmail = $request->query('user_email');
    $userName = $request->query('user_name', '');
    $sourceSystem = $request->query('source_system', 'auth_portal');
    $timestamp = $request->query('timestamp', time());
    
    // If no parameters provided, redirect to auth system
    if (!$authToken || !$userEmail) {
        return redirect()->away('http://127.0.0.1:8003/');
    }
    
    // Generate signature
    $sharedSecret = env('SSO_SHARED_SECRET', 'nbc-saccos-sso-shared-secret-2025');
    $targetSystem = 'test_microfinance';
    $signature = hash_hmac('sha256', $authToken . $timestamp . $targetSystem, $sharedSecret);
    
    // Create form and auto-submit to POST /auth
    return response()->make('
        <html>
        <body>
            <form id="sso-form" action="/auth" method="POST">
                ' . csrf_field() . '
                <input type="hidden" name="auth_token" value="' . htmlspecialchars($authToken) . '">
                <input type="hidden" name="source_system" value="' . htmlspecialchars($sourceSystem) . '">
                <input type="hidden" name="timestamp" value="' . htmlspecialchars($timestamp) . '">
                <input type="hidden" name="signature" value="' . htmlspecialchars($signature) . '">
                <input type="hidden" name="user_email" value="' . htmlspecialchars($userEmail) . '">
                <input type="hidden" name="user_name" value="' . htmlspecialchars($userName) . '">
            </form>
            <script>document.getElementById("sso-form").submit();</script>
            <p>Authenticating...</p>
        </body>
        </html>
    ');
})->name('sso.auth.get');

// SECURITY: Rate limited to 5 authentication attempts per minute
Route::post('/auth', [SSOAuthController::class, 'authenticate'])
    ->name('sso.authenticate')
    ->middleware('throttle:auth');

// SECURITY: Rate limited to 10 sensitive operations per minute
Route::post('/sso-logout', [SSOAuthController::class, 'logout'])
    ->name('sso.logout')
    ->middleware('throttle:sensitive');

// Logout route alias (for compatibility with Jetstream/Fortify components)
Route::post('/logout', [SSOAuthController::class, 'logout'])
    ->name('logout')
    ->middleware('throttle:sensitive');


// Secure File Serving Routes
// All file access goes through authorization checks
Route::middleware(['auth'])->group(function () {
    Route::get('/secure-files/{disk}/{path}', [\App\Http\Controllers\SecureFileController::class, 'serve'])
        ->where('path', '.*')
        ->name('secure-file.serve');

    Route::get('/secure-files/download/{disk}/{path}', [\App\Http\Controllers\SecureFileController::class, 'download'])
        ->where('path', '.*')
        ->name('secure-file.download');
});

// CSRF Token Refresh Endpoint (for auto-refresh JavaScript)
// SECURITY: Final piece for 100% CSRF protection
Route::get('/csrf-token-refresh', function() {
    return response()->json([
        'token' => csrf_token(),
        'timestamp' => time()
    ]);
})->middleware('auth');
