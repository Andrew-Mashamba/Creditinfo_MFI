<?php

namespace App\Http\Livewire\Users;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Employee;
use App\Models\departmentsList;
use App\Models\Role;
use App\Models\SubRole;
use App\Models\Branch;
use App\Notifications\UserCredentialsNotification;
use App\Services\SmsService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class SimpleUsers extends Component
{
    use WithPagination;

    // Search and filters
    public $search = '';
    public $departmentFilter = '';
    public $roleFilter = '';
    public $statusFilter = '';
    
    // Modal states
    public $showCreateUser = false;
    public $showEditUser = false;
    public $showDeleteUser = false;
    public $editingUserId = null;
    public $deletingUserId = null;
    
    // User form fields
    public $name;
    public $email;
    public $phone_number;
    // public $employee_id;
    public $password;
    public $password_confirmation;
    public $sendCredentials = true;
    
    // Department/Role/SubRole fields
    public $selectedDepartment = null;
    public $selectedRole = null;
    public $selectedSubRoles = [];
    
    // Available options
    public $departments = [];
    public $availableRoles = [];
    public $availableSubRoles = [];
    public $branches = [];
    public $selectedBranch = null;
    
    // Sorting
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    protected $listeners = ['refreshUsers' => '$refresh'];
    
    protected function rules()
    {
        $rules = [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'nullable|string|max:20',
            // 'employee_id' => 'nullable|string|max:50|unique:users,employeeId',
            'selectedDepartment' => 'required|exists:departments,id',
            'selectedRole' => 'required|exists:roles,id',
            'selectedBranch' => 'nullable|exists:branches,id',
            'selectedSubRoles' => 'nullable|array',
            'selectedSubRoles.*' => 'exists:sub_roles,id',
        ];
        
        if (!$this->editingUserId) {
            // $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            // $rules['password'] = 'nullable|string|min:8|confirmed';
            $rules['email'] = ['required', 'email', Rule::unique('users')->ignore($this->editingUserId)];
            // $rules['employee_id'] = ['nullable', 'string', 'max:50', Rule::unique('users', 'employeeId')->ignore($this->editingUserId)];
        }
        
        return $rules;
    }
    
    protected $messages = [
        'selectedDepartment.required' => 'Please select a department.',
        'selectedDepartment.exists' => 'The selected department is invalid.',
        'selectedRole.required' => 'Please select a role.',
        'selectedRole.exists' => 'The selected role is invalid.',
        'selectedSubRoles.*.exists' => 'One or more selected sub-roles are invalid.',
    ];
    
    public function mount()
    {
        $this->loadDepartments();
        $this->loadBranches();
    }
    
    public function loadDepartments()
    {
        $this->departments = departmentsList::where('status', true)
            ->orderBy('department_name')
            ->get();
    }
    
    public function loadBranches()
    {
        $this->branches = Branch::where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();
    }
    
    public function updatedSelectedDepartment($value)
    {
        $this->selectedRole = null;
        $this->selectedSubRoles = [];
        $this->availableSubRoles = [];
        
        if ($value) {
            $department = departmentsList::find($value);
            if ($department) {
                $this->availableRoles = $department->roles()
                    ->where('status', 'ACTIVE')
                    ->orderBy('name')
                    ->get();
            }
        } else {
            $this->availableRoles = [];
        }
    }
    
    public function updatedSelectedRole($value)
    {
        $this->selectedSubRoles = [];
        
        if ($value) {
            $role = Role::find($value);
            if ($role) {
                $this->availableSubRoles = $role->subRoles()
                    ->orderBy('name')
                    ->get();
            }
        } else {
            $this->availableSubRoles = [];
        }
    }
    
    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateUser = true;
    }
    
    public function openEditModal($userId)
    {
        $this->resetForm();
        $this->editingUserId = $userId;
        
        $user = User::with(['roles.department', 'roles.subRoles'])->find($userId);
        if ($user) {
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone_number = $user->phone_number;
            // $this->employee_id = $user->employeeId;
            
            // Load the user's department, role, and sub-roles
            if ($user->roles->first()) {
                $role = $user->roles->first();
                $this->selectedRole = $role->id;
                
                if ($role->department) {
                    $this->selectedDepartment = $role->department->id;
                    $this->updatedSelectedDepartment($role->department->id);
                    $this->selectedRole = $role->id; // Re-set after loading department
                    $this->updatedSelectedRole($role->id);
                }
                
                // Load user's sub-roles
                $this->selectedSubRoles = $user->subRoles->pluck('id')->toArray();
            }
            
            $this->selectedBranch = $user->branch;
            $this->showEditUser = true;
        }
    }
    
    public function openDeleteModal($userId)
    {
        $this->deletingUserId = $userId;
        $this->showDeleteUser = true;
    }
    
    public function saveUser()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            if ($this->editingUserId) {
                $this->updateUser();
            } else {
                $this->createUser();
            }
            
            DB::commit();
            
            session()->flash('message', $this->editingUserId ? 'User updated successfully.' : 'User created successfully.');
            
            $this->showCreateUser = false;
            $this->showEditUser = false;
            $this->resetForm();
            $this->emit('refreshUsers');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving user: ' . $e->getMessage());
            session()->flash('error', 'Error saving user: ' . $e->getMessage());
        }
    }
    
    private function createUser()
    {
        // Get the selected role and department
        $role = Role::find($this->selectedRole);
        $department = departmentsList::find($this->selectedDepartment);
        $generatedPassword = $this->generateSecurePassword();

        // Create the user
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($generatedPassword),
            'phone_number' => $this->phone_number,
            // 'employeeId' => $this->employee_id,
            'department_code' => $department ? $department->department_code : null,
            'branch' => $this->selectedBranch,
            'status' => 'ACTIVE',
            'password_changed_at' => now(),
        ]);

        //Add to employees
        $employee = Employee::create([
            'institution_user_id' => $user->id,
            'branch_id' => $this->selectedBranch ?? null,
            'user_id' => $user->id ?? null,
            'first_name' => $this->name ?? null,
            'email' => $this->email ?? null,
            'phone' => '255' . substr($this->phone_number, -9),
            // 'employee_number' => $this->employee_id,
            'department_id' => $department->id,
            'job_title' => $this->job_title ?? null,
            'hire_date' => $this->hire_date ?? null,
            'basic_salary' => $this->basic_salary ?? null,
            'gross_salary' => $this->basic_salary ?? null,
            'gender' => $this->gender ?? null,
            'date_of_birth' => $this->date_of_birth ?? null,
            'address' => $this->address ?? null,
            'employee_status' => $this->employee_status ?? null,
            'employment_type' => $this->employment_type ?? null,
        ]);

        $user->update([
            'employeeId' => $employee->id
        ]);

        // Assign the role
        $user->roles()->attach($this->selectedRole);

        // Assign sub-roles if any
        if (!empty($this->selectedSubRoles)) {
            $user->subRoles()->attach($this->selectedSubRoles);
        }

        // Sync permissions from role and sub-roles
        $this->syncUserPermissions($user, $role);

        // Register user in authentication system
        $this->registerInAuthenticationSystem($user, $generatedPassword, $department);

        // Send welcome email and SMS with credentials if enabled
        if ($this->sendCredentials) {
            $this->sendWelcomeEmail($user, $generatedPassword);

            // Send credentials via SMS if phone number is provided
            if ($user->phone_number) {
                $this->sendCredentialsSMS($user, $generatedPassword);
            }
        }
    }
    
    private function updateUser()
    {
        $user = User::find($this->editingUserId);
        
        if (!$user) {
            throw new \Exception('User not found');
        }
        
        // Get the selected department
        $department = departmentsList::find($this->selectedDepartment);
        
        // Update user data
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            // 'employeeId' => $this->employee_id,
            'department_code' => $department ? $department->department_code : null,
            'branch' => $this->selectedBranch,
        ];
        
        // if ($this->password) {
        //     $userData['password'] = Hash::make($this->password);
        //     $userData['password_changed_at'] = now();
        // }
        
        $user->update($userData);
        
        // Update role
        $user->roles()->sync([$this->selectedRole]);
        
        // Update sub-roles
        $user->subRoles()->sync($this->selectedSubRoles ?: []);
        
        // Sync permissions
        $role = Role::find($this->selectedRole);
        $this->syncUserPermissions($user, $role);
    }
    
    private function syncUserPermissions($user, $role)
    {
        // Get all permissions from the role
        $permissions = collect();
        
        if ($role) {
            $permissions = $permissions->merge($role->permissions);
        }
        
        // Add permissions from sub-roles
        foreach ($this->selectedSubRoles as $subRoleId) {
            $subRole = SubRole::find($subRoleId);
            if ($subRole) {
                $permissions = $permissions->merge($subRole->permissions);
            }
        }
        
        // Sync unique permissions to user
        $user->permissions()->sync($permissions->pluck('id')->unique());
    }
    
    private function sendWelcomeEmail($user, $password)
    {
        try {
            // Get department and role names for the email
            $department = departmentsList::find($this->selectedDepartment);
            $role = Role::find($this->selectedRole);

            $departmentName = $department ? $department->department_name : null;
            $roleName = $role ? $role->name : null;

            // Send the notification with user credentials
            $user->notify(new UserCredentialsNotification(
                $user,
                $password,
                $departmentName,
                $roleName
            ));

            Log::info('Welcome email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            session()->flash('info', 'Login credentials have been sent to ' . $user->email);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            session()->flash('warning', 'User created but failed to send credentials. Password: ' . $password);
        }
    }

    /**
     * Send login credentials via SMS to the staff user
     */
    private function sendCredentialsSMS($user, $password)
    {
        try {
            $smsService = new SmsService();

            // Prepare SMS message with credentials
            $appName = config('app.name', 'MFI Management System');
            $loginUrl = 'http://saccos-uat.intra.nbc.co.tz/';
            $message = "Welcome to {$appName}!\n\n" .
                      "Your staff account has been created.\n\n" .
                      "Email: {$user->email}\n" .
                      "Password: {$password}\n\n" .
                      "Login at: {$loginUrl}\n\n" .
                      "Please login and change your password immediately.\n\n" .
                      "DO NOT share these credentials with anyone.";

            // Send SMS
            $smsService->send($user->phone_number, $message, $user, [
                'smsType' => 'TRANSACTIONAL',
                'serviceName' => 'SACCOSS',
                'language' => 'English'
            ]);

            Log::info('Staff credentials SMS sent successfully', [
                'user_id' => $user->id,
                'phone' => $user->phone_number
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send credentials SMS', [
                'user_id' => $user->id,
                'phone' => $user->phone_number,
                'error' => $e->getMessage()
            ]);
            // Don't throw exception - SMS failure shouldn't block user creation
        }
    }

    public function deleteUser()
    {
        try {
            $user = User::find($this->deletingUserId);
            
            if ($user) {
                // Check if this is the super admin
                if ($user->id === 1) {
                    session()->flash('error', 'Cannot delete the super admin user.');
                    $this->showDeleteUser = false;
                    return;
                }
                
                // Soft delete or update status
                $user->update(['status' => 'INACTIVE']);
                
                session()->flash('message', 'User deactivated successfully.');
            }
            
            $this->closeModals();
            $this->emit('refreshUsers');
            
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            session()->flash('error', 'Error deleting user: ' . $e->getMessage());
        }
    }
    
    public function closeModals()
    {
        $this->showCreateUser = false;
        $this->showEditUser = false;
        $this->showDeleteUser = false;
        $this->deletingUserId = null;
        $this->editingUserId = null;
    }
    
    public function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->phone_number = '';
        // $this->employee_id = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedDepartment = null;
        $this->selectedRole = null;
        $this->selectedSubRoles = [];
        $this->selectedBranch = null;
        $this->availableRoles = [];
        $this->availableSubRoles = [];
        $this->editingUserId = null;
        $this->sendCredentials = true;
        
        // Close all modals
        $this->closeModals();
        
        $this->resetValidation();
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Generate secure password with 7 capital letters and 7 numbers
     */
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

    /**
     * Register staff user in the authentication system
     */
    private function registerInAuthenticationSystem($user, $password, $department)
    {
        \Log::info('=== START: Authentication System Registration (Staff User) ===', [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name
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

            try {
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
            $hashedPassword = Hash::make($password);
            \Log::info('Password hashed', ['hash_length' => strlen($hashedPassword)]);

            // Prepare user data for STAFF user
            \Log::info('Step 6: Preparing staff user data');
            $userData = [
                ':full_name' => $user->name,
                ':institution_number' => $institutionCode,
                ':member_number' => '00000', // Staff users have no member number
                ':email' => $user->email,
                ':phone_number' => $user->phone_number ?? '',
                ':password' => $hashedPassword,
                ':user_type' => 'staff', // Set as staff user
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
                WHERE email = :email
            ");
            $checkStmt->execute([
                ':email' => $user->email
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
                        user_type = :user_type,
                        updated_at = :updated_at
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':full_name' => $userData[':full_name'],
                    ':password' => $userData[':password'],
                    ':phone_number' => $userData[':phone_number'],
                    ':status' => $userData[':status'],
                    ':user_type' => $userData[':user_type'],
                    ':updated_at' => $userData[':updated_at'],
                    ':id' => $existingUser['id']
                ]);

                $rowsAffected = $updateStmt->rowCount();
                \Log::info("✓ SUCCESS: Updated existing staff user in authentication system", [
                    'email' => $user->email,
                    'user_id' => $existingUser['id'],
                    'rows_affected' => $rowsAffected
                ]);
            } else {
                // Insert new user
                \Log::info('Step 8b: Creating new staff user in authentication system');
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
                \Log::info("✓ SUCCESS: Registered new staff user in authentication system", [
                    'email' => $user->email,
                    'name' => $user->name,
                    'institution' => $institutionCode,
                    'new_user_id' => $newUserId,
                    'user_type' => 'staff'
                ]);
            }

            \Log::info('=== END: Authentication System Registration Complete (Staff User) ===', [
                'email' => $user->email,
                'success' => true
            ]);
        } catch (\PDOException $e) {
            \Log::error("✗ FAILED: Database error in authentication system registration", [
                'error' => $e->getMessage(),
                'user_email' => $user->email ?? 'unknown',
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
        } catch (\Exception $e) {
            \Log::error("✗ FAILED: General error in authentication system registration", [
                'error' => $e->getMessage(),
                'user_email' => $user->email ?? 'unknown',
                'class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function render()
    {
        $query = User::with(['roles.department', 'roles.subRoles'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('employeeId', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->departmentFilter, function ($q) {
                $q->whereHas('roles.department', function ($query) {
                    $query->where('id', $this->departmentFilter);
                });
            })
            ->when($this->roleFilter, function ($q) {
                $q->whereHas('roles', function ($query) {
                    $query->where('id', $this->roleFilter);
                });
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        return view('livewire.users.simple-users', [
            'users' => $query->paginate(10),
            'totalUsers' => User::count(),
            'activeUsers' => User::where('status', 'ACTIVE')->count(),
            'inactiveUsers' => User::where('status', 'INACTIVE')->count(),
        ]);
    }
}