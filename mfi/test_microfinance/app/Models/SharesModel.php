<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SharesModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'shares';

    protected $fillable = [
        'member_id',
        'shares',
        'price_per_share',
        'total_value',
        'transaction_type',
        'status',
        'narration',
    ];

    protected $casts = [
        'shares' => 'integer',
        'price_per_share' => 'decimal:2',
        'total_value' => 'decimal:2',
        'share_value' => 'decimal:2',
        'minimum_shares' => 'integer',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function member()
    {
        return $this->belongsTo(ClientsModel::class, 'member_id');
    }

    public function shareProduct()
    {
        return $this->belongsTo(sub_products::class, 'share_product_id');
    }

    public function transactions()
    {
        return $this->hasMany(ShareTransaction::class, 'share_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeDeleted($query)
    {
        return $query->where('status', 'deleted');
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isDeleted()
    {
        return $this->status === 'deleted';
    }
}