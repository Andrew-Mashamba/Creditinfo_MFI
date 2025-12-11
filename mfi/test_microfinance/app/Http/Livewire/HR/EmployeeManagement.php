<?php

namespace App\Http\Livewire\HR;

use App\Models\Employee;
use App\Models\Department;
use App\Models\User;
use App\Jobs\SendEmployeeCredentials;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class EmployeeManagement extends Component
{
    use WithPagination;

    public $showAddModal = false;
    public $showEditModal = false;
    public $showViewModal = false;
    public $showExitModal = false;
    public $viewingEmployee = null;
    public $editingEmployee = null;
    public $exitingEmployee = null;
    public $search = '';

    // Exit form fields
    public $exit_type = '';
    public $exit_date = '';
    public $exit_reason = '';
    public $notice_period_days = '';
    public $final_settlement_amount = '';
    public $exit_comments = '';

    // Filters
    public $departmentFilter = '';
    public $statusFilter = '';
    public $employmentTypeFilter = '';
    public $branchFilter = '';

    // Sorting
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Pagination
    public $perPage = 15;
    
    // Form fields - Personal Information
    public $first_name = '';
    public $last_name = '';
    public $middle_name = '';
    public $email = '';
    public $phone = '';
    public $employee_number = '';
    public $department_id = '';
    public $job_title = '';
    public $hire_date = '';
    public $basic_salary = '';
    public $gender = '';
    public $date_of_birth = '';
    public $address = '';
    public $employee_status = 'ACTIVE';
    public $employment_type = 'full-time';

    // Additional Personal Details
    public $marital_status = '';
    public $nationality = '';
    public $place_of_birth = '';
    public $education_level = '';

    // Address Details
    public $street = '';
    public $city = '';
    public $region = '';
    public $district = '';
    public $ward = '';
    public $postal_code = '';
    public $physical_address = '';

    // Emergency Contact
    public $emergency_contact_name = '';
    public $emergency_contact_relationship = '';
    public $emergency_contact_phone = '';
    public $emergency_contact_email = '';

    // Next of Kin
    public $next_of_kin_name = '';
    public $next_of_kin_phone = '';

    // Employment Details
    public $branch_id = '';
    public $role_id = '';
    public $reporting_manager_id = '';
    public $payment_frequency = '';
    public $notes = '';
    public $profile_photo_path = '';

    // Tax & Benefits
    public $tin_number = '';
    public $nida_number = '';
    public $nssf_number = '';
    public $nssf_rate = '';
    public $nhif_number = '';
    public $nhif_rate = '';
    public $workers_compensation = '';
    public $life_insurance = '';
    public $tax_category = '';
    public $paye_rate = '';
    public $tax_paid = '';
    public $pension = '';
    public $nhif = '';

    // Role & Permissions
    public $selectedRole = null;
    public $selectedSubRoles = [];
    public $availableRoles = [];
    public $availableSubRoles = [];

    protected $rules = [
        // Required fields
        'first_name' => 'required|string|max:50',
        'last_name' => 'required|string|max:50',
        'email' => 'required|email|unique:employees,email',
        'phone' => 'required|string|max:20',
        'employee_number' => 'required|unique:employees,employee_number',
        'department_id' => 'required|exists:departments,id',
        'job_title' => 'required|string|max:100',
        'hire_date' => 'required|date',
        'basic_salary' => 'required|numeric|min:0',
        'gender' => 'required|in:male,female',
        'employment_type' => 'required|in:full-time,part-time,contract',

        // Optional Personal Details
        'middle_name' => 'nullable|string|max:50',
        'marital_status' => 'nullable|string|in:single,married,divorced,widowed',
        'nationality' => 'nullable|string|max:100',
        'place_of_birth' => 'nullable|string|max:100',
        'education_level' => 'nullable|string|max:100',
        'date_of_birth' => 'nullable|date',

        // Address Details
        'address' => 'nullable|string|max:255',
        'street' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:100',
        'region' => 'nullable|string|max:100',
        'district' => 'nullable|string|max:100',
        'ward' => 'nullable|string|max:100',
        'postal_code' => 'nullable|string|max:20',
        'physical_address' => 'nullable|string|max:255',

        // Emergency Contact
        'emergency_contact_name' => 'nullable|string|max:100',
        'emergency_contact_relationship' => 'nullable|string|max:100',
        'emergency_contact_phone' => 'nullable|string|max:20',
        'emergency_contact_email' => 'nullable|email|max:100',

        // Next of Kin
        'next_of_kin_name' => 'nullable|string|max:100',
        'next_of_kin_phone' => 'nullable|string|max:20',

        // Employment Details
        'branch_id' => 'nullable|exists:branches,id',
        'role_id' => 'nullable|integer',
        'reporting_manager_id' => 'nullable|exists:employees,id',
        'payment_frequency' => 'nullable|string|max:50',
        'notes' => 'nullable|string',
        'profile_photo_path' => 'nullable|string|max:255',
        'employee_status' => 'nullable|string',

        // Tax & Benefits
        'tin_number' => 'nullable|string|max:30',
        'nida_number' => 'nullable|string|max:30',
        'nssf_number' => 'nullable|string|max:30',
        'nssf_rate' => 'nullable|numeric|min:0|max:100',
        'nhif_number' => 'nullable|string|max:30',
        'nhif_rate' => 'nullable|numeric|min:0|max:100',
        'workers_compensation' => 'nullable|numeric|min:0',
        'life_insurance' => 'nullable|numeric|min:0',
        'tax_category' => 'nullable|string|max:50',
        'paye_rate' => 'nullable|numeric|min:0|max:100',
        'tax_paid' => 'nullable|numeric|min:0',
        'pension' => 'nullable|numeric|min:0',
        'nhif' => 'nullable|numeric|min:0',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openAddModal()
    {
        $this->reset(['first_name', 'last_name', 'middle_name', 'email', 'phone', 
                     'employee_number', 'department_id', 'job_title', 'hire_date', 
                     'basic_salary', 'gender', 'date_of_birth', 'address']);
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetErrorBag();
    }

    public function saveEmployee()
    {
        $validatedData = $this->validate();
        
        // Generate employee number if not provided
        if (empty($this->employee_number)) {
            $this->employee_number = 'EMP' . str_pad(Employee::count() + 1, 5, '0', STR_PAD_LEFT);
        }
        
        \DB::beginTransaction();
        try {
            // Create the employee
            $employee = Employee::create([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'middle_name' => $this->middle_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'employee_number' => $this->employee_number,
                'department_id' => $this->department_id,
                'job_title' => $this->job_title,
                'hire_date' => $this->hire_date,
                'basic_salary' => $this->basic_salary,
                'gross_salary' => $this->basic_salary, // Can be calculated with allowances
                'gender' => $this->gender,
                'date_of_birth' => $this->date_of_birth,
                'address' => $this->address,
                'employee_status' => 'ACTIVE',
                'employment_type' => $this->employment_type,
            ]);
            
            // Create user account for the employee
            $this->createUserAccountForEmployee($employee);
            
            \DB::commit();
            
            session()->flash('success', 'Employee added successfully! Login credentials are being sent in the background.');
            $this->closeAddModal();
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error creating employee: ' . $e->getMessage());
            session()->flash('error', 'Error creating employee: ' . $e->getMessage());
        }
    }

    public function editEmployee($id)
    {
        $employee = Employee::find($id);
        $this->editingEmployee = $id;

        // Basic Information
        $this->first_name = $employee->first_name;
        $this->last_name = $employee->last_name;
        $this->middle_name = $employee->middle_name;
        $this->email = $employee->email;
        $this->phone = $employee->phone;
        $this->employee_number = $employee->employee_number;
        $this->department_id = $employee->department_id;
        $this->job_title = $employee->job_title;
        $this->hire_date = $employee->hire_date;
        $this->basic_salary = $employee->basic_salary;
        $this->gender = $employee->gender;
        $this->date_of_birth = $employee->date_of_birth;
        $this->address = $employee->address;
        $this->employee_status = $employee->employee_status;
        $this->employment_type = $employee->employment_type;

        // Additional Personal Details
        $this->marital_status = $employee->marital_status;
        $this->nationality = $employee->nationality;
        $this->place_of_birth = $employee->place_of_birth;
        $this->education_level = $employee->education_level;

        // Address Details
        $this->street = $employee->street;
        $this->city = $employee->city;
        $this->region = $employee->region;
        $this->district = $employee->district;
        $this->ward = $employee->ward;
        $this->postal_code = $employee->postal_code;
        $this->physical_address = $employee->physical_address;

        // Emergency Contact
        $this->emergency_contact_name = $employee->emergency_contact_name;
        $this->emergency_contact_relationship = $employee->emergency_contact_relationship;
        $this->emergency_contact_phone = $employee->emergency_contact_phone;
        $this->emergency_contact_email = $employee->emergency_contact_email;

        // Next of Kin
        $this->next_of_kin_name = $employee->next_of_kin_name;
        $this->next_of_kin_phone = $employee->next_of_kin_phone;

        // Employment Details
        $this->branch_id = $employee->branch_id;
        $this->role_id = $employee->role_id;
        $this->reporting_manager_id = $employee->reporting_manager_id;
        $this->payment_frequency = $employee->payment_frequency;
        $this->notes = $employee->notes;
        $this->profile_photo_path = $employee->profile_photo_path;

        // Tax & Benefits
        $this->tin_number = $employee->tin_number;
        $this->nida_number = $employee->nida_number;
        $this->nssf_number = $employee->nssf_number;
        $this->nssf_rate = $employee->nssf_rate;
        $this->nhif_number = $employee->nhif_number;
        $this->nhif_rate = $employee->nhif_rate;
        $this->workers_compensation = $employee->workers_compensation;
        $this->life_insurance = $employee->life_insurance;
        $this->tax_category = $employee->tax_category;
        $this->paye_rate = $employee->paye_rate;
        $this->tax_paid = $employee->tax_paid;
        $this->pension = $employee->pension;
        $this->nhif = $employee->nhif;

        // Load user's role and sub-roles if user exists
        if ($employee->user_id) {
            $user = User::with(['roles', 'subRoles'])->find($employee->user_id);
            if ($user && $user->roles->first()) {
                $role = $user->roles->first();
                $this->selectedRole = $role->id;

                // Load available roles for the department
                $this->updatedDepartmentId($employee->department_id);

                // Re-set role after loading department roles
                $this->selectedRole = $role->id;

                // Load available sub-roles for the role
                $this->updatedSelectedRole($role->id);

                // Load user's selected sub-roles
                $this->selectedSubRoles = $user->subRoles->pluck('id')->toArray();
            }
        }

        $this->showEditModal = true;
    }

    public function updateEmployee()
    {
        \Log::info('Update Employee function started', [
            'employee_id' => $this->editingEmployee,
            'user' => auth()->user()->id ?? 'unknown'
        ]);

        try {
            $rules = $this->rules;
            $rules['email'] = 'required|email|unique:employees,email,' . $this->editingEmployee;
            $rules['employee_number'] = 'required|unique:employees,employee_number,' . $this->editingEmployee;

            \Log::info('Validating employee data', ['employee_id' => $this->editingEmployee]);
            $validatedData = $this->validate($rules);
            \Log::info('Validation passed', ['employee_id' => $this->editingEmployee]);

            \DB::beginTransaction();

            \Log::info('Updating employee record', ['employee_id' => $this->editingEmployee]);
            Employee::find($this->editingEmployee)->update([
                // Basic Information
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'middle_name' => $this->middle_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'employee_number' => $this->employee_number,
                'department_id' => $this->department_id,
                'job_title' => $this->job_title,
                'hire_date' => $this->hire_date,
                'basic_salary' => $this->basic_salary,
                'gross_salary' => $this->basic_salary,
                'gender' => $this->gender,
                'date_of_birth' => $this->date_of_birth,
                'address' => $this->address,
                'employee_status' => $this->employee_status,
                'employment_type' => $this->employment_type,

                // Additional Personal Details
                'marital_status' => $this->marital_status,
                'nationality' => $this->nationality,
                'place_of_birth' => $this->place_of_birth,
                'education_level' => $this->education_level,

                // Address Details
                'street' => $this->street,
                'city' => $this->city,
                'region' => $this->region,
                'district' => $this->district,
                'ward' => $this->ward,
                'postal_code' => $this->postal_code,
                'physical_address' => $this->physical_address,

                // Emergency Contact
                'emergency_contact_name' => $this->emergency_contact_name,
                'emergency_contact_relationship' => $this->emergency_contact_relationship,
                'emergency_contact_phone' => $this->emergency_contact_phone,
                'emergency_contact_email' => $this->emergency_contact_email,

                // Next of Kin
                'next_of_kin_name' => $this->next_of_kin_name,
                'next_of_kin_phone' => $this->next_of_kin_phone,

                // Employment Details
                'branch_id' => $this->branch_id,
                'role_id' => $this->role_id,
                'reporting_manager_id' => $this->reporting_manager_id,
                'payment_frequency' => $this->payment_frequency,
                'notes' => $this->notes,
                'profile_photo_path' => $this->profile_photo_path,

                // Tax & Benefits
                'tin_number' => $this->tin_number,
                'nida_number' => $this->nida_number,
                'nssf_number' => $this->nssf_number,
                'nssf_rate' => $this->nssf_rate,
                'nhif_number' => $this->nhif_number,
                'nhif_rate' => $this->nhif_rate,
                'workers_compensation' => $this->workers_compensation,
                'life_insurance' => $this->life_insurance,
                'tax_category' => $this->tax_category,
                'paye_rate' => $this->paye_rate,
                'tax_paid' => $this->tax_paid,
                'pension' => $this->pension,
                'nhif' => $this->nhif,
            ]);
            \Log::info('Employee record updated successfully', ['employee_id' => $this->editingEmployee]);

            // Update user information and role/sub-roles
            \Log::info('Looking for user account', ['employee_id' => $this->editingEmployee]);
            $user = User::where('employeeId', $this->editingEmployee)->first();

            if ($user) {
                \Log::info('User account found, updating user data', [
                    'user_id' => $user->id,
                    'employee_id' => $this->editingEmployee
                ]);

                $user->update([
                    'email' => $this->email,
                    'phone_number' => $this->phone,
                    'department_code' => $this->department_id ? Department::find($this->department_id)->department_code : null,
                ]);
                \Log::info('User data updated', ['user_id' => $user->id]);

                // Update role and sub-roles if selectedRole is set
                if ($this->selectedRole) {
                    \Log::info('Updating user role and sub-roles', [
                        'user_id' => $user->id,
                        'selected_role' => $this->selectedRole,
                        'selected_sub_roles' => $this->selectedSubRoles
                    ]);

                    // Sync role
                    $user->roles()->sync([$this->selectedRole]);
                    \Log::info('Role synced', ['user_id' => $user->id, 'role_id' => $this->selectedRole]);

                    // Sync sub-roles
                    $user->subRoles()->sync($this->selectedSubRoles ?: []);
                    \Log::info('Sub-roles synced', [
                        'user_id' => $user->id,
                        'sub_roles' => $this->selectedSubRoles ?: []
                    ]);

                    // Sync permissions from role and sub-roles
                    \Log::info('Syncing permissions', ['user_id' => $user->id]);
                    $this->syncUserPermissions($user);
                    \Log::info('Permissions synced successfully', ['user_id' => $user->id]);
                } else {
                    \Log::info('No role selected, skipping role/sub-role update', ['user_id' => $user->id]);
                }
            } else {
                \Log::warning('User account not found for employee', ['employee_id' => $this->editingEmployee]);
            }

            \DB::commit();
            \Log::info('Employee update transaction committed successfully', ['employee_id' => $this->editingEmployee]);

            session()->flash('success', 'Employee updated successfully!');
            $this->showEditModal = false;
            $this->editingEmployee = null;
            $this->resetErrorBag();

        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollBack();
            \Log::error('Validation failed in updateEmployee', [
                'employee_id' => $this->editingEmployee,
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            throw $e;

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error updating employee', [
                'employee_id' => $this->editingEmployee,
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Error updating employee: ' . $e->getMessage());
            throw $e;
        }
    }

    public function openExitModal($id)
    {
        $this->exitingEmployee = Employee::find($id);
        $this->exit_type = '';
        $this->exit_date = now()->format('Y-m-d');
        $this->exit_reason = '';
        $this->notice_period_days = '';
        $this->final_settlement_amount = '';
        $this->exit_comments = '';
        $this->showExitModal = true;
    }

    public function processEmployeeExit()
    {
        $this->validate([
            'exit_type' => 'required|in:resignation,termination,retirement,contract_end,voluntary,involuntary',
            'exit_date' => 'required|date',
            'exit_reason' => 'required|string|min:10',
            'notice_period_days' => 'nullable|integer|min:0',
            'final_settlement_amount' => 'nullable|numeric|min:0',
            'exit_comments' => 'nullable|string',
        ]);

        try {
            \DB::beginTransaction();

            $employee = Employee::find($this->exitingEmployee->id);

            // Update employee status
            $employee->update([
                'employee_status' => 'INACTIVE',
                'notes' => ($employee->notes ? $employee->notes . "\n\n" : '') .
                           "EXIT DETAILS:\n" .
                           "Exit Type: " . ucwords(str_replace('_', ' ', $this->exit_type)) . "\n" .
                           "Exit Date: " . $this->exit_date . "\n" .
                           "Reason: " . $this->exit_reason . "\n" .
                           ($this->notice_period_days ? "Notice Period: " . $this->notice_period_days . " days\n" : '') .
                           ($this->final_settlement_amount ? "Final Settlement: TZS " . number_format($this->final_settlement_amount, 2) . "\n" : '') .
                           ($this->exit_comments ? "Comments: " . $this->exit_comments . "\n" : '') .
                           "Processed by: " . auth()->user()->name . "\n" .
                           "Processed at: " . now()->format('Y-m-d H:i:s')
            ]);

            // Deactivate user account if exists
            if ($employee->user_id) {
                $user = User::find($employee->user_id);
                if ($user) {
                    $user->update(['status' => 'INACTIVE']);
                }
            }

            \DB::commit();

            \Log::info('Employee exit processed successfully', [
                'employee_id' => $employee->id,
                'exit_type' => $this->exit_type,
                'exit_date' => $this->exit_date,
                'processed_by' => auth()->user()->id
            ]);

            session()->flash('success', 'Employee exit processed successfully!');
            $this->showExitModal = false;
            $this->exitingEmployee = null;

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error processing employee exit', [
                'employee_id' => $this->exitingEmployee->id,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error processing employee exit: ' . $e->getMessage());
        }
    }

    public function deleteEmployee($id)
    {
        Employee::find($id)->delete();
        session()->flash('success', 'Employee deleted successfully!');
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

    public function viewEmployee($id)
    {
        $this->viewingEmployee = Employee::with(['department', 'branch', 'reportingManager'])->find($id);
        $this->showViewModal = true;
    }

    public function resetFilters()
    {
        $this->departmentFilter = '';
        $this->statusFilter = '';
        $this->employmentTypeFilter = '';
        $this->branchFilter = '';
        $this->search = '';
    }

    public function updatedDepartmentId($value)
    {
        $this->selectedRole = null;
        $this->selectedSubRoles = [];
        $this->availableSubRoles = [];

        if ($value) {
            $department = Department::find($value);
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
            $role = \App\Models\Role::find($value);
            if ($role) {
                $this->availableSubRoles = $role->subRoles()
                    ->orderBy('name')
                    ->get();
            }
        } else {
            $this->availableSubRoles = [];
        }
    }

    protected function createUserAccountForEmployee($employee)
    {
        try {
            // Generate a random password
            $password = $this->generateSecurePassword();
            
            // Get department
            $department = Department::find($employee->department_id);
            
            // Create user account
            $user = User::create([
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'email' => $employee->email,
                'password' => Hash::make($password),
                'phone_number' => $employee->phone,
                'employeeId' => $employee->id,
                'department_code' => $department ? $department->code : null,
                'branch_id' => 1, // Default branch, can be modified as needed
                'status' => 'ACTIVE',
                'verification_status' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Assign default role (Employee role - adjust as needed)
            // You may want to assign role based on job title or department
            \DB::table('user_roles')->insert([
                'user_id' => $user->id,
                'role_id' => 3, // Default employee role ID - adjust as needed
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Dispatch job to send credentials in background
            SendEmployeeCredentials::dispatch($employee, $password, $user);
            
            \Log::info('User account created for employee and credentials job dispatched', [
                'employee_id' => $employee->id,
                'user_id' => $user->id,
                'email' => $employee->email
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error creating user account for employee', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    protected function generateSecurePassword($length = 10)
    {
        // Generate a secure random password
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*';

        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        $allChars = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        return str_shuffle($password);
    }

    protected function syncUserPermissions($user)
    {
        // Get all permissions from the role
        $permissions = collect();

        if ($this->selectedRole) {
            $role = \App\Models\Role::find($this->selectedRole);
            if ($role) {
                $permissions = $permissions->merge($role->permissions);
            }
        }

        // Add permissions from sub-roles
        foreach ($this->selectedSubRoles as $subRoleId) {
            $subRole = \App\Models\SubRole::find($subRoleId);
            if ($subRole) {
                $permissions = $permissions->merge($subRole->permissions);
            }
        }

        // Sync unique permissions to user
        $user->permissions()->sync($permissions->pluck('id')->unique());
    }

    public function render()
    {
        $query = Employee::with(['department', 'branch']);

        // Search
        if ($this->search) {
            $query->where(function($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('employee_number', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('job_title', 'like', '%' . $this->search . '%');
            });
        }

        // Department filter
        if ($this->departmentFilter) {
            $query->where('department_id', $this->departmentFilter);
        }

        // Status filter
        if ($this->statusFilter) {
            $query->where('employee_status', $this->statusFilter);
        }

        // Employment Type filter
        if ($this->employmentTypeFilter) {
            $query->where('employment_type', $this->employmentTypeFilter);
        }

        // Branch filter
        if ($this->branchFilter) {
            $query->where('branch_id', $this->branchFilter);
        }

        // Sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $employees = $query->paginate($this->perPage);

        // Statistics
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('employee_status', 'ACTIVE')->count();
        $inactiveEmployees = Employee::where('employee_status', 'INACTIVE')->count();
        $suspendedEmployees = Employee::where('employee_status', 'SUSPENDED')->count();

        $departments = Department::all();
        $branches = \App\Models\Branch::all();
        $allEmployees = Employee::select('id', 'first_name', 'last_name', 'employee_number')
            ->orderBy('first_name')
            ->get();

        return view('livewire.h-r.employee-management', [
            'employees' => $employees,
            'departments' => $departments,
            'branches' => $branches,
            'allEmployees' => $allEmployees,
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'inactiveEmployees' => $inactiveEmployees,
            'suspendedEmployees' => $suspendedEmployees,
        ]);
    }
}