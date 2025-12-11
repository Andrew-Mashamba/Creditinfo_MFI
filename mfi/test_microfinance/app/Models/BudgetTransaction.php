<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetTransaction extends Model
{
    use HasFactory;

    protected $table = 'budget_transactions';

    protected $fillable = [
        'budget_id',
        'transaction_id',
        'transaction_type',
        'reference_number',
        'amount',
        'description',
        'transaction_date',
        'status',
        'created_by',
        'posted_by',
        'posted_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'posted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the budget that owns the transaction
     */
    public function budget()
    {
        return $this->belongsTo(BudgetManagement::class, 'budget_id');
    }

    /**
     * Get the user who created the transaction
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who posted the transaction
     */
    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Scope for posted transactions
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'POSTED');
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope for expense transactions
     */
    public function scopeExpenses($query)
    {
        return $query->where('transaction_type', 'EXPENSE');
    }

    /**
     * Scope for commitment transactions
     */
    public function scopeCommitments($query)
    {
        return $query->where('transaction_type', 'COMMITMENT');
    }

    /**
     * Get transactions for a specific period
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Post the transaction
     */
    public function post()
    {
        $this->status = 'POSTED';
        $this->posted_by = auth()->id();
        $this->posted_at = now();
        $this->save();

        // Update budget spent amount
        if ($this->transaction_type === 'EXPENSE') {
            $this->budget->increment('spent_amount', $this->amount);
            $this->budget->update(['last_transaction_date' => now()]);
            $this->budget->calculateBudgetMetrics();
        }

        return $this;
    }

    /**
     * Reverse the transaction
     */
    public function reverse()
    {
        if ($this->status !== 'POSTED') {
            return false;
        }

        $this->status = 'REVERSED';
        $this->save();

        // Update budget spent amount
        if ($this->transaction_type === 'EXPENSE') {
            $this->budget->decrement('spent_amount', $this->amount);
            $this->budget->calculateBudgetMetrics();
        } elseif ($this->transaction_type === 'COMMITMENT') {
            $this->budget->decrement('committed_amount', $this->amount);
            $this->budget->calculateBudgetMetrics();
        }

        return $this;
    }

    /**
     * Cancel the transaction
     */
    public function cancel()
    {
        if ($this->status === 'POSTED') {
            return $this->reverse();
        }

        $this->status = 'CANCELLED';
        $this->save();

        return $this;
    }
}