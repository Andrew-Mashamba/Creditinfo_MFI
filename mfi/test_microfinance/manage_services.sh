#!/bin/bash

# SACCOS System Services Management Script
# Manages all queue workers, schedulers, and job listeners

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

function print_header() {
    echo ""
    echo "=================================================="
    echo "$1"
    echo "=================================================="
    echo ""
}

function start_services() {
    print_header "Starting SACCOS System Services"
    
    # Start queue workers
    echo -e "${YELLOW}Starting Queue Workers...${NC}"
    ./start_all_queues.sh --restart
    
    echo ""
    
    # Start scheduler
    echo -e "${YELLOW}Starting Laravel Scheduler...${NC}"
    ./start_scheduler.sh
    
    echo ""
    
    # Clear cache and optimize
    echo -e "${YELLOW}Optimizing Application...${NC}"
    php artisan cache:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    echo ""
    echo -e "${GREEN}✓ All services started successfully!${NC}"
}

function stop_services() {
    print_header "Stopping SACCOS System Services"
    
    echo -e "${YELLOW}Stopping all queue workers...${NC}"
    pkill -f "queue:work"
    
    echo -e "${YELLOW}Stopping scheduler...${NC}"
    pkill -f "schedule:work"
    
    echo ""
    echo -e "${GREEN}✓ All services stopped${NC}"
}

function restart_services() {
    stop_services
    sleep 2
    start_services
}

function status_services() {
    print_header "SACCOS System Services Status"
    
    echo -e "${YELLOW}Queue Workers:${NC}"
    echo "--------------------"
    if ps aux | grep -v grep | grep -q "queue:work"; then
        echo -e "${GREEN}✓ Queue workers are running:${NC}"
        ps aux | grep "queue:work" | grep -v grep | awk '{print "  PID:", $2, "Queue:", $15}'
    else
        echo -e "${RED}✗ No queue workers running${NC}"
    fi
    
    echo ""
    echo -e "${YELLOW}Laravel Scheduler:${NC}"
    echo "--------------------"
    if ps aux | grep -v grep | grep -q "schedule:work"; then
        echo -e "${GREEN}✓ Scheduler is running${NC}"
        ps aux | grep "schedule:work" | grep -v grep | awk '{print "  PID:", $2}'
    else
        echo -e "${RED}✗ Scheduler is not running${NC}"
    fi
    
    echo ""
    echo -e "${YELLOW}Failed Jobs:${NC}"
    echo "--------------------"
    php artisan queue:failed | head -10
    
    echo ""
    echo -e "${YELLOW}Pending Jobs:${NC}"
    echo "--------------------"
    php artisan queue:monitor default,notifications,EndOfDay,transaction-retries
}

function monitor_logs() {
    print_header "Monitoring Service Logs"
    
    echo "Available logs:"
    echo "1) Queue - Default"
    echo "2) Queue - Notifications"
    echo "3) Queue - EndOfDay"
    echo "4) Queue - Transaction Retries"
    echo "5) Scheduler"
    echo "6) Laravel (laravel.log)"
    echo "7) All queues (combined)"
    echo ""
    read -p "Select log to monitor (1-7): " choice
    
    case $choice in
        1) tail -f storage/logs/queue-default.log ;;
        2) tail -f storage/logs/queue-notifications.log ;;
        3) tail -f storage/logs/queue-EndOfDay.log ;;
        4) tail -f storage/logs/queue-transaction-retries.log ;;
        5) tail -f storage/logs/scheduler.log ;;
        6) tail -f storage/logs/laravel.log ;;
        7) tail -f storage/logs/queue-*.log ;;
        *) echo "Invalid choice" ;;
    esac
}

function retry_failed_jobs() {
    print_header "Retrying Failed Jobs"
    
    echo -e "${YELLOW}Failed jobs:${NC}"
    php artisan queue:failed
    
    echo ""
    read -p "Retry all failed jobs? (y/n): " confirm
    
    if [ "$confirm" = "y" ]; then
        php artisan queue:retry all
        echo -e "${GREEN}✓ Retrying all failed jobs${NC}"
    else
        read -p "Enter job ID to retry (or 'cancel'): " job_id
        if [ "$job_id" != "cancel" ]; then
            php artisan queue:retry "$job_id"
            echo -e "${GREEN}✓ Retrying job $job_id${NC}"
        fi
    fi
}

function clear_jobs() {
    print_header "Clear Jobs"
    
    echo "1) Clear all failed jobs"
    echo "2) Clear specific failed job"
    echo "3) Flush all pending jobs"
    echo ""
    read -p "Select option (1-3): " choice
    
    case $choice in
        1) 
            php artisan queue:flush
            echo -e "${GREEN}✓ All failed jobs cleared${NC}"
            ;;
        2)
            php artisan queue:failed
            read -p "Enter job ID to forget: " job_id
            php artisan queue:forget "$job_id"
            echo -e "${GREEN}✓ Job $job_id forgotten${NC}"
            ;;
        3)
            read -p "⚠️  This will delete ALL pending jobs. Are you sure? (yes/no): " confirm
            if [ "$confirm" = "yes" ]; then
                php artisan queue:clear
                echo -e "${GREEN}✓ All pending jobs cleared${NC}"
            fi
            ;;
        *) echo "Invalid choice" ;;
    esac
}

# Main menu
function main_menu() {
    clear
    print_header "SACCOS System Services Manager"
    
    echo "1) Start all services"
    echo "2) Stop all services"
    echo "3) Restart all services"
    echo "4) Check services status"
    echo "5) Monitor logs"
    echo "6) Retry failed jobs"
    echo "7) Clear jobs"
    echo "8) Exit"
    echo ""
    read -p "Select option (1-8): " choice
    
    case $choice in
        1) start_services ;;
        2) stop_services ;;
        3) restart_services ;;
        4) status_services ;;
        5) monitor_logs ;;
        6) retry_failed_jobs ;;
        7) clear_jobs ;;
        8) 
            echo "Goodbye!"
            exit 0
            ;;
        *) 
            echo -e "${RED}Invalid choice${NC}"
            sleep 2
            main_menu
            ;;
    esac
    
    echo ""
    read -p "Press Enter to continue..."
    main_menu
}

# Handle command line arguments
case "$1" in
    start)
        start_services
        ;;
    stop)
        stop_services
        ;;
    restart)
        restart_services
        ;;
    status)
        status_services
        ;;
    *)
        main_menu
        ;;
esac