<?php

namespace App\Helpers;

class PermissionHelper
{
    /**
     * Check if user can perform a specific action in the current menu context
     *
     * @param string $action The action to check (e.g., 'create', 'edit', 'delete')
     * @return bool
     */
    public static function userCan(string $action): bool
    {
        $activeMenu = session('active_menu');
        
        if (!$activeMenu) {
            return false;
        }

        $allowedActions = $activeMenu['allowed_actions'] ?? [];
        
        return in_array($action, $allowedActions);
    }

    /**
     * Get all allowed actions for the current menu context
     *
     * @return array
     */
    public static function getAllowedActions(): array
    {
        $activeMenu = session('active_menu');
        
        return $activeMenu['allowed_actions'] ?? [];
    }

    /**
     * Check if user has any of the specified actions
     *
     * @param array $actions Array of actions to check
     * @return bool
     */
    public static function userCanAny(array $actions): bool
    {
        $allowedActions = self::getAllowedActions();
        
        return !empty(array_intersect($actions, $allowedActions));
    }

    /**
     * Check if user has all of the specified actions
     *
     * @param array $actions Array of actions to check
     * @return bool
     */
    public static function userCanAll(array $actions): bool
    {
        $allowedActions = self::getAllowedActions();
        
        return empty(array_diff($actions, $allowedActions));
    }
} 