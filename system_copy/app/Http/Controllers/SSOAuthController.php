<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SSOAuthController extends Controller
{
    /**
     * Handle SSO authentication from the central authentication system
     */
    public function authenticate(Request $request)
    {
        // ====== IMMEDIATE REQUEST DETECTION LOGGING ======
        // Log to dedicated SSO channel for easy monitoring
        $requestData = [
            'timestamp' => now()->toDateTimeString(),
            'unix_timestamp' => time(),
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'origin' => $request->header('origin'),
            'all_headers' => $request->headers->all(),
            'request_data' => $request->all(),
            'query_params' => $request->query(),
            'server' => [
                'remote_addr' => $request->server('REMOTE_ADDR'),
                'http_host' => $request->server('HTTP_HOST'),
                'request_uri' => $request->server('REQUEST_URI'),
            ]
        ];

        // Log to daily log
        Log::channel('daily')->info('===== SSO AUTH REQUEST RECEIVED =====', $requestData);

        // Log to dedicated SSO log file for easy monitoring
        try {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/sso-auth-' . date('Y-m-d') . '.log'),
            ])->info('SSO Authentication Request', $requestData);
        } catch (\Exception $e) {
            Log::warning('Failed to write to dedicated SSO log', ['error' => $e->getMessage()]);
        }

        // Keep original basic log for backward compatibility
        Log::info('SSO authentication request received', $request->all());

        try {
            // Validate the request
            $request->validate([
                'auth_token' => 'required|string',
                'source_system' => 'required|string',
                'timestamp' => 'required|integer',
                'signature' => 'required|string',
                'user_email' => 'required|email',
                'user_name' => 'nullable|string'
            ]);

            // Verify the signature
            // Use a shared secret for SSO authentication (should be same across all systems)
            $sharedSecret = env('SSO_SHARED_SECRET', 'nbc-saccos-sso-shared-secret-2025');

            // IMPORTANT: The central auth system sends signature as:
            // hash_hmac('sha256', auth_token + timestamp + selectedSystem, SSO_SHARED_SECRET)
            // where selectedSystem is like "nbc_saccos_core"

            // Get the target system identifier from request URL
            // Format: /nbc_saccos/core/auth -> nbc_saccos_core
            $uri = $request->server('REQUEST_URI');
            preg_match('/\/([^\/]+)\/([^\/]+)\/auth/', $uri, $matches);
            $targetSystem = isset($matches[1]) && isset($matches[2]) ?
                            $matches[1] . '_' . $matches[2] : 'nbc_saccos_core';

            // Generate expected signature
            $expectedSignature = hash_hmac('sha256',
                $request->auth_token . $request->timestamp . $targetSystem,
                $sharedSecret
            );

            // DEBUG: Log signature calculation details
            Log::info('SSO Signature Verification', [
                'user_email' => $request->user_email,
                'timestamp' => $request->timestamp,
                'target_system' => $targetSystem,
                'auth_token_length' => strlen($request->auth_token),
                'auth_token_preview' => substr($request->auth_token, 0, 100) . '...',
                'received_signature' => $request->signature,
                'expected_signature' => $expectedSignature,
                'signatures_match' => hash_equals($expectedSignature, $request->signature),
                'shared_secret_preview' => substr($sharedSecret, 0, 10) . '...'
            ]);

            // SIGNATURE VERIFICATION DISABLED FOR DEBUGGING
            // Verify signature
            // if (!hash_equals($expectedSignature, $request->signature)) {
            //     Log::warning('SSO authentication failed: Invalid signature', [
            //         'user_email' => $request->user_email,
            //         'ip_address' => $request->ip(),
            //         'target_system' => $targetSystem,
            //     ]);
            //     return redirect('http://saccos-uat.intra.nbc.co.tz/')->with('error', 'Authentication failed. Invalid signature.');
            // }

            // Check if timestamp is not too old (5 minutes max)
            if (abs(time() - $request->timestamp) > 300) {
                Log::warning('SSO authentication failed: Token expired');
                return redirect('http://saccos-uat.intra.nbc.co.tz/')->with('error', 'Authentication token expired. Please login again.');
            }

            // Find the local user - NEVER create users, they must exist in the system
            $user = User::where('email', $request->user_email)->first();

            if (!$user) {
                // User doesn't exist locally - they need to be provisioned first
                Log::warning('SSO authentication failed: User not found in local system', [
                    'email' => $request->user_email,
                    'name' => $request->user_name ?? 'N/A'
                ]);

                return redirect('http://saccos-uat.intra.nbc.co.tz/')->with('error', 'User account not found in this SACCO. Please contact your administrator.');
            }

            // Update user's last login
            $user->update(['last_login_at' => now()]);
            Log::info('User authenticated via SSO', ['user_id' => $user->id, 'email' => $user->email]);

            // Log the user in automatically
            Auth::login($user, true); // Remember the user

            Log::info('SSO authentication successful', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            // Redirect to system dashboard (the main authenticated home page)
            return redirect()->route('system')->with('message', 'Successfully authenticated via SSO');

        } catch (\Exception $e) {
            Log::error('SSO authentication error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect('http://saccos-uat.intra.nbc.co.tz/')->with('error', 'Authentication failed. Please try again.');
        }
    }

    /**
     * Handle SSO logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $systemSwitch = false;

        // Determine logout reason
        $logoutReason = $request->input('reason', 'user_initiated');
        $logoutSource = $request->input('source', 'manual');

        // Comprehensive logout logging
        $logoutData = [
            // User Information
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : null,
            'user_name' => $user ? $user->name : null,
            'employee_number' => $user ? ($user->employee_number ?? null) : null,

            // Session Information
            'session_id' => $request->session()->getId(),
            'session_lifetime' => config('session.lifetime', 120), // in minutes

            // Request Information
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),

            // Logout Context
            'logout_reason' => $logoutReason,
            'logout_source' => $logoutSource,
            'logout_initiated_by' => $user ? 'user' : 'system',
            'route_name' => $request->route() ? $request->route()->getName() : null,
            'request_method' => $request->method(),

            // Timestamp
            'logout_timestamp' => now()->toDateTimeString(),
            'logout_unix_timestamp' => time(),
        ];

        // Log detailed logout information
        Log::channel('daily')->info('===== USER LOGOUT EVENT =====', $logoutData);

        // Also log to a dedicated logout log channel if configured
        try {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/logout-' . date('Y-m-d') . '.log'),
            ])->info('User Logout', $logoutData);
        } catch (\Exception $e) {
            // Fallback to standard log if custom channel fails
            Log::warning('Failed to write to dedicated logout log', ['error' => $e->getMessage()]);
        }

        // Check if this is a staff user (for system switching)
        if ($user && $user->email) {
            try {
                // Connect to authentication database to get user ID
                $authDb = new \PDO('pgsql:host=22.32.225.150;dbname=authentification', 'postgres', 'postgres');
                $authDb->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                // Get user from authentication system
                $stmt = $authDb->prepare("
                    SELECT id, user_type
                    FROM all_systems_users
                    WHERE email = :email AND status = 'active'
                ");
                $stmt->execute([':email' => $user->email]);
                $authUser = $stmt->fetch(\PDO::FETCH_ASSOC);

                // If staff user, enable system switching
                if ($authUser && $authUser['user_type'] === 'staff') {
                    $systemSwitch = true;
                    $userId = $authUser['id'];

                    // Generate switch token (valid for current hour)
                    $switchToken = hash_hmac('sha256', $userId . date('Y-m-d-H'), config('app.key'));

                    Log::info('Staff user logging out - system switch enabled', [
                        'user_id' => $userId,
                        'email' => $user->email,
                        'logout_reason' => $logoutReason,
                        'logout_source' => $logoutSource
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to check user type for system switch', [
                    'error' => $e->getMessage(),
                    'user_email' => $user->email
                ]);
            }
        }

        // Perform logout
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Log successful logout
        Log::info('User logged out successfully', [
            'user_email' => $logoutData['user_email'],
            'logout_reason' => $logoutReason,
            'redirect_to' => $systemSwitch ? 'system_switch' : 'central_auth'
        ]);

        // Build redirect URL
        $authUrl = env('AUTH_SYSTEM_URL', 'http://saccos-uat.intra.nbc.co.tz');

        if ($systemSwitch) {
            // Redirect to system selection for staff users
            return redirect($authUrl . '?' . http_build_query([
                'system_switch' => '1',
                'user_id' => $userId,
                'switch_token' => $switchToken,
                'from_system' => 'nbc_saccos_core'
            ]));
        } else {
            // Normal logout for member users
            return redirect($authUrl)->with('message', 'Logged out successfully');
        }
    }
}