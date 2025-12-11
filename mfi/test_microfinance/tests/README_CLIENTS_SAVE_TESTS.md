# Testing the Clients Save Function

## Overview
This document explains how to run and understand the tests for the `save()` function in the Clients Livewire component.

## Test Files Created

### 1. Feature Test: `tests/Feature/Livewire/ClientsSaveTest.php`
This is a comprehensive integration test that tests the entire save flow including:
- Database interactions
- File uploads
- Payment link generation
- Job dispatching
- Validation

### 2. Unit Test: `tests/Unit/Livewire/ClientsSaveUnitTest.php`
This focuses on testing individual pieces of logic within the save function:
- Member number generation
- Account number assignment
- Name formatting
- Data structure building

## Running the Tests

### Run All Clients Save Tests
```bash
php artisan test --filter=ClientsSave
```

### Run Only Feature Tests
```bash
php artisan test tests/Feature/Livewire/ClientsSaveTest.php
```

### Run Only Unit Tests
```bash
php artisan test tests/Unit/Livewire/ClientsSaveUnitTest.php
```

### Run Specific Test Method
```bash
php artisan test --filter=it_can_save_individual_member_with_complete_data
```

## Test Coverage

### Feature Tests Cover:
1. **Individual Member Registration** - Complete flow with all data
2. **Business Member Registration** - Business-specific fields
3. **Validation** - Required field validation
4. **Payment Link Generation** - Success and failure scenarios
5. **Bills Update** - Payment link storage in bills table
6. **Guarantor Validation** - Active member verification

### Unit Tests Cover:
1. **Member Number Generation** - Unique number creation
2. **Account Number Logic** - NBC account vs client number
3. **Name Formatting** - Uppercase conversion
4. **Account Name Generation** - Individual vs Business
5. **Data Structure** - Client data array building
6. **SACCOS ID Extraction** - Institution ID parsing

## Setting Up Test Environment

### 1. Database Configuration
Create a test database and update `.env.testing`:
```env
DB_CONNECTION=mysql
DB_DATABASE=saccos_test
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Required Tables
The tests expect these tables to exist (created by migrations):
- users
- clients
- branches
- institutions
- services
- bills
- accounts
- approvals
- guarantors
- client_documents

### 3. Mocking External Services
The tests mock:
- `PaymentLinkService` - To avoid actual API calls
- `Storage` - For file upload testing
- `Queue/Bus` - For job dispatching

## Common Test Scenarios

### 1. Testing Individual Member Save
```php
Livewire::test(Clients::class)
    ->set('membership_type', 'Individual')
    ->set('first_name', 'John')
    ->set('last_name', 'Doe')
    // ... set other required fields
    ->call('save')
    ->assertSessionHas('success');
```

### 2. Testing Payment Link Failure
The test mocks PaymentLinkService to throw an exception and verifies the fallback URL is used.

### 3. Testing Guarantor Validation
Creates an inactive member and verifies the save fails when using them as guarantor.

## Debugging Failed Tests

### 1. Database State
```bash
php artisan test --filter=test_name --stop-on-failure
```

### 2. View SQL Queries
Add to test:
```php
DB::listen(function ($query) {
    dump($query->sql);
    dump($query->bindings);
});
```

### 3. Check Validation Errors
```php
->assertHasErrors(['field_name'])
->dd() // Dump component state
```

## Extending the Tests

### Add New Test Case
```php
/** @test */
public function it_handles_new_scenario()
{
    $this->actingAs($this->user);
    
    Livewire::test(Clients::class)
        ->set('property', 'value')
        ->call('save')
        ->assertSessionHas('expected_message');
}
```

### Mock New Service
```php
$mockService = Mockery::mock(NewService::class);
$mockService->shouldReceive('method')
    ->once()
    ->andReturn($expectedValue);

$this->app->instance(NewService::class, $mockService);
```

## Best Practices

1. **Always use database transactions** - Tests use `RefreshDatabase` trait
2. **Mock external services** - Don't make real API calls in tests
3. **Test both success and failure paths** - Include error scenarios
4. **Use descriptive test names** - Should explain what is being tested
5. **Keep tests focused** - One assertion per test when possible
6. **Clean up after tests** - Use `tearDown()` method