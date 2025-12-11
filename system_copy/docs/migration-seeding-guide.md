# SACCOS Core System - Migration & Seeding Guide

## Overview
This document provides a comprehensive guide for running migrations and seeders in the SACCOS Core System, including solutions to common issues and best practices for future runs.

## Last Successful Run
- **Date**: 2025-09-07
- **Status**: ✅ Complete Success
- **Total Migrations**: 276
- **Total Seeders**: 200
- **Execution Time**: ~2-3 minutes
- **Errors Encountered**: None

## Quick Start Commands

### Fresh Migration with Seeding
```bash
# Recommended command for complete database reset
php artisan migrate:fresh --seed

# With output logging
php artisan migrate:fresh --seed 2>&1 | tee migration_output.log
```

### Check Migration Status
```bash
php artisan migrate:status
```

### Run Only Seeders
```bash
php artisan db:seed
```

## Database Statistics After Fresh Seeding

### Core Data Created
- **Users**: 3 (System Admin, Institution Admin, Demo User)
- **Branches**: 1 (Headquarters)
- **Institutions**: 11
- **Roles**: 2 (System Administrator, Institution Administrator)
- **Menus**: 31 with role assignments
- **Role-Menu Actions**: 62 permissions configured
- **Clients**: 2 sample clients

### Key Tables Populated
1. **System Configuration**
   - Process code configurations
   - API keys
   - Password policies
   - Document types
   - Currencies

2. **Financial Structure**
   - Account types
   - Sub-accounts
   - General ledger setup
   - Budget accounts

3. **Operational Setup**
   - Services and products
   - Charges and fees
   - Insurance configurations
   - Payment methods

## Verification Process

The system includes a `VerifySetupSeeder` that runs last and performs:

### Automatic Checks
1. **Branch Verification**
   - Ensures at least one branch exists
   - Creates Headquarters branch if missing

2. **User Verification**
   - Ensures minimum 3 users exist
   - Creates default users if missing:
     - System Administrator (admin@example.com)
     - Institution Administrator (inst_admin@example.com)  
     - Demo User (demo@example.com)

3. **Role Assignment**
   - Verifies all users have roles
   - Assigns appropriate roles to users without roles

4. **Menu Access**
   - Confirms role-menu action mappings
   - Reports menu access statistics per role

## Common Issues and Solutions

### Issue 1: Foreign Key Constraints
**Problem**: Foreign key violations during seeding
**Solution**: The system automatically handles this by:
```sql
-- Disable FK checks (PostgreSQL)
SET session_replication_role = replica;

-- Re-enable after seeding
SET session_replication_role = DEFAULT;
```

### Issue 2: Duplicate Key Violations
**Problem**: Attempting to insert duplicate records
**Solution**: Fresh migration clears all tables first
```bash
php artisan migrate:fresh --seed
```

### Issue 3: Memory Exhaustion
**Problem**: Large seeders consuming too much memory
**Solution**: Increase PHP memory limit
```bash
php -d memory_limit=2G artisan migrate:fresh --seed
```

### Issue 4: Timeout Issues
**Problem**: Long-running seeders timing out
**Solution**: Run without time limit
```bash
php artisan migrate:fresh --seed --timeout=0
```

## Seeding Order and Dependencies

The system follows a strict seeding order to handle dependencies:

### Phase 1: Core System (Must run first)
1. InstitutionsSeeder
2. BranchesSeeder
3. DepartmentsSeeder

### Phase 2: Access Control
1. RolesSeeder
2. PermissionsSeeder
3. UsersSeeder
4. UserRolesSeeder

### Phase 3: Menu System
1. MenuSeeder
2. SubMenusSeeder
3. MenuActionsSeeder
4. RoleMenuActionsSeeder

### Phase 4: Business Data
1. ClientsSeeder
2. ServicesSeeder
3. AccountsSeeder
4. Additional operational seeders...

### Phase 5: Verification
1. VerifySetupSeeder (Always runs last)

## Error Handling Strategy

The DatabaseSeeder implements robust error handling:

1. **Individual Seeder Execution**
   - Each seeder runs in isolation
   - Failures don't stop the entire process

