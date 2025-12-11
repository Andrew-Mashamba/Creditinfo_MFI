<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Menu;
use App\Models\MenuAction;
use App\Models\RoleMenuAction;
use Illuminate\Support\Facades\DB;
use App\Models\SubRole;

class LinkMenusToRoles extends Command
{
    protected $signature = 'link:menus-to-roles';
    protected $description = 'Link all menus to all roles with all available actions';

    public function handle()
    {
        $this->info('Linking all menus to all roles...');
        $roles = Role::all();
        $menus = Menu::all();
        $actions = MenuAction::all();
        $actionNames = $actions->pluck('name')->unique()->values()->toArray();

        // Fix any existing records with null allowed_actions
        DB::table('role_menu_actions')->whereNull('allowed_actions')->update(['allowed_actions' => json_encode([])]);

        $count = 0;
        foreach ($roles as $role) {
            // Get the first sub role name for this role, or use 'default'
            $subRole = $role->subRoles()->first();
            if (!$subRole) {
                // Create a default sub role if none exists
                $subRole = SubRole::create([
                    'role_id' => $role->id,
                    'name' => 'default',
                    'description' => 'Default sub role for ' . $role->name
                ]);
            }
            
            foreach ($menus as $menu) {
                // Get all actions for this menu
                $menuActions = MenuAction::where('menu_id', $menu->id)->pluck('name')->unique()->values()->toArray();
                
                // If no actions found, use default actions
                if (empty($menuActions)) {
                    $menuActions = ['view', 'create', 'edit', 'delete'];
                }

                // Ensure allowed_actions is properly encoded
                $allowedActions = json_encode($menuActions);

                RoleMenuAction::updateOrCreate(
                    [
                        'role_id' => $role->id,
                        'menu_id' => $menu->id,
                        'sub_role' => $subRole->name,
                    ],
                    [
                        'allowed_actions' => $allowedActions,
                    ]
                );
                $count++;
            }
        }
        $this->info("Linked {$menus->count()} menus to {$roles->count()} roles ({$count} records).");
        $this->info('Done!');
    }
} 