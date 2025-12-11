<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\approvals;
use App\Models\departmentsList;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class WebRoutesController extends Controller
{
    public function passwordReset(Request $request)
    {
        $email = $request->input('email');

        // Check if email is registered
        if (User::where('email',$email)->get()->count() == 1) {
            Session::put('status',null);

            // Create/update password reset approval process record
            $update_value = approvals::updateOrCreate(
                [
                    'process_id' => $email,
                    'user_id' => null
                ],
                [
                    'institution' => '',
                    'process_name' => 'passwordReset',
                    'process_description' => 'Password Reset request for user with email - '.$email,
                    'approval_process_description' => '',
                    'process_code' => '35',
                    'process_id' => null,
                    'process_status' => 'PENDING',
                    'approval_status' => 'PENDING',
                    'user_id'  => null,
                    'team_id'  => '',
                    'reset_email' => $email,
                    'edit_package'=> null
                ]
            );

            $adminRoleId = departmentsList::where('department_name', 'ADMINISTRATION')->value('id');
            $admins = User::where('department', $adminRoleId)->get();
            foreach ($admins as $admin ){
                $this->composeEmail($admin->email, 'Dear '.$admin->name.', User '. User::where('email', $email)->value('name') .' has requested to reset his/her password');
            }

            // Redirect to main dashboard page
            return redirect()->route('system');

        } else {
            // Email is not registered, redirect to password reset form with error message
            Session::put('status',"This password is not registered");
            return redirect()->route('password.request');
        }
    }

    public function verifyUser()
    {
        return view('otp');
    }

    public function verifyAccount()
    {
        $user = auth()->user(); // Get the current user

        // Check if user already has an OTP or if the existing OTP has expired
        if ($user->otp_time) {
            if($user->verification_status == '0' || now()->diffInMinutes($user->otp_time) >= config('auth.otp_validity_period')){
                // Generate new OTP using OtpService
                $otpService = app(\App\Services\OtpService::class);
                $result = $otpService->generateOtp($user);
                
                if ($result['success']) {
                    return redirect()->route('verify-user');
                } else {
                    Session::flash('error', $result['message']);
                    return redirect()->route('verify-user');
                }
            } else {
                // User has a valid OTP, redirect to main dashboard page
                Session::put('status',null);
                return redirect()->route('system');
            }
        } else {
            // Generate a new OTP using OtpService
            $otpService = app(\App\Services\OtpService::class);
            $result = $otpService->generateOtp($user);
            
            if ($result['success']) {
                return redirect()->route('verify-user');
            } else {
                Session::flash('error', $result['message']);
                return redirect()->route('verify-user');
            }
        }
    }

    public function fallback()
    {
        return view('pages/utility/404');
    }

    public function downloadPdf()
    {
        if (session()->has('pdf_download_data')) {
            $data = session()->get('pdf_download_data');
            $content = base64_decode($data['content']);
            $filename = $data['filename'];
            
            session()->forget('pdf_download_data');
            
            return response($content)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }
        
        return redirect()->back();
    }

    public function downloadCsv()
    {
        if (session()->has('csv_download_data')) {
            $data = session()->get('csv_download_data');
            $content = $data['content'];
            $filename = $data['filename'];
            
            session()->forget('csv_download_data');
            
            return response($content)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }
        
        return redirect()->back();
    }

    public function aiAgent()
    {
        return view('ai-agent.chat');
    }

    public function aiAgentTest()
    {
        $aiAgent = new \App\Services\AiAgentService();
        $response = $aiAgent->processRequest('How many liability accounts?', [
            'user_id' => auth()->id(),
            'user_permissions' => [],
            'session_id' => session()->getId()
        ]);
        
        return response()->json([
            'success' => true,
            'response' => $response
        ]);
    }

    public function testAiConversation()
    {
        $memoryService = new \App\Services\AiMemoryService('test_session_' . time());
        $result = $memoryService->addInteraction(
            'Test user message: ' . now(),
            'Test AI response: ' . now(),
            [],
            ['user_id' => auth()->id() ?? 1]
        );
        
        $conversations = $memoryService->getConversationList(10);
        
        return response()->json([
            'saved' => $result,
            'conversations' => $conversations,
            'user_id' => auth()->id() ?? 1
        ]);
    }

    private function composeEmail($to, $message)
    {
        // Implement email composition logic here
        // This would typically use Laravel's Mail facade
        // For now, just a placeholder
        \Log::info("Email would be sent to: $to with message: $message");
    }
}