2. **Progress Tracking**
   - Real-time progress display: `[X/200] SeederName`
   - Success indicators: ✅
   - Failure indicators: ❌

3. **Error Logging**
   - Detailed error messages with stack traces
   - Logs stored in `storage/logs/laravel.log`

4. **Summary Report**
   - Total seeders run
   - Successful count
   - Failed seeders list with error details

## Performance Optimization

### Database Optimizations
```sql
-- Before large operations
SET autocommit = 0;
SET unique_checks = 0;
SET foreign_key_checks = 0;

-- After completion
SET foreign_key_checks = 1;
SET unique_checks = 1;
SET autocommit = 1;
```

### PHP Optimizations
```ini
; php.ini settings for large datasets
memory_limit = 2G
max_execution_time = 0
post_max_size = 128M
```

## Monitoring and Validation

### Check Database State
```bash
# Count records in key tables
php artisan tinker
>>> DB::table('users')->count();
>>> DB::table('branches')->count();
>>> DB::table('institutions')->count();
>>> DB::table('roles')->count();
```

### Verify Relationships
```php
// Check user-role assignments
$users = User::with('roles')->get();
foreach($users as $user) {
    echo $user->email . ': ' . $user->roles->pluck('name')->join(', ') . "\n";
}
```

## Troubleshooting Commands

### Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### Reset Autoloader
```bash
composer dump-autoload
```

### Check Database Connection
```bash
php artisan db:show
```

## Best Practices

1. **Always Backup Before Migration**
   ```bash
   pg_dump dbname > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Use Transactions in Custom Seeders**
   ```php
   DB::beginTransaction();
   try {
       // Seeding logic
       DB::commit();
   } catch (\Exception $e) {
       DB::rollBack();
       throw $e;
   }
   ```

3. **Test in Development First**
   - Never run fresh migrations on production
   - Test all seeders in staging environment

4. **Monitor Resource Usage**
   ```bash
   # Watch memory usage during seeding
   watch -n 1 'ps aux | grep artisan'
   ```

5. **Use Chunking for Large Datasets**
   ```php
   $users->chunk(1000, function ($chunk) {
       // Process chunk
   });
   ```

## Environment-Specific Configurations

### Development
```env
APP_ENV=local
APP_DEBUG=true
DB_DATABASE=saccos_dev
```

### Staging
```env
APP_ENV=staging
APP_DEBUG=false
DB_DATABASE=saccos_staging
```

### Production
```env
APP_ENV=production
APP_DEBUG=false
DB_DATABASE=saccos_prod
# Never run migrate:fresh in production!
```

## Maintenance Scripts

### Daily Verification
```bash
#!/bin/bash
# verify_db.sh
php artisan db:show
php artisan migrate:status | tail -10
echo "Users: $(php artisan tinker --execute="echo User::count()")"
echo "Branches: $(php artisan tinker --execute="echo Branch::count()")"
```

### Weekly Optimization
```bash
#!/bin/bash
# optimize_db.sh
php artisan optimize
php artisan cache:clear
php artisan queue:restart
```

## Recovery Procedures

### If Seeding Fails Midway
1. Note the last successful seeder
2. Run fresh migration: `php artisan migrate:fresh`
3. Run seeders individually starting from the failed one
4. Run VerifySetupSeeder last

### If Database Corrupted
1. Drop and recreate database
2. Run fresh migrations
3. Run seeders with error logging
4. Verify data integrity

## Future Improvements

1. **Implement Seeder Versioning**
   - Track seeder versions
   - Support incremental seeding

2. **Add Progress Bar**
   - Visual progress indication
   - ETA calculation

3. **Parallel Seeding**
   - Run independent seeders in parallel
   - Reduce total seeding time

4. **Data Validation Suite**
   - Comprehensive data integrity checks
   - Business rule validation

## Contact and Support

For issues with migrations or seeders:
1. Check `storage/logs/laravel.log` for detailed errors
2. Review this documentation
3. Run VerifySetupSeeder for automatic fixes
4. Contact system administrator if issues persist

---
*Last Updated: 2025-09-07*
*Version: 1.0*
*Status: Production Ready*