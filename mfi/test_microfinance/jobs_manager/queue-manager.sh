#!/bin/bash

# Queue Manager Script for SACCOS Core System
# This script manages Laravel queue workers

PROJECT_DIR="/Volumes/DATA/PROJECTS/SACCOS/SYSTEMS/SACCOS_CORE_SYSTEM"
LOG_DIR="$PROJECT_DIR/storage/logs"
PID_FILE="$PROJECT_DIR/jobs_manager/queue-workers.pid"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

cd $PROJECT_DIR

case "$1" in
    start)
        echo -e "${GREEN}Starting queue workers...${NC}"
        
        # Check if workers are already running
        if [ -f "$PID_FILE" ]; then
            while IFS= read -r pid; do
                if ps -p $pid > /dev/null 2>&1; then
                    echo -e "${YELLOW}Queue workers already running with PID: $pid${NC}"
                    exit 1
                fi
            done < "$PID_FILE"
        fi
        
        # Clear the PID file
        > "$PID_FILE"
        
        # Start multiple workers for different queues
        echo "Starting default queue worker..."
        nohup php artisan queue:work --queue=default --tries=3 --timeout=90 --sleep=3 >> "$LOG_DIR/queue-default.log" 2>&1 &
        echo $! >> "$PID_FILE"
        echo "Default queue worker started with PID: $!"
        
        echo "Starting high priority queue worker..."
        nohup php artisan queue:work --queue=high --tries=3 --timeout=90 --sleep=1 >> "$LOG_DIR/queue-high.log" 2>&1 &
        echo $! >> "$PID_FILE"
        echo "High priority queue worker started with PID: $!"
        
        echo "Starting notifications queue worker..."
        nohup php artisan queue:work --queue=notifications --tries=5 --timeout=60 --sleep=2 >> "$LOG_DIR/queue-notifications.log" 2>&1 &
        echo $! >> "$PID_FILE"
        echo "Notifications queue worker started with PID: $!"
        
        echo "Starting emails queue worker..."
        nohup php artisan queue:work --queue=emails --tries=3 --timeout=120 --sleep=5 >> "$LOG_DIR/queue-emails.log" 2>&1 &
        echo $! >> "$PID_FILE"
        echo "Emails queue worker started with PID: $!"
        
        echo -e "${GREEN}✅ Queue workers started successfully${NC}"
        echo "Logs are being written to: $LOG_DIR/queue-*.log"
        ;;
        
    stop)
        echo -e "${RED}Stopping queue workers...${NC}"
        
        if [ -f "$PID_FILE" ]; then
            while IFS= read -r pid; do
                if ps -p $pid > /dev/null 2>&1; then
                    kill $pid
                    echo "Stopped worker with PID: $pid"
                else
                    echo "Worker with PID $pid is not running"
                fi
            done < "$PID_FILE"
            rm -f "$PID_FILE"
            echo -e "${GREEN}✅ All queue workers stopped${NC}"
        else
            echo -e "${YELLOW}No PID file found. Workers might not be running.${NC}"
            # Try to find and kill any running queue workers
            pkill -f "queue:work"
            echo "Attempted to stop any running queue workers"
        fi
        ;;
        
    restart)
        $0 stop
        sleep 2
        $0 start
        ;;
        
    status)
        echo -e "${YELLOW}Queue Worker Status:${NC}"
        echo "-------------------"
        
        if [ -f "$PID_FILE" ]; then
            active_count=0
            while IFS= read -r pid; do
                if ps -p $pid > /dev/null 2>&1; then
                    echo -e "${GREEN}✅ Worker PID $pid is running${NC}"
                    ps -p $pid -o command= | head -c 80
                    echo ""
                    ((active_count++))
                else
                    echo -e "${RED}❌ Worker PID $pid is not running${NC}"
                fi
            done < "$PID_FILE"
            
            echo ""
            echo "Total active workers: $active_count"
        else
            echo -e "${YELLOW}No PID file found. Checking for running processes...${NC}"
            pgrep -f "queue:work" > /dev/null
            if [ $? -eq 0 ]; then
                echo -e "${YELLOW}Found queue workers running:${NC}"
                ps aux | grep "queue:work" | grep -v grep
            else
                echo -e "${RED}No queue workers are running${NC}"
            fi
        fi
        
        echo ""
        echo "Jobs Table Status:"
        php artisan tinker --execute="
            echo 'Total jobs: ' . DB::table('jobs')->count();
            echo ' | Pending: ' . DB::table('jobs')->whereNull('reserved_at')->count();
            echo ' | Failed: ' . DB::table('failed_jobs')->count() . PHP_EOL;
        "
        ;;
        
    logs)
        echo -e "${YELLOW}Recent queue worker logs:${NC}"
        echo "-------------------------"
        
        for log in "$LOG_DIR"/queue-*.log; do
            if [ -f "$log" ]; then
                filename=$(basename "$log")
                echo -e "${GREEN}$filename:${NC}"
                tail -n 5 "$log"
                echo ""
            fi
        done
        ;;
        
    clear)
        echo -e "${YELLOW}Clearing failed jobs...${NC}"
        php artisan queue:flush
        echo -e "${GREEN}✅ Failed jobs cleared${NC}"
        ;;
        
    *)
        echo "Usage: $0 {start|stop|restart|status|logs|clear}"
        echo ""
        echo "Commands:"
        echo "  start   - Start queue workers for all queues"
        echo "  stop    - Stop all running queue workers"
        echo "  restart - Restart all queue workers"
        echo "  status  - Show status of queue workers and jobs"
        echo "  logs    - Show recent logs from queue workers"
        echo "  clear   - Clear all failed jobs"
        exit 1
        ;;
esac

exit 0