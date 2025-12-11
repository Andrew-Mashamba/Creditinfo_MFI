# Payments Module Architecture Documentation

## Overview
The SACCOS Payments module is a comprehensive payment processing system that integrates multiple payment gateways and services for the NBC SACCOS Management System. It handles various payment types including bills, transfers, utility payments, and loan repayments.

## Module Structure

```
/app/Http/Livewire/Payments/
├── Payments.php                 # Main payment component (orchestrator)
├── GepgPayment.php              # GEPG (Government Electronic Payment Gateway)
├── LukuPayment.php              # LUKU (Electricity prepaid tokens)
├── ManageOrder.php              # Order management
├── NewOrder.php                 # New payment orders
├── Approvals.php                # Payment approvals workflow
├── MoneyTransfer.php            # Fund transfers
└── Supporting Components
    ├── ApprovalsTable.php
    ├── ConfirmModal.php
    ├── EditData.php
    ├── ManageOrdersTable.php
    └── NewOrdersTable.php
```

## Payment Types Supported

### 1. **Bank-to-Bank Transfers**
- Direct account transfers between banks
- Uses NBC internal fund transfer service
- Supports NMB, CRDB, DTB banks

### 2. **Bank-to-Wallet Transfers**
- Mobile money transfers
- Supports M-Pesa, Airtel Money, Azam Pesa
- Phone number validation

### 3. **GEPG Payments**
- Government bill payments
- Control number verification
- Prepaid and postpaid options

### 4. **LUKU Payments**
- Electricity token purchases
- Meter number lookup
- Token generation

### 5. **Bill Payments**
- Generic bill payment system
- Service provider integration
- Bill inquiry and verification

## Core Components

### Main Payment Component (`Payments.php`)

**Key Features:**
- Central orchestrator for all payment types
- Multi-step payment flow
- Real-time validation

**Properties:**
```php
// Payment type selection
$selectedPaymentType = 'money_transfer' | 'gepg' | 'luku' | 'bills'

// Transfer configuration
$transferType = 'bank' | 'wallet'
$beneficiaryAccount    // For bank transfers
$phoneNumber          // For wallet transfers
$amount
$remarks

// State management
$currentPhase = 'form' | 'verify' | 'complete'
$isProcessing = false
$lookupData = []
```

**Payment Flow:**
1. **Form Phase** - User enters payment details
2. **Verify Phase** - Beneficiary/bill verification
3. **Complete Phase** - Payment processing and confirmation

### GEPG Payment Component

**Features:**
- Control number verification
- Bill details retrieval
- Prepaid/Postpaid payment processing

**Key Methods:**
```php
verifyControlNumber()     // Verify GEPG control number
prepareBillPayment()       // Prepare payment data
processPrepaidPayment()    // Process prepaid bills
processPostpaidPayment()   // Process postpaid bills
```

### LUKU Payment Component

**Features:**
- Meter number lookup
- Customer verification
- Token purchase processing

**Key Methods:**
```php
lookupMeter()             // Verify meter number
validateCustomer()        // Validate customer details
purchaseToken()          // Process token purchase
```

## Services Layer

### Payment Services (`/app/Services/NbcPayments/`)

```
NbcPayments/
├── NbcPaymentService.php           # Core payment processing
├── NbcLookupService.php           # Account/wallet lookups
├── GepgGatewayService.php         # GEPG integration
├── LukuService.php                # LUKU integration
├── FspDetailsService.php          # Financial service providers
├── InternalFundTransferService.php # Internal transfers
└── PaymentProcessorService.php    # Generic processor
```

### Service Integration Flow

```
User Input → Livewire Component → Service Layer → External Gateway → Response
     ↓              ↓                    ↓               ↓              ↓
 Validation    State Mgmt          API Calls      Processing      Update UI
```

## Database Schema

### Core Tables

#### `payments` table
```sql
- id
- bill_id
- payment_ref
- transaction_reference
- control_number
- amount
- currency
- payment_channel
- payer_name, payer_msisdn, payer_email
- status (PENDING|COMPLETED|FAILED)
- raw_payload (JSON)
- response_data (JSON)
- timestamps
```

#### `payment_notifications` table
```sql
- id
- payment_id
- notification_type
- channel (SMS|EMAIL|PUSH)
- recipient
- status
- sent_at
- timestamps
```

#### `bills` table
```sql
- id
- service_provider
- bill_reference
- control_number
- amount
- currency
- status
- timestamps
```

## Payment Processing Flow

### 1. Initialization
```php
// Component mount
public function mount() {
    $this->fetchFsp();        // Load financial service providers
    $this->fetchBillers();    // Load bill service providers
}
```

### 2. Beneficiary/Bill Verification
```php
// For transfers
public function verifyBeneficiary() {
    $lookupResponse = $lookupService->bankToBankLookup(...);
    $this->beneficiaryName = $lookupResponse['fullName'];
    $this->currentPhase = 'verify';
}

// For bills
public function inquireBill() {
    $this->billDetails = $service->inquireDetailedBill($payload);
}
```

