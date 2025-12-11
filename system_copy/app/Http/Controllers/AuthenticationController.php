<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthenticationController extends Controller
{
    /**
     * Handle authentication token from central authentication system
     * Accepts both GET and POST requests
     */
    public function handleAuth(Request $request)
    {
        // Check for token in both POST and GET parameters
        $token = $request->input('auth_token') ?: $request->get('token');

        // Optional: Verify signature if provided (for POST requests)
        if ($request->has('signature') && $request->has('timestamp')) {
            $expectedSignature = hash_hmac('sha256',
                $token . $request->input('timestamp') . $request->input('source_system', ''),
                config('app.key')
            );

            if (!hash_equals($expectedSignature, $request->input('signature'))) {
                Log::warning('Authentication signature verification failed', [
                    'ip' => $request->ip(),
                    'source' => $request->input('source_system')
                ]);
                // Continue anyway as signature is optional for backward compatibility
            }
        }

        if (!$token) {
            Log::error('Authentication failed: No token provided');
            // Redirect to central authentication system
            return redirect()->away('http://saccos-uat.intra.nbc.co.tz/')
                ->with('error', 'Authentication required. Please login through the central authentication system.');
        }

        try {
            // Decrypt and validate token
            $decrypted = Crypt::decryptString($token);
            $payload = json_decode($decrypted, true);

            if (!$payload) {
                throw new \Exception('Invalid token payload');
            }

            // Verify token hasn't expired
            if (Carbon::parse($payload['expires_at'])->isPast()) {
                Log::warning('Authentication token expired', ['token_id' => $payload['token_id'] ?? 'unknown']);
                return redirect()->away('http://saccos-uat.intra.nbc.co.tz/')
                    ->with('error', 'Authentication token expired. Please login again.');
            }

            // Verify this is the correct target system
            $currentSystem = $this->getCurrentSystemIdentifier();
            if ($payload['target_system'] !== $currentSystem) {
                Log::error('System mismatch in auth token', [
                    'expected' => $currentSystem,
                    'received' => $payload['target_system']
                ]);
                return redirect()->away('http://saccos-uat.intra.nbc.co.tz/')
                    ->with('error', 'Invalid authentication for this system.');
            }

            // Find or create user in local system
            $user = $this->findOrCreateUser($payload);

            if (!$user) {
                throw new \Exception('Failed to create or find user');
            }

            // Regenerate session ID to prevent session fixation attacks
            $request->session()->regenerate();

            // Login the user
            Auth::login($user, true);

            // Store authentication metadata
            session([
                'auth_token_id' => $payload['token_id'],
                'auth_source' => 'central_auth',
                'auth_timestamp' => now()->toIso8601String(),
                'user_type' => $payload['user_type'],
                'user_role' => $payload['role'] ?? 'user'
            ]);

            Log::info('User authenticated via central auth', [
                'user_id' => $user->id,
                'email' => $user->email,
                'token_id' => $payload['token_id']
            ]);

            // Redirect to appropriate dashboard based on user type
            return $this->redirectToDashboard($payload['user_type'], $payload['role'] ?? 'user');

        } catch (\Exception $e) {
            Log::error('Authentication token validation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->away('http://saccos-uat.intra.nbc.co.tz/')
                ->with('error', 'Authentication failed. Please try again.');
        }
    }

    /**
     * Find or create user from authentication payload
     */
    private function findOrCreateUser($payload)
    {
        // Check if user model exists (it should be in the users table)
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        // Try to find user by email
        $user = $userModel::where('email', $payload['email'])->first();

        if (!$user) {
            // Create new user
            $user = new $userModel();
            $user->name = $payload['full_name'];
            $user->email = $payload['email'];
            $user->password = $payload['password_hash']; // Use password hash from auth system

            // Set additional fields if they exist in the model
            $user->phone_number = $payload['phone_number'] ?? null;
            $user->role = $payload['role'] ?? 'user';
            $user->status = 'active';

            $user->save();

            Log::info('Created new user from central auth', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        } else {
            // Update user information from central auth
            $user->name = $payload['full_name'];
            $user->password = $payload['password_hash']; // Update password hash from auth system
            $user->phone_number = $payload['phone_number'] ?? $user->phone_number;
            $user->role = $payload['role'] ?? $user->role;

            $user->save();

            Log::info('Updated existing user from central auth', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        }

        return $user;
    }

    /**
     * Get the current system identifier
     */
    private function getCurrentSystemIdentifier()
    {
        // Extract system identifier from the current URL or config
        $url = request()->getHost() . request()->getRequestUri();

        // Parse URL to get system identifier
        // Format: /saccos_name/system_type/auth
        $parts = explode('/', trim(request()->getRequestUri(), '/'));

        if (count($parts) >= 2) {
            // Reconstruct system identifier
            $saccos = $parts[0];
            $systemType = $parts[1];

            // Return format matching what auth system sends
            return $saccos . '_' . $systemType;
        }

        // Fallback to environment config
        return env('SYSTEM_IDENTIFIER', 'unknown');
    }

    /**
     * Redirect to appropriate dashboard based on user type
     */
    private function redirectToDashboard($userType, $role)
    {
        // IMPORTANT: All users (staff and members) go to /system
        // The System component handles role-based UI/permissions internally
        return redirect()->route('system');
    }

    /**
     * Logout and redirect to central auth
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to central authentication system
        $authUrl = env('CENTRAL_AUTH_URL', 'http://saccos-uat.intra.nbc.co.tz');

        return redirect()->away($authUrl);
    }
}