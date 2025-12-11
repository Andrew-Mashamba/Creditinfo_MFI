<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_name',
        'service_code',
        'description',
        'service_type',
        'subscription_type',
        'base_price',
        'cost_per_unit',
        'unit_type',
        'included_units',
        'pricing_model',
        'status',
        'billing_frequency',
        'start_date',
        'end_date',
        'next_billing_date',
        'last_billed_date',
        'current_usage',
        'total_usage',
        'current_month_cost',
        'total_cost_paid',
        'features',
        'configuration',
        'auto_renew',
        'is_system_service',
        'trade_payable_id',
        'vendor_name',
        'vendor_email',
        'vendor_phone',
        'created_by',
        'updated_by',
        'branch_id'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'current_month_cost' => 'decimal:2',
        'total_cost_paid' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing_date' => 'date',
        'last_billed_date' => 'date',
        'features' => 'array',
        'configuration' => 'array',
        'auto_renew' => 'boolean',
        'is_system_service' => 'boolean',
    ];

    // Relationships
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function tradePayable(): BelongsTo
    {
        return $this->belongsTo(TradePayable::class, 'trade_payable_id');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'subscription_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaused($query)
    {
        return $query->where('status', 'paused');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeMandatory($query)
    {
        return $query->where('subscription_type', 'mandatory');
    }

    public function scopeOptional($query)
    {
        return $query->where('subscription_type', 'optional');
    }

    public function scopeSystemServices($query)
    {
        return $query->where('is_system_service', true);
    }

    public function scopeUserServices($query)
    {
        return $query->where('is_system_service', false);
    }

    public function scopeByServiceType($query, $type)
    {
        return $query->where('service_type', $type);
    }

    public function scopeDueForBilling($query)
    {
        return $query->where('status', 'active')
                    ->where('next_billing_date', '<=', now());
    }

    // Business Logic Methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isMandatory(): bool
    {
        return $this->subscription_type === 'mandatory';
    }

    public function isOptional(): bool
    {
        return $this->subscription_type === 'optional';
    }

    public function isSystemService(): bool
    {
        return $this->is_system_service;
    }

    public function canBePaused(): bool
    {
        return $this->isOptional() && $this->isActive();
    }

    public function canBeResumed(): bool
    {
        return $this->isOptional() && $this->isPaused();
    }

    public function canBeCancelled(): bool
    {
        return $this->isOptional() && in_array($this->status, ['active', 'paused']);
    }

    public function calculateCurrentMonthCost(): float
    {
        if ($this->pricing_model === 'free') {
            return 0;
        }

        if ($this->pricing_model === 'fixed') {
            return $this->base_price;
        }

        if ($this->pricing_model === 'usage_based') {
            $extraUsage = max(0, $this->current_usage - $this->included_units);
            return $this->base_price + ($extraUsage * $this->cost_per_unit);
        }

        return $this->base_price;
    }

    public function getNextBillingDate(): ?Carbon
    {
        if (!$this->next_billing_date) {
            return null;
        }

        return Carbon::parse($this->next_billing_date);
    }

    public function calculateNextBillingDate(): Carbon
    {
        $lastDate = $this->last_billed_date ? Carbon::parse($this->last_billed_date) : Carbon::parse($this->start_date);
        
        switch ($this->billing_frequency) {
            case 'monthly':
                return $lastDate->addMonth();
            case 'quarterly':
                return $lastDate->addMonths(3);
            case 'annually':
                return $lastDate->addYear();
            default:
                return $lastDate->addMonth();
        }
    }

    public function isDueForBilling(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $nextBilling = $this->getNextBillingDate();
        return $nextBilling && $nextBilling->isPast();
    }

    public function pause(): bool
    {
        if (!$this->canBePaused()) {
            return false;
        }

        $this->update([
            'status' => 'paused',
            'updated_by' => auth()->id()
        ]);

        return true;
    }

    public function resume(): bool
    {
        if (!$this->canBeResumed()) {
            return false;
        }

        // Recalculate next billing date when resuming
        $nextBillingDate = $this->calculateNextBillingDate();
        
        $this->update([
            'status' => 'active',
            'next_billing_date' => $nextBillingDate,
            'updated_by' => auth()->id()
        ]);

        return true;
    }

    public function cancel(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->update([
            'status' => 'cancelled',
            'end_date' => now(),
            'updated_by' => auth()->id()
        ]);

        return true;
    }

    public function restart(): bool
    {
        if (!$this->isCancelled()) {
            return false;
        }

        $nextBillingDate = $this->calculateNextBillingDate();
        
        $this->update([
            'status' => 'active',
            'start_date' => now(),
            'end_date' => null,
            'next_billing_date' => $nextBillingDate,
            'current_usage' => 0,
            'current_month_cost' => 0,
            'updated_by' => auth()->id()
        ]);

        return true;
    }

    public function updateUsage(int $usage): void
    {
        $this->update([
            'current_usage' => $usage,
            'current_month_cost' => $this->calculateCurrentMonthCost(),
            'total_usage' => $this->total_usage + $usage,
            'updated_by' => auth()->id()
        ]);
    }

    public function processBilling(): bool
    {
        if (!$this->isDueForBilling()) {
            return false;
        }

        $cost = $this->calculateCurrentMonthCost();
        
        // Update billing information
        $this->update([
            'last_billed_date' => now(),
            'next_billing_date' => $this->calculateNextBillingDate(),
            'current_usage' => 0,
            'current_month_cost' => 0,
            'total_cost_paid' => $this->total_cost_paid + $cost,
            'updated_by' => auth()->id()
        ]);

        // Create bill in trade_payables if linked
        if ($this->trade_payable_id) {
            $this->createBillingRecord($cost);
        }

        return true;
    }

    private function createBillingRecord(float $amount): void
    {
        // This would create a new bill record in the trade_payables table
        // Implementation depends on your TradePayable model structure
    }

    // Static Methods
    public static function getSystemServices(): array
    {
        return [
            [
                'service_name' => 'SMS Service',
                'service_code' => 'SMS',
                'service_type' => 'sms',
                'subscription_type' => 'mandatory',
                'pricing_model' => 'usage_based',
                'base_price' => 0,
                'cost_per_unit' => 15,
                'unit_type' => 'sms',
                'included_units' => 0,
                'billing_frequency' => 'monthly',
                'features' => ['Transaction alerts', 'Payment reminders', 'Marketing messages', 'OTP verification'],
                'is_system_service' => true
            ],
            [
                'service_name' => 'Email Service',
                'service_code' => 'EMAIL',
                'service_type' => 'email',
                'subscription_type' => 'mandatory',
                'pricing_model' => 'free',
                'base_price' => 0,
                'cost_per_unit' => 0,
                'unit_type' => 'email',
                'included_units' => 0,
                'billing_frequency' => 'monthly',
                'features' => ['Transaction emails', 'Monthly statements', 'Marketing campaigns', 'Email templates'],
                'is_system_service' => true
            ],
            [
                'service_name' => 'Control Number Payment',
                'service_code' => 'CTRL',
                'service_type' => 'control_numbers',
                'subscription_type' => 'mandatory',
                'pricing_model' => 'free',
                'base_price' => 0,
                'cost_per_unit' => 0,
                'unit_type' => 'control_number',
                'included_units' => 0,
                'billing_frequency' => 'monthly',
                'features' => ['Automated control numbers', 'Multi-bank integration', 'Real-time reconciliation', 'Payment tracking'],
                'is_system_service' => true
            ],
            [
                'service_name' => 'Pay by Link',
                'service_code' => 'PBL',
                'service_type' => 'payment_links',
                'subscription_type' => 'mandatory',
                'pricing_model' => 'free',
                'base_price' => 0,
                'cost_per_unit' => 0,
                'unit_type' => 'link',
                'included_units' => 0,
                'billing_frequency' => 'monthly',
                'features' => ['Secure payment links', 'Multiple payment methods', 'Automatic receipts', 'Link expiration control'],
                'is_system_service' => true
            ],
            [
                'service_name' => 'Mobile Application',
                'service_code' => 'MOB',
                'service_type' => 'mobile_app',
                'subscription_type' => 'optional',
                'pricing_model' => 'free',
                'base_price' => 0,
                'cost_per_unit' => 0,
                'unit_type' => 'user',
                'included_units' => 0,
                'billing_frequency' => 'monthly',
                'features' => ['Account access', 'Loan applications', 'Transfer funds', 'Push notifications', 'Biometric login'],
                'is_system_service' => true
            ],
            [
                'service_name' => 'Members Portal',
                'service_code' => 'PORT',
                'service_type' => 'members_portal',
                'subscription_type' => 'optional',
                'pricing_model' => 'free',
                'base_price' => 0,
                'cost_per_unit' => 0,
                'unit_type' => 'user',
                'included_units' => 0,
                'billing_frequency' => 'monthly',
                'features' => ['Online account access', 'Loan applications', 'Document downloads', 'Support tickets'],
                'is_system_service' => true
            ]
        ];
    }
}
