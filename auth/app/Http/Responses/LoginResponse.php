<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = Auth::user();
        
        // Check if user has MFI portal assignment
        if ($user && $user->port && $user->mfi_code) {
            // Generate SSO parameters
            $timestamp = time();
            $authToken = bin2hex(random_bytes(32));
            
            // Store auth token in database for validation (optional)
            // Or use a shared secret approach
            $sharedSecret = env('SSO_SHARED_SECRET', 'nbc-saccos-sso-shared-secret-2025');
            $signature = hash_hmac('sha256', 
                $authToken . $timestamp . $user->mfi_code, 
                $sharedSecret
            );
            
            // Build the SSO URL with parameters
            $mfiPortalUrl = $user->getMfiPortalUrl();
            $ssoParams = http_build_query([
                'auth_token' => $authToken,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'source_system' => 'auth_portal',
                'timestamp' => $timestamp,
                'signature' => $signature
            ]);
            
            // Redirect to MFI portal with SSO parameters
            return redirect($mfiPortalUrl . '/auth?' . $ssoParams);
        }
        
        // Default redirect if no MFI portal assigned
        return redirect()->intended(config('fortify.home', '/dashboard'));
    }
}