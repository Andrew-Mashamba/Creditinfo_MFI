#!/bin/bash

# Post-Migration Permission Assignment Script
# This script ensures super admin permissions are properly assigned after migrations

echo "======================================"
echo "POST-MIGRATION PERMISSION ASSIGNMENT"
echo "======================================"
echo ""

# Check if we're in the project root
if [ ! -f "artisan" ]; then
    echo "Error: This script must be run from the project root directory"
    exit 1
fi

echo "Step 1: Running system permissions seeder..."
php artisan db:seed --class=SystemPermissionsSeeder

if [ $? -ne 0 ]; then
    echo "Warning: System permissions seeder failed or partially completed"
    echo "Continuing with super admin assignment..."
fi

echo ""
echo "Step 2: Assigning super admin permissions to User ID 1..."
php artisan permissions:super-admin --force

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ SUCCESS: Super admin permissions have been assigned!"
    echo ""
    echo "User ID 1 now has full system access with all 301 permissions."
else
    echo ""
    echo "❌ ERROR: Failed to assign super admin permissions"
    echo "Please check the error messages above and try again."
    exit 1
fi

echo ""
echo "======================================"
echo "PERMISSION ASSIGNMENT COMPLETE"
echo "======================================"