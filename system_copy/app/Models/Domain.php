<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain_name',
        'registrant_name',
        'registrant_organization',
        'registrant_email',
        'registrant_phone',
        'registrant_address',
        'city',
        'country',
        'admin_name',
        'admin_email',
        'admin_phone',
        'nameservers',
        'registration_date',
        'expiry_date',
        'amount',
        'currency',
        'transaction_id',
        'status',
        'registration_period',
        'api_code',
        'api_message'
    ];

    protected $casts = [
        'nameservers' => 'array',
        'registration_date' => 'date',
        'expiry_date' => 'date',
        'amount' => 'decimal:2'
    ];

    /**
     * Scope for active domains
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for expired domains
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope for domains expiring soon
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', Carbon::now()->addDays($days))
                    ->where('status', 'active');
    }

    /**
     * Check if domain is expired
     */
    public function isExpired()
    {
        return $this->expiry_date < Carbon::now();
    }

    /**
     * Check if domain is expiring soon
     */
    public function isExpiringSoon($days = 30)
    {
        return $this->expiry_date <= Carbon::now()->addDays($days) && 
               $this->expiry_date > Carbon::now();
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute()
    {
        return Carbon::now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Get formatted expiry date
     */
    public function getFormattedExpiryDateAttribute()
    {
        return $this->expiry_date->format('M d, Y');
    }

    /**
     * Get formatted registration date
     */
    public function getFormattedRegistrationDateAttribute()
    {
        return $this->registration_date->format('M d, Y');
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'active' => 'bg-green-100 text-green-800',
            'expired' => 'bg-red-100 text-red-800',
            'suspended' => 'bg-yellow-100 text-yellow-800',
            'pending' => 'bg-gray-100 text-gray-800'
        ];

        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Update domain status based on expiry date
     */
    public function updateStatus()
    {
        if ($this->isExpired() && $this->status !== 'expired') {
            $this->update(['status' => 'expired']);
        } elseif (!$this->isExpired() && $this->status === 'expired') {
            $this->update(['status' => 'active']);
        }
    }

    /**
     * Get nameservers as string
     */
    public function getNameserversStringAttribute()
    {
        return is_array($this->nameservers) ? implode(', ', $this->nameservers) : $this->nameservers;
    }
}
