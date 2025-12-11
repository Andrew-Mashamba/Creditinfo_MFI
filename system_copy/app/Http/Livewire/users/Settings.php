<?php

namespace App\Http\Livewire\Users;

use App\Models\approvals;
use App\Models\departmentsList;
use App\Models\User;
use App\Models\UserSubMenu;
use App\Models\RoleMenuAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class Settings extends Component
{

    use WithPagination;
    // Core properties

    public $showCreateUser = false;

    public $editingUser;

    public $availableRoles = [];
    public $activeUsers;
    public $inActiveUsers;
    public $tab_id = 6; 
    public $user_sub_menus;

    // User creation properties
    public $showCreateNewUser = false;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $department_code;
    public $selectedRole;
    public $selectedSubRole;
    public $selectedPermissions = [];
    public $availableSubRoles;
    public $availablePermissions;

    public $users = [];

    // User management properties
    public $showDeleteUser = false;
    public $showEditUser = false;
    public $userSelected;
    public $permission = 'BLOCKED';
    public $pendinguser;

    // Data collections
    public $departmentList;
    public $departments;
    public $usersList;

    protected $listeners = [
        'showUsersList' => 'showUsersList',
        'blockUser' => 'blockUserModal',
        'editUser' => 'editUserModal',
        'refreshStats' => 'loadUserStats'
    ];

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'department_code' => 'required',
        'selectedRole' => 'required',
        'selectedSubRole' => 'required',
        'selectedPermissions' => 'required|array|min:1'
    ];

    protected $messages = [
        'name.required' => 'Please enter the user\'s name',
        'name.min' => 'Name must be at least 3 characters',
        'email.required' => 'Please enter the user\'s email',
        'email.email' => 'Please enter a valid email address',
        'email.unique' => 'This email is already registered',
        'password.required' => 'Please enter a password',
        'password.min' => 'Password must be at least 8 characters',
        'password.confirmed' => 'Password confirmation does not match',
        'department_code.required' => 'Please select a department',
        'selectedRole.required' => 'Please select a role',
        'selectedSubRole.required' => 'Please select a sub-role',
        'selectedPermissions.required' => 'Please select at least one permission',
        'selectedPermissions.min' => 'Please select at least one permission'
    ];

    public function boot(): void
    {
        $this->loadUserStats();
        $this->user_sub_menus = UserSubMenu::where('menu_id', 8)
            ->where('user_id', Auth::user()->id)
            ->get();
    }

    public function render()
    {
        $this->loadUserStats();
        $this->departmentList = departmentsList::get();
        $this->departments = departmentsList::select('department_code as code', 'department_name as name')->get();
        $this->usersList = User::get();
        $this->users = User::paginate(10)->items();

        return view('livewire.users.users', [
            'paginatedUsers' => User::paginate(10)
        ]);
    }

    public function showUsersList(): void
    {
        $this->tab_id = 6;
    }

    public function setView($page): void
    {
        $this->tab_id = $page;
    }

    public function createNewUser()
    {
        $this->validate();

        try {
            DB::beginTransaction();
            
            Log::info('Creating new user', [
                'name' => $this->name,
                'email' => $this->email,
                'department_code' => $this->department_code,
                'role' => $this->selectedRole,
                'sub_role' => $this->selectedSubRole
            ]);

            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'department_code' => $this->department_code,
                'role' => $this->selectedRole,
                'sub_role' => $this->selectedSubRole,
                'status' => 'ACTIVE'
            ]);

            foreach ($this->selectedPermissions as $menuId => $actions) {
                RoleMenuAction::create([
                    'sub_role' => $this->selectedSubRole,
                    'menu_id' => $menuId,
                    'allowed_actions' => json_encode($actions)
                ]);
            }

            DB::commit();
            session()->flash('message', 'User created successfully.');
            $this->reset(['name', 'email', 'password', 'password_confirmation', 'department_code', 'selectedRole', 'selectedSubRole', 'selectedPermissions']);
            $this->showCreateNewUser = false;
            $this->loadUserStats();
            $this->emit('refreshStats');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to create user. Please try again.');
        }
    }

    public function blockUserModal($userId)
    {
        $this->userSelected = $userId;
        $this->permission = 'BLOCKED';
        $this->showDeleteUser = true;
    }

    public function editUserModal($id)
    {
        Log::info('Opening edit user modal', ['user_id' => $id]);
        $this->pendinguser = $id;
        $this->department_code = User::where('id', $id)->value('department_code');
        $this->showEditUser = true;
    }

    public function closeModal()
    {
        $this->showCreateNewUser = false;
        $this->showDeleteUser = false;
        $this->showEditUser = false;
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'department_code', 'selectedRole', 'selectedSubRole', 'selectedPermissions']);
    }

    public function updatedSelectedRole($value)
    {
        Log::info('Role selection updated', ['selected_role' => $value]);
        $this->selectedSubRole = null;
        $this->selectedPermissions = [];
        $this->availableSubRoles = departmentsList::where('role', $value)
            ->select('sub_role')
            ->distinct()
            ->get();
    }

    public function updatedSelectedSubRole($value)
    {
        Log::info('Sub-role selection updated', ['selected_sub_role' => $value]);
        $this->selectedPermissions = [];
        $this->loadPermissionsForSubRole($value);
    }

    public function loadPermissionsForSubRole($subRole)
    {
        Log::info('Loading permissions for sub-role', ['sub_role' => $subRole]);
        $this->availablePermissions = RoleMenuAction::where('sub_role', $subRole)
            ->with('menu')
            ->get()
            ->map(function ($roleMenu) {
                return [
                    'menu_id' => $roleMenu->menu_id,
                    'menu_name' => $roleMenu->menu->menu_name,
                    'allowed_actions' => json_decode($roleMenu->allowed_actions, true)
                ];
            });
    }

    public function loadUserStats()
    {
        $this->activeUsers = User::where('status', 'ACTIVE')->count();
        $this->inActiveUsers = User::where('status', '!=', 'ACTIVE')->count();
    }

    public function saveRole()
    {
        Log::info('Updating user role', [
            'user_id' => $this->pendinguser,
            'department' => $this->department_code
        ]);

        $data = [
            'department_code' => $this->department_code,
        ];

        approvals::updateOrCreate(
            [
                'process_id' => $this->pendinguser,
                'user_id' => Auth::user()->id
            ],
            [
                'institution' => '',
                'process_name' => 'editUser',
                'process_description' => 'A request to edit a ROLE of user - ' . User::where('id', $this->pendinguser)->value('name'),
                'approval_process_description' => '',
                'process_code' => '27',
                'process_id' => $this->pendinguser,
                'process_status' => 'PENDING',
                'approval_status' => 'PENDING',
                'user_id' => Auth::user()->id,
                'team_id' => '',
                'edit_package' => json_encode($data),
            ]
        );

        session()->flash('message', 'Role change request submitted successfully');
        session()->flash('alert-class', 'alert-success');

        $this->closeModal();
    }
}
