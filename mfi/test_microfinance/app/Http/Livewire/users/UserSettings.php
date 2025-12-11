<?php

namespace App\Http\Livewire\Users;

use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use App\Models\Committee;
use App\Models\UserActionLog;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Actions\UpdateTeamClientRole;
use Laravel\Jetstream\Contracts\AddsTeamClients;
use Laravel\Jetstream\Contracts\InvitesTeamClients;
use Laravel\Jetstream\Contracts\RemovesTeamClients;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Role as JetstreamRole;
use App\Models\AccountsModel;
use Laravel\Jetstream\Team;
use App\Models\TeamUser;
use App\Models\departmentsList;
use App\Models\Branch;
use App\Models\Committee as CommitteeModel;
use App\Models\Institution;
use App\Models\Department as DepartmentModel;
use Illuminate\Support\Facades\Hash;

class UserSettings extends Component
{
    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * Indicates if a user's role is currently being managed.
     *
     * @var bool
     */
    public $currentlyManagingRole = false;

    /**
     * The user that is having their role managed.
     *
     * @var mixed
     */
    public $managingRoleFor;

    /**
     * The current role for the user that is having their role managed.
     *
     * @var string
     */
    public $currentRole;

    /**
     * Indicates if the application is confirming if a user wishes to leave the current team.
     *
     * @var bool
     */
    public $confirmingLeavingTeam = false;

    /**
     * Indicates if the application is confirming if a team member should be removed.
     *
     * @var bool
     */
    public $confirmingTeamClientRemoval = false;

    /**
     * The ID of the team member being removed.
     *
     * @var int|null
     */
    public $teamClientIdBeingRemoved = null;

    public $accounts;
    public $user;
    public $pendingUsers;
    public $department;
    public $departmentList;
    public $pendinguser;
    public $userrole;
    public $department_code;
    public $selectedCommittees = [];
    public $branch;
    public $institution_id;
    public $departments;
    public $committees;
    public $branches;
    public $institutions;

    /**
     * The "add team member" form state.
     *
     * @var array
     */
    public $addTeamClientForm = [
        'email' => '',
        'role' => null,
    ];

    public $name;
    public $email;
    public $department_id;
    public $roles = [];
    public $recentActivities = [];
    public $current_password;
    public $new_password;
    public $new_password_confirmation;
    public $notifications = [
        'email' => false,
        'sms' => false,
        'app' => false
    ];
    public $twoFactorEnabled = false;

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email',
        'department_id' => 'required|exists:departments,id',
        'roles' => 'required|array|min:1',
        'current_password' => 'required_with:new_password|current_password',
        'new_password' => 'nullable|min:8|confirmed',
        'new_password_confirmation' => 'nullable|min:8',
        'notifications.email' => 'boolean',
        'notifications.sms' => 'boolean',
        'notifications.app' => 'boolean',
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->department_id = $user->department_id;
        $this->roles = $user->roles->pluck('id')->toArray();
        $this->committees = $user->committees;
        $this->recentActivities = UserActionLog::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();
        $this->twoFactorEnabled = $user->two_factor_secret !== null;

        // Load notification preferences
        $this->notifications = [
            'email' => $user->notification_preferences['email'] ?? false,
            'sms' => $user->notification_preferences['sms'] ?? false,
            'app' => $user->notification_preferences['app'] ?? false,
        ];

        $this->user = auth()->user();
        $this->team = $this->user->currentTeam;
        $this->pendingUsers = User::get();
        // $institution = TeamUser::where('user_id',Auth::user()->id)->value('institution');
        $institution = User::where('id',Auth::user()->id)->value('institution_id');
        $this->departmentList = departmentsList::where('institution_id',$institution )->get();

