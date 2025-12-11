<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanWriteoffRecovery extends Model
{
    use HasFactory;

    protected $fillable = [
        'writeoff_id',
        'loan_id',
        'client_number',
        'recovery_date',
        'recovery_amount',
        'recovery_method',
        'recovery_description',
        'recovery_source',
        'recovery_details',
        'recorded_by',
        'approved_by',
        'approved_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'recovery_date' => 'date',
        'approved_date' => 'datetime',
        'recovery_amount' => 'decimal:2',
        'recovery_details' => 'array'
    ];

    // Relationships
    public function writeOff(): BelongsTo
    {
        return $this->belongsTo(LoanWriteOff::class, 'writeoff_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'loan_id', 'loan_id');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('recovery_method', $method);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('recovery_date', [$from, $to]);
    }

    // Accessors
    public function getFormattedRecoveryAmountAttribute()
    {
        return 'TZS ' . number_format($this->recovery_amount, 2);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Pending'],
            'approved' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Approved'],
            'reversed' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Reversed'],
        ];

        return $badges[$this->status] ?? ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($this->status)];
    }

    public function getRecoveryMethodBadgeAttribute()
    {
        $badges = [
            'cash' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Cash Payment'],
            'collateral_sale' => ['class' => 'bg-blue-100 text-blue-800', 'text' => 'Collateral Sale'],
            'legal_settlement' => ['class' => 'bg-purple-100 text-purple-800', 'text' => 'Legal Settlement'],
            'insurance_claim' => ['class' => 'bg-indigo-100 text-indigo-800', 'text' => 'Insurance Claim'],
            'debt_forgiveness' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Debt Forgiveness'],
            'other' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Other Method'],
        ];

        return $badges[$this->recovery_method] ?? ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($this->recovery_method)];
    }

    // Methods
    public function approve(?User $approver = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $approver?->id ?? auth()->id(),
            'approved_date' => now()
        ]);

        // Update the parent writeoff recovery status
        $this->writeOff->updateRecoveryStatus();

        // Add audit trail to writeoff
        $this->writeOff->addAuditEntry(
            'recovery_approved',
            [
                'recovery_id' => $this->id,
                'recovery_amount' => $this->recovery_amount,
                'recovery_method' => $this->recovery_method,
                'approver' => $approver?->name ?? auth()->user()->name
            ],
            $approver
        );

        return true;
    }

    public function reverse(string $reason, ?User $user = null): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $this->update([
            'status' => 'reversed',
            'notes' => ($this->notes ?? '') . "\n\nReversed: " . $reason . " on " . now()->format('Y-m-d H:i:s')
        ]);

        // Update the parent writeoff recovery status
        $this->writeOff->updateRecoveryStatus();

        // Add audit trail
        $this->writeOff->addAuditEntry(
            'recovery_reversed',
            [
                'recovery_id' => $this->id,
                'recovery_amount' => $this->recovery_amount,
                'reason' => $reason,
                'reversed_by' => $user?->name ?? auth()->user()->name
            ],
            $user
        );

        return true;
    }

    // Static methods
    public static function getTotalRecoveries($from = null, $to = null)
    {
        $query = self::where('status', 'approved');
        
        if ($from && $to) {
            $query->whereBetween('recovery_date', [$from, $to]);
        }

        return $query->sum('recovery_amount');
    }

    public static function getRecoveriesByMethod($from = null, $to = null)
    {
        $query = self::where('status', 'approved');
        
        if ($from && $to) {
            $query->whereBetween('recovery_date', [$from, $to]);
        }

        return $query->selectRaw('recovery_method, COUNT(*) as count, SUM(recovery_amount) as total_amount')
            ->groupBy('recovery_method')
            ->get();
    }
}