#!/bin/bash

# Direct API Test for Payment Link Generation
# This script tests the payment link API directly using curl

echo "========================================="
echo "  Direct Payment Link API Test"
echo "========================================="
echo ""

# API Configuration
API_URL="http://172.240.241.188/api/payment-links/generate-universal"
API_KEY="sample_client_key_ABC123DEF456"
API_SECRET="sample_client_secret_XYZ789GHI012"

# Test data
CUSTOMER_REF="MEMBER2001"
CUSTOMER_NAME="Simon Mpembee"
CUSTOMER_PHONE="255742099713"
CUSTOMER_EMAIL="mpembeesimon@email.com"

# Generate timestamp for unique reference
TIMESTAMP=$(date +%Y%m%d%H%M%S)

# Create JSON payload
read -r -d '' PAYLOAD << EOF
{
  "description": "SACCOS Loan Services - Test $TIMESTAMP",
  "target": "individual",
  "customer_reference": "$CUSTOMER_REF",
  "customer_name": "$CUSTOMER_NAME",
  "customer_phone": "$CUSTOMER_PHONE",
  "customer_email": "$CUSTOMER_EMAIL",
  "expires_at": "$(date -u -v+30d '+%Y-%m-%dT%H:%M:%S.000000Z' 2>/dev/null || date -u -d '+30 days' '+%Y-%m-%dT%H:%M:%S.000000Z')",
  "items": [
    {
      "type": "service",
      "product_service_reference": "5001",
      "product_service_name": "LOAN_INSTALLMENT_01",
      "amount": 120000,
      "is_required": true,
      "allow_partial": true
    },
    {
      "type": "service",
      "product_service_reference": "5002",
      "product_service_name": "LOAN_INSTALLMENT_02",
      "amount": 115000,
      "is_required": false,
      "allow_partial": true
    },
    {
      "type": "service",
      "product_service_reference": "5003",
      "product_service_name": "LOAN_INSTALLMENT_03",
      "amount": 115000,
      "is_required": false,
      "allow_partial": true
    }
  ]
}
EOF

echo "📋 Request Details:"
echo "   URL: $API_URL"
echo "   API Key: $API_KEY"
echo "   API Secret: ${API_SECRET:0:20}..."
echo ""

echo "📦 Request Payload:"
echo "$PAYLOAD" | jq . 2>/dev/null || echo "$PAYLOAD"
echo ""

echo "🔧 Sending API Request..."
echo "----------------------------------------"

# Make the API request
RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-API-Key: $API_KEY" \
  -H "X-API-Secret: $API_SECRET" \
  --data "$PAYLOAD")

# Extract HTTP status code (last line)
HTTP_CODE=$(echo "$RESPONSE" | tail -n1)

# Extract response body (all except last line)
RESPONSE_BODY=$(echo "$RESPONSE" | sed '$d')

echo ""
echo "📊 Response:"
echo "----------------------------------------"
echo "HTTP Status Code: $HTTP_CODE"
echo ""

if [ "$HTTP_CODE" -eq 200 ] || [ "$HTTP_CODE" -eq 201 ]; then
    echo "✅ SUCCESS!"
    echo ""
    echo "Response Body:"
    echo "$RESPONSE_BODY" | jq . 2>/dev/null || echo "$RESPONSE_BODY"
    
    # Extract payment URL if available
    PAYMENT_URL=$(echo "$RESPONSE_BODY" | jq -r '.data.payment_url' 2>/dev/null)
    if [ ! -z "$PAYMENT_URL" ] && [ "$PAYMENT_URL" != "null" ]; then
        echo ""
        echo "🔗 Payment URL: $PAYMENT_URL"
    fi
    
    # Extract link ID if available
    LINK_ID=$(echo "$RESPONSE_BODY" | jq -r '.data.link_id' 2>/dev/null)
    if [ ! -z "$LINK_ID" ] && [ "$LINK_ID" != "null" ]; then
        echo "🆔 Link ID: $LINK_ID"
    fi
    
    # Extract total amount if available
    TOTAL_AMOUNT=$(echo "$RESPONSE_BODY" | jq -r '.data.total_amount' 2>/dev/null)
    if [ ! -z "$TOTAL_AMOUNT" ] && [ "$TOTAL_AMOUNT" != "null" ]; then
        echo "💰 Total Amount: $TOTAL_AMOUNT TZS"
    fi
else
    echo "❌ ERROR: Request failed with status code $HTTP_CODE"
    echo ""
    echo "Response Body:"
    echo "$RESPONSE_BODY" | jq . 2>/dev/null || echo "$RESPONSE_BODY"
fi

echo ""
echo "========================================="
echo ""

# Show curl command for debugging
echo "💡 Debug Info - Full curl command:"
echo "----------------------------------------"
cat << 'DEBUG'
curl -X POST "http://172.240.241.188/api/payment-links/generate-universal" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-API-Key: sample_client_key_ABC123DEF456" \
  -H "X-API-Secret: sample_client_secret_XYZ789GHI012" \
  --data '{
    "description": "SACCOS Loan Services",
    "target": "individual",
    "customer_reference": "MEMBER2001",
    "customer_name": "Simon Mpembee",
    "customer_phone": "255742099713",
    "customer_email": "mpembeesimon@email.com",
    "expires_at": "2025-09-30T23:59:59.000000Z",
    "items": [
      {
        "type": "service",
        "product_service_reference": "5001",
        "product_service_name": "LOAN_INSTALLMENT_01",
        "amount": 120000,
        "is_required": true,
        "allow_partial": true
      }
    ]
  }'
DEBUG

echo ""
echo "Test completed!"
exit 0