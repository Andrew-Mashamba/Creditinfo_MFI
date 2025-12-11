# Tab State Management - Database-Driven Approach

## Overview

The loan process tab state management has been refactored to use a **database-driven approach** instead of in-memory state management. This ensures that tab completion status is persistent across sessions and page refreshes.

## Key Changes

### 1. Removed In-Memory State
- Removed `'completed' => false` from tab configuration arrays
- Eliminated in-memory state tracking in the `$tabs` array
- Removed `loadTabProgress()` and `saveTabProgress()` methods that managed in-memory state

### 2. Database-First Approach
- All tab completion status is now retrieved directly from the database
- Tab completion is saved immediately to the database when marked as completed
- Progress calculation happens on every render to ensure fresh data

## Database Schema

The tab completion status is stored in the `loan_process_progress` table:

```sql
CREATE TABLE loan_process_progress (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    loan_id BIGINT NOT NULL,
    completed_tabs JSON NULL,
    tab_data JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
    UNIQUE KEY unique_loan_id (loan_id)
);
```

## Key Methods

### `isTabCompleted($tabName)`
Checks if a tab is completed by:
1. Checking if manually marked as completed in the database
2. Checking if completed based on actual data validation
3. Returns `true` if either condition is met

### `markTabAsCompleted($tabName)`
Marks a tab as completed by:
1. Getting current completed tabs from database
2. Adding the new tab if not already present
3. Saving the updated list to database immediately
4. Recalculating progress

### `calculateProgress()`
Calculates progress by:
1. Getting all completed tabs from database
2. Computing percentage based on completed vs total tabs
3. Called on every render to ensure fresh data

## Tab Completion Logic

### Data-Based Completion
Each tab has specific completion criteria:

- **Client Tab**: Requires `first_name`, `last_name`, `phone_number`
- **Guarantor Tab**: Requires active guarantors with active collaterals
- **Document Tab**: Requires at least one document with category 'add-document'
- **Assessment Tab**: Requires assessment data or loan approval status

### Manual Completion
Users can manually mark tabs as completed using the "Mark Complete" buttons, which are stored separately in the database.

## Benefits

1. **Persistence**: Tab completion status survives page refreshes and browser sessions
2. **Reliability**: No risk of losing progress due to component re-initialization
3. **Real-time**: Progress is always calculated from the latest database state
4. **Scalability**: Can handle multiple users working on the same loan
5. **Audit Trail**: Database records provide an audit trail of completion status

## Usage

### In Views
The view automatically uses the new database-driven methods:

```php
@php $status = $this->getTabStatus($tabKey) @endphp
```

### In Components
Child components can emit completion events:

```php
$this->emit('tabCompleted', 'assessment');
```

### Manual Completion
Users can manually mark tabs as completed using the UI buttons, which call:

```php
wire:click="markTabAsCompleted('assessment')"
```

## Error Handling

The system includes robust error handling:
- Graceful fallbacks when the service is unavailable
- Comprehensive logging for debugging
- User-friendly error messages
- Default values when database operations fail

## Migration Notes

- Existing loans will automatically work with the new system
- The `loan_process_progress` table will be created automatically
- No data migration is required as completion status is calculated on-demand 