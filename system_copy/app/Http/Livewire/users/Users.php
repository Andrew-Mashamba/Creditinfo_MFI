<?php

namespace App\Http\Livewire\Users;

use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Branch;
use App\Notifications\NewMemberWelcomeNotification;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Users extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $department_code = '';
    public $statusFilter = '';
    public $roleFilter = '';
    public $departments;
    public $branches;
    public $managers;
    public $allRoles;

    // Removed public $users property - it was causing Livewire type error
    
    // User form properties
    public $showCreateUser = false;
    public $showDeleteUser = false;
    public $showViewUser = false;
    public $showManagePermissions = false;
    public $editingUser = null;
    public $viewingUser = null;
    public $currentStep = 0;
    public $userType = '';
    
    // Bulk actions
    public $selectedUsers = [];
    public $selectAll = false;
    
    // Sorting
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    // Statistics
    public $totalUsers = 0;
    public $activeUsers = 0;
    public $inactiveUsers = 0;
    public $totalDepartments = 0;
    
    // Personal Information
    public $firstName;
    public $middleName;
    public $lastName;
    public $email;
    public $phone;
    public $dob;
    public $gender;
    public $maritalStatus;
    public $nationality;
    public $nida;
    public $tin;
    public $physicalAddress;
    public $city;
    public $region;
    
    // Emergency Contact
    public $emergencyContactName;
    public $emergencyContactRelationship;
    public $emergencyContactPhone;
    public $emergencyContactEmail;
    
    // Employment Information
    public $department_id;
    public $branch_id;
    public $role_id;
    public $reporting_manager_id;
    public $employment_type;
    public $start_date;
    public $job_title;
    public $employeeId;
    public $status = 'active';
    
    // Payroll Information
    public $basicSalary;
    public $paymentFrequency;
    public $nssfNumber;
    public $nssfRate;
    public $nhifNumber;
    public $nhifRate;
    public $workersCompensation;
    public $lifeInsurance;
    public $taxCategory;
    public $payeRate;
    
    // Additional Client Information
    public $street;
    public $district;
    public $ward;
    public $postalCode;
    public $nextOfKinName;
    public $nextOfKinPhone;
    public $educationLevel;
    public $grossSalary;
    public $taxPaid;
    public $pension;
    public $nhif;
    
    // System Access
    public $password;
    public $password_confirmation;
    public $selectedRoles = [];
    public $availableRoles;
    public $sendCredentials = true;
    
    // File Uploads
    public $profilePhoto;
    public $cv;
    public $nationalId;
    public $employmentContract;

    protected $rules = [
        // Personal Information
        'firstName' => 'required|min:2|max:50',
        'lastName' => 'required|min:2|max:50',
        'email' => 'required|email|unique:users,email',
        'phone' => 'required|min:10|max:15',
        'dob' => 'required|date|before:today',
        'gender' => 'required|in:male,female,other',
        'maritalStatus' => 'required|in:single,married,divorced,widowed',
        'nationality' => 'required|string|max:50',
        'nida' => 'required|string|max:20|unique:employees,nida_number',
        'tin' => 'required|string|max:20|unique:employees,tin_number',
        'physicalAddress' => 'required|string|max:255',
        'city' => 'required|string|max:100',
        'region' => 'required|string|max:100',
        
        // Emergency Contact
        'emergencyContactName' => 'required|string|max:100',
        'emergencyContactRelationship' => 'required|string|max:50',
        'emergencyContactPhone' => 'required|string|min:10|max:15',
        'emergencyContactEmail' => 'nullable|email',
        
        // Employment Information
        'department_id' => 'required|exists:departments,id',
        'branch_id' => 'required|exists:branches,id',
        'role_id' => 'required|exists:roles,id',
        'reporting_manager_id' => 'nullable|exists:users,id',
        'employment_type' => 'required|in:full_time,part_time,contract,internship',
        'start_date' => 'required|date|after_or_equal:today',
        'job_title' => 'required|string|max:100',
        'employeeId' => 'required|string|max:20|unique:users,employeeId',
        'status' => 'required|in:ACTIVE,INACTIVE',
        
        // Payroll Information
        'basicSalary' => 'nullable|numeric|min:0',
        'paymentFrequency' => 'nullable|in:monthly,biweekly,weekly',
        'nssfNumber' => 'nullable|string|max:20|unique:employees,nssf_number',
        'nssfRate' => 'nullable|numeric|min:0|max:100',
        'nhifNumber' => 'nullable|string|max:20|unique:employees,nhif_number',
        'nhifRate' => 'nullable|numeric|min:0|max:100',
        'workersCompensation' => 'nullable|numeric|min:0',
        'lifeInsurance' => 'nullable|numeric|min:0',
        'taxCategory' => 'required|in:A,B,C',
        'payeRate' => 'required|numeric|min:0|max:100',
        
        // System Access
        'password' => 'required|min:8|confirmed',
        'selectedRoles' => 'required|array|min:1',
        
        // File Uploads
        'profilePhoto' => 'nullable|image|max:2048',
        'cv' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        'nationalId' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        'employmentContract' => 'nullable|file|mimes:pdf|max:2048',
    ];

    protected $messages = [
        'firstName.required' => 'First name is required.',
        'lastName.required' => 'Last name is required.',
        'email.required' => 'Email address is required.',
        'email.unique' => 'This email address is already registered.',
        'phone.required' => 'Phone number is required.',
        'dob.required' => 'Date of birth is required.',
        'dob.before' => 'Date of birth must be in the past.',
        'gender.required' => 'Gender is required.',
        'maritalStatus.required' => 'Marital status is required.',
        'nationality.required' => 'Nationality is required.',
        'nida.required' => 'NIDA number is required.',
        'nida.unique' => 'This NIDA number is already registered.',
        'tin.required' => 'TIN number is required.',
        'tin.unique' => 'This TIN number is already registered.',
        'physicalAddress.required' => 'Physical address is required.',
        'city.required' => 'City is required.',
        'region.required' => 'Region is required.',
        'emergencyContactName.required' => 'Emergency contact name is required.',
        'emergencyContactRelationship.required' => 'Emergency contact relationship is required.',
        'emergencyContactPhone.required' => 'Emergency contact phone is required.',
        'department_id.required' => 'Department is required.',
        'branch_id.required' => 'Branch is required.',
        'role_id.required' => 'Role is required.',
        'employment_type.required' => 'Employment type is required.',
        'start_date.required' => 'Start date is required.',
        'start_date.after_or_equal' => 'Start date must be today or in the future.',
        'job_title.required' => 'Job title is required.',
        'employeeId.required' => 'Employee ID is required.',
        'employeeId.unique' => 'This Employee ID is already taken.',
        'basicSalary.required' => 'Basic salary is required.',
        'basicSalary.numeric' => 'Basic salary must be a number.',
        'paymentFrequency.required' => 'Payment frequency is required.',
        'nssfNumber.required' => 'NSSF number is required.',
        'nssfNumber.unique' => 'This NSSF number is already registered.',
        'nhifNumber.required' => 'NHIF number is required.',
        'nhifNumber.unique' => 'This NHIF number is already registered.',
        'workersCompensation.required' => 'Workers compensation option is required.',
        'lifeInsurance.required' => 'Life insurance option is required.',
        'taxCategory.required' => 'Tax category is required.',
        'payeRate.required' => 'PAYE rate is required.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 8 characters.',
        'password.confirmed' => 'Password confirmation does not match.',
        'selectedRoles.required' => 'Please select at least one role.',
        'selectedRoles.min' => 'Please select at least one role.',
        'profilePhoto.image' => 'Profile photo must be an image.',
        'profilePhoto.max' => 'Profile photo must not exceed 2MB.',
        'cv.file' => 'CV must be a file.',
        'cv.mimes' => 'CV must be a PDF, DOC, or DOCX file.',
        'cv.max' => 'CV must not exceed 5MB.',
        'nationalId.file' => 'National ID must be a file.',
        'nationalId.mimes' => 'National ID must be a PDF, JPG, JPEG, or PNG file.',
        'nationalId.max' => 'National ID must not exceed 2MB.',
        'employmentContract.file' => 'Employment contract must be a file.',
        'employmentContract.mimes' => 'Employment contract must be a PDF file.',
        'employmentContract.max' => 'Employment contract must not exceed 2MB.',
    ];

    protected $listeners = ['departmentSelected' => 'loadDepartmentRoles'];

    protected function getRules()
    {
        $rules = $this->rules;
        
        // Make certain fields conditional based on user type
        if ($this->userType === 'governance') {
            // Board members don't need employment-specific fields
            unset($rules['emergencyContactName']);
            unset($rules['emergencyContactRelationship']);
            unset($rules['emergencyContactPhone']);
            unset($rules['emergencyContactEmail']);
            unset($rules['branch_id']);
            unset($rules['reporting_manager_id']);
            unset($rules['employment_type']);
            unset($rules['employeeId']);
            unset($rules['basicSalary']);
            unset($rules['paymentFrequency']);
            unset($rules['nssfNumber']);
            unset($rules['nssfRate']);
            unset($rules['nhifNumber']);
            unset($rules['nhifRate']);
            unset($rules['workersCompensation']);
            unset($rules['lifeInsurance']);
            unset($rules['taxCategory']);
            unset($rules['payeRate']);
        } else {
            // Staff members need all fields
            // Keep all rules as they are
        }
        
        if ($this->editingUser) {
            $rules['email'] = ['required', 'email', Rule::unique('users')->ignore($this->editingUser->id)];
            $rules['employeeId'] = ['required', Rule::unique('users')->ignore($this->editingUser->id)];
            $rules['nida'] = ['required', Rule::unique('employees')->ignore($this->editingUser->employee->id ?? 0)];
            $rules['tin'] = ['required', Rule::unique('employees')->ignore($this->editingUser->employee->id ?? 0)];
            $rules['nssfNumber'] = ['required', Rule::unique('employees')->ignore($this->editingUser->employee->id ?? 0)];
            $rules['nhifNumber'] = ['required', Rule::unique('employees')->ignore($this->editingUser->employee->id ?? 0)];
            $rules['password'] = 'nullable|min:8|confirmed';
        }
        
        return $rules;
    }

    public function mount()
    {
        $this->departments = Department::with('roles')->get();
        $this->branches = Branch::all();
        $this->managers = User::where('status', 'active')->get();
        $this->allRoles = Role::orderBy('name')->get();
        $this->loadDepartmentRoles();
        $this->calculateStatistics();
    }

    public function loadDepartmentRoles()
    {
        if ($this->department_id) {
            // Get the selected department
            $selectedDepartment = Department::find($this->department_id);
            
            if ($selectedDepartment) {
                // Load roles based on user type and department
                if ($this->userType === 'governance') {
                    // For governance users, load roles from governance departments
                    if ($selectedDepartment->level === 1 && $selectedDepartment->department_code === 'GOV') {
                        // Parent governance department - load roles for all child departments
                        $childDepartmentIds = Department::where('parent_department_id', $this->department_id)
                            ->pluck('id')
                            ->toArray();
                        $childDepartmentIds[] = $this->department_id; // Include parent department
                        
                        $this->availableRoles = Role::whereIn('department_id', $childDepartmentIds)
                            ->where('department_specific', true)
                            ->orderBy('name')
                            ->get();
                    } else {
                        // Child governance department - load roles for this specific department
                        $this->availableRoles = Role::where('department_id', $this->department_id)
                            ->where('department_specific', true)
                            ->orderBy('name')
                            ->get();
                    }
                } else {
                    // For operational users, load roles from operational departments
                    if ($selectedDepartment->level === 1 && $selectedDepartment->department_code === 'CMO') {
                        // Parent operational department - load roles for all child departments
                        $childDepartmentIds = Department::where('parent_department_id', $this->department_id)
                            ->pluck('id')
                            ->toArray();
                        $childDepartmentIds[] = $this->department_id; // Include parent department
                        
                        $this->availableRoles = Role::whereIn('department_id', $childDepartmentIds)
                            ->where('department_specific', true)
                            ->orderBy('name')
                            ->get();
                    } else {
                        // Child operational department - load roles for this specific department
                        $this->availableRoles = Role::where('department_id', $this->department_id)
                            ->where('department_specific', true)
                            ->orderBy('name')
                            ->get();
                    }
                }
                
                // Also include system roles that are appropriate for the user type
                $systemRoles = Role::where('is_system_role', true)
                    ->where(function($query) {
                        if ($this->userType === 'governance') {
                            $query->whereIn('name', ['Institution Administrator']);
                        } else {
                            $query->whereIn('name', ['System Administrator', 'Department Manager']);
                        }
                    })
                    ->get();
                
                $this->availableRoles = $this->availableRoles->merge($systemRoles)->sortBy('name');
                
                if ($this->availableRoles->count() > 0) {
                    $this->selectedRoles = [$this->availableRoles->first()->id];
                }
            }
        } else {
            $this->availableRoles = collect([]);
            $this->selectedRoles = [];
        }
    }

    public function updatedDepartmentId($value)
    {
        $this->loadDepartmentRoles();
        $this->role_id = ''; // Reset role selection when department changes
        $this->resetErrorBag(); // Clear validation errors
    }

    public function updatedUserType($value)
    {
        // Reset step when user type changes
        $this->currentStep = 0;
        
        // Clear form data when switching user types (but keep the userType)
        $this->clearFormData();
        
        // Clear validation errors
        $this->resetErrorBag();
        
        // Load appropriate roles based on user type
        $this->loadDepartmentRoles();
    }

    private function clearFormData()
    {
        // Personal Information
        $this->firstName = '';
        $this->middleName = '';
        $this->lastName = '';
        $this->email = '';
        $this->phone = '';
        $this->dob = '';
        $this->gender = '';
        $this->maritalStatus = '';
        $this->nationality = '';
        $this->nida = '';
        $this->tin = '';
        $this->physicalAddress = '';
        $this->city = '';
        $this->region = '';
        
        // Emergency Contact
        $this->emergencyContactName = '';
        $this->emergencyContactRelationship = '';
        $this->emergencyContactPhone = '';
        $this->emergencyContactEmail = '';
        
        // Employment Information
        $this->department_id = '';
        $this->branch_id = '';
        $this->role_id = '';
        $this->reporting_manager_id = '';
        $this->employment_type = '';
        $this->start_date = '';
        $this->job_title = '';
        $this->employeeId = '';
        $this->status = 'ACTIVE';
        
        // Payroll Information
        $this->basicSalary = '';
        $this->paymentFrequency = '';
        $this->nssfNumber = '';
        $this->nssfRate = '';
        $this->nhifNumber = '';
        $this->nhifRate = '';
        $this->workersCompensation = '';
        $this->lifeInsurance = '';
        $this->taxCategory = '';
        $this->payeRate = '';
        
        // System Access
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRoles = [];
        $this->sendCredentials = true;
        
        // File Uploads
        $this->profilePhoto = null;
        $this->cv = null;
        $this->nationalId = null;
        $this->employmentContract = null;
    }

    public function generateSecurePassword()
    {
        return Str::random(12);
    }

    // Statistics calculation
    public function calculateStatistics()
    {
        $this->totalUsers = User::count();
        $this->activeUsers = User::where('status', 'ACTIVE')->count();
        $this->inactiveUsers = User::where('status', 'INACTIVE')->count();
        $this->totalDepartments = Department::count();
    }

    // Sorting functionality
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // Bulk actions
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedUsers = User::pluck('id')->map(function($id) {
                return (string) $id;
            })->toArray();
        } else {
            $this->selectedUsers = [];
        }
    }

    public function clearSelection()
    {
        $this->selectedUsers = [];
        $this->selectAll = false;
    }

    public function bulkActivate()
    {
        if (empty($this->selectedUsers)) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'warning',
                'message' => 'Please select users to activate'
            ]);
            return;
        }

        try {
            User::whereIn('id', $this->selectedUsers)->update(['status' => 'ACTIVE']);
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => count($this->selectedUsers) . ' users activated successfully!'
            ]);
            
            $this->clearSelection();
        } catch (\Exception $e) {
            Log::error('Error in bulk activate: ' . $e->getMessage());
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Failed to activate users: ' . $e->getMessage()
            ]);
        }
    }

    public function bulkDeactivate()
    {
        if (empty($this->selectedUsers)) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'warning',
                'message' => 'Please select users to deactivate'
            ]);
            return;
        }

        try {
            User::whereIn('id', $this->selectedUsers)->update(['status' => 'INACTIVE']);
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => count($this->selectedUsers) . ' users deactivated successfully!'
            ]);
            
            $this->clearSelection();
        } catch (\Exception $e) {
            Log::error('Error in bulk deactivate: ' . $e->getMessage());
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Failed to deactivate users: ' . $e->getMessage()
            ]);
        }
    }

    public function bulkDelete()
    {
        if (empty($this->selectedUsers)) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'warning',
                'message' => 'Please select users to delete'
            ]);
            return;
        }

        try {
            DB::beginTransaction();
            
            // Delete user roles first
            DB::table('user_roles')->whereIn('user_id', $this->selectedUsers)->delete();
            
            // Delete users
            User::whereIn('id', $this->selectedUsers)->delete();
            
            DB::commit();
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => count($this->selectedUsers) . ' users deleted successfully!'
            ]);
            
            $this->clearSelection();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk delete: ' . $e->getMessage());
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Failed to delete users: ' . $e->getMessage()
            ]);
        }
    }

    // Enhanced CRUD operations
    public function viewUser($userId)
    {
        $this->viewingUser = User::with(['roles.permissions', 'department', 'employee.branch'])->find($userId);
        $this->showViewUser = true;
    }

    public function managePermissions($userId)
    {
        $this->editingUser = User::with(['roles.permissions', 'department'])->find($userId);
        $this->showManagePermissions = true;
    }

    public function toggleUserStatus($userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }

            $newStatus = $user->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
            $user->update(['status' => $newStatus]);

            $statusText = $newStatus === 'ACTIVE' ? 'activated' : 'deactivated';
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => "User {$statusText} successfully!"
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling user status: ' . $e->getMessage());
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Failed to update user status: ' . $e->getMessage()
            ]);
        }
    }

    public function updateUserPermissions()
    {
        try {
            $this->validate([
                'selectedRoles' => 'required|array|min:1',
            ]);

            $this->editingUser->roles()->sync($this->selectedRoles);

            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'User permissions updated successfully!'
            ]);

            $this->showManagePermissions = false;
            $this->editingUser = null;
            $this->selectedRoles = [];
        } catch (\Exception $e) {
            Log::error('Error updating user permissions: ' . $e->getMessage());
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Failed to update permissions: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        try {
            $query = User::query()
                ->with(['roles.permissions', 'department', 'employee.branch'])
                ->when($this->search, function($query) {
                    $query->where(function($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%')
                          ->orWhere('employeeId', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->department_code, function($query) {
                    $query->where('department_code', $this->department_code);
                })
                ->when($this->roleFilter, function($query) {
                    $query->whereHas('roles', function($q) {
                        $q->where('roles.id', $this->roleFilter);
                    });
                })
                ->when($this->statusFilter !== '', function($query) {
                    $query->where('status', $this->statusFilter == 1 ? 'ACTIVE' : 'INACTIVE');
                })
                ->orderBy($this->sortField, $this->sortDirection);

            $users = $query->paginate(10);

            //dd($users);

            // Update statistics
            $this->calculateStatistics();

            return view('livewire.users.users', [
                'users' => $users,
                'departments' => $this->departments,
                'branches' => $this->branches,
                'managers' => $this->managers,
                'allRoles' => $this->allRoles,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in Users component render method: ' . $e->getMessage(), [
                'search' => $this->search,
                'department_code' => $this->department_code,
                'roleFilter' => $this->roleFilter,
                'statusFilter' => $this->statusFilter,
                'trace' => $e->getTraceAsString()
            ]);

            // Create an empty paginated result
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                10,
                1,
                ['path' => request()->url(), 'pageName' => 'page']
            );

            return view('livewire.users.users', [
                'users' => $emptyPaginator,
                'departments' => $this->departments ?? collect([]),
                'branches' => $this->branches ?? collect([]),
                'managers' => $this->managers ?? collect([]),
                'allRoles' => $this->allRoles ?? collect([]),
            ]);
        }
    }

    public function create()
    {
        $this->resetForm();
        $this->showCreateUser = true;
        $this->resetErrorBag(); // Clear any previous validation errors
    }

    public function editUser($userId)
    {
        $this->editingUser = User::with(['roles', 'department', 'employee'])->find($userId);
        
        if ($this->editingUser) {
            // Personal Information
            $this->firstName = $this->editingUser->employee->first_name ?? '';
            $this->middleName = $this->editingUser->employee->middle_name ?? '';
            $this->lastName = $this->editingUser->employee->last_name ?? '';
            $this->email = $this->editingUser->email;
            $this->phone = $this->editingUser->employee->phone ?? '';
            $this->dob = $this->editingUser->employee->date_of_birth ?? '';
            $this->gender = $this->editingUser->employee->gender ?? '';
            $this->maritalStatus = $this->editingUser->employee->marital_status ?? '';
            $this->nationality = $this->editingUser->employee->nationality ?? '';
            $this->nida = $this->editingUser->employee->nida_number ?? '';
            $this->tin = $this->editingUser->employee->tin_number ?? '';
            $this->physicalAddress = $this->editingUser->employee->address ?? '';
            $this->city = $this->editingUser->employee->city ?? '';
            $this->region = $this->editingUser->employee->region ?? '';
            
            // Emergency Contact
            $this->emergencyContactName = $this->editingUser->employee->emergency_contact_name ?? '';
            $this->emergencyContactRelationship = $this->editingUser->employee->emergency_contact_relationship ?? '';
            $this->emergencyContactPhone = $this->editingUser->employee->emergency_contact_phone ?? '';
            $this->emergencyContactEmail = $this->editingUser->employee->emergency_contact_email ?? '';
            
            // Employment Information
            $this->department_id = $this->editingUser->department_id;
            $this->branch_id = $this->editingUser->employee->branch_id ?? '';
            $this->role_id = $this->editingUser->employee->role_id ?? '';
            $this->reporting_manager_id = $this->editingUser->employee->reporting_manager_id ?? '';
            $this->employment_type = $this->editingUser->employee->employment_type ?? '';
            $this->start_date = $this->editingUser->employee->hire_date ?? '';
            $this->job_title = $this->editingUser->employee->job_title ?? '';
            $this->employeeId = $this->editingUser->employeeId;
            $this->status = $this->editingUser->status;
            
            // Payroll Information
            $this->basicSalary = $this->editingUser->employee->basic_salary ?? '';
            $this->paymentFrequency = $this->editingUser->employee->payment_frequency ?? '';
            $this->nssfNumber = $this->editingUser->employee->nssf_number ?? '';
            $this->nssfRate = $this->editingUser->employee->nssf_rate ?? '';
            $this->nhifNumber = $this->editingUser->employee->nhif_number ?? '';
            $this->nhifRate = $this->editingUser->employee->nhif_rate ?? '';
            $this->workersCompensation = $this->editingUser->employee->workers_compensation ?? '';
            $this->lifeInsurance = ($this->editingUser->employee && $this->editingUser->employee->life_insurance) ? 'yes' : 'no';
            $this->taxCategory = $this->editingUser->employee->tax_category ?? '';
            $this->payeRate = $this->editingUser->employee->paye_rate ?? '';
            
            // System Access
            $this->selectedRoles = $this->editingUser->roles->pluck('id')->toArray();
            
            $this->loadDepartmentRoles();
        }
        
        $this->showCreateUser = true;
    }

    public function saveUser()
    {
        //dd('hh');
        try {
            $this->validate($this->getRules());
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation failed - errors are automatically set on the component
            // and will be displayed in the blade template
            Log::error('Validation failed: ' . $e->getMessage());
            return;
        }

        try {
            DB::beginTransaction();

            if ($this->editingUser) {
                // Update existing user and employee
                $this->updateExistingUser();
            } else {
                // Create new user and employee
                $this->createNewUser();
            }

            DB::commit();
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => $this->editingUser ? 'User updated successfully!' : 'User created successfully!'
            ]);

            $this->showCreateUser = false;
            $this->resetForm();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error saving user: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Failed to save user: ' . $e->getMessage()
            ]);
        }
    }

    private function createNewUser()
    {

    
        // Generate password if not provided
        $password = $this->password ?: $this->generateSecurePassword();
        
        // Generate employee ID for staff members
        $employeeId = $this->userType === 'operational' ? $this->employeeId : null;
        
        // Get department code from department_id
        $department = Department::find($this->department_id);
        $departmentCode = $department ? $department->department_code : null;
        
        // Create user account
        $user = User::create([
            'name' => $this->firstName . ' ' . $this->middleName . ' ' . $this->lastName,
            'email' => $this->email,
            'password' => Hash::make($password),
            'employeeId' => $employeeId,
            'department_code' => $departmentCode,
            'branch' => $this->branch_id,
            'status' => 'ACTIVE',
            'password_changed_at' => now(),
        ]);

        // Prepare employee data based on user type
        $employeeData = [
            'first_name' => $this->firstName,
            'middle_name' => $this->middleName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->dob,
            'gender' => $this->gender,
            'marital_status' => $this->maritalStatus,
            'nationality' => $this->nationality,
            'address' => $this->physicalAddress,
            'city' => $this->city,
            'region' => $this->region,
            'department_id' => !empty($this->department_id) ? (int)$this->department_id : null,
            'role_id' => !empty($this->role_id) ? (int)$this->role_id : null,
            'hire_date' => $this->start_date,
            'job_title' => $this->job_title,
            'employee_status' => $this->status,
            'tin_number' => $this->tin,
            'nida_number' => $this->nida,
            'user_id' => $user->id,
        ];

        // Add staff-specific fields for operational users
        if ($this->userType === 'operational') {
            $employeeData = array_merge($employeeData, [
                'emergency_contact_name' => $this->emergencyContactName,
                'emergency_contact_relationship' => $this->emergencyContactRelationship,
                'emergency_contact_phone' => $this->emergencyContactPhone,
                'emergency_contact_email' => $this->emergencyContactEmail,
                'branch_id' => !empty($this->branch_id) ? (int)$this->branch_id : null,
                'reporting_manager_id' => !empty($this->reporting_manager_id) ? (int)$this->reporting_manager_id : null,
                'employment_type' => $this->employment_type,
                'basic_salary' => $this->basicSalary !== '' ? (float)$this->basicSalary : null,
                'payment_frequency' => $this->paymentFrequency,
                'nssf_number' => $this->nssfNumber,
                'nssf_rate' => $this->nssfRate !== '' ? (float)$this->nssfRate : null,
                'nhif_number' => $this->nhifNumber,
                'nhif_rate' => $this->nhifRate !== '' ? (float)$this->nhifRate : null,
                'workers_compensation' => $this->workersCompensation !== '' ? (float)$this->workersCompensation : null,
                'life_insurance' => $this->lifeInsurance !== '' ? (float)$this->lifeInsurance : null,
                'tax_category' => $this->taxCategory,
                'paye_rate' => $this->payeRate !== '' ? (float)$this->payeRate : null,
            ]);
        }

        // Create employee record
        $employee = Employee::create($employeeData);

        // Assign roles
        $user->roles()->attach($this->selectedRoles);

        // Handle file uploads
        $this->handleFileUploads($user, $employee);

        // --- SACCO Member Onboarding Logic ---
        // Register employee as SACCO member
        $memberNumber = app(\App\Services\MemberNumberGeneratorService::class)->generate();
        
        // Create client record
        $client = \App\Models\ClientsModel::create([
            'member_number' => $memberNumber,
            'account_number' => $memberNumber,
            'first_name' => $this->firstName,
            'middle_name' => $this->middleName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone_number' => $this->phone,
            'mobile_phone_number' => $this->phone,
            'client_status' => 'ACTIVE',
            'membership_type' => 'EMPLOYEE',
            'marital_status' => $this->maritalStatus,
            'gender' => $this->gender,
            'date_of_birth' => !empty($this->dob) ? $this->dob : null,
            'nationality' => $this->nationality,
            'tin_number' => $this->tin,
            'nida_number' => $this->nida,
            'address' => $this->physicalAddress,
            'main_address' => $this->physicalAddress,
            'street' => $this->street ?? null,
            'city' => $this->city,
            'region' => $this->region,
            'district' => $this->district ?? null,
            'ward' => $this->ward ?? null,
            'postal_code' => $this->postalCode ?? null,
            'next_of_kin_name' => $this->nextOfKinName ?? null,
            'next_of_kin_phone' => $this->nextOfKinPhone ?? null,
            'employment' => $this->employment_type ?? null,
            'employer_name' => config('app.name'),
            'occupation' => $this->job_title ?? null,
            'education_level' => $this->educationLevel ?? null,
            'basic_salary' => !empty($this->basicSalary) ? (float)$this->basicSalary : null,
            'gross_salary' => !empty($this->grossSalary) ? (float)$this->grossSalary : null,
            'tax_paid' => !empty($this->taxPaid) ? (float)$this->taxPaid : null,
            'pension' => !empty($this->pension) ? (float)$this->pension : null,
            'nhif' => !empty($this->nhif) ? (float)$this->nhif : null,
            'registration_date' => now(),
            'branch_id' => !empty($this->branch_id) ? (int)$this->branch_id : null,
            'employee_id' => $employee->id
        ]);

        // Associate the models
        $employee->update([
            'client_id' => $client->id
        ]);
        $client->update([
            'user_id' => $user->id
        ]);
        // $user->update([
        //     'client_id' => $client->id
        // ]);

        // Update approval request
        $approvalData = [
      
            'process_name' => 'new_member_registration',
            'process_description' => auth()->user()->name . ' has requested to register a new member: ' . $this->firstName . ' ' . $this->lastName,
            'approval_process_description' => 'New member registration approval required',
            'process_code' => 'MEMBER_REG',
            'process_id' => $client->id,
            'process_status' => 'PENDING',
            'user_id' => auth()->user()->id,
            'team_id' => auth()->user()->current_team_id,
            'approver_id' => auth()->user()->id,
            'approval_status' => 'PENDING',
            'edit_package' => null
        ];
        $approval = \App\Models\Approval::create($approvalData);

        // Create mandatory accounts
        $accountService = new \App\Services\AccountCreationService();
        $institution = DB::table('institutions')->where('id', 1)->first();
        $branch_number = '01';
        $mandatorySharesAccount = $institution->mandatory_shares_account;
        $mandatorySavingsAccount = $institution->mandatory_savings_account;
        $mandatoryDepositsAccount = $institution->mandatory_deposits_account;
        $sharesAccount = $accountService->createAccount([
            'account_use' => 'external',
            'account_name' => $client->first_name . ' ' . $client->last_name,
            'type' => 'capital_accounts',
            'product_number' => '1000',
            'member_number' => $client->member_number,
            'branch_number' => $branch_number
        ], $mandatorySharesAccount);
        $savingsAccount = $accountService->createAccount([
            'account_use' => 'external',
            'account_name' => $client->first_name . ' ' . $client->last_name,
            'type' => 'savings_accounts',
            'product_number' => '2000',
            'member_number' => $client->member_number,
            'branch_number' => $branch_number
        ], $mandatorySavingsAccount);
        $depositsAccount = $accountService->createAccount([
            'account_use' => 'external',
            'account_name' => $client->first_name . ' ' . $client->last_name,
            'type' => 'deposits_accounts',
            'product_number' => '3000',
            'member_number' => $client->member_number,
            'branch_number' => $branch_number
        ], $mandatoryDepositsAccount);

        // Generate control numbers for mandatory services
        $billingService = new \App\Services\BillingService();
        $services = DB::table('services')
            ->whereIn('code', ['REG', 'SHC'])
            ->select('id', 'code', 'name', 'is_recurring', 'payment_mode', 'lower_limit')
            ->get()
            ->keyBy('code');
        $controlNumbers = [];
        foreach (['REG', 'SHC'] as $serviceCode) {
            $service = $services[$serviceCode];
            $controlNumber = $billingService->generateControlNumber(
                $client->member_number,
                $service->id,
                $service->is_recurring,
                $service->payment_mode
            );
            $controlNumbers[] = [
                'service_code' => $service->code,
                'control_number' => $controlNumber,
                'amount' => $service->lower_limit
            ];
        }
        foreach ($controlNumbers as $control) {
            $service = DB::table('services')
                ->where('code', $control['service_code'])
                ->first();
            if ($service) {
                $bill = $billingService->createBill(
                    $client->member_number,
                    $service->id,
                    $service->is_recurring,
                    $service->payment_mode,
                    $control['control_number'],
                    $service->lower_limit
                );
            }
        }
        // Send welcome email with control numbers and login credentials
        $client->notify(new \App\Notifications\NewMemberWelcomeNotification(
            $client,
            $controlNumbers,
            $sharesAccount,
            $savingsAccount,
            $depositsAccount,
            $password
        ));
        // --- End SACCO Member Onboarding Logic ---

    

        Log::info('New user and employee created successfully', [
            'user_id' => $user->id,
            'employee_id' => $employee->id,
            'email' => $user->email
        ]);
    }

    private function updateExistingUser()
    {
        // Get department code from department_id
        $department = Department::find($this->department_id);
        $departmentCode = $department ? $department->department_code : null;
        
        $updateData = [
            'name' => $this->firstName . ' ' . $this->middleName . ' ' . $this->lastName,
            'email' => $this->email,
            'department_code' => $departmentCode,
            'branch' => $this->branch_id,
            'status' => $this->status,
            'employeeId' => $this->employeeId,
        ];
        
        if ($this->password) {
            $updateData['password'] = Hash::make($this->password);
            $updateData['password_changed_at'] = now();
        }
        
        $this->editingUser->update($updateData);
        
        // Update employee record
        if ($this->editingUser->employee) {
            $this->editingUser->employee->update([
                'first_name' => $this->firstName,
                'middle_name' => $this->middleName,
                'last_name' => $this->lastName,
                'email' => $this->email,
                'phone' => $this->phone,
                'date_of_birth' => $this->dob,
                'gender' => $this->gender,
                'marital_status' => $this->maritalStatus,
                'nationality' => $this->nationality,
                'address' => $this->physicalAddress,
                'city' => $this->city,
                'region' => $this->region,
                'emergency_contact_name' => $this->emergencyContactName,
                'emergency_contact_relationship' => $this->emergencyContactRelationship,
                'emergency_contact_phone' => $this->emergencyContactPhone,
                'emergency_contact_email' => $this->emergencyContactEmail,
                'branch_id' => !empty($this->branch_id) ? (int)$this->branch_id : null,
                'reporting_manager_id' => !empty($this->reporting_manager_id) ? (int)$this->reporting_manager_id : null,
                'employment_type' => $this->employment_type,
                'basic_salary' => $this->basicSalary !== '' ? (float)$this->basicSalary : null,
                'payment_frequency' => $this->paymentFrequency,
                'nssf_number' => $this->nssfNumber,
                'nssf_rate' => $this->nssfRate !== '' ? (float)$this->nssfRate : null,
                'nhif_number' => $this->nhifNumber,
                'nhif_rate' => $this->nhifRate !== '' ? (float)$this->nhifRate : null,
                'workers_compensation' => $this->workersCompensation !== '' ? (float)$this->workersCompensation : null,
                'life_insurance' => $this->lifeInsurance !== '' ? (float)$this->lifeInsurance : null,
                'tax_category' => $this->taxCategory,
                'paye_rate' => $this->payeRate !== '' ? (float)$this->payeRate : null,
                'tin_number' => $this->tin,
                'nida_number' => $this->nida,
            ]);
        }
        
        // Update roles
        $this->editingUser->roles()->sync($this->selectedRoles);

        // Handle file uploads
        $this->handleFileUploads($this->editingUser, $this->editingUser->employee);

        Log::info('User and employee updated successfully', [
            'user_id' => $this->editingUser->id,
            'email' => $this->editingUser->email
        ]);
    }

    private function handleFileUploads($user, $employee)
    {
        // Handle profile photo
        if ($this->profilePhoto) {
            $path = $this->profilePhoto->store('profile-photos', 'public');
            $user->update(['profile_photo_path' => $path]);
        }

        // Handle other documents
        if ($this->cv) {
            $path = $this->cv->store('employee-documents/cv', 'public');
            // Store in employee documents table or similar
        }

        if ($this->nationalId) {
            $path = $this->nationalId->store('employee-documents/national-id', 'public');
            // Store in employee documents table or similar
        }

        if ($this->employmentContract) {
            $path = $this->employmentContract->store('employee-documents/contracts', 'public');
            // Store in employee documents table or similar
        }
    }

    private function sendWelcomeEmail($user, $password)
    {
        try {
            $user->notify(new NewMemberWelcomeNotification(
                $user,
                null, // control number
                null, // shares account
                null, // savings account
                null, // deposits account
                $password
            ));

            Log::info('Welcome email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteUser($userId)
    {
        $this->editingUser = User::find($userId);
        $this->showDeleteUser = true;
    }

    public function confirmDeleteUser()
    {
        try {
            DB::beginTransaction();

            if ($this->editingUser) {
                // Remove role assignments
                $this->editingUser->roles()->detach();
                
                // Delete associated employee record
                if ($this->editingUser->employee) {
                    $this->editingUser->employee->delete();
                }
                
                // Delete the user
                $this->editingUser->delete();
                
                session()->flash('message', 'User deleted successfully.');
            }
            
            DB::commit();
            $this->showDeleteUser = false;
            $this->resetForm();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while deleting the user: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->editingUser = null;
        $this->currentStep = 0;
        $this->userType = '';
        
        // Personal Information
        $this->firstName = '';
        $this->middleName = '';
        $this->lastName = '';
        $this->email = '';
        $this->phone = '';
        $this->dob = '';
        $this->gender = '';
        $this->maritalStatus = '';
        $this->nationality = '';
        $this->nida = '';
        $this->tin = '';
        $this->physicalAddress = '';
        $this->city = '';
        $this->region = '';
        
        // Emergency Contact
        $this->emergencyContactName = '';
        $this->emergencyContactRelationship = '';
        $this->emergencyContactPhone = '';
        $this->emergencyContactEmail = '';
        
        // Employment Information
        $this->department_id = '';
        $this->branch_id = '';
        $this->role_id = '';
        $this->reporting_manager_id = '';
        $this->employment_type = '';
        $this->start_date = '';
        $this->job_title = '';
        $this->employeeId = '';
        $this->status = 'ACTIVE';
        
        // Payroll Information
        $this->basicSalary = '';
        $this->paymentFrequency = '';
        $this->nssfNumber = '';
        $this->nssfRate = '';
        $this->nhifNumber = '';
        $this->nhifRate = '';
        $this->workersCompensation = '';
        $this->lifeInsurance = '';
        $this->taxCategory = '';
        $this->payeRate = '';
        
        // System Access
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRoles = [];
        $this->sendCredentials = true;
        
        // File Uploads
        $this->profilePhoto = null;
        $this->cv = null;
        $this->nationalId = null;
        $this->employmentContract = null;
        
        // Additional Client Information
        $this->street = '';
        $this->district = '';
        $this->ward = '';
        $this->postalCode = '';
        $this->nextOfKinName = '';
        $this->nextOfKinPhone = '';
        $this->educationLevel = '';
        $this->grossSalary = '';
        $this->taxPaid = '';
        $this->pension = '';
        $this->nhif = '';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDepartmentCode()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function getDepartmentRoles($departmentId)
    {
        return Role::where('department_id', $departmentId)
            ->where('is_system_role', false)
            ->get();
    }

    public function nextStep()
    {
        // Validate current step before proceeding
        if (!$this->validateCurrentStep()) {
            return;
        }

        $maxSteps = $this->userType === 'governance' ? 4 : 7;
        if ($this->currentStep < $maxSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 0) {
            $this->currentStep--;
        }
    }

    private function validateCurrentStep()
    {
        switch ($this->currentStep) {
            case 0:
                // Validate user type selection
                if (empty($this->userType)) {
                    $this->addError('userType', 'Please select a user type.');
                    return false;
                }
                break;
                
            case 1:
                // Validate personal information
                $this->validate([
                    'firstName' => 'required|min:2|max:50',
                    'lastName' => 'required|min:2|max:50',
                    'email' => 'required|email|unique:users,email',
                    'phone' => 'required|min:10|max:15',
                    'dob' => 'required|date|before:today',
                    'gender' => 'required|in:male,female,other',
                    'maritalStatus' => 'required|in:single,married,divorced,widowed',
                    'nationality' => 'required|string|max:50',
                    'nida' => 'required|string|max:20|unique:employees,nida_number',
                    'tin' => 'required|string|max:20|unique:employees,tin_number',
                    'physicalAddress' => 'required|string|max:255',
                    'city' => 'required|string|max:100',
                    'region' => 'required|string|max:100',
                ]);
                break;
                
            case 2:
                if ($this->userType === 'operational') {
                    // Validate emergency contact for operational users
                    $this->validate([
                        'emergencyContactName' => 'required|string|max:100',
                        'emergencyContactRelationship' => 'required|string|max:50',
                        'emergencyContactPhone' => 'required|string|min:10|max:15',
                        'emergencyContactEmail' => 'nullable|email',
                    ]);
                } elseif ($this->userType === 'governance') {
                    // Validate governance role
                    $this->validate([
                        'department_id' => 'required|exists:departments,id',
                        'role_id' => 'required|exists:roles,id',
                        'job_title' => 'required|string|max:100',
                        'start_date' => 'required|date|after_or_equal:today',
                    ]);
                }
                break;
                
            case 3:
                if ($this->userType === 'operational') {
                    // Validate employment information
                    $this->validate([
                        'department_id' => 'required|exists:departments,id',
                        'branch_id' => 'required|exists:branches,id',
                        'role_id' => 'required|exists:roles,id',
                        'employment_type' => 'required|in:full_time,part_time,contract,internship',
                        'start_date' => 'required|date|after_or_equal:today',
                        'job_title' => 'required|string|max:100',
                        'employeeId' => 'required|string|max:20|unique:users,employeeId',
                        'status' => 'required|in:ACTIVE,INACTIVE',
                    ]);
                } elseif ($this->userType === 'governance') {
                    // Validate system access for governance
                    $this->validate([
                        'password' => 'required|min:8|confirmed',
                        'selectedRoles' => 'required|array|min:1',
                    ]);
                }
                break;
                
            case 4:
                if ($this->userType === 'operational') {
                    // Validate payroll information
                    $this->validate([
                        'basicSalary' => 'nullable|numeric|min:0',
                        'paymentFrequency' => 'nullable|in:monthly,biweekly,weekly',
                        'nssfNumber' => 'nullable|string|max:20|unique:employees,nssf_number',
                        'nssfRate' => 'nullable|numeric|min:0|max:100',
                        'nhifNumber' => 'nullable|string|max:20|unique:employees,nhif_number',
                        'nhifRate' => 'nullable|numeric|min:0|max:100',
                        'workersCompensation' => 'nullable|numeric|min:0',
                        'lifeInsurance' => 'nullable|numeric|min:0',
                        'taxCategory' => 'required|in:A,B,C',
                        'payeRate' => 'required|numeric|min:0|max:100',
                    ]);
                }
                break;
                
            case 5:
                if ($this->userType === 'operational') {
                    // Document upload step - no validation required as files are optional
                    break;
                }
                break;
                
            case 6:
                if ($this->userType === 'operational') {
                    // Validate system access for operational users
                    $this->validate([
                        'password' => 'required|min:8|confirmed',
                        'selectedRoles' => 'required|array|min:1',
                    ]);
                }
                break;
        }
        
        return true;
    }
}
