# Transaction Processing System (TPS) Documentation

## Overview

The Transaction Processing System is a comprehensive, enterprise-grade solution that handles multiple payment methods with robust error handling, retry logic, and audit trails. It's designed to be reliable, scalable, and maintainable.

## Architecture

### Core Components

1. **TransactionProcessingService** - Main orchestrator
2. **ProcessTransactionRetry** - Job for handling retries
3. **ProcessFailedTransactions** - Command for managing failed transactions
4. **TransactionTestController** - Test endpoints for validation

### Database Tables

- `transactions` - Main transaction records
- `transaction_audit_logs` - Audit trail
- `transaction_retry_logs` - Retry history
- `transaction_reconciliations` - Reconciliation records

## Features Implemented

### ✅ Reliability
- **Persistent Storage**: All transactions stored in robust `transactions` table
- **Retry Mechanism**: Exponential backoff with up to 3 attempts
- **Error Handling**: Comprehensive try-catch with detailed logging
- **Audit Trails**: Complete audit logging for all operations

### ✅ Transactionality
- **ACID Operations**: Database transactions with rollback on failure
- **Idempotency**: Correlation ID-based duplicate detection
- **Isolation**: Proper transaction isolation across services

### ✅ Fault Tolerance
- **Circuit Breaker**: Prevents cascading failures
- **Dead Letter Queue**: Handles permanently failed transactions
- **Retry Logic**: Exponential backoff with jitter

### ✅ Messaging
- **Queue Support**: Laravel queues for async processing
- **Message Transformation**: Proper payload handling
- **Routing Logic**: Service-based routing

### ✅ Security
- **Input Validation**: Comprehensive validation
- **Data Masking**: Sensitive data masked in logs
- **Access Control**: User-based tracking

### ✅ Observability
- **Centralized Logging**: Structured logging with correlation IDs
- **Monitoring**: Processing time tracking
- **Tracing**: Correlation ID throughout the flow

## Usage Examples

### 1. Cash Transaction
```php
$tps = new TransactionProcessingService(
    'cash',                    // serviceType
    'loan',                    // saccosService
    100000,                    // amount
    '1234567890',              // sourceAccount
    '0987654321',              // destinationAccount
    'M001',                    // memberId
    ['narration' => 'Loan disbursement']
);
$result = $tps->process();
```

### 2. TIPS Mobile Money
```php
$tps = new TransactionProcessingService(
    'tips_mno',                // serviceType
    'loan',                    // saccosService
    100000,                    // amount
    '1234567890',              // sourceAccount
    '0987654321',              // destinationAccount
    'M001',                    // memberId
    [
        'phone_number' => '255712345678',
        'wallet_provider' => 'MPESA',
        'narration' => 'Loan disbursement via M-Pesa',
        'payer_name' => 'John Doe'
    ]
);
$result = $tps->process();
```

### 3. TIPS Bank Transfer
```php
$tps = new TransactionProcessingService(
    'tips_bank',               // serviceType
    'loan',                    // saccosService
    100000,                    // amount
    '1234567890',              // sourceAccount
    '0987654321',              // destinationAccount
    'M001',                    // memberId
    [
        'bank_code' => '015',  // NBC bank code
        'phone_number' => '255712345678',
        'narration' => 'Loan disbursement to bank account',
        'product_code' => 'FTLC'
    ]
);
$result = $tps->process();
```

### 4. Internal Transfer
```php
$tps = new TransactionProcessingService(
    'internal_transfer',       // serviceType
    'loan',                    // saccosService
    100000,                    // amount
    '1234567890',              // sourceAccount
    '0987654321',              // destinationAccount
    'M001',                    // memberId
    [
        'narration' => 'Internal transfer loan disbursement',
        'payer_name' => 'John Doe'
    ]
);
$result = $tps->process();
```

## Response Format

```json
{
    "success": true,
    "referenceNumber": "123456789012",
    "externalReferenceNumber": "CBS123456789",
    "correlationId": "uuid-string",
    "processingTimeMs": 1500
}
```

