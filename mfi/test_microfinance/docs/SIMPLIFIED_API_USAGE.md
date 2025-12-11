# Simplified Loan API - Quick Start Guide

## Overview
The simplified loan API automatically creates and disburses loans with just **two parameters**: `client_number` and `amount`.

## Endpoint
```
POST https://your-domain.com/api/v1/loans/auto-disburse
```

## Authentication
```http
X-API-Key: your-api-key
Authorization: Bearer your-token
```

## Request Format

### Simple Request (Only 2 fields required!)
```json
{
  "client_number": "CL001234",
  "amount": 5000000
}
```

That's it! The API handles everything else automatically.

## What Happens Automatically

1. **Client Validation**: Verifies client exists and has NBC account
2. **Loan Creation**: 
   - Loan ID: `AUTO{YEAR}000001` format
   - Product: Default product (id=1) 
   - Type: NEW loan
   - Tenure: Maximum term from product
   - Interest: From product settings
   - Status: Auto-approved
3. **Deductions Applied**:
   - Processing charges
   - Insurance premiums  
   - First month interest
4. **Disbursement**:
   - Transfers to client's NBC account
   - Creates loan account
   - Posts to general ledger
5. **Repayment Schedule**: Monthly installments generated
6. **Notifications**: SMS & Email sent to client
7. **Control Numbers**: Generated for payment collection

## Example Usage

### Using cURL
```bash
curl -X POST https://your-domain.com/api/v1/loans/auto-disburse \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-api-key" \
  -H "Authorization: Bearer your-token" \
  -d '{
    "client_number": "CL001234",
    "amount": 5000000
  }'
```

### Using PHP
```php
<?php
$data = [
    "client_number" => "CL001234",
    "amount" => 5000000
];

$ch = curl_init("https://your-domain.com/api/v1/loans/auto-disburse");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-API-Key: your-api-key",
    "Authorization: Bearer your-token"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$result = json_decode($response, true);

if ($result['success']) {
    echo "Loan ID: " . $result['data']['loan_id'];
    echo "Net Disbursed: " . $result['data']['disbursement']['net_disbursed'];
}
```

### Using Python
```python
import requests

url = "https://your-domain.com/api/v1/loans/auto-disburse"
headers = {
    "Content-Type": "application/json",
    "X-API-Key": "your-api-key",
    "Authorization": "Bearer your-token"
}
data = {
    "client_number": "CL001234",
    "amount": 5000000
}

response = requests.post(url, json=data, headers=headers)
result = response.json()

if result['success']:
    print(f"Loan ID: {result['data']['loan_id']}")
    print(f"Net Disbursed: {result['data']['disbursement']['net_disbursed']}")
```

### Using JavaScript/Node.js
```javascript
const axios = require('axios');

const data = {
  client_number: "CL001234",
  amount: 5000000
};

axios.post('https://your-domain.com/api/v1/loans/auto-disburse', data, {
  headers: {
    'Content-Type': 'application/json',
    'X-API-Key': 'your-api-key',
    'Authorization': 'Bearer your-token'
  }
})
.then(response => {
  if (response.data.success) {
    console.log('Loan ID:', response.data.data.loan_id);
    console.log('Net Disbursed:', response.data.data.disbursement.net_disbursed);
  }
})
.catch(error => {
  console.error('Error:', error.response.data.message);
});
```

## Success Response

```json
{
  "success": true,
  "message": "Loan created and disbursed successfully",
  "data": {
    "transaction_id": "AUTO_DISB_65432abcdef",
    "loan_id": "AUTO2024000001",
    "loan_account": "LN01J1234567890",
    "client": {
      "number": "CL001234",
      "name": "John Doe",
      "nbc_account": "01J1234567890"
    },
    "loan_details": {
      "amount": 5000000,
      "tenure_months": 12,
      "interest_rate": "18%",
      "monthly_installment": 458333.33,
      "total_payable": 5500000.00
    },
    "disbursement": {
      "gross_amount": 5000000,
      "deductions": {
        "total": 250000,
        "charges": 100000,
        "insurance": 50000,
        "first_interest": 100000,
        "breakdown": [
          {
            "type": "charge",
            "name": "Processing Fee",
            "amount": 100000
          },
          {
            "type": "insurance",
            "name": "Loan Protection",
            "amount": 50000
          },
          {
            "type": "first_interest",
            "name": "First Month Interest",
            "amount": 100000
          }
        ]
      },
      "net_disbursed": 4750000,
      "payment_method": "NBC_ACCOUNT",
      "payment_reference": "NBC_AUTO_65432abcdef",
      "disbursement_date": "2024-01-15T10:30:00Z"
    },
    "repayment": {
      "first_payment_date": "2024-02-01",
      "control_numbers": [
        {
          "type": "REPAYMENT",
          "number": "AUTO16897654321234",
          "description": "Monthly Loan Repayment",
          "valid_until": "2024-02-15"
        }
      ],
      "frequency": "MONTHLY"
    }
  },
  "meta": {
    "execution_time_ms": 1250.5,
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

## Error Responses

### Client Not Found
```json
{
  "success": false,
  "message": "Client not found: CL001234",
  "error": {
    "code": "CLIENT_NOT_FOUND"
  }
}
```

### Invalid Amount
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "amount": ["Minimum loan amount is TZS 100,000"]
  }
}
```

### No NBC Account
```json
{
  "success": false,
  "message": "Client CL001234 does not have an NBC account number",
  "error": {
    "code": "NO_NBC_ACCOUNT"
  }
}
```

## Validation Rules

| Field | Rule | Description |
|-------|------|-------------|
| client_number | Required, Must exist | Client must exist in system |
| amount | Required, Min: 100,000, Max: 100,000,000 | Between 100k and 100M TZS |

## Default Settings Used

| Setting | Value | Source |
|---------|-------|--------|
| Loan Product | ID = 1 | loan_sub_products table |
| Loan Type | NEW | Always new loan |
| Tenure | max_term | From product settings |
| Interest Rate | interest_value | From product settings |
| Payment Method | NBC_ACCOUNT | Internal transfer |
| Approval Status | APPROVED | Auto-approved |
| Amortization | From product | equal_installments or equal_principal |

## What You Need Before Using

1. **API Credentials**:
   - API Key
   - Bearer Token
   - IP Whitelisted

2. **System Setup**:
   - Default loan product (id=1) must exist and be active
   - Client must exist with valid NBC account
   - Product must have charges/insurance configured

3. **Minimum Data**:
   - Valid client_number
   - Amount between 100k and 100M TZS

## Testing

Test with a small amount first:
```bash
curl -X POST https://your-domain.com/api/v1/loans/auto-disburse \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-api-key" \
  -H "Authorization: Bearer your-token" \
  -d '{
    "client_number": "CL001234",
    "amount": 100000
  }'
```

## Support

For issues or questions:
- Email: api-support@nbc-sacco.com
- Documentation: Full API docs at `/docs/API_LOAN_DISBURSEMENT.md`

## Summary

**Just send 2 fields → Get a fully disbursed loan!**

The API handles:
- ✅ Loan creation
- ✅ Auto-approval
- ✅ Deduction calculations
- ✅ NBC account transfer
- ✅ Repayment schedule
- ✅ Control numbers
- ✅ SMS/Email notifications
- ✅ General ledger posting

Simple, fast, and automatic!