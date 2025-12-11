<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BudgetAlert extends Model
{
    use HasFactory;

    protected $table = 'budget_alerts';

    protected $fillable = [
        'budget_id',
        'alert_type',
        'threshold_value',
        'actual_value',
        'message',
        'recipients',
        'is_sent',
        'sent_at',
        'is_acknowledged',
        'acknowledged_by',
        'acknowledged_at'
    ];

    protected $casts = [
        'threshold_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'recipients' => 'array',
        'is_sent' => 'boolean',
        'is_acknowledged' => 'boolean',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the budget that owns the alert
     */
    public function budget()
    {
        return $this->belongsTo(BudgetManagement::class, 'budget_id');
    }

    /**
     * Get the user who acknowledged the alert
     */
    public function acknowledger()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Scope for unacknowledged alerts
     */
    public function scopeUnacknowledged($query)
    {
        return $query->where('is_acknowledged', false);
    }

    /**
     * Scope for sent alerts
     */
    public function scopeSent($query)
    {
        return $query->where('is_sent', true);
    }

    /**
     * Scope for pending alerts
     */
    public function scopePending($query)
    {
        return $query->where('is_sent', false);
    }

    /**
     * Acknowledge the alert
     */
    public function acknowledge($userId = null)
    {
        $this->is_acknowledged = true;
        $this->acknowledged_by = $userId ?? auth()->id();
        $this->acknowledged_at = now();
        $this->save();

        return $this;
    }

    /**
     * Send the alert
     */
    public function send()
    {
        try {
            // Get recipients
            $recipients = $this->getRecipients();

            // Log the alert
            Log::channel('budget_management')->warning('Budget Alert', [
                'budget_id' => $this->budget_id,
                'budget_name' => $this->budget->budget_name,
                'alert_type' => $this->alert_type,
                'threshold' => $this->threshold_value,
                'actual' => $this->actual_value,
                'message' => $this->message
            ]);

            // TODO: Send email notifications to recipients
            // This would integrate with your email system
            
            // Mark as sent
            $this->is_sent = true;
            $this->sent_at = now();
            $this->save();

            // Update budget last alert sent
            $this->budget->update(['last_alert_sent' => now()]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send budget alert', [
                'alert_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get alert recipients
     */
    private function getRecipients()
    {
        if ($this->recipients) {
            return $this->recipients;
        }

        // Default recipients based on alert type
        $recipients = [];
        
        // Get budget owner/department head
        if ($this->budget->department) {
            // TODO: Get department head email
        }

        // Get finance team for critical alerts
        if (in_array($this->alert_type, ['CRITICAL', 'OVERSPENT'])) {
            // TODO: Get finance team emails
        }

        return $recipients;
    }

    /**
     * Create alert for budget
     */
    public static function createForBudget(BudgetManagement $budget, $type, $message = null)
    {
        $alertMessage = $message ?? self::getDefaultMessage($budget, $type);

        return self::create([
            'budget_id' => $budget->id,
            'alert_type' => $type,
            'threshold_value' => $type === 'WARNING' ? $budget->warning_threshold : $budget->critical_threshold,
            'actual_value' => $budget->utilization_percentage,
            'message' => $alertMessage
        ]);
    }

    /**
     * Get default message for alert type
     */
    private static function getDefaultMessage(BudgetManagement $budget, $type)
    {
        $messages = [
            'WARNING' => "Budget '{$budget->budget_name}' has reached {$budget->utilization_percentage}% utilization (Warning threshold: {$budget->warning_threshold}%)",
            'CRITICAL' => "Budget '{$budget->budget_name}' has reached {$budget->utilization_percentage}% utilization (Critical threshold: {$budget->critical_threshold}%)",
            'OVERSPENT' => "Budget '{$budget->budget_name}' is OVERSPENT at {$budget->utilization_percentage}% utilization",
            'MILESTONE' => "Budget '{$budget->budget_name}' has reached a milestone",
            'PERIOD_END' => "Budget '{$budget->budget_name}' period is ending soon"
        ];

        return $messages[$type] ?? "Budget alert for '{$budget->budget_name}'";
    }
}