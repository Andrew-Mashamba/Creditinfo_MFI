<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LockedAmount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'amount',
        'service_type',
        'service_id',
        'reason',
        'status',
        'description',
        'locked_at',
        'released_at',
        'expires_at',
        'locked_by',
        'released_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'locked_at' => 'datetime',
        'released_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountsModel::class, 'account_id');
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function releasedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeOnProgress($query)
    {
        return $query->where('status', 'ONPROGRESS');
    }

    public function scopeReleased($query)
    {
        return $query->where('status', 'RELEASED');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'EXPIRED');
    }

    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeForService($query, $serviceType, $serviceId)
    {
        return $query->where('service_type', $serviceType)
                    ->where('service_id', $serviceId);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    // Static methods for locking/unlocking
    public static function lockAmount($accountId, $amount, $serviceType, $serviceId, $reason, $description = null, $expiresAt = null, $status = null)
    {
        // Check if account has sufficient available balance
        $account = AccountsModel::find($accountId);
        if (!$account) {
            throw new \Exception('Account not found');
        }

        $availableBalance = self::getAvailableBalance($accountId);
        if ($availableBalance < $amount) {
            throw new \Exception("Insufficient available balance. Available: {$availableBalance}, Requested: {$amount}");
        }

        // Set default status based on service type
        if ($status === null) {
            $status = ($serviceType === 'loan_collateral') ? 'ONPROGRESS' : 'ACTIVE';
        }

        // Create the lock
        $lock = self::create([
            'account_id' => $accountId,
            'amount' => $amount,
            'service_type' => $serviceType,
            'service_id' => $serviceId,
            'reason' => $reason,
            'status' => $status,
            'description' => $description,
            'locked_at' => now(),
            'expires_at' => $expiresAt,
            'locked_by' => Auth::id(),
        ]);

        // Update account's locked_amount field
        self::updateAccountLockedAmount($accountId);

        return $lock;
    }

    public static function releaseAmount($accountId, $amount, $serviceType, $serviceId, $reason = null)
    {
        $locks = self::forAccount($accountId)
                    ->forService($serviceType, $serviceId)
                    ->active()
                    ->orderBy('locked_at')
                    ->get();

        $totalLocked = $locks->sum('amount');
        if ($totalLocked < $amount) {
            throw new \Exception("Cannot release more than locked amount. Locked: {$totalLocked}, Requested: {$amount}");
        }

        $remainingToRelease = $amount;
        foreach ($locks as $lock) {
            if ($remainingToRelease <= 0) break;

            $releaseAmount = min($lock->amount, $remainingToRelease);
            
            if ($releaseAmount == $lock->amount) {
                // Release entire lock
                $lock->update([
                    'status' => 'RELEASED',
                    'released_at' => now(),
                    'released_by' => Auth::id(),
                ]);
            } else {
                // Partial release - create new lock with remaining amount
                $lock->update([
                    'amount' => $lock->amount - $releaseAmount,
                ]);

                // Create new lock for remaining amount
                self::create([
                    'account_id' => $accountId,
                    'amount' => $lock->amount - $releaseAmount,
                    'service_type' => $serviceType,
                    'service_id' => $serviceId,
                    'reason' => $lock->reason,
                    'status' => 'ACTIVE',
                    'description' => $lock->description,
                    'locked_at' => $lock->locked_at,
                    'expires_at' => $lock->expires_at,
                    'locked_by' => $lock->locked_by,
                ]);

                // Mark original lock as released
                $lock->update([
                    'amount' => $releaseAmount,
                    'status' => 'RELEASED',
                    'released_at' => now(),
                    'released_by' => Auth::id(),
                ]);
            }

            $remainingToRelease -= $releaseAmount;
        }

        // Update account's locked_amount field
        self::updateAccountLockedAmount($accountId);

        return true;
    }

    public static function releaseAllForService($serviceType, $serviceId)
    {
        $locks = self::forService($serviceType, $serviceId)->active()->get();
        
        foreach ($locks as $lock) {
            $lock->update([
                'status' => 'RELEASED',
                'released_at' => now(),
                'released_by' => Auth::id(),
            ]);
        }

        // Update account's locked_amount field for affected accounts
        $accountIds = $locks->pluck('account_id')->unique();
        foreach ($accountIds as $accountId) {
            self::updateAccountLockedAmount($accountId);
        }

        return true;
    }

    /**
     * Activate ONPROGRESS locks when loan is disbursed
     */
    public static function activateLoanCollateralLocks($loanId)
    {
        $locks = self::forService('loan_collateral', $loanId)->onProgress()->get();
        
        foreach ($locks as $lock) {
            $lock->update([
                'status' => 'ACTIVE',
            ]);
        }

        // Update account's locked_amount field for affected accounts
        $accountIds = $locks->pluck('account_id')->unique();
        foreach ($accountIds as $accountId) {
            self::updateAccountLockedAmount($accountId);
        }

        return $locks->count();
    }

    public static function getAvailableBalance($accountId)
    {
        $account = AccountsModel::find($accountId);
        if (!$account) {
            return 0;
        }

        $totalLocked = self::forAccount($accountId)
                          ->active()
                          ->notExpired()
                          ->sum('amount');

        return $account->balance - $totalLocked;
    }

    public static function getLockedAmount($accountId)
    {
        return self::forAccount($accountId)
                  ->active()
                  ->notExpired()
                  ->sum('amount');
    }

    public static function updateAccountLockedAmount($accountId)
    {
        $totalLocked = self::getLockedAmount($accountId);
        
        AccountsModel::where('id', $accountId)->update([
            'locked_amount' => $totalLocked
        ]);
    }

    public static function checkAccountAvailability($accountId, $requiredAmount)
    {
        $availableBalance = self::getAvailableBalance($accountId);
        return $availableBalance >= $requiredAmount;
    }

    public static function getAccountLockSummary($accountId)
    {
        $locks = self::forAccount($accountId)
                    ->active()
                    ->notExpired()
                    ->with(['lockedByUser'])
                    ->get();

        $summary = [
            'total_locked' => $locks->sum('amount'),
            'locks_by_service' => $locks->groupBy('service_type')->map(function ($serviceLocks) {
                return [
                    'total_amount' => $serviceLocks->sum('amount'),
                    'locks' => $serviceLocks->map(function ($lock) {
                        return [
                            'id' => $lock->id,
                            'amount' => $lock->amount,
                            'service_id' => $lock->service_id,
                            'reason' => $lock->reason,
                            'description' => $lock->description,
                            'locked_at' => $lock->locked_at,
                            'expires_at' => $lock->expires_at,
                            'locked_by' => $lock->lockedByUser->name ?? 'System',
                        ];
                    })
                ];
            })
        ];

        return $summary;
    }

    // Instance methods
    public function isActive()
    {
        return $this->status === 'ACTIVE';
    }

    public function isExpired()
    {
        return $this->expires_at && now()->gt($this->expires_at);
    }

    public function canBeReleased()
    {
        return $this->isActive() && !$this->isExpired();
    }
}
