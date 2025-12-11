<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'category_id',
        'status_id',
        'title',
        'description',
        'resolution_notes',
        'resolved_at',
        'assigned_to',
        'resolved_by',
        'priority',
        'reference_number',
        'attachments',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'attachments' => 'array',
        'priority' => 'integer',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_id');
    }

    public function category()
    {
        return $this->belongsTo(ComplaintCategory::class, 'category_id');
    }

    public function status()
    {
        return $this->belongsTo(ComplaintStatus::class, 'status_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status_id', 1); // Assuming 1 is pending status
    }

    public function scopeResolved($query)
    {
        return $query->where('status_id', 3); // Assuming 3 is resolved status
    }

    public function scopeInProgress($query)
    {
        return $query->where('status_id', 2); // Assuming 2 is in progress status
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Accessors
    public function getPriorityTextAttribute()
    {
        $priorities = [
            1 => 'Low',
            2 => 'Medium',
            3 => 'High',
            4 => 'Critical'
        ];

        return $priorities[$this->priority] ?? 'Unknown';
    }

    public function getPriorityColorAttribute()
    {
        $colors = [
            1 => 'bg-gray-100 text-gray-800',
            2 => 'bg-blue-100 text-blue-800',
            3 => 'bg-yellow-100 text-yellow-800',
            4 => 'bg-red-100 text-red-800'
        ];

        return $colors[$this->priority] ?? 'bg-gray-100 text-gray-800';
    }

    public function getResolutionTimeAttribute()
    {
        if ($this->resolved_at && $this->created_at) {
            return $this->created_at->diffInDays($this->resolved_at);
        }

        return null;
    }

    // Mutators
    public function setReferenceNumberAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['reference_number'] = 'COMP-' . str_pad($this->id ?? 1, 6, '0', STR_PAD_LEFT);
        } else {
            $this->attributes['reference_number'] = $value;
        }
    }

    // Boot method to auto-generate reference number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($complaint) {
            if (empty($complaint->reference_number)) {
                $lastComplaint = static::orderBy('id', 'desc')->first();
                $nextId = $lastComplaint ? $lastComplaint->id + 1 : 1;
                $complaint->reference_number = 'COMP-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
