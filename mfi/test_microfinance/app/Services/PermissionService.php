<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Permission;
use App\Models\User;

class PermissionService
{
    protected $user;
    protected $cacheKey;
    protected $cacheDuration = 300; // 5 minutes
    
    public function __construct($user = null)
    {
        $this->user = $user ?? Auth::user();
        $this->cacheKey = 'user_permissions_' . ($this->user?->id ?? 0);
    }
    
    /**
     * Check if user has permission for a module action
     * 
     * @param string $module
     * @param string $action
     * @return bool
     */
    public function can(string $module, string $action): bool
    {
        $permission = "{$module}.{$action}";
        return $this->hasPermission($permission);
    }
    
    /**
     * Check if user has any of the permissions
     * 
     * @param string $module
     * @param array $actions
     * @return bool
     */
    public function canAny(string $module, array $actions): bool
    {
        foreach ($actions as $action) {
            if ($this->can($module, $action)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if user has all of the permissions
     * 
     * @param string $module
     * @param array $actions
     * @return bool
     */
    public function canAll(string $module, array $actions): bool
    {
        foreach ($actions as $action) {
            if (!$this->can($module, $action)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Get all permissions for a module
     * 
     * @param string $module
     * @return array
     */
    public function getModulePermissions(string $module): array
    {
        $permissions = $this->getAllPermissions();
        
        return collect($permissions)
            ->filter(fn($p) => str_starts_with($p, $module . '.'))
            ->map(fn($p) => str_replace($module . '.', '', $p))
            ->values()
            ->toArray();
    }
    
    /**
     * Get all module permissions as boolean array
     * 
     * @param string $module
     * @return array
     */
    public function getModulePermissionsMap(string $module): array
    {
        // Get all possible permissions for this module from database
        $allModulePermissions = Permission::where('name', 'like', $module . '.%')
            ->pluck('name')
            ->map(fn($p) => str_replace($module . '.', '', $p))
            ->toArray();
        
        // If no permissions are defined in database, use default set
        if (empty($allModulePermissions)) {
            $allModulePermissions = ['view', 'create', 'edit', 'delete', 'approve', 'manage', 'export'];
        }
        
        $userPermissions = $this->getModulePermissions($module);
        
        $map = [];
        foreach ($allModulePermissions as $action) {
            $map['can' . ucfirst(str_replace('_', '', ucwords($action, '_')))] = in_array($action, $userPermissions);
        }
        
        return $map;
    }
    
    /**
     * Check if user has specific permission
     * 
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->user) {
            return false;
        }
        
        // Super admin check
        if ($this->user->is_super_admin ?? false) {
            return true;
        }
        
        $permissions = $this->getAllPermissions();
        return in_array($permission, $permissions);
    }
    
    /**
     * Get all user permissions (cached)
     * 
     * @return array
     */
    public function getAllPermissions(): array
    {
        if (!$this->user) {
            return [];
        }
        
        return Cache::remember($this->cacheKey, $this->cacheDuration, function () {
            try {
                // Get permissions through the HasRoles trait
                if (method_exists($this->user, 'getAllPermissions')) {
                    $permissions = $this->user->getAllPermissions();
                    
                    // Handle different return types
                    if ($permissions instanceof \Illuminate\Support\Collection) {
                        return $permissions->pluck('name')->unique()->values()->toArray();
                    }
                    
                    return collect($permissions)->pluck('name')->unique()->values()->toArray();
                }
                
                // Fallback to direct permission lookup
                $directPermissions = \DB::table('user_permissions')
                    ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
                    ->where('user_permissions.user_id', $this->user->id)
                    ->where('user_permissions.is_granted', true)
                    ->pluck('permissions.name')
                    ->toArray();
                
                // Get role permissions
                $rolePermissions = \DB::table('user_roles')
                    ->join('role_permissions', 'user_roles.role_id', '=', 'role_permissions.role_id')
                    ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                    ->where('user_roles.user_id', $this->user->id)
                    ->pluck('permissions.name')
                    ->toArray();
                
                // Get sub-role permissions
                $subRolePermissions = \DB::table('user_sub_roles')
                    ->join('sub_role_permissions', 'user_sub_roles.sub_role_id', '=', 'sub_role_permissions.sub_role_id')
                    ->join('permissions', 'sub_role_permissions.permission_id', '=', 'permissions.id')
                    ->where('user_sub_roles.user_id', $this->user->id)
                    ->pluck('permissions.name')
                    ->toArray();
                
                return array_unique(array_merge($directPermissions, $rolePermissions, $subRolePermissions));
                
            } catch (\Exception $e) {
                Log::error('Error fetching user permissions', [
                    'user_id' => $this->user->id,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }
    
    /**
     * Clear permission cache for user
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }
    
    /**
     * Clear all permission caches
     */
    public static function clearAllCaches(): void
    {
        // Clear all permission caches (pattern based)
        Cache::flush(); // Or use Cache::tags(['permissions'])->flush() if using tagged cache
    }
    
    /**
     * Set user for permission checking
     * 
     * @param User|null $user
     * @return self
     */
    public function forUser($user): self
    {
        $this->user = $user;
        $this->cacheKey = 'user_permissions_' . ($this->user?->id ?? 0);
        return $this;
    }
    
    /**
     * Check permission and log attempts
     * 
     * @param string $module
     * @param string $action
     * @param bool $log
     * @return bool
     */
    public function authorize(string $module, string $action, bool $log = true): bool
    {
        $hasPermission = $this->can($module, $action);
        
        if ($log && !$hasPermission) {
            Log::warning('Unauthorized access attempt', [
                'user_id' => $this->user?->id,
                'module' => $module,
                'action' => $action,
                'ip' => request()->ip()
            ]);
        }
        
        return $hasPermission;
    }
}