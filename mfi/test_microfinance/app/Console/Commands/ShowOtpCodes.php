<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class ShowOtpCodes extends Command
{
    protected $signature = 'otp:show {email?}';
    protected $description = 'Show OTP codes for users (for testing when email is not working)';

    public function handle()
    {
        $email = $this->argument('email');
        
        if ($email) {
            $user = User::where('email', $email)->first();
            if (!$user) {
                $this->error("User with email {$email} not found");
                return 1;
            }
            
            $this->showUserOtp($user);
        } else {
            // Show all recent OTPs
            $users = User::whereNotNull('otp_hash')
                ->where('otp_expires_at', '>', Carbon::now())
                ->get();
                
            if ($users->isEmpty()) {
                $this->info("No active OTP codes found");
                return 0;
            }
            
            $this->info("Active OTP Codes:");
            $this->info(str_repeat("-", 80));
            
            foreach ($users as $user) {
                $this->showUserOtp($user);
                $this->info(str_repeat("-", 80));
            }
        }
        
        return 0;
    }
    
    private function showUserOtp($user)
    {
        // Check the OTP log file for the actual OTP code
        $logFile = storage_path('logs/otp-' . date('Y-m-d') . '.log');
        
        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);
            
            // Find the most recent OTP for this user
            $pattern = '/OTP delivery failed.*"user_email":"' . preg_quote($user->email, '/') . '".*"otp_code":"(\d+)"/';
            
            if (preg_match_all($pattern, $logs, $matches)) {
                $otpCode = end($matches[1]);
                
                $this->info("User: {$user->name}");
                $this->info("Email: {$user->email}");
                $this->info("OTP Code: <fg=yellow;options=bold>{$otpCode}</>");
                $this->info("Expires: {$user->otp_expires_at}");
                
                return;
            }
        }
        
        // If not found in logs, show hashed info
        $this->info("User: {$user->name}");
        $this->info("Email: {$user->email}");
        $this->info("OTP Status: Active (check logs for code)");
        $this->info("Expires: {$user->otp_expires_at}");
    }
}