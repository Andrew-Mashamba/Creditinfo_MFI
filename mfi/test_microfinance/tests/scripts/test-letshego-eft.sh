#!/bin/bash

# Letshego EFT Service Test Script
# Usage: ./test-letshego-eft.sh [test_name]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
BASE_URL="https://api-staging-cb.letshego.com/letshego-api-gw/tz-eftpay-tips/v2"
CONSUMER_ID="12123"
API_KEY="eyJ4NXQiOiJPREUzWTJaaE1UQmpNRE00WlRCbU1qQXlZemxpWVRJMllqUmhZVFpsT0dJeVptVXhOV0UzWVE9PSIsImtpZCI6ImdhdGV3YXlfY2VydGlmaWNhdGVfYWxpYXMiLCJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiJhZG1pbkBjYXJib24uc3VwZXIiLCJhcHBsaWNhdGlvbiI6eyJvd25lciI6ImFkbWluIiwidGllclF1b3RhVHlwZSI6bnVsbCwidGllciI6IlVubGltaXRlZCIsIm5hbWUiOiJUWi1USVBTLVJBSUxTLVYyIiwiaWQiOjcsInV1aWQiOiJhZWU4ZmE5NC0xNzQ1LTQyM2UtOGZjYi1kMzMwMDkyZGZkYzMifSwiaXNzIjoiaHR0cHM6XC9cL3dzbzItYXBpbS1zdGFnaW5nLmxldHNoZWdvLmNvbTo5NDQ0XC9vYXV0aDJcL3Rva2VuIiwidGllckluZm8iOnsiVW5saW1pdGVkIjp7InRpZXJRdW90YVR5cGUiOiJyZXF1ZXN0Q291bnQiLCJncmFwaFFMTWF4Q29tcGxleGl0eSI6MCwiZ3JhcGhRTE1heERlcHRoIjowLCJzdG9wT25RdW90YVJlYWNoIjp0cnVlLCJzcGlrZUFycmVzdExpbWl0IjowLCJzcGlrZUFycmVzdFVuaXQiOm51bGx9fSwia2V5dHlwZSI6IlBST0RVQ1RJT04iLCJwZXJtaXR0ZWRSZWZlcmVyIjoiIiwic3Vic2NyaWJlZEFQSXMiOlt7InN1YnNjcmliZXJUZW5hbnREb21haW4iOiJjYXJib24uc3VwZXIiLCJuYW1lIjoidHotZWZ0cGF5LXRpcHMtcmFpbHMiLCJjb250ZXh0IjoiXC9sZXRzaGVnby1hcGktZ3dcL3R6LWVmdHBheS10aXBzXC92MiIsInB1Ymxpc2hlciI6ImFkbWluIiwidmVyc2lvbiI6InYyIiwic3Vic2NyaXB0aW9uVGllciI6IlVubGltaXRlZCJ9XSwidG9rZW5fdHlwZSI6ImFwaUtleSIsInBlcm1pdHRlZElQIjoiIiwiaWF0IjoxNzU3MzI5ODYwLCJqdGkiOiJjMDQ3NDY3Yy1kZmE1LTRhMTItOTU4ZC00ZGEyOGExMDFhYzcifQ==.ME2XheKIiUrdG5cOZ0INhmoNemXUL_hLS4Qx54TdszlKEHhcZf375GJIHQvEFTr-Lc7bXQNcWfyBzGcD7GfVo_eg8eECCfjyaTF_8JiGYpa2ImSPMKnhwU4O4b1YnuWpQC5bjpDDx5c9K2RnGurmPbxVf4nRSs4zUTTGk3xz2ktCD9hgAUOAdic2ubandIv9XnzZ7tjxufI5TcVNUlvu5Gxwg_BnIF9KFfVXkM-TSI0JnPBzBrgqbgPc9NXLCgDPQu2OaZWmxTnY1N4lkI29pNPetZ47zN0NHzHGQfeGJ9qOIKXeI4t_7mjajM-TCVfNOxhdDSouQUL2Hv5ba0HVCA=="

# Test data
TEST_ACCOUNT="0752518001"
TEST_BANK_CODE="016"
LETSHEGO_ACCOUNT="21710176786"
SAMPLE_REF="044-WK175371850125227076"

# Functions
print_header() {
    echo -e "\n${BLUE}===========================================${NC}"
    echo -e "${BLUE}🚀 Letshego EFT Service Test Suite${NC}"
    echo -e "${BLUE}===========================================${NC}\n"
}

