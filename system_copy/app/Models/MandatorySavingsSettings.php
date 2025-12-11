<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MandatorySavingsSettings extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mandatory_savings_settings';

    protected $fillable = [
        'institution_id',
        'mandatory_savings_account',
        'monthly_amount',
        'due_day',
        'grace_period_days',
        'enable_notifications',
        'first_reminder_days',
        'second_reminder_days',
        'final_reminder_days',
        'enable_sms_notifications',
        'enable_email_notifications',
        'sms_template',
        'email_template',
        'additional_settings'
    ];

    protected $casts = [
        'monthly_amount' => 'decimal:2',
        'due_day' => 'integer',
        'grace_period_days' => 'integer',
        'enable_notifications' => 'boolean',
        'first_reminder_days' => 'integer',
        'second_reminder_days' => 'integer',
        'final_reminder_days' => 'integer',
        'enable_sms_notifications' => 'boolean',
        'enable_email_notifications' => 'boolean',
        'additional_settings' => 'array'
    ];

    /**
     * Get the institution for these settings.
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id', 'id');
    }

    /**
     * Get the mandatory savings account.
     */
    public function mandatorySavingsAccount()
    {
        return $this->belongsTo(AccountsModel::class, 'mandatory_savings_account', 'account_number');
    }

    /**
     * Get settings for a specific institution.
     */
    public static function forInstitution($institutionId = '1')
    {
        return static::where('institution_id', $institutionId)->first();
    }

    /**
     * Get the default SMS template.
     */
    public function getDefaultSmsTemplate()
    {
        return $this->sms_template ?? 'Dear {member_name}, your mandatory savings payment of TZS {amount} for {period} is due on {due_date}. Please make your payment to avoid penalties.';
    }

    /**
     * Get the default email template.
     */
    public function getDefaultEmailTemplate()
    {
        return $this->email_template ?? 'Dear {member_name},

This is a reminder that your mandatory savings payment of TZS {amount} for {period} is due on {due_date}.

Payment Details:
- Amount Due: TZS {amount}
- Due Date: {due_date}
- Account: {account_number}

Please make your payment to avoid any penalties or late fees.

If you have already made the payment, please disregard this message.

Best regards,
{institution_name}';
    }

    /**
     * Get the due date for a specific month and year.
     */
    public function getDueDate($year, $month)
    {
        return \Carbon\Carbon::createFromDate($year, $month, $this->due_day);
    }

    /**
     * Get the grace period end date for a specific month and year.
     */
    public function getGracePeriodEndDate($year, $month)
    {
        return $this->getDueDate($year, $month)->addDays($this->grace_period_days);
    }

    /**
     * Get the first reminder date for a specific month and year.
     */
    public function getFirstReminderDate($year, $month)
    {
        return $this->getDueDate($year, $month)->subDays($this->first_reminder_days);
    }

    /**
     * Get the second reminder date for a specific month and year.
     */
    public function getSecondReminderDate($year, $month)
    {
        return $this->getDueDate($year, $month)->subDays($this->second_reminder_days);
    }

    /**
     * Get the final reminder date for a specific month and year.
     */
    public function getFinalReminderDate($year, $month)
    {
        return $this->getDueDate($year, $month)->subDays($this->final_reminder_days);
    }

    /**
     * Check if notifications are enabled.
     */
    public function notificationsEnabled()
    {
        return $this->enable_notifications;
    }

    /**
     * Check if SMS notifications are enabled.
     */
    public function smsNotificationsEnabled()
    {
        return $this->enable_notifications && $this->enable_sms_notifications;
    }

    /**
     * Check if email notifications are enabled.
     */
    public function emailNotificationsEnabled()
    {
        return $this->enable_notifications && $this->enable_email_notifications;
    }
} 