        $this->departments = DepartmentModel::all();
        $this->committees = CommitteeModel::all();
        $this->branches = Branch::all();
        $this->institutions = Institution::all();
    }

    public function saveSettings()
    {
        $this->validate();

        $user = Auth::user();
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'department_id' => $this->department_id,
            'notification_preferences' => $this->notifications,
        ]);

        if ($this->new_password) {
            $user->update([
                'password' => Hash::make($this->new_password)
            ]);
        }

        $user->roles()->sync($this->roles);

        // Log the action
        UserActionLog::create([
            'user_id' => $user->id,
            'action_type' => 'UPDATE_PROFILE',
            'action_details' => 'Updated profile settings',
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        session()->flash('message', 'Settings updated successfully.');
    }

    public function save()
    {
        //dd('kk');

        //$this->validate();

        TeamUser::where('user_id',$this->pendinguser)->update([
            'role'=>$this->userrole,
            'institution'=>TeamUser::where('user_id',Auth::user()->id)->value('institution'),
            'team_id'=>TeamUser::where('user_id',Auth::user()->id)->value('team_id'),
            'department'=>$this->department
        ]);

        User::where('id',$this->pendinguser)->update([
            'branch_id'=>'OFFICER',
            'current_team_id'=>TeamUser::where('user_id',Auth::user()->id)->value('team_id'),
        ]);

        $this->pendinguser = null;
        $this->department = null;
        $this->userrole = null;
    }

    public function saveAssignments()
    {
        $user = Auth::user();
        $user->update([
            'department_code' => $this->department_code,
            'branch' => $this->branch,
            'institution_id' => $this->institution_id,
        ]);

        $user->committees()->sync($this->selectedCommittees);

        session()->flash('message', 'Assignments saved successfully.');
    }

    /**
     * Add a new team member to a team.
     *
     * @return void
     */
    public function addTeamClient()
    {
        $this->resetErrorBag();

        if (Features::sendsTeamInvitations()) {
            app(InvitesTeamClients::class)->invite(
                $this->user,
                $this->team,
                $this->addTeamClientForm['email'],
                $this->addTeamClientForm['role']
            );
        } else {
            app(AddsTeamClients::class)->add(
                $this->user,
                $this->team,
                $this->addTeamClientForm['email'],
                $this->addTeamClientForm['role']
            );
        }

        $this->addTeamClientForm = [
            'email' => '',
            'role' => null,
        ];

        $this->team = $this->team->fresh();

        $this->emit('saved');
    }

    /**
     * Cancel a pending team member invitation.
     *
     * @param int $invitationId
     * @return void
     */
    public function cancelTeamInvitation($invitationId)
    {
        if (!empty($invitationId)) {
            $model = Jetstream::teamInvitationModel();

            $model::whereKey($invitationId)->delete();
        }

        $this->team = $this->team->fresh();
    }

    /**
     * Allow the given user's role to be managed.
     *
     * @param int $userId
     * @return void
     */
    public function manageRole($userId)
    {
        $this->currentlyManagingRole = true;
        $this->managingRoleFor = Jetstream::findUserByIdOrFail($userId);
        $this->currentRole = $this->managingRoleFor->teamRole($this->team)->key;
    }

    /**
     * Save the role for the user being managed.
     *
     * @param \Laravel\Jetstream\Actions\UpdateTeamClientRole $updater
     * @return void
     */
    public function updateRole(UpdateTeamClientRole $updater)
    {
        $updater->update(
            $this->user,
            $this->team,
            $this->managingRoleFor->id,
            $this->currentRole
        );

        $this->team = $this->team->fresh();

        $this->stopManagingRole();
    }

    /**
     * Stop managing the role of a given user.
     *
     * @return void
     */
    public function stopManagingRole()
    {
        $this->currentlyManagingRole = false;
    }

    /**
     * Remove the currently authenticated user from the team.
     *
     * @param \Laravel\Jetstream\Contracts\RemovesTeamClients $remover
     * @return void
     */
    public function leaveTeam(RemovesTeamClients $remover)
    {
        $remover->remove(
            $this->user,
            $this->team,
            $this->user
        );

        $this->confirmingLeavingTeam = false;

        $this->team = $this->team->fresh();

        return redirect(config('fortify.home'));
    }

    /**
     * Confirm that the given team member should be removed.
     *
     * @param int $userId
     * @return void
     */
    public function confirmTeamClientRemoval($userId)
    {
        $this->confirmingTeamClientRemoval = true;

        $this->teamClientIdBeingRemoved = $userId;
    }

    /**
     * Remove a team member from the team.
     *
     * @param \Laravel\Jetstream\Contracts\RemovesTeamClients $remover
     * @return void
     */
    public function removeTeamClient(RemovesTeamClients $remover)
    {
        $remover->remove(
            $this->user,
            $this->team,
            $user = Jetstream::findUserByIdOrFail($this->teamClientIdBeingRemoved)
        );

        $this->confirmingTeamClientRemoval = false;

        $this->teamClientIdBeingRemoved = null;

        $this->team = $this->team->fresh();
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Auth::user();
    }

    /**
     * Get the available team member roles.
     *
     * @return array
     */
    public function getRolesProperty()
    {
        return collect(Jetstream::$roles)->transform(function ($role) {
            return with($role->jsonSerialize(), function ($data) {
                return (new JetstreamRole(
                    $data['key'],
                    $data['name'],
                    $data['permissions']
                ))->description($data['description']);
            });
        })->values()->all();
    }

    public function enableTwoFactor()
    {
        $user = Auth::user();
        $user->two_factor_secret = encrypt(random_bytes(32));
        $user->save();

        $this->twoFactorEnabled = true;

        // Log the action
        UserActionLog::create([
            'user_id' => $user->id,
            'action_type' => 'ENABLE_2FA',
            'action_details' => 'Enabled two-factor authentication',
        ]);

        session()->flash('message', 'Two-factor authentication has been enabled.');
    }

    public function disableTwoFactor()
    {
        $user = Auth::user();
        $user->two_factor_secret = null;
        $user->save();

        $this->twoFactorEnabled = false;

        // Log the action
        UserActionLog::create([
            'user_id' => $user->id,
            'action_type' => 'DISABLE_2FA',
            'action_details' => 'Disabled two-factor authentication',
        ]);

        session()->flash('message', 'Two-factor authentication has been disabled.');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $this->accounts = AccountsModel::where('sub_product_number', '19')->get();
        return view('livewire.users.user-settings', [
            'departments' => DepartmentModel::all(),
            'availableRoles' => Role::all(),
        ]);
    }
}
