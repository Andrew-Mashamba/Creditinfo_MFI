<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class RoleMenuAction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'role_menu_actions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'allowed_actions' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($roleMenuAction) {
            Log::info('RoleMenuAction created', [
                'id' => $roleMenuAction->id,
                'role_id' => $roleMenuAction->role_id,
                'menu_id' => $roleMenuAction->menu_id,
                'sub_role' => $roleMenuAction->sub_role,
                'allowed_actions' => $roleMenuAction->allowed_actions
            ]);
        });

        static::updated(function ($roleMenuAction) {
            Log::info('RoleMenuAction updated', [
                'id' => $roleMenuAction->id,
                'role_id' => $roleMenuAction->role_id,
                'menu_id' => $roleMenuAction->menu_id,
                'sub_role' => $roleMenuAction->sub_role,
                'allowed_actions' => $roleMenuAction->allowed_actions,
                'changes' => $roleMenuAction->getDirty()
            ]);
        });

        static::deleted(function ($roleMenuAction) {
            Log::info('RoleMenuAction deleted', [
                'id' => $roleMenuAction->id,
                'role_id' => $roleMenuAction->role_id,
                'menu_id' => $roleMenuAction->menu_id,
                'sub_role' => $roleMenuAction->sub_role
            ]);
        });
    }

    /**
     * Get the menu that owns the role menu action.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the department that owns the role menu action.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'sub_role', 'sub_role');
    }

    /**
     * Check if a role has a specific action for a menu.
     *
     * @param string $action
     * @return bool
     */
    public function hasAction($action)
    {
        $hasAction = in_array($action, $this->allowed_actions);
        Log::debug('Checking action permission', [
            'role_menu_action_id' => $this->id,
            'role_id' => $this->role_id,
            'menu_id' => $this->menu_id,
            'action' => $action,
            'has_action' => $hasAction,
            'allowed_actions' => $this->allowed_actions
        ]);
        return $hasAction;
    }

    /**
     * Get all actions for a specific role and menu by sub_role (legacy).
     *
     * @param string $subRole
     * @param int $menuId
     * @return array
     */
    public static function getActionsForRoleAndMenu($subRole, $menuId)
    {
        Log::info('Getting actions for role and menu by sub_role', [
            'sub_role' => $subRole,
            'menu_id' => $menuId
        ]);

        $roleMenuAction = self::where('sub_role', $subRole)
            ->where('menu_id', $menuId)
            ->first();

        $actions = $roleMenuAction ? $roleMenuAction->allowed_actions : [];
        
        Log::debug('Retrieved actions for role and menu by sub_role', [
            'sub_role' => $subRole,
            'menu_id' => $menuId,
            'found' => (bool)$roleMenuAction,
            'actions' => $actions
        ]);

        return $actions;
    }

    /**
     * Get all actions for a specific role and menu by role_id.
     *
     * @param int $roleId
     * @param int $menuId
     * @return array
     */
    public static function getActionsForRoleAndMenuByRoleId($roleId, $menuId)
    {
        Log::info('Getting actions for role and menu by role_id', [
            'role_id' => $roleId,
            'menu_id' => $menuId
        ]);

        $roleMenuAction = self::where('role_id', $roleId)
            ->where('menu_id', $menuId)
            ->first();

        $actions = $roleMenuAction ? $roleMenuAction->allowed_actions : [];
        
        Log::debug('Retrieved actions for role and menu by role_id', [
            'role_id' => $roleId,
            'menu_id' => $menuId,
            'found' => (bool)$roleMenuAction,
            'actions' => $actions
        ]);

        return $actions;
    }

    /**
     * Get all menu actions for a specific role by sub_role (legacy).
     *
     * @param string $subRole
     * @return array
     */
    public static function getAllMenuActionsForRole($subRole)
    {
        Log::info('Getting all menu actions for role by sub_role', [
            'sub_role' => $subRole
        ]);

        $actions = self::where('sub_role', $subRole)
            ->with('menu')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->menu->menu_name => $item->allowed_actions];
            })
            ->toArray();

        Log::debug('Retrieved all menu actions for role by sub_role', [
            'sub_role' => $subRole,
            'menu_count' => count($actions),
            'menus' => array_keys($actions)
        ]);

        return $actions;
    }

    /**
     * Get all menu actions for a specific role by role_id.
     *
     * @param int $roleId
     * @return array
     */
    public static function getAllMenuActionsForRoleId($roleId)
    {
        Log::info('Getting all menu actions for role by role_id', [
            'role_id' => $roleId
        ]);

        $actions = self::where('role_id', $roleId)
            ->with('menu')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->menu->menu_name => $item->allowed_actions];
            })
            ->toArray();

        Log::debug('Retrieved all menu actions for role by role_id', [
            'role_id' => $roleId,
            'menu_count' => count($actions),
            'menus' => array_keys($actions)
        ]);

        return $actions;
    }

    public function subRole()
    {
        return $this->belongsTo(SubRole::class);
    }

    public function menuAction()
    {
        return $this->belongsTo(MenuAction::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
