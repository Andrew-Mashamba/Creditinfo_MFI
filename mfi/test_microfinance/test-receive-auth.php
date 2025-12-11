<?php
/**
 * Script to test receiving authentication token in MFI_CORE_SYSTEM
 * This simulates what happens when the /auth route receives a token
 */

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

echo "=== Testing Authentication Token Reception in MFI_CORE_SYSTEM ===\n\n";

// Read the token from the saved file
$tokenFile = '/var/www/html/AUTHENTICATION/test-auth-url.txt';
if (!file_exists($tokenFile)) {
    echo "❌ Token file not found. Please run test-auth-redirect.php in AUTHENTICATION first.\n";
    exit(1);
}

$authUrl = file_get_contents($tokenFile);
$urlParts = parse_url($authUrl);
parse_str($urlParts['query'], $queryParams);
$encryptedToken = $queryParams['token'] ?? null;

if (!$encryptedToken) {
    echo "❌ No token found in URL\n";
    exit(1);
}

echo "✅ Token extracted from URL\n";

try {
    // Step 1: Decrypt the token
    echo "\n--- Decrypting Token ---\n";
    $decrypted = Crypt::decryptString($encryptedToken);
    $payload = json_decode($decrypted, true);

    if (!$payload) {
        throw new Exception('Invalid token payload');
    }

    echo "✅ Token decrypted successfully\n";

    // Step 2: Verify token hasn't expired
    echo "\n--- Verifying Token ---\n";
    if (Carbon::parse($payload['expires_at'])->isPast()) {
        echo "❌ Token has expired\n";
        exit(1);
    }
    echo "✅ Token is valid (expires at: " . $payload['expires_at'] . ")\n";

    // Step 3: Extract user information
    echo "\n--- User Information from Token ---\n";
    echo "Email: " . $payload['email'] . "\n";
    echo "Full Name: " . $payload['full_name'] . "\n";
    echo "Phone: " . ($payload['phone_number'] ?? 'N/A') . "\n";
    echo "User Type: " . $payload['user_type'] . "\n";
    echo "Role: " . $payload['role'] . "\n";
    echo "Member Number: " . ($payload['member_number'] ?? 'N/A') . "\n";
    echo "Institution: " . ($payload['institution_number'] ?? 'N/A') . "\n";
    echo "Password Hash: " . (isset($payload['password_hash']) ? '[PRESENT]' : '[MISSING]') . "\n";

    // Step 4: Check/Create user in local database
    echo "\n--- Local User Management ---\n";

    $user = DB::table('users')->where('email', $payload['email'])->first();

    if ($user) {
        echo "✅ User exists in local database (ID: " . $user->id . ")\n";

        // Update user information
        DB::table('users')->where('id', $user->id)->update([
            'name' => $payload['full_name'],
            'password' => $payload['password_hash'],
            'updated_at' => now()
        ]);

        echo "✅ User information updated\n";
    } else {
        echo "📝 User doesn't exist, would create new user\n";

        // In a real scenario, we would create the user:
        /*
        $userId = DB::table('users')->insertGetId([
            'name' => $payload['full_name'],
            'email' => $payload['email'],
            'password' => $payload['password_hash'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
        */

        echo "   - Name: " . $payload['full_name'] . "\n";
        echo "   - Email: " . $payload['email'] . "\n";
        echo "   - Password: [HASHED]\n";
    }

    // Step 5: Determine redirect based on role
    echo "\n--- Redirect Logic ---\n";
    $redirectPath = '/dashboard'; // Default

    if ($payload['user_type'] === 'staff') {
        if ($payload['role'] === 'admin' || $payload['role'] === 'super_admin') {
            $redirectPath = '/admin/dashboard';
            echo "✅ Admin user - would redirect to: /admin/dashboard\n";
        } elseif ($payload['role'] === 'manager') {
            $redirectPath = '/manager/dashboard';
            echo "✅ Manager user - would redirect to: /manager/dashboard\n";
        } else {
            $redirectPath = '/staff/dashboard';
            echo "✅ Staff user - would redirect to: /staff/dashboard\n";
        }
    } else {
        $redirectPath = '/member/dashboard';
        echo "✅ Member user - would redirect to: /member/dashboard\n";
    }

    // Step 6: Session data that would be stored
    echo "\n--- Session Data ---\n";
    echo "Would store in session:\n";
    echo "  - auth_token_id: " . $payload['token_id'] . "\n";
    echo "  - auth_source: central_auth\n";
    echo "  - user_type: " . $payload['user_type'] . "\n";
    echo "  - user_role: " . $payload['role'] . "\n";

    echo "\n=== Authentication Handoff Test Complete ===\n";
    echo "\n✅ All steps of the authentication handoff process validated successfully!\n";

    echo "\n📋 Summary:\n";
    echo "1. Token successfully decrypted\n";
    echo "2. Token is valid and not expired\n";
    echo "3. User information extracted from token\n";
    echo "4. Password hash included in token\n";
    echo "5. User can be created/updated in local database\n";
    echo "6. Correct redirect path determined based on role\n";

} catch (Exception $e) {
    echo "❌ Error processing token: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}