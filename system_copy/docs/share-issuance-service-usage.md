# Share Issuance Service Usage Guide

## Overview

The `ShareIssuanceService` is a dedicated service that handles all share issuance operations. It can be used from any component or controller to issue shares to members.

## Basic Usage

### 1. Inject the Service

```php
use App\Services\ShareIssuanceService;

class YourComponent extends Component
{
    protected $shareIssuanceService;

    public function __construct(ShareIssuanceService $shareIssuanceService)
    {
        $this->shareIssuanceService = $shareIssuanceService;
    }
}
```

### 2. Issue Shares

```php
public function issueShares()
{
    $data = [
        'product_id' => $this->product_id,
        'client_number' => $this->client_number,
        'number_of_shares' => $this->number_of_shares,
        'price_per_share' => $this->price_per_share,
        'total_value' => $this->total_value,
        'linked_savings_account' => $this->linked_savings_account,
        'share_account' => $this->share_account,
        'reference_number' => '1000'
    ];

    $result = $this->shareIssuanceService->issueShares($data);

    if ($result['success']) {
        session()->flash('success', $result['message']);
        // Handle success
    } else {
        if (isset($result['errors'])) {
            session()->flash('validation_errors', $result['errors']);
        } else {
            session()->flash('error', $result['message']);
        }
    }
}
```

### 3. Process Approved Issuance

```php
public function processApprovedIssuance($approval)
{
    $result = $this->shareIssuanceService->processApprovedIssuance($approval);
    
    if (!$result['success']) {
        throw new \Exception($result['message']);
    }
    
    // Handle success
}
```

## Alternative Usage (Using Service Container)

If you prefer not to inject the service, you can use the service container:

```php
public function issueShares()
{
    $shareIssuanceService = app(ShareIssuanceService::class);
    
    $data = [
        'product_id' => $this->product_id,
        'client_number' => $this->client_number,
        'number_of_shares' => $this->number_of_shares,
        'price_per_share' => $this->price_per_share,
        'total_value' => $this->total_value,
        'linked_savings_account' => $this->linked_savings_account,
        'share_account' => $this->share_account,
        'reference_number' => '1000'
    ];

    $result = $shareIssuanceService->issueShares($data);
    
    // Handle result...
}
```

## Data Requirements

The service expects the following data structure:

```php
$data = [
    'product_id' => 1,                    // Required: ID of the share product
    'client_number' => '12345',           // Required: 5-digit client number
    'number_of_shares' => 100,            // Required: Number of shares to issue
    'price_per_share' => 10.00,           // Required: Price per share
    'total_value' => 1000.00,             // Required: Total value (shares * price)
    'linked_savings_account' => 'ACC001', // Required: Savings account number
    'share_account' => 'SHARE001',        // Required: Share account number
    'reference_number' => '1000'          // Optional: Reference number
];
```

## Validation

The service automatically validates:

- All required fields are present
- Client number is exactly 5 digits
- Product exists in the database
- Savings and share accounts exist
- Member is active
- Sufficient balance in savings account
- Share limits (maximum per member, available shares, minimum required)

## Error Handling

The service returns a structured response:

```php
// Success
[
    'success' => true,
    'message' => 'Share issuance request submitted successfully.',
    'issuance_id' => 123,
    'reference_number' => 'SH202412011234'
]

// Validation Error
[
    'success' => false,
    'message' => 'Validation failed',
    'errors' => [
        'client_number' => ['Client number must be exactly 5 digits.']
    ]
]

// General Error
[
    'success' => false,
    'message' => 'An error occurred while processing your request: Member not found'
]
```

## Database Operations

The service handles:

1. **Share Issuance Creation**: Creates a record in `issued_shares` table
2. **Approval Request**: Creates an approval record in `approvals` table
3. **Share Register Updates**: Updates or creates records in `share_registers` table
4. **Product Updates**: Updates available shares in `sub_products` table
5. **Transaction Posting**: Posts financial transactions using `TransactionPostingService`

## Logging

The service includes comprehensive logging for:

- Process start/completion
- Validation failures
- Database operations
- Error conditions
- Approval processing

## Example Implementation in Livewire Component

```php
<?php

namespace App\Http\Livewire\Shares;

use Livewire\Component;
use App\Services\ShareIssuanceService;

class ShareIssuanceForm extends Component
{
    public $product_id;
    public $client_number;
    public $number_of_shares;
    public $price_per_share;
    public $linked_savings_account;
    public $share_account;

    protected $shareIssuanceService;

    public function boot(ShareIssuanceService $shareIssuanceService)
    {
        $this->shareIssuanceService = $shareIssuanceService;
    }

    public function issueShares()
    {
        $this->validate([
            'product_id' => 'required',
            'client_number' => 'required|size:5',
            'number_of_shares' => 'required|numeric|min:1',
            'price_per_share' => 'required|numeric|min:0',
            'linked_savings_account' => 'required',
            'share_account' => 'required'
        ]);

        $data = [
            'product_id' => $this->product_id,
            'client_number' => $this->client_number,
            'number_of_shares' => $this->number_of_shares,
            'price_per_share' => $this->price_per_share,
            'total_value' => $this->number_of_shares * $this->price_per_share,
            'linked_savings_account' => $this->linked_savings_account,
            'share_account' => $this->share_account,
            'reference_number' => '1000'
        ];

        $result = $this->shareIssuanceService->issueShares($data);

        if ($result['success']) {
            session()->flash('success', $result['message']);
            $this->resetForm();
        } else {
            if (isset($result['errors'])) {
                foreach ($result['errors'] as $field => $errors) {
                    foreach ($errors as $error) {
                        $this->addError($field, $error);
                    }
                }
            } else {
                session()->flash('error', $result['message']);
            }
        }
    }

    private function resetForm()
    {
        $this->reset([
            'product_id',
            'client_number',
            'number_of_shares',
            'price_per_share',
            'linked_savings_account',
            'share_account'
        ]);
    }

    public function render()
    {
        return view('livewire.shares.share-issuance-form');
    }
}
```

## Benefits

1. **Reusability**: Can be used from any component or controller
2. **Consistency**: Ensures all share issuances follow the same process
3. **Maintainability**: Centralized logic for share issuance operations
4. **Validation**: Comprehensive validation and error handling
5. **Logging**: Detailed logging for debugging and auditing
6. **Transaction Safety**: Database transactions ensure data integrity 