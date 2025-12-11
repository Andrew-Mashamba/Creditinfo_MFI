<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\SubRole;
use App\Models\Role;
use App\Models\Department;

class SyncUserRoles extends Command
{
    protected $signature = 'user-roles:sync';
    protected $description = 'Sync user roles with departments';

    public function handle()
    {
        $users = User::all();
        $departments = Department::all();

        foreach ($users as $user) {
            if ($user->department_id) {
                $department = $departments->firstWhere('id', $user->department_id);
                if ($department) {
                    $role = Role::firstOrCreate(
                        [
                            'name' => 'User',
                            'department_id' => $department->id
                        ],
                        [
                            'description' => "Default role for users in {$department->name}"
                        ]
                    );

                    $subRole = SubRole::firstOrCreate(
                        [
                            'name' => 'Basic User',
                            'role_id' => $role->id
                        ],
                        [
                            'description' => "Basic user role in {$department->name}"
                        ]
                    );

                    $user->syncSubRoles([$subRole->id]);
                }
            }
        }

        $this->info('User roles synced successfully.');
    }
} 