### 3. Payment Confirmation
```php
public function confirmTransfer() {
    $paymentResponse = $paymentService->processBankToBankTransfer(...);
    if ($paymentResponse['success']) {
        $this->currentPhase = 'complete';
        $this->engineRef = $paymentResponse['engineRef'];
    }
}
```

### 4. Status Tracking
```php
public function checkPaymentStatus($channelRef) {
    $this->paymentStatus = $service->checkPaymentStatus([
        'channelRef' => $channelRef
    ]);
}
```

## API Integration Points

### External Gateways

1. **NBC Gateway** (Base URL: `https://nbc-gateway-uat.intra.nbc.co.tz`)
   - GEPG integration
   - LUKU integration
   - Bill payments

2. **Internal Transfer API** (`http://cbpuat.intra.nbc.co.tz:6666`)
   - Bank-to-bank transfers
   - Account lookups

3. **Payment Link API** (`http://172.240.241.188`)
   - Payment URL generation
   - Status checking

### Callback Endpoints
```php
// Payment callbacks
Route::post('/callback', 'handlePaymentCallback')
Route::post('/luku/callback', 'handleLukuCallback')
Route::post('/gepg/callback', 'handleGepgCallback')
```

## Security Features

### Authentication & Authorization
- User authentication required
- Role-based access control
- Transaction limits enforcement

### Data Protection
- Encrypted sensitive data
- API key authentication
- SSL/TLS for external calls

### Audit Trail
- All transactions logged
- Status change tracking
- User action logging

## Error Handling

### Common Error Scenarios

1. **Network Failures**
```php
try {
    $response = $service->processPayment();
} catch (ConnectionException $e) {
    Log::error('Network error', ['error' => $e->getMessage()]);
    $this->errorMessage = 'Connection failed. Please try again.';
}
```

2. **Validation Errors**
```php
$this->validate([
    'amount' => 'required|numeric|min:1000',
    'beneficiaryAccount' => 'required_if:transferType,bank'
]);
```

3. **Gateway Errors**
```php
if (!$paymentResponse['success']) {
    $this->errorMessage = $paymentResponse['message'];
    $this->currentPhase = 'form'; // Return to form
}
```

## Configuration

### Environment Variables
```env
# NBC Gateway
NBC_GATEWAY_BASE_URL=https://nbc-gateway-uat.intra.nbc.co.tz
NBC_GATEWAY_CHANNEL_ID=SACCOSNBC
NBC_GATEWAY_API_TOKEN=...

# GEPG
GEPG_BASE_URL=...
GEPG_CHANNEL_ID=SACCOSNBC
GEPG_AUTH_TOKEN=...

# LUKU
LUKU_GATEWAY_BASE_URL=...
LUKU_GATEWAY_CHANNEL_ID=SACCOSNBC

# Internal Transfer
NBC_INTERNAL_FUND_TRANSFER_BASE_URL=...
NBC_INTERNAL_FUND_TRANSFER_API_KEY=...
```

## Available Banks & Wallets

### Banks
- `NMIBTZTZ` - NMB Bank
- `CORUTZTZ` - CRDB Bank  
- `DTKETZTZ` - DTB Bank

### Mobile Wallets
- `VMCASHIN` - M-Pesa
- `AMCASHIN` - Airtel Money
- `APCASHIN` - Azam Pesa

## Testing

### Test Accounts
```php
// Test data
$testPayment = [
    'debitAccount' => '28012040011',
    'creditAccount' => '28012040022',
    'amount' => 10000,
    'currency' => 'TZS'
];
```

### Validation Rules
```php
protected $rules = [
    'transferType' => 'required|in:bank,wallet',
    'amount' => 'required|numeric|min:1000',
    'beneficiaryAccount' => 'required_if:transferType,bank',
    'phoneNumber' => 'required_if:transferType,wallet',
    'remarks' => 'required|string|max:50'
];
```

## Monitoring & Logs

### Log Channels
```php
Log::channel('payments')->info('Payment initiated', [...]);
Log::channel('luku')->info('LUKU payment processed', [...]);
Log::channel('gepg')->info('GEPG verification', [...]);
```

### Key Metrics
- Transaction success rate
- Average processing time
- Gateway response times
- Failed transaction reasons

## Common Issues & Solutions

### 1. Timeout Errors
- Increase timeout in service configuration
- Implement retry logic
- Use async processing for large batches

### 2. Verification Failures
- Check account number format
- Verify bank codes
- Ensure sufficient balance

### 3. Gateway Unavailable
- Implement fallback mechanisms
- Queue for retry
- Notify administrators

## Future Enhancements

1. **Bulk Payments** - Process multiple payments in batch
2. **Recurring Payments** - Automated scheduled payments
3. **Payment Templates** - Save frequent payment details
4. **Multi-currency** - Support for USD, EUR transactions
5. **QR Code Payments** - Mobile QR scanning
6. **Payment Analytics** - Dashboard and reporting

---

**Module Version**: 1.0  
**Last Updated**: September 2025  
**Maintained By**: SACCOS Development Team