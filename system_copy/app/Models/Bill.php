<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ClientsModel;

class Bill extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'is_recurring' => 'boolean',
        'due_date' => 'date'
    ];

    public function member()
    {
        // Try to find by client_number first, then fall back to member_id
        return $this->belongsTo(ClientsModel::class, 'client_number', 'client_number')
                    ->orWhere('client_number', $this->member_id);
    }

    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_number', 'client_number');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function billingCycle()
    {
        return $this->belongsTo(BillingCycle::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the client/member information, trying both client_number and member_id
     */
    public function getClientInfoAttribute()
    {
        // First try to find by client_number
        if ($this->client_number) {
            $client = ClientsModel::where('client_number', $this->client_number)->first();
            if ($client) {
                return $client;
            }
        }
        
        // Fall back to member_id if client_number doesn't work
        if ($this->member_id) {
            $client = ClientsModel::where('client_number', $this->member_id)->first();
            if ($client) {
                return $client;
            }
        }
        
        return null;
    }

    /**
     * Get the client name for display
     */
    public function getClientNameAttribute()
    {
        $client = $this->client_info;
        return $client ? $client->full_name : ($this->member_id ?: $this->client_number ?: 'Unknown');
    }
}
