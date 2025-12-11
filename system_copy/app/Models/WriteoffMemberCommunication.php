<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WriteoffMemberCommunication extends Model
{
    use HasFactory;

    protected $fillable = [
        'writeoff_id',
        'loan_id',
        'client_number',
        'communication_type',
        'message_content',
        'sent_date',
        'delivery_status',
        'delivery_details',
        'template_used',
        'personalization_data',
        'sent_by',
        'member_acknowledged',
        'acknowledgment_date',
        'member_response'
    ];

    protected $casts = [
        'sent_date' => 'datetime',
        'acknowledgment_date' => 'datetime',
        'delivery_details' => 'array',
        'personalization_data' => 'array',
        'member_acknowledged' => 'boolean'
    ];

    // Relationships
    public function writeOff(): BelongsTo
    {
        return $this->belongsTo(LoanWriteOff::class, 'writeoff_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    // Scopes
    public function scopeDelivered($query)
    {
        return $query->where('delivery_status', 'delivered');
    }

    public function scopeFailed($query)
    {
        return $query->where('delivery_status', 'failed');
    }

    public function scopeAcknowledged($query)
    {
        return $query->where('member_acknowledged', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('communication_type', $type);
    }

    public function scopeByClient($query, $clientNumber)
    {
        return $query->where('client_number', $clientNumber);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('sent_date', [$from, $to]);
    }

    // Accessors
    public function getFormattedSentDateAttribute()
    {
        return $this->sent_date->format('d/m/Y H:i');
    }

    public function getDeliveryStatusBadgeAttribute()
    {
        $badges = [
            'pending' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Pending'],
            'delivered' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Delivered'],
            'failed' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Failed'],
            'read' => ['class' => 'bg-blue-100 text-blue-800', 'text' => 'Read'],
        ];

        return $badges[$this->delivery_status] ?? ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($this->delivery_status)];
    }

    public function getCommunicationTypeBadgeAttribute()
    {
        $badges = [
            'sms' => ['class' => 'bg-green-100 text-green-800', 'text' => 'SMS', 'icon' => 'message-square'],
            'email' => ['class' => 'bg-blue-100 text-blue-800', 'text' => 'Email', 'icon' => 'mail'],
            'letter' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Letter', 'icon' => 'file-text'],
            'call' => ['class' => 'bg-purple-100 text-purple-800', 'text' => 'Phone Call', 'icon' => 'phone'],
            'meeting' => ['class' => 'bg-indigo-100 text-indigo-800', 'text' => 'Meeting', 'icon' => 'users'],
        ];

        return $badges[$this->communication_type] ?? ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($this->communication_type), 'icon' => 'info'];
    }

    public function getTimeSinceSentAttribute()
    {
        return $this->sent_date->diffForHumans();
    }

    public function getResponseTimeAttribute()
    {
        if (!$this->member_acknowledged || !$this->acknowledgment_date) {
            return null;
        }

        $diff = $this->acknowledgment_date->diff($this->sent_date);
        
        if ($diff->days > 0) {
            return $diff->days . ' day' . ($diff->days > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
        } else {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        }
    }

    // Methods
    public function markAsDelivered(array $details = []): void
    {
        $this->update([
            'delivery_status' => 'delivered',
            'delivery_details' => array_merge($this->delivery_details ?? [], $details)
        ]);
    }

    public function markAsFailed(string $reason, array $details = []): void
    {
        $this->update([
            'delivery_status' => 'failed',
            'delivery_details' => array_merge($this->delivery_details ?? [], [
                'failure_reason' => $reason,
                'failed_at' => now()->toISOString(),
                ...$details
            ])
        ]);
    }

    public function markAsRead(): void
    {
        if ($this->delivery_status === 'delivered') {
            $this->update(['delivery_status' => 'read']);
        }
    }

    public function recordAcknowledgment(string $response = null): void
    {
        $this->update([
            'member_acknowledged' => true,
            'acknowledgment_date' => now(),
            'member_response' => $response
        ]);
    }

    public function resend(?User $sender = null): self
    {
        $newCommunication = $this->replicate();
        $newCommunication->sent_date = now();
        $newCommunication->delivery_status = 'pending';
        $newCommunication->member_acknowledged = false;
        $newCommunication->acknowledgment_date = null;
        $newCommunication->member_response = null;
        $newCommunication->sent_by = $sender?->id ?? auth()->id() ?? $this->sent_by;
        $newCommunication->delivery_details = [
            'resent' => true,
            'original_id' => $this->id,
            'resent_at' => now()->toISOString()
        ];
        $newCommunication->save();
        
        return $newCommunication;
    }

    // Static methods
    public static function getDeliveryStatistics($from = null, $to = null)
    {
        $query = self::query();
        
        if ($from && $to) {
            $query->whereBetween('sent_date', [$from, $to]);
        }

        $total = $query->count();
        $delivered = $query->clone()->where('delivery_status', 'delivered')->count();
        $failed = $query->clone()->where('delivery_status', 'failed')->count();
        $acknowledged = $query->clone()->where('member_acknowledged', true)->count();

        return [
            'total' => $total,
            'delivered' => $delivered,
            'failed' => $failed,
            'acknowledged' => $acknowledged,
            'delivery_rate' => $total > 0 ? round(($delivered / $total) * 100, 2) : 0,
            'acknowledgment_rate' => $delivered > 0 ? round(($acknowledged / $delivered) * 100, 2) : 0,
        ];
    }

    public static function getCommunicationSummaryByType($from = null, $to = null)
    {
        $query = self::query();
        
        if ($from && $to) {
            $query->whereBetween('sent_date', [$from, $to]);
        }

        return $query->selectRaw('
                communication_type,
                COUNT(*) as total,
                SUM(CASE WHEN delivery_status = "delivered" THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN delivery_status = "failed" THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN member_acknowledged = true THEN 1 ELSE 0 END) as acknowledged
            ')
            ->groupBy('communication_type')
            ->get()
            ->map(function($item) {
                $item->delivery_rate = $item->total > 0 
                    ? round(($item->delivered / $item->total) * 100, 2) : 0;
                $item->acknowledgment_rate = $item->delivered > 0 
                    ? round(($item->acknowledged / $item->delivered) * 100, 2) : 0;
                return $item;
            });
    }
}