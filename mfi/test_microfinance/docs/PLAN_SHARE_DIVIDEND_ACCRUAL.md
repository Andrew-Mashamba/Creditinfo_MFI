# Plan: Daily Share Dividend Accrual System

## Overview
Implement a scheduled task `shares:accrue-dividends` that calculates and accrues daily dividends for share capital accounts, similar to how interest is accrued on savings/deposits.

## Business Requirements

### Dividend Calculation Method
- **Formula**: Daily Dividend = (Share Value × Annual Rate) / Days in Year
- **Method**: Actual/365 (or 366 for leap years)
- **Basis**: Current share balance × current price per share
- **Rate Source**: Institution-configured annual dividend rate (e.g., 10% p.a.)

### Key Differences from Interest Accrual
| Aspect | Savings Interest | Share Dividends |
|--------|------------------|-----------------|
| Source | Account balance | Share value (shares × price) |
| Rate | From sub_product | From institution settings |
| Frequency | Daily accrual | Daily accrual |
| Payment | Monthly/Quarterly | Annual (after AGM approval) |
| GL Entry | Expense account | Retained Earnings |

## Database Changes

### 1. New Table: `share_dividend_accruals`
```sql
CREATE TABLE share_dividend_accruals (
    id BIGSERIAL PRIMARY KEY,
    share_register_id BIGINT NOT NULL,
    member_number VARCHAR(50) NOT NULL,
    member_name VARCHAR(255),
    accrual_date DATE NOT NULL,
    share_balance INTEGER NOT NULL,
    price_per_share NUMERIC(20,6) NOT NULL,
    share_value NUMERIC(20,6) NOT NULL,
    annual_rate NUMERIC(10,4) NOT NULL,
    daily_rate NUMERIC(20,10) NOT NULL,
    dividend_amount NUMERIC(20,6) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACCRUED',  -- ACCRUED, CAPITALIZED, PAID
    fiscal_year INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),

    UNIQUE(share_register_id, accrual_date)
);

CREATE INDEX idx_share_dividend_accruals_date ON share_dividend_accruals(accrual_date);
CREATE INDEX idx_share_dividend_accruals_member ON share_dividend_accruals(member_number);
CREATE INDEX idx_share_dividend_accruals_fiscal_year ON share_dividend_accruals(fiscal_year);
```

### 2. Add to `institutions` table
```sql
ALTER TABLE institutions ADD COLUMN IF NOT EXISTS share_dividend_rate NUMERIC(10,4) DEFAULT 10.0000;
ALTER TABLE institutions ADD COLUMN IF NOT EXISTS dividend_expense_account VARCHAR(50) DEFAULT '0101300031003110';
ALTER TABLE institutions ADD COLUMN IF NOT EXISTS dividends_payable_account VARCHAR(50) DEFAULT '0101300036003640';
```

## Files to Create

### 1. Service: `app/Services/ShareDividendAccrualService.php`
Main service handling daily dividend accrual logic.

**Key Methods:**
- `processDailyAccrual(Carbon $date)` - Main entry point
- `getActiveShareRegisters()` - Get all active share accounts
- `accrueDividendForRegister($register, $date)` - Calculate and store dividend
- `getDividendRate()` - Get annual dividend rate from institution
- `postDividendToGL($date, $totalDividend)` - Post GL entries
- `getAccrualSummary($year, $month)` - Reporting method

**GL Entries (Daily):**
```
DR: Retained Earnings - Current Year (0101300031003110)
CR: Dividends Payable (0101300036003640)
```

### 2. Command: `app/Console/Commands/AccrueShareDividends.php`
Artisan command for scheduled execution.

**Signature:**
```php
protected $signature = 'shares:accrue-dividends
                        {--date= : Process date (YYYY-MM-DD, defaults to yesterday)}
                        {--report : Show accrual summary without processing}
                        {--year= : Year for report}
                        {--month= : Month for report}';
```

### 3. Update Kernel.php
Add schedule entry:
```php
// Daily Share Dividend Accrual - Run at 00:20 AM
$schedule->command('shares:accrue-dividends')
        ->dailyAt('00:20')
        ->withoutOverlapping()
        ->runInBackground()
        ->appendOutputTo(storage_path('logs/share-dividend-accrual.log'));
```

## Implementation Steps

### Step 1: Database Migration
- Create `share_dividend_accruals` table
- Add institution columns for dividend settings
- Set default values

