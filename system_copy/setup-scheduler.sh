#!/bin/bash

# SACCOS Core System - Laravel Scheduler Setup Script
# This script sets up the Laravel scheduler to run automatically

echo "================================================"
echo "SACCOS Core System - Scheduler Setup"
echo "================================================"
echo ""

# Get the current project directory
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
PHP_PATH=$(which php)

echo "Project Directory: $PROJECT_DIR"
echo "PHP Path: $PHP_PATH"
echo ""

# Function to add cron job
add_cron_job() {
    echo "Setting up Laravel scheduler cron job..."
    
    # Define the cron job
    CRON_JOB="* * * * * cd $PROJECT_DIR && $PHP_PATH artisan schedule:run >> /dev/null 2>&1"
    
    # Check if cron job already exists
    if crontab -l 2>/dev/null | grep -q "artisan schedule:run"; then
        echo "✓ Laravel scheduler cron job already exists"
    else
        # Add the cron job
        (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
        echo "✓ Laravel scheduler cron job added successfully"
    fi
    
    echo ""
    echo "Current cron jobs:"
    crontab -l 2>/dev/null | grep "artisan" || echo "No Laravel cron jobs found"
}

# Function to create systemd service (for Linux servers)
create_systemd_service() {
    echo ""
    echo "Creating systemd service file (for production servers)..."
    
    cat > laravel-scheduler.service << EOF
[Unit]
Description=Laravel Scheduler for SACCOS Core System
After=network.target

[Service]
Type=oneshot
User=www-data
Group=www-data
WorkingDirectory=$PROJECT_DIR
ExecStart=$PHP_PATH $PROJECT_DIR/artisan schedule:run

[Install]
WantedBy=multi-user.target
EOF
    
    echo "✓ Systemd service file created: laravel-scheduler.service"
    echo ""
    echo "To install on production server, run:"
    echo "  sudo cp laravel-scheduler.service /etc/systemd/system/"
    echo "  sudo systemctl daemon-reload"
    echo "  sudo systemctl enable laravel-scheduler.timer"
}

# Function to create systemd timer (for Linux servers)
create_systemd_timer() {
    cat > laravel-scheduler.timer << EOF
[Unit]
Description=Run Laravel Scheduler every minute for SACCOS Core System
Requires=laravel-scheduler.service

[Timer]
OnCalendar=*-*-* *:*:00
Persistent=true

[Install]
WantedBy=timers.target
EOF
    
    echo "✓ Systemd timer file created: laravel-scheduler.timer"
}

# Function to create supervisor configuration
create_supervisor_config() {
    echo ""
    echo "Creating supervisor configuration (alternative for production)..."
    
    cat > saccos-scheduler.conf << EOF
[program:saccos-scheduler]
process_name=%(program_name)s
command=$PHP_PATH $PROJECT_DIR/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=$PROJECT_DIR/storage/logs/scheduler.log
stopwaitsecs=3600
EOF
    
    echo "✓ Supervisor config created: saccos-scheduler.conf"
    echo ""
    echo "To install on production server with supervisor, run:"
    echo "  sudo cp saccos-scheduler.conf /etc/supervisor/conf.d/"
    echo "  sudo supervisorctl reread"
    echo "  sudo supervisorctl update"
}

# Function to test the scheduler
test_scheduler() {
    echo ""
    echo "Testing scheduler setup..."
    echo "================================================"
    
    # Test if scheduler can run
    echo "Running scheduler test..."
    cd "$PROJECT_DIR"
    $PHP_PATH artisan schedule:list
    
    echo ""
    echo "Testing daily activities command..."
    $PHP_PATH artisan system:daily-activities --help
    
    echo ""
    echo "✓ Scheduler test completed"
}

# Function to create log directory if it doesn't exist
setup_logs() {
    echo ""
    echo "Setting up log directories..."
    
    LOG_DIR="$PROJECT_DIR/storage/logs"
    
    # Create log directory if it doesn't exist
    if [ ! -d "$LOG_DIR" ]; then
        mkdir -p "$LOG_DIR"
        echo "✓ Created log directory: $LOG_DIR"
    else
        echo "✓ Log directory exists: $LOG_DIR"
    fi
    
    # Create specific log files
    touch "$LOG_DIR/daily-activities.log"
    touch "$LOG_DIR/scheduled-reports.log"
    touch "$LOG_DIR/monthly-activities.log"
    touch "$LOG_DIR/quarterly-activities.log"
    
    # Set permissions
    chmod -R 775 "$LOG_DIR"
    
    echo "✓ Log files created and permissions set"
}

# Main execution
echo "Choose setup option:"
echo "1. Development (macOS/local) - Add cron job only"
echo "2. Production (Linux) - Create systemd service files"
echo "3. Production (with Supervisor) - Create supervisor config"
echo "4. All options - Create all configuration files"
echo ""
read -p "Enter option (1-4): " OPTION

case $OPTION in
    1)
        add_cron_job
        setup_logs
        test_scheduler
        ;;
    2)
        create_systemd_service
        create_systemd_timer
        setup_logs
        test_scheduler
        ;;
    3)
        create_supervisor_config
        setup_logs
        test_scheduler
        ;;
    4)
        add_cron_job
        create_systemd_service
        create_systemd_timer
        create_supervisor_config
        setup_logs
        test_scheduler
        ;;
    *)
        echo "Invalid option. Please run the script again."
        exit 1
        ;;
esac

echo ""
echo "================================================"
echo "IMPORTANT: Daily Activities Schedule"
echo "================================================"
echo ""
echo "The system:daily-activities command is scheduled to run:"
echo "  • Time: Daily at 00:05 (12:05 AM)"
echo "  • Log file: storage/logs/daily-activities.log"
echo ""
echo "This command executes:"
echo "  1. Loan processing (repayments, arrears, provisions)"
echo "  2. Savings & deposit interest calculations"
echo "  3. Share management and dividends"
echo "  4. Financial reconciliation"
echo "  5. Member services and benefits"
echo "  6. Compliance reporting"
echo "  7. System maintenance and backups"
echo "  8. Security audits"
echo ""
echo "To manually run the daily activities:"
echo "  php artisan system:daily-activities"
echo ""
echo "To monitor the scheduler:"
echo "  php artisan schedule:list"
echo ""
echo "To view logs:"
echo "  tail -f storage/logs/daily-activities.log"
echo ""
echo "================================================"
echo "Setup completed successfully!"
echo "================================================"