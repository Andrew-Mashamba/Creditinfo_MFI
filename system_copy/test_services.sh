#!/bin/bash

###############################################################################
# Service API Test Runner
# 
# This script provides an easy interface to test all API endpoints
# with comprehensive logging and debugging information.
#
# Usage:
#   ./test_services.sh                    # Test all services
#   ./test_services.sh list               # List available services
#   ./test_services.sh <service-name>     # Test specific service
#   ./test_services.sh --verbose          # Test with verbose output
#   ./test_services.sh --env staging      # Test against staging environment
###############################################################################

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color
BOLD='\033[1m'

# Default values
ENVIRONMENT="local"
SERVICE="all"
VERBOSE=false
WATCH_LOGS=false
PHP_BINARY="php"

# Function to print colored output
print_color() {
    local color=$1
    shift
    echo -e "${color}$*${NC}"
}

# Function to print banner
print_banner() {
    echo ""
    print_color "$CYAN" "╔════════════════════════════════════════════════════════════════════╗"
    print_color "$CYAN" "║                    ${BOLD}API SERVICE TEST RUNNER${NC}${CYAN}                        ║"
    print_color "$CYAN" "╚════════════════════════════════════════════════════════════════════╝"
    echo ""
}

# Function to show usage
show_usage() {
    echo "Usage: $0 [OPTIONS] [SERVICE]"
    echo ""
    echo "OPTIONS:"
    echo "  -e, --env ENV        Environment to test (local, staging, production)"
    echo "  -v, --verbose        Enable verbose output"
    echo "  -w, --watch          Watch log file in real-time"
    echo "  -h, --help           Show this help message"
    echo "  -l, --list           List all available services"
    echo ""
    echo "SERVICES:"
    echo "  all                       Test all services (default)"
    echo "  internal-funds-transfer   Test internal funds transfer"
    echo "  external-funds-transfer   Test external funds transfer"
    echo "  mini-statement           Test mini statement"
    echo "  full-statement           Test full statement"
    echo "  account-lookup           Test account lookup"
    echo "  utilities                Test utilities payment"
    echo "  sms                      Test SMS service"
    echo "  direct-debit             Test direct debit"
    echo "  control-number           Test control number payments"
    echo "  luku                     Test LUKU service"
    echo "  gepg                     Test GEPG service"
    echo "  pay-by-link              Test pay by link"
    echo ""
    echo "EXAMPLES:"
    echo "  $0                                    # Test all services"
    echo "  $0 -e staging                         # Test all services on staging"
    echo "  $0 sms                                # Test only SMS service"
    echo "  $0 -v luku                            # Test LUKU with verbose output"
    echo "  $0 -w internal-funds-transfer         # Test and watch logs"
    echo ""
}

# Function to check dependencies
check_dependencies() {
    print_color "$YELLOW" "Checking dependencies..."
    
    # Check PHP
    if ! command -v php &> /dev/null; then
        print_color "$RED" "✗ PHP is not installed"
        exit 1
    else
        PHP_VERSION=$(php -v | head -n 1)
        print_color "$GREEN" "✓ PHP found: $PHP_VERSION"
    fi
    
    # Check curl
    if ! command -v curl &> /dev/null; then
        print_color "$RED" "✗ curl is not installed"
        exit 1
    else
        print_color "$GREEN" "✓ curl found"
    fi
    
    # Check if Laravel is running (for local environment)
    if [ "$ENVIRONMENT" == "local" ]; then
        if curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000 | grep -q "000"; then
            print_color "$YELLOW" "⚠ Laravel server might not be running on port 8000"
            print_color "$YELLOW" "  Run: php artisan serve"
            echo ""
            read -p "Do you want to continue anyway? (y/n): " -n 1 -r
            echo ""
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                exit 1
            fi
        else
            print_color "$GREEN" "✓ Laravel server is accessible"
        fi
    fi
    
    echo ""
}

# Function to create test results directory
setup_directories() {
    if [ ! -d "test_results" ]; then
        mkdir -p test_results
        print_color "$GREEN" "✓ Created test_results directory"
    fi
    
    # Create archive directory for old results
    if [ ! -d "test_results/archive" ]; then
        mkdir -p test_results/archive
    fi
    
    # Archive old results (keep last 10)
    local count=$(ls -1 test_results/*.json 2>/dev/null | wc -l)
    if [ $count -gt 10 ]; then
        print_color "$YELLOW" "Archiving old test results..."
        ls -1t test_results/*.json | tail -n +11 | xargs -I {} mv {} test_results/archive/
    fi
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -e|--env)
            ENVIRONMENT="$2"
            shift 2
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        -w|--watch)
            WATCH_LOGS=true
            shift
            ;;
        -h|--help)
            show_usage
            exit 0
            ;;
        -l|--list)
            SERVICE="list"
            shift
            ;;
        *)
            SERVICE="$1"
            shift
            ;;
    esac
done

# Main execution
print_banner

# Show current configuration
print_color "$BLUE" "Configuration:"
echo "  Environment: $ENVIRONMENT"
echo "  Service: $SERVICE"
echo "  Verbose: $VERBOSE"
echo "  Watch Logs: $WATCH_LOGS"
echo ""

# Check dependencies
check_dependencies

# Setup directories
setup_directories

# Run the PHP test script
print_color "$YELLOW" "Starting tests..."
echo ""

TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
LOG_FILE="test_results/service_test_${TIMESTAMP}.log"

if [ "$VERBOSE" = true ]; then
    $PHP_BINARY test_all_services.php "$ENVIRONMENT" "$SERVICE" 2>&1 | tee "$LOG_FILE"
else
    $PHP_BINARY test_all_services.php "$ENVIRONMENT" "$SERVICE" 2>&1
fi

TEST_EXIT_CODE=$?

# Check if we should watch the logs
if [ "$WATCH_LOGS" = true ] && [ -f "$LOG_FILE" ]; then
    print_color "$YELLOW" "\nWatching log file (Press Ctrl+C to stop)..."
    tail -f "$LOG_FILE"
fi

# Show completion message
echo ""
if [ $TEST_EXIT_CODE -eq 0 ]; then
    print_color "$GREEN" "═══════════════════════════════════════════════════════════════"
    print_color "$GREEN" "                    TESTS COMPLETED SUCCESSFULLY"
    print_color "$GREEN" "═══════════════════════════════════════════════════════════════"
else
    print_color "$RED" "═══════════════════════════════════════════════════════════════"
    print_color "$RED" "                    TESTS COMPLETED WITH ERRORS"
    print_color "$RED" "═══════════════════════════════════════════════════════════════"
fi

# Show result file locations
echo ""
print_color "$CYAN" "Results saved to:"
echo "  • Log file: test_results/service_test_${TIMESTAMP}.log"
echo "  • JSON file: test_results/service_test_${TIMESTAMP}.json"
echo ""

# Offer to open the results
if command -v jq &> /dev/null; then
    read -p "Do you want to view the JSON results with jq? (y/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        jq '.' "test_results/service_test_${TIMESTAMP}.json" | less
    fi
fi

exit $TEST_EXIT_CODE