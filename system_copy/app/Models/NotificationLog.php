<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{

    protected $fillable = [
        'process_id',
        'recipient_type',
        'recipient_id',
        'notification_type',
        'channel',
        'status',
        'error_message',
        'error_details',
        'attempts',
        'sent_at',
        'delivered_at',
        'failed_at',
        'response_data',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'error_details' => 'array',
        'response_data' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'attempts' => 'integer'
    ];

    public function recipient()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeByProcess($query, $processId)
    {
        return $query->where('process_id', $processId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'attempts' => $this->attempts + 1
        ]);
    }

    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);
    }

    public function markAsFailed($error, $details = null)
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $error,
            'error_details' => $details,
            'attempts' => $this->attempts + 1
        ]);
    }

    public static function logNotification($data)
    {
        return static::create([
            'process_id' => $data['process_id'],
            'recipient_type' => $data['recipient_type'],
            'recipient_id' => $data['recipient_id'],
            'notification_type' => $data['notification_type'],
            'channel' => $data['channel'],
            'status' => $data['status'],
            'error_message' => $data['error_message'] ?? null,
            'error_details' => $data['error_details'] ?? null,
            'attempts' => $data['attempts'] ?? 1,
            'sent_at' => $data['sent_at'] ?? now(),
            'delivered_at' => $data['delivered_at'] ?? null,
            'failed_at' => $data['failed_at'] ?? null,
            'response_data' => $data['response_data'] ?? null,
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null
        ]);
    }
} 