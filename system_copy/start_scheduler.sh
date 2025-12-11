#!/bin/bash

# SACCOS Laravel Scheduler Management Script
# This script ensures the Laravel scheduler is running

echo "============================================"
echo "Starting SACCOS Laravel Scheduler"
echo "============================================"
echo ""

# Check if scheduler is already running
if ps aux | grep -v grep | grep -q "schedule:work"; then
    echo "✓ Laravel scheduler is already running"
    ps aux | grep "schedule:work" | grep -v grep
else
    echo "Starting Laravel scheduler..."
    nohup php artisan schedule:work > storage/logs/scheduler.log 2>&1 &
    
    sleep 2
    
    if ps aux | grep -v grep | grep -q "schedule:work"; then
        echo "✓ Laravel scheduler started successfully"
        ps aux | grep "schedule:work" | grep -v grep
    else
        echo "✗ Failed to start Laravel scheduler"
        echo "Trying alternative method with cron..."
        
        # Add to crontab if not already present
        cron_entry="* * * * * cd /Volumes/DATA/PROJECTS/SACCOS/SYSTEMS/SACCOS_CORE_SYSTEM && php artisan schedule:run >> /dev/null 2>&1"
        
        if ! crontab -l 2>/dev/null | grep -q "schedule:run"; then
            (crontab -l 2>/dev/null; echo "$cron_entry") | crontab -
            echo "✓ Added Laravel scheduler to crontab"
        else
            echo "✓ Laravel scheduler already in crontab"
        fi
    fi
fi

echo ""
echo "Scheduled Tasks:"
echo "----------------"
echo "• Hourly: Generate scheduled reports"
echo "• Daily (02:00): Cleanup old report files"
echo "• Daily (00:05): System daily activities & budget monitoring"
echo "• Weekly (Sundays 06:00): Generate weekly reports"
echo "• Monthly (1st, 07:00): Generate monthly reports"
echo "• Monthly (Last day, 23:00): Monthly system activities"
echo "• Monthly (Last day, 23:30): Monthly budget close"
echo "• Quarterly (23:00): Quarterly system activities"
echo "• Quarterly (23:45): Quarterly budget close"
echo ""
echo "To view scheduler logs: tail -f storage/logs/scheduler.log"
echo "To view crontab: crontab -l"