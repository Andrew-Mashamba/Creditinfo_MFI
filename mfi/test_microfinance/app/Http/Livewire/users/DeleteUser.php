<?php

namespace App\Http\Livewire\Users;

use App\Models\approvals;
use App\Models\NodesList;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteUser extends Component
{
    public $password = null;
    public $userSelected;
    public $nodesList;
    public string $NODE_NAME;
    public string $userName;
    public $usersList;
    public $permission = 'BLOCKED';
    public $showConfirmModal = false;

    protected $rules = [
        'password' => 'required|min:8',
        'userSelected' => 'required|exists:users,id',
        'permission' => 'required|in:BLOCKED,ACTIVE,DELETED'
    ];

    protected $messages = [
        'password.required' => 'Please enter your password to confirm',
        'password.min' => 'Password must be at least 8 characters',
        'userSelected.required' => 'No user selected',
        'userSelected.exists' => 'Selected user does not exist',
        'permission.required' => 'Please select a status',
        'permission.in' => 'Invalid status selected'
    ];

    public function boot(): void
    {
        $this->nodesList = User::select('id', 'name', 'email')->get();
        $this->userName = '';
    }

    public function confirmDelete($userId)
    {
        $this->userSelected = $userId;
        $this->userName = User::where('id', $userId)->value('name');
        $this->showConfirmModal = true;
    }

    public function delete(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $user = User::where('id', $this->userSelected)->first();
            
            if (!$user) {
                throw new \Exception('User not found');
            }

            $action = match($this->permission) {
                'BLOCKED' => 'blockUser',
                'ACTIVE' => 'activateUser',
                'DELETED' => 'deleteUser',
                default => throw new \Exception('Invalid permission status')
            };

            approvals::updateOrCreate(
                [
                    'process_id' => $this->userSelected,
                    'user_id' => Auth::user()->id
                ],
                [
                    'institution' => '',
                    'process_name' => $action,
                    'process_description' => $this->permission . ' user - ' . $user->name,
                    'approval_process_description' => '',
                    'process_code' => '29',
                    'process_id' => $this->userSelected,
                    'process_status' => $this->permission,
                    'approval_status' => 'PENDING',
                    'user_id' => Auth::user()->id,
                    'team_id' => '',
                    'edit_package' => null
                ]
            );

            DB::commit();

            session()->flash('message', 'Awaiting approval');
            session()->flash('alert-class', 'alert-success');

            $this->emitUp('showUsersList');
            $this->resetForm();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating user status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('message', 'Error updating user status: ' . $e->getMessage());
            session()->flash('alert-class', 'alert-danger');
        }
    }

    public function confirmPassword(): void
    {
        $this->validate();

        if (Hash::check($this->password, auth()->user()->password)) {
            $this->delete();
        } else {
            session()->flash('message', 'This password does not match our records');
            session()->flash('alert-class', 'alert-warning');
        }
        
        $this->resetPassword();
    }

    public function resetPassword(): void
    {
        $this->password = null;
    }

    public function resetForm(): void
    {
        $this->reset(['password', 'userSelected', 'permission', 'showConfirmModal']);
    }

    public function render()
    {
        $this->usersList = User::select('id', 'name', 'email', 'status')->get();
        return view('livewire.users.delete-user');
    }
}