### Step 2: Create ShareDividendAccrualService
```php
class ShareDividendAccrualService
{
    protected $dividendExpenseAccount;  // Retained Earnings
    protected $dividendsPayableAccount; // Dividends Payable

    public function processDailyAccrual(Carbon $accrualDate): array
    {
        // 1. Check if already processed for this date
        // 2. Get all active share registers
        // 3. For each register, calculate daily dividend
        // 4. Store accrual records
        // 5. Update share_registers.accumulated_dividends
        // 6. Post GL entry for total
        // 7. Return summary
    }

    protected function accrueDividendForRegister($register, $accrualDate): array
    {
        $annualRate = $this->getDividendRate();

        // Skip if zero rate
        if ($annualRate <= 0) {
            return ['status' => 'skipped', 'reason' => 'Zero dividend rate'];
        }

        // Skip if zero shares
        if ($register->current_share_balance <= 0) {
            return ['status' => 'skipped', 'reason' => 'No shares'];
        }

        // Calculate
        $shareValue = $register->current_share_balance * $register->current_price;
        $daysInYear = $accrualDate->isLeapYear() ? 366 : 365;
        $dailyRate = $annualRate / $daysInYear / 100;
        $dailyDividend = $shareValue * $dailyRate;

        // Store accrual record
        DB::table('share_dividend_accruals')->insert([
            'share_register_id' => $register->id,
            'member_number' => $register->member_number,
            'member_name' => $register->member_name,
            'accrual_date' => $accrualDate->toDateString(),
            'share_balance' => $register->current_share_balance,
            'price_per_share' => $register->current_price,
            'share_value' => $shareValue,
            'annual_rate' => $annualRate,
            'daily_rate' => $dailyRate,
            'dividend_amount' => round($dailyDividend, 2),
            'status' => 'ACCRUED',
            'fiscal_year' => $accrualDate->year,
        ]);

        // Update share register accumulated dividends
        DB::table('share_registers')
            ->where('id', $register->id)
            ->increment('accumulated_dividends', round($dailyDividend, 2));

        return [
            'status' => 'success',
            'dividend_amount' => round($dailyDividend, 2)
        ];
    }
}
```

### Step 3: Create Artisan Command
Similar structure to `AccrueSavingsInterest.php`

### Step 4: Update Scheduler
Add to Kernel.php with appropriate timing (after savings interest accrual)

### Step 5: Add Livewire View (Optional)
Create `InterestOnShares.php` or `DividendAccrual.php` in Livewire/Shares to display:
- Daily accruals per member
- Summary statistics
- Historical data

## GL Account Mapping

| Account | Number | Usage |
|---------|--------|-------|
| Retained Earnings - Current Year | 0101300031003110 | DR - Source of dividend |
| Dividends Payable | 0101300036003640 | CR - Liability until paid |
| Dividends Declared | 0101300036003610 | Used when formally declared |
| Final Dividends Paid | 0101300036003630 | When paid to members |

## Workflow

### Daily Accrual (Automated)
```
00:20 AM Daily:
1. shares:accrue-dividends runs
2. For each active share register:
   - Calculate: (shares × price × rate) / 365
   - Insert into share_dividend_accruals
   - Update share_registers.accumulated_dividends
3. Post single GL entry:
   DR: Retained Earnings
   CR: Dividends Payable
4. Log results
```

### Annual Dividend Declaration (Manual via UI)
```
1. Management sets dividend rate for year
2. System calculates based on accumulated dividends
3. Approval workflow triggers
4. Upon approval:
   DR: Dividends Payable (0101300036003640)
   CR: Member Savings Accounts (individual credits)
   - Update share_registers.total_paid_dividends
   - Clear share_registers.total_pending_dividends
```

## Testing Plan

1. **Unit Test**: Dividend calculation formula
2. **Integration Test**: Full accrual cycle
3. **Manual Test**:
   - Run command for specific date
   - Verify GL entries
   - Verify share_registers updated
   - Verify accrual records created

## Example Calculation

For member 00002 with 137 shares at TZS 5,000 each:
- Share Value: 137 × 5,000 = TZS 685,000
- Annual Rate: 10%
- Daily Rate: 10% / 365 = 0.0274%
- Daily Dividend: 685,000 × 0.000274 = TZS 187.67

Annual projected dividend: TZS 68,500

## Estimated Timeline
- Database migration: Done in implementation
- Service creation: Core implementation
- Command creation: Following service
- Scheduler update: Single line addition
- Testing: Verify with manual run
