#!/bin/bash

echo "WARNING: This will rewrite Git history!"
echo "Make sure you have a backup and all team members are aware."
echo "Press Ctrl+C to cancel, or Enter to continue..."
read

# Use git filter-branch to remove large files from history
echo "Removing large files from Git history..."

# Remove client documents directory from all commits
git filter-branch --force --index-filter \
  'git rm -r --cached --ignore-unmatch public/client-documents/' \
  --prune-empty --tag-name-filter cat -- --all

# Remove test logs from history
git filter-branch --force --index-filter \
  'git rm -r --cached --ignore-unmatch sit-tests/incoming-api-tests/logs/' \
  --prune-empty --tag-name-filter cat -- --all

# Remove vendor directory from history
git filter-branch --force --index-filter \
  'git rm -r --cached --ignore-unmatch vendor/' \
  --prune-empty --tag-name-filter cat -- --all

# Remove node_modules from history
git filter-branch --force --index-filter \
  'git rm -r --cached --ignore-unmatch node_modules/' \
  --prune-empty --tag-name-filter cat -- --all

# Remove log files from history
git filter-branch --force --index-filter \
  'git rm --cached --ignore-unmatch storage/logs/*.log' \
  --prune-empty --tag-name-filter cat -- --all

# Remove specific large files from history
git filter-branch --force --index-filter \
  'git rm --cached --ignore-unmatch public/calculator.xlsx' \
  --prune-empty --tag-name-filter cat -- --all

# Clean up refs and garbage collect
echo "Cleaning up Git references..."
rm -rf .git/refs/original/
git reflog expire --expire=now --all
git gc --prune=now --aggressive

echo "Git history cleanup completed!"
echo ""
echo "Repository size before: $(du -sh .git | cut -f1)"
echo ""
echo "IMPORTANT: You must force push to update the remote repository:"
echo "git push origin --force --all"
echo "git push origin --force --tags"
echo ""
echo "All team members will need to re-clone the repository after this operation."