<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'receipt_number',
        'transaction_id',
        'account_id',
        'member_number',
        'member_name',
        'amount',
        'currency',
        'payment_method',
        'depositor_name',
        'narration',
        'reference_number',
        'bank_name',
        'processed_by',
        'branch',
        'transaction_type',
        'status',
        'generated_at',
        'printed_at',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'generated_at' => 'datetime',
        'printed_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Relationships
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function account()
    {
        return $this->belongsTo(AccountsModel::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopeGenerated($query)
    {
        return $query->where('status', 'GENERATED');
    }

    public function scopePrinted($query)
    {
        return $query->where('status', 'PRINTED');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('generated_at', [$startDate, $endDate]);
    }

    public function scopeByMember($query, $memberNumber)
    {
        return $query->where('member_number', $memberNumber);
    }

    public function scopeByPaymentMethod($query, $paymentMethod)
    {
        return $query->where('payment_method', $paymentMethod);
    }

    // Methods
    public function markAsPrinted()
    {
        $this->update([
            'status' => 'PRINTED',
            'printed_at' => now()
        ]);
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedDateAttribute()
    {
        return $this->generated_at->format('d/m/Y H:i:s');
    }

    public function getReceiptUrlAttribute()
    {
        return route('receipts.show', $this->receipt_number);
    }

    public function getPrintUrlAttribute()
    {
        return route('receipts.print', $this->receipt_number);
    }
}
