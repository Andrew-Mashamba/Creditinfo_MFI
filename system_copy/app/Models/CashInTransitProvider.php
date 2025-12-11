<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashInTransitProvider extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cash_in_transit_providers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'company_code',
        'contact_person',
        'phone_number',
        'email',
        'address',
        'license_number',
        'license_expiry_date',
        'status',
        'service_fee_percentage',
        'minimum_fee',
        'service_areas',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'license_expiry_date' => 'date',
        'service_fee_percentage' => 'decimal:2',
        'minimum_fee' => 'decimal:2',
        'service_areas' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Scope a query to only include active providers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * Scope a query to only include inactive providers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'INACTIVE');
    }

    /**
     * Get the branches that use this CIT provider.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function branches()
    {
        return $this->hasMany(BranchesModel::class, 'cit_provider_id');
    }

    /**
     * Check if the provider's license is expired.
     *
     * @return bool
     */
    public function isLicenseExpired()
    {
        return $this->license_expiry_date && $this->license_expiry_date->isPast();
    }

    /**
     * Check if the provider's license is expiring soon (within 30 days).
     *
     * @return bool
     */
    public function isLicenseExpiringSoon()
    {
        return $this->license_expiry_date && 
               $this->license_expiry_date->isFuture() && 
               $this->license_expiry_date->diffInDays(now()) <= 30;
    }

    /**
     * Get the service areas as a formatted string.
     *
     * @return string
     */
    public function getServiceAreasStringAttribute()
    {
        return is_array($this->service_areas) ? implode(', ', $this->service_areas) : '';
    }

    /**
     * Calculate service fee for a given amount.
     *
     * @param float $amount
     * @return float
     */
    public function calculateServiceFee($amount)
    {
        $percentageFee = ($amount * $this->service_fee_percentage) / 100;
        return max($percentageFee, $this->minimum_fee);
    }
}