## Error Handling

### Retry Logic
- **Max Retries**: 3 attempts
- **Backoff Strategy**: Exponential (1s, 2s, 4s) + jitter
- **Circuit Breaker**: Opens after 5 failures in 5 minutes

### Failure Scenarios
1. **External Service Down**: Queued for retry
2. **Validation Failure**: Immediate failure, no retry
3. **Network Timeout**: Retried with backoff
4. **Permanent Failure**: Moved to Dead Letter Queue

## Commands

### Process Failed Transactions
```bash
# Retry failed transactions
php artisan transactions:process-failed --retry

# Move to Dead Letter Queue
php artisan transactions:process-failed --dlq

# Process specific status
php artisan transactions:process-failed --status=retry_pending --limit=100
```

## Queue Configuration

The system uses dedicated queues for transaction processing:

```php
'transaction-retries' => [
    'driver' => 'database',
    'table' => 'jobs',
    'queue' => 'transaction-retries',
    'retry_after' => 300, // 5 minutes
    'after_commit' => false,
],
```

## Monitoring and Logging

### Log Structure
All logs include:
- Correlation ID
- Transaction reference
- Processing time
- Error details (if applicable)

### Key Metrics
- Transaction success rate
- Processing time
- Retry count distribution
- External service response times

## Integration Points

### External Services
1. **NbcPaymentService** - TIPS MNO and Bank transfers
2. **InternalFundTransferService** - NBC internal transfers
3. **TransactionPostingService** - Ledger posting

### Database Integration
- Uses existing `transactions` table
- Integrates with `general_ledger` for accounting
- Leverages existing `AccountsModel` for account management

## Security Considerations

### Data Protection
- Sensitive data masked in logs
- Account numbers partially hidden
- Phone numbers formatted securely

### Access Control
- User tracking for all operations
- IP address logging
- Session tracking

## Testing

### Test Endpoints
- `/test/cash-transaction`
- `/test/tips-mno-transaction`
- `/test/tips-bank-transaction`
- `/test/internal-transfer-transaction`
- `/transaction/status/{referenceNumber}`

### Test Scenarios
1. **Happy Path**: Successful transaction processing
2. **Retry Path**: External service failure with retry
3. **Failure Path**: Permanent failure to DLQ
4. **Idempotency**: Duplicate request handling

## Deployment Considerations

### Environment Variables
Ensure these are configured:
- `NBC_PAYMENTS_BASE_URL`
- `NBC_PAYMENTS_API_KEY`
- `NBC_INTERNAL_FUND_TRANSFER_BASE_URL`
- `NBC_INTERNAL_FUND_TRANSFER_API_KEY`

### Queue Workers
Start queue workers for transaction processing:
```bash
php artisan queue:work --queue=transaction-retries
```

### Monitoring
Set up monitoring for:
- Queue job failures
- Transaction processing times
- External service availability
- Error rates

## Future Enhancements

### Planned Features
1. **Saga Pattern**: For complex multi-step transactions
2. **Event Sourcing**: For transaction history replay
3. **API Contracts**: OpenAPI/Swagger documentation
4. **Advanced Monitoring**: Prometheus metrics
5. **Notification System**: Email/SMS alerts for failures

### Scalability Improvements
1. **Horizontal Scaling**: Multiple queue workers
2. **Database Sharding**: For high-volume scenarios
3. **Caching**: Redis for frequently accessed data
4. **Load Balancing**: For external service calls

## Support and Maintenance

### Troubleshooting
1. Check transaction status via correlation ID
2. Review audit logs for detailed flow
3. Monitor queue job failures
4. Verify external service connectivity

### Maintenance Tasks
1. **Daily**: Monitor failed transaction queue
2. **Weekly**: Review DLQ for manual intervention
3. **Monthly**: Analyze performance metrics
4. **Quarterly**: Update external service configurations

---

**Version**: 1.0  
**Last Updated**: December 2024  
**Author**: System Administrator 