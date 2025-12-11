<?php

if (!function_exists('userCan')) {
    /**
     * Check if user can perform a specific action in the current menu context
     *
     * @param string $action The action to check (e.g., 'create', 'edit', 'delete')
     * @return bool
     */
    function userCan(string $action): bool
    {
        return \App\Helpers\PermissionHelper::userCan($action);
    }
}

if (!function_exists('getAllowedActions')) {
    /**
     * Get all allowed actions for the current menu context
     *
     * @return array
     */
    function getAllowedActions(): array
    {
        return \App\Helpers\PermissionHelper::getAllowedActions();
    }
}

if (!function_exists('userCanAny')) {
    /**
     * Check if user has any of the specified actions
     *
     * @param array $actions Array of actions to check
     * @return bool
     */
    function userCanAny(array $actions): bool
    {
        return \App\Helpers\PermissionHelper::userCanAny($actions);
    }
}

if (!function_exists('userCanAll')) {
    /**
     * Check if user has all of the specified actions
     *
     * @param array $actions Array of actions to check
     * @return bool
     */
    function userCanAll(array $actions): bool
    {
        return \App\Helpers\PermissionHelper::userCanAll($actions);
    }
} 