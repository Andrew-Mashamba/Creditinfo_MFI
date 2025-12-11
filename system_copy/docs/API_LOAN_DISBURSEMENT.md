# Loan Disbursement API Documentation

## Overview
The Loan Disbursement API allows external systems to automatically trigger loan disbursements for approved loans. It supports multiple payment methods and provides comprehensive validation and error handling.

## Base URL
```
https://your-domain.com/api/v1/loans
```

## Authentication
All API requests require authentication using:
1. **API Key**: Include in header as `X-API-Key`
2. **Bearer Token**: Include in header as `Authorization: Bearer {token}`

## Endpoints

### 1. Single Loan Disbursement

**Endpoint:** `POST /disburse`

**Description:** Disburse a single approved loan

#### Request Headers
```http
Content-Type: application/json
X-API-Key: your-api-key
Authorization: Bearer your-token
```

#### Request Body
```json
{
  "loan_id": "LN2024001234",
  "payment_method": "NBC_ACCOUNT",
  "payment_details": {
    "account_number": "01J1234567890",
    "account_holder_name": "John Doe"
  },
  "narration": "Loan disbursement for business expansion",
  "validate_only": false
}
```

#### Payment Methods

##### 1. CASH
```json
{
  "payment_method": "CASH",
  "payment_details": {
    "deposit_account": "CASH001",
    "cashier_id": "EMP001",
    "branch_code": "BR001"
  }
}
```

##### 2. NBC_ACCOUNT (Internal Transfer)
```json
{
  "payment_method": "NBC_ACCOUNT",
  "payment_details": {
    "account_number": "01J1234567890",
    "account_holder_name": "John Doe"
  }
}
```

##### 3. TIPS_MNO (Mobile Money)
```json
{
  "payment_method": "TIPS_MNO",
  "payment_details": {
    "phone_number": "255712345678",
    "mno_provider": "MPESA",
    "wallet_holder_name": "John Doe"
  }
}
```
Supported MNO Providers: `MPESA`, `TIGOPESA`, `AIRTELMONEY`, `HALOPESA`

##### 4. TIPS_BANK (Bank Transfer)
```json
{
  "payment_method": "TIPS_BANK",
  "payment_details": {
    "bank_code": "CRDB",
    "bank_account": "0150123456789",
    "bank_account_holder_name": "John Doe",
    "swift_code": "CORUTZTZ"
  }
}
```

#### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Loan disbursed successfully",
  "data": {
    "transaction_id": "API_DISB_65432abcdef",
    "loan_id": "LN2024001234",
    "status": "DISBURSED",
    "disbursement_details": {
      "total_loan_amount": {
        "value": 5000000.00,
        "formatted": "5,000,000.00",
        "display": "TZS 5,000,000.00"
      },
      "disbursed_amount": {
        "value": 4750000.00,
        "formatted": "4,750,000.00",
        "display": "TZS 4,750,000.00"
      },
      "currency": "TZS"
    },
    "deductions": {
      "total": {
        "value": 250000.00,
        "formatted": "250,000.00",
        "display": "TZS 250,000.00"
      },
      "charges": {
        "value": 100000.00,
        "formatted": "100,000.00",
        "display": "TZS 100,000.00"
      },
      "insurance": {
        "value": 50000.00,
        "formatted": "50,000.00",
        "display": "TZS 50,000.00"
      },
      "first_interest": {
        "value": 100000.00,
        "formatted": "100,000.00",
        "display": "TZS 100,000.00"
      },
      "top_up_settlement": {
        "value": 0.00,
        "formatted": "0.00",
        "display": "TZS 0.00"
      },
      "top_up_penalty": {
        "value": 0.00,
        "formatted": "0.00",
        "display": "TZS 0.00"
      },
      "breakdown": [
        {
          "type": "charge",
          "name": "Processing Fee",
          "amount": {
            "value": 100000.00,
            "formatted": "100,000.00",
            "display": "TZS 100,000.00"
          }
        },
        {
          "type": "insurance",
          "name": "Loan Protection Insurance",
          "amount": {
            "value": 50000.00,
            "formatted": "50,000.00",
            "display": "TZS 50,000.00"
          }
        }
      ]
    },
    "payment_info": {
      "method": "NBC_ACCOUNT",
      "reference": "NBC_65432abcdef",
      "status": "COMPLETED"
    },
    "control_numbers": [
      {
        "type": "REPAYMENT",
        "number": "REP16897654321234",
        "description": "Monthly Loan Repayment",
        "valid_until": "2024-02-15"
      }
    ],
    "loan_account": "LN01J1234567890",
    "disbursement_date": "2024-01-15T10:30:00Z",
    "next_payment_date": "2024-02-01",
    "repayment_info": {
      "frequency": "MONTHLY",
      "installment_amount": {
        "value": 458333.33,
        "formatted": "458,333.33",
        "display": "TZS 458,333.33"
      },
      "total_installments": 12,
      "first_payment_date": "2024-02-01"
    }
  },
  "meta": {
    "execution_time_ms": 1250.5,
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

#### Validation Only Response (200 OK)
When `validate_only: true`:
```json
{
  "success": true,
  "message": "Loan validated successfully. Ready for disbursement.",
  "data": {
    "transaction_id": "API_DISB_65432abcdef",
    "loan_id": "LN2024001234",
    "validation_status": "PASSED",
    "loan_details": {
      "client_number": "CL001234",
      "loan_amount": 5000000.00,
      "loan_type": "NEW",
      "status": "APPROVED"
    }
  }
}
```

#### Error Responses

##### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "payment_details.phone_number": [
      "Phone number is required for mobile money transfers"
    ]
  },
  "meta": {
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

##### Loan Not Found (404)
```json
{
  "success": false,
  "message": "Loan not found: LN2024001234",
  "error": {
    "code": "LOAN_NOT_FOUND",
    "transaction_id": "API_DISB_65432abcdef"
  },
  "meta": {
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

##### Already Disbursed (422)
```json
{
  "success": false,
  "message": "Loan LN2024001234 is already disbursed or active. Current status: DISBURSED",
  "error": {
    "code": "LOAN_ALREADY_DISBURSED",
    "transaction_id": "API_DISB_65432abcdef"
  },
  "meta": {
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

##### Insufficient Funds (402)
```json
{
  "success": false,
  "message": "Insufficient funds in disbursement account",
  "error": {
    "code": "INSUFFICIENT_FUNDS",
    "transaction_id": "API_DISB_65432abcdef"
  },
  "meta": {
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

### 2. Bulk Loan Disbursement

**Endpoint:** `POST /bulk-disburse`

**Description:** Disburse multiple loans in a single request (max 100 loans)

#### Request Body
```json
{
  "disbursements": [
    {
      "loan_id": "LN2024001234",
      "payment_method": "NBC_ACCOUNT",
      "payment_details": {
        "account_number": "01J1234567890"
      },
      "narration": "Business loan disbursement"
    },
    {
      "loan_id": "LN2024001235",
      "payment_method": "TIPS_MNO",
      "payment_details": {
        "phone_number": "255712345678",
        "mno_provider": "MPESA"
      },
      "narration": "Personal loan disbursement"
    }
  ]
}
```

#### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Processed 2 successful and 0 failed disbursements",
  "data": {
    "summary": {
      "total": 2,
      "successful": 2,
      "failed": 0
    },
    "results": [
      {
        "index": 0,
        "loan_id": "LN2024001234",
        "success": true,
        "data": {
          // Full disbursement details as in single disbursement
        }
      },
      {
        "index": 1,
        "loan_id": "LN2024001235",
        "success": true,
        "data": {
          // Full disbursement details as in single disbursement
        }
      }
    ]
  }
}
```

### 3. Get Disbursement Status

**Endpoint:** `GET /disbursement/{transactionId}/status`

**Description:** Check the status of a disbursement transaction

#### Success Response (200 OK)
```json
{
  "success": true,
  "data": {
    "transaction_id": "API_DISB_65432abcdef",
    "loan_id": "LN2024001234",
    "status": "COMPLETED",
    "amount": 4750000.00,
    "payment_method": "NBC_ACCOUNT",
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:30:15Z"
  }
}
```

## Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 400 | Bad Request |
| 401 | Unauthorized |
| 402 | Payment Required (Insufficient Funds) |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Unprocessable Entity (Validation Error) |
| 429 | Too Many Requests |
| 500 | Internal Server Error |

## Rate Limiting
- 100 requests per minute per API key
- 1000 requests per hour per API key
- 10000 requests per day per API key

## Webhooks
Configure webhook URLs to receive real-time updates on disbursement status:

```json
POST https://your-webhook-url.com/disbursement-status
{
  "event": "disbursement.completed",
  "transaction_id": "API_DISB_65432abcdef",
  "loan_id": "LN2024001234",
  "status": "COMPLETED",
  "timestamp": "2024-01-15T10:30:15Z"
}
```

## Testing
Use the `validate_only: true` parameter to test your integration without actually disbursing loans.

## Support
For API support, contact:
- Email: api-support@nbc-sacco.com
- Phone: +255 123 456 789
- Documentation: https://api.nbc-sacco.com/docs