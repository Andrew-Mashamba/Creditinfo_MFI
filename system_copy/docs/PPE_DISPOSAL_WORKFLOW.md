# PPE Disposal Workflow Documentation

## Complete Disposal Process Flow

The PPE disposal process follows a multi-stage workflow with approval requirements and accounting integration:

```
Active Asset → Pending Disposal → Approved for Disposal → Disposed (Complete)
```

## Stage 1: Initiate Disposal
**Status Change:** `active` → `pending_disposal`

### Trigger
- User clicks "Initiate Disposal" button on an active asset
- Method: `initiateDisposal($id)` in PpeManagement.php (line 383)

### What Happens
1. Asset status changes to `pending_disposal`
2. Creates an approval record in `approvals` table with:
   - process_code: `ASSET_DISP`
   - process_status: `PENDING`
3. Asset enters the approval queue

### Code Reference
```php
// PpeManagement.php line 383-400
public function initiateDisposal($id)
{
    $asset = PPE::find($id);
    if ($asset) {
        $asset->update(['status' => 'pending_disposal']);
        
        // Create approval record
        \App\Models\approvals::create([
            'process_name' => 'Dispose Asset',
            'process_code' => 'ASSET_DISP',
            'process_id' => $asset->id,
            'process_status' => 'PENDING',
            ...
        ]);
    }
}
```

## Stage 2: Approval Process
**Status:** Remains `pending_disposal`
**Approval Status:** `pending` → `approved` or `rejected`

### Location
- Handled in Approvals.php component
- Approvers see the request in the Approvals module

### Approval Action
When approved via the Approvals module:
1. Approval record status changes to `APPROVED`
2. PPE status changes to `approved_for_disposal`
3. `disposal_approval_status` field updates to `approved`

### Code Reference
```php
// Approvals.php line 1277-1281
'ASSET_DISP' => [
    'table' => 'ppes',
    'approval_status' => 'approved_for_disposal',
    'rejection_status' => 'active'  // If rejected, goes back to active
],
```

### If Rejected
- Asset status reverts to `active`
- Disposal process is cancelled
- Asset remains in service

## Stage 3: Complete Disposal (Final Stage)
**Status Change:** `approved_for_disposal` → `disposed`

### Prerequisites
- Asset must have status = `approved_for_disposal` OR
- Asset must have disposal_approval_status = `approved`

### Trigger
- User with disposal rights clicks on approved asset
- Opens disposal form and clicks "Complete Disposal"
- Method: `completeDisposal()` in PpeManagement.php (line 425)

### What Happens
1. **Validation** - Disposal details are validated:
   - Disposal date (required)
   - Disposal method (sold/scrapped/donated/lost/stolen/other)
   - Disposal proceeds (≥ 0)
   - Disposal notes (optional)

2. **Status Updates**:
   ```php
   $asset->update([
       'status' => 'disposed',
       'disposal_date' => $this->disposal_date,
       'disposal_method' => $this->disposal_method,
       'disposal_proceeds' => $this->disposal_proceeds,
       'disposal_notes' => $this->disposal_notes,
       'disposal_approval_status' => 'completed',
       'disposal_approved_by' => auth()->id(),
       'disposal_approved_at' => now()
   ]);
   ```

3. **Accounting Entries Created** (line 458-590):
   - **Entry 1**: Reverse accumulated depreciation
     - Debit: Accumulated Depreciation Account
     - Credit: PPE Asset Account
   
   - **Entry 2**: Record sale proceeds (if any)
     - Debit: Bank/Cash Account
     - Credit: PPE Asset Account
   
   - **Entry 3**: Recognize gain or loss
     - If Gain (proceeds > book value):
       - Credit: Gain on Disposal Account
     - If Loss (proceeds < book value):
       - Debit: Loss on Disposal Account
   
   All entries use `TransactionPostingService::postTransaction()`

4. **Final State**:
   - Asset marked as `disposed`
   - Cannot be reversed
   - Removed from active asset lists
   - Appears in disposal reports

### Code Reference
```php
// PpeManagement.php line 425-456
public function completeDisposal()
{
    // Validate disposal details
    $this->validate([...]);
    
    $asset = PPE::find($this->disposalAssetId);
    if ($asset && ($asset->status === 'approved_for_disposal' || 
                   $asset->disposal_approval_status === 'approved')) {
        
        // Update asset to disposed status
        $asset->update([
            'status' => 'disposed',
            ...
        ]);
        
        // Create accounting entries
        $this->createDisposalAccountingEntry($asset);
    }
}
```

## Summary Table

| Stage | Status | Approval Status | Actions Available | Accounting Impact |
|-------|--------|-----------------|-------------------|-------------------|
| 1. Active | `active` | - | Initiate Disposal | None |
| 2. Pending Approval | `pending_disposal` | `pending` | Approve/Reject | None |
| 3. Approved | `approved_for_disposal` | `approved` | Complete Disposal | None |
| 4. Disposed | `disposed` | `completed` | None (Final) | GL Entries Posted |

## Key Points

1. **Two-Step Approval**: 
   - First: Approve the disposal request
   - Second: Execute the actual disposal with details

2. **Accounting Entries**: 
   - Only created when disposal is completed (Stage 3)
   - Not created during approval stage
   - Follows double-entry bookkeeping

3. **Irreversible**: 
   - Once status = `disposed`, it cannot be undone
   - Accounting entries are permanent

4. **Audit Trail**:
   - Tracks who initiated disposal
   - Records approval details
   - Logs who completed disposal
   - Timestamps all actions

## Testing the Workflow

1. **Initiate**: Click "Initiate Disposal" on any active asset
2. **Approve**: Go to Approvals module, find ASSET_DISP request, approve it
3. **Complete**: Return to PPE Management, find approved asset, click "Complete Disposal"
4. **Verify**: Check General Ledger for disposal entries

## Database Fields Involved

```sql
-- PPE Table Fields
status: active → pending_disposal → approved_for_disposal → disposed
disposal_approval_status: null → pending → approved → completed
disposal_date: Set when disposed
disposal_method: Set when disposed
disposal_proceeds: Set when disposed
disposal_notes: Set when disposed
disposal_approved_by: User ID who completed disposal
disposal_approved_at: Timestamp of completion

-- Approvals Table
process_code: 'ASSET_DISP'
process_status: PENDING → APPROVED/REJECTED
```

---
*Generated: 2025-09-14*
*SACCOS Core System - PPE Disposal Workflow*