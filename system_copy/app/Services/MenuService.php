<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class MenuService
{
    /**
     * Get menu items for a specific role
     *
     * @param \App\Models\Role $role
     * @return \Illuminate\Support\Collection
     */
    public function getMenuItemsForRole($role)
    {

        //dd($role);
        try {
            $startTime = microtime(true);
            $menuItems = collect();

            Log::info('Getting menu items for role', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'permission_inheritance_enabled' => $role->permission_inheritance_enabled,
                'parent_role_id' => $role->parent_role_id,
                'department_specific' => $role->department_specific,
                'timestamp' => now()
            ]);

            // Get menu items from role menu actions
            $roleMenuActions = $role->menuActions()
                ->with(['menu', 'menuAction'])
                ->get();

                //dd($roleMenuActions);

            Log::info('Retrieved role menu actions', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'menu_actions_count' => $roleMenuActions->count(),
                'menu_actions_details' => $roleMenuActions->map(function($action) {
                    return [
                        'id' => $action->id,
                        'menu_id' => $action->menu_id,
                        'menu_name' => $action->menu ? $action->menu->menu_name : 'null',
                        'allowed_actions' => $action->allowed_actions,
                        'sub_role' => $action->sub_role
                    ];
                })->toArray(),
                'timestamp' => now()
            ]);

            foreach ($roleMenuActions as $roleMenuAction) {
                if ($roleMenuAction->menu) {
                    $menuItems->push($roleMenuAction->menu->id);
                    Log::info('Added menu item from role menu action', [
                        'role_id' => $role->id,
                        'menu_id' => $roleMenuAction->menu->id,
                        'menu_name' => $roleMenuAction->menu->menu_name,
                        'allowed_actions' => $roleMenuAction->allowed_actions,
                        'sub_role' => $roleMenuAction->sub_role,
                        'timestamp' => now()
                    ]);
                } else {
                    Log::warning('Menu not found for role menu action', [
                        'role_id' => $role->id,
                        'role_menu_action_id' => $roleMenuAction->id,
                        'menu_id' => $roleMenuAction->menu_id,
                        'timestamp' => now()
                    ]);
                }
            }

            // Add inherited permissions from parent roles
            if ($role->permission_inheritance_enabled && $role->parent_role_id) {
                $parentRole = $role->parent;
                if ($parentRole) {
                    Log::info('Processing parent role inheritance', [
                        'role_id' => $role->id,
                        'parent_role_id' => $parentRole->id,
                        'parent_role_name' => $parentRole->name,
                        'timestamp' => now()
                    ]);

                    $inheritedMenuItems = $this->getMenuItemsForRole($parentRole);
                    $menuItems = $menuItems->merge($inheritedMenuItems);

                    Log::info('Added inherited menu items', [
                        'role_id' => $role->id,
                        'inherited_items_count' => $inheritedMenuItems->count(),
                        'inherited_items' => $inheritedMenuItems->toArray(),
                        'total_items_after_inheritance' => $menuItems->count(),
                        'timestamp' => now()
                    ]);
                } else {
                    Log::warning('Parent role not found', [
                        'role_id' => $role->id,
                        'parent_role_id' => $role->parent_role_id,
                        'timestamp' => now()
                    ]);
                }
            }

            // Add department-specific permissions
            if ($role->department_specific && $role->department) {
                Log::info('Processing department-specific permissions', [
                    'role_id' => $role->id,
                    'department_id' => $role->department->id,
                    'department_name' => $role->department->department_name,
                    'timestamp' => now()
                ]);

                $departmentMenuItems = $role->department->roleMenuActions()
                    ->with(['menu', 'menuAction'])
                    ->get()
                    ->pluck('menu.id');

                $menuItems = $menuItems->merge($departmentMenuItems);

                Log::info('Added department-specific menu items', [
                    'role_id' => $role->id,
                    'department_items_count' => $departmentMenuItems->count(),
                    'department_items' => $departmentMenuItems->toArray(),
                    'total_items_after_department' => $menuItems->count(),
                    'timestamp' => now()
                ]);
            }

            // Get menu items from role permissions
            $permissions = $role->permissions;
            Log::info('Processing role permissions', [
                'role_id' => $role->id,
                'permissions_count' => $permissions->count(),
                'permissions_details' => $permissions->map(function($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'menu_id' => $permission->menu_id,
                        'resource_type' => $permission->resource_type
                    ];
                })->toArray(),
                'timestamp' => now()
            ]);

            foreach ($permissions as $permission) {
                if ($permission->menu_id) {
                    $menuItems->push($permission->menu_id);
                    Log::info('Added menu item from permission', [
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                        'permission_name' => $permission->name,
                        'menu_id' => $permission->menu_id,
                        'resource_type' => $permission->resource_type,
                        'timestamp' => now()
                    ]);
                }
            }

            $endTime = microtime(true);
            $uniqueMenuItems = $menuItems->unique()->values();

            Log::info('Menu items retrieved for role', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'total_menu_items' => $menuItems->count(),
                'unique_menu_items' => $uniqueMenuItems->count(),
                'unique_menu_items_details' => $uniqueMenuItems->map(function($menuId) {
                    $menu = \App\Models\Menu::find($menuId);
                    return [
                        'menu_id' => $menuId,
                        'menu_name' => $menu ? $menu->menu_name : 'null'
                    ];
                })->toArray(),
                'execution_time' => round($endTime - $startTime, 4),
                'timestamp' => now()
            ]);

            return $uniqueMenuItems;
        } catch (\Exception $e) {
            Log::error('Error getting menu items for role', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'role_id' => $role->id,
                'role_name' => $role->name,
                'timestamp' => now()
            ]);
            return collect();
        }
    }
} 