#!/bin/bash

# Test Payment Link Generation Script
# Usage: ./tests/test_payment_link.sh [test_type]

echo "========================================="
echo "  SACCOS Payment Link Test Suite"
echo "========================================="
echo ""

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ️  $1${NC}"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "Error: This script must be run from the Laravel root directory"
    exit 1
fi

# Parse arguments
TEST_TYPE=${1:-"installments"}
USE_REAL_DATA=${2:-""}

print_info "Test Type: $TEST_TYPE"

case $TEST_TYPE in
    "all")
        print_info "Running all payment link tests..."
        echo ""
        
        echo "1. Testing URL Generation..."
        php artisan payment:test-link --type=url
        echo ""
        
        echo "2. Testing Full Response..."
        php artisan payment:test-link --type=full
        echo ""
        
        echo "3. Testing Member Payment..."
        php artisan payment:test-link --type=member
        echo ""
        
        echo "4. Testing Loan Payment..."
        php artisan payment:test-link --type=loan
        echo ""
        
        echo "5. Testing Loan Installments..."
        php artisan payment:test-link --type=installments
        ;;
        
    "installments")
        print_info "Testing Loan Installments Payment Link..."
        echo ""
        
        if [ "$USE_REAL_DATA" == "real" ]; then
            php artisan payment:test-link --type=installments --real
        else
            php artisan payment:test-link --type=installments
        fi
        ;;
        
    "url")
        print_info "Testing Payment URL Generation..."
        php artisan payment:test-link --type=url
        ;;
        
    "full")
        print_info "Testing Full Response..."
        php artisan payment:test-link --type=full
        ;;
        
    "member")
        print_info "Testing Member Payment..."
        php artisan payment:test-link --type=member
        ;;
        
    "loan")
        print_info "Testing Loan Payment..."
        php artisan payment:test-link --type=loan
        ;;
        
    "standalone")
        print_info "Running standalone PHP test script..."
        php tests/PaymentLink/test_payment_link_generation.php
        ;;
        
    "help")
        echo "Usage: $0 [test_type] [real]"
        echo ""
        echo "Available test types:"
        echo "  all         - Run all tests"
        echo "  installments - Test loan installments (default)"
        echo "  url         - Test URL generation only"
        echo "  full        - Test full response"
        echo "  member      - Test member payment"
        echo "  loan        - Test single loan payment"
        echo "  standalone  - Run standalone PHP test"
        echo "  help        - Show this help message"
        echo ""
        echo "Options:"
        echo "  real        - Use real data from database (for installments test)"
        echo ""
        echo "Examples:"
        echo "  $0                    # Run default installments test with mock data"
        echo "  $0 installments       # Run installments test with mock data"
        echo "  $0 installments real  # Run installments test with real database data"
        echo "  $0 all                # Run all tests"
        echo "  $0 help               # Show help"
        ;;
        
    *)
        print_error "Unknown test type: $TEST_TYPE"
        echo "Use '$0 help' for usage information"
        exit 1
        ;;
esac

echo ""
print_success "Test execution completed!"
echo ""

# Check logs for any errors
if [ -f "storage/logs/laravel-$(date +%Y-%m-%d).log" ]; then
    ERROR_COUNT=$(grep -c "ERROR" storage/logs/laravel-$(date +%Y-%m-%d).log 2>/dev/null || echo "0")
    if [ "$ERROR_COUNT" -gt "0" ]; then
        print_info "Found $ERROR_COUNT errors in today's log file"
        echo "Check storage/logs/laravel-$(date +%Y-%m-%d).log for details"
    fi
fi

exit 0