# SACCOS Core System - Migration Troubleshooting Guide

## Quick Reference

### ✅ Successful Run (2025-09-07)
```bash
php artisan migrate:fresh --seed
```
- **Result**: Complete success
- **Time**: ~2-3 minutes
- **Tables Created**: 243
- **Seeders Run**: 200/200 successful
- **Database Size**: 44 MB

## Common Issues and Solutions

### 1. PostgreSQL vs MySQL Differences

#### Issue: SHOW TABLES doesn't work
```sql
-- MySQL
SHOW TABLES;

-- PostgreSQL (correct)
SELECT table_name FROM information_schema.tables 
WHERE table_schema = 'public' AND table_type = 'BASE TABLE';
```

#### Issue: Foreign Key Syntax
```sql
-- MySQL
SET FOREIGN_KEY_CHECKS = 0;

-- PostgreSQL (correct)
SET session_replication_role = replica;
```

### 2. Table Name Case Sensitivity

#### Issue: Table not found errors
```php
// Incorrect (case-sensitive in PostgreSQL)
DB::table('PayRolls')->count();

// Correct
DB::table('payrolls')->count();
```

### 3. Model Naming Issues

#### Issue: Class not found
```php
// Check actual model names
use App\Models\ClientsModel;  // Not Clients
use App\Models\branches;       // lowercase b
use App\Models\institutions;   // lowercase i
use App\Models\menus;         // lowercase m
```

### 4. Seeder Dependencies

#### Issue: Foreign key violations
**Solution**: DatabaseSeeder handles this automatically by:
1. Disabling FK checks at start
2. Running seeders in dependency order
3. Re-enabling FK checks at end

### 5. Empty Tables After Seeding

#### Normal Empty Tables
These tables are expected to be empty after fresh seeding:
- `accounts` - Created by users
- `transactions` - Generated during operations
- `loans` - Created through loan applications
- `bills` - Generated monthly
- `general_ledger` - Populated by transactions
- `tills`, `vaults` - Setup by admin
- `api_keys` - Created as needed

#### Tables That Should Have Data
If these are empty, re-run seeders:
- `users` (3+ records)
- `branches` (1+ records)
- `institutions` (11 records)
- `roles` (2+ records)
- `menus` (31 records)
- `process_code_configs` (73 records)

### 6. Memory Issues

#### Issue: Allowed memory size exhausted
```bash
# Solution 1: Increase memory limit
php -d memory_limit=2G artisan migrate:fresh --seed

# Solution 2: Edit php.ini
memory_limit = 2G
```

### 7. Timeout Issues

#### Issue: Maximum execution time exceeded
```bash
# Solution 1: Remove time limit
php artisan migrate:fresh --seed --timeout=0

# Solution 2: Edit php.ini
max_execution_time = 0
```

### 8. Database Connection Issues

#### Issue: SQLSTATE[08006] Connection refused
```bash
# Check PostgreSQL is running
sudo systemctl status postgresql

# Start if stopped
sudo systemctl start postgresql

# Check connection
php artisan db:show
```

### 9. Migration Already Exists

#### Issue: Migration class already exists
```bash
# Solution: Clear and regenerate
composer dump-autoload
php artisan migrate:fresh --seed
```

### 10. Partial Seeding Failure

#### Issue: Some seeders fail, others succeed
```bash
# Run individual seeder
php artisan db:seed --class=SpecificSeeder

# Run from specific point
php artisan migrate:fresh
php artisan db:seed --class=InstitutionsSeeder
# ... continue with remaining seeders
```

## Verification Commands

### Quick Health Check
```bash
# Run verification script
php verify_database.php

# Check specific tables
php artisan tinker
>>> DB::table('users')->count();
>>> DB::table('branches')->count();
```

### Check Migration Status
```bash
php artisan migrate:status | tail -20
```

### Database Size Check
```sql
SELECT pg_database_size('saccos_core'), 
       pg_size_pretty(pg_database_size('saccos_core'));
```

## Recovery Procedures

### Complete Reset
```bash
# 1. Drop all tables
php artisan db:wipe

# 2. Fresh migration with seeds
php artisan migrate:fresh --seed

# 3. Verify
php verify_database.php
```

### Selective Recovery
```bash
# Reset specific tables only
php artisan migrate:refresh --path=/database/migrations/specific_migration.php
php artisan db:seed --class=SpecificSeeder
```

## Performance Optimization

### For Large Datasets
```php
// In seeders, use chunks
DB::table('large_table')->chunk(1000, function ($records) {
    foreach ($records as $record) {
        // Process record
    }
});

// Use insert instead of create
DB::table('table')->insert($data); // Faster
Model::create($data); // Slower but triggers events
```

### Disable Logs During Seeding
```php
DB::disableQueryLog();
// Run seeders
DB::enableQueryLog();
```

## Debugging Tips

### Enable SQL Logging
```php
// In tinker or seeder
DB::enableQueryLog();
// Run queries
dd(DB::getQueryLog());
```

### Check Last Error
```bash
tail -50 storage/logs/laravel.log
```

### Verbose Output
```bash
php artisan migrate:fresh --seed -vvv
```

## Environment-Specific Issues

### Docker/Container Issues
```bash
# Ensure database container is running
docker-compose ps

# Access container
docker exec -it saccos_db psql -U saccos_user -d saccos_core

# Check connectivity from app container
docker exec -it saccos_app php artisan db:show
```

### Permission Issues
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Monitoring Script

Create `monitor_db.sh`:
```bash
#!/bin/bash
while true; do
    clear
    echo "=== SACCOS DB Monitor ==="
    echo "Time: $(date)"
    echo ""
    echo "Table Counts:"
    php artisan tinker --execute="
        echo 'Users: ' . DB::table('users')->count();
        echo 'Branches: ' . DB::table('branches')->count();
        echo 'Transactions: ' . DB::table('transactions')->count();
    "
    sleep 5
done
```

## Emergency Contacts

For critical issues:
1. Check this troubleshooting guide
2. Review `storage/logs/laravel.log`
3. Run `php verify_database.php`
4. Check PostgreSQL logs: `/var/log/postgresql/`

## Success Indicators

A successful migration/seeding shows:
- ✅ 200/200 seeders completed
- ✅ No failed seeders
- ✅ VerifySetupSeeder runs successfully
- ✅ 3+ users created
- ✅ 1+ branches created
- ✅ Role assignments verified
- ✅ Database status: 🟢 HEALTHY

---
*Last Updated: 2025-09-07*
*Verified Working Configuration*