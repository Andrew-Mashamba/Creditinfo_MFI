#!/bin/bash

echo "Starting repository cleanup for SACCOS_CORE_SYSTEM..."

# Remove all client documents (uploaded files - should not be in repo)
echo "Removing client-documents directory..."
rm -rf public/client-documents/*
echo "/.gitkeep" > public/client-documents/.gitignore

# Remove uploaded files from storage
echo "Removing uploaded documents from storage..."
rm -rf storage/app/public/documents/*
rm -rf storage/app/documents/*
rm -rf storage/app/public/client-documents/*

# Remove all log files (should be generated locally)
echo "Removing log files..."
rm -f storage/logs/*.log
rm -f storage/logs/incoming-api-tests/*.json
rm -f php_errors.log
echo "*.log" >> storage/logs/.gitignore

# Remove test logs and responses
echo "Removing test logs and data..."
rm -rf sit-tests/incoming-api-tests/logs/*
rm -rf uat-tests/logs/*
rm -rf test/logs/*
echo "*.json" > sit-tests/incoming-api-tests/logs/.gitignore
echo "*.log" >> sit-tests/incoming-api-tests/logs/.gitignore

# Remove large calculator and example files
echo "Removing example and calculator files..."
rm -f public/calculator.xlsx
rm -f public/*.xlsx
rm -f public/*.xls

# Remove compiled assets that should be built locally
echo "Removing compiled assets (will be rebuilt during provisioning)..."
rm -rf public/build/*
rm -rf public/assets/js/*.map
rm -rf public/assets/css/*.map
rm -rf resources/public/js/assets/*

# Remove node_modules if it exists (should use npm install)
echo "Removing node_modules..."
rm -rf node_modules/

# Remove vendor directory (should use composer install)
echo "Removing vendor directory..."
rm -rf vendor/

# Remove IDE and system files
echo "Removing IDE and system files..."
find . -name ".DS_Store" -delete
rm -rf .idea/
rm -rf .vscode/workspace-settings.json

# Remove cache and temp files
echo "Removing cache and temp files..."
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
rm -rf storage/framework/testing/*
rm -rf bootstrap/cache/*.php

# Remove any backup files
echo "Removing backup files..."
find . -name "*.bak" -delete
find . -name "*.backup" -delete
find . -name "*.old" -delete
find . -name "*~" -delete

# Remove database dumps and backups
echo "Removing database dumps..."
rm -f database/*.sql
rm -f database/*.dump
rm -f database/backups/*
rm -rf data_migration/*.sql
rm -rf data_migration/*.csv

# Remove any uploaded images except core assets
echo "Removing uploaded images..."
find public -type f \( -name "*.png" -o -name "*.jpg" -o -name "*.jpeg" -o -name "*.gif" \) -size +500k -delete

# Create necessary .gitignore files
echo "Creating .gitignore files..."

# Main .gitignore additions
cat >> .gitignore << 'EOF'

# Client uploads
/public/client-documents/*
!/public/client-documents/.gitkeep
/storage/app/public/documents/*
/storage/app/documents/*

# Logs
*.log
/storage/logs/*.log
/sit-tests/incoming-api-tests/logs/*
/uat-tests/logs/*

# Compiled assets
/public/build/*
/public/assets/js/*.map
/public/assets/css/*.map

# Large files
*.xlsx
*.xls
/public/calculator.xlsx

# Database dumps
*.sql
*.dump
/database/backups/*
/data_migration/*

# IDE
.idea/
.vscode/
*.swp
*.swo

# System files
.DS_Store
Thumbs.db

# Dependencies (install with composer/npm)
/vendor/
/node_modules/

# Cache
/storage/framework/cache/data/*
/storage/framework/sessions/*
/storage/framework/views/*
/bootstrap/cache/*
EOF

echo "Repository cleanup completed!"
echo ""
echo "Summary of removed items:"
echo "- Client documents and uploads"
echo "- Log files and test data"
echo "- Vendor and node_modules directories"
echo "- Compiled assets and source maps"
echo "- Cache and temporary files"
echo "- Database dumps and backups"
echo "- Large image files (>500KB)"
echo ""
echo "Next steps:"
echo "1. Review changes: git status"
echo "2. Add changes: git add -A"
echo "3. Commit: git commit -m 'Clean up repository - remove unnecessary files'"
echo "4. Clean Git history to permanently remove large files (see cleanup-git-history.sh)"