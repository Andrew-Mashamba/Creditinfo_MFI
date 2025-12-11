<?php

namespace App\Http\Livewire\MembersWebPortal;


use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Models\ClientsModel;
use App\Models\User;
use App\Models\WebPortalUser;
use App\Models\AccountsModel;
use App\Models\SharesModel;
use App\Models\ShareRegister;
use App\Models\Loan;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Notifications\MemberPasswordResetNotification;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class MembersWebPortal extends Component
{
    use WithPagination, WithFileUploads;

    // Authentication properties
    public $showLogin = true;
    public $showRegister = false;
    public $showForgotPassword = false;
    public $showResetPassword = false;
    public $showDashboard = false;
    
    // Login properties
    public $loginIdentifier = ''; // Can be member number, phone, or email
    public $loginPassword = '';
    public $rememberMe = false;
    
    // Registration properties
    public $registrationData = [
        'member_number' => '',
        'phone_number' => '',
        'email' => '',
        'first_name' => '',
        'last_name' => '',
        'password' => '',
        'password_confirmation' => '',
        'terms_accepted' => false
    ];
    
    // Password reset properties
    public $resetEmail = '';
    public $resetToken = '';
    public $newPassword = '';
    public $newPasswordConfirmation = '';
    
    // Dashboard properties
    public $memberData = null;
    public $accounts = [];
    public $recentTransactions = [];
    public $loanSummary = [];
    public $shareSummary = [];
    public $savingsSummary = [];
    
    // Session management
    public $activeSessions = [];
    public $currentSession = null;
    
    protected $listeners = [
        'refreshMemberData' => 'loadMemberData',
        'logoutMember' => 'logout'
    ];

    public function mount()
    {
        // Check if member is already authenticated
        if (Session::has('member_authenticated') && Session::has('member_id')) {
            $this->showDashboard = true;
            $this->loadMemberData();
        }
    }

    public function render()
    {
        return view('livewire.members-web-portal.members-web-portal')
            ->layout('layouts.members-portal');
    }

    // Authentication Methods
    public function showLoginForm()
    {
        $this->resetAuthForms();
        $this->showLogin = true;
        $this->showRegister = false;
        $this->showForgotPassword = false;
        $this->showResetPassword = false;
        $this->showDashboard = false;
    }

    public function showRegistrationForm()
    {
        $this->resetAuthForms();
        $this->showLogin = false;
        $this->showRegister = true;
        $this->showForgotPassword = false;
        $this->showResetPassword = false;
        $this->showDashboard = false;
    }

    public function showForgotPasswordForm()
    {
        $this->resetAuthForms();
        $this->showLogin = false;
        $this->showRegister = false;
        $this->showForgotPassword = true;
        $this->showResetPassword = false;
        $this->showDashboard = false;
    }

    public function login()
    {
        $this->validate([
            'loginIdentifier' => 'required|string',
            'loginPassword' => 'required|string|min:6',
        ], [
            'loginIdentifier.required' => 'Please enter your member number, phone, or email.',
            'loginPassword.required' => 'Please enter your password.',
            'loginPassword.min' => 'Password must be at least 6 characters.',
        ]);

        // Find portal user by identifier (username, email, phone, or client_number)
        $portalUser = WebPortalUser::where(function($query) {
                $query->where('username', $this->loginIdentifier)
                      ->orWhere('email', $this->loginIdentifier)
                      ->orWhere('phone', $this->loginIdentifier)
                      ->orWhere('client_number', $this->loginIdentifier);
            })
            ->with('client')
            ->first();

        if (!$portalUser) {
            throw ValidationException::withMessages([
                'loginIdentifier' => 'Member not found. Please check your credentials.',
            ]);
        }

        // Check if account is locked
        if ($portalUser->isAccountLocked()) {
            throw ValidationException::withMessages([
                'loginIdentifier' => 'Account is locked. Please contact the SACCO office or try again later.',
            ]);
        }

        // Check if account is active
        if (!$portalUser->canLogin()) {
            throw ValidationException::withMessages([
                'loginIdentifier' => 'Account is not active. Please contact the SACCO office.',
            ]);
        }

        // Verify password
        if (!Hash::check($this->loginPassword, $portalUser->password_hash)) {
            $portalUser->recordFailedLogin();
            
            throw ValidationException::withMessages([
                'loginPassword' => 'Invalid password. Please try again.',
            ]);
        }

        // Record successful login
        $portalUser->recordSuccessfulLogin();
        
        // Start session
        $sessionId = Session::getId();
        $portalUser->startSession($sessionId);

        // Create session data
        Session::put('member_authenticated', true);
        Session::put('portal_user_id', $portalUser->id);
        Session::put('member_id', $portalUser->client_id);
        Session::put('member_number', $portalUser->client_number);
        Session::put('member_name', $portalUser->getFullNameAttribute());
        
        if ($this->rememberMe) {
            Session::put('member_remember_token', Str::random(60));
        }

        // Check if password change is required
        if ($portalUser->needsPasswordChange()) {
            Session::put('force_password_change', true);
        }

        // Log successful login
        $this->logMemberActivity($portalUser->id, 'login', 'Member logged in successfully');

        $this->showDashboard = true;
        $this->loadMemberData();
        
        $this->dispatchBrowserEvent('member-logged-in', [
            'message' => 'Welcome back, ' . $portalUser->getFullNameAttribute() . '!'
        ]);
    }

    public function register()
    {
        $this->validate([
            'registrationData.member_number' => 'required|string|exists:clients,client_number',
            'registrationData.phone_number' => 'required|string|exists:clients,mobile_phone_number',
            'registrationData.email' => 'required|email|exists:clients,email',
            'registrationData.first_name' => 'required|string|min:2',
            'registrationData.last_name' => 'required|string|min:2',
            'registrationData.password' => 'required|string|min:8|confirmed',
            'registrationData.terms_accepted' => 'required|accepted',
        ], [
            'registrationData.member_number.exists' => 'Member number not found in our records.',
            'registrationData.phone_number.exists' => 'Phone number not found in our records.',
            'registrationData.email.exists' => 'Email not found in our records.',
            'registrationData.terms_accepted.accepted' => 'You must accept the terms and conditions.',
        ]);

        // Verify member exists and data matches
        $member = ClientsModel::where('client_number', $this->registrationData['member_number'])
            ->where('mobile_phone_number', $this->registrationData['phone_number'])
            ->where('email', $this->registrationData['email'])
            ->where('first_name', $this->registrationData['first_name'])
            ->where('last_name', $this->registrationData['last_name'])
            ->first();

        if (!$member) {
            throw ValidationException::withMessages([
                'registrationData.member_number' => 'Member information does not match our records.',
            ]);
        }

        // Check if portal access already exists
        if ($member->portal_access_enabled) {
            throw ValidationException::withMessages([
                'registrationData.member_number' => 'Portal access is already enabled for this member.',
            ]);
        }

        // Enable portal access and set password
        $member->update([
            'portal_access_enabled' => true,
            'password_hash' => Hash::make($this->registrationData['password']),
            'portal_registered_at' => now(),
        ]);

        // Send welcome email
        $this->sendWelcomeEmail($member);

        $this->dispatchBrowserEvent('member-registered', [
            'message' => 'Registration successful! You can now log in to your portal.'
        ]);

        $this->showLoginForm();
    }

    public function forgotPassword()
    {
        $this->validate([
            'resetEmail' => 'required|email',
        ]);

        $member = ClientsModel::where('email', $this->resetEmail)
            ->where('portal_access_enabled', true)
            ->first();

        if (!$member) {
            throw ValidationException::withMessages([
                'resetEmail' => 'No member found with this email address or portal access not enabled.',
            ]);
        }

        // Generate reset token
        $token = Str::random(60);
        $member->update([
            'password_reset_token' => $token,
            'password_reset_expires_at' => now()->addHours(24),
        ]);

        // Send reset email
        $member->notify(new MemberPasswordResetNotification($token));

        $this->dispatchBrowserEvent('password-reset-sent', [
            'message' => 'Password reset instructions have been sent to your email.'
        ]);

        $this->showLoginForm();
    }

    public function resetPassword()
    {
        $this->validate([
            'resetToken' => 'required|string',
            'newPassword' => 'required|string|min:8|confirmed',
        ]);

        $member = ClientsModel::where('password_reset_token', $this->resetToken)
            ->where('password_reset_expires_at', '>', now())
            ->first();

        if (!$member) {
            throw ValidationException::withMessages([
                'resetToken' => 'Invalid or expired reset token.',
            ]);
        }

        // Update password and clear reset token
        $member->update([
            'password_hash' => Hash::make($this->newPassword),
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);

        $this->dispatchBrowserEvent('password-reset-success', [
            'message' => 'Password has been reset successfully. You can now log in.'
        ]);

        $this->showLoginForm();
    }

    public function logout()
    {
        $memberId = Session::get('member_id');
        
        if ($memberId) {
            $this->logMemberActivity($memberId, 'logout', 'Member logged out');
        }

        Session::forget([
            'member_authenticated',
            'member_id',
            'member_number',
            'member_name',
            'member_remember_token'
        ]);

        $this->showLoginForm();
        
        $this->dispatchBrowserEvent('member-logged-out', [
            'message' => 'You have been logged out successfully.'
        ]);
    }

    // Dashboard Methods
    public function loadMemberData()
    {
        $memberId = Session::get('member_id');
        if (!$memberId) {
            return;
        }

        $this->memberData = ClientsModel::find($memberId);
        
        if ($this->memberData) {
            $this->loadAccounts();
            $this->loadRecentTransactions();
            $this->loadLoanSummary();
            $this->loadShareSummary();
            $this->loadSavingsSummary();
            $this->loadActiveSessions();
        }
    }

    public function loadAccounts()
    {
        $memberNumber = Session::get('member_number');
        
        $this->accounts = AccountsModel::where('client_number', $memberNumber)
            ->with('shareProduct')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'account_number' => $account->account_number,
                    'account_type' => $account->account_type,
                    'current_balance' => $account->current_balance,
                    'product_name' => $account->shareProduct->name ?? 'N/A',
                    'status' => $account->status,
                ];
            });
    }

    public function loadRecentTransactions()
    {
        $memberNumber = Session::get('member_number');
        
        $this->recentTransactions = Transaction::where('client_number', $memberNumber)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'amount' => $transaction->amount,
                    'type' => $transaction->type,
                    'narration' => $transaction->narration,
                    'status' => $transaction->status,
                    'created_at' => $transaction->created_at->format('M d, Y H:i'),
                ];
            });
    }

    public function loadLoanSummary()
    {
        $memberNumber = Session::get('member_number');
        
        $loans = Loan::where('client_number', $memberNumber)
            ->where('status', 'ACTIVE')
            ->get();

        $this->loanSummary = [
            'total_loans' => $loans->count(),
            'total_balance' => $loans->sum('outstanding_balance'),
            'next_payment' => $loans->min('next_payment_date'),
            'overdue_amount' => $loans->where('is_overdue', true)->sum('overdue_amount'),
        ];
    }

    public function loadShareSummary()
    {
        $memberNumber = Session::get('member_number');

        $shares = ShareRegister::where('member_id', $this->memberData->id)
            ->where('status', 'ACTIVE')
            ->get();

        $this->shareSummary = [
            'total_shares' => $shares->sum('current_share_balance'),
            'total_value' => $shares->sum('total_share_value'),
            'share_types' => $shares->count(),
        ];
    }

    public function loadSavingsSummary()
    {
        $memberNumber = Session::get('member_number');
        
        $savings = Saving::where('client_number', $memberNumber)
            ->get();

        $this->savingsSummary = [
            'total_balance' => $savings->sum('current_balance'),
            'accounts_count' => $savings->count(),
        ];
    }

    public function loadActiveSessions()
    {
        $memberId = Session::get('member_id');
        
        // This would typically come from a sessions table
        // For now, we'll show current session info
        $this->currentSession = [
            'id' => Session::getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'last_activity' => now()->format('M d, Y H:i:s'),
        ];
    }

    // Session Management
    public function terminateSession($sessionId)
    {
        // This would terminate a specific session
        // For now, we'll just log the action
        $this->logMemberActivity(Session::get('member_id'), 'session_terminated', 'Session terminated by user');
        
        $this->dispatchBrowserEvent('session-terminated', [
            'message' => 'Session terminated successfully.'
        ]);
    }

    // Utility Methods
    private function resetAuthForms()
    {
        $this->loginIdentifier = '';
        $this->loginPassword = '';
        $this->rememberMe = false;
        $this->registrationData = [
            'member_number' => '',
            'phone_number' => '',
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'password' => '',
            'password_confirmation' => '',
            'terms_accepted' => false
        ];
        $this->resetEmail = '';
        $this->resetToken = '';
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';
    }

    private function logMemberActivity($memberId, $action, $description)
    {
        // Log member activity for audit trail
        Log::info('Member Activity', [
            'member_id' => $memberId,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]);
    }

    private function sendWelcomeEmail($member)
    {
        // Send welcome email to new portal user
        Mail::send('emails.member-welcome', [
            'member' => $member,
            'portal_url' => route('members.portal')
        ], function ($message) use ($member) {
            $message->to($member->email, $member->getFullNameAttribute())
                    ->subject('Welcome to SACCO Members Portal');
        });
    }
}
