<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_date',
        'transfer_type',
        'from_account_id',
        'to_account_id',
        'amount',
        'narration',
        'attachment_path',
        'status',
        'created_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the source account for this transfer
     */
    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    /**
     * Get the destination account for this transfer
     */
    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    /**
     * Get the user who created this transfer
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reference number for this transfer
     */
    public function getReferenceNumberAttribute(): string
    {
        return 'IT' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Get the transfer type label
     */
    public function getTransferTypeLabelAttribute(): string
    {
        $types = [
            'asset_to_asset' => 'Asset to Asset',
            'asset_to_liability' => 'Asset to Liability',
            'liability_to_liability' => 'Liability to Liability',
            'liability_to_asset' => 'Liability to Asset',
            'equity_to_equity' => 'Equity to Equity',
            'liability_to_equity' => 'Liability to Equity',
            'equity_to_liability' => 'Equity to Liability',
            'asset_to_equity' => 'Asset to Equity',
            'equity_to_asset' => 'Equity to Asset',
        ];

        return $types[$this->transfer_type] ?? $this->transfer_type;
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeByDateRange($query, $fromDate, $toDate)
    {
        if ($fromDate) {
            $query->where('transfer_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('transfer_date', '<=', $toDate);
        }
        return $query;
    }

    /**
     * Scope for searching transfers
     */
    public function scopeSearch($query, $term)
    {
        if ($term) {
            return $query->where(function($q) use ($term) {
                $q->where('narration', 'like', '%' . $term . '%')
                  ->orWhereHas('fromAccount', function($subQ) use ($term) {
                      $subQ->where('account_name', 'like', '%' . $term . '%');
                  })
                  ->orWhereHas('toAccount', function($subQ) use ($term) {
                      $subQ->where('account_name', 'like', '%' . $term . '%');
                  });
            });
        }
        return $query;
    }

    /**
     * Check if the transfer has an attachment
     */
    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path);
    }

    /**
     * Get the attachment file name
     */
    public function getAttachmentNameAttribute(): string
    {
        if ($this->attachment_path) {
            return basename($this->attachment_path);
        }
        return '';
    }
}