print_test() {
    echo -e "${YELLOW}📋 $1${NC}"
    echo -e "${BLUE}Endpoint:${NC} $2"
    echo ""
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

make_request() {
    local method=$1
    local endpoint=$2
    local headers=$3
    local data=$4
    
    local url="${BASE_URL}${endpoint}"
    
    echo -e "${BLUE}Request:${NC} $method $url"
    echo -e "${BLUE}Headers:${NC} $headers"
    if [ ! -z "$data" ]; then
        echo -e "${BLUE}Data:${NC} $data"
    fi
    echo ""
    
    local start_time=$(date +%s%N)
    
    if [ "$method" = "GET" ]; then
        response=$(curl -s -w "\n%{http_code}" $headers "$url")
    else
        response=$(curl -s -w "\n%{http_code}" -X $method $headers -d "$data" "$url")
    fi
    
    local end_time=$(date +%s%N)
    local duration=$(( (end_time - start_time) / 1000000 ))
    
    local http_code=$(echo "$response" | tail -n1)
    local body=$(echo "$response" | head -n -1)
    
    echo -e "${BLUE}Response Code:${NC} $http_code"
    echo -e "${BLUE}Duration:${NC} ${duration}ms"
    echo -e "${BLUE}Response Body:${NC}"
    echo "$body" | jq . 2>/dev/null || echo "$body"
    echo ""
    
    if [ $http_code -ge 200 ] && [ $http_code -lt 300 ]; then
        print_success "Request successful"
    else
        print_error "Request failed with code $http_code"
    fi
    
    echo "-------------------------------------------"
    return $http_code
}

test_institutions() {
    print_test "Test 1: Institution List" "GET /${CONSUMER_ID}/outwards/institution"
    
    local headers="-H 'apikey: $API_KEY'"
    make_request "GET" "/${CONSUMER_ID}/outwards/institution" "$headers"
}

test_account_lookup() {
    print_test "Test 2: Account Lookup" "GET /${CONSUMER_ID}/outwards/accountenquiry/${TEST_ACCOUNT}"
    
    local headers="-H 'apikey: $API_KEY' -H 'fsp-destination: $TEST_BANK_CODE' -H 'x-user-platform: web'"
    make_request "GET" "/${CONSUMER_ID}/outwards/accountenquiry/${TEST_ACCOUNT}" "$headers"
}

test_invalid_account_lookup() {
    print_test "Test 3: Invalid Account Lookup" "GET /${CONSUMER_ID}/outwards/accountenquiry/1234567890"
    
    local headers="-H 'apikey: $API_KEY' -H 'fsp-destination: 999' -H 'x-user-platform: web'"
    make_request "GET" "/${CONSUMER_ID}/outwards/accountenquiry/1234567890" "$headers"
}

test_transfer() {
    print_test "Test 4: Money Transfer" "POST /${CONSUMER_ID}/outwards/transfers"
    
    local timestamp=$(date +%s)
    local payer_ref="044-TEST${timestamp}"
    
    local headers="-H 'Content-Type: application/json' -H 'apikey: $API_KEY' -H 'x-user-platform: web'"
    
    local data='{
        "payee": {
            "identifierType": "BANK",
            "identifier": "'$TEST_ACCOUNT'",
            "fspId": "'$TEST_BANK_CODE'",
            "fullName": "HAJI NYEMBO",
            "accountCategory": "PERSON",
            "accountType": "BANK",
            "identity": {
                "type": "NIN",
                "value": ""
            },
            "additionalInfo": null
        },
        "payer": {
            "identifierType": "BANK",
            "identifier": "'$LETSHEGO_ACCOUNT'",
            "fspId": "044",
            "fullName": "LETSHEGO TEST ACCOUNT",
            "accountCategory": "BUSINESS",
            "accountType": "BANK",
            "identity": {
                "type": "NIN",
                "value": ""
            }
        },
        "amount": {
            "amount": "50000.0",
            "currency": "TZS"
        },
        "endUserFee": {
            "amount": "130",
            "currency": "TZS"
        },
        "transactionType": {
            "scenario": "TRANSFER",
            "initiator": "PAYER",
            "initiatorType": "CONSUMER",
            "subScenario": null
        },
        "description": "Test transfer via bash script",
        "payerRef": "'$payer_ref'",
        "callbackUrl": "http://localhost:8005/api/letshego-callback"
    }'
    
    print_info "Transfer Reference: $payer_ref"
    make_request "POST" "/${CONSUMER_ID}/outwards/transfers" "$headers" "$data"
    
    if [ $? -eq 200 ]; then
        echo -e "${GREEN}💡 Save this reference for status checking: $payer_ref${NC}"
    fi
}

test_transaction_status() {
    print_test "Test 5: Transaction Status" "GET /${CONSUMER_ID}/outwards/status/${SAMPLE_REF}"
    
    local headers="-H 'apikey: $API_KEY'"
    make_request "GET" "/${CONSUMER_ID}/outwards/status/${SAMPLE_REF}" "$headers"
}

test_invalid_status() {
    print_test "Test 6: Invalid Transaction Status" "GET /${CONSUMER_ID}/outwards/status/INVALID-REF"
    
    local headers="-H 'apikey: $API_KEY'"
    make_request "GET" "/${CONSUMER_ID}/outwards/status/INVALID-REF" "$headers"
}

