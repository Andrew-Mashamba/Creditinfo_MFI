<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanCollectionEffort extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'client_number',
        'effort_date',
        'effort_type',
        'effort_description',
        'outcome',
        'promised_payment_date',
        'promised_amount',
        'client_response',
        'staff_id',
        'contact_details',
        'supporting_documents',
        'cost_incurred',
        'notes'
    ];

    protected $casts = [
        'effort_date' => 'date',
        'promised_payment_date' => 'date',
        'promised_amount' => 'decimal:2',
        'cost_incurred' => 'decimal:2',
        'contact_details' => 'array',
        'supporting_documents' => 'array'
    ];

    // Relationships
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'loan_id', 'loan_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    // Scopes
    public function scopeByLoan($query, $loanId)
    {
        return $query->where('loan_id', $loanId);
    }

    public function scopeByEffortType($query, $type)
    {
        return $query->where('effort_type', $type);
    }

    public function scopeByOutcome($query, $outcome)
    {
        return $query->where('outcome', $outcome);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('effort_date', [$from, $to]);
    }

    public function scopeWithPromises($query)
    {
        return $query->whereNotNull('promised_payment_date');
    }

    public function scopeSuccessful($query)
    {
        return $query->whereIn('outcome', ['promise_to_pay', 'payment_made']);
    }

    // Accessors
    public function getFormattedPromisedAmountAttribute()
    {
        return $this->promised_amount ? 'TZS ' . number_format($this->promised_amount, 2) : null;
    }

    public function getFormattedCostIncurredAttribute()
    {
        return 'TZS ' . number_format($this->cost_incurred, 2);
    }

    public function getEffortTypeBadgeAttribute()
    {
        $badges = [
            'call' => ['class' => 'bg-blue-100 text-blue-800', 'text' => 'Phone Call'],
            'sms' => ['class' => 'bg-green-100 text-green-800', 'text' => 'SMS'],
            'email' => ['class' => 'bg-indigo-100 text-indigo-800', 'text' => 'Email'],
            'visit' => ['class' => 'bg-purple-100 text-purple-800', 'text' => 'Physical Visit'],
            'letter' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Written Letter'],
            'legal_notice' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Legal Notice'],
            'court_summons' => ['class' => 'bg-red-200 text-red-900', 'text' => 'Court Summons'],
            'other' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Other'],
        ];

        return $badges[$this->effort_type] ?? ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($this->effort_type)];
    }

    public function getOutcomeBadgeAttribute()
    {
        $badges = [
            'promise_to_pay' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Promise to Pay'],
            'payment_made' => ['class' => 'bg-green-200 text-green-900', 'text' => 'Payment Made'],
            'dispute' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Dispute Raised'],
            'no_response' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'No Response'],
            'unreachable' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Unreachable'],
            'partial_payment' => ['class' => 'bg-blue-100 text-blue-800', 'text' => 'Partial Payment'],
            'request_extension' => ['class' => 'bg-purple-100 text-purple-800', 'text' => 'Extension Requested'],
            'other' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Other'],
        ];

        return $badges[$this->outcome] ?? ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($this->outcome)];
    }

    public function getIsPromiseKeptAttribute()
    {
        if (!$this->promised_payment_date || !$this->promised_amount) {
            return null;
        }

        // Check if there was a payment made after the promise date
        return $this->loan?->repayments()
            ->where('payment_date', '>=', $this->promised_payment_date)
            ->where('amount', '>=', $this->promised_amount)
            ->exists() ?? false;
    }

    public function getDaysOverdueFromPromiseAttribute()
    {
        if (!$this->promised_payment_date) {
            return null;
        }

        return now()->diffInDays($this->promised_payment_date, false);
    }

    // Methods
    public function markPaymentMade($amount = null): void
    {
        $this->update([
            'outcome' => 'payment_made',
            'notes' => ($this->notes ?? '') . "\n\nPayment confirmed: TZS " . number_format($amount ?? $this->promised_amount ?? 0, 2) . " on " . now()->format('Y-m-d')
        ]);
    }

    public function addFollowUpNote(string $note): void
    {
        $this->update([
            'notes' => ($this->notes ?? '') . "\n\n[" . now()->format('Y-m-d H:i') . "] " . $note
        ]);
    }

    // Static methods
    public static function getCollectionEffortsSummary($loanId = null, $from = null, $to = null)
    {
        $query = self::query();
        
        if ($loanId) {
            $query->where('loan_id', $loanId);
        }
        
        if ($from && $to) {
            $query->whereBetween('effort_date', [$from, $to]);
        }

        return [
            'total_efforts' => $query->count(),
            'by_type' => $query->selectRaw('effort_type, COUNT(*) as count')
                ->groupBy('effort_type')
                ->pluck('count', 'effort_type'),
            'by_outcome' => $query->selectRaw('outcome, COUNT(*) as count')
                ->groupBy('outcome')
                ->pluck('count', 'outcome'),
            'total_cost' => $query->sum('cost_incurred'),
            'promises_made' => $query->whereNotNull('promised_payment_date')->count(),
            'successful_efforts' => $query->whereIn('outcome', ['promise_to_pay', 'payment_made'])->count(),
        ];
    }

    public static function getEffortivenessByStaff($from = null, $to = null)
    {
        $query = self::with('staff');
        
        if ($from && $to) {
            $query->whereBetween('effort_date', [$from, $to]);
        }

        return $query->selectRaw("staff_id, COUNT(*) as total_efforts, 
                                 SUM(CASE WHEN outcome IN ('promise_to_pay', 'payment_made') THEN 1 ELSE 0 END) as successful_efforts,
                                 SUM(cost_incurred) as total_cost")
            ->groupBy('staff_id')
            ->get()
            ->map(function ($item) {
                $item->success_rate = $item->total_efforts > 0 ? round(($item->successful_efforts / $item->total_efforts) * 100, 2) : 0;
                $item->cost_per_effort = $item->total_efforts > 0 ? round($item->total_cost / $item->total_efforts, 2) : 0;
                return $item;
            });
    }
}