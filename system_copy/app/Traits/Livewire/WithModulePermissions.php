<?php

namespace App\Traits\Livewire;

use App\Services\PermissionService;
use Illuminate\Support\Facades\Log;

trait WithModulePermissions
{
    /**
     * Array of permission flags for the module
     * @var array
     */
    public $permissions = [];
    
    /**
     * The permission service instance
     * @var PermissionService
     */
    protected $permissionService;
    
    /**
     * The module name for permission checking
     * @var string
     */
    protected $module;
    
    /**
     * Initialize permissions for the module
     * This should be called in the mount() method of the Livewire component
     */
    public function initializeWithModulePermissions(): void
    {
        $this->permissionService = app(PermissionService::class);
        $this->loadModulePermissions();
    }
    
    /**
     * Load all permissions for this module
     */
    protected function loadModulePermissions(): void
    {
        if (!$this->module) {
            $this->module = $this->getModuleName();
        }
        
        // Get all permissions for this module as a map
        $this->permissions = $this->permissionService->getModulePermissionsMap($this->module);
        
        // Add some common shortcuts
        $this->permissions['hasAnyPermission'] = !empty(array_filter($this->permissions));
        $this->permissions['module'] = $this->module;
        
        Log::info('Module permissions loaded', [
            'module' => $this->module,
            'user_id' => auth()->id(),
            'permissions' => $this->permissions
        ]);
    }
    
    /**
     * Check if user can perform action
     * 
     * @param string $action
     * @return bool
     */
    public function can(string $action): bool
    {
        return $this->permissionService->can($this->module, $action);
    }
    
    /**
     * Check if user can perform any of the actions
     * 
     * @param array $actions
     * @return bool
     */
    public function canAny(array $actions): bool
    {
        return $this->permissionService->canAny($this->module, $actions);
    }
    
    /**
     * Check if user can perform all of the actions
     * 
     * @param array $actions
     * @return bool
     */
    public function canAll(array $actions): bool
    {
        return $this->permissionService->canAll($this->module, $actions);
    }
    
    /**
     * Check permission and show error if not authorized
     * 
     * @param string $action
     * @param string|null $message
     * @return bool
     */
    protected function authorize(string $action, string $message = null): bool
    {
        if (!$this->can($action)) {
            $message = $message ?? "You don't have permission to {$action} {$this->module}";
            session()->flash('error', $message);
            session()->flash('alert-class', 'alert-danger');
            
            Log::warning('Unauthorized action attempted', [
                'module' => $this->module,
                'action' => $action,
                'user_id' => auth()->id()
            ]);
            
            return false;
        }
        return true;
    }
    
    /**
     * Check permission silently (no error message)
     * 
     * @param string $action
     * @return bool
     */
    protected function authorizeSilently(string $action): bool
    {
        return $this->can($action);
    }
    
    /**
     * Refresh permissions (useful after role changes)
     */
    public function refreshPermissions(): void
    {
        $this->permissionService->clearCache();
        $this->loadModulePermissions();
    }
    
    /**
     * Get module name from class name
     * Override this method if your module name doesn't match the class name
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        // Get the class name without namespace
        $className = class_basename(static::class);
        
        // Remove common suffixes
        $className = str_replace(['Component', 'Controller', 'Manager'], '', $className);
        
        // Convert to lowercase
        return strtolower($className);
    }
    
    /**
     * Get a specific permission flag
     * 
     * @param string $permission
     * @return bool
     */
    public function getPermission(string $permission): bool
    {
        return $this->permissions[$permission] ?? false;
    }
    
    /**
     * Check if user has at least one permission in the module
     * 
     * @return bool
     */
    public function hasModuleAccess(): bool
    {
        return $this->permissions['hasAnyPermission'] ?? false;
    }
    
    /**
     * Get all permission flags for the module
     * 
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }
    
    /**
     * Ensure user has permission or abort
     * 
     * @param string $action
     * @param string|null $message
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function authorizeOrAbort(string $action, string $message = null): void
    {
        if (!$this->can($action)) {
            $message = $message ?? "Unauthorized access to {$this->module}.{$action}";
            abort(403, $message);
        }
    }
}