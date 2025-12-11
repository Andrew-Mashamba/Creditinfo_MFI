# Roles, Permissions, and Menu System Architecture

## Overview
The SACCOS system implements a comprehensive role-based access control (RBAC) system with dynamic menu generation based on user permissions.

## Database Schema

### Core Tables

#### 1. **roles** table
- Primary role definitions
- Fields: id, name, description, status

#### 2. **permissions** table  
- Individual permission definitions
- Fields: id, name, description, guard_name

#### 3. **user_roles** table
- Links users to roles (many-to-many)
- Fields: user_id, role_id

#### 4. **role_permissions** table
- Links roles to permissions
- Fields: role_id, permission_id, department_id, conditions, is_granted

#### 5. **user_permissions** table
- Direct user permissions (overrides role permissions)
- Fields: user_id, permission_id, department_id, conditions, is_granted, granted_by

### Menu System Tables

#### 6. **menus** table
- Top-level menu definitions
- Fields: id, menu_name, description, menu_title, status

#### 7. **sub_menus** table
- Submenu items
- Fields: id, menu_id, name, route, icon, order

#### 8. **menu_actions** table
- Available actions for each menu
- Fields: id, menu_id, action_name, action_code

#### 9. **role_menu_actions** table
- Links roles to menu actions with permissions
- Fields: id, role_id, menu_id, sub_role, allowed_actions (JSON)

#### 10. **user_sub_menus** table
- User-specific menu assignments
- Fields: user_id, submenu_id, is_active

## Key Relationships

```
User ─────┬──► user_roles ◄──── Role
          │         │              │
          │         ▼              ▼
          ├──► user_permissions    role_permissions ◄─── Permission
          │                              │
          │                              ▼
          └──► user_sub_menus     role_menu_actions
                    │                    │
                    ▼                    ▼
              sub_menus ◄────────── Menu ◄──── menu_actions
```

## How the Sidebar Works

### 1. **User Authentication**
When a user logs in, the system loads their roles and permissions.

### 2. **Menu Generation Process** (in Sidebar.php)

```php
// Step 1: Load user roles
$this->currentUserRoles = auth()->user()->roles()
    ->with(['permissions', 'department'])
    ->get();

// Step 2: Get menu items for each role
foreach ($this->currentUserRoles as $role) {
    $menuItems = $menuItems->merge(
        $this->menuService->getMenuItemsForRole($role)
    );
}

// Step 3: Add direct user permissions
$permissions = $this->getUserPermissions();
// Process permissions to add menu items

// Step 4: Generate unique menu list
$this->menuItems = $menuItems->unique()->values()->toArray();
```

### 3. **Permission Checking**
The system uses the `HasRoles` trait to check permissions:

```php
// Check if user has a specific permission
$user->hasPermission('view_users');

// Check multiple permissions
$user->hasAnyPermission(['edit_users', 'delete_users']);
$user->hasAllPermissions(['view_users', 'edit_users']);
```

### 4. **Menu Display Logic**
- Menus are grouped by department/category
- Only menus with granted permissions are shown
- Actions are filtered based on allowed_actions in role_menu_actions

## Migration Files

### Roles & Permissions Migrations
1. `2023_01_02_000003_create_permissions_table.php`
2. `2023_01_03_000002_create_roles_table.php`
3. `2023_01_04_000002_create_sub_roles_table.php`
4. `2023_01_04_000003_create_role_permissions_table.php`
5. `2023_01_05_000002_create_user_roles_table.php`
6. `2025_07_28_161604_create_user_permissions_table.php`
7. `2023_01_06_000115_create_sub_role_permissions_table.php`
8. `2023_01_06_000120_create_user_sub_roles_table.php`

### Menu System Migrations
1. `2023_01_03_000008_create_menus_table.php`
2. `2023_01_04_000001_create_menu_actions_table.php`
3. `2023_01_06_000073_create_sub_menus_table.php`
4. `2023_01_06_000079_create_user_sub_menus_table.php`
5. `2023_01_06_000087_create_role_menu_actions_table.php`

## Seeder Files

### Core Seeders
1. **RolesSeeder** - Creates System Administrator, Institution Administrator roles
2. **PermissionsSeeder** - Creates base permissions (view_dashboard, edit_users, etc.)
3. **UsersSeeder** - Creates default users
4. **UserrolesSeeder** - Assigns roles to users
5. **UserpermissionsSeeder** - Grants direct permissions to users
6. **RolepermissionsSeeder** - Links permissions to roles
7. **SubrolesSeeder** - Creates sub-roles for departments
8. **SubrolepermissionsSeeder** - Sub-role specific permissions

### Menu Seeders
1. **MenuSeeder** - Creates main menu items
2. **SubMenusSeeder** - Creates submenu items
3. **MenuActionsSeeder** - Defines available actions per menu
4. **RoleMenuActionsSeeder** - Assigns menu actions to roles
5. **UsersubmenusSeeder** - User-specific menu assignments

## Key Models and Traits

### User Model
- Uses `HasRoles` trait
- Properties: role, sub_role fields
- Relationships: roles(), permissions() via trait

### HasRoles Trait (`app/Traits/HasRoles.php`)
Key methods:
- `roles()` - Get user's roles
- `getAllPermissions()` - Get all permissions (role + direct)
- `hasPermission($permission)` - Check specific permission
- `assignRole($role)` - Assign role to user
- `grantPermission($permission)` - Grant direct permission

### Menu Model
- Relationships: roleMenuActions(), menuActions()
- Used to define menu structure

### RoleMenuAction Model
- Links roles to menus with specific actions
- `allowed_actions` field stores JSON array of permitted actions
- Methods: hasAction($action) to check permissions

## Permission Flow

1. **User Login** → Load user roles from user_roles table
2. **Role Processing** → For each role, load permissions from role_permissions
3. **Direct Permissions** → Add user_permissions (can override role permissions)
4. **Menu Generation** → Based on permissions, generate menu items
5. **Action Filtering** → Filter available actions per menu based on role_menu_actions
6. **Display** → Show only authorized menus and actions in sidebar

## Common Permission Patterns

### Adding a New Menu Item
1. Create menu in menus table
2. Add menu actions in menu_actions table
3. Link to roles via role_menu_actions with allowed_actions
4. Users with those roles will see the menu

### Granting User Access
```php
// Via role
$user->assignRole($roleId);

// Direct permission
$user->grantPermission($permission);
```

### Checking Access in Controllers
```php
if (!auth()->user()->hasPermission('edit_loans')) {
    abort(403, 'Unauthorized');
}
```

## Current System State
- **Roles**: 2 (System Administrator, Institution Administrator)
- **Permissions**: 2 base permissions configured
- **Users**: 2 admin users configured
- **Menus**: Multiple menus seeded with actions

## Troubleshooting

### User Can't See Menu
1. Check user_roles table for role assignment
2. Verify role_menu_actions has entry for role + menu
3. Check allowed_actions JSON contains required actions
4. Verify menu status is active

### Permission Denied
1. Check role_permissions for role-based permission
2. Check user_permissions for direct grants
3. Verify permission name matches exactly
4. Check department_id if department-specific

---
**Generated**: September 2, 2025  
**System**: SACCOS Management System