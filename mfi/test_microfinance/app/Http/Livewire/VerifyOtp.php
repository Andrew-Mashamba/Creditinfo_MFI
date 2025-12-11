<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class VerifyOtp extends Component
{
    public $otp = '';
    public $errorMessage = '';
    public $successMessage = '';
    protected $otpService;
    protected $logger;

    protected $rules = [
        'otp' => 'required|digits:6'
    ];

    protected $listeners = [
        'debug' => 'handleDebug'
    ];

    public function __construct()
    {
        $this->logger = Log::channel('otp');
    }

    public function boot(OtpService $otpService)
    {
        $this->logger->info('Initializing VerifyOtp component', [
            'timestamp' => now()->toDateTimeString(),
            'session_id' => session()->getId(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
        $this->otpService = $otpService;
    }

    public function mount()
    {
        $user = Auth::user();
        $this->logger->info('Mounting VerifyOtp component', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => $user ? $user->id : null,
            'email' => $user ? $user->email : null,
            'phone_number' => $user ? $user->phone_number : null,
            'session_id' => session()->getId(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    public function render()
    {
        Log::debug('Rendering VerifyOtp component', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => Auth::id(),
            'has_error' => !empty($this->errorMessage),
            'has_success' => !empty($this->successMessage)
        ]);
        return view('livewire.verify-otp');
    }

    public function handleDebug($message)
    {
        Log::debug('Debug message from frontend', [
            'message' => $message,
            'timestamp' => now()->toDateTimeString(),
            'user_id' => Auth::id()
        ]);
    }

    public function updated($property)
    {
        if ($property === 'otp') {
            // Clear error message when user starts typing
            $this->errorMessage = '';

            // Auto-submit when 6 digits are entered
            if (strlen($this->otp) === 6) {
                $this->submitOTP();
            }
        }
    }

    public function submitOTP()
    {
        $user = Auth::user();
        $this->logger->info('Starting OTP submission process', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => $user ? $user->id : null,
            'email' => $user ? $user->email : null,
            'phone_number' => $user ? $user->phone_number : null,
            'session_id' => session()->getId(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'otp_length' => strlen($this->otp)
        ]);

        try {
            $this->validate();

            if (!$user) {
                $this->logger->warning('OTP submission failed: User not authenticated', [
                    'timestamp' => now()->toDateTimeString(),
                    'session_id' => session()->getId(),
                    'ip' => request()->ip()
                ]);

                $this->errorMessage = 'User not authenticated';
                return;
            }

            $result = $this->otpService->validateOtp($user, $this->otp);

            if ($result['success']) {
                $this->logger->info('OTP validation successful', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'timestamp' => now()->toDateTimeString(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                $this->successMessage = $result['message'];
                $this->errorMessage = '';
                $this->redirect(route('system'));
            } else {
                $this->logger->warning('OTP validation failed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'timestamp' => now()->toDateTimeString(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'message' => $result['message']
                ]);

                $this->errorMessage = $result['message'];
                $this->otp = '';
            }
        } catch (\Exception $e) {
            $this->logger->error('OTP verification failed with exception', [
                'user_id' => Auth::id(),
                'timestamp' => now()->toDateTimeString(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => session()->getId(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            $this->errorMessage = 'An error occurred while verifying the code. Please try again.';
        }
    }

    public function resendOTP()
    {
        $this->logger->info('=== RESEND OTP BUTTON CLICKED ===', [
            'timestamp' => now()->toDateTimeString(),
            'session_id' => session()->getId(),
            'ip' => request()->ip(),
            'method' => 'resendOTP'
        ]);

        $user = Auth::user();
        
        $this->logger->info('Starting OTP resend process - User Check', [
            'timestamp' => now()->toDateTimeString(),
            'user_exists' => $user ? 'YES' : 'NO',
            'user_id' => $user ? $user->id : 'NULL',
            'email' => $user ? $user->email : 'NULL',
            'phone_number' => $user ? $user->phone_number : 'NULL',
            'session_id' => session()->getId(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        try {
            if (!$user) {
                $this->logger->error('OTP resend CRITICAL ERROR: User not authenticated', [
                    'timestamp' => now()->toDateTimeString(),
                    'session_id' => session()->getId(),
                    'ip' => request()->ip(),
                    'auth_check' => Auth::check() ? 'TRUE' : 'FALSE',
                    'auth_id' => Auth::id()
                ]);

                $this->errorMessage = 'User not authenticated';
                return;
            }

            $this->logger->info('Calling OtpService->generateOtp()', [
                'user_id' => $user->id,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'timestamp' => now()->toDateTimeString()
            ]);

            $result = $this->otpService->generateOtp($user);

            $this->logger->info('OtpService->generateOtp() Response', [
                'success' => $result['success'] ?? 'NULL',
                'message' => $result['message'] ?? 'NULL',
                'user_id' => $user->id,
                'timestamp' => now()->toDateTimeString()
            ]);

            if ($result['success']) {
                $this->logger->info('✓ OTP RESEND SUCCESSFUL', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'new_otp' => $user->fresh()->otp ?? 'NOT_SET',
                    'otp_expires_at' => $user->fresh()->otp_expires_at ?? 'NOT_SET',
                    'timestamp' => now()->toDateTimeString(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'message' => $result['message']
                ]);

                $this->successMessage = $result['message'];
                $this->errorMessage = '';
                $this->otp = '';
                
                $this->logger->info('Emitting otpResent event', [
                    'user_id' => $user->id,
                    'timestamp' => now()->toDateTimeString()
                ]);
                
                $this->emit('otpResent');
            } else {
                $this->logger->error('✗ OTP RESEND FAILED', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'timestamp' => now()->toDateTimeString(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'failure_message' => $result['message'],
                    'result_data' => json_encode($result)
                ]);

                $this->errorMessage = $result['message'];
            }
        } catch (\Exception $e) {
            $this->logger->error('✗✗✗ OTP RESEND EXCEPTION ✗✗✗', [
                'user_id' => Auth::id(),
                'timestamp' => now()->toDateTimeString(),
                'exception_class' => get_class($e),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'session_id' => session()->getId(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            $this->errorMessage = 'An error occurred while resending the code. Please try again.';
        }
        
        $this->logger->info('=== RESEND OTP COMPLETED ===', [
            'timestamp' => now()->toDateTimeString(),
            'has_error' => !empty($this->errorMessage),
            'error_message' => $this->errorMessage,
            'success_message' => $this->successMessage
        ]);
    }

    public function logout()
    {
        Log::info('User logging out from OTP verification', [
            'user_id' => Auth::id(),
            'timestamp' => now()->toDateTimeString(),
            'session_id' => session()->getId()
        ]);

        Auth::guard('web')->logout();
        Session::flush();
        return redirect()->route('login');
    }
}
