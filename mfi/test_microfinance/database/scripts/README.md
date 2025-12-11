# Database Scripts

This directory contains utility scripts for database management and administration.

## Scripts Available

1. **grant_admin_permissions.php** - Grants full menu access to System Administrator
2. **fix_sequences.php** - Fixes PostgreSQL auto-increment sequences

## grant_admin_permissions.php

### Purpose
This script grants full menu access permissions to the System Administrator role. It ensures that users with the System Administrator role can see and interact with all menu items in the sidebar.

### What it does:
1. **Fetches all menus** from the `menus` table
2. **Verifies** that the System Administrator role exists (ID: 1)
3. **Clears** any existing menu permissions for System Administrator
4. **Grants** all possible actions on all menus:
   - `view` - View the menu item
   - `create` - Create new records
   - `edit` - Edit existing records
   - `delete` - Delete records
   - `approve` - Approve transactions/requests
   - `reject` - Reject transactions/requests
   - `manage` - Manage settings and configurations
   - `configure` - Configure system settings
   - `audit` - View audit logs
   - `export` - Export data
   - `import` - Import data
5. **Inserts** permissions into the `role_menu_actions` table
6. **Verifies** that permissions were successfully granted

### How to run:

#### Option 1: Direct PHP execution
```bash
php database/scripts/grant_admin_permissions.php
```

#### Option 2: Via Artisan command (if you create a custom command)
```bash
php artisan permissions:grant-admin
```

### When to run this script:
- After fresh installation
- After adding new menus to the system
- If System Administrator loses access to menus
- When you need to reset admin permissions
- After running migrations that might affect permissions

### Requirements:
- Database must be migrated
- Roles seeder must have been run (to create System Administrator role)
- Menus must exist in the database (MenuSeeder must have been run)

### Output Example:
```
====================================
Grant Admin Permissions Script
====================================

📋 Found 31 menus in the system
👤 Found role: System Administrator
🔑 Granting actions: view, create, edit, delete, approve, reject, manage, configure, audit, export, import
✅ Inserted 31 permissions...

✅ SUCCESS!
📊 Summary:
   - Role: System Administrator
   - Total menus granted: 31
   - Actions per menu: 11
   - Total permissions created: 31

🎉 System Administrator now has full access to all menus!
```

### Troubleshooting:
- If you get "No menus found", run: `php artisan db:seed --class=MenuSeeder`
- If you get "System Administrator role not found", run: `php artisan db:seed --class=RolesSeeder`
- If permissions don't appear in the UI, clear the cache: `php artisan cache:clear`

### Database Tables Affected:
- `role_menu_actions` - Stores the menu permissions for each role

### Safety:
- The script uses database transactions
- If any error occurs, all changes are rolled back
- Existing permissions are cleared before new ones are added to avoid duplicates

---

## fix_sequences.php

### Purpose
This script fixes PostgreSQL sequence values that are out of sync with their table's maximum ID values. This solves the "duplicate key value violates unique constraint" errors that occur when the auto-increment sequence is behind the actual data.

### What it does:
1. **Scans all tables** with auto-increment columns
2. **Compares** the maximum ID in each table with its sequence value
3. **Fixes sequences** that are behind (sequence < max ID)
4. **Reports** the status of each table
5. **Provides summary** of all fixes applied

### How to run:

#### Option 1: Fix all sequences
```bash
php database/scripts/fix_sequences.php
```

#### Option 2: Fix specific table (future enhancement)
```bash
php database/scripts/fix_sequences.php approvals
```

### When to run this script:
- When you get "duplicate key value violates unique constraint" errors
- After importing data from another database
- After manually inserting records with specific IDs
- After running seeders that set specific IDs
- As part of regular database maintenance

### Output Example:
```
====================================
PostgreSQL Sequence Fixer
====================================

✅ Fixed: approvals.id - Sequence set to 5 (was 3)
✓ OK: users.id - Sequence (10) > Max ID (9)
...

====================================
Summary:
====================================
Tables checked: 216
Sequences fixed: 111

🎉 All sequences have been synchronized!
```

### How it works:
1. Queries PostgreSQL's information_schema to find all sequences
2. For each table with a sequence:
   - Gets the maximum ID value from the table
   - Gets the current sequence value
   - If max ID >= sequence value, updates the sequence to max ID + 1
3. Uses PostgreSQL's `setval()` function to update sequences

### Database Tables Affected:
- All tables with auto-increment primary keys
- Specifically fixes the `approvals` table and verifies it

### Safety:
- Read-only for checking values
- Only updates sequences that are behind
- Does not modify any actual data in tables
- Safe to run multiple times