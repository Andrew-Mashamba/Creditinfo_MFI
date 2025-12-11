<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class MandatorySavingsTracking extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mandatory_savings_tracking';

    protected $fillable = [
        'client_number',
        'account_number',
        'year',
        'month',
        'required_amount',
        'paid_amount',
        'balance',
        'status',
        'due_date',
        'paid_date',
        'months_in_arrears',
        'total_arrears',
        'notes',
        'arrears_status',
        'arrears_start_date',
        'last_reminder_date',
        'reminder_count',
        'escalation_level',
        'is_defaulter',
        'defaulter_date',
        'collection_action'
    ];

    protected $casts = [
        'required_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'total_arrears' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
        'arrears_start_date' => 'date',
        'last_reminder_date' => 'date',
        'defaulter_date' => 'date',
        'months_in_arrears' => 'integer',
        'reminder_count' => 'integer',
        'escalation_level' => 'integer',
        'is_defaulter' => 'boolean'
    ];

    /**
     * Arrears status constants
     */
    const ARREARS_CURRENT = 'CURRENT';
    const ARREARS_1_MONTH = 'ARREARS_1M';
    const ARREARS_2_MONTHS = 'ARREARS_2M';
    const ARREARS_3_MONTHS = 'ARREARS_3M';
    const ARREARS_DEFAULTER = 'DEFAULTER';

    /**
     * Get the client/member for this tracking record.
     */
    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_number', 'client_number');
    }

    /**
     * Get the account for this tracking record.
     */
    public function account()
    {
        return $this->belongsTo(AccountsModel::class, 'account_number', 'account_number');
    }

    /**
     * Get notifications for this tracking record.
     */
    public function notifications()
    {
        return $this->hasMany(MandatorySavingsNotification::class, 'client_number', 'client_number')
            ->where('year', $this->year)
            ->where('month', $this->month);
    }

    /**
     * Scope to get records for a specific period.
     */
    public function scopeForPeriod($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    /**
     * Scope to get unpaid records.
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE']);
    }

    /**
     * Scope to get overdue records.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'OVERDUE');
    }

    /**
     * Scope to get records due within a date range.
     */
    public function scopeDueBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    /**
     * Get the period as a formatted string.
     */
    public function getPeriodAttribute()
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }

    /**
     * Check if the payment is overdue.
     */
    public function isOverdue()
    {
        return $this->status === 'OVERDUE' || 
               ($this->due_date->isPast() && $this->balance > 0);
    }

    /**
     * Get the number of days overdue.
     */
    public function getDaysOverdueAttribute()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return $this->due_date->diffInDays(now());
    }

    /**
     * Update the payment status based on current data.
     */
    public function updateStatus()
    {
        if ($this->paid_amount >= $this->required_amount) {
            $this->status = 'PAID';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'PARTIAL';
        } elseif ($this->due_date->isPast()) {
            $this->status = 'OVERDUE';
        } else {
            $this->status = 'UNPAID';
        }

        $this->balance = $this->required_amount - $this->paid_amount;
        $this->save();
    }

    /**
     * Record a payment for this tracking record.
     */
    public function recordPayment($amount, $paymentDate = null)
    {
        $this->paid_amount += $amount;
        $this->paid_date = $paymentDate ?? now();
        $this->updateStatus();
    }

    /**
     * Calculate arrears for this member.
     */
    public static function calculateArrears($clientNumber, $asOfDate = null)
    {
        $asOfDate = $asOfDate ?? now();

        $unpaidRecords = self::where('client_number', $clientNumber)
            ->where('due_date', '<=', $asOfDate)
            ->where('balance', '>', 0)
            ->get();

        $totalArrears = $unpaidRecords->sum('balance');
        $monthsInArrears = $unpaidRecords->count();

        return [
            'total_arrears' => $totalArrears,
            'months_in_arrears' => $monthsInArrears,
            'unpaid_records' => $unpaidRecords
        ];
    }

    /**
     * Update arrears status based on months in arrears
     */
    public function updateArrearsStatus()
    {
        // If paid, reset arrears status
        if ($this->status === 'PAID') {
            $this->arrears_status = self::ARREARS_CURRENT;
            $this->arrears_start_date = null;
            $this->is_defaulter = false;
            $this->save();
            return;
        }

        // Calculate months since due date
        if (!$this->due_date) {
            return;
        }

        $monthsOverdue = $this->due_date->diffInMonths(now());

        // Set arrears start date if not set
        if (!$this->arrears_start_date && $this->due_date->isPast() && $this->balance > 0) {
            $this->arrears_start_date = $this->due_date;
        }

        // Update arrears status based on months overdue
        if ($monthsOverdue >= 3) {
            $this->arrears_status = self::ARREARS_DEFAULTER;
            $this->is_defaulter = true;
            if (!$this->defaulter_date) {
                $this->defaulter_date = now();
            }
            $this->status = 'DEFAULTED';
        } elseif ($monthsOverdue >= 2) {
            $this->arrears_status = self::ARREARS_3_MONTHS;
            $this->escalation_level = 3;
        } elseif ($monthsOverdue >= 1) {
            $this->arrears_status = self::ARREARS_2_MONTHS;
            $this->escalation_level = 2;
        } elseif ($this->due_date->isPast() && $this->balance > 0) {
            $this->arrears_status = self::ARREARS_1_MONTH;
            $this->escalation_level = 1;
        } else {
            $this->arrears_status = self::ARREARS_CURRENT;
            $this->escalation_level = 0;
        }

        $this->months_in_arrears = $monthsOverdue;
        $this->save();
    }

    /**
     * Record a reminder sent
     */
    public function recordReminderSent()
    {
        $this->reminder_count = ($this->reminder_count ?? 0) + 1;
        $this->last_reminder_date = now();
        $this->save();
    }

    /**
     * Check if reminder should be sent (every 5 days)
     */
    public function shouldSendReminder($intervalDays = 5)
    {
        // Don't send if paid
        if ($this->status === 'PAID') {
            return false;
        }

        // Don't send before due date
        if (!$this->due_date->isPast()) {
            return false;
        }

        // If no reminder sent yet, send one
        if (!$this->last_reminder_date) {
            return true;
        }

        // Check if enough days have passed since last reminder
        $daysSinceLastReminder = Carbon::parse($this->last_reminder_date)->diffInDays(now());
        return $daysSinceLastReminder >= $intervalDays;
    }

    /**
     * Get escalation level description
     */
    public function getEscalationDescription()
    {
        return match($this->arrears_status) {
            self::ARREARS_CURRENT => 'Current - No arrears',
            self::ARREARS_1_MONTH => '1st Month Arrears - Friendly reminder',
            self::ARREARS_2_MONTHS => '2nd Month Arrears - Urgent follow-up',
            self::ARREARS_3_MONTHS => '3rd Month Arrears - Final warning',
            self::ARREARS_DEFAULTER => 'Defaulter - Collection action required',
            default => 'Unknown status'
        };
    }

    /**
     * Scope to get members in arrears
     */
    public function scopeInArrears($query)
    {
        return $query->whereIn('arrears_status', [
            self::ARREARS_1_MONTH,
            self::ARREARS_2_MONTHS,
            self::ARREARS_3_MONTHS
        ]);
    }

    /**
     * Scope to get defaulters
     */
    public function scopeDefaulters($query)
    {
        return $query->where('arrears_status', self::ARREARS_DEFAULTER)
                     ->orWhere('is_defaulter', true);
    }

    /**
     * Scope to get records needing reminder
     */
    public function scopeNeedsReminder($query, $intervalDays = 5)
    {
        return $query->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
                     ->where('due_date', '<', now())
                     ->where(function($q) use ($intervalDays) {
                         $q->whereNull('last_reminder_date')
                           ->orWhere('last_reminder_date', '<=', now()->subDays($intervalDays));
                     });
    }

    /**
     * Get all unpaid records for a member (for cumulative arrears)
     */
    public static function getMemberArrearsSummary($clientNumber)
    {
        $records = self::where('client_number', $clientNumber)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE', 'DEFAULTED'])
            ->where('balance', '>', 0)
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $totalArrears = $records->sum('balance');
        $monthsInArrears = $records->count();
        $oldestUnpaid = $records->first();
        $isDefaulter = $monthsInArrears >= 3;

        return [
            'client_number' => $clientNumber,
            'total_arrears' => $totalArrears,
            'months_in_arrears' => $monthsInArrears,
            'is_defaulter' => $isDefaulter,
            'oldest_unpaid_period' => $oldestUnpaid ? $oldestUnpaid->period : null,
            'oldest_due_date' => $oldestUnpaid ? $oldestUnpaid->due_date : null,
            'records' => $records
        ];
    }
} 