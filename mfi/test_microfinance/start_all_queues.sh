#!/bin/bash

# SACCOS Queue Workers Management Script
# This script starts all configured queue workers

echo "============================================"
echo "Starting SACCOS Queue Workers"
echo "============================================"
echo ""

# Function to check if a queue worker is already running
is_queue_running() {
    local queue_name=$1
    if ps aux | grep -v grep | grep -q "queue:work.*--queue=$queue_name"; then
        return 0
    else
        return 1
    fi
}

# Function to start a queue worker
start_queue() {
    local queue_name=$1
    local tries=$2
    local timeout=$3
    local memory=$4
    
    if is_queue_running "$queue_name"; then
        echo "✓ Queue '$queue_name' is already running"
    else
        echo "Starting queue: $queue_name"
        nohup php artisan queue:work database \
            --queue="$queue_name" \
            --tries="$tries" \
            --timeout="$timeout" \
            --memory="$memory" \
            --sleep=3 \
            --daemon \
            > "storage/logs/queue-$queue_name.log" 2>&1 &
        
        sleep 2
        
        if is_queue_running "$queue_name"; then
            echo "✓ Queue '$queue_name' started successfully"
        else
            echo "✗ Failed to start queue '$queue_name'"
        fi
    fi
}

# Kill existing queue workers if requested
if [ "$1" == "--restart" ]; then
    echo "Stopping all existing queue workers..."
    pkill -f "queue:work"
    sleep 2
    echo "All queue workers stopped."
    echo ""
fi

# Start all configured queues
echo "Starting queue workers..."
echo "------------------------"

# Default queue - for general jobs
start_queue "default" 3 90 256

# Notifications queue - for email/SMS notifications
start_queue "notifications" 5 60 128

# EndOfDay queue - for end-of-day processing
start_queue "EndOfDay" 2 300 512

# Transaction retries queue - for failed transaction retries
start_queue "transaction-retries" 10 120 256

echo ""
echo "============================================"
echo "Queue Workers Status"
echo "============================================"
ps aux | grep "queue:work" | grep -v grep | awk '{print $2, $11, $12, $13, $14, $15}'

echo ""
echo "To view logs:"
echo "  tail -f storage/logs/queue-default.log"
echo "  tail -f storage/logs/queue-notifications.log"
echo "  tail -f storage/logs/queue-EndOfDay.log"
echo "  tail -f storage/logs/queue-transaction-retries.log"
echo ""
echo "To restart all queues: ./start_all_queues.sh --restart"
echo "To stop all queues: pkill -f 'queue:work'"