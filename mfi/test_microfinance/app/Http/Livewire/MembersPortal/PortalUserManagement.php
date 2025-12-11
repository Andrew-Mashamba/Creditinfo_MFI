<?php

namespace App\Http\Livewire\MembersPortal;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\ClientsModel;
use App\Models\User;
use App\Models\WebPortalUser;
use App\Notifications\MemberPortalCredentialsNotification;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class PortalUserManagement extends Component
{
    use WithPagination, WithFileUploads;

    // Search and filter properties
    public $search = '';
    public $filterStatus = 'all';
    public $perPage = 10;

    // Modal properties
    public $showEnablePortalModal = false;
    public $showCredentialsModal = false;
    public $showBulkEnableModal = false;

    // Selected member for portal access
    public $selectedMember = null;
    public $selectedMembers = [];

    // Portal access data
    public $portalData = [
        'auto_generate_password' => true,
        'custom_password' => '',
        'send_credentials_email' => true,
        'send_credentials_sms' => false,
        'portal_permissions' => [
            'view_accounts' => true,
            'view_transactions' => true,
            'view_loans' => true,
            'view_shares' => true,
            'download_statements' => true,
            'update_profile' => false,
        ]
    ];

    // Generated credentials
    public $generatedCredentials = null;

    // Statistics
    public $stats = [
        'total_members' => 0,
        'portal_enabled' => 0,
        'active_users' => 0,
        'pending_activations' => 0
    ];

    protected $listeners = [
        'refreshPortalUsers' => '$refresh',
        'memberSelected' => 'enablePortalAccess'
    ];

    public function mount()
    {
        $this->loadStatistics();
    }

    public function render()
    {
        $members = $this->getMembers();
        return view('livewire.members-portal.portal-user-management', [
            'members' => $members
        ]);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    private function getMembers()
    {
        $query = ClientsModel::query()
            ->leftJoin('web_portal_users', 'clients.id', '=', 'web_portal_users.client_id')
            ->select('clients.*', 'web_portal_users.is_active as portal_active', 
                    'web_portal_users.last_login_at as portal_last_login',
                    'web_portal_users.is_locked as portal_locked',
                    'web_portal_users.id as portal_user_id')
            ->where(function ($q) {
                if ($this->search) {
                    $q->where('clients.client_number', 'like', '%' . $this->search . '%')
                      ->orWhere('clients.first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('clients.last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('clients.email', 'like', '%' . $this->search . '%')
                      ->orWhere('clients.mobile_phone_number', 'like', '%' . $this->search . '%');
                }
            });

        // Apply status filter
        switch ($this->filterStatus) {
            case 'portal_enabled':
                $query->whereNotNull('web_portal_users.id')
                      ->where('web_portal_users.is_active', true);
                break;
            case 'portal_disabled':
                $query->whereNull('web_portal_users.id');
                break;
            case 'recently_registered':
                $query->where('web_portal_users.portal_registered_at', '>=', now()->subDays(7));
                break;
            case 'never_logged_in':
                $query->whereNotNull('web_portal_users.id')
                      ->where('web_portal_users.is_active', true)
                      ->whereNull('web_portal_users.last_login_at');
                break;
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($this->perPage);
    }

    public function loadStatistics()
    {
        $this->stats = [
            'total_members' => ClientsModel::count(),
            'portal_enabled' => WebPortalUser::active()->count(),
            'active_users' => WebPortalUser::active()
                                        ->whereNotNull('last_login_at')
                                        ->count(),
            'pending_activations' => WebPortalUser::active()
                                                ->whereNull('last_login_at')
                                                ->count()
        ];
    }

    public function enablePortalAccess($memberId)
    {
        $this->selectedMember = ClientsModel::find($memberId);

        if (!$this->selectedMember) {
            $this->dispatchBrowserEvent('error', [
                'message' => 'Member not found.'
            ]);
            return;
        }

        // Check if portal user already exists
        $existingPortalUser = WebPortalUser::where('client_id', $this->selectedMember->id)->first();
        if ($existingPortalUser) {
            // If user exists but is disabled, re-enable them
            if (!$existingPortalUser->is_active) {
                // Generate new password for re-enabled user
                $newPassword = Str::random(12);

                $existingPortalUser->update([
                    'is_active' => true,
                    'password' => Hash::make($newPassword),
                    'updated_by' => auth()->id(),
                ]);

                // Register or update user in authentication system
                $this->registerInAuthenticationSystem($this->selectedMember, $newPassword);

                // Prepare credentials for display
                $this->generatedCredentials = [
                    'member_number' => $this->selectedMember->client_number,
                    'email' => $this->selectedMember->email,
                    'phone' => $this->selectedMember->mobile_phone_number,
                    'password' => $newPassword,
                    'portal_url' => route('members.portal')
                ];

                // Send new credentials via email
                try {
                    $this->selectedMember->notify(new MemberPortalCredentialsNotification([
                        'member_name' => $this->selectedMember->getFullNameAttribute(),
                        'member_number' => $this->selectedMember->client_number,
                        'username' => $existingPortalUser->username,
                        'password' => $newPassword,
                        'portal_url' => route('members.portal'),
                        'is_reactivation' => true
                    ]));
                } catch (\Exception $e) {
                    \Log::error('Failed to send re-activation credentials', [
                        'error' => $e->getMessage(),
                        'member' => $this->selectedMember->client_number
                    ]);
                }

                $this->showCredentialsModal = true;

                $this->dispatchBrowserEvent('success', [
                    'message' => 'Portal access has been re-enabled for ' . $this->selectedMember->getFullNameAttribute()
                ]);

                $this->loadStatistics();
                return;
            }

            // If already active, show error
            $this->dispatchBrowserEvent('error', [
                'message' => 'Portal access is already enabled for this member.'
            ]);
            return;
        }

        $this->showEnablePortalModal = true;
    }

    public function processPortalAccess()
    {
        $this->validate([
            'portalData.custom_password' => $this->portalData['auto_generate_password'] ? '' : 'required|min:8',
        ], [
            'portalData.custom_password.required' => 'Please enter a password or enable auto-generate.',
            'portalData.custom_password.min' => 'Password must be at least 8 characters.',
        ]);

        if (!$this->selectedMember) {
            return;
        }

        // Generate or use custom password
        $password = $this->portalData['auto_generate_password'] 
            ? $this->generateSecurePassword() 
            : $this->portalData['custom_password'];

        // Create WebPortalUser record
        $portalUser = WebPortalUser::create([
            'client_id' => $this->selectedMember->id,
            'client_number' => $this->selectedMember->client_number,
            'username' => $this->selectedMember->client_number, // Default to member number
            'email' => $this->selectedMember->email,
            'phone' => $this->selectedMember->mobile_phone_number,
            'password_hash' => $password, // Will be hashed automatically by mutator
            'is_active' => true,
            'permissions' => $this->portalData['portal_permissions'],
            'portal_registered_at' => now(),
            'registered_by' => auth()->id(),
            'created_by' => auth()->id(),
        ]);

        // Store credentials for display
        $this->generatedCredentials = [
            'member_number' => $this->selectedMember->client_number,
            'email' => $this->selectedMember->email,
            'phone' => $this->selectedMember->mobile_phone_number,
            'password' => $password,
            'portal_url' => route('members.portal')
        ];

        // Register user in authentication system
        $this->registerInAuthenticationSystem($this->selectedMember, $password);

        // Send credentials
        if ($this->portalData['send_credentials_email']) {
            $this->sendCredentialsEmail();
        }

        if ($this->portalData['send_credentials_sms']) {
            $this->sendCredentialsSMS();
        }

        $this->showEnablePortalModal = false;
        $this->showCredentialsModal = true;
        $this->loadStatistics();

        $this->dispatchBrowserEvent('portal-access-enabled', [
            'message' => 'Portal access enabled successfully for ' . $this->selectedMember->getFullNameAttribute()
        ]);
    }





    public function bulkEnablePortalAccess()
    {
        if (empty($this->selectedMembers)) {
            $this->dispatchBrowserEvent('error', [
                'message' => 'Please select at least one member.'
            ]);
            return;
        }

        $this->showBulkEnableModal = true;
    }

    public function processBulkPortalAccess()
    {
        if (empty($this->selectedMembers)) {
            return;
        }

        $processed = 0;
        $errors = 0;

        foreach ($this->selectedMembers as $memberId) {
            $member = ClientsModel::find($memberId);
            
            if ($member && !$member->portal_access_enabled) {
                $password = $this->generateSecurePassword();
                
                $member->update([
                    'portal_access_enabled' => true,
                    'password_hash' => Hash::make($password),
                    'portal_registered_at' => now(),
                    'portal_permissions' => json_encode($this->portalData['portal_permissions'])
                ]);

                // Send credentials
                if ($this->portalData['send_credentials_email']) {
                    $this->selectedMember = $member;
                    $this->generatedCredentials = [
                        'member_number' => $member->client_number,
                        'email' => $member->email,
                        'phone' => $member->mobile_phone_number,
                        'password' => $password,
                        'portal_url' => route('members.portal')
                    ];
                    $this->sendCredentialsEmail();
                }

                $processed++;
            } else {
                $errors++;
            }
        }

        $this->selectedMembers = [];
        $this->showBulkEnableModal = false;
        $this->loadStatistics();

        $this->dispatchBrowserEvent('bulk-process-complete', [
            'message' => "Processed: {$processed}, Errors: {$errors}"
        ]);
    }

    private function generateSecurePassword()
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';

        $password = '';

        // Add 7 random capital letters
        for ($i = 0; $i < 7; $i++) {
            $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        }

        // Add 7 random numbers
        for ($i = 0; $i < 7; $i++) {
            $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        }

        // Shuffle the password to mix letters and numbers
        return str_shuffle($password);
    }

    private function sendCredentialsEmail()
    {
        if (!$this->selectedMember || !$this->generatedCredentials) {
            return;
        }

        try {
            Mail::send('emails.member-portal-credentials', [
                'member' => $this->selectedMember,
                'credentials' => $this->generatedCredentials
            ], function ($message) {
                $message->to($this->selectedMember->email, $this->selectedMember->getFullNameAttribute())
                        ->subject('Your SACCO Members Portal Access Credentials');
            });
        } catch (\Exception $e) {
            Log::error('Failed to send portal credentials email: ' . $e->getMessage());
        }
    }

    private function sendCredentialsSMS()
    {
        // SMS implementation would go here
        // This is a placeholder for SMS service integration
        Log::info('SMS credentials sent to: ' . $this->selectedMember->mobile_phone_number);
    }

    public function closeModals()
    {
        $this->showEnablePortalModal = false;
        $this->showCredentialsModal = false;
        $this->showBulkEnableModal = false;
        $this->selectedMember = null;
        $this->generatedCredentials = null;
        $this->portalData['custom_password'] = '';
    }

    public function toggleMemberSelection($memberId)
    {
        if (in_array($memberId, $this->selectedMembers)) {
            $this->selectedMembers = array_diff($this->selectedMembers, [$memberId]);
        } else {
            $this->selectedMembers[] = $memberId;
        }
    }

    public function selectAllMembers()
    {
        $members = $this->getMembers();
        $this->selectedMembers = $members->pluck('id')->toArray();
    }

    public function deselectAllMembers()
    {
        $this->selectedMembers = [];
    }

    public function unlockPortalAccess($memberId)
    {
        $member = ClientsModel::findOrFail($memberId);
        $portalUser = WebPortalUser::where('client_id', $memberId)->first();

        if (!$portalUser) {
            $this->dispatchBrowserEvent('error', [
                'message' => 'Portal user not found.'
            ]);
            return;
        }

        $portalUser->unlockAccount();

        $this->dispatchBrowserEvent('success', [
            'message' => 'Portal access unlocked for ' . $member->getFullNameAttribute()
        ]);

        $this->loadStatistics();
    }

    public function resetMemberPassword($memberId)
    {
        $member = ClientsModel::findOrFail($memberId);
        $portalUser = WebPortalUser::where('client_id', $memberId)->first();

        if (!$portalUser) {
            $this->dispatchBrowserEvent('error', [
                'message' => 'Portal user not found.'
            ]);
            return;
        }

        // Generate new password
        $newPassword = $this->generateSecurePassword();
        
        // Update password and force change on next login
        $portalUser->update([
            'password_hash' => $newPassword, // Will be hashed by mutator
            'force_password_change' => true,
            'updated_by' => auth()->id(),
        ]);

        // Unlock account if it was locked
        if ($portalUser->isAccountLocked()) {
            $portalUser->unlockAccount();
        }

        // Store credentials for display
        $this->generatedCredentials = [
            'member_number' => $member->client_number,
            'email' => $member->email,
            'phone' => $member->mobile_phone_number,
            'password' => $newPassword,
            'portal_url' => route('members.portal')
        ];

        // Register/update user in authentication system with new password
        $this->registerInAuthenticationSystem($member, $newPassword);

        // Send new credentials via email
        try {
            $member->notify(new MemberPortalCredentialsNotification([
                'member_name' => $member->getFullNameAttribute(),
                'member_number' => $member->client_number,
                'username' => $portalUser->username,
                'password' => $newPassword,
                'portal_url' => route('members.portal'),
                'is_password_reset' => true
            ]));
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'member_id' => $memberId,
                'error' => $e->getMessage()
            ]);
        }

        $this->showCredentialsModal = true;

        $this->dispatchBrowserEvent('success', [
            'message' => 'Password reset successfully for ' . $member->getFullNameAttribute()
        ]);
    }

    public function disablePortalAccess($memberId)
    {
        $member = ClientsModel::findOrFail($memberId);
        $portalUser = WebPortalUser::where('client_id', $memberId)->first();

        if (!$portalUser) {
            $this->dispatchBrowserEvent('error', [
                'message' => 'Portal user not found.'
            ]);
            return;
        }

        // Deactivate the portal user
        $portalUser->update([
            'is_active' => false,
            'updated_by' => auth()->id(),
        ]);

        // End all active sessions
        $portalUser->endAllSessions();

        // Update status in authentication system
        $this->updateAuthenticationSystemStatus($member, 'inactive');

        $this->dispatchBrowserEvent('success', [
            'message' => 'Portal access disabled for ' . $member->getFullNameAttribute()
        ]);

        $this->loadStatistics();
    }

    /**
     * Register user in the authentication system
     */
    private function registerInAuthenticationSystem($member, $password)
    {
        \Log::info('=== START: Authentication System Registration ===', [
            'member_number' => $member->client_number,
            'email' => $member->email,
            'member_id' => $member->id
        ]);

        try {
            // Get institution code from local database
            \Log::info('Step 1: Fetching institution code from local database');
            $institutionCode = \DB::table('institutions')->where('id', 1)->value('code');

            if (!$institutionCode) {
                \Log::error('FAILED: Institution code not found in local database');
                return;
            }
            \Log::info('Institution code retrieved', ['code' => $institutionCode]);

            // Get institution data from administration database
            \Log::info('Step 2: Fetching institution data from administration database');

            // Log connection details for debugging
            try {
                $adminConfig = config('database.connections.admin');
                \Log::info('Admin DB connection config', [
                    'host' => $adminConfig['host'] ?? 'not set',
                    'database' => $adminConfig['database'] ?? 'not set',
                    'username' => $adminConfig['username'] ?? 'not set'
                ]);

                $institutionData = \DB::connection('admin')
                    ->table('institutions')
                    ->where('institution_id', $institutionCode)
                    ->first();
            } catch (\Exception $e) {
                \Log::error('Failed to connect to admin database', [
                    'error' => $e->getMessage(),
                    'institution_code' => $institutionCode
                ]);
                return;
            }

            if (!$institutionData) {
                \Log::error("FAILED: Institution not found in administration database", [
                    'institution_code' => $institutionCode
                ]);
                return;
            }
            \Log::info('Institution data retrieved', [
                'name' => $institutionData->name,
                'id' => $institutionData->id
            ]);

            // Connect to PostgreSQL authentication database
            \Log::info('Step 3: Connecting to PostgreSQL authentication database');
            \Log::info('Using PostgreSQL database', ['host' => '127.0.0.1', 'dbname' => 'mfi_auth_db']);
            $authDb = new \PDO(
                'pgsql:host=127.0.0.1;dbname=mfi_auth_db;connect_timeout=5',
                'postgres',
                'postgres',
                [
                    \PDO::ATTR_TIMEOUT => 5,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ]
            );
            \Log::info('Database connection established');

            // Determine the alias from the current environment
            \Log::info('Step 4: Determining environment alias');
            $currentPath = base_path();
            preg_match('/INSTANCES\/([^\/]+)\//', $currentPath, $matches);
            $alias = $matches[1] ?? 'unknown';
            \Log::info('Environment alias determined', ['alias' => $alias, 'path' => $currentPath]);

            // Use Laravel's Hash to create the same hash format
            \Log::info('Step 5: Hashing password');
            $hashedPassword = \Illuminate\Support\Facades\Hash::make($password);
            \Log::info('Password hashed', ['hash_length' => strlen($hashedPassword)]);

            // Prepare user data
            \Log::info('Step 6: Preparing user data');
            $userData = [
                ':full_name' => $member->getFullNameAttribute(),
                ':institution_number' => $institutionCode,
                ':member_number' => $member->client_number,
                ':email' => $member->email,
                ':phone_number' => $member->mobile_phone_number,
                ':password' => $hashedPassword,
                ':user_type' => 'member',
                ':institution_id' => $institutionCode,
                ':institution_name' => $institutionData->name,
                ':status' => 'active',
                ':email_verified' => true,
                ':default_system' => $alias, // Just the base SACCOS name (e.g., "nbc_saccos")
                ':created_at' => date('Y-m-d H:i:s'),
                ':updated_at' => date('Y-m-d H:i:s')
            ];

            \Log::info('User data prepared', [
                'full_name' => $userData[':full_name'],
                'member_number' => $userData[':member_number'],
                'email' => $userData[':email'],
                'user_type' => $userData[':user_type'],
                'default_system' => $userData[':default_system']
            ]);

            // Check if user already exists
            \Log::info('Step 7: Checking if user exists in authentication system');
            $checkStmt = $authDb->prepare("
                SELECT id FROM all_systems_users
                WHERE email = :email OR member_number = :member_number
            ");
            $checkStmt->execute([
                ':email' => $member->email,
                ':member_number' => $member->client_number
            ]);

            $existingUser = $checkStmt->fetch(\PDO::FETCH_ASSOC);
            \Log::info('User existence check completed', [
                'exists' => $existingUser ? true : false,
                'user_id' => $existingUser['id'] ?? null
            ]);

            if ($existingUser) {
                // Update existing user
                \Log::info('Step 8a: Updating existing user in authentication system', [
                    'user_id' => $existingUser['id']
                ]);
                $updateStmt = $authDb->prepare("
                    UPDATE all_systems_users SET
                        full_name = :full_name,
                        password = :password,
                        phone_number = :phone_number,
                        status = :status,
                        updated_at = :updated_at
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':full_name' => $userData[':full_name'],
                    ':password' => $userData[':password'],
                    ':phone_number' => $userData[':phone_number'],
                    ':status' => $userData[':status'],
                    ':updated_at' => $userData[':updated_at'],
                    ':id' => $existingUser['id']
                ]);

                $rowsAffected = $updateStmt->rowCount();
                \Log::info("✓ SUCCESS: Updated existing user in authentication system", [
                    'member_number' => $member->client_number,
                    'email' => $member->email,
                    'user_id' => $existingUser['id'],
                    'rows_affected' => $rowsAffected
                ]);
            } else {
                // Insert new user
                \Log::info('Step 8b: Creating new user in authentication system');
                $insertStmt = $authDb->prepare("
                    INSERT INTO all_systems_users (
                        full_name, institution_number, member_number, email,
                        phone_number, password, user_type, institution_id,
                        institution_name, status, email_verified,
                        default_system, created_at, updated_at
                    ) VALUES (
                        :full_name, :institution_number, :member_number, :email,
                        :phone_number, :password, :user_type, :institution_id,
                        :institution_name, :status, :email_verified,
                        :default_system, :created_at, :updated_at
                    )
                ");
                $insertStmt->execute($userData);

                $newUserId = $authDb->lastInsertId();
                \Log::info("✓ SUCCESS: Registered new user in authentication system", [
                    'member_number' => $member->client_number,
                    'email' => $member->email,
                    'institution' => $institutionCode,
                    'new_user_id' => $newUserId
                ]);
            }

            \Log::info('=== END: Authentication System Registration Complete ===', [
                'member_number' => $member->client_number,
                'success' => true
            ]);
        } catch (\PDOException $e) {
            \Log::error("✗ FAILED: Database error in authentication system registration", [
                'error' => $e->getMessage(),
                'member_number' => $member->client_number ?? 'unknown',
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
        } catch (\Exception $e) {
            \Log::error("✗ FAILED: General error in authentication system registration", [
                'error' => $e->getMessage(),
                'member_number' => $member->client_number ?? 'unknown',
                'class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Update user status in authentication system
     */
    private function updateAuthenticationSystemStatus($member, $status)
    {
        \Log::info('=== START: Authentication System Status Update ===', [
            'member_number' => $member->client_number,
            'email' => $member->email,
            'new_status' => $status
        ]);

        try {
            // Connect to PostgreSQL authentication database
            \Log::info('Step 1: Connecting to PostgreSQL authentication database');
            \Log::info('Using PostgreSQL database', ['host' => '127.0.0.1', 'dbname' => 'mfi_auth_db']);
            $authDb = new \PDO(
                'pgsql:host=127.0.0.1;dbname=mfi_auth_db;connect_timeout=5',
                'postgres',
                'postgres',
                [
                    \PDO::ATTR_TIMEOUT => 5,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ]
            );
            \Log::info('Database connection established');

            // Update user status
            \Log::info('Step 2: Updating user status', [
                'member_number' => $member->client_number,
                'email' => $member->email,
                'status' => $status
            ]);

            $updateStmt = $authDb->prepare("
                UPDATE all_systems_users
                SET status = :status, updated_at = :updated_at
                WHERE member_number = :member_number OR email = :email
            ");

            $updateStmt->execute([
                ':status' => $status,
                ':updated_at' => date('Y-m-d H:i:s'),
                ':member_number' => $member->client_number,
                ':email' => $member->email
            ]);

            $rowsAffected = $updateStmt->rowCount();
            \Log::info("✓ SUCCESS: Updated user status in authentication system", [
                'member_number' => $member->client_number,
                'status' => $status,
                'rows_affected' => $rowsAffected
            ]);

            \Log::info('=== END: Authentication System Status Update Complete ===', [
                'member_number' => $member->client_number,
                'success' => true
            ]);
        } catch (\PDOException $e) {
            \Log::error("✗ FAILED: Database error updating user status", [
                'error' => $e->getMessage(),
                'member_number' => $member->client_number ?? 'unknown',
                'code' => $e->getCode()
            ]);
        } catch (\Exception $e) {
            \Log::error("✗ FAILED: General error updating user status", [
                'error' => $e->getMessage(),
                'member_number' => $member->client_number ?? 'unknown',
                'class' => get_class($e)
            ]);
        }
    }
}
