<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OtpController extends Controller
{
    public function verifyOtp(Request $request)
    {
        $otp = $request->input('otp');
        $storedOtp = Session::get('otp');

        if ($otp === $storedOtp) {
            Session::put('otp_verified', true);
            return redirect()->intended('/dashboard');
        } else {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }
    }

    public function generateOtp()
    {
        $otp = rand(100000, 999999);
        Session::put('otp', $otp);
        // Send OTP to user via email or SMS
        // Example: Mail::to($user->email)->send(new OtpMail($otp));
        return view('otp.verify');
    }
}
