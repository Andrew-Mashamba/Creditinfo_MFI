<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SubRole;
use App\Models\MenuAction;
use App\Models\RoleMenuAction;
use Illuminate\Support\Facades\Log;

class SyncRolePermissions extends Command
{
    protected $signature = 'role-permissions:sync';
    protected $description = 'Sync role permissions with menu actions';

    public function handle()
    {
        Log::info('Starting role permissions synchronization');
        
        $subRoles = SubRole::all();
        $menuActions = MenuAction::all();

        Log::info('Found roles and actions to sync', [
            'sub_roles_count' => $subRoles->count(),
            'menu_actions_count' => $menuActions->count()
        ]);

        foreach ($subRoles as $subRole) {
            Log::info('Processing sub-role', [
                'sub_role' => $subRole->name
            ]);

            // Get all menu actions for the sub-role's menu
            $menuActions = MenuAction::whereHas('menu', function ($query) use ($subRole) {
                $query->where('id', $subRole->menu_id);
            })->get();

            Log::info('Found menu actions for sub-role', [
                'sub_role' => $subRole->name,
                'menu_actions_count' => $menuActions->count()
            ]);

            // Group actions by menu
            $actionsByMenu = $menuActions->groupBy('menu_id');

            // Sync permissions
            foreach ($actionsByMenu as $menuId => $actions) {
                RoleMenuAction::updateOrCreate(
                    [
                        'sub_role' => $subRole->name,
                        'menu_id' => $menuId
                    ],
                    [
                        'allowed_actions' => $actions->pluck('name')->toArray()
                    ]
                );
            }

            Log::info('Synced permissions for sub-role', [
                'sub_role' => $subRole->name,
                'menus_count' => $actionsByMenu->count()
            ]);
        }

        Log::info('Role permissions synchronization completed');
        $this->info('Role permissions synced successfully.');
    }
} 