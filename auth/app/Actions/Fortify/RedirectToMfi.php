<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Http\Responses\LoginResponse as FortifyLoginResponse;
use Illuminate\Support\Facades\Auth;

class RedirectToMfi extends FortifyLoginResponse
{
    public function toResponse($request)
    {
        $user = $request->user();
        
        if ($user && method_exists($user, 'getMfiPortalUrl')) {
            // Generate SSO authentication parameters
            $timestamp = time();
            $authToken = bin2hex(random_bytes(32));
            
            // Use shared secret for signature generation
            $sharedSecret = env('SSO_SHARED_SECRET', 'nbc-saccos-sso-shared-secret-2025');
            
            // Get MFI code from user (assuming it's stored in the user model)
            $mfiCode = $user->mfi_code ?? 'test_microfinance';
            
            // Generate signature matching what the MFI portal expects
            $signature = hash_hmac('sha256', 
                $authToken . $timestamp . $mfiCode, 
                $sharedSecret
            );
            
            // Build SSO parameters
            $ssoParams = http_build_query([
                'auth_token' => $authToken,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'source_system' => 'auth_portal',
                'timestamp' => $timestamp
            ]);
            
            // Get MFI portal URL and append /auth endpoint with SSO params
            $mfiUrl = $user->getMfiPortalUrl();
            $ssoUrl = $mfiUrl . '/auth?' . $ssoParams;
            
            // Log the SSO redirect for debugging
            \Log::info('SSO Redirect to MFI Portal', [
                'user_email' => $user->email,
                'mfi_code' => $mfiCode,
                'mfi_url' => $mfiUrl,
                'redirect_url' => $ssoUrl
            ]);
            
            return redirect($ssoUrl);
        }
        
        return redirect()->intended(config('fortify.home'));
    }
}