<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class MandatorySavingsNotification extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mandatory_savings_notifications';

    protected $fillable = [
        'client_number',
        'account_number',
        'year',
        'month',
        'notification_type',
        'notification_method',
        'message',
        'status',
        'sent_at',
        'scheduled_at',
        'metadata'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Get the client/member for this notification.
     */
    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_number', 'client_number');
    }

    /**
     * Get the account for this notification.
     */
    public function account()
    {
        return $this->belongsTo(AccountsModel::class, 'account_number', 'account_number');
    }

    /**
     * Get the tracking record for this notification.
     */
    public function tracking()
    {
        return $this->belongsTo(MandatorySavingsTracking::class, 'client_number', 'client_number')
            ->where('year', $this->year)
            ->where('month', $this->month);
    }

    /**
     * Scope to get pending notifications.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope to get sent notifications.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'SENT');
    }

    /**
     * Scope to get notifications scheduled for a specific date range.
     */
    public function scopeScheduledBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('scheduled_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get notifications for a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    /**
     * Scope to get notifications for a specific period.
     */
    public function scopeForPeriod($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    /**
     * Mark notification as sent.
     */
    public function markAsSent()
    {
        $this->status = 'SENT';
        $this->sent_at = now();
        $this->save();
    }

    /**
     * Mark notification as failed.
     */
    public function markAsFailed()
    {
        $this->status = 'FAILED';
        $this->save();
    }

    /**
     * Check if notification is overdue for sending.
     */
    public function isOverdue()
    {
        return $this->status === 'PENDING' && $this->scheduled_at->isPast();
    }

    /**
     * Get the period as a formatted string.
     */
    public function getPeriodAttribute()
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }

    /**
     * Get notification type as human readable text.
     */
    public function getNotificationTypeTextAttribute()
    {
        return match($this->notification_type) {
            'FIRST_REMINDER' => 'First Reminder',
            'SECOND_REMINDER' => 'Second Reminder',
            'FINAL_REMINDER' => 'Final Reminder',
            'OVERDUE_NOTICE' => 'Overdue Notice',
            default => $this->notification_type
        };
    }
} 