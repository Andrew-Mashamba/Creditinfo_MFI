<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TillTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'till_id',
        'member_id',
        'account_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'narration',
        'reference',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    /**
     * Get the till for this transaction
     */
    public function till(): BelongsTo
    {
        return $this->belongsTo(Till::class);
    }

    /**
     * Get the member for this transaction
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the account for this transaction
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the user who created this transaction
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Get the transaction type label
     */
    public function getTypeLabelAttribute(): string
    {
        $types = [
            'deposit' => 'Cash Deposit',
            'withdrawal' => 'Cash Withdrawal',
            'transfer_to_vault' => 'Transfer to Vault',
            'transfer_from_vault' => 'Transfer from Vault',
        ];

        return $types[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Check if transaction increases till balance
     */
    public function isCredit(): bool
    {
        return in_array($this->type, ['deposit', 'transfer_from_vault']);
    }

    /**
     * Check if transaction decreases till balance
     */
    public function isDebit(): bool
    {
        return in_array($this->type, ['withdrawal', 'transfer_to_vault']);
    }
} 