test_reversal() {
    print_test "Test 7: Transaction Reversal" "POST /${CONSUMER_ID}/outwards/transfers/reversal"
    
    local headers="-H 'Content-Type: application/json' -H 'apikey: $API_KEY'"
    
    local data='{
        "payerRef": "044-20220616000009",
        "reversalReason": "Test reversal via bash script"
    }'
    
    make_request "POST" "/${CONSUMER_ID}/outwards/transfers/reversal" "$headers" "$data"
}

test_invalid_api_key() {
    print_test "Test 8: Invalid API Key" "GET /${CONSUMER_ID}/outwards/institution"
    
    local headers="-H 'apikey: invalid-api-key-12345'"
    make_request "GET" "/${CONSUMER_ID}/outwards/institution" "$headers"
}

test_missing_headers() {
    print_test "Test 9: Missing Required Headers" "GET /${CONSUMER_ID}/outwards/accountenquiry/${TEST_ACCOUNT}"
    
    local headers="-H 'apikey: $API_KEY'"  # Missing fsp-destination header
    make_request "GET" "/${CONSUMER_ID}/outwards/accountenquiry/${TEST_ACCOUNT}" "$headers"
}

test_invalid_transfer() {
    print_test "Test 10: Invalid Transfer Data" "POST /${CONSUMER_ID}/outwards/transfers"
    
    local headers="-H 'Content-Type: application/json' -H 'apikey: $API_KEY' -H 'x-user-platform: web'"
    
    local data='{
        "payee": {
            "identifier": "'$TEST_ACCOUNT'"
        },
        "amount": {
            "amount": "50000.0"
        },
        "description": "Invalid transfer test"
    }'
    
    make_request "POST" "/${CONSUMER_ID}/outwards/transfers" "$headers" "$data"
}

run_all_tests() {
    print_header
    
    echo -e "${YELLOW}Running comprehensive test suite...${NC}\n"
    
    test_institutions
    sleep 1
    
    test_account_lookup
    sleep 1
    
    test_invalid_account_lookup
    sleep 1
    
    test_transaction_status
    sleep 1
    
    test_invalid_status
    sleep 1
    
    test_reversal
    sleep 1
    
    test_invalid_api_key
    sleep 1
    
    test_missing_headers
    sleep 1
    
    test_invalid_transfer
    sleep 1
    
    echo -e "\n${GREEN}🎉 All tests completed!${NC}"
    echo -e "${BLUE}To test actual transfers, run:${NC} $0 transfer"
}

run_laravel_tests() {
    print_header
    
    echo -e "${YELLOW}Running Laravel test suite...${NC}\n"
    
    cd "$(dirname "$0")/../.."
    
    echo -e "${BLUE}Running PHPUnit tests...${NC}"
    ./vendor/bin/phpunit tests/Feature/Services/LetshEgoEftServiceTest.php --verbose
    
    echo -e "\n${BLUE}Running Artisan command tests...${NC}"
    php artisan test:letshego-eft all
}

show_help() {
    echo -e "${BLUE}Letshego EFT Service Test Script${NC}"
    echo ""
    echo "Usage: $0 [command]"
    echo ""
    echo "Commands:"
    echo "  all           - Run all HTTP tests (default)"
    echo "  institutions  - Test institution list"
    echo "  lookup        - Test account lookup"
    echo "  transfer      - Test money transfer (interactive)"
    echo "  status        - Test transaction status"
    echo "  reversal      - Test transaction reversal"
    echo "  laravel       - Run Laravel/PHPUnit tests"
    echo "  help          - Show this help"
    echo ""
    echo "Examples:"
    echo "  $0              # Run all tests"
    echo "  $0 institutions # Test institution list only"
    echo "  $0 transfer     # Test transfer with confirmation"
    echo "  $0 laravel      # Run Laravel test suite"
}

# Main script
case "${1:-all}" in
    institutions)
        print_header
        test_institutions
        ;;
    lookup)
        print_header
        test_account_lookup
        ;;
    transfer)
        print_header
        echo -e "${YELLOW}⚠️  This will attempt a real transfer!${NC}"
        read -p "Are you sure you want to proceed? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            test_transfer
        else
            echo -e "${RED}Transfer cancelled.${NC}"
        fi
        ;;
    status)
        print_header
        test_transaction_status
        ;;
    reversal)
        print_header
        echo -e "${YELLOW}⚠️  This will attempt a real reversal!${NC}"
        read -p "Are you sure you want to proceed? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            test_reversal
        else
            echo -e "${RED}Reversal cancelled.${NC}"
        fi
        ;;
    laravel)
        run_laravel_tests
        ;;
    help)
        show_help
        ;;
    all|*)
        run_all_tests
        ;;
esac